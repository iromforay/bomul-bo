<?php

namespace App\Entity;

// app/sql/2026_08_27_visit_log.sql 의 visit_log 테이블 매핑 - www 페이지뷰 원본 로그.
// is_landing='Y'인 행만 유입경로/검색어 집계 대상(같은 방문자의 세션 내 2번째 이후 페이지뷰는
// referrer가 자사 사이트라 유입경로 판단에 의미 없음).
class VisitLog_Entity
{
    private $visit_log_no; //방문로그 시퀀스(PK)
    private $visitor_token; //익명 방문자 식별 토큰(vtok 쿠키) - 랜덤값, 개인정보 아님

    private $page_path; //요청 경로 (예: /, /qna)
    private $referrer_host; //리퍼러 호스트만 저장 (예: search.naver.com) - 전체 URL 미저장
    private $search_keyword; //검색엔진 리퍼러 쿼리스트링에서 추출한 검색어

    private $utm_source; //마케팅 링크 utm_source
    private $utm_medium; //마케팅 링크 utm_medium
    private $utm_campaign; //마케팅 링크 utm_campaign

    private $device_type; //PC/MOBILE - User-Agent 원본은 저장하지 않고 분류값만 저장
    private $is_landing; //해당 방문자 세션 내 첫 페이지뷰 여부(Y/N)

    private $created_at; //방문 시각

    /**
     * @return mixed
     */
    public function getVisitLogNo()
    {
        return $this->visit_log_no;
    }

    /**
     * @param mixed $visit_log_no
     */
    public function setVisitLogNo($visit_log_no): void
    {
        $this->visit_log_no = $visit_log_no;
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
    public function getPagePath()
    {
        return $this->page_path;
    }

    /**
     * @param mixed $page_path
     */
    public function setPagePath($page_path): void
    {
        $this->page_path = $page_path;
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
    public function getUtmSource()
    {
        return $this->utm_source;
    }

    /**
     * @param mixed $utm_source
     */
    public function setUtmSource($utm_source): void
    {
        $this->utm_source = $utm_source;
    }

    /**
     * @return mixed
     */
    public function getUtmMedium()
    {
        return $this->utm_medium;
    }

    /**
     * @param mixed $utm_medium
     */
    public function setUtmMedium($utm_medium): void
    {
        $this->utm_medium = $utm_medium;
    }

    /**
     * @return mixed
     */
    public function getUtmCampaign()
    {
        return $this->utm_campaign;
    }

    /**
     * @param mixed $utm_campaign
     */
    public function setUtmCampaign($utm_campaign): void
    {
        $this->utm_campaign = $utm_campaign;
    }

    /**
     * @return mixed
     */
    public function getDeviceType()
    {
        return $this->device_type;
    }

    /**
     * @param mixed $device_type
     */
    public function setDeviceType($device_type): void
    {
        $this->device_type = $device_type;
    }

    /**
     * @return mixed
     */
    public function getIsLanding()
    {
        return $this->is_landing;
    }

    /**
     * @param mixed $is_landing
     */
    public function setIsLanding($is_landing): void
    {
        $this->is_landing = $is_landing;
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
