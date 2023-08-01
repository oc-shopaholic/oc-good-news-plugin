<?php namespace Lovata\GoodNews\Components;

use Lovata\Toolbox\Classes\Component\ElementPage;

use Lovata\GoodNews\Models\Article;
use Lovata\GoodNews\Classes\Item\ArticleItem;

/**
 * Class ArticlePage
 * @package Lovata\GoodNews\Components
 * @author Andrey Kahranenka, a.khoronenko@lovata.com, LOVATA Group
 */
class ArticlePage extends ElementPage
{
    protected $bNeedSmartURLCheck = true;

    /** @var Article */
    protected $obElement;

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
     * Get element object
     * @param string $sElementSlug
     * @return Article
     */
    protected function getElementObject($sElementSlug)
    {
        if(empty($sElementSlug)) {
            return null;
        }

        $obElement = Article::getBySlug($sElementSlug)
            ->getByStatus(Article::STATUS_PUBLISHED)
            ->getPublished()
            ->first();

        $obElement = $this->hasRelationWithSite($obElement) ? $obElement : null;
        if(!empty($obElement)) {
            $obElement->view_count++;
            $obElement->save();
        }

        return $obElement;
    }

    /**
     * Get element item
     * @param int    $iElementID
     * @param Article $obElement
     * @return ArticleItem
     */
    protected function makeItem($iElementID, $obElement)
    {
        $obElementItem = ArticleItem::make($iElementID, $obElement);

        return $obElementItem;
    }
}
