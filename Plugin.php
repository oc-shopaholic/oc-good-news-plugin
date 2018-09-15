<?php namespace Lovata\GoodNews;

use Event;
use System\Classes\PluginBase;

//Item
use Lovata\GoodNews\Classes\Item\ArticleItem;
use Lovata\GoodNews\Classes\Item\CategoryItem;

//Collection
use Lovata\GoodNews\Classes\Collection\ArticleCollection;
use Lovata\GoodNews\Classes\Collection\CategoryCollection;

//Store
use Lovata\GoodNews\Classes\Store\ArticleListStore;
use Lovata\GoodNews\Classes\Store\CategoryListStore;

//Event
use Lovata\GoodNews\Classes\Event\ArticleModelHandler;
use Lovata\GoodNews\Classes\Event\CategoryModelHandler;

//Components
use Lovata\GoodNews\Components\ArticleData;
use Lovata\GoodNews\Components\ArticlePage;
use Lovata\GoodNews\Components\ArticleList;
use Lovata\GoodNews\Components\CategoryData;
use Lovata\GoodNews\Components\CategoryList;
use Lovata\GoodNews\Components\CategoryPage;

//Console
use Lovata\GoodNews\Console\UpdatePublishedArticleList;

/**
 * Class Plugin
 * @package Lovata\GoodNews
 * @author Andrey Kahranenka, a.khoronenko@lovata.com, LOVATA Group
 */
class Plugin extends PluginBase
{
    const CACHE_TAG = 'lovata-good-news';

    /** @var array Plugin dependencies */
    public $require = ['Lovata.Toolbox'];
    
    /**
     * Registration components
     * @return array
     */
    public function registerComponents()
    {
        return [
            ArticleList::class    => 'ArticleList',
            ArticlePage::class    => 'ArticlePage',
            ArticleData::class    => 'ArticleData',
            CategoryList::class   => 'CategoryList',
            CategoryPage::class   => 'CategoryPage',
            CategoryData::class   => 'CategoryData',
        ];
    }

    /**
     * Register artisan command
     */
    public function register()
    {
        $this->registerConsoleCommand('UpdatePublishedArticleList', UpdatePublishedArticleList::class);
    }

    /**
     * Plugin boot method
     */
    public function boot()
    {
        $this->app->singleton(CategoryListStore::class, CategoryListStore::class);
        $this->app->singleton(ArticleListStore::class, ArticleListStore::class);
        
        $this->app->bind(ArticleItem::class, ArticleItem::class);
        $this->app->bind(CategoryItem::class, CategoryItem::class);
        
        $this->app->bind(ArticleCollection::class, ArticleCollection::class);
        $this->app->bind(CategoryCollection::class, CategoryCollection::class);
        
        $this->addEventListener();
    }

    /**
     * Add event listeners
     */
    protected function addEventListener()
    {
        Event::subscribe(ArticleModelHandler::class);
        Event::subscribe(CategoryModelHandler::class);
    }
}
