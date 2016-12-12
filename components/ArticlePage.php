<?php namespace Lovata\GoodNews\Components;

use Lang;
use Lovata\GoodNews\Models\Article;
use Lovata\Toolbox\Classes\ComponentTraitNotFoundResponse;
use Response;
use Cms\Classes\ComponentBase;

/**
 * Class ArticlePage
 * @package Lovata\GoodNews\Components
 * @author Andrey Kahranenka, a.khoronenko@lovata.com, LOVATA Group
 */
class ArticlePage extends ComponentBase
{
    use ComponentTraitNotFoundResponse;

    /** @var Article */
    protected $obElement;


    /** @var string Формат вывода даты */
    protected $sDateFormat;

    /**
     * @return array
     */
    public function componentDetails()
    {
        return [
            'name'        => 'lovata.goodnews::lang.component.article_page',
            'description' => 'lovata.goodnews::lang.component.article_page_desc'
        ];
    }

    /**
     * @return array
     */
    public function defineProperties()
    {
        $arProperties = [
            'dateFormat' => [
                'title'             => 'lovata.goodnews::lang.component.property_date_format',
                'type'              => 'string',
                'default'           => Article::DEFAULT_DATE_FORMAT,
            ],
        ];

        $arProperties = array_merge($arProperties, $this->getElementPageProperties());
        return $arProperties;
    }

    /**
     * Get element object
     * @return \Illuminate\Http\Response|void
     */
    public function onRun()
    {
        $bDisplayError404 = $this->property('error_404') == 'on' ? true : false;

        //Get element slug
        $sElementSlug = $this->property('slug');
        if (empty($sElementSlug)) {
            return $this->getErrorResponse($bDisplayError404);
        }

        //Get element by slug
        /** @var Article $obElement */
        $obElement = Article::getPublished()->getBySlug($sElementSlug)->first();
        if (empty($obElement)) {
            return $this->getErrorResponse($bDisplayError404);
        }

        $this->obElement = $obElement;
        $this->sDateFormat = $this->property('dateFormat', Article::DEFAULT_DATE_FORMAT);
        
        return;
    }
    
    /**
     * Get article data
     * @return array|null
     */
    public function get()
    {
        if(empty($this->obElement)) {
            return null;
        }

        return $this->obElement->getCacheData($this->obElement->id, $this->sDateFormat, $this->obElement);
    }
}
