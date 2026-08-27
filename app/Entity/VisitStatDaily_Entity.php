<?php

namespace App\Entity;

// app/sql/2026_08_27_visit_log.sql 의 visit_stat_daily 테이블 매핑 - visit_log/visit_conversion을
// 배치(app/batch/visit_stat_aggregate.php)가 하루 단위로 집계해서 적재하는 결과 테이블.
class VisitStatDaily_Entity
{
    private $visit_stat_daily_no; //집계행 시퀀스(PK)
    private $stat_date; //집계 대상일
    private $search_keyword; //검색어 - 없으면 빈 문자열('') sentinel (UNIQUE KEY의 NULL 중복행 방지, VisitService 참고)
    private $referrer_host; //리퍼러 호스트 - 없으면 빈 문자열('') sentinel
    private $visit_count; //해당일 해당 검색어/유입경로 방문자수(is_landing=Y distinct visitor_token)
    private $signup_count; //그 중 가입 전환된 방문자수(visit_conversion 조인)

    /**
     * @return mixed
     */
    public function getVisitStatDailyNo()
    {
        return $this->visit_stat_daily_no;
    }

    /**
     * @param mixed $visit_stat_daily_no
     */
    public function setVisitStatDailyNo($visit_stat_daily_no): void
    {
        $this->visit_stat_daily_no = $visit_stat_daily_no;
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
    public function getSearchKeyword()
    {
        return $this->search_keyword;
    }

    /**
     * @param mixed $search_keyword
     */
    public function setSearchKeyword($search_keyword): void
    {
        $this->search_keyword = $search_keyword;
    }

    /**
     * @return mixed
     */
    public function getReferrerHost()
    {
        return $this->referrer_host;
    }

    /**
     * @param mixed $referrer_host
     */
    public function setReferrerHost($referrer_host): void
    {
        $this->referrer_host = $referrer_host;
    }

    /**
     * @return mixed
     */
    public function getVisitCount()
    {
        return $this->visit_count;
    }

    /**
     * @param mixed $visit_count
     */
    public function setVisitCount($visit_count): void
    {
        $this->visit_count = $visit_count;
    }

    /**
     * @return mixed
     */
    public function getSignupCount()
    {
        return $this->signup_count;
    }

    /**
     * @param mixed $signup_count
     */
    public function setSignupCount($signup_count): void
    {
        $this->signup_count = $signup_count;
    }
}
