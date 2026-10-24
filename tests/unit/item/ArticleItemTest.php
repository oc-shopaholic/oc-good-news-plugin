<?php namespace Lovata\GoodNews\Tests\Unit\Item;

use Carbon\Carbon;
use Lovata\Toolbox\Tests\CommonTest;

use Lovata\GoodNews\Models\Article;
use Lovata\GoodNews\Classes\Item\ArticleItem;
use Lovata\GoodNews\Tests\Fixtures\ImageSetsTestTrait;
use Lovata\GoodNews\Tests\Fixtures\ImageSetsItemTestTrait;

/**
 * Class ArticleItemTest
 * @package Lovata\GoodNews\Tests\Unit\Item
 * @author  Pavel Lautsevich, p.lautsevich@lovata.com, LOVATA Group
 *
 * @mixin \PHPUnit\Framework\Assert
 */
class ArticleItemTest extends CommonTest
{
    use ImageSetsTestTrait;
    use ImageSetsItemTestTrait;

    protected $sImageSetsItemClass = ArticleItem::class;

    /** @var  Article */
    protected $obElement;

    protected $arCreateData = [
        'status_id'       => 4,
        'title'           => 'title',
        'slug'            => 'slug',
        'preview_text'    => 'preview_text',
        'content'         => 'content',
        'seo_title'       => 'seo_title',
        'seo_keywords'    => 'seo_keywords',
        'seo_description' => 'seo_description',
        'view_count'      => 0,
    ];

    /**
     * Set up test method
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->setUpImageSets();
    }

    /**
     * Tear down test method
     */
    public function tearDown(): void
    {
        $this->tearDownImageSets();
        parent::tearDown();
    }

    /**
     * Check item fields
     */
    public function testItemFields()
    {
        $this->createTestData();

        $sErrorMessage = 'Article item data is not correct';

        $arCreatedData = $this->arCreateData;
        $arCreatedData['id'] = $this->obElement->id;

        //Check item fields
        $obItem = ArticleItem::make($this->obElement->id);
        foreach ($arCreatedData as $sField => $sValue) {
            self::assertEquals($sValue, $obItem->$sField, $sErrorMessage);
        }

        self::assertSame([], $obItem->image_sets, $sErrorMessage);
    }

    /**
     * Check update cache item data, after update model data
     */
    public function testItemClearCache()
    {
        $this->createTestData();

        $sErrorMessage = 'Article item data is not correct, after model update';

        $obItem = ArticleItem::make($this->obElement->id);
        self::assertEquals('title', $obItem->title, $sErrorMessage);

        //Check cache update
        $this->obElement->title = 'test';
        $this->obElement->save();

        $obItem = ArticleItem::make($this->obElement->id);
        self::assertEquals('test', $obItem->title, $sErrorMessage);
    }

    /**
     * Create data for test
     */
    protected function createTestData()
    {
        $arCreatedData = $this->arCreateData;
        $arCreatedData['published_start'] = Carbon::today();

        $this->obElement = Article::create($arCreatedData);
    }

    /**
     * Create element for image sets tests
     * @param string $sName
     * @return Article
     */
    protected function createImageSetsElement($sName = 'Element')
    {
        return Article::create([
            'status_id'       => Article::STATUS_PUBLISHED,
            'title'           => $sName,
            'slug'            => str_slug($sName),
            'published_start' => Carbon::today(),
        ]);
    }
}
