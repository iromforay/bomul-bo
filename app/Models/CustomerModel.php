<?php

namespace App\Models;

use App\Entity\Customer_Entity;

class CustomerModel extends CI_Model
{
    public function __construct($db)
    {
        parent::__construct($db, "customer", "customer_no");
        return $this;
    }

    // 로그인용 조회 - 비활성 계정(use_yn='N')은 로그인 대상에서 제외
    public function findByLoginId($customer_id)
    {
        $sql = "SELECT * FROM `$this->table`
                WHERE `customer_id` = " . $this->db->escape($customer_id) . "
                  AND `use_yn` = 'Y'
                LIMIT 1";
        $rows = $this->getExecQuery($sql);
        return $rows[0] ?? null;
    }

    // 아이디 중복체크 - use_yn 상관없이 전체에서 확인 (customer_id는 UNIQUE)
    public function existsByLoginId($customer_id, $exclude_no = null)
    {
        $sql = "SELECT `customer_no` FROM `$this->table` WHERE `customer_id` = " . $this->db->escape($customer_id);
        if ($exclude_no !== null) {
            $sql .= " AND `customer_no` != " . $this->db->escape($exclude_no);
        }
        $sql .= " LIMIT 1";
        $rows = $this->getExecQuery($sql);
        return !empty($rows);
    }

    // 서비스 신청 단계에서 fid(2차도메인) 입력받을 때 중복체크용 - customer 안에서만 확인.
    // 실제 발급 가능 여부는 이미 활성화된 중앙 member.fid까지 같이 봐야 하니, 신청 처리 로직에서
    // 이 체크와 별도로 중앙 member 쪽 중복도 반드시 확인해야 한다 (아직 미구현).
    public function existsByFid($fid, $exclude_no = null)
    {
        $sql = "SELECT `customer_no` FROM `$this->table` WHERE `fid` = " . $this->db->escape($fid);
        if ($exclude_no !== null) {
            $sql .= " AND `customer_no` != " . $this->db->escape($exclude_no);
        }
        $sql .= " LIMIT 1";
        $rows = $this->getExecQuery($sql);
        return !empty($rows);
    }

    public function sql_save(Customer_Entity $entity)
    {
        // 회원가입은 id/pw/company_name/phone 4개만 받는다 - customer_name/biz_reg_no/broker_reg_no/
        // email/fid는 서비스 신청 단계(사업자정보 및 도메인 입력)에서 채워지므로 가입 시점엔 NULL 가능.
        $esc = function ($value) {
            return $value !== null && $value !== '' ? $this->db->escape($value) : 'NULL';
        };

        $osql = " INSERT INTO `$this->table` SET
                        `customer_id`=%s, `customer_pw`=%s, `customer_name`=%s,
                        `company_name`=%s, `biz_reg_no`=%s, `broker_reg_no`=%s, `phone`=%s, `email`=%s, `fid`=%s,
                        `biz_reg_verified_yn`=%s, `broker_reg_verified_yn`=%s,
                        `use_yn`=%s, `service_yn`=%s, `migrated_yn`=%s, `reg_date`=now()
                ";
        $sql = sprintf(
            $osql,
            $this->db->escape($entity->getCustomerId()),
            $this->db->escape($entity->getCustomerPw()),
            $esc($entity->getCustomerName()),
            $this->db->escape($entity->getCompanyName()),
            $esc($entity->getBizRegNo()),
            $esc($entity->getBrokerRegNo()),
            $this->db->escape($entity->getPhone()),
            $esc($entity->getEmail()),
            $esc($entity->getFid()),
            // 가입 시점엔 항상 미검증(N)/미이관(N)으로 시작 - entity 값과 무관하게 여기서 고정.
            // service_yn도 결제 전이라 무조건 N에서 시작.
            $this->db->escape('N'),
            $this->db->escape('N'),
            $this->db->escape($entity->getUseYn() ?? 'Y'),
            $this->db->escape('N'),
            $this->db->escape('N')
        );
        return $this->safeExecQuery($sql);
    }

