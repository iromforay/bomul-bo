<?php
namespace App\Models;

use App\Entity\VisitLog_Entity;

class VisitLogModel extends CI_Model
{
    public function __construct($db)
    {
        parent::__construct($db, "visit_log", "visit_log_no");
        return $this;
    }

    public function sql_save(VisitLog_Entity $entity)
    {
        $esc = function ($value) {
            return $value !== null && $value !== '' ? $this->db->escape($value) : 'NULL';
        };

        $osql = " INSERT INTO `$this->table` SET
                        `visitor_token`=%s, `page_path`=%s, `referrer_host`=%s, `search_keyword`=%s,
                        `utm_source`=%s, `utm_medium`=%s, `utm_campaign`=%s,
                        `device_type`=%s, `is_landing`=%s
                ";
        $sql = sprintf(
            $osql,
            $this->db->escape($entity->getVisitorToken()),
            $this->db->escape($entity->getPagePath()),
            $esc($entity->getReferrerHost()),
            $esc($entity->getSearchKeyword()),
            $esc($entity->getUtmSource()),
            $esc($entity->getUtmMedium()),
            $esc($entity->getUtmCampaign()),
            $this->db->escape($entity->getDeviceType() ?? 'PC'),
            $this->db->escape($entity->getIsLanding() ?? 'N')
        );
        return $this->safeExecQuery($sql);
    }

    // 배치(visit_stat_aggregate.php)용 - 특정일의 검색어/유입경로별 방문자수·가입전환수 집계.
    // is_landing='Y'인 행만 대상(세션 내 재방문은 유입경로 판단에서 제외). search_keyword/referrer_host의
    // NULL은 visit_stat_daily의 UNIQUE KEY가 NULL을 서로 다른 값으로 취급하는 문제를 피하려고
    // COALESCE로 빈 문자열('') sentinel로 맞춰서 그룹핑한다(VisitStatDailyModel 참고).
    public function get_daily_keyword_stats($stat_date)
    {
        $sql = "SELECT
                    COALESCE(vl.search_keyword, '') AS search_keyword,
                    COALESCE(vl.referrer_host, '') AS referrer_host,
                    COUNT(DISTINCT vl.visitor_token) AS visit_count,
                    COUNT(DISTINCT vc.customer_no) AS signup_count
                FROM `$this->table` vl
                LEFT JOIN `visit_conversion` vc ON vc.visitor_token = vl.visitor_token
                WHERE vl.is_landing = 'Y' AND DATE(vl.created_at) = " . $this->db->escape($stat_date) . "
                GROUP BY COALESCE(vl.search_keyword, ''), COALESCE(vl.referrer_host, '')";
        return $this->getExecQuery($sql);
    }
}
