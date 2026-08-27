<?php namespace App\Models;

use CodeIgniter\Model;

class Users_model extends Model
{

    // IDと一致した商品情報を取得する
    public function get_users_google_sub($google_sub)
    {
        // DB接続を取得
        $db = \Config\Database::connect();
        $query = $db->query(
            "
                SELECT
                    *
                FROM
                    users
                WHERE
                    google_sub = ?
            ",
            [$google_sub]
        );
        if (! $query) 
        {
            return false;
        }
        return $query->getRowArray();
    }

    // google_user情報を登録する
    public function insert_google_user(array $data)
    {
        // DB接続を取得する
        $db = \Config\Database::connect($this->DBGroup);
        $sql = "
            INSERT INTO
                users
                (
                    google_sub,
                    name,
                    email,
                    created_at,
                    updated_at
                )
                VALUES
                (
                    ?,
                    ?,
                    ?,
                    NOW(),
                    NOW()
                )
        ";
        $result = $db->query($sql, [
            $data['google_sub'],
            $data['name'],
            $data['email']
        ]);
        if (! $result) {
            return false;
        }
        return true;
    }

    // google_user情報を更新する
    public function update_google_user(array $data)
    {
        // DB接続を取得する
        $db = \Config\Database::connect($this->DBGroup);
        $sql = "
            UPDATE
                users
            SET
                google_sub = :google_sub:,
                name       = :name:,
                email      = :email:,
                updated_at = NOW()
            WHERE
                google_sub = :google_sub:
        ";
        $query = $db->query($sql,[
            'google_sub'      => $data['google_sub'],
            'name'            => $data['name'],
            'email'           => $data['email']
        ]);
        if (! $query) 
        {
            return false;
        }
        return true;
    }
    




}