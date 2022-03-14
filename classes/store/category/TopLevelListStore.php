<?php namespace Lovata\GoodNews\Classes\Store\Category;

use Lovata\Toolbox\Classes\Store\AbstractStoreWithoutParam;

use Lovata\GoodNews\Models\Category;

/**
 * Class TopLevelListStore
 * @package Lovata\GoodNews\Classes\Store\Category
 * @author  Andrey Kharanenka, a.khoronenko@lovata.com, LOVATA Group
 */
class TopLevelListStore extends AbstractStoreWithoutParam
{
    protected static $instance;

    /**
     * Get ID list from database
     * @return array
     */
    protected function getIDListFromDB() : array
    {
        $arElementIDList = (array) Category::active()
            ->where('nest_depth', 0)
            ->orderBy('nest_left', 'asc')
            ->pluck('id')->all();

        return $arElementIDList;
    }
}
