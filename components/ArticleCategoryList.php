<?php namespace Lovata\GoodNews\Components;

use Cms\Classes\ComponentBase;
use Lovata\GoodNews\Classes\Collection\CategoryCollection;

/**
 * Class ArticleCategoryList
 * @package Lovata\GoodNews\Components
 * @author Andrey Kharanenka, a.khoronenko@lovata.com, LOVATA Group
 */
class ArticleCategoryList extends ComponentBase
{
    /**
     * @return array
     */
    public function componentDetails()
    {
        return [
            'name'          => 'lovata.goodnews::lang.component.category_list',
            'description'   => 'lovata.goodnews::lang.component.category_list_desc',
        ];
    }

    /**
     * Make element collection
     * @param array $arElementIDList
     *
     * @return CategoryCollection
     */
    public function make($arElementIDList = null)
    {
        return CategoryCollection::make($arElementIDList);
    }

    /**
     * Method for ajax request with empty response
     * @return bool
     */
    public function onAjaxRequest()
    {
        return true;
    }
}
