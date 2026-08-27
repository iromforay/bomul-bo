<?php
namespace App\Models;

use App\Entity\VisitStatDaily_Entity;

class VisitStatDailyModel extends CI_Model
{
    public function __construct($db)
    {
        parent::__construct($db, "visit_stat_daily", "visit_stat_daily_no");
        return $this;
    }

    // uk_stat_date_keyword_host 기준 UPSERT - 같은 날짜를 배치가 재실행돼도(재시도/수동 재집계)
    // 카운트를 덮어쓰기만 할 뿐 중복행이 안 생긴다.
    // 반환값 주의: safeExecQuery()는 INSERT 쿼리면 무조건 insertID()를 반환하는데, ON DUPLICATE
    // KEY UPDATE로 "기존 행 갱신"만 일어난 경우 MySQL이 insertID()를 0으로 줄 수 있어(falsy) 성공을
    // 실패로 오판할 수 있다 - 그래서 반환값 대신 DB 에러 유무로 직접 성공 여부를 판단한다.
    public function sql_upsert(VisitStatDaily_Entity $entity): bool
    {
        $osql = " INSERT INTO `$this->table` SET
                        `stat_date`=%s, `search_keyword`=%s, `referrer_host`=%s, `visit_count`=%s, `signup_count`=%s
                    ON DUPLICATE KEY UPDATE
                        `visit_count`=VALUES(`visit_count`), `signup_count`=VALUES(`signup_count`)
                ";
        $sql = sprintf(
            $osql,
            $this->db->escape($entity->getStatDate()),
            $this->db->escape((string) $entity->getSearchKeyword()),
            $this->db->escape((string) $entity->getReferrerHost()),
            $this->db->escape((int) $entity->getVisitCount()),
            $this->db->escape((int) $entity->getSignupCount())
        );
        $this->safeExecQuery($sql);
        return (int) ($this->getDbError()['code'] ?? 0) === 0;
    }

    // 관리자 통계 화면(다음 단계)에서 쓸 조회 - 특정일, 방문자수 많은 순
    public function get_by_date($stat_date)
    {
        $sql = "SELECT * FROM `$this->table`
                WHERE `stat_date` = " . $this->db->escape($stat_date) . "
                ORDER BY `visit_count` DESC";
        return $this->getExecQuery($sql);
    }
}
