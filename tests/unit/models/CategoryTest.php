<?php namespace Lovata\GoodNews\Tests\Unit\Models;

include_once __DIR__.'/../../../../../../tests/PluginTestCase.php';

use PluginTestCase;
use Lovata\GoodNews\Models\Category;
use Lovata\GoodNews\Models\Article;
use Lovata\GoodNews\Tests\Fixtures\ImageSetsTestTrait;
use Lovata\Toolbox\Models\ImageSet;
use Lovata\Toolbox\Traits\Tests\TestModelHasImages;
use Lovata\Toolbox\Traits\Tests\TestModelHasPreviewImage;
use Lovata\Toolbox\Traits\Tests\TestModelValidationNameField;
use Lovata\Toolbox\Traits\Tests\TestModelValidationSlugField;

/**
 * Class CategoryTest
 * @package Lovata\GoodNews\Tests\Unit\Models
 * @author Andrey Kharanenka, a.khoronenko@lovata.com, LOVATA Group
 *
 * @mixin \PHPUnit\Framework\Assert
 */
class CategoryTest extends PluginTestCase
{
    use TestModelHasPreviewImage;
    use TestModelHasImages;

    use TestModelValidationNameField;
    use TestModelValidationSlugField;

    use ImageSetsTestTrait;

    protected $sModelClass;

    /**
     * CategoryTest constructor.
     */
    public function __construct()
    {
        $this->sModelClass = Category::class;
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
     * Check model "article" relation config
     */
    public function testHasArticleRelation()
    {
        $sErrorMessage = $this->sModelClass.' model has not correct "article" relation config';

        /** @var Category $obModel */
        $obModel = new Category();
        self::assertNotEmpty($obModel->hasMany, $sErrorMessage);
        self::assertArrayHasKey('article', $obModel->hasMany, $sErrorMessage);
        self::assertEquals(Article::class, $obModel->hasMany['article'], $sErrorMessage);
    }

    /**
     * Check model "image_sets" relation config
     */
    public function testHasImageSetsRelation()
    {
        $sErrorMessage = $this->sModelClass.' model has not correct "image_sets" relation config';

        /** @var Category $obModel */
        $obModel = new Category();
        self::assertArrayHasKey('image_sets', $obModel->morphMany, $sErrorMessage);
        self::assertEquals([ImageSet::class, 'name' => 'imageable', 'delete' => true], $obModel->morphMany['image_sets'], $sErrorMessage);
        self::assertContains('image_sets', $obModel->cached, $sErrorMessage);
    }

    /**
     * Check that "image_sets" relation works
     */
    public function testImageSetsRelation()
    {
        $obCategory = $this->createCategory();
        $obImageSet = $this->createImageSet($obCategory, $this->createProfile('Tablet', 768), ['first.jpg', 'second.jpg']);

        $obCategory = Category::find($obCategory->id);
        self::assertCount(1, $obCategory->image_sets);
        self::assertEquals($obImageSet->id, $obCategory->image_sets->first()->id);
        self::assertEquals(['first.jpg', 'second.jpg'], $obCategory->image_sets->first()->images->pluck('file_name')->all());

        $obImageSet = ImageSet::find($obImageSet->id);
        self::assertInstanceOf(Category::class, $obImageSet->imageable);
        self::assertEquals($obCategory->id, $obImageSet->imageable->id);
    }

    /**
     * Image set created before category is saved, is attached to category with deferred binding
     */
    public function testImageSetsDeferredBinding()
    {
        $sSessionKey = uniqid('session_key', true);

        $obImageSet = new ImageSet();
        $obImageSet->profile_id = $this->createProfile('Tablet', 768)->id;
        $obImageSet->save();
        $obImageSet->images()->add($this->createFile('first.jpg'));

        $obCategory = new Category();
        $obCategory->name = 'Category';
        $obCategory->slug = 'category';
        $obCategory->image_sets()->add($obImageSet, $sSessionKey);
        $obCategory->save(null, $sSessionKey);

        $obImageSet = ImageSet::find($obImageSet->id);
        self::assertEquals(Category::class, $obImageSet->imageable_type);
        self::assertEquals($obCategory->id, $obImageSet->imageable_id);
        self::assertCount(1, $obImageSet->images);
    }

    /**
     * Delete removes image sets and files of category, sets of other categories are kept
     */
    public function testDeleteRemovesImageSets()
    {
        $obProfile = $this->createProfile('Tablet', 768);
        $obCategory = $this->createCategory();
        $obOtherCategory = $this->createCategory('Other');

        $obImageSet = $this->createImageSet($obCategory, $obProfile, ['first.jpg', 'second.jpg']);
        $obOtherImageSet = $this->createImageSet($obOtherCategory, $obProfile);

        $arFileIDList = $obImageSet->images->pluck('id')->all();
        $arFilePathList = $this->getFilePathList($obImageSet);
        $obCategory->delete();
        $this->assertImageSetRemoved($obImageSet, $arFileIDList, $arFilePathList);

        self::assertNotNull(ImageSet::find($obOtherImageSet->id));
        self::assertCount(1, ImageSet::find($obOtherImageSet->id)->images);
    }

    /**
     * Delete of parent category removes image sets and files of all child categories, sets of other categories are kept
     */
    public function testDeleteParentRemovesChildImageSets()
    {
        $obProfile = $this->createProfile('Tablet', 768);
        $obParentCategory = $this->createCategory('Parent');
        $obChildCategory = $this->createCategory('Child', $obParentCategory);
        $obNestedCategory = $this->createCategory('Nested', $obChildCategory);
        $obOtherCategory = $this->createCategory('Other');
        $obOtherChildCategory = $this->createCategory('Other child', $obOtherCategory);

        $arImageSetList = [
            $this->createImageSet($obParentCategory, $obProfile, ['parent.jpg']),
            $this->createImageSet($obChildCategory, $obProfile, ['child-1.jpg', 'child-2.jpg']),
            $this->createImageSet($obNestedCategory, $obProfile, ['nested.jpg']),
        ];
        $obOtherImageSet = $this->createImageSet($obOtherCategory, $obProfile, ['other.jpg']);
        $obOtherChildImageSet = $this->createImageSet($obOtherChildCategory, $obProfile, ['other-child.jpg']);

        $arRemovedList = [];
        foreach ($arImageSetList as $obImageSet) {
            $arRemovedList[] = [$obImageSet, $obImageSet->images->pluck('id')->all(), $this->getFilePathList($obImageSet)];
        }

        Category::find($obParentCategory->id)->delete();

        self::assertNull(Category::find($obChildCategory->id));
        self::assertNull(Category::find($obNestedCategory->id));
        foreach ($arRemovedList as $arRemovedData) {
            list($obImageSet, $arFileIDList, $arFilePathList) = $arRemovedData;
            $this->assertImageSetRemoved($obImageSet, $arFileIDList, $arFilePathList);
        }

        foreach ([$obOtherImageSet, $obOtherChildImageSet] as $obImageSet) {
            $this->assertImageSetExists($obImageSet, $obImageSet->images->pluck('id')->all(), $this->getFilePathList($obImageSet));
        }
    }

    /**
     * Create category
     * @param string        $sName
     * @param Category|null $obParentCategory
     * @return Category
     */
    protected function createCategory($sName = 'Category', $obParentCategory = null)
    {
        $obCategory = Category::create([
            'name' => $sName,
            'slug' => 'category-'.uniqid(),
        ]);

        if (!empty($obParentCategory)) {
            $obCategory->parent_id = $obParentCategory->id;
            $obCategory->save();
        }

        return $obCategory;
    }
}
