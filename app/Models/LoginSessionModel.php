<?php
namespace App\Models;

// #37 bo 사용현황 배치용 - login_session은 각 fid의 bo 백오피스가 공유하는 중앙 세션 테이블
// (app/sql/full_backup.sql 기준, bomul-bo가 물고 있는 중앙 bomul DB에 이미 존재).
class LoginSessionModel extends CI_Model
{
    public function __construct($db)
    {
        parent::__construct($db, "login_session", "login_session_no");
        return $this;
    }

    // #37 배치용 - 특정 날짜에 로그인(접속) 기록이 있는 fid 목록. bo_usage_scan.php가 한 번만
    // 호출해서 전체 테넌트 스캔에 재사용(테넌트마다 따로 조회 안 함).
    public function get_login_fids_on_date($stat_date): array
    {
        $sql = "SELECT DISTINCT `fid`
                FROM `$this->table`
                WHERE DATE(`write_date`) = " . $this->db->escape($stat_date) . "
                  AND `fid` IS NOT NULL AND `fid` != ''";
        $rows = $this->getExecQuery($sql);
        return array_column($rows, 'fid');
    }
}
