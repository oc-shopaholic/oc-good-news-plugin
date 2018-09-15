<?php namespace Lovata\GoodNews\Classes\Item;

use Lovata\GoodNews\Models\Article;
use Lovata\Toolbox\Classes\Item\ElementItem;
use Lovata\GoodNews\Plugin;

/**
 * Class ArticleItem
 *
 * @package Lovata\GoodNews\Classes\Item
 * @author Dmitry Drozd, d.drozd@lovata.com, LOVATA Group
 *
 * @property int $id
 * @property int $status_id
 * @property integer $category_id
 * @property integer $view_count

 * @property string $title
 * @property string $slug
 * @property string $preview_text
 * @property string $content
 *
 * @property CategoryItem $category
 *
 * @property \Carbon\Carbon $published_start
 * @property \Carbon\Carbon $published_stop
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \System\Models\File $preview_image
 * @property \October\Rain\Database\Collection|\System\Models\File[] $images
 * 
 */
class ArticleItem extends ElementItem
{
    const CACHE_TAG_ELEMENT = 'good-news-article-element';

    /** @var Article */
    protected $obElement = null;

    public $arRelationList = [
        'category' => [
            'class' => CategoryItem::class,
            'field' => 'category_id',
        ],
    ];

    /**
     * Set element object
     */
    protected function setElementObject()
    {
        if(!empty($this->obElement) && ! $this->obElement instanceof Article) {
            $this->obElement = null;
        }

        if(!empty($this->obElement) || empty($this->iElementID)) {
            return;
        }

        $this->obElement = Article::getByStatus(Article::STATUS_PUBLISHED)
            ->getPublished()
            ->find($this->iElementID);
    }

    /**
     * Get cache tag array for model
     * @return array
     */
    protected static function getCacheTag()
    {
        return [Plugin::CACHE_TAG, self::CACHE_TAG_ELEMENT];
    }

    /**
     * Get element data
     * @return array
     */
    public function getElementData()
    {
        $arResult = [
            'id'              => $this->obElement->id,
            'status_id'       => $this->obElement->status_id,
            'category_id'     => $this->obElement->category_id,
            'view_count'      => $this->obElement->view_count,
            'title'           => $this->obElement->title,
            'slug'            => $this->obElement->slug,
            'preview_text'    => $this->obElement->preview_text,
            'content'         => $this->obElement->content,
            'created_at'      => $this->obElement->created_at,
            'updated_at'      => $this->obElement->updated_at,
            'published_start' => $this->obElement->getDateValue('published_start', 'Y-m-d H:i:s'),
            'published_stop'  => $this->obElement->getDateValue('published_stop', 'Y-m-d H:i:s'),
            'image'           => $this->obElement->getFileListData('images'),
            'preview_image'   => $this->obElement->getFileData('preview_image'),
        ];

        return $arResult;
    }

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
}