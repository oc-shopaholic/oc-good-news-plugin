<?php namespace Lovata\GoodNews\Models;

use Kharanenka\Helper\CustomValidationMessage;
use Kharanenka\Scope\ActiveField;
use Kharanenka\Scope\SlugField;
use Kharanenka\Helper\CCache;
use Lovata\GoodNews\Plugin;
use Model;
use October\Rain\Database\Builder;
use October\Rain\Database\Collection;

/**
 * Class Category
 * @package Lovata\GoodNews\Models
 * @author Andrey Kahranenka, a.khoronenko@lovata.com, LOVATA Group
 * 
 * @mixin Builder
 * @mixin \Eloquent
 * 
 * @property integer $id
 * @property boolean $active
 * @property string $name
 * @property string $slug
 * @property string $code
 * @property string $description
 * @property string $created_at
 * @property string $updated_at
 */
class Category extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\NestedTree;
    use CustomValidationMessage;
    use ActiveField;
    use SlugField;

    const CACHE_TAG_ELEMENT = 'category-element';
    const CACHE_TAG_LIST = 'category-list';
    const CACHE_TAG_LIST_MENU = 'category-list-menu';

    public $table = 'lovata_goodnews_categories';
    
    public $rules = [
        'name' => 'required',
        'slug' => 'required|unique:lovata_goodnews_categories',
    ];
    
    public $customMessages = [];
    public $attributeNames = [];
    protected $dates = ['created_at', 'updated_at'];
    protected $casts =[
        'active' => 'boolean',
    ];

    public function __construct(array $attributes = []) {
        
        $this->setCustomMessage(Plugin::NAME, ['required', 'unique']);
        $this->setCustomAttributeName(Plugin::NAME, ['name', 'slug']);
        
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
        CCache::clear([Plugin::CACHE_TAG, self::CACHE_TAG_LIST_MENU]);
    }
    
    /**
     * Get category data
     * @return array|null|string
     */
    public function getData() {

        $arResult = [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'code' => $this->code,
            'description' => $this->description,
            'children' => $this->getChildrenCategories(),
        ];

        return $arResult;
    }

    /**
     * Get children categories
     * @return array
     */
    protected function getChildrenCategories() {

        $arChildrenData = [];

        //Get children category
        /** @var Collection $arChildrenCategory */
        $arChildrenCategory = $this->children;
        if($arChildrenCategory->isEmpty()) {
            return $arChildrenData;
        }

        /** @var Category $obChildrenCategory */
        foreach($arChildrenCategory as $obChildrenCategory) {
            //Get category data
            $arChildrenData[$obChildrenCategory->id] = $obChildrenCategory->getData();
        }

        return $arChildrenData;
    }

    /**
     * Get category data from cache
     * @param int $iCategoryID
     * @param null|Category $obCategory
     * @return array|null|string
     */
    public static function getCacheData($iCategoryID, $obCategory = null) {

        //Get cache data
        $arCacheTags = [Plugin::CACHE_TAG, self::CACHE_TAG_ELEMENT];
        $sCacheKey = $iCategoryID;

        $arResult = CCache::get($arCacheTags, $sCacheKey);
        if(!empty($arResult)) {
            return $arResult;
        }

        //Get category object
        if(empty($obCategory)) {
            $obCategory = self::find($iCategoryID);
        }

        if(empty($obCategory)) {
            return [];
        }

        $arResult = $obCategory->getData();

        //Set cache data
        $iCacheTime = Settings::getCacheTime('cache_time_category');
        CCache::put($arCacheTags, $sCacheKey, $arResult, $iCacheTime);

        return $arResult;
    }
}