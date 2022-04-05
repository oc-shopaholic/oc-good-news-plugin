<?php namespace Lovata\GoodNews\Classes\Store\Category;

use Lovata\Toolbox\Classes\Store\AbstractStoreWithoutParam;

use Lovata\GoodNews\Models\Category;

/**
 * Class ActiveListStore
 * @package Lovata\GoodNews\Classes\Store\Category
 * @author  Andrey Kharanenka, a.khoronenko@lovata.com, LOVATA Group
 */
class ActiveListStore extends AbstractStoreWithoutParam
{
    protected static $instance;

    /**
     * Get ID list from database
     * @return array
     */
    protected function getIDListFromDB() : array
    {
        $arElementIDList = (array) Category::active()->pluck('id')->all();

        return $arElementIDList;
    }
}
