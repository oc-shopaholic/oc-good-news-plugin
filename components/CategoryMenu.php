<?php namespace Lovata\GoodNews\Components;

use Lovata\GoodNews\Models\Settings;
use October\Rain\Database\Collection;
use Cms\Classes\ComponentBase;
use Kharanenka\Helper\CCache;
use Lovata\GoodNews\Models\Category;
use Lovata\GoodNews\Plugin;

/**
 * Class CategoryMenu
 * @package Lovata\GoodNews\Components
 * @author Andrey Kahranenka, a.khoronenko@lovata.com, LOVATA Group
 */
class CategoryMenu extends ComponentBase
{
    /**
     * @return array
     */
    public function componentDetails()
    {
        return [
            'name'        => 'lovata.goodnews::lang.component.category_menu',
            'description' => 'lovata.goodnews::lang.component.category_menu_desc'
        ];
    }

    /**
     * @return array
     */
    public function defineProperties()
    {
        $arProperties = [
            'slug' => [
                'title'             => 'lovata.goodnews::lang.component.property_slug',
                'type'              => 'string',
                'default'           => '{{:slug}}',
            ],
        ];

        return $arProperties;
    }

    /**
     * Get category list
     * @return array
     */
    public function get()
    {
        //Get cache data
        $arCacheTags = [Plugin::CACHE_TAG, Category::CACHE_TAG_LIST_MENU];
        $sCacheKey = Category::CACHE_TAG_LIST_MENU;

        $arResult = CCache::get($arCacheTags, $sCacheKey);
        if(!empty($arResult)) {
            $this->setActiveMenu($arResult);
            return $arResult;
        }

        $arResult = [];

        /** @var Collection $arCategories */
        $arCategories = Category::active()->orderBy('nest_left', 'asc')->get()->toNested();
        if($arCategories->isEmpty()) {
            return $arResult;
        }

        /** @var Category $obCategory */
        foreach($arCategories as $obCategory) {
            $arResult[$obCategory->id] = $obCategory->getData();
        }

        //Set cache data
        $iCacheTime = Settings::getCacheTime('cache_time_category');
        CCache::put($arCacheTags, $sCacheKey, $arResult, $iCacheTime);

        $this->setActiveMenu($arResult);
        return $arResult;
    }

    /**
     * Set active menu by slug
     * @param array $arCategoryList
     */
    protected function setActiveMenu(&$arCategoryList)
    {
        if(empty($arCategoryList)) {
            return;
        }
        
        //get slug value
        $sSlug = $this->property('slug');
        
        //Set active element
        foreach($arCategoryList as &$arCategoryData) {
            if($sSlug == $arCategoryData['slug']) {
                $arCategoryData['active'] = true;
            } else {
                $arCategoryData['active'] = false;
            }
            
            $this->setActiveMenu($arCategoryData['children']);
        }
    }
}
