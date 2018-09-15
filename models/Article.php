<?php namespace Lovata\GoodNews\Models;

use Lang;
use Model;
use October\Rain\Argon\Argon;
use October\Rain\Database\Traits\Validation;
use Lovata\Toolbox\Traits\Helpers\TraitCached;

use Kharanenka\Helper\DataFileModel;
use Kharanenka\Scope\CategoryBelongsTo;
use Kharanenka\Scope\DateField;
use Kharanenka\Scope\SlugField;

/**
 * Class Article
 *
 * @package Lovata\GoodNews\Models
 * @author Andrey Kahranenka, a.khoronenko@lovata.com, LOVATA Group
 *
 * @mixin \October\Rain\Database\Builder
 * @mixin \Eloquent
 *
 * @property int $id
 * @property int $status_id
 * @property string $title
 * @property string $slug
 * @property integer $category_id
 * @property integer $view_count
 * @property string $preview_text
 * @property string $content
 * @property \October\Rain\Argon\Argon $published_start
 * @property \October\Rain\Argon\Argon $published_stop
 * @property \October\Rain\Argon\Argon $created_at
 * @property \October\Rain\Argon\Argon $updated_at
 *
 * @property \System\Models\File $preview_image
 * @property \October\Rain\Database\Collection|\System\Models\File[] $images
 *
 * @property Category $category
 * @method static \October\Rain\Database\Relations\BelongsTo|Category category()
 *
 * @method static $this getPublished()
 * @method static $this getByStatus(int $iStatusID)
 * @method static $this getByStatusIn(array $arStatusID)
 */
class Article extends Model
{
    use Validation;
    use DateField;
    use DataFileModel;
    use SlugField;
    use CategoryBelongsTo;
    use TraitCached;

    const STATUS_NEW = 1;
    const STATUS_IN_WORK = 2;
    const STATUS_REVIEW = 3;
    const STATUS_PUBLISHED = 4;

    public $table = 'lovata_good_news_articles';

    public $rules = [
        'title' => 'required',
        'slug'  => 'required|unique:lovata_good_news_articles',
    ];

    public $attributeNames = [
        'lovata.toolbox::lang.field.title',
        'lovata.toolbox::lang.field.slug',
    ];

    public $dates = ['created_at', 'updated_at', 'published_start', 'published_stop'];

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
        'published_start',
        'published_stop',
        'view_count',
    ];

    public function beforeSave()
    {
        if($this->status_id == self::STATUS_PUBLISHED && empty($this->published_start)) {
            $this->published_start = Argon::now();
        }
    }

    /**
     * Get element by status_id value
     * @param Article $obQuery
     * @param string $sData
     * @return Article
     */
    public function scopeGetByStatus($obQuery, $sData)
    {
        if(!empty($sData)) {
            $obQuery->where('status_id', $sData);
        }

        return $obQuery;
    }

    /**
     * Get element by status_id value
     * @param Article $obQuery
     * @param array $arData
     * @return Article
     */
    public function scopeGetByStatusIn($obQuery, $arData)
    {
        if(!empty($arData)) {
            $obQuery->whereIn('status_id', $arData);
        }

        return $obQuery;
    }

    /**
     * Get published elements
     * @param Article $obQuery
     * @return Article
     */
    public function scopeGetPublished($obQuery)
    {
        $sDateNow = Argon::now()->format('Y-m-d H:i:s');

        return $obQuery->where('published_start', '<=', $sDateNow)
            ->where(function($obQuery) use ($sDateNow) {
                /** @var Article $obQuery */
                $obQuery->whereNull('published_stop')->orWhere('published_stop', '>', $sDateNow);
            });
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