<?php namespace Lovata\GoodNews\Classes\Collection;

use Site;
use Lovata\Toolbox\Classes\Collection\ElementCollection;

use Lovata\GoodNews\Classes\Item\ArticleItem;
use Lovata\GoodNews\Classes\Item\CategoryItem;
use Lovata\GoodNews\Classes\Store\ArticleListStore;

/**
 * Class ArticleCollection
 * @package Lovata\GoodNews\Classes\Item
 * @author Andrey Kharanenka, a.khoronenko@lovata.com, LOVATA Group
 */
class ArticleCollection extends ElementCollection
{
    const ITEM_CLASS = ArticleItem::class;

    /**
     * Apply filter by active article list
     * @return $this
     */
    public function published()
    {
        $arResultIDList = ArticleListStore::instance()->published->get();

        return $this->intersect($arResultIDList);
    }

    /**
     * Sort list by
     * @param string $sSorting
     * @return $this
     */
    public function sort($sSorting)
    {
        $arResultIDList = ArticleListStore::instance()->sorting->get($sSorting);

        return $this->applySorting($arResultIDList);
    }

    /**
     * Filter article list by category ID
     * @param int|array $arCategoryIDList
     * @param bool $bWithChildren
     * @return $this
     */
    public function category($arCategoryIDList, $bWithChildren = false)
    {
        if (!is_array($arCategoryIDList)) {
            $arCategoryIDList = [$arCategoryIDList];
        }

        $arResultIDList = [];
        foreach ($arCategoryIDList as $iCategoryID) {
            $arResultIDList = array_merge($arResultIDList, (array) ArticleListStore::instance()->category->get($iCategoryID));
            if ($bWithChildren) {
                $arResultIDList = array_merge($arResultIDList, (array) $this->getIDListChildrenCategory($iCategoryID));
            }
        }

        return $this->intersect($arResultIDList);
    }

    /**
     * Apply filter by relation with site field
     * @return $this
     */
    public function site($iSiteID = null): self
    {
        $iSiteID = empty($iSiteID) ? Site::getSiteIdFromContext() : $iSiteID;
        $arResultIDList = ArticleListStore::instance()->site->get($iSiteID);

        return $this->intersect($arResultIDList);
    }

    /**
     * Get article ID list for children categories
     * @param int $iCategoryID
     * @return array
     */
    protected function getIDListChildrenCategory($iCategoryID) : array
    {
        //Get category item
        $obCategoryItem = CategoryItem::make($iCategoryID);
        if ($obCategoryItem->isEmpty() || $obCategoryItem->children->isEmpty()) {
            return [];
        }

        $arResultIDList = [];
        foreach ($obCategoryItem->children as $obChildCategoryItem) {
            $arResultIDList = array_merge($arResultIDList, (array) ArticleListStore::instance()->category->get($obChildCategoryItem->id));
            $arResultIDList = array_merge($arResultIDList, $this->getIDListChildrenCategory($obChildCategoryItem->id));
        }

        return $arResultIDList;
    }
}
