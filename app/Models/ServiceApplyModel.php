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

    // 상품 삭제 전 안전장치 - 이미 신청이력이 있는 상품을 삭제하면 그 신청서의 product_no가
    // 고아 참조로 남으므로, 어드민 "상품 관리"에서 삭제 시 이걸로 막는다(상태 무관 전체 이력 기준).
    public function existsByProductNo($product_no): bool
    {
        $sql = "SELECT `apply_no` FROM `$this->table` WHERE `product_no` = " . $this->db->escape((int) $product_no) . " LIMIT 1";
        $rows = $this->getExecQuery($sql);
        return !empty($rows);
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

    // 어드민 "서비스 신청 현황" 목록용 - 순수하게 service_apply에 등록된 신청 목록만 보여준다.
    // 고객정보/결제정보처럼 복잡한 조인은 상세(get_detail_by_apply_no)에서만 붙인다.
    // product_name만 예외 - 신청 당시 어떤 상품을 신청했는지는 목록에서도 필요한 신청서 자체 정보.
    // sl.status/install_status는 정지/재개 버튼을 어떤 걸 보여줄지 판단하는 데 씀.
    // queue_status는 신청상태 옆 "세팅현황" 배지용 - 최초 세팅용 큐(action='new')만, 상세와 동일 기준.
    public function get_list_with_details()
    {
        $sql = "SELECT sa.*, p.product_name, p.product_code,
                       sl.status AS live_status, sl.install_status,
                       q.status AS queue_status
                FROM `$this->table` sa
                LEFT JOIN `product` p ON p.product_no = sa.product_no
                LEFT JOIN `service_live` sl ON sl.apply_no = sa.apply_no
                LEFT JOIN `queue` q ON q.queue_no = (
                    SELECT q2.queue_no FROM `queue` q2
                    WHERE q2.live_no = sl.live_no AND q2.action = 'new'
                    ORDER BY q2.queue_no DESC LIMIT 1
                )
                ORDER BY sa.apply_no DESC";

        return $this->getExecQuery($sql);
    }

    // sub_domain(희망 도메인) 유일성 체크 - 세팅 전 상태(REQUEST/REVIEW/APPROVED)만 점유로 본다.
    // REJECTED/CANCEL은 세팅 전에 끝난 것이므로 그 도메인은 다시 신청 가능해야 한다.
    public function existsBySubDomain($sub_domain, $exclude_apply_no = null): bool
    {
        $sql = "SELECT `apply_no` FROM `$this->table`
                WHERE `sub_domain` = " . $this->db->escape($sub_domain) . "
                  AND `status` IN ('REQUEST','REVIEW','APPROVED')";
        if ($exclude_apply_no !== null) {
            $sql .= " AND `apply_no` != " . $this->db->escape((int) $exclude_apply_no);
        }
        $sql .= " LIMIT 1";
        $rows = $this->getExecQuery($sql);
        return !empty($rows);
    }

    public function findByIdx($apply_no)
    {
        $rows = $this->findbyid((int) $apply_no);
        return $rows[0] ?? null;
    }

    // 어드민 "서비스 신청 상세" 단건 조회 - get_list_with_details()와 동일한 조인을 apply_no로 한정.
    // 승인완료(APPROVED) 상태면 하단에 "세팅 진행 현황"(대기→세팅중→완료|실패)을 보여줘야 해서
    // service_live/큐(신규 세팅 action='new'만, extend·status 같은 이후 큐는 제외)까지 같이 조인한다.
    public function get_detail_by_apply_no($apply_no)
    {
        $sql = "SELECT sa.*, c.customer_id AS customer_login_id, p.product_name, p.product_code,
                       pay.pay_status, pay.pay_method, pay.pay_amount, pay.paid_at,
                       sl.live_no, sl.install_status,
                       q.status AS queue_status, q.sent_at AS queue_sent_at,
                       q.completed_at AS queue_completed_at, q.fail_reason AS queue_fail_reason
                FROM `$this->table` sa
                LEFT JOIN `customer` c ON c.customer_no = sa.customer_no
                LEFT JOIN `product` p ON p.product_no = sa.product_no
                LEFT JOIN `payment` pay ON pay.payment_no = (
                    SELECT pay2.payment_no FROM `payment` pay2
                    WHERE pay2.apply_no = sa.apply_no
                    ORDER BY pay2.payment_no DESC LIMIT 1
                )
                LEFT JOIN `service_live` sl ON sl.apply_no = sa.apply_no
                LEFT JOIN `queue` q ON q.queue_no = (
                    SELECT q2.queue_no FROM `queue` q2
                    WHERE q2.live_no = sl.live_no AND q2.action = 'new'
                    ORDER BY q2.queue_no DESC LIMIT 1
                )
                WHERE sa.apply_no = " . $this->db->escape((int) $apply_no) . "
                LIMIT 1";
        $rows = $this->getExecQuery($sql);
        return $rows[0] ?? null;
    }

    // 어드민 수정 화면 - 신청서 스냅샷 항목만 갱신 (status/승인 관련 값은 approve()/reject()에서만 처리)
    public function sql_edit(ServiceApply_Entity $entity)
    {
        $esc = function ($value) {
            return $value !== null && $value !== '' ? $this->db->escape($value) : 'NULL';
        };

        $osql = " UPDATE `$this->table` SET
                        `company_name`=%s, `ceo_name`=%s,
                        `license_no`=%s, `biz_no`=%s,
                        `manager_name`=%s, `manager_phone`=%s, `manager_email`=%s,
                        `tel`=%s, `fax`=%s,
                        `zipcode`=%s, `addr1`=%s, `addr2`=%s,
                        `homepage_name`=%s, `sub_domain`=%s, `custom_domain`=%s,
                        `memo`=%s
                  WHERE `apply_no` = %s
                ";
        $sql = sprintf(
            $osql,
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
            $this->db->escape((int) $entity->getApplyNo())
        );
        return $this->safeExecQuery($sql);
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
