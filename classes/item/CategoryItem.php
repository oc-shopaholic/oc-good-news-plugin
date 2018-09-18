<?php namespace Lovata\GoodNews\Classes\Item;

use Lovata\Toolbox\Classes\Item\ElementItem;

use Lovata\GoodNews\Models\Category;
use Lovata\GoodNews\Classes\Collection\CategoryCollection;

/**
 * Class Category
 * @package Lovata\GoodNews\Classes\Item
 * @author  Dmitry Drozd, d.drozd@lovata.com, LOVATA Group
 * @property                                   $id
 * @property string                            $name
 * @property string                            $slug
 * @property string                            $code
 * @property string                            $active
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
    protected $obElement = null;

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

    /**
     * Get Breadcrumbs
     * @param array $arResult
     * @param bool  $bSetFalse
     * @return array
     */
    public function getBreadcrumbs(&$arResult = [], $bSetFalse = false)
    {
        $arResult[] = [
            'id'     => $this->id,
            'name'   => $this->name,
            'slug'   => $this->slug,
            'active' => $bSetFalse ? false : true,
            'page'   => 'goodnews_category',
        ];

        if ($this->parent_id) {
            $obCategory = $this->parent;
            $obCategory->getBreadcrumbs($arResult, true);
        }

        return array_reverse($arResult);
    }
}