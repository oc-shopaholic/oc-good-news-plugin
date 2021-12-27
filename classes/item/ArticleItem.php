<?php namespace Lovata\GoodNews\Classes\Item;

use Cms\Classes\Page as CmsPage;

use Lovata\Toolbox\Classes\Item\ElementItem;
use Lovata\Toolbox\Classes\Helper\PageHelper;

use Lovata\GoodNews\Models\Article;

/**
 * Class ArticleItem
 * @package Lovata\GoodNews\Classes\Item
 * @author  Andrey Kharanenka, a.khoronenko@lovata.com, LOVATA Group
 * @property int                       $id
 * @property int                       $status_id
 * @property integer                   $category_id
 * @property integer                   $view_count
 * @property string                    $title
 * @property string                    $slug
 * @property string                    $preview_text
 * @property string                    $content
 * @property string                    $seo_title
 * @property string                    $seo_keywords
 * @property string                    $seo_description
 * @property CategoryItem              $category
 * @property \October\Rain\Argon\Argon $published_start
 * @property \October\Rain\Argon\Argon $published_stop
 * @property \October\Rain\Argon\Argon $created_at
 * @property \October\Rain\Argon\Argon $updated_at
 * @property \System\Models\File       $preview_image
 * @property \System\Models\File[]     $images
 */
class ArticleItem extends ElementItem
{
    const MODEL_CLASS = Article::class;

    public static $arQueryWith = [
        'preview_image',
        'images',
    ];

    public $arRelationList = [
        'category' => [
            'class' => CategoryItem::class,
            'field' => 'category_id',
        ],
    ];

    /**
     * Returns URL of a category page.
     *
     * @param string $sPageCode
     * @param array  $arRemoveParamList
     *
     * @return string
     */
    public function getPageUrl($sPageCode = 'article', $arRemoveParamList = [])
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
    public function getPageParamList($sPageCode, $arRemoveParamList = []): array
    {
        $arResult = [];
        $arPageParamList = [];
        if (!empty($arRemoveParamList)) {
            foreach ($arRemoveParamList as $sParamName) {
                $arResult[$sParamName] = null;
            }
        }

        //Get URL params for categories
        $aCategoryParamList = $this->category->getPageParamList($sPageCode);

        //Get URL params for page
        $arParamList = (array) PageHelper::instance()->getUrlParamList($sPageCode, 'ArticlePage');
        if (!empty($arParamList)) {
            $sPageParam = array_shift($arParamList);
            $arPageParamList[$sPageParam] = $this->slug;
        }

        $arResult = array_merge($arResult, $aCategoryParamList, $arPageParamList);

        return $arResult;
    }
}
