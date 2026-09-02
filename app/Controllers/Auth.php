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
        // refresh_tokenを取得するために必要
        $client->setAccessType('offline'); //ユーザーがブラウザを閉じている間でも、システムがGoogle APIを使えるようにする設定
        // 開発中：同意画面を再表示してrefresh_tokenを発行させる
        $client->setPrompt('consent select_account'); //ログイン時にアカウント選択画面と利用同意画面が出やすくなります
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
        // トークン取得失敗
        if (isset($token['error']))
        {
            log_message(
                'error',
                'Googleトークン取得失敗: ' . json_encode($token)
            );

            throw new \RuntimeException('Google認証に失敗しました');
        }
        $client->setAccessToken($token); //アクセストークン設定
        //OAuthサービス生成
        $oauth = new \Google\Service\Oauth2($client);
        $user_info = $oauth->userinfo->get(); //ユーザー情報取得
        //情報表示
        echo '<h2>Googleログイン成功</h2>';
        echo 'Google ID : ' . $user_info->id . '<br>';
        echo '名前 : ' . $user_info->name . '<br>';
        echo 'メール : ' . $user_info->email . '<br>';

        // Google ID が一致するユーザーを users テーブルから探す
        $google_sub = $user_info->id;
        $users_model = model(\App\Models\Users_model::class);
        $get_users = $users_model->get_users_google_sub($google_sub);
        if($get_users === false)
        {
            log_message('error', 'DB接続失敗: get_users_google_sub');
            throw new \RuntimeException('エラー');
        }
        // usersテーブルへの保存データの生成
        $google_user_data['google_sub'] = $user_info['id'];
        $google_user_data['name'] = $user_info['name'];
        $google_user_data['email'] = $user_info['email'];
        // access_tokenの有効期限を計算
        $token_created = $token['created'] ?? time();
        $expires_in    = $token['expires_in'] ?? 3600;
        $token_expires_at = date(
            'Y-m-d H:i:s',
            $token_created + $expires_in
        );
        // refresh_tokenの暗号化
        $encrypter = \Config\Services::encrypter();
        $encrypted_refresh_token = base64_encode($encrypter->encrypt($token['refresh_token']));
        // google_accountsテーブルへの保存データの生成
        $google_accounts_data['google_email'] = $user_info['email'];
        $google_accounts_data['access_token'] = $token['access_token'];
        $google_accounts_data['refresh_token'] = $encrypted_refresh_token;
        $google_accounts_data['token_expires_at'] = $token_expires_at;
        $google_accounts_data['created_at'] = $user_info['email'];
        $google_accounts_data['updated_at'] = $user_info['email'];
        $google_accounts_model = model(\App\Models\Google_accounts_model::class);
        // 初回ログイン
        if ($get_users === null)
        {
            // users に登録
            $user_id = $users_model->insert_google_user($google_user_data);
            if($user_id === false)
            {
                log_message('error', 'DB接続失敗: insert_google_user');
                throw new \RuntimeException('エラー');
            }
            $google_accounts_data['user_id'] = $user_id;
            // google_accounts に登録
            $google_accounts_id = $google_accounts_model->insert_google_accounts($google_accounts_data);
            if($google_accounts_id === false)
            {
                log_message('error', 'DB接続失敗: insert_google_accounts');
                throw new \RuntimeException('エラー');
            }
            // 通知設定の登録
            $notification_settings_model = model(\App\Models\Notification_settings_model::class);
            $insert_notification_settings_id = $notification_settings_model->insert_notification_settings($user_id);
            if($insert_notification_settings_id === false)
            {
                log_message('error', 'DB接続失敗: insert_notification_settings_id');
                throw new \RuntimeException('エラー');
            }
        }
        // 2回目以降のログイン
        else
        {
            // userテーブル更新
            $user_id = $users_model->update_google_user($google_user_data);
            if($user_id === false)
            {
                log_message('error', 'DB接続失敗: insert_google_user');
                throw new \RuntimeException('エラー');
            }
            $google_accounts_data['user_id'] = $user_id;
            // google_accountsテーブル更新
            $google_accounts_id = $google_accounts_model->update_google_accounts($google_accounts_data);
            if($google_accounts_id === false)
            {
                log_message('error', 'DB接続失敗: update_google_accounts');
                throw new \RuntimeException('エラー');
            }
        }

        // 登録・更新後、最新のusers情報を取得
        $login_user =  $users_model->get_users_google_sub($google_sub);
        if($login_user === false)
        {
            log_message('error', 'DB接続失敗: get_users_google_sub');
            throw new \RuntimeException('エラー');
        }
        if ($login_user === null)
        {
            log_message('error', 'ユーザー情報が見つかりません');
            throw new \RuntimeException('エラー');
        }

        // session()->set([
        //     'user_id' => $userId,
        //     'name'    => $userInfo->name,
        //     'email'   => $userInfo->email,
        //     'logged_in' => true,
        // ]);

        // return 'ログイン成功：' . esc($userInfo->name);



























    }

}