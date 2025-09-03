<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cleaned_datasets', function (Blueprint $table) {
            $table->id();
            $table->text('raw_tweet')->unique();
            $table->text('casefolded_tweet')->nullable();
            $table->text('semi_cleaned_tweet')->nullable();
            $table->text('cleansed_tweet')->nullable();
            $table->text('fixedwords_tweet')->nullable();
            $table->text('stopwordremoved_tweet')->nullable();
            $table->text('stemmed_tweet')->nullable();
            $table->text('fully_cleaned_tweet')->nullable();
            $table->string('language')->nullable();
            $table->string('classification', 20)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cleaned_datasets');
    }
};
