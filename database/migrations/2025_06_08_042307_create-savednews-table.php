<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('saved_news', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('news_id')->constrained('NewsScrapping')->onDelete('cascade');
            $table->timestamp('saved_at')->useCurrent();
            $table->unique(['user_id', 'news_id']);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('saved_news');
    }
};
