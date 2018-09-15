<?php namespace Lovata\GoodNews\Classes\Collection;

use Lovata\Toolbox\Classes\Collection\ElementCollection;

use Lovata\GoodNews\Models\Article;
use Lovata\GoodNews\Classes\Item\ArticleItem;
use Lovata\GoodNews\Classes\Store\ArticleListStore;

/**
 * Class ArticleCollection
 * @package Lovata\GoodNews\Classes\Item
 * @author Dmitry Drozd, d.drozd@lovata.com, LOVATA Group
 */
class ArticleCollection extends ElementCollection
{
    /** @var ArticleListStore */
    protected $obArticleListStore;

    /**
     * StickerCollection constructor.
     * @param ArticleListStore $obArticleListStore
     */
    public function __construct(ArticleListStore $obArticleListStore)
    {
        $this->obArticleListStore = $obArticleListStore;
        parent::__construct();
    }


    /**
     * Make element item
     * @param int $iElementID
     * @param Article $obElement
     *
     * @return ArticleItem
     */
    protected function makeItem($iElementID, $obElement = null)
    {
        return ArticleItem::make($iElementID, $obElement);
    }

    /**
     * Sort list
     * @param string $sSorting
     * @return $this
     */
    public function sort($sSorting)
    {
        if(!$this->isClear() && $this->isEmpty()) {
            return $this;
        }

        //Get sorting list
        $arElementIDList = $this->obArticleListStore->getBySorting($sSorting);
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

    /**
     * Apply filter by active element list
     * @return $this
     */
    public function published()
    {
        $arElementIDList = $this->obArticleListStore->getPublishedList();
        return $this->intersect($arElementIDList);
    }

    /**
     * Apply filter by category element list
     * @param int $iCategoryID
     * @return $this
     */
    public function category($iCategoryID)
    {
        $arElementIDList = $this->obArticleListStore->getByCategory($iCategoryID);
        return $this->intersect($arElementIDList);
    }

    /**
     * Apply filter by status element list
     * @param int $iStatusID
     * @return $this
     */
    public function status($iStatusID)
    {
        $arElementIDList = $this->obArticleListStore->getByStatus($iStatusID);
        return $this->intersect($arElementIDList);
    }
}

