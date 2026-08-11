<?php

namespace App\Models;

class PaymentModel extends CI_Model
{
    public function __construct($db)
    {
        parent::__construct($db, "payment", "payment_no");
        return $this;
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
