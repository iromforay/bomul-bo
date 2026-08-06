<?php

namespace App\Models;

use App\Entity\ServiceLive_Entity;

class ServiceLiveModel extends CI_Model
{
    public function __construct($db)
    {
        parent::__construct($db, "service_live", "live_no");
        return $this;
    }

    public function sql_save(ServiceLive_Entity $entity)
    {
        $esc = function ($value) {
            return $value !== null && $value !== '' ? $this->db->escape($value) : 'NULL';
        };

        $osql = " INSERT INTO `$this->table` SET
                        `customer_no`=%s, `apply_no`=%s, `server_no`=%s,
                        `fid`=%s, `license_key`=%s,
                        `status`=%s, `install_status`=%s,
                        `start_date`=%s, `expire_date`=%s
                ";
        $sql = sprintf(
            $osql,
            $this->db->escape($entity->getCustomerNo()),
            $this->db->escape($entity->getApplyNo()),
            $this->db->escape($entity->getServerNo()),
            $this->db->escape($entity->getFid()),
            $esc($entity->getLicenseKey()),
            $this->db->escape($entity->getStatus() ?? 'ACTIVE'),
            $this->db->escape($entity->getInstallStatus() ?? 'PENDING'),
            $esc($entity->getStartDate()),
            $esc($entity->getExpireDate())
        );
        return $this->safeExecQuery($sql);
    }

    // Admin\Callback이 운영서버 완료보고 받았을 때 갱신
    public function updateInstallStatus($live_no, string $installStatus, ?string $errorMsg = null)
    {
        $set = ["`install_status` = " . $this->db->escape($installStatus)];
        if ($installStatus === 'DONE') {
            $set[] = "`installed_at` = NOW()";
        }
        if ($errorMsg !== null) {
            $set[] = "`install_error` = " . $this->db->escape($errorMsg);
        }
        $sql = "UPDATE `$this->table` SET " . implode(",", $set) . " WHERE `live_no` = " . $this->db->escape((int) $live_no);
        return $this->safeExecQuery($sql);
    }

    public function findByIdx($live_no)
    {
        $rows = $this->findbyid((int) $live_no);
        return $rows[0] ?? null;
    }
}
