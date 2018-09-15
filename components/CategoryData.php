<?php namespace Lovata\GoodNews\Components;

use Lovata\GoodNews\Classes\Item\CategoryItem;
use Lovata\Toolbox\Classes\Component\ElementData;

/**
 * Class CategoryData
 * @package Lovata\GoodNews\Components
 * @author Andrey Kahranenka, a.khoronenko@lovata.com, LOVATA Group
 */
class CategoryData extends ElementData
{
    protected $iElementID;

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
