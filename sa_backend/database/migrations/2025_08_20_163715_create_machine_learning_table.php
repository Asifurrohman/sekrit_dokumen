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
        Schema::create('machine_learnings', function (Blueprint $table) {
            $table->id();
            $table->string('model_name');
            $table->integer('total_data')->default(0);
            $table->float('accuracy', 8, 4)->nullable();
            $table->json('precision')->nullable();      
            $table->json('recall')->nullable();
            $table->json('f1_score')->nullable();
            $table->json('confusion_matrix')->nullable();
            $table->json('top_words')->nullable();
            
            $table->timestamps();
        });
    }
    
    /**
    * Reverse the migrations.
    */
    public function down(): void
    {
        Schema::dropIfExists('machine_learnings');
    }
};
