<?php

//動詞(取り込んで)→住所(ここから探してきて)→目的語(このクラスを使って)
use Illuminate\Support\Facades\Route; //ルーティング機能というクラスを取り込む
use App\Http\Controllers\SubmitController; //コントローラー機能というクラスを取り込む

Route::get('/', function () {
    return view('welcome');
});
// Route::get：Route機能よ、GETアクセスを受け付けなさい。
// '/' ：アクセスされたURLのパス部分が「/」だったら、
// function () { この中の処理を実行しなさい。
// return view('welcome'); } ：「welcome.blade.phpを表示して

Route::get('/test', function () {
    return 'ルート、動いてます！';
});

Route::get('/submit-test', [SubmitController::class, 'store']);
//「/submit-test というURLにアクセスが来たら、SubmitController の中にある store という処理を実行しなさい

Route::get('/departments/{slug}', [SubmitController::class, 'department']);
//「/departments/〇〇 というURLにアクセスが来たら、SubmitController の中にある department という処理を実行しなさい

Route::post('/api/send-mail', [SubmitController::class, 'sendMail']);