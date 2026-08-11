<?php

namespace App\Models;

use App\Entity\ServerList_Entity;

class ServerListModel extends CI_Model
{
    public function __construct($db)
    {
        parent::__construct($db, "server_list", "server_no");
        return $this;
    }

    // 저장된 컬럼값 대신 실시간 COUNT()로 계산 - service_live 서브쿼리를 모든 SELECT에 공통으로 붙인다.
    // TERMINATED(종료)는 더 이상 이 서버를 점유하지 않는 상태라 카운트에서 제외.
    // (예전엔 server_list.tenant_count를 승인 시 +1만 하고 감소는 전혀 없어서, 큐 적재 실패/종료된
    // 건도 계속 누적되어 실제 배정 수와 어긋나는 문제가 있었음 - 2026-08-10 실시간 계산으로 전환)
    private function liveTenantCountSubquery(): string
    {
        return "(SELECT COUNT(*) FROM `service_live` sl WHERE sl.server_no = `$this->table`.server_no AND sl.status != 'TERMINATED')";
    }

    // 승인 시 배정할 서버 선정 - 설치가능(Y) + 정상가동(USE) 중 부하(실시간 tenant_count)가 가장 적은 곳
    public function findLeastLoaded()
    {
        $sql = "SELECT *, " . $this->liveTenantCountSubquery() . " AS `real_tenant_count`
                FROM `$this->table`
                WHERE `installable_yn` = 'Y' AND `status` = 'USE'
                ORDER BY `tenant_count` ASC, `server_no` ASC
                LIMIT 1";
        $rows = $this->getExecQuery($sql);
        return $rows[0] ?? null;
    }

    public function findByIdx($server_no)
    {
        $sql = "SELECT *, " . $this->liveTenantCountSubquery() . " AS `real_tenant_count`
                FROM `$this->table`
                WHERE `server_no` = " . $this->db->escape((int) $server_no) . "
                LIMIT 1";
        $rows = $this->getExecQuery($sql);
        return $rows[0] ?? null;
    }

    // 어드민 "서버 목록" - 전체, server_no순
    public function get_list($start = 0, $limit = 0)
    {
        $sql = "SELECT *, " . $this->liveTenantCountSubquery() . " AS `real_tenant_count`
                FROM `$this->table`
                ORDER BY `server_no` ASC";
        return $this->getExecQuery($sql);
    }

    // server_code 중복체크 - UNIQUE 컬럼
    public function existsByCode($server_code, $exclude_no = null): bool
    {
        $sql = "SELECT `server_no` FROM `$this->table` WHERE `server_code` = " . $this->db->escape($server_code);
        if ($exclude_no !== null) {
            $sql .= " AND `server_no` != " . $this->db->escape((int) $exclude_no);
        }
        $sql .= " LIMIT 1";
        $rows = $this->getExecQuery($sql);
        return !empty($rows);
    }

    public function sql_save(ServerList_Entity $entity)
    {
        $esc = function ($value) {
            return $value !== null && $value !== '' ? $this->db->escape($value) : 'NULL';
        };

        $osql = " INSERT INTO `$this->table` SET
                        `server_code`=%s, `server_name`=%s,
                        `db_host`=%s, `db_port`=%s,
                        `receiver_url`=%s, `api_key`=%s,
                        `tenant_count`=0,
                        `installable_yn`=%s, `status`=%s, `memo`=%s
                ";
        $sql = sprintf(
            $osql,
            $this->db->escape($entity->getServerCode()),
            $this->db->escape($entity->getServerName()),
            $esc($entity->getDbHost()),
            $esc($entity->getDbPort()),
            $this->db->escape($entity->getReceiverUrl()),
            $this->db->escape($entity->getApiKey()),
            $this->db->escape($entity->getInstallableYn() ?? 'Y'),
            $this->db->escape($entity->getStatus() ?? 'USE'),
            $esc($entity->getMemo())
        );
        return $this->safeExecQuery($sql);
    }

    public function sql_edit(ServerList_Entity $entity)
    {
        $esc = function ($value) {
            return $value !== null && $value !== '' ? $this->db->escape($value) : 'NULL';
        };

        $osql = " UPDATE `$this->table` SET
                        `server_code`=%s, `server_name`=%s,
                        `db_host`=%s, `db_port`=%s,
                        `receiver_url`=%s, `api_key`=%s,
                        `installable_yn`=%s, `status`=%s, `memo`=%s
                  WHERE `server_no` = %s
                ";
        $sql = sprintf(
            $osql,
            $this->db->escape($entity->getServerCode()),
            $this->db->escape($entity->getServerName()),
            $esc($entity->getDbHost()),
            $esc($entity->getDbPort()),
            $this->db->escape($entity->getReceiverUrl()),
            $this->db->escape($entity->getApiKey()),
            $this->db->escape($entity->getInstallableYn()),
            $this->db->escape($entity->getStatus()),
            $esc($entity->getMemo()),
            $this->db->escape((int) $entity->getServerNo())
        );
        return $this->safeExecQuery($sql);
    }

    public function sql_delete($server_no)
    {
        $sql = "DELETE FROM `$this->table` WHERE `server_no` = " . $this->db->escape((int) $server_no);
        return $this->safeExecQuery($sql);
    }
}
