<?php namespace Lovata\GoodNews\Classes\Event;

use Lovata\GoodNews\Classes\Store\CategoryListStore;
use Lovata\GoodNews\Classes\Item\CategoryItem;
use Lovata\GoodNews\Models\Category;
use Lovata\Toolbox\Classes\Event\ModelHandler;

/**
 * Class CategoryModelHandler
 * @package Lovata\GoodNews\Classes\Event
 * @author Dmitry Drozd, d.drozd@lovata.com, LOVATA Group
 */
class CategoryModelHandler extends ModelHandler
{
    /** @var  CategoryListStore */
    protected $obListStore;

    /**
     * CategoryModelHandler constructor.
     *
     * @param CategoryListStore $obCategoryListStore
     */
    public function __construct(CategoryListStore $obCategoryListStore)
    {
        $this->obListStore = $obCategoryListStore;
    }

    public function subscribe($obEvent)
    {
        parent::subscribe($obEvent);

        Category::updated(function($obElement) {
            $this->clearCache($obElement);
        });

        Category::deleted(function($obElement) {
            $this->clearCache($obElement);
        });
    }

    /**
     * Clear cache data
     * @param Category $obElement
     */
    protected function clearCache($obElement)
    {
        CategoryItem::clearCache($obElement->id);
        CategoryItem::clearCache($obElement->parent_id);

        if($obElement->parent_id != $obElement->getOriginal('parent_id')) {
            CategoryItem::clearCache($obElement->getOriginal('parent_id'));
        }
    }

    /**
     * Get model class name
     * @return string
     */
    protected function getModelClass()
    {
        return Category::class;
    }

    /**
     * Get item class name
     * @return string
     */
    protected function getItemClass()
    {
        return CategoryItem::class;
    }
}