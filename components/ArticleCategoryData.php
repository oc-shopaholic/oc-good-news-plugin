<?php namespace Lovata\GoodNews\Components;

use Lovata\Toolbox\Classes\Component\ElementData;

use Lovata\GoodNews\Classes\Item\CategoryItem;

/**
 * Class ArticleCategoryData
 * @package Lovata\GoodNews\Components
 * @author Andrey Kahranenka, a.khoronenko@lovata.com, LOVATA Group
 */
class ArticleCategoryData extends ElementData
{
    /**
     * @return array
     */
    public function componentDetails()
    {
        return [
            'name'        => 'lovata.goodnews::lang.component.category_data',
            'description' => 'lovata.goodnews::lang.component.category_data_desc'
        ];
    }

    /**
     * Make new element item
     * @param int $iElementID
     * @return CategoryItem
     */
    protected function makeItem($iElementID)
    {
        return CategoryItem::make($iElementID);
    }
}
