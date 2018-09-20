<?php namespace Lovata\GoodNews\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * Class CreateTableArticles
 * @package Lovata\GoodNews\Updates
 */
class CreateTableArticles extends Migration
{
    const TABLE_NAME = 'lovata_good_news_articles';

    /**
     * Apply migration
     */
    public function up()
    {
        if(Schema::hasTable(self::TABLE_NAME)) {
            return;
        }
        
        Schema::create(self::TABLE_NAME, function(Blueprint $table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('status_id')->nullable();
            $table->string('title');
            $table->string('slug');
            $table->integer('view_count')->default(0)->unsigned();
            $table->integer('category_id')->nullable();
            $table->text('preview_text')->nullable();
            $table->text('content')->nullable();
            $table->dateTime('published_start')->nullable();
            $table->dateTime('published_stop')->nullable();
            $table->timestamps();

            $table->index('title');
            $table->index('slug');
            $table->index('status_id');
            $table->index('category_id');
            $table->index('published_start');
            $table->index('published_stop');
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