<?php namespace App\Models;

use CodeIgniter\Model;

class Google_accounts_model extends Model
{
    // // IDと一致した商品情報を取得する
    // public function get_users_google_sub($google_sub)
    // {
    //     // DB接続を取得
    //     $db = \Config\Database::connect();
    //     $query = $db->query(
    //         "
    //             SELECT
    //                 *
    //             FROM
    //                 users
    //             WHERE
    //                 google_sub = ?
    //         ",
    //         [$google_sub]
    //     );
    //     if (! $query) 
    //     {
    //         return false;
    //     }
    //     return $query->getRowArray();
    // }

    // google_accounts情報を登録する
    public function insert_google_accounts(array $data)
    {
        // DB接続を取得する
        $db = \Config\Database::connect($this->DBGroup);
        $sql = "
            INSERT INTO
                google_accounts
                (
                    user_id,
                    google_email,
                    access_token,
                    refresh_token,
                    token_expires_at,
                    created_at,
                    updated_at
                )
                VALUES
                (
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    NOW(),
                    NOW()
                )
        ";
        $result = $db->query($sql, [
            $data['user_id'],
            $data['google_email'],
            $data['access_token'],
            $data['refresh_token'],
            $data['token_expires_at']
        ]);
        if (! $result) {
            return false;
        }
        return true;
    }

    // google_accounts情報を更新する
    public function update_google_accounts(array $data)
    {
        // DB接続を取得する
        $db = \Config\Database::connect($this->DBGroup);
        $sql = "
            UPDATE
                google_accounts
            SET
                google_email     = :google_email:,
                access_token     = :access_token:,
                refresh_token    = :refresh_token:,
                token_expires_at = :token_expires_at:,
                updated_at       = NOW()
            WHERE
                user_id = :user_id:
        ";
        $query = $db->query($sql,[
            'google_email'     => $data['google_email'],
            'access_token'     => $data['access_token'],
            'refresh_token'    => $data['refresh_token'],
            'token_expires_at' => $data['token_expires_at'],
            'user_id'          => $data['user_id']
        ]);
        if (! $query) 
        {
            return false;
        }
        return true;
    }
}