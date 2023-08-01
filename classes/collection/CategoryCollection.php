<?php namespace Lovata\GoodNews\Classes\Collection;

use Site;
use Lovata\Toolbox\Classes\Collection\ElementCollection;

use Lovata\GoodNews\Classes\Item\CategoryItem;
use Lovata\GoodNews\Classes\Store\CategoryListStore;

/**
 * Class CategoryCollection
 * @package Lovata\GoodNews\Classes\Item
 * @author Andrey Kharanenka, a.khoronenko@lovata.com, LOVATA Group
 */
class CategoryCollection extends ElementCollection
{
    const ITEM_CLASS = CategoryItem::class;

    /**
     * Set to element ID list top level category ID list
     * @return CategoryCollection
     */
    public function tree()
    {
        $arResultIDList = CategoryListStore::instance()->top_level->get();

        return $this->applySorting($arResultIDList);
    }

    /**
     * Apply filter by active field
     * @return $this
     */
    public function active()
    {
        $arResultIDList = CategoryListStore::instance()->active->get();

        return $this->intersect($arResultIDList);
    }

    /**
     * Apply filter by relation with site field
     * @return $this
     */
    public function site($iSiteID = null): self
    {
        $iSiteID = empty($iSiteID) ? Site::getSiteIdFromContext() : $iSiteID;
        $arResultIDList = CategoryListStore::instance()->site->get($iSiteID);

        return $this->intersect($arResultIDList);
    }
}
