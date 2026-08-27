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
}
