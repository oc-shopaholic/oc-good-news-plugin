<?php namespace Lovata\GoodNews\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * Class UpdateTableArticlesAddCodeField
 * @package Lovata\GoodNews\Updates
 */
class UpdateTableArticlesAddCodeField extends Migration
{
    /**
     * Apply migration
     */
    public function up()
    {
        if (Schema::hasTable('lovata_good_news_articles')) {

            Schema::table('lovata_good_news_articles', function (Blueprint $obTable) {
                $obTable->string('code')->nullable();
                $obTable->index('code');
            });
        }
    }

    /**
     * Rollback migration
     */
    public function down()
    {
        if (Schema::hasTable('lovata_good_news_articles')) {
            Schema::table('lovata_good_news_articles', function (Blueprint $obTable) {
                $obTable->dropColumn(['code']);
            });
        }
    }
}
