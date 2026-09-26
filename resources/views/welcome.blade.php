<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIM発行依頼フォーム</title>

    <!-- ① Viteでビルドしたapp.cssとapp.jsを、このHTMLに埋め込んでね」という意味 -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <!-- ② 空の箱を用意する。「Vueが後から乗っ取る場所」の目印 -->
    <div id="app">
        <!-- ③ Vueで登録したコンポーネントを配置 -->
        <sim-form></sim-form>
    </div>
</body>
</html>