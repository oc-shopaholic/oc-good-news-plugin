<?php namespace Lovata\GoodNews\Tests\Unit\Item;

use October\Rain\Argon\Argon;
use Lovata\Toolbox\Tests\CommonTest;

use Lovata\GoodNews\Models\Article;
use Lovata\GoodNews\Classes\Collection\ArticleCollection;
use Lovata\GoodNews\Models\Category;
use Lovata\GoodNews\Classes\Item\CategoryItem;
use Lovata\GoodNews\Classes\Collection\CategoryCollection;

/**
 * Class CategoryItemTest
 * @package Lovata\GoodNews\Tests\Unit\Item
 * @author  Andrey Kharanenka, a.khoronenko@lovata.com, LOVATA Group
 *
 * @mixin \PHPUnit\Framework\Assert
 */
class CategoryItemTest extends CommonTest
{
    /** @var  Category */
    protected $obElement;

    /** @var  Category */
    protected $obChildElement;

    /** @var Article */
    protected $obArticle;

    protected $arCreateData = [
        'name'            => 'name',
        'slug'            => 'slug',
        'code'            => 'code',
        'preview_text'    => 'preview_text',
        'description'     => 'description',
        'seo_title'       => 'seo_title',
        'seo_keywords'    => 'seo_keywords',
        'seo_description' => 'seo_description',
    ];

    protected $arArticleData = [
        'status_id'       => 4,
        'title'           => 'title',
        'slug'            => 'slug',
        'preview_text'    => 'preview_text',
        'content'         => 'content',
        'seo_title'       => 'seo_title',
        'seo_keywords'    => 'seo_keywords',
        'seo_description' => 'seo_description',
        'view_count'      => 0
    ];

    /**
     * Check item fields
     */
    public function testItemFields()
    {
        $this->createTestData();
        if (empty($this->obElement)) {
            return;
        }

        $sErrorMessage = 'Category item data is not correct';

        $arCreatedData = $this->arCreateData;
        $arCreatedData['id'] = $this->obElement->id;
        $arCreatedData['nest_depth'] = 0;
        $arCreatedData['parent_id'] = 0;
        $arCreatedData['children_id_list'] = [$this->obChildElement->id];

        //Check item fields
        $obItem = CategoryItem::make($this->obElement->id);
        foreach ($arCreatedData as $sField => $sValue) {
            self::assertEquals($sValue, $obItem->$sField, $sErrorMessage);
        }

        if (empty($this->obChildElement)) {
            return;
        }

        $arCreatedData = $this->arCreateData;
        $arCreatedData['id'] = $this->obChildElement->id;
        $arCreatedData['slug'] = 'slug1';
        $arCreatedData['nest_depth'] = 1;
        $arCreatedData['parent_id'] = $this->obElement->id;

        $obChildrenCollection = $obItem->children;
        self::assertNotEmpty($obChildrenCollection, $sErrorMessage);
        self::assertInstanceOf(CategoryCollection::class, $obChildrenCollection, $sErrorMessage);

        /** @var CategoryItem $obChildItem */
        $obChildItem = $obChildrenCollection->shift();

        //Check item fields
        foreach ($arCreatedData as $sField => $sValue) {
            self::assertEquals($sValue, $obChildItem->$sField, $sErrorMessage);
        }
    }

    /**
     * Check update cache item data, after update model data
     */
    public function testItemClearCache()
    {
        $this->createTestData();
        if (empty($this->obElement)) {
            return;
        }

        $sErrorMessage = 'Category item data is not correct, after model update';

        $obItem = CategoryItem::make($this->obElement->id);
        self::assertEquals('name', $obItem->name, $sErrorMessage);

        //Check cache update
        $this->obElement->name = 'test';
        $this->obElement->save();

        $obItem = CategoryItem::make($this->obElement->id);
        self::assertEquals('test', $obItem->name, $sErrorMessage);
    }

    /**
     * Check update cache item data, after remove element
     */
    public function testRemoveElement()
    {
        $this->createTestData();
        if (empty($this->obElement)) {
            return;
        }

        $sErrorMessage = 'Category item data is not correct, after model remove';

        $obItem = CategoryItem::make($this->obChildElement->id);
        self::assertEquals(false, $obItem->isEmpty(), $sErrorMessage);

        //Remove element
        $this->obChildElement->delete();

        $obItem = CategoryItem::make($this->obChildElement->id);
        self::assertEquals(true, $obItem->isEmpty(), $sErrorMessage);

        $obItem = CategoryItem::make($this->obElement->id);
        $obChildCollection = $obItem->children;
        self::assertEquals(true, $obChildCollection->isEmpty(), $sErrorMessage);
    }

