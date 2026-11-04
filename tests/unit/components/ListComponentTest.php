<?php namespace Lovata\GoodNews\Tests\Unit\Components;

use ReflectionMethod;
use Lovata\Toolbox\Tests\CommonTest;

use Lovata\GoodNews\Components\ArticleList;
use Lovata\GoodNews\Components\ArticleCategoryList;
use Lovata\GoodNews\Classes\Collection\ArticleCollection;
use Lovata\GoodNews\Classes\Collection\CategoryCollection;

/**
 * Class ListComponentTest
 * @package Lovata\GoodNews\Tests\Unit\Components
 *
 * @mixin \PHPUnit\Framework\Assert
 */
class ListComponentTest extends CommonTest
{
    /**
     * @return array
     */
    public function componentProvider()
    {
        return [
            'ArticleList'         => [ArticleList::class, ArticleCollection::class],
            'ArticleCategoryList' => [ArticleCategoryList::class, CategoryCollection::class],
        ];
    }

    /**
     * makeCollection() without arguments returns a collection with the list not set
     * @dataProvider componentProvider
     */
    public function testMakeCollectionWithoutArguments($sComponentClass, $sCollectionClass)
    {
        $obCollection = (new $sComponentClass())->makeCollection();

        self::assertInstanceOf($sCollectionClass, $obCollection);
        self::assertTrue($obCollection->isClear());
    }

    /**
     * makeCollection() passes the ID list to the collection
     * @dataProvider componentProvider
     */
    public function testMakeCollectionWithIDList($sComponentClass, $sCollectionClass)
    {
        $obCollection = (new $sComponentClass())->makeCollection([1, 2]);

        self::assertInstanceOf($sCollectionClass, $obCollection);
        self::assertEquals(2, $obCollection->count());
    }

    /**
     * The plugin must not declare make(): it conflicts with the static
     * Larajax\Traits\ViewComponent::make() on October CMS 4.4.2+
     * @dataProvider componentProvider
     */
    public function testComponentDoesNotDeclareMake($sComponentClass)
    {
        if (!method_exists($sComponentClass, 'make')) {
            self::assertTrue(true);
            return;
        }

        self::assertNotSame($sComponentClass, (new ReflectionMethod($sComponentClass, 'make'))->class);
    }
}
