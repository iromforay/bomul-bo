<?php
namespace App\Models;

use App\Entity\VisitConversion_Entity;

class VisitConversionModel extends CI_Model
{
    public function __construct($db)
    {
        parent::__construct($db, "visit_conversion", "visit_conversion_no");
        return $this;
    }

    // uk_customer_no로 회원당 1건만 남는다 - INSERT IGNORE로 중복 호출(가입 로직 재시도 등)돼도 에러 없이 무시.
    public function sql_save(VisitConversion_Entity $entity)
    {
        $osql = " INSERT IGNORE INTO `$this->table` SET
                        `visitor_token`=%s, `customer_no`=%s
                ";
        $sql = sprintf(
            $osql,
            $this->db->escape($entity->getVisitorToken()),
            $this->db->escape((int) $entity->getCustomerNo())
        );
        return $this->safeExecQuery($sql);
    }
}
