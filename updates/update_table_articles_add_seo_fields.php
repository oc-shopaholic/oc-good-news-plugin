<?php

namespace Lovata\GoodNews\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * Class UpdateTableArticlesAddSeoFields
 * @package Lovata\GoodNews\Updates
 */
class UpdateTableArticlesAddSeoFields extends Migration
{
    const TABLE_NAME = 'lovata_good_news_articles';

    protected $arNewFieldList = [
        'seo_title',
        'seo_keywords',
        'seo_description',
    ];

    /**
     * Apply migration
     */
    public function up()
    {
        if (!Schema::hasTable(self::TABLE_NAME)) {
            return;
        }

        foreach ($this->arNewFieldList as $iKey => $sFieldName) {
            if (Schema::hasColumn(self::TABLE_NAME, $sFieldName)) {
                unset($this->arNewFieldList[$iKey]);
            }
        }

        if (empty($this->arNewFieldList)) {
            return;
        }

        Schema::table(self::TABLE_NAME, function (Blueprint $obTable) {
            foreach ($this->arNewFieldList as $sFieldName) {
                if ($sFieldName === 'seo_title') {
                    $obTable->string($sFieldName)->nullable()->after('content');
                } else {
                    $obTable->mediumText($sFieldName)->nullable()->after('seo_title');
                }
            }
        });
    }

    /**
     * Rollback migration
     */
    public function down()
    {
        if (!Schema::hasTable(self::TABLE_NAME)) {
            return;
        }

        foreach ($this->arNewFieldList as $iKey => $sFieldName) {
            if (!Schema::hasColumn(self::TABLE_NAME, $sFieldName)) {
                unset($this->arNewFieldList[$iKey]);
            }
        }

        if (empty($this->arNewFieldList)) {
            return;
        }

        Schema::table(self::TABLE_NAME, function (Blueprint $obTable) {
            $obTable->dropColumn($this->arNewFieldList);
        });
    }
}
