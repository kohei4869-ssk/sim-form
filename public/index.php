<?php

//index.phpの役割：「ブラウザからのリクエストを受け取ったら、Laravelを起動して、あとの判断（どのルート？どのコントローラー？）は全部Laravel本体に丸投げする」//

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

//現在が何時何分何秒か？定義。リクエスト受理→レスポンス返却までの時間を計測するために使用する
define('LARAVEL_START', microtime(true));

// file_exists: 指定したファイルが「実際に存在するかどうか」をチェックするPHPの関数
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

//必要なファイルを自動で探し出せるように、索引表を読み込んでおく
require __DIR__.'/../vendor/autoload.php';
//__DIR__:  そのファイルの存在するディレクトリ
//（/.. は「1つ上のフォルダへ戻る」の意味）:今開いているindex.phpの1つ上の my-app フォルダに戻ってから vendor/autoload.php を指定

//$appはApplicationクラスのオブジェクト＝「Laravelの設計図どおりに作られた本物のエンジンが入っているよ」
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';
//require_once ≒require: そのファイルを読み込む。すでに読み込まれていたら、もう一度読み込まない

$app->handleRequest(Request::capture());
//Request::capture()：ブラウザ（ユーザー）からのリクエストを全部キャッチして
//$app->handleRequest()：集めたアクセス情報をLaravelエンジン（$app）に渡して、あとの処理（ルーティングやコントローラー）は全部よろしく
