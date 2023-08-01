<?php namespace Lovata\GoodNews\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * @package Lovata\Shopaholic\Updates
 */
class CreateTableCategorySiteRelation extends Migration
{
    const TABLE_NAME = 'lovata_goodnews_category_site_relation';

    /**
     * Apply migration
     */
    public function up()
    {
        if (Schema::hasTable(self::TABLE_NAME)) {
            return;
        }

        Schema::create(self::TABLE_NAME, function (Blueprint $obTable) {
            $obTable->engine = 'InnoDB';
            $obTable->integer('category_id')->unsigned();
            $obTable->integer('site_id')->unsigned();
            $obTable->primary(['category_id', 'site_id'], 'category_site');
        });
    }

    /**
     * Rollback migration
     */
    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
