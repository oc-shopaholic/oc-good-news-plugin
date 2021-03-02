<?php namespace Lovata\GoodNews\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * Class TableCreateAdditionProperties
 * @package Lovata\GoodNews\Updates
 */
class TableCreateAdditionProperties extends Migration
{
    const TABLE = 'lovata_good_news_addition_properties';

    /**
     * Apply migration
     */
    public function up()
    {
        if(Schema::hasTable(self::TABLE)) {
            return;
        }

        Schema::create(self::TABLE, function(Blueprint $obTable)
        {
            $obTable->engine = 'InnoDB';
            $obTable->increments('id');
            $obTable->boolean('active')->default(1);
            $obTable->string('name');
            $obTable->string('slug');
            $obTable->string('code')->nullable();
            $obTable->string('description')->nullable();
            $obTable->string('type')->default('input');
            $obTable->text('settings')->nullable();
            $obTable->integer('sort_order')->nullable();
            $obTable->timestamps();

            $obTable->index('name');
            $obTable->index('slug');
            $obTable->index('code');
            $obTable->index('sort_order');
        });
    }

    /**
     * Rollback migration
     */
    public function down()
    {
        Schema::dropIfExists(self::TABLE);
    }
}
