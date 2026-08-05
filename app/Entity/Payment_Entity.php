<?php

namespace App\Entity;

// app/sql/2026_08_05_payment_table.sql 의 payment 테이블 매핑 - service_apply 1건에 결제가 N건
// 붙을 수 있다(월구독 갱신 등). 무료/이벤트 신청도 pay_method=NONE, pay_amount=0으로 행을 남겨서
// "결제(신청) 이력이 있다"는 판단 기준을 하나로 통일한다.
class Payment_Entity
{
    private $payment_idx;
    private $apply_idx;

    private $pay_method;
    private $pg_provider;
    private $pg_tid;

    private $pay_amount;
    private $pay_status;

    private $paid_at;
    private $cancelled_at;
    private $fail_reason;

    private $created_at;
    private $updated_at;

    /**
     * @return mixed
     */
    public function getPaymentIdx()
    {
        return $this->payment_idx;
    }

    /**
     * @param mixed $payment_idx
     */
    public function setPaymentIdx($payment_idx): void
    {
        $this->payment_idx = $payment_idx;
    }

    /**
     * @return mixed
     */
    public function getApplyIdx()
    {
        return $this->apply_idx;
    }

    /**
     * @param mixed $apply_idx
     */
    public function setApplyIdx($apply_idx): void
    {
        $this->apply_idx = $apply_idx;
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
