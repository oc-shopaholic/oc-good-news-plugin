<?php namespace Lovata\GoodNews\Components;

use Lang;
use Lovata\GoodNews\Models\Article;
use Response;
use Cms\Classes\ComponentBase;

/**
 * Class ArticlePage
 * @package Lovata\GoodNews\Components
 * @author Andrey Kahranenka, a.khoronenko@lovata.com, LOVATA Group
 */
class ArticlePage extends ComponentBase {

    /** @var Article */
    protected $obElement;

    public function componentDetails() {
        return [
            'name'        => 'lovata.goodnews::lang.component.article_page',
            'description' => 'lovata.goodnews::lang.component.article_page_desc'
        ];
    }

    /** @var string Формат вывода даты */
    protected $sDateFormat;

    public function defineProperties() {
        return [
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
            'slug' => [
                'title'             => Lang::get('lovata.goodnews::lang.component.property_slug'),
                'type'              => 'string',
                'default'           => '{{ :slug }}',
            ],
            'dateFormat' => [
                'title'             => 'lovata.goodnews::lang.component.property_date_format',
                'type'              => 'string',
                'default'           => Article::DEFAULT_DATE_FORMAT,
            ],
        ];
    }

    /**
     * Get element object
     * @return \Illuminate\Http\Response|void
     */
    public function onRun() {
        
        $bDisplayError404 = $this->property('error_404') == 'on' ? true : false;

        //Get element slug
        $sElementSlug = $this->property('slug');
        if (empty($sElementSlug)) {

            if (!$bDisplayError404) {
                return;
            }

            return Response::make($this->controller->run('404')->getContent(), 404);
        }

        //Get element by slug
        /** @var Article $obElement */
        $obElement = Article::getPublished()->getBySlug($sElementSlug)->first();
        if (empty($obElement)) {

            if (!$bDisplayError404) {
                return;
            }

            return Response::make($this->controller->run('404')->getContent(), 404);
        }

        $this->obElement = $obElement;
        $this->sDateFormat = $this->property('dateFormat', Article::DEFAULT_DATE_FORMAT);
        
        return;
    }
    
    /**
     * Get article data
     * @return array|null
     */
    public function get() {

        if(empty($this->obElement)) {
            return null;
        }

        return $this->obElement->getCacheData($this->obElement->id, $this->sDateFormat, $this->obElement);
    }
}
