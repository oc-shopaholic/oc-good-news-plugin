<?php namespace Lovata\GoodNews\Classes\Event;

use Site;
use Lovata\Toolbox\Models\Settings;
use Lovata\Toolbox\Classes\Event\ModelHandler;

use Lovata\GoodNews\Models\Category;
use Lovata\GoodNews\Classes\Item\CategoryItem;
use Lovata\GoodNews\Classes\Store\CategoryListStore;

/**
 * Class CategoryModelHandler
 * @package Lovata\GoodNews\Classes\Event
 * @author Andrey Kharanenka, a.khoronenko@lovata.com, LOVATA Group
 */
class CategoryModelHandler extends ModelHandler
{
    /** @var  Category */
    protected $obElement;

    /**
     * Add listeners
     * @param \Illuminate\Events\Dispatcher $obEvent
     */
    public function subscribe($obEvent)
    {
        parent::subscribe($obEvent);

        Category::extend(function ($obModel) {
            /** @var Category $obModel */
            $bSlugIsTranslatable = Settings::getValue('slug_is_translatable');
            if ($bSlugIsTranslatable) {
                $obModel->translatable[] = ['slug', 'index' => true];
            }
        });

        $obEvent->listen('good_news.category.update.sorting', function () {
            CategoryListStore::instance()->top_level->clear();

            //Get category ID list
            $arCategoryIDList = Category::pluck('id')->all();
            if (empty($arCategoryIDList)) {
                return;
            }

            //Clear cache for all categories
            foreach ($arCategoryIDList as $iCategoryID) {
                CategoryItem::clearCache($iCategoryID);
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
    }

    /**
     * After save event handler
     */
    protected function afterSave()
    {
        parent::afterSave();

        CategoryListStore::instance()->top_level->clear();

        $this->checkFieldChanges('active', CategoryListStore::instance()->active);

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

        CategoryListStore::instance()->top_level->clear();

        //Clear parent item cache
        if (!empty($this->obElement->parent_id)) {
            CategoryItem::clearCache($this->obElement->parent_id);
        }

        if ($this->obElement->active) {
            CategoryListStore::instance()->active->clear();
        }

        $this->clearCachedListBySite();
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
            CategoryListStore::instance()->site->clear($obSite->id);
        }
    }

    /**
     * Get model class name
     * @return string
     */
    protected function getModelClass()
    {
        return Category::class;
    }

    /**
     * Get item class name
     * @return string
     */
    protected function getItemClass()
    {
        return CategoryItem::class;
    }
}
