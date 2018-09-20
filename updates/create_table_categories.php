<?php namespace Lovata\GoodNews\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * Class CreateTableCategories
 * @package Lovata\GoodNews\Updates
 */
class CreateTableCategories extends Migration
{
    const TABLE_NAME = 'lovata_good_news_categories';

    /**
     * Apply migration
     */
    public function up()
    {
        if (Schema::hasTable(self::TABLE_NAME)) {
            return;
        }

        Schema::create(self::TABLE_NAME, function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->boolean('active')->nullable()->default(1);
            $table->string('name');
            $table->string('slug');
            $table->string('code')->nullable();
            $table->text('preview_text')->nullable();
            $table->text('description')->nullable();
            $table->integer('parent_id')->nullable()->unsigned();
            $table->integer('nest_left')->nullable()->unsigned();
            $table->integer('nest_right')->nullable()->unsigned();
            $table->integer('nest_depth')->nullable()->unsigned();

            $table->index('name');
            $table->index('slug');
            $table->index('code');

            $table->timestamps();
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
