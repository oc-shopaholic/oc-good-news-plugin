<?php namespace Lovata\GoodNews\Classes\Store;

use Kharanenka\Helper\CCache;

use Lovata\GoodNews\Plugin;
use Lovata\GoodNews\Models\Article;
use Lovata\GoodNews\Classes\Item\CategoryItem;

/**
 * Class ArticleListStore
 *
 * @package Lovata\GoodNews\Classes\Store
 * @author Andrey Kahranenka, a.khoronenko@lovata.com, LOVATA Group
 */
class ArticleListStore
{
    const CACHE_TAG_LIST = 'good-news-article-list';

    const SORT_NO = 'no';
    const SORT_PUBLISH_ASC = 'publish|asc';
    const SORT_PUBLISH_DESC = 'publish|desc';
    const SORT_VIEW_COUNT_ASC = 'view|asc';
    const SORT_VIEW_COUNT_DESC = 'view|desc';

    /**
     * Get available sorting value list
     *
     * @return array
     */
    public function getAvailableSorting()
    {
        return [
            self::SORT_NO,
            self::SORT_PUBLISH_ASC,
            self::SORT_PUBLISH_DESC,
            self::SORT_VIEW_COUNT_ASC,
            self::SORT_VIEW_COUNT_DESC,
        ];
    }

    /**
     * Get article ID list by sorting value
     *
     * @param string $sSorting
     * @return array
     */
    public function getBySorting($sSorting)
    {
        // Get cache data
        $arCacheTags = [Plugin::CACHE_TAG, self::CACHE_TAG_LIST];
        $sCacheKey = $sSorting;

        $arArticleIDList = CCache::get($arCacheTags, $sCacheKey);
        if(!empty($arArticleIDList)) {
            return $arArticleIDList;
        }

        return $this->updateCacheBySorting($sSorting);
    }

    /**
     * Update cache article ID list by sorting
     *
     * @param string $sSorting
     * @return array|null
     */
    public function updateCacheBySorting($sSorting)
    {
        // Get cache data
        $arCacheTags = [Plugin::CACHE_TAG, self::CACHE_TAG_LIST];
        $sCacheKey = $sSorting;
        
        /** @var array $arArticleIDList */
        switch($sSorting) {
            case self::SORT_PUBLISH_ASC :

                // Get article ID list (sort by date publication)
                $arArticleIDList = Article::orderBy('published_start', 'asc')->lists('id');
                if(empty($arArticleIDList)) {
                    return null;
                }

                break;
            case self::SORT_PUBLISH_DESC :

                // Get article ID list (sort by date publication)
                $arArticleIDList = Article::orderBy('published_start', 'desc')->lists('id');
                if(empty($arArticleIDList)) {
                    return null;
                }

                break;
            case self::SORT_VIEW_COUNT_ASC :

                // Get article ID list (sort by view_count)
                $arArticleIDList = Article::orderBy('view_count', 'asc')->lists('id');
                if(empty($arArticleIDList)) {
                    return null;
                }

                break;
            case self::SORT_VIEW_COUNT_DESC :

                // Get article ID list (sort by view_count)
                $arArticleIDList = Article::orderBy('view_count', 'desc')->lists('id');
                if(empty($arArticleIDList)) {
                    return null;
                }

                break;
            default:
                $arArticleIDList = Article::lists('id');
                break;
        }

        if(empty($arArticleIDList)) {
            return null;
        }

        //Set cache data
        CCache::forever($arCacheTags, $sCacheKey, $arArticleIDList);

        return $arArticleIDList;
    }

    /**
     * Get published article ID list
     *
     * @return array|null
     */
    public function getPublishedList()
    {
        //Get cache data
        $arCacheTags = [Plugin::CACHE_TAG, self::CACHE_TAG_LIST];
        $sCacheKey = self::CACHE_TAG_LIST;

        $arArticleIDList = CCache::get($arCacheTags, $sCacheKey);
        if(empty($arArticleIDList)) {

            //Get article ID list
            //Added select by status "published"
            $arArticleIDList = Article::getByStatus(Article::STATUS_PUBLISHED)->getPublished()->lists('id');
            if(empty($arArticleIDList)) {
                return null;
            }

            //Set cache data
            CCache::forever($arCacheTags, $sCacheKey, $arArticleIDList);
        }

        return $arArticleIDList;
    }

    /**
     * Clear published element list
     */
    public function clearPublishedList()
    {
        $arCacheTags = [Plugin::CACHE_TAG, self::CACHE_TAG_LIST];
        $sCacheKey = self::CACHE_TAG_LIST;

        CCache::clear($arCacheTags, $sCacheKey);
    }

    /**
     * Get cached article ID list by category ID
     *
     * @param int $iCategoryID
     * @return array|null
     */
    public function getByCategory($iCategoryID)
    {
        if(empty($iCategoryID)) {
            return null;
        }

        // Get cache data
        $arCacheTags = [Plugin::CACHE_TAG, self::CACHE_TAG_LIST, CategoryItem::CACHE_TAG_ELEMENT];
        $sCacheKey = $iCategoryID;

        $arArticleIDList = CCache::get($arCacheTags, $sCacheKey);
        if(!empty($arArticleIDList)) {
            return $arArticleIDList;
        }

        /** @var array $arArticleIDList - Get article ID list */
        $arArticleIDList = Article::getByCategory($iCategoryID)->lists('id');
        if(empty($arArticleIDList)) {
            return null;
        }

        // Set cache data
        CCache::forever($arCacheTags, $sCacheKey, $arArticleIDList);

        return $arArticleIDList;
    }

    /**
     * Clear list with filter by category ID
     * @param int $iCategoryID
     */
    public function clearListByCategory($iCategoryID)
    {
        if(empty($iCategoryID)) {
            return;
        }

        // Get cache data
        $arCacheTags = [Plugin::CACHE_TAG, self::CACHE_TAG_LIST, CategoryItem::CACHE_TAG_ELEMENT];
        $sCacheKey = $iCategoryID;

        CCache::clear($arCacheTags, $sCacheKey);
    }

    /**
     * Get cached article ID list by status ID
     *
     * @param int $iStatusID
     * @return array|null
     */
    public function getByStatus($iStatusID)
    {
        if(empty($iStatusID)) {
            return null;
        }

        // Get cache data
        $arCacheTags = [Plugin::CACHE_TAG, self::CACHE_TAG_LIST];
        $sCacheKey = implode('_', [self::CACHE_TAG_LIST, $iStatusID]);

        $arArticleIDList = CCache::get($arCacheTags, $sCacheKey);
        if(!empty($arArticleIDList)) {
            return $arArticleIDList;
        }

        /** @var array $arArticleIDList - Get article ID list */
        $arArticleIDList = Article::getByStatus($iStatusID)->lists('id');
        if(empty($arArticleIDList)) {
            return null;
        }

        // Set cache data
        CCache::forever($arCacheTags, $sCacheKey, $arArticleIDList);

        return $arArticleIDList;
    }

    /**
     * Clear element list with filter by status ID
     * @param int $iStatusID
     */
    public function clearListByStatus($iStatusID)
    {
        if(empty($iStatusID)) {
            return;
        }

        // Get cache data
        $arCacheTags = [Plugin::CACHE_TAG, self::CACHE_TAG_LIST];
        $sCacheKey = implode('_', [self::CACHE_TAG_LIST, $iStatusID]);

        CCache::clear($arCacheTags, $sCacheKey);
    }
}