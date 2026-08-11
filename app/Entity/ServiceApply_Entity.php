<?php

namespace App\Entity;

// app/sql/2026_08_05_service_table.sql 의 service_apply 테이블 매핑 - 로그인한 고객(customer)이
// 상품(product)을 선택해 넣는 서비스 신청서. 결제 연동 전까지는 status로만 진행 상태를 관리한다.
class ServiceApply_Entity
{
    private $apply_no;
    private $customer_no;
    private $product_no;
    private $apply_type;
    private $renew_live_no;

    private $company_name;
    private $ceo_name;

    private $license_no;
    private $biz_no;

    private $manager_name;
    private $manager_phone;
    private $manager_email;

    private $tel;
    private $fax;

    private $zipcode;
    private $addr1;
    private $addr2;

    private $homepage_name;
    private $sub_domain;
    private $custom_domain;

    private $memo;

    private $status;

    private $request_date;
    private $approve_date;
    private $reject_reason;

    private $created_at;
    private $updated_at;

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

    // NEW(신규) / RENEW(연장) - approve() 시 이 값으로 큐 action을 분기한다(new vs extend).
    /**
     * @return mixed
     */
    public function getApplyType()
    {
        return $this->apply_type;
    }

    /**
     * @param mixed $apply_type
     */
    public function setApplyType($apply_type): void
    {
        $this->apply_type = $apply_type;
    }

    // apply_type=RENEW일 때만 사용 - 연장 대상 service_live.live_no
    /**
     * @return mixed
     */
    public function getRenewLiveNo()
    {
        return $this->renew_live_no;
    }

    /**
     * @param mixed $renew_live_no
     */
    public function setRenewLiveNo($renew_live_no): void
    {
        $this->renew_live_no = $renew_live_no;
    }

    /**
     * @return mixed
     */
    public function getCompanyName()
    {
        return $this->company_name;
    }

    /**
     * @param mixed $company_name
     */
    public function setCompanyName($company_name): void
    {
        $this->company_name = $company_name;
    }

    /**
     * @return mixed
     */
    public function getCeoName()
    {
        return $this->ceo_name;
    }

    /**
     * @param mixed $ceo_name
     */
    public function setCeoName($ceo_name): void
    {
        $this->ceo_name = $ceo_name;
    }

    /**
     * @return mixed
     */
    public function getLicenseNo()
    {
        return $this->license_no;
    }

    /**
     * @param mixed $license_no
     */
    public function setLicenseNo($license_no): void
    {
        $this->license_no = $license_no;
    }

    /**
     * @return mixed
     */
    public function getBizNo()
    {
        return $this->biz_no;
    }

    /**
     * @param mixed $biz_no
     */
    public function setBizNo($biz_no): void
    {
        $this->biz_no = $biz_no;
    }

    /**
     * @return mixed
     */
    public function getManagerName()
    {
        return $this->manager_name;
    }

    /**
     * @param mixed $manager_name
     */
    public function setManagerName($manager_name): void
    {
        $this->manager_name = $manager_name;
    }

    /**
     * @return mixed
     */
    public function getManagerPhone()
    {
        return $this->manager_phone;
    }

    /**
     * @param mixed $manager_phone
     */
    public function setManagerPhone($manager_phone): void
    {
        $this->manager_phone = $manager_phone;
    }

    /**
     * @return mixed
     */
    public function getManagerEmail()
    {
        return $this->manager_email;
    }

    /**
     * @param mixed $manager_email
     */
    public function setManagerEmail($manager_email): void
    {
        $this->manager_email = $manager_email;
    }

    /**
     * @return mixed
     */
    public function getTel()
    {
        return $this->tel;
    }

    /**
     * @param mixed $tel
     */
    public function setTel($tel): void
    {
        $this->tel = $tel;
    }

    /**
     * @return mixed
     */
    public function getFax()
    {
        return $this->fax;
    }

    /**
     * @param mixed $fax
     */
    public function setFax($fax): void
    {
        $this->fax = $fax;
    }

    /**
     * @return mixed
     */
    public function getZipcode()
    {
        return $this->zipcode;
    }

    /**
     * @param mixed $zipcode
     */
    public function setZipcode($zipcode): void
    {
        $this->zipcode = $zipcode;
    }

    /**
     * @return mixed
     */
    public function getAddr1()
    {
        return $this->addr1;
    }

    /**
     * @param mixed $addr1
     */
    public function setAddr1($addr1): void
    {
        $this->addr1 = $addr1;
    }

    /**
     * @return mixed
     */
    public function getAddr2()
    {
        return $this->addr2;
    }

    /**
     * @param mixed $addr2
     */
    public function setAddr2($addr2): void
    {
        $this->addr2 = $addr2;
    }

    /**
     * @return mixed
     */
    public function getHomepageName()
    {
        return $this->homepage_name;
    }

    /**
     * @param mixed $homepage_name
     */
    public function setHomepageName($homepage_name): void
    {
        $this->homepage_name = $homepage_name;
    }

    // 희망 서브도메인 - customer.fid와는 별개로 신청서에서 다시 받는 값 (승인 시 fid 확정에 사용될 값)
    /**
     * @return mixed
     */
    public function getSubDomain()
    {
        return $this->sub_domain;
    }

    /**
     * @param mixed $sub_domain
     */
    public function setSubDomain($sub_domain): void
    {
        $this->sub_domain = $sub_domain;
    }

    /**
     * @return mixed
     */
    public function getCustomDomain()
    {
        return $this->custom_domain;
    }

    /**
     * @param mixed $custom_domain
     */
    public function setCustomDomain($custom_domain): void
    {
        $this->custom_domain = $custom_domain;
    }

    /**
     * @return mixed
     */
    public function getMemo()
    {
        return $this->memo;
    }

    /**
     * @param mixed $memo
     */
    public function setMemo($memo): void
    {
        $this->memo = $memo;
    }

    // REQUEST(신청완료) / REVIEW(검토중) / APPROVED(승인완료) / REJECTED(승인거절) / CANCEL(신청취소)
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

    /**
     * @return mixed
     */
    public function getRequestDate()
    {
        return $this->request_date;
    }

    /**
     * @param mixed $request_date
     */
    public function setRequestDate($request_date): void
    {
        $this->request_date = $request_date;
    }

    /**
     * @return mixed
     */
    public function getApproveDate()
    {
        return $this->approve_date;
    }

    /**
     * @param mixed $approve_date
     */
    public function setApproveDate($approve_date): void
    {
        $this->approve_date = $approve_date;
    }

    /**
     * @return mixed
     */
    public function getRejectReason()
    {
        return $this->reject_reason;
    }

    /**
     * @param mixed $reject_reason
     */
    public function setRejectReason($reject_reason): void
    {
        $this->reject_reason = $reject_reason;
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
