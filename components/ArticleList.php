<?php namespace Lovata\GoodNews\Components;

use Lang;
use Input;
use Lovata\GoodNews\Models\Settings;
use Response;
use Cms\Classes\ComponentBase;
use Kharanenka\Helper\CCache;
use Kharanenka\Helper\Pagination;
use Lovata\GoodNews\Models\Article;
use Lovata\GoodNews\Models\Category;
use Lovata\GoodNews\Plugin;

/**
 * Class ArticleList
 * @package Lovata\GoodNews\Components
 * @author Andrey Kahranenka, a.khoronenko@lovata.com, LOVATA Group
 */
class ArticleList extends ComponentBase {

    const SORT_PUBLISH_ASC = 'publish|asc';
    const SORT_PUBLISH_DESC = 'publish|desc';
    
    protected $iElementOnPage = 10;
    protected $arResult = [];
    
    /** @var Category */
    protected $obCategory;
    
    public function componentDetails() {
        return [
            'name'        => 'lovata.goodnews::lang.component.article_list',
            'description' => 'lovata.goodnews::lang.component.article_list_desc'
        ];
    }

    public function defineProperties() {

        $arProperties = [
            'error_404' => [
                'title' => Lang::get('lovata.goodnews::lang.component.property_name_error_404'),
                'description' => Lang::get('lovata.goodnews::lang.component.property_description_error_404'),
                'default' => 'on',
                'type' => 'dropdown',
                'options' => [
                    'on' => Lang::get('lovata.goodnews::lang.component.property_value_on'),
                    'off' => Lang::get('lovata.goodnews::lang.component.property_value_off'),
                ],
            ],
            'dateFormat' => [
                'title'             => 'lovata.goodnews::lang.component.property_date_format',
                'type'              => 'string',
                'default'           => Article::DEFAULT_DATE_FORMAT,
            ],
            'slug' => [
                'title'             => 'lovata.goodnews::lang.component.property_slug',
                'type'              => 'string',
                'default'           => '{{:slug}}',
            ],
            'sorting' => [
                'title' => Lang::get('lovata.goodnews::lang.component.property_sorting'),
                'type' => 'dropdown',
                'default' => self::SORT_PUBLISH_DESC,
                'options' => [
                    self::SORT_PUBLISH_ASC => Lang::get('lovata.goodnews::lang.component.sorting_publish_asc'),
                    self::SORT_PUBLISH_DESC => Lang::get('lovata.goodnews::lang.component.sorting_publish_desc'),
                ],
            ],
        ];

        $arProperties = array_merge($arProperties, Pagination::getProperties('goodnews'));
        return $arProperties;
    }

    public function onRun()
    {
        $iElementOnPage = $this->property('count_per_page');
        if($iElementOnPage > 0) {
            $this->iElementOnPage = $iElementOnPage;
        }
        
        $sCategorySlug = $this->property('slug');
        if(empty($sCategorySlug)) {
            return;
        }

        $bDisplayError404 = $this->property('error_404') == 'on' ? true : false;
        
        //Get category object
        $obCategory = Category::active()->getBySlug($sCategorySlug)->first();
        if(empty($obCategory)) {

            if(!$bDisplayError404) {
                return;
            }

            return Response::make($this->controller->run('404')->getContent(), 404);
        }

        $this->obCategory = $obCategory;
        return;
    }

