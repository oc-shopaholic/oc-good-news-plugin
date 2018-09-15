<?php namespace Lovata\GoodNews\Classes\Event;

use Queue;
use Lovata\GoodNews\Classes\Store\ArticleListStore;
use Lovata\GoodNews\Classes\Item\ArticleItem;
use Lovata\GoodNews\Models\Article;
use Lovata\Toolbox\Classes\Event\ModelHandler;

/**
 * Class ArticleModelHandler
 * @package Lovata\GoodNews\Classes\Event
 * @author Dmitry Drozd, d.drozd@lovata.com, LOVATA Group
 */
class ArticleModelHandler extends ModelHandler
{
    /** @var  Article */
    protected $obElement;
    
    /** @var  ArticleListStore */
    protected $obListStore;

    /**
     * ArticleModelHandler constructor.
     *
     * @param ArticleListStore $obArticleListStore
     */
    public function __construct(ArticleListStore $obArticleListStore)
    {
        $this->obListStore = $obArticleListStore;
    }

    /**
     * Add listeners
     * @param \Illuminate\Events\Dispatcher $obEvent
     */
    public function subscribe($obEvent)
    {
        parent::subscribe($obEvent);
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

    /**
     * After save event handler
     */
    protected function afterSave()
    {
        parent::afterSave();

        if($this->obElement->getOriginal('published_start') != $this->obElement->published_start || empty($this->obElement->getOriginal('id'))) {
            // Update article ID list with sorting
            $this->obListStore->updateCacheBySorting(ArticleListStore::SORT_PUBLISH_DESC);
            $this->obListStore->updateCacheBySorting(ArticleListStore::SORT_PUBLISH_ASC);
        }

        if($this->obElement->getOriginal('view_count') != $this->obElement->view_count) {
            $this->obListStore->updateCacheBySorting(ArticleListStore::SORT_VIEW_COUNT_DESC);
            $this->obListStore->updateCacheBySorting(ArticleListStore::SORT_VIEW_COUNT_ASC);
        }

        // Update article ID list by category filter
        if($this->obElement->getOriginal('category_id') != $this->obElement->category_id) {
            
            $this->obListStore->clearListByCategory($this->obElement->category_id);
            $this->obListStore->clearListByCategory($this->obElement->getOriginal('category_id'));
        }

        // Update article ID list by status filter
        if($this->obElement->getOriginal('status_id') != $this->obElement->status_id) {

            $this->obListStore->clearListByStatus($this->obElement->status_id);
            $this->obListStore->clearListByStatus($this->obElement->getOriginal('status_id'));
        }
    }

    /**
     * After delete event handler
     */
    protected function afterDelete()
    {
        parent::afterDelete();

        $this->obListStore->updateCacheBySorting(ArticleListStore::SORT_PUBLISH_DESC);
        $this->obListStore->updateCacheBySorting(ArticleListStore::SORT_PUBLISH_ASC);

        $this->obListStore->updateCacheBySorting(ArticleListStore::SORT_VIEW_COUNT_DESC);
        $this->obListStore->updateCacheBySorting(ArticleListStore::SORT_VIEW_COUNT_ASC);

        $this->obListStore->clearListByCategory($this->obElement->category_id);
        $this->obListStore->clearListByStatus($this->obElement->status_id);
        
        if($this->obElement->status_id == Article::STATUS_PUBLISHED) {
            $this->obListStore->clearPublishedList();
        }
    }
}