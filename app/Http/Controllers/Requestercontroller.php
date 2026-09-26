<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * 依頼者のログイン・新規登録を担当するコントローラー（パスワードあり）。
 *
 * Email + Password で本人確認を行うシンプルな認証。
 * Google/Facebook等の外部ログインやパスワードリセット機能は今回は実装しない。
 * 一度登録しておけば、次回以降はENTRY画面のログインだけで
 * 「依頼者名」「Email」の入力を省略できるようにするためのもの。
 * 所属（department）は異動を考慮し、ここでは持たせず毎回選び直す。
 */
class RequesterController extends Controller
{
    // 新規登録
    public function register(Request $request)
    {
        $data = $request->json()->all();
        $name = trim((string) ($data['name'] ?? ''));
        $email = trim((string) ($data['email'] ?? ''));
        $password = (string) ($data['password'] ?? '');

        if ($name === '' || $email === '' || $password === '') {
            return response()->json(['message' => '氏名・メールアドレス・パスワードを入力してください。'], 422);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['message' => '有効なメールアドレスを入力してください。'], 422);
        }
        if (mb_strlen($password) < 8) {
            return response()->json(['message' => 'パスワードは8文字以上で入力してください。'], 422);
        }

        $existing = DB::table('requesters')->where('email', $email)->first();
        if ($existing) {
            return response()->json(['message' => 'このメールアドレスは既に登録されています。'], 422);
        }

        $id = DB::table('requesters')->insertGetId([
            'name'     => $name,
            'email'    => $email,
            'password' => Hash::make($password),
        ]);

        return response()->json(['id' => $id, 'name' => $name, 'email' => $email], 201);
    }

    // ログイン
    public function login(Request $request)
    {
        $data = $request->json()->all();
        $email = trim((string) ($data['email'] ?? ''));
        $password = (string) ($data['password'] ?? '');

        if ($email === '' || $password === '') {
            return response()->json(['message' => 'メールアドレスとパスワードを入力してください。'], 422);
        }

        $user = DB::table('requesters')->where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return response()->json(['message' => 'メールアドレスまたはパスワードが正しくありません。'], 401);
        }

        return response()->json(['id' => $user->id, 'name' => $user->name, 'email' => $user->email]);
    }
}