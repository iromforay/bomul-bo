<?php
namespace App\Models;

use App\Entity\Payment_Entity;

class PaymentModel extends CI_Model
{
    public function __construct($db)
    {
        parent::__construct($db, "payment", "payment_no");
        return $this;
    }

    // 무료(0원)/이벤트 신청 시 즉시 완료 처리로 남기는 행 - 실제 PG 결제(#11)가 붙으면 그때는
    // 결제완료 콜백에서 pg_provider/pg_tid까지 채워서 같은 메서드로 저장하면 된다.
    public function sql_save(Payment_Entity $entity)
    {
        $esc = function ($value) {
            return $value !== null && $value !== '' ? $this->db->escape($value) : 'NULL';
        };

        $osql = " INSERT INTO `$this->table` SET
                        `apply_no`=%s, `customer_no`=%s, `pay_method`=%s, `pg_provider`=%s, `pg_tid`=%s,
                        `pay_amount`=%s, `pay_status`=%s, `paid_at`=%s, `fail_reason`=%s, `pg_response`=%s
                ";
        $sql = sprintf(
            $osql,
            $this->db->escape((int) $entity->getApplyNo()),
            $esc($entity->getCustomerNo()),
            $this->db->escape($entity->getPayMethod()),
            $esc($entity->getPgProvider()),
            $esc($entity->getPgTid()),
            $this->db->escape((int) $entity->getPayAmount()),
            $this->db->escape($entity->getPayStatus()),
            $esc($entity->getPaidAt()),
            $esc($entity->getFailReason()),
            $esc($entity->getPgResponse())
        );
        return $this->safeExecQuery($sql);
    }

    // 마이페이지(#16) "내 결제 리스트" - 이 고객의 신청서(service_apply)에 달린 결제 이력 전체, 최신순.
    // 무료/이벤트 신청도 pay_method=NONE으로 행이 남아있어(Payment_Entity 주석 참고) 그대로 노출된다.
    public function get_list_by_customer($customer_no)
    {
        $sql = "SELECT pay.*, sa.company_name, sa.sub_domain, p.product_name
                FROM `$this->table` pay
                JOIN `service_apply` sa ON sa.apply_no = pay.apply_no
                LEFT JOIN `product` p ON p.product_no = sa.product_no
                WHERE sa.customer_no = " . $this->db->escape((int) $customer_no) . "
                ORDER BY pay.payment_no DESC";
        return $this->getExecQuery($sql);
    }
}
