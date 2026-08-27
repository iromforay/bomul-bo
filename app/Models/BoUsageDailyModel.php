<?php
namespace App\Models;

use App\Entity\BoUsageDaily_Entity;

class BoUsageDailyModel extends CI_Model
{
    public function __construct($db)
    {
        parent::__construct($db, "bo_usage_daily", "bo_usage_daily_no");
        return $this;
    }

    // uk_stat_date_live_no 기준 UPSERT - 같은 날짜를 배치가 재실행돼도(재시도/수동 재집계) 값만 덮어씀.
    // 반환값 주의: VisitStatDailyModel::sql_upsert()와 동일한 이유로, insertID() 대신 DB 에러
    // 유무로 성공 판정한다(ON DUPLICATE KEY UPDATE만 일어나면 insertID()가 0일 수 있어서).
    public function sql_upsert(BoUsageDaily_Entity $entity): bool
    {
        $osql = " INSERT INTO `$this->table` SET
                        `stat_date`=%s, `live_no`=%s, `customer_no`=%s, `fid`=%s,
                        `login_yn`=%s, `setup_touch_yn`=%s, `content_add_count`=%s
                    ON DUPLICATE KEY UPDATE
                        `customer_no`=VALUES(`customer_no`), `fid`=VALUES(`fid`),
                        `login_yn`=VALUES(`login_yn`), `setup_touch_yn`=VALUES(`setup_touch_yn`),
                        `content_add_count`=VALUES(`content_add_count`)
                ";
        $sql = sprintf(
            $osql,
            $this->db->escape($entity->getStatDate()),
            $this->db->escape((int) $entity->getLiveNo()),
            $this->db->escape((int) $entity->getCustomerNo()),
            $this->db->escape($entity->getFid()),
            $this->db->escape($entity->getLoginYn() ?? 'N'),
            $this->db->escape($entity->getSetupTouchYn() ?? 'N'),
            $this->db->escape((int) $entity->getContentAddCount())
        );
        $this->safeExecQuery($sql);
        return (int) ($this->getDbError()['code'] ?? 0) === 0;
    }

    // 관리자 대시보드용 - 최근 N일 전체 테넌트 합계(가입사용자 활성화 추이 등에 사용)
    public function get_daily_totals($fromDate, $toDate)
    {
        $sql = "SELECT `stat_date`,
                       COUNT(*) AS tenant_count,
                       SUM(`login_yn` = 'Y') AS login_count,
                       SUM(`setup_touch_yn` = 'Y') AS setup_count,
                       SUM(`content_add_count`) AS content_add_total
                FROM `$this->table`
                WHERE `stat_date` BETWEEN " . $this->db->escape($fromDate) . " AND " . $this->db->escape($toDate) . "
                GROUP BY `stat_date`
                ORDER BY `stat_date`";
        return $this->getExecQuery($sql);
    }
}
