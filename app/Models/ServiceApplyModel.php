<?php

namespace App\Models;

use App\Entity\ServiceApply_Entity;

class ServiceApplyModel extends CI_Model
{
    public function __construct($db)
    {
        parent::__construct($db, "service_apply", "apply_no");
        return $this;
    }

    // 이벤트(2개월 무료 등) 상품 중복신청 체크용 - idx_customer_product 인덱스를 그대로 탄다.
    // 상태 무관 전체 신청이력 기준(REJECTED/CANCEL 포함) - 재신청 허용 정책이 정해지면 status 조건 추가.
    public function existsByCustomerProduct($customer_no, $product_no): bool
    {
        $sql = "SELECT `apply_no` FROM `$this->table`
                WHERE `customer_no` = " . $this->db->escape((int) $customer_no) . "
                  AND `product_no` = " . $this->db->escape((int) $product_no) . "
                LIMIT 1";
        $rows = $this->getExecQuery($sql);
        return !empty($rows);
    }

    // 어드민 "서비스 신청 현황" 목록용 - 신청서 자체(회사명/대표자 등)는 service_apply 스냅샷을 그대로
    // 쓰고, 어느 계정(로그인 아이디)이 신청했는지/상품명/최근 결제상태만 customer·product·payment를
    // 조인해서 보여준다. payment는 1신청:N결제(월구독 갱신)라 가장 최근 1건만 서브쿼리로 붙인다.
    public function get_list_with_details()
    {
        $sql = "SELECT sa.*, c.customer_id AS customer_login_id, p.product_name, p.product_code,
                       pay.pay_status, pay.pay_method, pay.pay_amount, pay.paid_at
                FROM `$this->table` sa
                JOIN `customer` c ON c.customer_no = sa.customer_no
                JOIN `product` p ON p.product_no = sa.product_no
                LEFT JOIN `payment` pay ON pay.payment_no = (
                    SELECT pay2.payment_no FROM `payment` pay2
                    WHERE pay2.apply_no = sa.apply_no
                    ORDER BY pay2.payment_no DESC LIMIT 1
                )
                ORDER BY sa.apply_no DESC";
        return $this->getExecQuery($sql);
    }

    public function findByIdx($apply_no)
    {
        $rows = $this->findbyid((int) $apply_no);
        return $rows[0] ?? null;
    }

    // 승인/거절 처리 - approve()는 approve_date도 같이 찍고, reject()는 reject_reason을 같이 받는다
    public function updateStatus($apply_no, string $status, ?string $rejectReason = null)
    {
        $set = ["`status` = " . $this->db->escape($status)];
        if ($status === 'APPROVED') {
            $set[] = "`approve_date` = NOW()";
        }
        if ($rejectReason !== null) {
            $set[] = "`reject_reason` = " . $this->db->escape($rejectReason);
        }
        $sql = "UPDATE `$this->table` SET " . implode(",", $set) . " WHERE `apply_no` = " . $this->db->escape((int) $apply_no);
        return $this->safeExecQuery($sql);
    }

    public function sql_save(ServiceApply_Entity $entity)
    {
        $esc = function ($value) {
            return $value !== null && $value !== '' ? $this->db->escape($value) : 'NULL';
        };

        $osql = " INSERT INTO `$this->table` SET
                        `customer_no`=%s, `product_no`=%s,
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
            $this->db->escape($entity->getCustomerNo()),
            $this->db->escape($entity->getProductNo()),
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