    /**
     * Test article_count field for main category
     */
    public function testArticleCountField()
    {
        $this->createTestData();
        if (empty($this->obElement)) {
            return;
        }

        $obParentItem = CategoryItem::make($this->obElement->id);
        $obItem = CategoryItem::make($this->obChildElement->id);

        self::assertEquals(1, $obParentItem->article_count);
        self::assertEquals(1, $obItem->article_count);

        //Set empty category_id in Article object
        $this->obArticle->category_id = null;
        $this->obArticle->save();

        $obParentItem = CategoryItem::make($this->obElement->id);
        $obItem = CategoryItem::make($this->obChildElement->id);

        self::assertEquals(0, $obParentItem->article_count);
        self::assertEquals(0, $obItem->article_count);

        //Set parent category_id in Article object
        $this->obArticle->category_id = $this->obElement->id;
        $this->obArticle->save();

        $obParentItem = CategoryItem::make($this->obElement->id);
        $obItem = CategoryItem::make($this->obChildElement->id);

        self::assertEquals(1, $obParentItem->article_count);
        self::assertEquals(0, $obItem->article_count);

        //Set child category_id in Article object
        $this->obArticle->category_id = $this->obChildElement->id;
        $this->obArticle->save();

        $obParentItem = CategoryItem::make($this->obElement->id);
        $obItem = CategoryItem::make($this->obChildElement->id);

        self::assertEquals(1, $obParentItem->article_count);
        self::assertEquals(1, $obItem->article_count);

        //Set status_id == 1 in Article object
        $this->obArticle->status_id = 1;
        $this->obArticle->save();

        ArticleCollection::make()->published()->save(CategoryItem::class.'_published');
        $obParentItem = CategoryItem::make($this->obElement->id);
        $obItem = CategoryItem::make($this->obChildElement->id);

        self::assertEquals(0, $obParentItem->article_count);
        self::assertEquals(0, $obItem->article_count);


        //Set status_id == 4 in Article object
        $this->obArticle->status_id = 4;
        $this->obArticle->save();

        ArticleCollection::make()->published()->save(CategoryItem::class.'_published');
        $obParentItem = CategoryItem::make($this->obElement->id);
        $obItem = CategoryItem::make($this->obChildElement->id);

        self::assertEquals(1, $obParentItem->article_count);
        self::assertEquals(1, $obItem->article_count);

        //Set published_start == tomorrow Article object
        $this->obArticle->published_start = Argon::tomorrow();
        $this->obArticle->save();

        ArticleCollection::make()->published()->save(CategoryItem::class.'_published');
        $obParentItem = CategoryItem::make($this->obElement->id);
        $obItem = CategoryItem::make($this->obChildElement->id);

        self::assertEquals(0, $obParentItem->article_count);
        self::assertEquals(0, $obItem->article_count);

        //Set published_start == today Article object
        $this->obArticle->published_start = Argon::today();
        $this->obArticle->save();

        ArticleCollection::make()->published()->save(CategoryItem::class.'_published');
        $obParentItem = CategoryItem::make($this->obElement->id);
        $obItem = CategoryItem::make($this->obChildElement->id);

        self::assertEquals(1, $obParentItem->article_count);
        self::assertEquals(1, $obItem->article_count);

        //Delete Article object
        $this->obArticle->delete();

        ArticleCollection::make()->published()->save(CategoryItem::class.'_published');
        $obParentItem = CategoryItem::make($this->obElement->id);
        $obItem = CategoryItem::make($this->obChildElement->id);

        self::assertEquals(0, $obParentItem->article_count);
        self::assertEquals(0, $obItem->article_count);
    }

    /**
     * Create data for test
     */
    protected function createTestData()
    {
        //Create new element data
        $arCreatedData = $this->arCreateData;
        $arCreatedData['active'] = true;

        $this->obElement = Category::create($arCreatedData);

        $arCreatedData = $this->arCreateData;
        $arCreatedData['active'] = true;
        $arCreatedData['slug'] = 'slug1';

        $this->obChildElement = Category::create($arCreatedData);

        $this->obChildElement->parent_id = $this->obElement->id;
        $this->obChildElement->nest_depth = 1;
        $this->obChildElement->save();


        $arArticleData = $this->arArticleData;
        $arArticleData['category_id'] = $this->obChildElement->id;

        $arArticleData['published_start'] = Argon::today();

        $this->obArticle = Article::create($arArticleData);
    }
}
