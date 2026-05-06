<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ProtocolController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ThreadController;
use App\Http\Controllers\TypesenseController;
use App\Http\Controllers\VoteController;
use Illuminate\Support\Facades\Route;

Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);

Route::get('protocols', [ProtocolController::class, 'index']);
Route::get('protocols/{protocol}', [ProtocolController::class, 'show']);

Route::middleware('optional.sanctum')->group(function (): void {
    Route::get('threads', [ThreadController::class, 'index']);
    Route::get('threads/{thread}', [ThreadController::class, 'show']);

    Route::get('comments', [CommentController::class, 'index']);
    Route::get('comments/{comment}', [CommentController::class, 'show']);
});

Route::get('reviews', [ReviewController::class, 'index']);
Route::get('reviews/{review}', [ReviewController::class, 'show']);

Route::get('search/protocols', [SearchController::class, 'protocols']);
Route::get('search/threads', [SearchController::class, 'threads']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me', [AuthController::class, 'me']);

    Route::post('protocols', [ProtocolController::class, 'store']);
    Route::put('protocols/{protocol}', [ProtocolController::class, 'update']);
    Route::delete('protocols/{protocol}', [ProtocolController::class, 'destroy']);

    Route::post('threads', [ThreadController::class, 'store']);
    Route::put('threads/{thread}', [ThreadController::class, 'update']);
    Route::delete('threads/{thread}', [ThreadController::class, 'destroy']);

    Route::post('comments', [CommentController::class, 'store']);
    Route::put('comments/{comment}', [CommentController::class, 'update']);
    Route::delete('comments/{comment}', [CommentController::class, 'destroy']);

    Route::post('reviews', [ReviewController::class, 'store']);
    Route::put('reviews/{review}', [ReviewController::class, 'update']);
    Route::delete('reviews/{review}', [ReviewController::class, 'destroy']);

    Route::post('votes', [VoteController::class, 'store']);

    Route::post('typesense/reindex', [TypesenseController::class, 'reindex']);
});
