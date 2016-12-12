<?php namespace Lovata\GoodNews\Components;

use Lovata\GoodNews\Models\Article;
use Request;
use Cms\Classes\ComponentBase;

/**
 * Class ArticleData
 * @package Lovata\GoodNews\Components
 * @author Andrey Kahranenka, a.khoronenko@lovata.com, LOVATA Group
 */
class ArticleData extends ComponentBase
{
    protected $iElementID;

    /**
     * @return array
     */
    public function componentDetails()
    {
        return [
            'name'        => 'lovata.goodnews::lang.component.article_data',
            'description' => 'lovata.goodnews::lang.component.article_data_desc'
        ];
    }

    /**
     * @return array
     */
    public function defineProperties()
    {
        return [
            'dateFormat' => [
                'title'             => 'lovata.goodnews::lang.component.property_date_format',
                'type'              => 'string',
                'default'           => Article::DEFAULT_DATE_FORMAT,
            ],
        ];
    }

    /**
     * Get article data (ajax)
     */
    public function onAjaxRequest()
    {
        $this->iElementID = Request::get('article_id');
    }
    
    /**
     * Get article data by ID
     * @param int $iElementID
     * @return array|null
     */
    public function get($iElementID = null)
    {
        if(empty($iElementID) && !empty($this->iElementID)) {
            $iElementID = $this->iElementID;
        } elseif(empty($iElementID)) {
            return [];
        }

        return Article::getCacheData($iElementID, $this->property('dateFormat'));
    }
}
