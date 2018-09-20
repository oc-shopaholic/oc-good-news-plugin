<?php namespace Lovata\GoodNews\Components;

use Lovata\Toolbox\Classes\Component\ElementData;

use Lovata\GoodNews\Classes\Item\ArticleItem;

/**
 * Class ArticleData
 * @package Lovata\GoodNews\Components
 * @author Andrey Kahranenka, a.khoronenko@lovata.com, LOVATA Group
 */
class ArticleData extends ElementData
{
    /**
     * @return array
     */
    public function componentDetails()
    {
        return [
            'name'        => 'lovata.goodnews::lang.component.article_data',
            'description' => 'lovata.goodnews::lang.component.article_data_desc'
        ];
    }

    /**
     * Make new element item
     * @param int $iElementID
     * @return ArticleItem
     */
    protected function makeItem($iElementID)
    {
        return ArticleItem::make($iElementID);
    }
}
