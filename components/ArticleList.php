<?php namespace Lovata\GoodNews\Components;

use Lang;
use Lovata\Toolbox\Classes\Component\SortingElementList;

use Lovata\GoodNews\Classes\Store\ArticleListStore;
use Lovata\GoodNews\Classes\Collection\ArticleCollection;

/**
 * Class ArticleList
 * @package Lovata\GoodNews\Components
 * @author Andrey Kahranenka, a.khoronenko@lovata.com, LOVATA Group
 */
class ArticleList extends SortingElementList
{
    /**
     * @return array
     */
    public function componentDetails()
    {
        return [
            'name'        => 'lovata.goodnews::lang.component.article_list',
            'description' => 'lovata.goodnews::lang.component.article_list_desc'
        ];
    }

    /**
     * @return array
     */
    public function defineProperties()
    {
        $arProperties = [
            'sorting' => [
                'title' => 'lovata.goodnews::lang.component.property_sorting',
                'type' => 'dropdown',
                'default' => ArticleListStore::SORT_PUBLISH_DESC,
                'options' => [
                    ArticleListStore::SORT_PUBLISH_ASC     => Lang::get('lovata.goodnews::lang.component.sorting_publish_asc'),
                    ArticleListStore::SORT_PUBLISH_DESC    => Lang::get('lovata.goodnews::lang.component.sorting_publish_desc'),
                    ArticleListStore::SORT_VIEW_COUNT_DESC => Lang::get('lovata.goodnews::lang.component.sorting_view_count_desc'),
                    ArticleListStore::SORT_VIEW_COUNT_ASC  => Lang::get('lovata.goodnews::lang.component.sorting_view_count_acs'),
                ],
            ],
        ];

        return $arProperties;
    }

    /**
     * Make element collection
     * @param array $arElementIDList
     *
     * @return ArticleCollection
     */
    public function make($arElementIDList = null)
    {
        return ArticleCollection::make($arElementIDList);
    }

    public function onAjaxRequest()
    {
        return true;
    }
}
