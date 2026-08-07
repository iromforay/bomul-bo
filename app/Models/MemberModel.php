<?php

namespace App\Models;

use App\Entity\Member_Entity;

class MemberModel extends CI_Model
{
    public function __construct($db)
    {
        parent::__construct($db, "member", "member_no");
        return $this;
    }

    // 로그인용 조회 - 비활성 계정(use_yn='N')은 로그인 대상에서 제외
    public function findByLoginId($member_id)
    {
        $sql = "SELECT * FROM `$this->table`
                WHERE `member_id` = " . $this->db->escape($member_id) . "
                  AND `use_yn` = 'Y'
                LIMIT 1";
        $rows = $this->getExecQuery($sql);
        return $rows[0] ?? null;
    }

    // 아이디 중복체크 - use_yn 상관없이 전체에서 확인 (member_id는 UNIQUE)
    public function existsByLoginId($member_id, $exclude_no = null)
    {
        $sql = "SELECT `member_no` FROM `$this->table` WHERE `member_id` = " . $this->db->escape($member_id);
        if ($exclude_no !== null) {
            $sql .= " AND `member_no` != " . $this->db->escape($exclude_no);
        }
        $sql .= " LIMIT 1";
        $rows = $this->getExecQuery($sql);
        return !empty($rows);
    }

    // 사업자등록번호 중복체크 - 한 사업자당 하나의 계정만 받을지 정책에 따라 서비스단에서 사용
    public function existsByBizRegNo($biz_reg_no, $exclude_no = null)
    {
        $sql = "SELECT `member_no` FROM `$this->table` WHERE `biz_reg_no` = " . $this->db->escape($biz_reg_no);
        if ($exclude_no !== null) {
            $sql .= " AND `member_no` != " . $this->db->escape($exclude_no);
        }
        $sql .= " LIMIT 1";
        $rows = $this->getExecQuery($sql);
        return !empty($rows);
    }

    public function sql_save(Member_Entity $entity)
    {
        $osql = " INSERT INTO `$this->table` SET
                        `member_id`=%s, `member_pw`=%s, `member_name`=%s,
                        `company_name`=%s, `biz_reg_no`=%s, `broker_reg_no`=%s, `phone`=%s, `email`=%s,
                        `biz_reg_verified_yn`=%s, `broker_reg_verified_yn`=%s,
                        `use_yn`=%s, `reg_date`=now()
                ";
        $sql = sprintf(
            $osql,
            $this->db->escape($entity->getMemberId()),
            $this->db->escape($entity->getMemberPw()),
            $this->db->escape($entity->getMemberName()),
            $this->db->escape($entity->getCompanyName()),
            $this->db->escape($entity->getBizRegNo()),
            $this->db->escape($entity->getBrokerRegNo()),
            $this->db->escape($entity->getPhone()),
            $this->db->escape($entity->getEmail()),
            // 가입 시점엔 항상 미검증(N)으로 시작 - entity 값과 무관하게 여기서 고정
            $this->db->escape('N'),
            $this->db->escape('N'),
            $this->db->escape($entity->getUseYn() ?? 'Y')
        );
        return $this->safeExecQuery($sql);
    }

    public function sql_edit(Member_Entity $entity)
    {
        $set = [];

        !is_null($entity->getMemberPw())    ? $set[] = "`member_pw`=".    $this->db->escape($entity->getMemberPw())    : "";
        !is_null($entity->getMemberName())  ? $set[] = "`member_name`=".  $this->db->escape($entity->getMemberName())  : "";
        !is_null($entity->getCompanyName()) ? $set[] = "`company_name`=". $this->db->escape($entity->getCompanyName()) : "";
        !is_null($entity->getBizRegNo())    ? $set[] = "`biz_reg_no`=".   $this->db->escape($entity->getBizRegNo())    : "";
        !is_null($entity->getBrokerRegNo()) ? $set[] = "`broker_reg_no`=".$this->db->escape($entity->getBrokerRegNo()) : "";
        !is_null($entity->getPhone())       ? $set[] = "`phone`=".       $this->db->escape($entity->getPhone())       : "";
        !is_null($entity->getEmail())       ? $set[] = "`email`=".       $this->db->escape($entity->getEmail())       : "";
        !is_null($entity->getBizRegVerifiedYn())    ? $set[] = "`biz_reg_verified_yn`=".   $this->db->escape($entity->getBizRegVerifiedYn())    : "";
        !is_null($entity->getBrokerRegVerifiedYn()) ? $set[] = "`broker_reg_verified_yn`=".$this->db->escape($entity->getBrokerRegVerifiedYn()) : "";
        !is_null($entity->getUseYn())       ? $set[] = "`use_yn`=".      $this->db->escape($entity->getUseYn())       : "";

        if (count($set) === 0) {
            return false;
        }

        $sql = "UPDATE `$this->table` SET " . implode(",", $set) .
               " WHERE `$this->primaryKey` = " . $this->db->escape($entity->getMemberNo());

        return $this->safeExecQuery($sql);
    }

    public function sql_delete($member_no)
    {
        $sql = "DELETE FROM `$this->table` WHERE `$this->primaryKey` = " . $this->db->escape($member_no);
        return $this->safeExecQuery($sql);
    }
}
