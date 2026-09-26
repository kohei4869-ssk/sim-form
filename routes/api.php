<?php

//api.php:

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SubmitController;
use App\Http\Controllers\RequesterController;

// データ送信
// Postリクエストでデータが送られてきたら、SubmitController の store メソッド（新規保存処理）を実行する
Route::post('/submit', [SubmitController::class, 'store']);

// ステータス変更
Route::post('/campaigns/{id}/status', [SubmitController::class, 'updateStatus']);

// パターン保存
Route::get('/campaigns/{id}/patterns', [SubmitController::class, 'patternsJson']);

// 見積もり保存
Route::post('/patterns/{id}/estimate', [SubmitController::class, 'saveEstimate']);

// 担当者変更
Route::post('/campaigns/{id}/assignee', [SubmitController::class, 'updateAssignee']);

//Route::get: データを「取得する（読む）」ためのアクセス(例：一覧の取得)
//Route::post: データを「送信する・保存する・更新する」ためのアクセス（例：フォーム送信、ステータス変更）

// 依頼者ログイン・新規登録（パスワードあり）
Route::post('/requesters/register', [RequesterController::class, 'register']);
Route::post('/requesters/login', [RequesterController::class, 'login']);