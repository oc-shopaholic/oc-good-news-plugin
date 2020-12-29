<?php namespace Lovata\GoodNews\Tests\Unit\Models;

include_once __DIR__.'/../../../../../../tests/PluginTestCase.php';

use PluginTestCase;
use Lovata\GoodNews\Models\Category;
use Lovata\GoodNews\Models\Article;
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
}
