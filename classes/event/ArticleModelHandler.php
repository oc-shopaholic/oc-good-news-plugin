<?php namespace Lovata\GoodNews\Classes\Event;

use Site;
use Lovata\Toolbox\Models\Settings;
use Lovata\Toolbox\Classes\Event\ModelHandler;

use Lovata\GoodNews\Classes\Store\ArticleListStore;
use Lovata\GoodNews\Classes\Item\ArticleItem;
use Lovata\GoodNews\Models\Article;
use Lovata\GoodNews\Classes\Item\CategoryItem;

/**
 * Class ArticleModelHandler
 * @package Lovata\GoodNews\Classes\Event
 * @author  Sergey Zakharevich, s.zakharevich@lovata.com, LOVATA Group
 */
class ArticleModelHandler extends ModelHandler
{
    /** @var  Article */
    protected $obElement;

    public function subscribe($obEvent)
    {
        parent::subscribe($obEvent);

        Article::extend(function ($obModel) {
            /** @var Article $obModel */
            $bSlugIsTranslatable = Settings::getValue('slug_is_translatable');
            if ($bSlugIsTranslatable) {
                $obModel->translatable[] = ['slug', 'index' => true];
            }
        });
    }

    /**
     * After create event handler
     */
    protected function afterCreate()
    {
        parent::afterCreate();

        $this->clearCachedListBySite();

        ArticleListStore::instance()->sorting->clear(ArticleListStore::SORT_NO);
    }

    /**
     * After save event handler
     */
    protected function afterSave()
    {
        parent::afterSave();

        $this->checkFieldChanges('status_id', ArticleListStore::instance()->published);
        $this->checkFieldChanges('published_start', ArticleListStore::instance()->published);
        $this->checkFieldChanges('published_stop', ArticleListStore::instance()->published);

        if ($this->isFieldChanged('published_start')) {
            $this->clearBySortingPublished();
            $this->clearCategoryArticleCount($this->obElement->category_id);
            $this->clearCategoryArticleCount((int) $this->obElement->getOriginal('category_id'));
        }

        if ($this->isFieldChanged('view_count')) {
            $this->clearBySortingViews();
        }

        //Check "category_id" field
        $this->checkCategoryIDField();

        $this->checkStatusField();

        if ($this->isFieldChanged('site_list')) {
            $this->clearCachedListBySite();
        }
    }

    /**
     * After delete event handler
     */
    protected function afterDelete()
    {
        parent::afterDelete();

        if ($this->obElement->status_id == Article::STATUS_PUBLISHED) {
            ArticleListStore::instance()->published->clear();
        }

        $this->clearBySortingPublished();
        $this->clearBySortingViews();

        ArticleListStore::instance()->category->clear($this->obElement->category_id);

        $this->clearCategoryArticleCount($this->obElement->category_id);
        $this->clearCategoryArticleCount((int) $this->obElement->getOriginal('category_id'));

        $this->clearCachedListBySite();
    }

    /**
     * Clear cache by sorting published
     */
    protected function clearBySortingPublished()
    {
        ArticleListStore::instance()->sorting->clear(ArticleListStore::SORT_PUBLISH_ASC);
        ArticleListStore::instance()->sorting->clear(ArticleListStore::SORT_PUBLISH_DESC);
    }

    /**
     * Clear cache by sorting views
     */
    protected function clearBySortingViews()
    {
        ArticleListStore::instance()->sorting->clear(ArticleListStore::SORT_VIEW_COUNT_ASC);
        ArticleListStore::instance()->sorting->clear(ArticleListStore::SORT_VIEW_COUNT_DESC);
    }
    /**
     * Check article "status_id" field, if it was changed, then clear cache
     */
    protected function checkStatusField()
    {
        //check article "status_id" field
        if (!$this->isFieldChanged('status_id')) {
            return;
        }

        ArticleListStore::instance()->published->clear();

        $this->clearCategoryArticleCount($this->obElement->category_id);
    }

    /**
     * Check article "category_id" field, if it was changed, then clear cache
     */
    protected function checkCategoryIDField()
    {
        //Check "category_id" field
        if (!$this->isFieldChanged('category_id')) {
            return;
        }

        //Update article ID cache list for category
        ArticleListStore::instance()->category->clear($this->obElement->category_id);
        ArticleListStore::instance()->category->clear((int) $this->obElement->getOriginal('category_id'));


        $this->clearCategoryArticleCount($this->obElement->category_id);
        $this->clearCategoryArticleCount((int) $this->obElement->getOriginal('category_id'));
    }

    /**
     * Clear article count cache in category item
     * @param int $iCategoryID
     */
    protected function clearCategoryArticleCount($iCategoryID)
    {
        $obCategoryItem = CategoryItem::make($iCategoryID);
        if ($obCategoryItem->isNotEmpty()) {
            $obCategoryItem->clearArticleCount();
        }
    }

    /**
     * Clear filtered articles by site ID
     */
    protected function clearCachedListBySite()
    {
        /** @var \October\Rain\Database\Collection $obSiteList */
        $obSiteList = Site::listEnabled();
        if (empty($obSiteList) || $obSiteList->isEmpty()) {
            return;
        }

        foreach ($obSiteList as $obSite) {
            ArticleListStore::instance()->site->clear($obSite->id);
        }
    }

    /**
     * Get model class name
     * @return string
     */
    protected function getModelClass()
    {
        return Article::class;
    }

    /**
     * Get item class name
     * @return string
     */
    protected function getItemClass()
    {
        return ArticleItem::class;
    }
}
