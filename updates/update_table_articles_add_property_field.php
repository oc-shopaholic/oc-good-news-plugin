<?php

namespace Lovata\GoodNews\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * Class UpdateTableArticlesAddPropertyField
 * @package Lovata\GoodNews\Updates
 */
class UpdateTableArticlesAddPropertyField extends Migration
{
    const TABLE = 'lovata_good_news_articles';
    /**
     * Apply migration
     */
    public function up()
    {
        if (Schema::hasTable(self::TABLE) && !Schema::hasColumn(self::TABLE, 'property')) {
            Schema::table(self::TABLE, function (Blueprint $obTable) {
                $obTable->mediumText('property')->nullable();
            });
        }
    }

    /**
     * Rollback migration
     */
    public function down()
    {
        if (Schema::hasTable(self::TABLE) && Schema::hasColumn(self::TABLE, 'property')) {
            Schema::table(self::TABLE, function (Blueprint $obTable) {
                $obTable->dropColumn(['property']);
            });
        }
    }
}
