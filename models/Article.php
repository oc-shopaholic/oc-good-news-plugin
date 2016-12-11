<?php namespace Lovata\GoodNews\Models;

use Carbon\Carbon;
use Kharanenka\Helper\CustomValidationMessage;
use Kharanenka\Helper\DataFileModel;
use Kharanenka\Scope\CategoryBelongsTo;
use Kharanenka\Scope\DateField;
use Kharanenka\Scope\PublishField;
use Kharanenka\Helper\CCache;
use Kharanenka\Scope\SlugField;
use Lovata\GoodNews\Plugin;
use October\Rain\Database\Builder;
use October\Rain\Database\Collection;
use System\Classes\PluginManager;
use Model;

/**
 * Class Article
 *
 * @package Lovata\GoodNews\Models
 * @author Andrey Kahranenka, a.khoronenko@lovata.com, LOVATA Group
 * 
 * @mixin Builder
 * @mixin \Eloquent
 * 
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property integer $category_id
 * @property string $preview
 * @property string $content
 * @property boolean $published
 * @property Carbon $published_start
 * @property Carbon $published_stop
 * @property boolean $top
 * @property boolean $hot
 * @property string $author
 * @property string $photo_author
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property Category $category
 * 
 * "Good news for Shopaholic" fields
 * @property Collection|\Lovata\Shopaholic\Models\Product[] $product
 * @property Collection|\Lovata\Shopaholic\Models\Category[] $category_product
 * 
 * @method static Builder|$this whereTitle($value)
 * @method static Builder|$this top()
 * @method static Builder|$this hot()
 */
class Article extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use CustomValidationMessage;
    use PublishField;
    use CategoryBelongsTo;
    use DateField;
    use DataFileModel;
    use SlugField;

    const CACHE_TAG_LIST = 'article-list';
    const CACHE_TAG_ELEMENT = 'article-element';
    
    const DEFAULT_DATE_FORMAT = 'd.m.Y';
    
    /**
     * @var string The database table used by the model.
     */
    public $table = 'lovata_goodnews_articles';
    
    public $rules = [
        'title' => 'required',
        'slug' => 'required|unique:lovata_goodnews_articles',
    ];

    public $customMessages = [];
    public $attributeNames = [];
    public $dates = ['created_at', 'updated_at', 'published_start', 'published_stop'];

    public $belongsTo = [
        'category' => [
            'Lovata\GoodNews\Models\Category',
            'table' => 'lovata_articles_categories',
        ],
    ];

    public $attachOne = [
        'image' => ['System\Models\File'],
        'preview_image' => ['System\Models\File'],
    ];
    
    public function __construct(array $attributes = [])
    {
        $this->setCustomMessage(Plugin::NAME, ['required', 'unique']);
        $this->setCustomAttributeName(Plugin::NAME, ['title', 'slug']);

        if(PluginManager::instance()->hasPlugin('Lovata.GoodNewsShopaholic')) {
            $this->belongsToMany['product'] = \Lovata\GoodNewsShopaholic\Plugin::getProductRelationConfig();
            $this->belongsToMany['category_product'] = \Lovata\GoodNewsShopaholic\Plugin::getCategoryRelationConfig();
        }
        
        parent::__construct($attributes);
    }

    public function afterSave()
    {
        $this->clearCache();
    }

    public function afterDelete()
    {
        $this->clearCache();
    }

    /**
     * Clear cache data
     */
    public function clearCache()
    {
        CCache::clear([Plugin::CACHE_TAG, self::CACHE_TAG_ELEMENT], $this->id);
        CCache::clear([Plugin::CACHE_TAG, self::CACHE_TAG_LIST]);
    }
    
    /**
     * Get top articles
     * @param Builder $obQuery
     * @return Builder;
     */
    protected function scopeTop($obQuery)
    {
        return $obQuery->where('top', true);
    }

    /**
     * Get hot articles
     * @param Builder $obQuery
     * @return Builder;
     */
    protected function scopeHot($obQuery)
    {
        return $obQuery->where('hot', true);
    }

    /**
     * Get element data
     * @param string $sDateFormat
     * @return array
     */
    public function getData($sDateFormat)
    {
        if(empty($sDateFormat)) {
            $sDateFormat = 'd.m.Y';
        }
        
        $arResult = [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'preview' => $this->preview,
            'content' => $this->content,
            'published' => $this->published,
            'published_start' => $this->getDateValue('published_start', $sDateFormat),
            'published_stop' => $this->getDateValue('published_stop', $sDateFormat),
            'top' => $this->top,
            'hot' => $this->hot,
            'image' => $this->getFileData('image'),
            'preview_image' => $this->getFileData('preview_image'),
            'category_id' => $this->category_id,
            'category_name' => $this->getCategoryName(),
            'author' => $this->author,
            'photo_author' => $this->photo_author,
        ];
        
        return $arResult;
    }
    
    /**
     * Get cached data
     * @param $iElementID
     * @param null|Article $obElement
     * @param string $sDateFormat
     * @return array|null
     */
    public static function getCacheData($iElementID, $sDateFormat, $obElement = null)
    {
        if(empty($iElementID)) {
            return [];
        }

        //Get cache data
        $arCacheTags = [Plugin::CACHE_TAG, self::CACHE_TAG_ELEMENT];
        $sCacheKey = $iElementID;

        $arResult = CCache::get($arCacheTags, $sCacheKey);
        if(!empty($arResult)) {
            return $arResult;
        }

        //Get element object
        if(empty($obElement)) {
            $obElement = self::find($iElementID);
        }

        if(empty($obElement)) {
            return [];
        }

        $arResult = $obElement->getData($sDateFormat);

        //Set cache data
        $iCacheTime = Settings::getCacheTime('cache_time_article');
        CCache::put($arCacheTags, $sCacheKey, $arResult, $iCacheTime);

        return $arResult;
    }

    /**
     * Get category name
     * @return null|string
     */
    public function getCategoryName() {
        
        $obCategory = $this->category;
        if(empty($obCategory)) {
            return null;
        }
        
        return $obCategory->name;
    }
}