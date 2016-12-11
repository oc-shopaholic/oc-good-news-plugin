<?php namespace Lovata\GoodNews\Components;

use Lovata\GoodNews\Models\Article;
use Cms\Classes\ComponentBase;

/**
 * Class ArticleNearest
 * @package Lovata\GoodNews\Components
 * @author Andrey Kahranenka, a.khoronenko@lovata.com, LOVATA Group
 */
class ArticleNearest extends ComponentBase {
    
    public function componentDetails() {
        return [
            'name'        => 'lovata.goodnews::lang.component.article_nearest',
            'description' => 'lovata.goodnews::lang.component.article_nearest_desc',
        ];
    }

    public function defineProperties() {
        return [
            'dateFormat' => [
                'title'             => 'lovata.goodnews::lang.component.property_date_format',
                'type'              => 'string',
                'default'           => Article::DEFAULT_DATE_FORMAT,
            ],
        ];
    }

    /**
     * Get previous elements
     * @param int $iElementID
     * @param int $iCount
     * @return array
     */
    public function getPrev($iElementID, $iCount = 1) {
        
        $arResult = [];
        if(empty($iElementID)) {
            return $arResult;
        }
        
        if($iCount < 1) {
            $iCount = 1;
        }
        
        //Get element by ID
        /** @var Article $obElement */
        $obElement = Article::getPublished()->find($iElementID);
        if(empty($obElement)) {
            return $arResult;
        }
        
        //Get ID previous elements
        $arElementIDList = Article::getPublished()
            ->where('id', '!=', $obElement->id)
            ->getByDateValue('published_start', $obElement->published_start, '<=')
            ->orderBy('published_start', 'desc')
            ->take($iCount)
            ->lists('id');
        
        if(empty($arElementIDList)) {
            return $arResult;
        }
        
        //Get elements data
        foreach($arElementIDList as $iElementID) {
            $arElementData = Article::getCacheData($iElementID, $this->property('dateFormat'));
            if(empty($arElementData)) {
                continue;
            }
            
            $arResult[] = $arElementData;
        }
        
        return $arResult;
    }

    /**
     * Get next elements
     * @param int $iElementID
     * @param int $iCount
     * @return array
     */
    public function getNext($iElementID, $iCount = 1) {

        $arResult = [];
        if(empty($iElementID)) {
            return $arResult;
        }

        if($iCount < 1) {
            $iCount = 1;
        }

        //Get element by ID
        /** @var Article $obElement */
        $obElement = Article::getPublished()->find($iElementID);
        if(empty($obElement)) {
            return $arResult;
        }

        //Get ID previous elements
        $arElementIDList = Article::getPublished()
            ->where('id', '!=', $obElement->id)
            ->getByDateValue('published_start', $obElement->published_start, '>=')
            ->orderBy('published_start', 'asc')
            ->take($iCount)
            ->lists('id');
        
        if(empty($arElementIDList)) {
            return $arResult;
        }

        //Get elements data
        foreach($arElementIDList as $iElementID) {
            $arElementData = Article::getCacheData($iElementID, $this->property('dateFormat'));
            if(empty($arElementData)) {
                continue;
            }

            $arResult[] = $arElementData;
        }

        return $arResult;
    }

    /**
     * Get nearest elements
     * @param int $iElementID
     * @param int $iCount
     * @return array
     */
    public function getNearest($iElementID, $iCount = 1) {

        $arResult = [];
        if(empty($iElementID)) {
            return $arResult;
        }

        if($iCount < 1) {
            $iCount = 1;
        }

        $arPrevElements = $this->getPrev($iElementID, $iCount * 2);
        $arNextElements = $this->getNext($iElementID, $iCount * 2);
        
        //Add prev elements
        $iPrevCount = $iCount;
        if(!empty($arPrevElements)) {
            for($i = 0; $i < $iCount; $i++) {
                if(empty($arPrevElements)) {
                    break;
                }
                
                $arResult[] = array_shift($arPrevElements);
                $iPrevCount--;
            }
        }

        //Add next elements
        $iNextCount = $iCount;
        if(!empty($arNextElements)) {
            for($i = 0; $i < $iCount; $i++) {
                if(empty($arNextElements)) {
                    break;
                }

                $arResult[] = array_shift($arNextElements);
                $iNextCount--;
            }
        }
        
        //Add addition prev elements
        if(!empty($arPrevElements) && $iNextCount > 0) {
            for($i = 0; $i < $iNextCount; $i++) {
                if(empty($arPrevElements)) {
                    break;
                }

                $arResult = array_merge([array_shift($arPrevElements)], $arResult);
                $iNextCount--;
            }
        }

        //Add addition next elements
        if(!empty($arNextElements) && $iPrevCount > 0) {
            for($i = 0; $i < $iPrevCount; $i++) {
                if(empty($arNextElements)) {
                    break;
                }

                $arResult[] = array_shift($arNextElements);
                $iPrevCount--;
            }
        }
        
        return $arResult;
    }
}
