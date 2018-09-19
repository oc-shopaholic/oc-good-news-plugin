<?php namespace Lovata\GoodNews;

use Event;
use System\Classes\PluginBase;

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

/**
 * Class Plugin
 * @package Lovata\GoodNews
 * @author Andrey Kahranenka, a.khoronenko@lovata.com, LOVATA Group
 */
class Plugin extends PluginBase
{
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
     * Plugin boot method
     */
    public function boot()
    {
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