    public function sql_edit(Customer_Entity $entity)
    {
        $set = [];

        !is_null($entity->getCustomerId())          ? $set[] = "`customer_id`=".            $this->db->escape($entity->getCustomerId())          : "";
        !is_null($entity->getCustomerPw())          ? $set[] = "`customer_pw`=".            $this->db->escape($entity->getCustomerPw())          : "";
        !is_null($entity->getCustomerName())        ? $set[] = "`customer_name`=".          $this->db->escape($entity->getCustomerName())        : "";
        !is_null($entity->getCompanyName())         ? $set[] = "`company_name`=".           $this->db->escape($entity->getCompanyName())         : "";
        !is_null($entity->getBizRegNo())            ? $set[] = "`biz_reg_no`=".             $this->db->escape($entity->getBizRegNo())            : "";
        !is_null($entity->getBrokerRegNo())         ? $set[] = "`broker_reg_no`=".          $this->db->escape($entity->getBrokerRegNo())         : "";
        !is_null($entity->getPhone())               ? $set[] = "`phone`=".                  $this->db->escape($entity->getPhone())               : "";
        !is_null($entity->getEmail())               ? $set[] = "`email`=".                  $this->db->escape($entity->getEmail())               : "";
        !is_null($entity->getBizRegVerifiedYn())    ? $set[] = "`biz_reg_verified_yn`=".     $this->db->escape($entity->getBizRegVerifiedYn())    : "";
        !is_null($entity->getBrokerRegVerifiedYn()) ? $set[] = "`broker_reg_verified_yn`=".  $this->db->escape($entity->getBrokerRegVerifiedYn()) : "";
        !is_null($entity->getUseYn())               ? $set[] = "`use_yn`=".                 $this->db->escape($entity->getUseYn())               : "";
        !is_null($entity->getServiceYn())           ? $set[] = "`service_yn`=".             $this->db->escape($entity->getServiceYn())           : "";
        !is_null($entity->getServiceExpireDate())   ? $set[] = "`service_expire_date`=".    $this->db->escape($entity->getServiceExpireDate())   : "";
        !is_null($entity->getBizRegFileDir())       ? $set[] = "`biz_reg_file_dir`=".       $this->db->escape($entity->getBizRegFileDir())       : "";
        !is_null($entity->getBizRegFileName())      ? $set[] = "`biz_reg_file_name`=".      $this->db->escape($entity->getBizRegFileName())      : "";
        !is_null($entity->getBrokerRegFileDir())    ? $set[] = "`broker_reg_file_dir`=".    $this->db->escape($entity->getBrokerRegFileDir())    : "";
        !is_null($entity->getBrokerRegFileName())   ? $set[] = "`broker_reg_file_name`=".   $this->db->escape($entity->getBrokerRegFileName())   : "";
        !is_null($entity->getFid())                 ? $set[] = "`fid`=".                    $this->db->escape($entity->getFid())                 : "";
        !is_null($entity->getMemberNo())            ? $set[] = "`member_no`=".              $this->db->escape($entity->getMemberNo())            : "";
        !is_null($entity->getMigratedYn())          ? $set[] = "`migrated_yn`=".            $this->db->escape($entity->getMigratedYn())          : "";
        !is_null($entity->getMigratedDate())        ? $set[] = "`migrated_date`=".          $this->db->escape($entity->getMigratedDate())        : "";

        if (count($set) === 0) {
            return false;
        }

        $sql = "UPDATE `$this->table` SET " . implode(",", $set) .
               " WHERE `$this->primaryKey` = " . $this->db->escape($entity->getCustomerNo());

        return $this->safeExecQuery($sql);
    }

    public function sql_delete($customer_no)
    {
        $sql = "DELETE FROM `$this->table` WHERE `$this->primaryKey` = " . $this->db->escape($customer_no);
        return $this->safeExecQuery($sql);
    }
}
