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

    // action='update' 콜백 완료 시 사용 - ACTIVE/SUSPENDED(member.service Y/N에 대응)
    public function updateStatus($live_no, string $status)
    {
        $sql = "UPDATE `$this->table` SET `status` = " . $this->db->escape($status) . " WHERE `live_no` = " . $this->db->escape((int) $live_no);
        return $this->safeExecQuery($sql);
    }

    public function findByIdx($live_no)
    {
        $rows = $this->findbyid((int) $live_no);
        return $rows[0] ?? null;
    }

    // apply_no 1건당 service_live는 최대 1건 - 승인 시 딱 1번만 생성됨
    public function findByApplyNo($apply_no)
    {
        $sql = "SELECT * FROM `$this->table` WHERE `apply_no` = " . $this->db->escape((int) $apply_no) . " LIMIT 1";
        $rows = $this->getExecQuery($sql);
        return $rows[0] ?? null;
    }

    // 어드민 "서비스 라이브 현황" 목록 - 만료 임박한 건을 먼저 보도록 expire_date 오름차순.
    public function get_list_with_details()
    {
        $sql = "SELECT sl.*, sa.company_name, sa.ceo_name, sa.manager_name, sa.manager_phone, sa.sub_domain,
                       s.server_code, s.server_name
                FROM `$this->table` sl
                LEFT JOIN `service_apply` sa ON sa.apply_no = sl.apply_no
                LEFT JOIN `server_list` s ON s.server_no = sl.server_no
                ORDER BY sl.expire_date ASC";
        return $this->getExecQuery($sql);
    }

    // 어드민 "서비스 라이브 상세" - get_list_with_details()와 동일한 조인을 apply_no로 한정.
    // receiver_url까지 같이 가져와서 상세화면에 "세팅된 서버 정보"(host)로 보여준다.
    public function get_detail_by_apply_no($apply_no)
    {
        $sql = "SELECT sl.*, sa.company_name, sa.ceo_name, sa.manager_name, sa.manager_phone,
                       sa.manager_email, sa.sub_domain,
                       s.server_code, s.server_name, s.receiver_url
                FROM `$this->table` sl
                LEFT JOIN `service_apply` sa ON sa.apply_no = sl.apply_no
                LEFT JOIN `server_list` s ON s.server_no = sl.server_no
                WHERE sl.apply_no = " . $this->db->escape((int) $apply_no) . "
                LIMIT 1";
        $rows = $this->getExecQuery($sql);
        return $rows[0] ?? null;
    }

    // 기간 연장/축소 - 관리자가 큐 적재와 함께 즉시 반영(낙관적 갱신)할 때 사용
    public function updateExpireDate($live_no, string $newExpireDate)
    {
        $sql = "UPDATE `$this->table` SET `expire_date` = " . $this->db->escape($newExpireDate) . "
                WHERE `live_no` = " . $this->db->escape((int) $live_no);
        return $this->safeExecQuery($sql);
    }

    // service:collect-request 크론이 픽업할 대상 - status='REQUEST'인 건은 아직 실제 세팅(큐 적재)이
    // 안 됐거나 관리자가 재세팅을 요청한 건이다. enqueue에 필요한 신청서/상품/고객 정보까지 한 번에 조인.
    public function get_request_pending()
    {
        $sql = "SELECT sl.*, sa.company_name, sa.ceo_name, sa.biz_no, sa.license_no,
                       p.product_code, c.customer_id, c.customer_pw
                FROM `$this->table` sl
                LEFT JOIN `service_apply` sa ON sa.apply_no = sl.apply_no
                LEFT JOIN `product` p ON p.product_no = sa.product_no
                LEFT JOIN `customer` c ON c.customer_no = sl.customer_no
                WHERE sl.status = 'REQUEST'";
        return $this->getExecQuery($sql);
    }
}
