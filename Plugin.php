<?php namespace Lovata\GoodNews;

use Event;
use System\Classes\PluginBase;

//Event
use Lovata\GoodNews\Classes\Event\ArticleModelHandler;
use Lovata\GoodNews\Classes\Event\CategoryModelHandler;

/**
 * Class Plugin
 * @package Lovata\GoodNews
 * @author  Andrey Kahranenka, a.khoronenko@lovata.com, LOVATA Group
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
            'Lovata\GoodNews\Components\ArticleList'         => 'ArticleList',
            'Lovata\GoodNews\Components\ArticlePage'         => 'ArticlePage',
            'Lovata\GoodNews\Components\ArticleData'         => 'ArticleData',
            'Lovata\GoodNews\Components\ArticleCategoryList' => 'ArticleCategoryList',
            'Lovata\GoodNews\Components\ArticleCategoryPage' => 'ArticleCategoryPage',
            'Lovata\GoodNews\Components\ArticleCategoryData' => 'ArticleCategoryData',
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
