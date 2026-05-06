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
        Schema::create('tbl_protocols', function (Blueprint $table) {
            $table->bigIncrements('tbl_protocol_id');
            $table->string('tbl_protocol_title');
            $table->string('tbl_protocol_slug')->unique();
            $table->text('tbl_protocol_content');
            $table->json('tbl_protocol_tags')->nullable();
            $table->unsignedBigInteger('tbl_protocol_author_id');
            $table->decimal('tbl_protocol_avg_rating', 8, 2)->default(0);
            $table->unsignedInteger('tbl_protocol_reviews_count')->default(0);
            $table->integer('tbl_protocol_votes_count')->default(0);
            $table->timestamp('tbl_protocol_created_at')->nullable();
            $table->timestamp('tbl_protocol_updated_at')->nullable();

            $table->foreign('tbl_protocol_author_id')
                ->references('tbl_user_id')
                ->on('tbl_users')
                ->cascadeOnDelete();
        });

        Schema::create('tbl_threads', function (Blueprint $table) {
            $table->bigIncrements('tbl_thread_id');
            $table->unsignedBigInteger('tbl_thread_protocol_id');
            $table->unsignedBigInteger('tbl_thread_author_id');
            $table->string('tbl_thread_title');
            $table->text('tbl_thread_body');
            $table->json('tbl_thread_tags')->nullable();
            $table->integer('tbl_thread_votes_count')->default(0);
            $table->unsignedInteger('tbl_thread_comments_count')->default(0);
            $table->timestamp('tbl_thread_created_at')->nullable();
            $table->timestamp('tbl_thread_updated_at')->nullable();

            $table->foreign('tbl_thread_protocol_id')
                ->references('tbl_protocol_id')
                ->on('tbl_protocols')
                ->cascadeOnDelete();

            $table->foreign('tbl_thread_author_id')
                ->references('tbl_user_id')
                ->on('tbl_users')
                ->cascadeOnDelete();
        });

        Schema::create('tbl_comments', function (Blueprint $table) {
            $table->bigIncrements('tbl_comment_id');
            $table->unsignedBigInteger('tbl_comment_thread_id');
            $table->unsignedBigInteger('tbl_comment_author_id');
            $table->unsignedBigInteger('tbl_comment_parent_id')->nullable();
            $table->text('tbl_comment_body');
            $table->integer('tbl_comment_votes_count')->default(0);
            $table->timestamp('tbl_comment_created_at')->nullable();
            $table->timestamp('tbl_comment_updated_at')->nullable();

            $table->foreign('tbl_comment_thread_id')
                ->references('tbl_thread_id')
                ->on('tbl_threads')
                ->cascadeOnDelete();

            $table->foreign('tbl_comment_author_id')
                ->references('tbl_user_id')
                ->on('tbl_users')
                ->cascadeOnDelete();

            $table->foreign('tbl_comment_parent_id')
                ->references('tbl_comment_id')
                ->on('tbl_comments')
                ->nullOnDelete();
        });

        Schema::create('tbl_reviews', function (Blueprint $table) {
            $table->bigIncrements('tbl_review_id');
            $table->unsignedBigInteger('tbl_review_protocol_id');
            $table->unsignedBigInteger('tbl_review_author_id');
            $table->unsignedTinyInteger('tbl_review_rating');
            $table->text('tbl_review_feedback')->nullable();
            $table->timestamp('tbl_review_created_at')->nullable();
            $table->timestamp('tbl_review_updated_at')->nullable();

            $table->foreign('tbl_review_protocol_id')
                ->references('tbl_protocol_id')
                ->on('tbl_protocols')
                ->cascadeOnDelete();

            $table->foreign('tbl_review_author_id')
                ->references('tbl_user_id')
                ->on('tbl_users')
                ->cascadeOnDelete();

            $table->unique(
                ['tbl_review_protocol_id', 'tbl_review_author_id'],
                'tbl_reviews_protocol_author_unique'
            );
        });

        Schema::create('tbl_votes', function (Blueprint $table) {
            $table->bigIncrements('tbl_vote_id');
            $table->unsignedBigInteger('tbl_vote_user_id');
            $table->unsignedBigInteger('tbl_vote_votable_id');
            $table->string('tbl_vote_votable_type');
            $table->tinyInteger('tbl_vote_value');
            $table->timestamp('tbl_vote_created_at')->nullable();
            $table->timestamp('tbl_vote_updated_at')->nullable();

            $table->foreign('tbl_vote_user_id')
                ->references('tbl_user_id')
                ->on('tbl_users')
                ->cascadeOnDelete();

            $table->unique(
                ['tbl_vote_user_id', 'tbl_vote_votable_id', 'tbl_vote_votable_type'],
                'tbl_votes_user_votable_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_votes');
        Schema::dropIfExists('tbl_reviews');
        Schema::dropIfExists('tbl_comments');
        Schema::dropIfExists('tbl_threads');
        Schema::dropIfExists('tbl_protocols');
    }
};