    /**
     * Get element list by page number
     * @param int $iPage
     * @return array
     */
    public function get($iPage = 1) {

        $arResult = [
            'list' => [],
            'pagination' => [],
            'page' => $iPage,
            'count' => 0,
        ];

        $iRequestPage = Input::get('page');
        if(!empty($iRequestPage)) {
            $iPage = $iRequestPage;
        }

        //Set page default value
        if($iPage < 1 && $iPage != -1) {
            $iPage = 1;
            $arResult['page'] = $iPage;
        }

        $sSorting = $this->getActiveSorting();
        $arResult['sort'] = $sSorting;
        
        $iCategoryID = 0;
        if(!empty($this->obCategory)) {
            $iCategoryID = $this->obCategory->id;
        }
        
        if(isset($this->arResult[$iCategoryID]) && isset($this->arResult[$iCategoryID][$iPage])) {
            return $this->arResult[$iCategoryID][$iPage];
        }
        
        //Get cache data
        $arCacheTags = [Plugin::CACHE_TAG, Article::CACHE_TAG_LIST];
        $sCacheKey = implode('_', [Article::CACHE_TAG_LIST, $iCategoryID, $sSorting]);

        $arElementIDList = CCache::get($arCacheTags, $sCacheKey);
        if(empty($arElementIDList)) {

            //Get element ID list
            $obQuery = Article::getPublished();
            if(!empty($iCategoryID)) {
                $obQuery = $obQuery->getByCategory($iCategoryID);
            }
            
            $arElementIDList = $this->applySorting($obQuery, $sSorting)->lists('id');
            if(empty($arElementIDList)) {
                return $arResult;
            }

            //Set cache data
            $iCacheTime = Settings::getCacheTime('cache_time_article');
            CCache::put($arCacheTags, $sCacheKey, $arElementIDList, $iCacheTime);
        }

        //Apply pagination
        $arResult['count'] = count($arElementIDList);
        $arResult['max_page'] = ceil($arResult['count'] / $this->iElementOnPage);

        //Get last page number
        if($iPage == -1) {
            $iPage = $arResult['max_page'];
        }

        $arResult['page'] = $iPage;
        $arResult['pagination'] = Pagination::get($iPage, $arResult['count'], $this->properties);

        //Get element ID list for page
        $arElementIDList = array_slice($arElementIDList, $this->iElementOnPage * ($iPage - 1), $this->iElementOnPage);

        $sDateFormat = $this->property('dateFormat');
        
        //Get elements data
        foreach($arElementIDList as $iElementID) {

            //Get product data
            $arElementData = Article::getCacheData($iElementID, $sDateFormat);
            if(!empty($arElementData)) {
                $arResult['list'][$iElementID] = $arElementData;
            }
        }

        $this->arResult[$iCategoryID][$iPage] = $arResult;
        return $arResult;
    }

    /**
     * Get ajax element list
     * @return string
     */
    public function onAjaxRequest() {

        $iElementOnPage = $this->property('count_per_page');
        if($iElementOnPage > 0) {
            $this->iElementOnPage = $iElementOnPage;
        }

        return;
    }

    /**
     * Get pagination data
     * @param int $iPage
     * @return array|mixed
     */
    public function getPagination($iPage = 1) {

        $arResult = $this->get($iPage);
        if(isset($arResult['pagination'])) {
            return $arResult['pagination'];
        }

        return [];
    }

    /**
     * Get count products
     * @param int $iPage
     * @return array|mixed
     */
    public function getCount($iPage = 1) {

        $arResult = $this->get($iPage);
        if(isset($arResult['count'])) {
            return $arResult['count'];
        }

        return 0;
    }

    /**
     * Get sorting
     * @return mixed|string
     */
    public function getActiveSorting() {

        $sSorting = Input::get('sort');
        if(empty($sSorting)) {
            $sSorting = $this->property('sorting');
        }

        if(!in_array($sSorting, [self::SORT_PUBLISH_ASC, self::SORT_PUBLISH_DESC])) {
            $sSorting = $this->property('sorting');
        }

        return $sSorting;
    }

    /**
     * Apply sorting
     * @param Article $obQuery
     * @param string $sSorting
     * @return Article
     */
    protected function applySorting($obQuery, $sSorting) {
        
        switch($sSorting) {
            case self::SORT_PUBLISH_ASC:
                return $obQuery->orderBy('published_start', 'asc');
            case self::SORT_PUBLISH_DESC:
                return $obQuery->orderBy('published_start', 'desc');
            default:
                return $obQuery->orderBy('published_start', 'asc');
        }
    }
}
