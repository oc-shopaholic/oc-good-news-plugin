<?php namespace Lovata\GoodNews\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateTableCategories extends Migration
{
    public function up()
    {
        if(Schema::hasTable('lovata_good_news_categories')) {
            return;
        }
        
        Schema::create('lovata_good_news_categories', function(Blueprint $table)
        {
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
    
    public function down()
    {
        Schema::dropIfExists('lovata_good_news_categories');
    }
}