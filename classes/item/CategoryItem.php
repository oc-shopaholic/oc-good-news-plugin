<?php namespace Lovata\GoodNews\Classes\Item;

use Lovata\Toolbox\Classes\Item\ElementItem;

use Lovata\GoodNews\Models\Category;
use Lovata\GoodNews\Classes\Collection\CategoryCollection;

/**
 * Class Category
 * @package Lovata\GoodNews\Classes\Item
 * @author  Andrey Kharanenka, a.khoronenko@lovata.com, LOVATA Group
 * @property int                               $id
 * @property string                            $name
 * @property string                            $slug
 * @property string                            $code
 * @property int                               $nest_depth
 * @property int                               $parent_id
 * @property string                            $preview_text
 * @property array                             $preview_image
 * @property string                            $description
 * @property array                             $images
 * @property CategoryItem                      $parent
 * @property array                             $children_id_list
 * @property CategoryCollection|CategoryItem[] $children
 */
class CategoryItem extends ElementItem
{
    const MODEL_CLASS = Category::class;

    /** @var Category */
    protected $obElement;

    public $arRelationList = [
        'parent'   => [
            'class' => CategoryItem::class,
            'field' => 'parent_id',
        ],
        'children' => [
            'class' => CategoryCollection::class,
            'field' => 'children_id_list',
        ],
    ];

    /**
     * Set element data from model object
     *
     * @return array
     */
    protected function getElementData()
    {
        $arResult = [
            'nest_depth' => $this->obElement->getDepth(),
        ];

        $arResult['children_id_list'] = $this->obElement->children()
            ->active()
            ->orderBy('nest_left', 'asc')
            ->lists('id');

        return $arResult;
    }
}