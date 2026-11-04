<?php namespace Lovata\GoodNews\Tests\Unit\Models;

include_once __DIR__.'/../../../../../../tests/PluginTestCase.php';

use Carbon\Carbon;
use PluginTestCase;
use Lovata\GoodNews\Models\Article;
use Lovata\GoodNews\Models\Category;
use Lovata\GoodNews\Tests\Fixtures\ImageSetsTestTrait;
use Lovata\Toolbox\Models\ImageSet;

/**
 * Class ArticleTest
 * @package Lovata\GoodNews\Tests\Unit\Models
 * @author  Pavel Lautsevich, p.lautsevich@lovata.com, LOVATA Group
 *
 * @mixin \PHPUnit\Framework\Assert
 */
class ArticleTest extends PluginTestCase
{
    use ImageSetsTestTrait;

    protected $sModelClass;

    /**
     * ArticleTest constructor.
     */
    public function __construct()
    {
        $this->sModelClass = Article::class;
        parent::__construct();
    }

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
     * Check model "category" relation config
     */
    public function testHasCategoryRelation()
    {
        $sErrorMessage = $this->sModelClass.' model has not correct "category" relation config';

        /** @var Article $obModel */
        $obModel = new Article();
        self::assertArrayHasKey('category', $obModel->belongsTo, $sErrorMessage);
        self::assertEquals([Category::class], $obModel->belongsTo['category'], $sErrorMessage);
    }

    /**
     * Check model "image_sets" relation config
     */
    public function testHasImageSetsRelation()
    {
        $sErrorMessage = $this->sModelClass.' model has not correct "image_sets" relation config';

        /** @var Article $obModel */
        $obModel = new Article();
        self::assertArrayHasKey('image_sets', $obModel->morphMany, $sErrorMessage);
        self::assertEquals([ImageSet::class, 'name' => 'imageable', 'delete' => true], $obModel->morphMany['image_sets'], $sErrorMessage);
        self::assertContains('image_sets', $obModel->cached, $sErrorMessage);
    }

    /**
     * Check that "image_sets" relation works
     */
    public function testImageSetsRelation()
    {
        $obArticle = $this->createArticle();
        $obImageSet = $this->createImageSet($obArticle, $this->createProfile('Tablet', 768), ['first.jpg', 'second.jpg']);

        $obArticle = Article::find($obArticle->id);
        self::assertCount(1, $obArticle->image_sets);
        self::assertEquals($obImageSet->id, $obArticle->image_sets->first()->id);
        self::assertEquals(['first.jpg', 'second.jpg'], $obArticle->image_sets->first()->images->pluck('file_name')->all());

        $obImageSet = ImageSet::find($obImageSet->id);
        self::assertInstanceOf(Article::class, $obImageSet->imageable);
        self::assertEquals($obArticle->id, $obImageSet->imageable->id);
    }

    /**
     * Image set created before article is saved, is attached to article with deferred binding
     */
    public function testImageSetsDeferredBinding()
    {
        $sSessionKey = uniqid('session_key', true);

        $obImageSet = new ImageSet();
        $obImageSet->profile_id = $this->createProfile('Tablet', 768)->id;
        $obImageSet->save();
        $obImageSet->images()->add($this->createFile('first.jpg'));

        $obArticle = new Article();
        $obArticle->title = 'Article';
        $obArticle->slug = 'article';
        $obArticle->published_start = Carbon::today();
        $obArticle->image_sets()->add($obImageSet, $sSessionKey);
        $obArticle->save(null, $sSessionKey);

        $obImageSet = ImageSet::find($obImageSet->id);
        self::assertEquals(Article::class, $obImageSet->imageable_type);
        self::assertEquals($obArticle->id, $obImageSet->imageable_id);
        self::assertCount(1, $obImageSet->images);
    }

    /**
     * Soft delete keeps image sets and files, restore returns them
     */
    public function testSoftDeleteKeepsImageSets()
    {
        $obArticle = $this->createArticle();
        $obImageSet = $this->createImageSet($obArticle, $this->createProfile('Tablet', 768), ['first.jpg', 'second.jpg']);
        $arFileIDList = $obImageSet->images->pluck('id')->all();
        $arFilePathList = $this->getFilePathList($obImageSet);

        $obArticle->delete();
        self::assertNull(Article::find($obArticle->id));
        $this->assertImageSetExists($obImageSet, $arFileIDList, $arFilePathList);

        Article::withTrashed()->find($obArticle->id)->restore();
        $obArticle = Article::find($obArticle->id);
        self::assertNotNull($obArticle);
        self::assertCount(1, $obArticle->image_sets);
        self::assertCount(2, $obArticle->image_sets->first()->images);
        $this->assertImageSetExists($obImageSet, $arFileIDList, $arFilePathList);
    }

    /**
     * Permanent delete removes image sets and files: directly and from trash
     */
    public function testForceDeleteRemovesImageSets()
    {
        $obProfile = $this->createProfile('Tablet', 768);
        $obArticle = $this->createArticle();
        $obTrashedArticle = $this->createArticle('Trashed');
        $obOtherArticle = $this->createArticle('Other');

        $obImageSet = $this->createImageSet($obArticle, $obProfile, ['first.jpg', 'second.jpg']);
        $obTrashedImageSet = $this->createImageSet($obTrashedArticle, $obProfile, ['third.jpg']);
        $obOtherImageSet = $this->createImageSet($obOtherArticle, $obProfile);

        $arFileIDList = $obImageSet->images->pluck('id')->all();
        $arFilePathList = $this->getFilePathList($obImageSet);
        $obArticle->forceDelete();
        $this->assertImageSetRemoved($obImageSet, $arFileIDList, $arFilePathList);

        $arFileIDList = $obTrashedImageSet->images->pluck('id')->all();
        $arFilePathList = $this->getFilePathList($obTrashedImageSet);
        $obTrashedArticle->delete();
        Article::withTrashed()->find($obTrashedArticle->id)->forceDelete();
        $this->assertImageSetRemoved($obTrashedImageSet, $arFileIDList, $arFilePathList);

        self::assertNotNull(ImageSet::find($obOtherImageSet->id));
        self::assertCount(1, ImageSet::find($obOtherImageSet->id)->images);
    }

    /**
     * Create article
     * @param string $sName
     * @return Article
     */
    protected function createArticle($sName = 'Article')
    {
        return Article::create([
            'title'           => $sName,
            'slug'            => 'article-'.uniqid(),
            'published_start' => Carbon::today(),
        ]);
    }
}
