<?php

namespace App\Entity;

// app/sql/2026_08_05_product_table.sql + app/sql/2026_08_11_product_type.sql
// + app/sql/2026_08_11_product_list_price.sql 의 product 테이블 매핑 -
// 서비스 신청 시 선택하는 결제 상품(구독형/1회성/이벤트). product_name은 순수하게 "무엇인지"만
// 담고(예: "중개사 사이트 개설"), 기간(1개월/6개월/구독형 등)은 billing_type+period_day로 별도
// 계산해서 배지로 보여준다 - render_helper.php의 product_period_label() 참고.
class Product_Entity
{
    private $product_no;
    private $product_code;
    private $product_type;
    private $product_name;
    private $price;
    private $list_price;
    private $billing_type;
    private $period_day;
    private $description;
    private $status;
    private $is_event;
    private $sort;
    private $created_at;
    private $updated_at;

    /**
     * @return mixed
     */
    public function getProductNo()
    {
        return $this->product_no;
    }

    /**
     * @param mixed $product_no
     */
    public function setProductNo($product_no): void
    {
        $this->product_no = $product_no;
    }

    /**
     * @return mixed
     */
    public function getProductCode()
    {
        return $this->product_code;
    }

    /**
     * @param mixed $product_code
     */
    public function setProductCode($product_code): void
    {
        $this->product_code = $product_code;
    }

    // BASE(기본상품, 예: 중개사 사이트 개설) / ADDON(부가서비스, 예: 디자인 템플릿 적용)
    /**
     * @return mixed
     */
    public function getProductType()
    {
        return $this->product_type;
    }

    /**
     * @param mixed $product_type
     */
    public function setProductType($product_type): void
    {
        $this->product_type = $product_type;
    }

    /**
     * @return mixed
     */
    public function getProductName()
    {
        return $this->product_name;
    }

    /**
     * @param mixed $product_name
     */
    public function setProductName($product_name): void
    {
        $this->product_name = $product_name;
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param mixed $price
     */
    public function setPrice($price): void
    {
        $this->price = $price;
    }

    // 할인 강조용 정가(원가) - price보다 크게 넣으면 화면에서 취소선 정가+판매가로 보여준다.
    // NULL이면 할인 표시 없이 price만 노출(app/sql/2026_08_11_product_list_price.sql).
    /**
     * @return mixed
     */
    public function getListPrice()
    {
        return $this->list_price;
    }

    /**
     * @param mixed $list_price
     */
    public function setListPrice($list_price): void
    {
        $this->list_price = $list_price;
    }

    // MONTHLY(월구독) / ONCE(1회결제) / NONE(미결제-이벤트/무통장)
    /**
     * @return mixed
     */
    public function getBillingType()
    {
        return $this->billing_type;
    }

    /**
     * @param mixed $billing_type
     */
    public function setBillingType($billing_type): void
    {
        $this->billing_type = $billing_type;
    }

    /**
     * @return mixed
     */
    public function getPeriodDay()
    {
        return $this->period_day;
    }

    /**
     * @param mixed $period_day
     */
    public function setPeriodDay($period_day): void
    {
        $this->period_day = $period_day;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description): void
    {
        $this->description = $description;
    }

    // USE(사용) / STOP(중지)
    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status): void
    {
        $this->status = $status;
    }

    // 1(프로모션 상품) / 0(일반 상품)
    /**
     * @return mixed
     */
    public function getIsEvent()
    {
        return $this->is_event;
    }

    /**
     * @param mixed $is_event
     */
    public function setIsEvent($is_event): void
    {
        $this->is_event = $is_event;
    }

    /**
     * @return mixed
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * @param mixed $sort
     */
    public function setSort($sort): void
    {
        $this->sort = $sort;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @param mixed $created_at
     */
    public function setCreatedAt($created_at): void
    {
        $this->created_at = $created_at;
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * @param mixed $updated_at
     */
    public function setUpdatedAt($updated_at): void
    {
        $this->updated_at = $updated_at;
    }
}
