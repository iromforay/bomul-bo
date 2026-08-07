<?php

namespace App\Models;

// 가정: member(mb_no, mb_id, mb_pw, shop_code, mb_name) 테이블. 실제 DB 스키마와 다르면 맞춰서 수정 필요.
class LoginModel extends CI_Model
{
    public function __construct($db)
    {
        parent::__construct($db, "member", "mb_no");
        return $this;
    }

    public function findByLoginId($mb_id)
    {
        $sql = "SELECT * FROM `$this->table` WHERE `mb_id` = " . $this->db->escape($mb_id) . " LIMIT 1";
        $rows = $this->getExecQuery($sql);
        return $rows[0] ?? null;
    }
}
