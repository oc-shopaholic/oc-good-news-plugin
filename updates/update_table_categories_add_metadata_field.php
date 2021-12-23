<?php

namespace Lovata\GoodNews\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * Class UpdateTableCategoriesAddMetadataField
 * @package Lovata\GoodNews\Updates
 */
class UpdateTableCategoriesAddMetadataField extends Migration
{
    const TABLE_NAME = 'lovata_good_news_categories';
    const FIELD_NAME = 'metadata';

    /**
     * Apply migration
     */
    public function up()
    {
        if (!Schema::hasTable(self::TABLE_NAME) || Schema::hasColumn(self::TABLE_NAME, self::FIELD_NAME)) {
            return;
        }

        Schema::table(self::TABLE_NAME, function (Blueprint $obTable) {
            $obTable->text(self::FIELD_NAME)->nullable()->after('description');
        });
    }

    /**
     * Rollback migration
     */
    public function down()
    {
        if (!Schema::hasTable(self::TABLE_NAME) || !Schema::hasColumn(self::TABLE_NAME, self::FIELD_NAME)) {
            return;
        }

        Schema::table(self::TABLE_NAME, function (Blueprint $obTable) {
            $obTable->dropColumn([self::FIELD_NAME]);
        });
    }
}
