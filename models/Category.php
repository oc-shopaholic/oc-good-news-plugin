<?php namespace Lovata\GoodNews\Models;

use Model;
use October\Rain\Database\Traits\Validation;
use October\Rain\Database\Traits\NestedTree;
use October\Rain\Database\Traits\Sluggable;
use System\Models\SiteDefinition;

use Lovata\Toolbox\Traits\Models\MultisiteHelperTrait;
use Lovata\Toolbox\Traits\Helpers\TraitCached;

use Kharanenka\Helper\DataFileModel;
use Kharanenka\Scope\ActiveField;
use Kharanenka\Scope\NameField;
use Kharanenka\Scope\SlugField;
use Kharanenka\Scope\CodeField;

/**
 * Class Category
 * @package Lovata\GoodNews\Models
 * @author  Andrey Kahranenka, a.khoronenko@lovata.com, LOVATA Group
 *
 * @mixin \October\Rain\Database\Builder
 * @mixin \Eloquent
 *
 * @property integer                                                 $id
 * @property boolean                                                 $active
 * @property string                                                  $name
 * @property string                                                  $slug
 * @property string                                                  $code
 * @property string                                                  $preview_text
 * @property string                                                  $description
 * @property string                                                  $seo_title
 * @property string                                                  $seo_keywords
 * @property string                                                  $seo_description
 * @property int                                                     $parent_id
 * @property int                                                     $nest_left
 * @property int                                                     $nest_right
 * @property int                                                     $nest_depth
 * @property \October\Rain\Argon\Argon                               $created_at
 * @property \October\Rain\Argon\Argon                               $updated_at
 *
 * @property \System\Models\File                                     $preview_image
 * @property \October\Rain\Database\Collection|\System\Models\File[] $images
 *
 * @property \October\Rain\Database\Collection|SiteDefinition[]       $site
 * @method \October\Rain\Database\Relations\MorphToMany|SiteDefinition site()
 *
 * @property Category                                                $parent
 * @property \October\Rain\Database\Collection|Category[]            $children
 *
 * @method static \October\Rain\Database\Relations\HasMany|Category children()
 */
class Category extends Model
{
    use Validation;
    use Sluggable;
    use NestedTree;
    use ActiveField;
    use SlugField;
    use NameField;
    use DataFileModel;
    use CodeField;
    use TraitCached;
    use MultisiteHelperTrait;

    public $table = 'lovata_good_news_categories';
    /** @var array */
    public $implement = ['@RainLab.Translate.Behaviors.TranslatableModel'];
    /** @var array */
    public $translatable = [
        'name',
        'preview_text',
        'description',
        'seo_title',
        'seo_keywords',
        'seo_description',
    ];
    public $rules = [
        'name' => 'required',
        'slug' => 'required|unique:lovata_good_news_categories',
    ];

    public $attributeNames = [
        'lovata.toolbox::lang.field.name',
        'lovata.toolbox::lang.field.slug',
    ];

    protected $slugs = ['slug' => 'name'];

    protected $dates = ['created_at', 'updated_at'];

    public $attachOne = [
        'preview_image' => 'System\Models\File'
    ];

    public $attachMany = [
        'images' => 'System\Models\File'
    ];

    public $belongsTo = [];
    public $hasMany = ['article' => Article::class];

    public $belongsToMany = [
        'site'                => [
            SiteDefinition::class,
            'table'    => 'lovata_goodnews_category_site_relation',
            'otherKey' => 'site_id',
        ],
    ];

    public $morphMany = [];

    public $fillable = [
        'name',
        'slug',
        'active',
        'code',
        'preview_text',
        'description',
        'seo_title',
        'seo_keywords',
        'seo_description',
    ];

    public $cached = [
        'id',
        'name',
        'slug',
        'active',
        'code',
        'preview_text',
        'description',
        'seo_title',
        'seo_keywords',
        'seo_description',
        'nest_depth',
        'parent_id',
        'preview_image',
        'images',
    ];

    /**
     * Before validate event handler
     */
    public function beforeValidate()
    {
        if (empty($this->slug)) {
            $this->slugAttributes();
        }
    }
}
