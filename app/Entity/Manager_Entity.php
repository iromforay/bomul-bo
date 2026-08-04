<?php

namespace App\Entity;

// ad.bomul.ai.kr 백오피스 로그인 전용 계정. 중개사(향후 매물 서비스 사용자) 테이블과는
// 별개로 관리하기 위해 이름을 manager로 분리했다.
class Manager_Entity
{
    private $manager_no;
    private $manager_id;
    private $manager_pw;
    private $manager_name;
    private $use_yn;
    private $reg_date;

    /**
     * @return mixed
     */
    public function getManagerNo()
    {
        return $this->manager_no;
    }

    /**
     * @param mixed $manager_no
     */
    public function setManagerNo($manager_no): void
    {
        $this->manager_no = $manager_no;
    }

    /**
     * @return mixed
     */
    public function getManagerId()
    {
        return $this->manager_id;
    }

    /**
     * @param mixed $manager_id
     */
    public function setManagerId($manager_id): void
    {
        $this->manager_id = $manager_id;
    }

    /**
     * @return mixed
     */
    public function getManagerPw()
    {
        return $this->manager_pw;
    }

    /**
     * @param mixed $manager_pw
     */
    public function setManagerPw($manager_pw): void
    {
        $this->manager_pw = $manager_pw;
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
