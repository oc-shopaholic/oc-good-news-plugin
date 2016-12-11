<?php namespace Lovata\GoodNews\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use System\Classes\PluginManager;

/**
 * Class Articles
 * @package Lovata\GoodNews\Controllers
 * @author Andrey Kahranenka, a.khoronenko@lovata.com, LOVATA Group
 */
class Articles extends Controller
{
    public $implement = ['Backend\Behaviors\ListController','Backend\Behaviors\FormController','Backend\Behaviors\RelationController'];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $relationConfig = [];

    public function __construct()
    {
        //Add relation config
        if(PluginManager::instance()->hasPlugin('Lovata.GoodNewsShopaholic')) {
            // for related products
            $this->relationConfig['product'] = \Lovata\GoodNewsShopaholic\Plugin::getProductRelationFieldConfig();
            $this->relationConfig['category_product'] = \Lovata\GoodNewsShopaholic\Plugin::getCategoryRelationFieldConfig();
        }
        
        parent::__construct();
        BackendMenu::setContext('Lovata.GoodNews', 'main-good-news', 'side-good-news-article');
    }
}