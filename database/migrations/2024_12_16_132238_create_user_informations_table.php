<?php

use App\Core\Cli\Migration;
use App\Core\Cli\Schema;

class CreateUserInformationsTable extends Migration
{
    public function up()
    {
        Schema::create('user_informations', function ($table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('contact');
            $table->string('address');
            $table->string('occupation');
            $table->string('civil_status');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_informations');
    }
}
