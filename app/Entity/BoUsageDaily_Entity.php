<?php

namespace App\Entity;

// app/sql/2026_08_27_bo_usage_daily.sql 의 bo_usage_daily 테이블 매핑 - app/batch/bo_usage_scan.php가
// 전일치로 스캔한 "그 날짜의" bo 활동 로그(날짜+live_no당 1행, #36 visit_stat_daily와 동일한 구조).
class BoUsageDaily_Entity
{
    private $bo_usage_daily_no; //시퀀스(PK)
    private $stat_date; //집계 대상일(전일)
    private $live_no; //라이브 서비스 IDX(FK - service_live.live_no)
    private $customer_no; //고객 IDX - live_no 조인 없이 바로 조회하기 위한 비정규화
    private $fid; //테넌트 식별자(=접속한 DB명)

    private $login_yn; //그 날짜에 bo 로그인 여부(중앙 login_session 기준)
    private $setup_touch_yn; //그 날짜에 기본세팅 테이블을 손댔는지
    private $content_add_count; //그 날짜에 새로 등록된 컨텐츠(property+note+post) 건수

    /**
     * @return mixed
     */
    public function getBoUsageDailyNo()
    {
        return $this->bo_usage_daily_no;
    }

    /**
     * @param mixed $bo_usage_daily_no
     */
    public function setBoUsageDailyNo($bo_usage_daily_no): void
    {
        $this->bo_usage_daily_no = $bo_usage_daily_no;
    }

    /**
     * @return mixed
     */
    public function getStatDate()
    {
        return $this->stat_date;
    }

    /**
     * @param mixed $stat_date
     */
    public function setStatDate($stat_date): void
    {
        $this->stat_date = $stat_date;
    }

    /**
     * @return mixed
     */
    public function getLiveNo()
    {
        return $this->live_no;
    }

    /**
     * @param mixed $live_no
     */
    public function setLiveNo($live_no): void
    {
        $this->live_no = $live_no;
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

    /**
     * @return mixed
     */
    public function getLoginYn()
    {
        return $this->login_yn;
    }

    /**
     * @param mixed $login_yn
     */
    public function setLoginYn($login_yn): void
    {
        $this->login_yn = $login_yn;
    }

    /**
     * @return mixed
     */
    public function getSetupTouchYn()
    {
        return $this->setup_touch_yn;
    }

    /**
     * @param mixed $setup_touch_yn
     */
    public function setSetupTouchYn($setup_touch_yn): void
    {
        $this->setup_touch_yn = $setup_touch_yn;
    }

    /**
     * @return mixed
     */
    public function getContentAddCount()
    {
        return $this->content_add_count;
    }

    /**
     * @param mixed $content_add_count
     */
    public function setContentAddCount($content_add_count): void
    {
        $this->content_add_count = $content_add_count;
    }
}
