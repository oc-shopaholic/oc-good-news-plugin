<?php namespace Lovata\GoodNews\Classes\Item;

use Cms\Classes\Page as CmsPage;

use Lovata\Toolbox\Classes\Item\ElementItem;
use Lovata\Toolbox\Classes\Helper\PageHelper;

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
     * Returns URL of a category page.
     *
     * @param string $sPageCode
     *
     * @return string
     */
    public function getPageUrl($sPageCode)
    {
        //Get URL params
        $arParamList = $this->getPageParamList($sPageCode);

        //Generate page URL
        $sURL = CmsPage::url($sPageCode, $arParamList);

        return $sURL;
    }

    /**
     * Get URL param list by page code
     * @param string $sPageCode
     * @return array
     */
    public function getPageParamList($sPageCode): array
    {
        //Get URL params for page
        $arParamList = PageHelper::instance()->getUrlParamList($sPageCode, 'ArticleCategoryPage');
        if (empty($arParamList)) {
            return [];
        }

        $arParamList = array_reverse($arParamList);

        //Get slug list
        $arSlugList = $this->getSlugList();
        $arSlugList = array_reverse($arSlugList);

        //Prepare page property list
        $arPagePropertyList = [];
        foreach ($arParamList as $sParamName) {
            $arPagePropertyList[$sParamName] = array_shift($arSlugList);
        }

        return $arPagePropertyList;
    }

    /**
     * Get array with categories slugs
     * @return array
     */
    protected function getSlugList() : array
    {
        $arResult = [$this->slug];

        $obParentCategory = $this->parent;
        while ($obParentCategory->isNotEmpty()) {
            $arResult[] = $obParentCategory->slug;
            $obParentCategory = $obParentCategory->parent;
        }

        return $arResult;
    }

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
