<?php namespace Lovata\GoodNews\Classes\Event;

use Cache;
use October\Rain\Argon\Argon;
use Lovata\Toolbox\Classes\Event\ModelHandler;

use Lovata\GoodNews\Classes\Store\ArticleListStore;
use Lovata\GoodNews\Classes\Item\ArticleItem;
use Lovata\GoodNews\Models\Article;

/**
 * Class ArticleModelHandler
 * @package Lovata\GoodNews\Classes\Event
 * @author  Sergey Zakharevich, s.zakharevich@lovata.com, LOVATA Group
 */
class ArticleModelHandler extends ModelHandler
{
    const CACHE_TAG_DATA_PUBLISHED_START = 'lovata.goodnews.data_published_start';
    const CACHE_TAG_DATA_PUBLISHED_STOP = 'lovata.goodnews.data_published_stop';

    /** @var  Article */
    protected $obElement;

    /**
     * Add listeners
     * @param \Illuminate\Events\Dispatcher $obEvent
     */
    public function subscribe($obEvent)
    {
        parent::subscribe($obEvent);

        $obEvent->listen('good_news.category.update.sorting', function () {
            $this->checkDateUpdate();
        });
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

    /**
     * After save event handler
     */
    protected function afterSave()
    {
        parent::afterSave();

        if ($this->isFieldChanged('status_id')) {
            $this->clearByPublished();
        }

        if ($this->isFieldChanged('published_start') || $this->isFieldChanged('published_stop')) {
            $this->clearBySortingPublished();
        }

        if ($this->isFieldChanged('view_count')) {
            $this->clearBySortingViews();
        }

        if ($this->isFieldChanged('category_id') && !empty($this->obElement->category_id)) {
            $this->clearByCategory($this->obElement->category_id);
        }
        $this->setCacheDatePublishedStart();
        $this->setCacheDatePublishedStop();
    }

    /**
     * After delete event handler
     */
    protected function afterDelete()
    {
        parent::afterDelete();

        if ($this->obElement->status_id == Article::STATUS_PUBLISHED) {
            $this->clearByPublished();
            $this->clearBySortingPublished();
            $this->clearBySortingViews();

            if (!empty($this->obElement->category_id)) {
                $this->clearByCategory($this->obElement->category_id);
            }
        }
        $this->setCacheDatePublishedStart();
        $this->setCacheDatePublishedStop();
    }

    /**
     * Clear cache by sorting published
     */
    protected function clearBySortingPublished()
    {
        ArticleListStore::instance()->sorting->clear(ArticleListStore::SORT_NO);
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
     * Clear cache by published
     */
    protected function clearByPublished()
    {
        ArticleListStore::instance()->published->clear();
    }

    /**
     * Clear cache by published
     * @param mixed $arCategoryIdList
     */
    protected function clearByCategory($arCategoryIdList)
    {
        if (!empty($arCategoryIdList)) {
            ArticleListStore::instance()->category->clear($arCategoryIdList);
        }
    }

    /**
     * Cached min date published start
     */
    protected function setCacheDatePublishedStart()
    {
        $obArticle = Article::getByPublishedStart()
            ->getByStatus(Article::STATUS_PUBLISHED)
            ->orderBy('published_start', 'asc')
            ->first();

        $arPublishedStart = null;
        if (!empty($obArticle) && !empty($obArticle->published_start)) {
            $arPublishedStart = [
                'published_start' => $obArticle->published_start,
                'category_id'     => $obArticle->category_id,
            ];
        }
        Cache::forever(self::CACHE_TAG_DATA_PUBLISHED_START, $arPublishedStart);
    }

    /**
     * Cached min date published stop
     */
    protected function setCacheDatePublishedStop()
    {
        $obArticle = Article::getByPublishedStop()
            ->getByStatus(Article::STATUS_PUBLISHED)
            ->orderBy('published_stop', 'asc')
            ->first();

        $arPublishedStop = null;
        if (!empty($obArticle) && !empty($obArticle->published_stop)) {
            $arPublishedStop = [
                'published_stop' => $obArticle->published_stop,
                'category_id'    => $obArticle->category_id,
            ];
        }
        Cache::forever(self::CACHE_TAG_DATA_PUBLISHED_STOP, $arPublishedStop);
    }

    /**
     * Check and update by date published
     */
    protected function checkDateUpdate()
    {
        $sDataNow = Argon::now()->format('Y-m-d H:i:s');

        $arPublishedStart = Cache::get(self::CACHE_TAG_DATA_PUBLISHED_START);
        $arPublishedStop = Cache::get(self::CACHE_TAG_DATA_PUBLISHED_STOP);

        $bClear = false;

        if (!empty($arPublishedStart) && $arPublishedStart['published_start'] < $sDataNow) {
            $this->setCacheDatePublishedStart();
            $bClear = true;
            if (!empty($arPublishedStart['category_id'])) {
                $this->clearByCategory($arPublishedStart['category_id']);
            }
        }

        if (!empty($arPublishedStop) && $arPublishedStop['published_stop'] < $sDataNow) {
            $this->setCacheDatePublishedStop();
            $bClear = true;
            if (!empty($arPublishedStop['category_id'])) {
                $this->clearByCategory($arPublishedStop['category_id']);
            }
        }

        if ($bClear) {
            $this->clearByPublished();
            $this->clearBySortingPublished();
            $this->clearBySortingViews();
        }
    }
}