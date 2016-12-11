<?php namespace Lovata\GoodNews\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateLovataGoodNewsArticles extends Migration
{
    public function up()
    {
        Schema::create('lovata_goodnews_articles', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('title');
            $table->string('slug')->index();
            $table->integer('category_id')->nullable();
            $table->text('preview')->nullable();
            $table->text('content')->nullable();
            $table->boolean('published')->nullable()->default(1);
            $table->dateTime('published_start')->nullable();
            $table->dateTime('published_stop')->nullable();
            $table->boolean('top')->nullable()->default(0);
            $table->boolean('hot')->nullable()->default(0);
            $table->string('author')->nullable();
            $table->string('photo_author')->nullable();
            $table->timestamps();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('lovata_goodnews_articles');
    }
}