<?php namespace Lovata\GoodNews\Models;

use Lang;
use Model;
use October\Rain\Argon\Argon;
use October\Rain\Database\Traits\Validation;
use October\Rain\Database\Traits\Sluggable;
use System\Models\SiteDefinition;

use Kharanenka\Helper\DataFileModel;
use Kharanenka\Scope\CategoryBelongsTo;
use Kharanenka\Scope\DateField;
use Kharanenka\Scope\SlugField;

use Lovata\Toolbox\Traits\Models\MultisiteHelperTrait;
use Lovata\Toolbox\Traits\Helpers\TraitCached;
use October\Rain\Database\Traits\SoftDelete;

/**
 * Class Article
 *
 * @package Lovata\GoodNews\Models
 * @author  Andrey Kahranenka, a.khoronenko@lovata.com, LOVATA Group
 *
 * @mixin \October\Rain\Database\Builder
 * @mixin \Eloquent
 *
 * @property int                                                     $id
 * @property int                                                     $status_id
 * @property string                                                  $title
 * @property string                                                  $slug
 * @property integer                                                 $category_id
 * @property integer                                                 $view_count
 * @property string                                                  $preview_text
 * @property string                                                  $content
 * @property string                                                  $seo_title
 * @property string                                                  $seo_keywords
 * @property string                                                  $seo_description
 * @property \October\Rain\Argon\Argon                               $published_start
 * @property \October\Rain\Argon\Argon                               $published_stop
 * @property \October\Rain\Argon\Argon                               $created_at
 * @property \October\Rain\Argon\Argon                               $updated_at
 *
 * @property \System\Models\File                                     $preview_image
 * @property \October\Rain\Database\Collection|\System\Models\File[] $images
 *
 * @property \October\Rain\Database\Collection|SiteDefinition[]       $site
 * @method \October\Rain\Database\Relations\MorphToMany|SiteDefinition site()
 *
 * @property Category                                                $category
 * @method static \October\Rain\Database\Relations\BelongsTo|Category category()
 *
 * @method static $this getByStatus($sData)
 * @method static $this getByStatusIn($arData)
 * @method static $this getPublished()
 *
 */
class Article extends Model
{
    use Validation;
    use Sluggable;
    use DateField;
    use DataFileModel;
    use SlugField;
    use CategoryBelongsTo;
    use TraitCached;
    use SoftDelete;
    use MultisiteHelperTrait;

    const STATUS_NEW = 1;
    const STATUS_IN_WORK = 2;
    const STATUS_REVIEW = 3;
    const STATUS_PUBLISHED = 4;

    public $table = 'lovata_good_news_articles';
    /** @var array */
    public $implement = ['@RainLab.Translate.Behaviors.TranslatableModel'];
    /** @var array */
    public $translatable = [
        'title',
        'preview_text',
        'content',
        'seo_title',
        'seo_keywords',
        'seo_description',
    ];

    public $rules = [
        'title'           => 'required',
        'published_start' => 'required',
        'slug'            => 'required|unique:lovata_good_news_articles',
    ];

    public $attributeNames = [
        'title'           => 'lovata.toolbox::lang.field.title',
        'slug'            => 'lovata.toolbox::lang.field.slug',
        'published_start' => 'lovata.goodnews::lang.field.published_start',
    ];

    protected $slugs = ['slug' => 'name'];

    public $dates = ['created_at', 'updated_at', 'published_start', 'published_stop', 'deleted_at'];

    public $belongsToMany = [
        'site'                => [
            SiteDefinition::class,
            'table'    => 'lovata_goodnews_article_site_relation',
            'otherKey' => 'site_id',
        ],
    ];

    public $belongsTo = [
        'category' => [
            Category::class,
        ],
    ];

    public $attachOne = [
        'preview_image' => 'System\Models\File'
    ];

    public $attachMany = [
        'images' => 'System\Models\File'
    ];

    public $fillable = [
        'status_id',
        'category_id',
        'title',
        'slug',
        'preview_text',
        'content',
        'seo_title',
        'seo_keywords',
        'seo_description',
        'published_start',
        'published_stop',
        'view_count',
    ];

    public $cached = [
        'id',
        'status_id',
        'category_id',
        'title',
        'slug',
        'preview_text',
        'content',
        'seo_title',
        'seo_keywords',
        'seo_description',
        'published_start',
        'published_stop',
        'view_count',
        'preview_image',
        'images',
    ];

    /**
     * Get element by status_id value
     * @param \Illuminate\Database\Eloquent\Builder|\October\Rain\Database\Builder $obQuery
     * @param string                                                               $sData
     * @return \Illuminate\Database\Eloquent\Builder|\October\Rain\Database\Builder
     */
    public function scopeGetByStatus($obQuery, $sData)
    {
        if (!empty($sData)) {
            $obQuery->where('status_id', $sData);
        }

        return $obQuery;
    }

    /**
     * Get element by status_id value
     * @param \Illuminate\Database\Eloquent\Builder|\October\Rain\Database\Builder $obQuery
     * @param array                                                                $arData
     * @return \Illuminate\Database\Eloquent\Builder|\October\Rain\Database\Builder
     */
    public function scopeGetByStatusIn($obQuery, $arData)
    {
        if (!empty($arData)) {
            $obQuery->whereIn('status_id', $arData);
        }

        return $obQuery;
    }

    /**
     * Get published elements
     * @param \Illuminate\Database\Eloquent\Builder|\October\Rain\Database\Builder $obQuery
     * @return \Illuminate\Database\Eloquent\Builder|\October\Rain\Database\Builder
     */
    public function scopeGetPublished($obQuery)
    {
        $sDateNow = Argon::now()->format('Y-m-d H:i:s');

        return $obQuery->where('published_start', '<=', $sDateNow)
            ->where(function ($obQuery) use ($sDateNow) {
                /** @var Article $obQuery */
                $obQuery->whereNull('published_stop')->orWhere('published_stop', '>', $sDateNow);
            });
    }

    /**
     * Before validate event handler
     */
    public function beforeValidate()
    {
        if (empty($this->slug)) {
            $this->slugAttributes();
        }
    }

    /**
     * Get status_id options
     * @return array
     */
    public function getStatusIdOptions()
    {
        return [
            self::STATUS_NEW       => Lang::get('lovata.goodnews::lang.status.' . self::STATUS_NEW),
            self::STATUS_IN_WORK   => Lang::get('lovata.goodnews::lang.status.' . self::STATUS_IN_WORK),
            self::STATUS_PUBLISHED => Lang::get('lovata.goodnews::lang.status.' . self::STATUS_PUBLISHED),
        ];
    }
}
