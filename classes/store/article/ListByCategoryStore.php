<?php namespace Lovata\GoodNews\Classes\Store\Article;

use Lovata\Toolbox\Classes\Store\AbstractStoreWithParam;

use Lovata\GoodNews\Models\Article;

/**
 * Class ListByCategoryStore
 * @package Lovata\GoodNews\Classes\Store\Article
 * @author  Sergey Zakharevich, s.zakharevich@lovata.com, LOVATA Group
 */
class ListByCategoryStore extends AbstractStoreWithParam
{
    protected static $instance;

    /**
     * Get ID list from database
     * @return array
     */
    protected function getIDListFromDB() : array
    {
        $arElementIDList = (array) Article::getByCategory($this->sValue)->pluck('id')->all();

        return $arElementIDList;
    }
}
