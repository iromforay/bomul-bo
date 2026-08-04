<?php

namespace App\Models;

use App\Entity\Manager_Entity;

class ManagerModel extends CI_Model
{
    public function __construct($db)
    {
        parent::__construct($db, "manager", "manager_no");
        return $this;
    }

    // 로그인용 조회 - 탈퇴/비활성 계정(use_yn='N')은 로그인 대상에서 제외
    public function findByLoginId($manager_id)
    {
        $sql = "SELECT * FROM `$this->table`
                WHERE `manager_id` = " . $this->db->escape($manager_id) . "
                  AND `use_yn` = 'Y'
                LIMIT 1";
        $rows = $this->getExecQuery($sql);
        return $rows[0] ?? null;
    }

    // 아이디 중복체크용 - use_yn 상관없이 전체에서 확인 (manager_id는 UNIQUE)
    public function existsByLoginId($manager_id, $exclude_no = null)
    {
        $sql = "SELECT `manager_no` FROM `$this->table` WHERE `manager_id` = " . $this->db->escape($manager_id);
        if ($exclude_no !== null) {
            $sql .= " AND `manager_no` != " . $this->db->escape($exclude_no);
        }
        $sql .= " LIMIT 1";
        $rows = $this->getExecQuery($sql);
        return !empty($rows);
    }

    public function sql_save(Manager_Entity $entity)
    {
        $osql = " INSERT INTO `$this->table` SET
                        `manager_id`=%s, `manager_pw`=%s, `manager_name`=%s,
                        `use_yn`=%s, `reg_date`=now()
                ";
        $sql = sprintf(
            $osql,
            $this->db->escape($entity->getManagerId()),
            $this->db->escape($entity->getManagerPw()),
            $this->db->escape($entity->getManagerName()),
            $this->db->escape($entity->getUseYn() ?? 'Y')
        );
        return $this->safeExecQuery($sql);
    }

    public function sql_edit(Manager_Entity $entity)
    {
        $set = [];

        !is_null($entity->getManagerId())   ? $set[] = "`manager_id`=".   $this->db->escape($entity->getManagerId())   : "";
        !is_null($entity->getManagerPw())   ? $set[] = "`manager_pw`=".   $this->db->escape($entity->getManagerPw())   : "";
        !is_null($entity->getManagerName()) ? $set[] = "`manager_name`=". $this->db->escape($entity->getManagerName()) : "";
        !is_null($entity->getUseYn())       ? $set[] = "`use_yn`=".      $this->db->escape($entity->getUseYn())       : "";

        if (count($set) === 0) {
            return false;
        }

        $sql = "UPDATE `$this->table` SET " . implode(",", $set) .
               " WHERE `$this->primaryKey` = " . $this->db->escape($entity->getManagerNo());

        return $this->safeExecQuery($sql);
    }

    public function sql_delete($manager_no)
    {
        $sql = "DELETE FROM `$this->table` WHERE `$this->primaryKey` = " . $this->db->escape($manager_no);
        return $this->safeExecQuery($sql);
    }
}
