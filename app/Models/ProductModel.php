<?php

namespace App\Models;

class ProductModel extends CI_Model
{
    public function __construct($db)
    {
        parent::__construct($db, "product", "product_no");
        return $this;
    }

    // 신청 폼 상품선택 스텝에 노출할 목록 - 중지(STOP)된 상품은 제외, 정렬순
    public function get_usable_list()
    {
        $sql = "SELECT * FROM `$this->table` WHERE `status` = 'USE' ORDER BY `sort` ASC, `product_no` ASC";
        return $this->getExecQuery($sql);
    }

    public function findByIdx($product_no)
    {
        $rows = $this->findbyid((int) $product_no);
        return $rows[0] ?? null;
    }

    // Home 페이지 요금카드 등에서 ?product=1month 같은 사람이 읽을 수 있는 코드로 넘어올 때 조회
    public function findByCode($product_code)
    {
        $sql = "SELECT * FROM `$this->table` WHERE `product_code` = " . $this->db->escape($product_code) . " AND `status` = 'USE' LIMIT 1";
        $rows = $this->getExecQuery($sql);
        return $rows[0] ?? null;
    }
}
