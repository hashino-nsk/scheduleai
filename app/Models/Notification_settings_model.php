<?php namespace App\Models;

use CodeIgniter\Model;

class Notification_settings_model extends Model
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

    // google_user情報を登録する
    public function insert_notification_settings($user_id)
    {
        // DB接続を取得する
        $db = \Config\Database::connect($this->DBGroup);
        $sql = "
            INSERT INTO
                notification_settings
                (
                    user_id,
                    notify_time,
                    timezone,
                    is_enabled,
                    created_at,
                    updated_at
                )
                VALUES
                (
                    ?,
                    '09:00:00',
                    'Asia/Tokyo',
                    1,
                    NOW(),
                    NOW()
                )
        ";
        $result = $db->query($sql, [
            $user_id
        ]);
        if (! $result) {
            return false;
        }
        return true;
    }

    // // google_user情報を更新する
    // public function update_google_user(array $data)
    // {
    //     // DB接続を取得する
    //     $db = \Config\Database::connect($this->DBGroup);
    //     $sql = "
    //         UPDATE
    //             users
    //         SET
    //             google_sub = :google_sub:,
    //             name       = :name:,
    //             email      = :email:,
    //             updated_at = NOW()
    //         WHERE
    //             google_sub = :google_sub:
    //     ";
    //     $query = $db->query($sql,[
    //         'google_sub'      => $data['google_sub'],
    //         'name'            => $data['name'],
    //         'email'           => $data['email']
    //     ]);
    //     if (! $query) 
    //     {
    //         return false;
    //     }
    //     return true;
    // }
    




}