<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFiles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('parent_id')->nullable()->index();
            $table->string('name')->nullable(false)->index();
            $table->string('type')->nullable(false);
            $table->string('external_id')->nullable()->index();
            $table->uuid('uuid')->nullable(false)->unique()->index();
            $table->integer('user_id')->nullable(false);
            $table->jsonb('payload')->nullable();
            $table->unique(['parent_id', 'name','type']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('files');
    }
}
