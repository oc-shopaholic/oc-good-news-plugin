<?php namespace Lovata\GoodNews\Classes\Collection;

use Lovata\Toolbox\Classes\Collection\ElementCollection;

use Lovata\GoodNews\Models\Category;
use Lovata\GoodNews\Classes\Item\CategoryItem;
use Lovata\GoodNews\Classes\Store\CategoryListStore;

/**
 * Class CategoryCollection
 * @package Lovata\GoodNews\Classes\Item
 * @author Dmitry Drozd, d.drozd@lovata.com, LOVATA Group
 */
class CategoryCollection extends ElementCollection
{
    /** @var CategoryListStore */
    protected $obCategoryListStore;

    /**
     * StickerCollection constructor.
     * @param CategoryListStore $obCategoryListStore
     */
    public function __construct(CategoryListStore $obCategoryListStore)
    {
        $this->obCategoryListStore = $obCategoryListStore;
        parent::__construct();
    }


    /**
     * Make element item
     * @param int $iElementID
     * @param Category $obElement
     *
     * @return CategoryItem
     */
    protected function makeItem($iElementID, $obElement = null)
    {
        return CategoryItem::make($iElementID, $obElement);
    }

    /**
     * Sort list
     * @return $this
     */
    public function getTree()
    {
        if(!$this->isClear() && $this->isEmpty()) {
            return $this;
        }

        //Get sorting list
        $arElementIDList = $this->obCategoryListStore->getTree();
        if(empty($arElementIDList)) {
            return $this->clear();
        }

        if($this->isClear()) {
            $this->arElementIDList = $arElementIDList;
            return $this;
        }

        $this->arElementIDList = array_intersect($arElementIDList, $this->arElementIDList);
        return $this->returnThis();
    }
}

