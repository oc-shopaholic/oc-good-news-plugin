<?php namespace Lovata\GoodNews\Classes\Item;

use Lovata\GoodNews\Classes\Collection\CategoryCollection;
use Lovata\GoodNews\Models\Category;
use Lovata\Toolbox\Classes\Item\ElementItem;

use Lovata\GoodNews\Plugin;

/**
 * Class Category
 * @package Lovata\GoodNews\Classes\Item
 * @author Dmitry Drozd, d.drozd@lovata.com, LOVATA Group
 *
 * @property        $id
 * @property string $name
 * @property string $slug
 * @property string $code
 * @property int    $nest_depth
 * @property int    $parent_id
 *
 * @property string $preview_text
 * @property array  $preview_image
 *
 * @property string $description
 * @property array  $images
 *
 * @property CategoryItem $parent
 *
 * @property array  $children_id_list
 * @property CategoryCollection|CategoryItem[] $children
 */
class CategoryItem extends ElementItem
{
    /** @var Category */
    protected $obElement = null;

    const CACHE_TAG_ELEMENT = 'good-news-category-element';

    public $arRelationList = [
        'parent' => [
            'class' => CategoryItem::class,
            'field' => 'parent_id',
        ],
        'children' => [
            'class' => CategoryCollection::class,
            'field' => 'children_id_list',
        ],
    ];

    /**
     * Set element object
     */
    protected function setElementObject()
    {
        if(!empty($this->obElement) && ! $this->obElement instanceof Category) {
            $this->obElement = null;
        }

        if(!empty($this->obElement) || empty($this->iElementID)) {
            return;
        }

        $this->obElement = Category::active()->find($this->iElementID);
    }

    /**
     * Get cache tag array for model
     * @return array
     */
    protected static function getCacheTag()
    {
        return [Plugin::CACHE_TAG, self::CACHE_TAG_ELEMENT];
    }

    protected function getElementData()
    {
        if(empty($this->obElement)) {
            return null;
        }

        $arResult = [
            'id'            => $this->obElement->id,
            'name'          => $this->obElement->name,
            'slug'          => $this->obElement->slug,
            'code'          => $this->obElement->code,
            'preview_text'  => $this->obElement->preview_text,
            'description'   => $this->obElement->description,
            'nest_depth'    => $this->obElement->getDepth(),
            'parent_id'     => $this->obElement->parent_id,
            'preview_image' => $this->obElement->getFileData('preview_image'),
            'images'        => $this->obElement->getFileListData('images'),
        ];

        $arResult['children_id_list'] = $this->obElement->children()
            ->active()
            ->orderBy('nest_left', 'asc')
            ->lists('id');

        return $arResult;
    }

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