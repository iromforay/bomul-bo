<?php

namespace App\Entity;

// 서비스 고객(중개사) 회원가입 계정. manager(백오피스 운영자)와는 별개.
class Member_Entity
{
    private $member_no;
    private $member_id;
    private $member_pw;
    private $member_name;
    private $company_name;
    private $biz_reg_no;
    private $broker_reg_no;
    private $phone;
    private $email;
    private $biz_reg_verified_yn;
    private $broker_reg_verified_yn;
    private $use_yn;
    private $reg_date;

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
    public function getMemberId()
    {
        return $this->member_id;
    }

    /**
     * @param mixed $member_id
     */
    public function setMemberId($member_id): void
    {
        $this->member_id = $member_id;
    }

    /**
     * @return mixed
     */
    public function getMemberPw()
    {
        return $this->member_pw;
    }

    /**
     * @param mixed $member_pw
     */
    public function setMemberPw($member_pw): void
    {
        $this->member_pw = $member_pw;
    }

    /**
     * @return mixed
     */
    public function getMemberName()
    {
        return $this->member_name;
    }

    /**
     * @param mixed $member_name
     */
    public function setMemberName($member_name): void
    {
        $this->member_name = $member_name;
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
