<?php namespace Lovata\GoodNews\Classes\Store\Article;

use Lovata\Toolbox\Classes\Store\AbstractStoreWithoutParam;

use Lovata\GoodNews\Models\Article;

/**
 * Class PublishedListStore
 * @package Lovata\GoodNews\Classes\Store\Article
 * @author  Andrey Kharanenka, a.khoronenko@lovata.com, LOVATA Group
 */
class PublishedListStore extends AbstractStoreWithoutParam
{
    protected static $instance;

    /**
     * Get ID list from database
     * @return array
     */
    protected function getIDListFromDB() : array
    {
        $arElementIDList = (array) Article::getPublished()
            ->getByStatus(Article::STATUS_PUBLISHED)
            ->lists('id');

        return $arElementIDList;
    }
}
