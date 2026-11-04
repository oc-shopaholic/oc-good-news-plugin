<?php namespace Lovata\GoodNews\Classes\Event;

use Lovata\Toolbox\Classes\Event\AbstractImageSetsFieldHandler;

use Lovata\GoodNews\Models\Article;
use Lovata\GoodNews\Controllers\Articles;

/**
 * Class ExtendArticleImageSetsFieldHandler
 * Adds image sets field to article form
 * @package Lovata\GoodNews\Classes\Event
 * @author  Pavel Lautsevich, p.lautsevich@lovata.com, LOVATA Group
 */
class ExtendArticleImageSetsFieldHandler extends AbstractImageSetsFieldHandler
{
    /**
     * Get tab name for image sets field
     * @return string
     */
    protected function getTabName() : string
    {
        return 'lovata.toolbox::lang.tab.images';
    }

    /**
     * Get model class name
     * @return string
     */
    protected function getModelClass() : string
    {
        return Article::class;
    }

    /**
     * Get controller class name
     * @return string
     */
    protected function getControllerClass() : string
    {
        return Articles::class;
    }
}
