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

    // 승인 시 배정할 서버 선정 - 설치가능(Y) + 정상가동(USE) 중 부하(tenant_count)가 가장 적은 곳
    public function findLeastLoaded()
    {
        $sql = "SELECT * FROM `$this->table`
                WHERE `installable_yn` = 'Y' AND `status` = 'USE'
                ORDER BY `tenant_count` ASC, `server_no` ASC
                LIMIT 1";
        $rows = $this->getExecQuery($sql);
        return $rows[0] ?? null;
    }

    public function incrementTenantCount($server_no)
    {
        $sql = "UPDATE `$this->table` SET `tenant_count` = `tenant_count` + 1 WHERE `server_no` = " . $this->db->escape((int) $server_no);
        return $this->safeExecQuery($sql);
    }

    public function findByIdx($server_no)
    {
        $rows = $this->findbyid((int) $server_no);
        return $rows[0] ?? null;
    }

    // 어드민 "서버 목록" - 전체, server_no순
    public function get_list($start = 0, $limit = 0)
    {
        $sql = "SELECT * FROM `$this->table` ORDER BY `server_no` ASC";
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
