<?php namespace Lovata\GoodNews\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

/**
 * Class Categories
 * @package Lovata\GoodNews\Controllers
 * @author Andrey Kahranenka, a.khoronenko@lovata.com, LOVATA Group
 */
class Categories extends Controller
{
    public $implement = ['Backend\Behaviors\ListController','Backend\Behaviors\FormController','Backend\Behaviors\ReorderController'];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $reorderConfig = 'config_reorder.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Lovata.GoodNews', 'main-good-news', 'side-good-news-category');
    }
}