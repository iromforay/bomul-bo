<?php

namespace App\Entity;

// app/sql/2026_08_05_payment_table.sql 의 payment 테이블 매핑 - service_apply 1건에 결제가 N건
// 붙을 수 있다(월구독 갱신 등). 무료/이벤트 신청도 pay_method=NONE, pay_amount=0으로 행을 남겨서
// "결제(신청) 이력이 있다"는 판단 기준을 하나로 통일한다.
class Payment_Entity
{
    private $payment_no; //결제 시퀀스(PK)
    private $apply_no; //신청서 번호(FK - service_apply.apply_no)
    private $customer_no; //고객 번호(FK - customer.customer_no) - 마이페이지 결제내역 조회를 apply_no 조인 없이 바로 하기 위한 비정규화 컬럼

    private $pay_method; //결제수단 - CARD/VBANK/TRANSFER/NONE(무료·이벤트)
    private $pg_provider; //PG사 (예: TOSS)
    private $pg_tid; //PG 거래ID (토스는 paymentKey)

    private $pay_amount; //결제금액 - 신청 시점 product.price 스냅샷
    private $pay_status; //결제상태 - READY/PAID/FAILED/CANCELLED/REFUNDED

    private $paid_at; //결제완료일시
    private $cancelled_at; //취소일시
    private $fail_reason; //실패사유
    private $pg_response; //PG사 응답 전문(json_encode) - 감사/디버깅용 원본 보관

    private $created_at; //생성일시
    private $updated_at; //수정일시

    /**
     * @return mixed
     */
    public function getPaymentNo()
    {
        return $this->payment_no;
    }

    /**
     * @param mixed $payment_no
     */
    public function setPaymentNo($payment_no): void
    {
        $this->payment_no = $payment_no;
    }

    /**
     * @return mixed
     */
    public function getApplyNo()
    {
        return $this->apply_no;
    }

    /**
     * @param mixed $apply_no
     */
    public function setApplyNo($apply_no): void
    {
        $this->apply_no = $apply_no;
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

    // CARD / VBANK / TRANSFER / NONE(무료-이벤트)
    /**
     * @return mixed
     */
    public function getPayMethod()
    {
        return $this->pay_method;
    }

    /**
     * @param mixed $pay_method
     */
    public function setPayMethod($pay_method): void
    {
        $this->pay_method = $pay_method;
    }

    /**
     * @return mixed
     */
    public function getPgProvider()
    {
        return $this->pg_provider;
    }

    /**
     * @param mixed $pg_provider
     */
    public function setPgProvider($pg_provider): void
    {
        $this->pg_provider = $pg_provider;
    }

    /**
     * @return mixed
     */
    public function getPgTid()
    {
        return $this->pg_tid;
    }

    /**
     * @param mixed $pg_tid
     */
    public function setPgTid($pg_tid): void
    {
        $this->pg_tid = $pg_tid;
    }

    // 신청 시점 실 청구금액 - product.price 스냅샷(나중에 상품가가 바뀌어도 과거 결제행은 그대로 보존)
    /**
     * @return mixed
     */
    public function getPayAmount()
    {
        return $this->pay_amount;
    }

    /**
     * @param mixed $pay_amount
     */
    public function setPayAmount($pay_amount): void
    {
        $this->pay_amount = $pay_amount;
    }

    // READY / PAID / FAILED / CANCELLED / REFUNDED
    /**
     * @return mixed
     */
    public function getPayStatus()
    {
        return $this->pay_status;
    }

    /**
     * @param mixed $pay_status
     */
    public function setPayStatus($pay_status): void
    {
        $this->pay_status = $pay_status;
    }

    /**
     * @return mixed
     */
    public function getPaidAt()
    {
        return $this->paid_at;
    }

    /**
     * @param mixed $paid_at
     */
    public function setPaidAt($paid_at): void
    {
        $this->paid_at = $paid_at;
    }

    /**
     * @return mixed
     */
    public function getCancelledAt()
    {
        return $this->cancelled_at;
    }

    /**
     * @param mixed $cancelled_at
     */
    public function setCancelledAt($cancelled_at): void
    {
        $this->cancelled_at = $cancelled_at;
    }

    /**
     * @return mixed
     */
    public function getFailReason()
    {
        return $this->fail_reason;
    }

    /**
     * @param mixed $fail_reason
     */
    public function setFailReason($fail_reason): void
    {
        $this->fail_reason = $fail_reason;
    }

    /**
     * @return mixed
     */
    public function getPgResponse()
    {
        return $this->pg_response;
    }

    /**
     * @param mixed $pg_response
     */
    public function setPgResponse($pg_response): void
    {
        $this->pg_response = $pg_response;
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
