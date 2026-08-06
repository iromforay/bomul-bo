<?php

namespace App\Models;

class QueueModel extends CI_Model
{
    public function __construct($db)
    {
        parent::__construct($db, "queue", "queue_no");
        return $this;
    }

    public function sql_save($live_no, $server_no, $fid, array $payload)
    {
        $sql = "INSERT INTO `$this->table` SET
                    `live_no`   = " . $this->db->escape((int) $live_no) . ",
                    `server_no` = " . $this->db->escape((int) $server_no) . ",
                    `fid`       = " . $this->db->escape($fid) . ",
                    `payload`   = " . $this->db->escape(json_encode($payload, JSON_UNESCAPED_UNICODE)) . ",
                    `status`    = 'PENDING'";
        return $this->safeExecQuery($sql);
    }

    // service cron(QueuePush)이 push 대상으로 가져갈 pending 목록 - 서버 접속정보까지 조인
    public function get_pending_with_server(int $limit = 20)
    {
        $sql = "SELECT q.*, s.receiver_url, s.api_key
                FROM `$this->table` q
                JOIN `server_list` s ON s.server_no = q.server_no
                WHERE q.status = 'PENDING'
                ORDER BY q.queue_no ASC
                LIMIT " . $limit;
        return $this->getExecQuery($sql);
    }

    public function markSent($queue_no)
    {
        $sql = "UPDATE `$this->table` SET `status`='SENT', `sent_at`=NOW() WHERE `queue_no` = " . $this->db->escape((int) $queue_no);
        return $this->safeExecQuery($sql);
    }

    public function markDone($queue_no)
    {
        $sql = "UPDATE `$this->table` SET `status`='DONE', `completed_at`=NOW() WHERE `queue_no` = " . $this->db->escape((int) $queue_no);
        return $this->safeExecQuery($sql);
    }

    public function markFailed($queue_no, string $reason)
    {
        $sql = "UPDATE `$this->table` SET `status`='FAILED', `fail_reason`=" . $this->db->escape($reason) . "
                WHERE `queue_no` = " . $this->db->escape((int) $queue_no);
        return $this->safeExecQuery($sql);
    }

    public function findByIdx($queue_no)
    {
        $rows = $this->findbyid((int) $queue_no);
        return $rows[0] ?? null;
    }
}
