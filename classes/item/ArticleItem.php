<?php namespace Lovata\GoodNews\Classes\Item;

use Lovata\Toolbox\Classes\Item\ElementItem;

use Lovata\GoodNews\Models\Article;

/**
 * Class ArticleItem
 * @package Lovata\GoodNews\Classes\Item
 * @author  Dmitry Drozd, d.drozd@lovata.com, LOVATA Group
 * @property int                                                     $id
 * @property int                                                     $status_id
 * @property integer                                                 $category_id
 * @property integer                                                 $view_count
 * @property string                                                  $title
 * @property string                                                  $slug
 * @property string                                                  $preview_text
 * @property string                                                  $content
 * @property CategoryItem                                            $category
 * @property \Carbon\Carbon                                          $published_start
 * @property \Carbon\Carbon                                          $published_stop
 * @property \Carbon\Carbon                                          $created_at
 * @property \Carbon\Carbon                                          $updated_at
 * @property \System\Models\File                                     $preview_image
 * @property \October\Rain\Database\Collection|\System\Models\File[] $images
 */
class ArticleItem extends ElementItem
{
    const MODEL_CLASS = Article::class;

    /** @var Article */
    protected $obElement = null;

    public $arRelationList = [
        'category' => [
            'class' => CategoryItem::class,
            'field' => 'category_id',
        ],
    ];

    /**
     * Set element data from model object
     * @return array
     */
    protected function getElementData()
    {
        $arResult = [
            'category_id_list' => $this->obElement->lists('id'),
        ];

        return $arResult;
    }

    /**
     * Get Breadcrumbs
     * @return array
     */
    public function getBreadcrumbs()
    {
        $arResult[] = [
            'id'     => $this->id,
            'name'   => $this->title,
            'slug'   => $this->slug,
            'active' => true,
            'page'   => 'goodnews_article',
        ];

        if ($this->category_id) {
            $obCategory = $this->category;
            $obCategory->getBreadcrumbs($arResult, true);
        }

        return array_reverse($arResult);
    }

    /**
     * check category for activity
     * @return bool
     */
    public function isActiveCategory()
    {
        $obCategory = $this->category;

        if (!empty($obCategory) && $obCategory->active) {
            return true;
        }
        return false;
    }
}