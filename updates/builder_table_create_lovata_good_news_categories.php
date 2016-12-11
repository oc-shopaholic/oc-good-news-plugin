<?php namespace Lovata\GoodNews\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateLovataGoodNewsCategories extends Migration
{
    public function up()
    {
        Schema::create('lovata_goodnews_categories', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->boolean('active')->nullable()->default(1);
            $table->string('name');
            $table->string('slug')->index();
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->integer('parent_id')->nullable()->unsigned();
            $table->integer('nest_left')->nullable()->unsigned();
            $table->integer('nest_right')->nullable()->unsigned();
            $table->integer('nest_depth')->nullable()->unsigned();
            $table->timestamps();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('lovata_goodnews_categories');
    }
}