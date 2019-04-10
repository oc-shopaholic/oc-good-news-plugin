<?php namespace Lovata\GoodNews\Classes\Event;

use Lovata\Toolbox\Classes\Event\ModelHandler;

use Lovata\GoodNews\Classes\Store\ArticleListStore;
use Lovata\GoodNews\Classes\Item\ArticleItem;
use Lovata\GoodNews\Models\Article;

/**
 * Class ArticleModelHandler
 * @package Lovata\GoodNews\Classes\Event
 * @author  Sergey Zakharevich, s.zakharevich@lovata.com, LOVATA Group
 */
class ArticleModelHandler extends ModelHandler
{
    /** @var  Article */
    protected $obElement;

    /**
     * After create event handler
     */
    protected function afterCreate()
    {
        parent::afterCreate();

        ArticleListStore::instance()->sorting->clear(ArticleListStore::SORT_NO);
    }

    /**
     * After save event handler
     */
    protected function afterSave()
    {
        parent::afterSave();

        $this->checkFieldChanges('status_id', ArticleListStore::instance()->published);
        $this->checkFieldChanges('published_start', ArticleListStore::instance()->published);
        $this->checkFieldChanges('published_stop', ArticleListStore::instance()->published);

        if ($this->isFieldChanged('published_start')) {
            $this->clearBySortingPublished();
        }

        if ($this->isFieldChanged('view_count')) {
            $this->clearBySortingViews();
        }

        $this->checkFieldChanges('category_id', ArticleListStore::instance()->category);
    }

    /**
     * After delete event handler
     */
    protected function afterDelete()
    {
        parent::afterDelete();

        if ($this->obElement->status_id == Article::STATUS_PUBLISHED) {
            ArticleListStore::instance()->published->clear();
        }

        $this->clearBySortingPublished();
        $this->clearBySortingViews();

        ArticleListStore::instance()->category->clear($this->obElement->category_id);
    }

    /**
     * Clear cache by sorting published
     */
    protected function clearBySortingPublished()
    {
        ArticleListStore::instance()->sorting->clear(ArticleListStore::SORT_PUBLISH_ASC);
        ArticleListStore::instance()->sorting->clear(ArticleListStore::SORT_PUBLISH_DESC);
    }

    /**
     * Clear cache by sorting views
     */
    protected function clearBySortingViews()
    {
        ArticleListStore::instance()->sorting->clear(ArticleListStore::SORT_VIEW_COUNT_ASC);
        ArticleListStore::instance()->sorting->clear(ArticleListStore::SORT_VIEW_COUNT_DESC);
    }

    /**
     * Get model class name
     * @return string
     */
    protected function getModelClass()
    {
        return Article::class;
    }

    /**
     * Get item class name
     * @return string
     */
    protected function getItemClass()
    {
        return ArticleItem::class;
    }
}