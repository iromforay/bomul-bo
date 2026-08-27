<?php

namespace App\Entity;

// app/sql/2026_08_27_visit_log.sql 의 visit_conversion 테이블 매핑 - 방문자 토큰(vtok 쿠키)과
// 가입회원을 1:1로 잇는 매핑. visit_log와 조인해 "검색어별 가입 전환율"을 산출하는 용도.
class VisitConversion_Entity
{
    private $visit_conversion_no; //매핑 시퀀스(PK)
    private $visitor_token; //가입 시점 vtok 쿠키 값 - visit_log 조인용
    private $customer_no; //가입 완료된 회원(FK - customer.customer_no)
    private $created_at; //가입 완료 시각

    /**
     * @return mixed
     */
    public function getVisitConversionNo()
    {
        return $this->visit_conversion_no;
    }

    /**
     * @param mixed $visit_conversion_no
     */
    public function setVisitConversionNo($visit_conversion_no): void
    {
        $this->visit_conversion_no = $visit_conversion_no;
    }

    /**
     * @return mixed
     */
    public function getVisitorToken()
    {
        return $this->visitor_token;
    }

    /**
     * @param mixed $visitor_token
     */
    public function setVisitorToken($visitor_token): void
    {
        $this->visitor_token = $visitor_token;
    }

    /**
     * @return mixed
     */
    public function getCustomerNo()
    {
        return $this->customer_no;
    }

    /**
     * @param mixed $customer_no
     */
    public function setCustomerNo($customer_no): void
    {
        $this->customer_no = $customer_no;
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
}
