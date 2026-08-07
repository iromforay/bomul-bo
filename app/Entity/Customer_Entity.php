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
    private $service_yn;
    private $service_expire_date;
    private $biz_reg_file_dir;
    private $biz_reg_file_name;
    private $broker_reg_file_dir;
    private $broker_reg_file_name;
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

    // 서비스(구독) 사용여부 - 계정 사용여부(use_yn)와 별개. 결제로 Y가 되고 만료되면 N.
    /**
     * @return mixed
     */
    public function getServiceYn()
    {
        return $this->service_yn;
    }

    /**
     * @param mixed $service_yn
     */
    public function setServiceYn($service_yn): void
    {
        $this->service_yn = $service_yn;
    }

    /**
     * @return mixed
     */
    public function getServiceExpireDate()
    {
        return $this->service_expire_date;
    }

    /**
     * @param mixed $service_expire_date
     */
    public function setServiceExpireDate($service_expire_date): void
    {
        $this->service_expire_date = $service_expire_date;
    }

    // 사업자등록증/중개사무소등록증 파일 - File_Upload 라이브러리 결과의 file_path/file_name 그대로 저장
    /**
     * @return mixed
     */
    public function getBizRegFileDir()
    {
        return $this->biz_reg_file_dir;
    }

    /**
     * @param mixed $biz_reg_file_dir
     */
    public function setBizRegFileDir($biz_reg_file_dir): void
    {
        $this->biz_reg_file_dir = $biz_reg_file_dir;
    }

    /**
     * @return mixed
     */
    public function getBizRegFileName()
    {
        return $this->biz_reg_file_name;
    }

    /**
     * @param mixed $biz_reg_file_name
     */
    public function setBizRegFileName($biz_reg_file_name): void
    {
        $this->biz_reg_file_name = $biz_reg_file_name;
    }

    /**
     * @return mixed
     */
    public function getBrokerRegFileDir()
    {
        return $this->broker_reg_file_dir;
    }

    /**
     * @param mixed $broker_reg_file_dir
     */
    public function setBrokerRegFileDir($broker_reg_file_dir): void
    {
        $this->broker_reg_file_dir = $broker_reg_file_dir;
    }

    /**
     * @return mixed
     */
    public function getBrokerRegFileName()
    {
        return $this->broker_reg_file_name;
    }

    /**
     * @param mixed $broker_reg_file_name
     */
    public function setBrokerRegFileName($broker_reg_file_name): void
    {
        $this->broker_reg_file_name = $broker_reg_file_name;
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
