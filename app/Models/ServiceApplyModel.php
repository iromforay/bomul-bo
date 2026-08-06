<?php

namespace App\Models;

use App\Entity\ServiceApply_Entity;

class ServiceApplyModel extends CI_Model
{
    public function __construct($db)
    {
        parent::__construct($db, "service_apply", "apply_idx");
        return $this;
    }

    // 이벤트(2개월 무료 등) 상품 중복신청 체크용 - idx_customer_product 인덱스를 그대로 탄다.
    // 상태 무관 전체 신청이력 기준(REJECTED/CANCEL 포함) - 재신청 허용 정책이 정해지면 status 조건 추가.
    public function existsByCustomerProduct($customer_idx, $product_idx): bool
    {
        $sql = "SELECT `apply_idx` FROM `$this->table`
                WHERE `customer_idx` = " . $this->db->escape((int) $customer_idx) . "
                  AND `product_idx` = " . $this->db->escape((int) $product_idx) . "
                LIMIT 1";
        $rows = $this->getExecQuery($sql);
        return !empty($rows);
    }

    public function sql_save(ServiceApply_Entity $entity)
    {
        $esc = function ($value) {
            return $value !== null && $value !== '' ? $this->db->escape($value) : 'NULL';
        };

        $osql = " INSERT INTO `$this->table` SET
                        `customer_idx`=%s, `product_idx`=%s,
                        `company_name`=%s, `ceo_name`=%s,
                        `license_no`=%s, `biz_no`=%s,
                        `manager_name`=%s, `manager_phone`=%s, `manager_email`=%s,
                        `tel`=%s, `fax`=%s,
                        `zipcode`=%s, `addr1`=%s, `addr2`=%s,
                        `homepage_name`=%s, `sub_domain`=%s, `custom_domain`=%s,
                        `memo`=%s,
                        `status`=%s, `request_date`=now()
                ";
        $sql = sprintf(
            $osql,
            $this->db->escape($entity->getCustomerIdx()),
            $this->db->escape($entity->getProductIdx()),
            $this->db->escape($entity->getCompanyName()),
            $this->db->escape($entity->getCeoName()),
            $esc($entity->getLicenseNo()),
            $esc($entity->getBizNo()),
            $esc($entity->getManagerName()),
            $this->db->escape($entity->getManagerPhone()),
            $this->db->escape($entity->getManagerEmail()),
            $esc($entity->getTel()),
            $esc($entity->getFax()),
            $esc($entity->getZipcode()),
            $esc($entity->getAddr1()),
            $esc($entity->getAddr2()),
            $esc($entity->getHomepageName()),
            $esc($entity->getSubDomain()),
            $esc($entity->getCustomDomain()),
            $esc($entity->getMemo()),
            $this->db->escape($entity->getStatus() ?? 'REQUEST')
        );
        return $this->safeExecQuery($sql);
    }
}
