<?php

namespace App\Entity;

// 가입~검증~서비스신청(결제 전) 단계의 고객(중개사) 정보.
// 결제까지 끝나 실사용자가 되면 이 정보를 중앙 member 테이블로 이관한다 - member 자체는
// bo.bomul.ai.kr(중개사 로그인)이 쓰는 중앙 DB 테이블이라 여기서 직접 건드리지 않는다.
class Customer_Entity
{
    private $customer_no;
    private $customer_id;
    private $customer_pw;
    private $customer_name;
    private $company_name;
    private $biz_reg_no;
    private $broker_reg_no;
    private $phone;
    private $email;
    private $biz_reg_verified_yn;
    private $broker_reg_verified_yn;
    private $use_yn;
    private $fid;
    private $member_no;
    private $migrated_yn;
    private $migrated_date;
    private $reg_date;

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
    public function getCustomerId()
    {
        return $this->customer_id;
    }

    /**
     * @param mixed $customer_id
     */
    public function setCustomerId($customer_id): void
    {
        $this->customer_id = $customer_id;
    }

    /**
     * @return mixed
     */
    public function getCustomerPw()
    {
        return $this->customer_pw;
    }

    /**
     * @param mixed $customer_pw
     */
    public function setCustomerPw($customer_pw): void
    {
        $this->customer_pw = $customer_pw;
    }

    /**
     * @return mixed
     */
    public function getCustomerName()
    {
        return $this->customer_name;
    }

    /**
     * @param mixed $customer_name
     */
    public function setCustomerName($customer_name): void
    {
        $this->customer_name = $customer_name;
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
    public function getBizRegNo()
    {
        return $this->biz_reg_no;
    }

    /**
     * @param mixed $biz_reg_no
     */
    public function setBizRegNo($biz_reg_no): void
    {
        $this->biz_reg_no = $biz_reg_no;
    }

    /**
     * @return mixed
     */
    public function getBrokerRegNo()
    {
        return $this->broker_reg_no;
    }

    /**
     * @param mixed $broker_reg_no
     */
    public function setBrokerRegNo($broker_reg_no): void
    {
        $this->broker_reg_no = $broker_reg_no;
    }

    /**
     * @return mixed
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param mixed $phone
     */
    public function setPhone($phone): void
    {
        $this->phone = $phone;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email): void
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getBizRegVerifiedYn()
    {
        return $this->biz_reg_verified_yn;
    }

    /**
     * @param mixed $biz_reg_verified_yn
     */
    public function setBizRegVerifiedYn($biz_reg_verified_yn): void
    {
        $this->biz_reg_verified_yn = $biz_reg_verified_yn;
    }

    /**
     * @return mixed
     */
    public function getBrokerRegVerifiedYn()
    {
        return $this->broker_reg_verified_yn;
    }

    /**
     * @param mixed $broker_reg_verified_yn
     */
    public function setBrokerRegVerifiedYn($broker_reg_verified_yn): void
    {
        $this->broker_reg_verified_yn = $broker_reg_verified_yn;
    }

    /**
     * @return mixed
     */
    public function getUseYn()
    {
        return $this->use_yn;
    }

    /**
     * @param mixed $use_yn
     */
    public function setUseYn($use_yn): void
    {
        $this->use_yn = $use_yn;
    }

    // 2차도메인 - 가입 시점엔 null, 서비스 신청 단계에서 고객이 입력/선택
    /**
     * @return mixed
     */
    public function getFid()
    {
        return $this->fid;
    }

    /**
     * @param mixed $fid
     */
    public function setFid($fid): void
    {
        $this->fid = $fid;
    }

    // 결제 완료로 이관됐을 때 채워지는 중앙 member 테이블의 PK (이관 전엔 null)
    /**
     * @return mixed
     */
    public function getMemberNo()
    {
        return $this->member_no;
    }

    /**
     * @param mixed $member_no
     */
    public function setMemberNo($member_no): void
    {
        $this->member_no = $member_no;
    }

    /**
     * @return mixed
     */
    public function getMigratedYn()
    {
        return $this->migrated_yn;
    }

    /**
     * @param mixed $migrated_yn
     */
    public function setMigratedYn($migrated_yn): void
    {
        $this->migrated_yn = $migrated_yn;
    }

    /**
     * @return mixed
     */
    public function getMigratedDate()
    {
        return $this->migrated_date;
    }

    /**
     * @param mixed $migrated_date
     */
    public function setMigratedDate($migrated_date): void
    {
        $this->migrated_date = $migrated_date;
    }

    /**
     * @return mixed
     */
    public function getRegDate()
    {
        return $this->reg_date;
    }

    /**
     * @param mixed $reg_date
     */
    public function setRegDate($reg_date): void
    {
        $this->reg_date = $reg_date;
    }
}
