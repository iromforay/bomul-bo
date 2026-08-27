<?php

namespace App\Service;

use App\Entity\BoUsageDaily_Entity;

// #37 bo(테넌트 백오피스) 사용 현황 배치 - app/batch/bo_usage_scan.php 전용. "매일 배치가 전일 작업
// 현황을 가져오도록" - 각 테넌트의 {fid} DB에 직접 붙어 지정한 날짜(기본: 어제) 하루 동안의
// 로그인/기본세팅/컨텐츠 활동만 스캔해서 bo_usage_daily에 날짜별 로그로 저장한다(#36 visit_stat_daily와
// 동일한 성격 - 하루 전체 누적이 아니라 그날 있었던 일만).
//
// API 통신(bomul-api 경유)이 아니라 이 서버가 테넌트 DB에 직접 접속하는 이유: 극초기에는 테넌트 DB가
// bomul-bo와 같은 MySQL 호스트에 있어 원격 API가 필요 없다는 판단. 계정은 새로 만들지 않고
// config('Database')->default(중앙 DB 접속 계정)를 그대로 재사용 - bomul-api의 TenantProvisioner가
// 신규 테넌트 DB를 만들 때 쓰는 것과 동일한 전제.
class BoUsageScanService extends CI_Service
{
    private $liveModel;
    private $loginModel;
    private $dailyModel;

    public function __construct()
    {
        parent::__construct();
        $this->liveModel = model('App\Models\ServiceLiveModel', true, $this->db);
        $this->loginModel = model('App\Models\LoginSessionModel', true, $this->db);
        $this->dailyModel = model('App\Models\BoUsageDailyModel', true, $this->db);
    }

    // 반환값: [성공 건수, 실패 건수(테넌트 DB 연결 실패 등 - 다음 실행 때 재시도됨)]
    public function runForDate(string $statDate): array
    {
        $tenants = $this->liveModel->get_active_for_usage_scan();
        $loginFids = $this->loginModel->get_login_fids_on_date($statDate);

        $done = 0;
        $failed = 0;
        foreach ($tenants as $tenant) {
            $metrics = $this->scanTenantDb($tenant, $statDate);
            if ($metrics === null) {
                $failed++;
                continue;
            }

            $entity = new BoUsageDaily_Entity();
            $entity->setStatDate($statDate);
            $entity->setLiveNo($tenant['live_no']);
            $entity->setCustomerNo($tenant['customer_no']);
            $entity->setFid($tenant['fid']);
            $entity->setLoginYn(in_array($tenant['fid'], $loginFids, true) ? 'Y' : 'N');
            $entity->setSetupTouchYn($metrics['setup_touch_yn']);
            $entity->setContentAddCount($metrics['content_add_count']);

            $this->dailyModel->sql_upsert($entity) ? $done++ : $failed++;
        }

        return [$done, $failed];
    }

    // db_connect('default')를 그대로 못 쓴다 - 그 커넥션은 이 서비스가 물고 있는 중앙 bomul DB
    // (service_live 등)에 이미 연결돼 있는데, 여기서 USE `{fid}`를 해버리면 같은 커넥션을 쓰는 다른
    // 코드까지 DB가 바뀐다. 그래서 테넌트 DB 스캔은 별도 mysqli 커넥션을 새로 열어서 쓴다
    // (bomul-api TenantProvisioner와 동일한 이유).
    private function scanTenantDb(array $tenant, string $statDate): ?array
    {
        $dbConfig = config('Database')->default;
        $host = $tenant['db_host'] !== '' ? $tenant['db_host'] : $dbConfig['hostname'];
        $port = (int) ($tenant['db_port'] ?: ($dbConfig['port'] ?? 3306));

        $mysqli = @new \mysqli($host, $dbConfig['username'], $dbConfig['password'], $tenant['fid'], $port);
        if ($mysqli->connect_errno) {
            log_message('error', "[bo_usage_scan] {$tenant['fid']} DB 연결 실패: {$mysqli->connect_error}");
            return null;
        }
        $escapedDate = $mysqli->real_escape_string($statDate);

        try {
            // 기본세팅 - basic/menu/add_menu는 modify_date, img는 write_date(수정 없이 추가/삭제만) 중
            // 하나라도 그 날짜에 손댄 게 있으면 Y. 세부 건수보다 "그날 만졌는가" 여부만 필요해서 카운트는 안 함.
            $setupTouched = false;
            foreach ([['basic', 'modify_date'], ['menu', 'modify_date'], ['add_menu', 'modify_date'], ['img', 'write_date']] as [$table, $col]) {
                $row = $mysqli->query("SELECT COUNT(*) AS cnt FROM `{$table}` WHERE DATE(`{$col}`) = '{$escapedDate}'")->fetch_assoc();
                if ((int) $row['cnt'] > 0) {
                    $setupTouched = true;
                    break;
                }
            }

            // 컨텐츠 - 그 날짜에 "새로 등록된" 건수만(수정은 안 셈). property/note는 created_at, post는
            // write_date가 등록시각. property/note는 is_deleted 플래그가 있어 삭제건 제외, post는 없음.
            $contentAddCount = 0;

            $row = $mysqli->query("SELECT COUNT(*) AS cnt FROM `property` WHERE `is_deleted` = 0 AND DATE(`created_at`) = '{$escapedDate}'")->fetch_assoc();
            $contentAddCount += (int) $row['cnt'];

            $row = $mysqli->query("SELECT COUNT(*) AS cnt FROM `note` WHERE `is_deleted` = 0 AND DATE(`created_at`) = '{$escapedDate}'")->fetch_assoc();
            $contentAddCount += (int) $row['cnt'];

            $row = $mysqli->query("SELECT COUNT(*) AS cnt FROM `post` WHERE DATE(`write_date`) = '{$escapedDate}'")->fetch_assoc();
            $contentAddCount += (int) $row['cnt'];

            return [
                'setup_touch_yn'    => $setupTouched ? 'Y' : 'N',
                'content_add_count' => $contentAddCount,
            ];
        } catch (\mysqli_sql_exception $e) {
            log_message('error', "[bo_usage_scan] {$tenant['fid']} 스캔 쿼리 실패: " . $e->getMessage());
            return null;
        } finally {
            $mysqli->close();
        }
    }
}
