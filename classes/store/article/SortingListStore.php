<?php namespace Lovata\GoodNews\Classes\Store\Article;

use Lovata\GoodNews\Models\Article;
use Lovata\GoodNews\Classes\Store\ArticleListStore;
use Lovata\Toolbox\Classes\Store\AbstractStoreWithParam;

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
        }

        return $arElementIDList;
    }

    /**
     * Get default article list
     * @return array
     */
    protected function getArticleList() : array
    {
        $arElementIDList = (array) Article::getPublished()
            ->getByStatus(Article::STATUS_PUBLISHED)
            ->lists('id');

        return $arElementIDList;
    }

    /**
     * Get sorting ID list by published (ASC)
     * @return array
     */
    protected function getByPublishASC() : array
    {
        $arElementIDList = (array) Article::getPublished()
            ->getByStatus(Article::STATUS_PUBLISHED)
            ->orderBy('published_start', 'asc')
            ->lists('id');

        $arElementIDList = array_unique($arElementIDList);

        return $arElementIDList;
    }

    /**
     * Get sorting ID list by published (DESC)
     * @return array
     */
    protected function getByPublishDESC() : array
    {
        $arElementIDList = (array) Article::getPublished()
            ->getByStatus(Article::STATUS_PUBLISHED)
            ->orderBy('published_start', 'desc')
            ->lists('id');

        $arElementIDList = array_unique($arElementIDList);

        return $arElementIDList;
    }

    /**
     * Get sorting ID list by views (ASC)
     * @return array
     */
    protected function getByViewsASC() : array
    {
        $arElementIDList = (array) Article::getPublished()
            ->getByStatus(Article::STATUS_PUBLISHED)
            ->orderBy('view_count', 'asc')
            ->lists('id');

        $arElementIDList = array_unique($arElementIDList);

        return $arElementIDList;
    }

    /**
     * Get sorting ID list by views (DESC)
     * @return array
     */
    protected function getByViewsDESC() : array
    {
        $arElementIDList = (array) Article::getPublished()
            ->getByStatus(Article::STATUS_PUBLISHED)
            ->orderBy('view_count', 'asc')
            ->lists('id');

        $arElementIDList = array_unique($arElementIDList);

        return $arElementIDList;
    }
}
