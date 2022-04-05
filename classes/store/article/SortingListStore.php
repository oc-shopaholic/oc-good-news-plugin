<?php namespace Lovata\GoodNews\Classes\Store\Article;

use Event;
use Lovata\Toolbox\Classes\Store\AbstractStoreWithParam;

use Lovata\GoodNews\Models\Article;
use Lovata\GoodNews\Classes\Store\ArticleListStore;

/**
 * Class SortingListStore
 * @package Lovata\GoodNews\Classes\Store\Article
 * @author  Andrey Kharanenka, a.khoronenko@lovata.com, LOVATA Group
 */
class SortingListStore extends AbstractStoreWithParam
{
    protected static $instance;

    /**
     * Get ID list from database
     * @return array
     */
    protected function getIDListFromDB() : array
    {
        switch ($this->sValue) {
            case ArticleListStore::SORT_NO:
                $arElementIDList = $this->getArticleList();
                break;
            case ArticleListStore::SORT_PUBLISH_ASC:
                $arElementIDList = $this->getByPublishASC();
                break;
            case ArticleListStore::SORT_PUBLISH_DESC:
                $arElementIDList = $this->getByPublishDESC();
                break;
            case ArticleListStore::SORT_VIEW_COUNT_ASC:
                $arElementIDList = $this->getByViewsASC();
                break;
            case ArticleListStore::SORT_VIEW_COUNT_DESC:
                $arElementIDList = $this->getByViewsDESC();
                break;
            default:
                $arElementIDList = $this->getCustomSortingList();
                break;
        }

        return $arElementIDList;
    }

    /**
     * Get default article list
     * @return array
     */
    protected function getArticleList() : array
    {
        $arElementIDList = (array) Article::pluck('id')->all();

        return $arElementIDList;
    }

    /**
     * Get sorting ID list by published (ASC)
     * @return array
     */
    protected function getByPublishASC() : array
    {
        $arElementIDList = (array) Article::orderBy('published_start', 'asc')->pluck('id')->all();

        return $arElementIDList;
    }

    /**
     * Get sorting ID list by published (DESC)
     * @return array
     */
    protected function getByPublishDESC() : array
    {
        $arElementIDList = (array) Article::orderBy('published_start', 'desc')->pluck('id')->all();

        return $arElementIDList;
    }

    /**
     * Get sorting ID list by views (ASC)
     * @return array
     */
    protected function getByViewsASC() : array
    {
        $arElementIDList = (array) Article::orderBy('view_count', 'asc')->pluck('id')->all();

        return $arElementIDList;
    }

    /**
     * Get sorting ID list by views (DESC)
     * @return array
     */
    protected function getByViewsDESC() : array
    {
        $arElementIDList = (array) Article::orderBy('view_count', 'desc')->pluck('id')->all();

        return $arElementIDList;
    }

    /**
     * Get element list with custom sorting
     * @return array
     */
    protected function getCustomSortingList() : array
    {
        $arElementIDList = Event::fire('good_news.sorting.get.list', $this->sValue, true);
        if (empty($arElementIDList) || !is_array($arElementIDList)) {
            return [];
        }

        return $arElementIDList;
    }
}
