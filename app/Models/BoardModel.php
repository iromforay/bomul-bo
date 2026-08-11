<?php

namespace App\Models;

use App\Entity\Board_Entity;

class BoardModel extends CI_Model
{
    public function __construct($db)
    {
        parent::__construct($db, "board", "board_no");
        return $this;
    }

    // 게시판 목록 - board_type 하나로 한정, 최신순. $onlyVisible=true면 www 공개 목록용으로
    // use_yn='Y'인 글만(Admin 목록은 숨김글도 다 보여야 해서 기본값 false)
    public function get_list_by_type($board_type, $onlyVisible = false, $start = 0, $limit = 0)
    {
        $_limit = "";
        if ($limit > 0 && $start > 0) {
            $_limit = " LIMIT $start,$limit ";
        }
        $sql = "SELECT * FROM `$this->table`
                WHERE `board_type` = " . $this->db->escape($board_type) .
                ($onlyVisible ? " AND `use_yn` = 'Y'" : "") . "
                ORDER BY `board_no` DESC " . $_limit;
        return $this->getExecQuery($sql);
    }

    public function incrementViewCount($board_no)
    {
        $sql = "UPDATE `$this->table` SET `view_count` = `view_count` + 1 WHERE `$this->primaryKey` = " . $this->db->escape((int) $board_no);
        return $this->safeExecQuery($sql);
    }

    public function sql_save(Board_Entity $entity)
    {
        $esc = function ($value) {
            return $value !== null && $value !== '' ? $this->db->escape($value) : 'NULL';
        };

        $osql = " INSERT INTO `$this->table` SET
                        `board_type`=%s, `title`=%s, `content`=%s,
                        `writer_name`=%s, `writer_phone`=%s, `customer_no`=%s,
                        `status`=%s, `reply_content`=%s, `replied_at`=%s,
                        `use_yn`=%s
                ";
        $sql = sprintf(
            $osql,
            $this->db->escape($entity->getBoardType()),
            $this->db->escape($entity->getTitle()),
            $this->db->escape($entity->getContent()),
            $esc($entity->getWriterName()),
            $esc($entity->getWriterPhone()),
            $esc($entity->getCustomerNo()),
            $this->db->escape($entity->getStatus() ?? 'WAIT'),
            $esc($entity->getReplyContent()),
            $esc($entity->getRepliedAt()),
            $this->db->escape($entity->getUseYn() ?? 'Y')
        );
        return $this->safeExecQuery($sql);
    }

    public function sql_edit(Board_Entity $entity)
    {
        $set = [];

        !is_null($entity->getTitle())        ? $set[] = "`title`=".         $this->db->escape($entity->getTitle())        : "";
        !is_null($entity->getContent())      ? $set[] = "`content`=".       $this->db->escape($entity->getContent())      : "";
        !is_null($entity->getWriterName())   ? $set[] = "`writer_name`=".   $this->db->escape($entity->getWriterName())   : "";
        !is_null($entity->getWriterPhone())  ? $set[] = "`writer_phone`=".  $this->db->escape($entity->getWriterPhone())  : "";
        !is_null($entity->getStatus())       ? $set[] = "`status`=".        $this->db->escape($entity->getStatus())       : "";
        !is_null($entity->getReplyContent()) ? $set[] = "`reply_content`=". $this->db->escape($entity->getReplyContent()) : "";
        !is_null($entity->getRepliedAt())    ? $set[] = "`replied_at`=".    $this->db->escape($entity->getRepliedAt())    : "";
        !is_null($entity->getUseYn())        ? $set[] = "`use_yn`=".        $this->db->escape($entity->getUseYn())        : "";

        if (count($set) === 0) {
            return false;
        }

        $sql = "UPDATE `$this->table` SET " . implode(",", $set) .
               " WHERE `$this->primaryKey` = " . $this->db->escape($entity->getBoardNo());

        return $this->safeExecQuery($sql);
    }

    public function sql_delete($board_no)
    {
        $sql = "DELETE FROM `$this->table` WHERE `$this->primaryKey` = " . $this->db->escape($board_no);
        return $this->safeExecQuery($sql);
    }
}
