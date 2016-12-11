<?php namespace Lovata\GoodNews;

use System\Classes\PluginBase;

/**
 * Class Plugin
 * @package Lovata\GoodNews
 * @author Andrey Kahranenka, a.khoronenko@lovata.com, LOVATA Group
 */
class Plugin extends PluginBase
{
    const NAME = 'goodnews';
    const CACHE_TAG = 'lovata-good-news';
    const CACHE_TIME_DEFAULT = 10080;
    
    
    /**
     * Registration components
     * @return array
     */
    public function registerComponents()
    {
        return [
            '\Lovata\GoodNews\Components\ArticleList' => 'ArticleList',
            '\Lovata\GoodNews\Components\ArticlePage' => 'ArticlePage',
            '\Lovata\GoodNews\Components\ArticleData' => 'ArticleData',
            '\Lovata\GoodNews\Components\ArticleNearest' => 'ArticleNearest',
            '\Lovata\GoodNews\Components\CategoryMenu' => 'CategoryMenu',
        ];
    }

    public function registerSettings() {
        return [
            'config' => [
                'label'       => 'lovata.goodnews::lang.plugin.name',
                'icon'        => 'icon-cogs',
                'description' => 'lovata.goodnews::lang.plugin.description',
                'class'       => 'Lovata\GoodNews\Models\Settings',
                'permissions' => ['lovata-good-news-settings'],
                'order'       => 100
            ]
        ];
    }
}
