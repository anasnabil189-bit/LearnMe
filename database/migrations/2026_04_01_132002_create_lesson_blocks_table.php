<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained('lessons')->onDelete('cascade');
            $table->enum('type', ['text', 'image']);
            $table->longText('content')->nullable(); // for text blocks
            $table->string('path')->nullable();      // for image blocks
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // Migrate existing lesson content into text blocks
        if (Schema::hasTable('lessons') && Schema::hasColumn('lessons', 'content')) {
            $lessons = \DB::table('lessons')->whereNotNull('content')->where('content', '!=', '')->get();
            foreach ($lessons as $lesson) {
                \DB::table('lesson_blocks')->insert([
                    'lesson_id'  => $lesson->id,
                    'type'       => 'text',
                    'content'    => $lesson->content,
                    'path'       => null,
                    'order'      => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Migrate existing lesson_images into image blocks
        if (Schema::hasTable('lesson_images')) {
            $images = \DB::table('lesson_images')->orderBy('lesson_id')->orderBy('order')->get();
            foreach ($images as $img) {
                // Get max order for this lesson's blocks already inserted
                $maxOrder = \DB::table('lesson_blocks')->where('lesson_id', $img->lesson_id)->max('order') ?? 0;
                \DB::table('lesson_blocks')->insert([
                    'lesson_id'  => $img->lesson_id,
                    'type'       => 'image',
                    'content'    => null,
                    'path'       => $img->path,
                    'order'      => $maxOrder + 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_blocks');
    }
};
