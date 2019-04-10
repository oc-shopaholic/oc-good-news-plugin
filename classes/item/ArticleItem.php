<?php namespace Lovata\GoodNews\Classes\Item;

use Lovata\Toolbox\Classes\Item\ElementItem;

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

    public $arRelationList = [
        'category' => [
            'class' => CategoryItem::class,
            'field' => 'category_id',
        ],
    ];
}
