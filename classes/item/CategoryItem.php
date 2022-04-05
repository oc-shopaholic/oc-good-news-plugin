<?php namespace Lovata\GoodNews\Classes\Item;

use Cms\Classes\Page as CmsPage;

use Kharanenka\Helper\CCache;

use Lovata\Toolbox\Classes\Item\ItemStorage;
use Lovata\Toolbox\Classes\Item\ElementItem;
use Lovata\Toolbox\Classes\Helper\PageHelper;

use Lovata\GoodNews\Models\Category;
use Lovata\GoodNews\Classes\Collection\ArticleCollection;
use Lovata\GoodNews\Classes\Collection\CategoryCollection;

/**
 * Class Category
 * @package Lovata\GoodNews\Classes\Item
 * @author  Andrey Kharanenka, a.khoronenko@lovata.com, LOVATA Group
 * @property int                               $id
 * @property string                            $name
 * @property string                            $slug
 * @property string                            $code
 * @property int                               $article_count
 * @property int                               $nest_depth
 * @property int                               $parent_id
 * @property string                            $preview_text
 * @property array                             $preview_image
 * @property string                            $description
 * @property string                            $seo_title
 * @property string                            $seo_keywords
 * @property string                            $seo_description
 * @property array                             $images
 * @property CategoryItem                      $parent
 * @property array                             $children_id_list
 * @property CategoryCollection|CategoryItem[] $children
 */
class CategoryItem extends ElementItem
{
    const MODEL_CLASS = Category::class;

    public static $arQueryWith = [
        'preview_image',
        'images',
    ];

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
     * Clear article count cache
     */
    public function clearArticleCount()
    {
        $arCacheTag = [static::class];
        $sCacheKey = 'article_count_'.$this->id;

        CCache::clear($arCacheTag, $sCacheKey);
        ItemStorage::clear(static::class, $this->id);

        $obParentItem = $this->parent;
        if ($obParentItem->isEmpty()) {
            return;
        }

        $obParentItem->clearArticleCount();
    }

    /**
     * Returns URL of a category page.
     *
     * @param string $sPageCode
     * @param array  $arRemoveParamList
     *
     * @return string
     */
    public function getPageUrl($sPageCode, $arRemoveParamList = [])
    {
        //Get URL params
        $arParamList = $this->getPageParamList($sPageCode, $arRemoveParamList);

        //Generate page URL
        $sURL = CmsPage::url($sPageCode, $arParamList);

        return $sURL;
    }

    /**
     * Get URL param list by page code
     * @param string $sPageCode
     * @param array  $arRemoveParamList
     * @return array
     */
    public function getPageParamList($sPageCode, $arRemoveParamList = []) : array
    {
        $arResult = [];
        if (!empty($arRemoveParamList)) {
            foreach ($arRemoveParamList as $sParamName) {
                $arResult[$sParamName] = null;
            }
        }

        //Get all slug params
        $arParamList = PageHelper::instance()->getUrlParamList($sPageCode, null);
        if (!empty($arParamList)) {
            foreach ($arParamList as $sParamName) {
                $arResult[$sParamName] = null;
            }
        }

        //Get URL params for page
        $arParamList = PageHelper::instance()->getUrlParamList($sPageCode, 'ArticleCategoryPage');
        if (empty($arParamList)) {
            return [];
        }

        //Get slug list
        $arSlugList = $this->getSlugList();

        $arWildcardParamList = PageHelper::instance()->getUrlParamList($sPageCode, 'ArticleCategoryPage', 'slug', true);
        if (!empty($arWildcardParamList)) {
            $arSlugList = array_reverse($arSlugList);
            $arResult[array_shift($arWildcardParamList)] = implode('/', $arSlugList);

            return $arResult;
        } elseif (count($arParamList) == 1) {
            $sParamName = array_shift($arParamList);
            $arResult[$sParamName] = array_shift($arSlugList);

            return $arResult;
        }

        //Prepare page property list
        $arSlugList = array_reverse($arSlugList);
        $arParamList = array_reverse($arParamList);
        foreach ($arParamList as $sParamName) {
            if (!empty($arSlugList)) {
                $arResult[$sParamName] = array_shift($arSlugList);
            }
        }

        return $arResult;
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
            ->pluck('id')->all();

        return $arResult;
    }

    /**
     * Get article count for category
     * @return int
     */
    protected function getArticleCountAttribute()
    {
        $iArticleCount = $this->getAttribute('article_count');
        if ($iArticleCount !== null) {
            return $iArticleCount;
        }

        //Get article count from cache
        $arCacheTag = [static::class];
        $sCacheKey = 'article_count_'.$this->id;

        $iArticleCount = CCache::get($arCacheTag, $sCacheKey);
        if ($iArticleCount !== null) {
            return $iArticleCount;
        }

        //Calculate article count from child categories
        $iArticleCount = 0;
        $obChildCategoryCollect = $this->children;
        if ($obChildCategoryCollect->isNotEmpty()) {
            /** @var CategoryItem $obChildCategoryItem */
            foreach ($obChildCategoryCollect as $obChildCategoryItem) {
                if ($obChildCategoryItem->isEmpty()) {
                    continue;
                }

                $iArticleCount += $obChildCategoryItem->article_count;
            }
        }

        $iArticleCount += ArticleCollection::make()->published()->category($this->id)->count();

        CCache::forever($arCacheTag, $sCacheKey, $iArticleCount);
        $this->setAttribute('article_count', $iArticleCount);

        return $iArticleCount;
    }
}
