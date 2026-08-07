<?php

namespace App\Models;

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
}
