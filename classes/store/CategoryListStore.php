<?php namespace Lovata\GoodNews\Classes\Store;

use Kharanenka\Helper\CCache;
use Lovata\GoodNews\Models\Category;
use Lovata\GoodNews\Plugin;

/**
 * Class CategoryListStore
 *
 * @package Lovata\GoodNews\Classes\Store
 * @author Andrey Kahranenka, a.khoronenko@lovata.com, LOVATA Group
 */
class CategoryListStore
{
    const CACHE_TAG_LIST = 'good-news-category-list';
    const CACHE_KEY_TOP_LEVEL_LIST = 'good-news-category-top-level-list';
    
    /**
     * Get category list tree
     * @return array
     */
    public function getTree()
    {
        //Get cache data
        $arCacheTags = [Plugin::CACHE_TAG, self::CACHE_TAG_LIST];
        $sCacheKey = self::CACHE_KEY_TOP_LEVEL_LIST;

        //Get category ID list
        $arResult = [];
        $arCategoryListID = CCache::get($arCacheTags, $sCacheKey);
        if(empty($arCategoryListID)) {
            $arCategoryListID = Category::active()
                ->where('nest_depth', 0)
                ->orderBy('nest_left', 'asc')
                ->lists('id');
            CCache::forever($arCacheTags, $sCacheKey, $arCategoryListID);
        }

        if(empty($arCategoryListID)) {
            return $arResult;
        }

        return $arCategoryListID;
    }

    /**
     * Clear top level category ID list
     */
    public function clearTopLevelList()
    {
        $arCacheTags = [Plugin::CACHE_TAG, CategoryListStore::CACHE_TAG_LIST];
        $sCacheKey = CategoryListStore::CACHE_KEY_TOP_LEVEL_LIST;

        CCache::clear($arCacheTags, $sCacheKey);
    }
}