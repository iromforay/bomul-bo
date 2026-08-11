<?php

namespace App\Models;

use App\Entity\Product_Entity;

class ProductModel extends CI_Model
{
    public function __construct($db)
    {
        parent::__construct($db, "product", "product_no");
        return $this;
    }

    // 어드민 "상품 목록" - status 상관없이 전체, 정렬순
    public function get_list($start = 0, $limit = 0)
    {
        $sql = "SELECT * FROM `$this->table` ORDER BY `sort` ASC, `product_no` ASC";
        return $this->getExecQuery($sql);
    }

    // 신청 폼 상품선택 스텝에 노출할 목록 - 중지(STOP)된 상품은 제외, 정렬순
    public function get_usable_list()
    {
        $sql = "SELECT * FROM `$this->table` WHERE `status` = 'USE' ORDER BY `sort` ASC, `product_no` ASC";
        return $this->getExecQuery($sql);
    }

    // www 요금 안내 그리드용 - 기본상품(BASE)만. 부가서비스(ADDON, 예: 디자인 템플릿 적용)는 아직
    // 단독 신청 그리드에 섞일 흐름이 없어서(기본상품 신청 후 추가하는 형태가 될 예정) 제외한다.
    public function get_usable_base_list()
    {
        $sql = "SELECT * FROM `$this->table` WHERE `status` = 'USE' AND `product_type` = 'BASE' ORDER BY `sort` ASC, `product_no` ASC";
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

    // product_code 중복체크 - status 상관없이 전체에서 확인 (product_code는 UNIQUE)
    public function existsByCode($product_code, $exclude_no = null): bool
    {
        $sql = "SELECT `product_no` FROM `$this->table` WHERE `product_code` = " . $this->db->escape($product_code);
        if ($exclude_no !== null) {
            $sql .= " AND `product_no` != " . $this->db->escape((int) $exclude_no);
        }
        $sql .= " LIMIT 1";
        $rows = $this->getExecQuery($sql);
        return !empty($rows);
    }

    public function sql_save(Product_Entity $entity)
    {
        $esc = function ($value) {
            return $value !== null && $value !== '' ? $this->db->escape($value) : 'NULL';
        };

        $osql = " INSERT INTO `$this->table` SET
                        `product_code`=%s, `product_type`=%s, `product_name`=%s, `price`=%s,
                        `billing_type`=%s, `period_day`=%s, `description`=%s,
                        `status`=%s, `is_event`=%s, `sort`=%s
                ";
        $sql = sprintf(
            $osql,
            $this->db->escape($entity->getProductCode()),
            $this->db->escape($entity->getProductType() ?? 'BASE'),
            $this->db->escape($entity->getProductName()),
            $this->db->escape((int) $entity->getPrice()),
            $this->db->escape($entity->getBillingType()),
            $this->db->escape((int) $entity->getPeriodDay()),
            $esc($entity->getDescription()),
            $this->db->escape($entity->getStatus() ?? 'USE'),
            $this->db->escape((int) $entity->getIsEvent()),
            $this->db->escape((int) ($entity->getSort() ?? 0))
        );
        return $this->safeExecQuery($sql);
    }

    public function sql_edit(Product_Entity $entity)
    {
        $esc = function ($value) {
            return $value !== null && $value !== '' ? $this->db->escape($value) : 'NULL';
        };

        $osql = " UPDATE `$this->table` SET
                        `product_code`=%s, `product_type`=%s, `product_name`=%s, `price`=%s,
                        `billing_type`=%s, `period_day`=%s, `description`=%s,
                        `status`=%s, `is_event`=%s, `sort`=%s
                  WHERE `product_no` = %s
                ";
        $sql = sprintf(
            $osql,
            $this->db->escape($entity->getProductCode()),
            $this->db->escape($entity->getProductType() ?? 'BASE'),
            $this->db->escape($entity->getProductName()),
            $this->db->escape((int) $entity->getPrice()),
            $this->db->escape($entity->getBillingType()),
            $this->db->escape((int) $entity->getPeriodDay()),
            $esc($entity->getDescription()),
            $this->db->escape($entity->getStatus()),
            $this->db->escape((int) $entity->getIsEvent()),
            $this->db->escape((int) ($entity->getSort() ?? 0)),
            $this->db->escape((int) $entity->getProductNo())
        );
        return $this->safeExecQuery($sql);
    }

    public function sql_delete($product_no)
    {
        $sql = "DELETE FROM `$this->table` WHERE `product_no` = " . $this->db->escape((int) $product_no);
        return $this->safeExecQuery($sql);
    }
}
