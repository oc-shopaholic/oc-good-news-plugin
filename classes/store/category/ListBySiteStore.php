<?php namespace Lovata\GoodNews\Classes\Store\Category;

use Lovata\Toolbox\Classes\Store\AbstractStoreWithParam;

use Lovata\GoodNews\Models\Category;

/**
 * @package Lovata\Shopaholic\Classes\Store\Product
 * @author  Andrey Kharanenka, a.khoronenko@lovata.com, LOVATA Group
 */
class ListBySiteStore extends AbstractStoreWithParam
{
    protected static $instance;

    /**
     * Get ID list from database
     * @return array
     */
    protected function getIDListFromDB() : array
    {
        $arElementIDList = (array) Category::whereHas('site', function($obQuery) {
            return $obQuery->where('id', $this->sValue);
        })
            ->orDoesntHave('site')
            ->pluck('id')
            ->all();

        return $arElementIDList;
    }
}
