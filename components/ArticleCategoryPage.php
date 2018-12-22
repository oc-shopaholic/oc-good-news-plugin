<?php namespace Lovata\GoodNews\Components;

use Lovata\Toolbox\Classes\Component\ElementPage;

use Lovata\GoodNews\Models\Category;
use Lovata\GoodNews\Classes\Item\CategoryItem;

/**
 * Class ArticleCategoryPage
 * @package Lovata\GoodNews\Components
 * @author Andrey Kahranenka, a.khoronenko@lovata.com, LOVATA Group
 */
class ArticleCategoryPage extends ElementPage
{
    /** @var Category */
    protected $obElement;

    /**
     * @return array
     */
    public function componentDetails()
    {
        return [
            'name'        => 'lovata.goodnews::lang.component.category_page',
            'description' => 'lovata.goodnews::lang.component.category_page_desc'
        ];
    }

    /**
     * Get element object
     * @param string $sElementSlug
     * @return Category
     */
    protected function getElementObject($sElementSlug)
    {
        if(empty($sElementSlug)) {
            return null;
        }

        $obElement = Category::active()->getBySlug($sElementSlug)->first();
        return $obElement;
    }

    /**
     * Make new element item
     * @param int $iElementID
     * @param Category $obElement
     * @return CategoryItem
     */
    protected function makeItem($iElementID, $obElement)
    {
        return CategoryItem::make($iElementID, $obElement);
    }
}
