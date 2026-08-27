<?php

namespace App\Controllers;

class Auth extends BaseController
{
    // Googleログイン画面表示
    public function google()
    {
        $client = new \Google\Client(); //Google OAuthを操作するためのオブジェクト生成
        //.envの情報を取得
        $client->setClientId(env('GOOGLE_CLIENT_ID')); //.env に記述したIDを読み込む
        $client->setClientSecret(env('GOOGLE_CLIENT_SECRET')); //.env に記述したクライアントシークレットを読み込む
        $client->setRedirectUri(env('GOOGLE_REDIRECT_URI')); //.env に記述したURLを読み込む（ログイン成功後の戻り先）
        //取得したい情報指定
        $client->addScope('email'); //メールアドレスを取得したい
        $client->addScope('profile'); //名前やアイコンを取得したい
        //毎回アカウント選択画面が出やすくなります(本番では消す)
        $client->setPrompt('select_account');
        //Googleへリダイレクト
        return redirect()->to($client->createAuthUrl()); //$client->createAuthUrl()がリンクを生成
    }

    // ログイン後の画面表示
    public function callback()
    {
        $code = $this->request->getGet('code'); //認証コード取得
        //code が無ければエラー
        if (!$code)
        {
            return 'Googleログインに失敗しました';
        }
        $client = new \Google\Client(); //Google OAuthを操作するためのオブジェクト生成
        //.envの情報を取得
        $client->setClientId(env('GOOGLE_CLIENT_ID')); //.env に記述したIDを読み込む
        $client->setClientSecret(env('GOOGLE_CLIENT_SECRET')); //.env に記述したクライアントシークレットを読み込む
        $client->setRedirectUri(env('GOOGLE_REDIRECT_URI')); //.env に記述したURLを読み込む（ログイン成功後の戻り先）
        // アクセストークン設定
        $token = $client->fetchAccessTokenWithAuthCode($code); //認証コードをアクセストークンへ変換
        $client->setAccessToken($token); //アクセストークン設定
        //OAuthサービス生成
        $oauth = new \Google\Service\Oauth2($client);
        $user_info = $oauth->userinfo->get(); //ユーザー情報取得
        //情報表示
        echo '<h2>Googleログイン成功</h2>';
        // echo 'Google ID : ' . $user_info->id . '<br>';
        // echo '名前 : ' . $user_info->name . '<br>';
        // echo 'メール : ' . $user_info->email . '<br>';

        // Google ID が一致するユーザーを users テーブルから探す
        $google_sub = $user_info->id;
        $users_model = model(\App\Models\Users_model::class);
        $get_users = $users_model->get_users_google_sub($google_sub);
        if($get_users === false)
        {
            log_message('error', 'DB接続失敗: get_users_google_sub');
            show_error('エラー', 200, '');
        }
        $google_user_data['google_sub'] = $user_info['id'];
        $google_user_data['name'] = $user_info['name'];
        $google_user_data['email'] = $user_info['email'];
        
        if ($get_users === null)
        {
            // 初回ログイン：users に登録
            $user_id = $users_model->insert_google_user($google_user_data);
            if($user_id === false)
            {
                log_message('error', 'DB接続失敗: insert_google_user');
                show_error('エラー', 200, '');
            }
            echo '1';
        }
        else
        {
            // 2回目以降：更新
            $user_id = $users_model->update_google_user($google_user_data);
            if($user_id === false)
            {
                log_message('error', 'DB接続失敗: insert_google_user');
                show_error('エラー', 200, '');
            }
            echo '2';
        }

        // 登録・更新後、最新のusers情報を取得
        $login_user =  $users_model->get_users_google_sub($google_sub);
        if($login_user === false)
        {
            log_message('error', 'DB接続失敗: get_users_google_sub');
            show_error('エラー', 200, '');
        }
            // echo '<pre>';
            // var_dump($get_users);
            // echo '</pre>';







        // if (!$user) {
        //     $db->table('users')->insert([
        //         'google_sub' => $userInfo->id,
        //         'name'       => $userInfo->name,
        //         'email'      => $userInfo->email,
        //         'created_at' => date('Y-m-d H:i:s'),
        //         'updated_at' => date('Y-m-d H:i:s'),
        //     ]);

        //     $userId = $db->insertID();

        //     $db->table('notification_settings')->insert([
        //         'user_id'    => $userId,
        //         'notify_time'=> '07:00:00',
        //         'timezone'   => 'Asia/Tokyo',
        //         'is_enabled' => 1,
        //         'created_at' => date('Y-m-d H:i:s'),
        //         'updated_at' => date('Y-m-d H:i:s'),
        //     ]);
        // } else {
        //     $userId = $user['id'];

        //     $db->table('users')
        //         ->where('id', $userId)
        //         ->update([
        //             'name'       => $userInfo->name,
        //             'email'      => $userInfo->email,
        //             'updated_at' => date('Y-m-d H:i:s'),
        //         ]);
        // }

        // $db->table('google_accounts')->replace([
        //     'user_id'          => $userId,
        //     'google_email'     => $userInfo->email,
        //     'access_token'     => json_encode($token, JSON_UNESCAPED_UNICODE),
        //     'refresh_token'    => $token['refresh_token'] ?? null,
        //     'token_expires_at' => isset($token['expires_in'])
        //         ? date('Y-m-d H:i:s', time() + $token['expires_in'])
        //         : null,
        //     'created_at'       => date('Y-m-d H:i:s'),
        //     'updated_at'       => date('Y-m-d H:i:s'),
        // ]);

        // session()->set([
        //     'user_id' => $userId,
        //     'name'    => $userInfo->name,
        //     'email'   => $userInfo->email,
        //     'logged_in' => true,
        // ]);

        // return 'ログイン成功：' . esc($userInfo->name);



























    }

}