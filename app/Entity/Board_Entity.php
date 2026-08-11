<?php

namespace App\Entity;

// 통합 게시판 - board_type(INQUIRY/QNA/NOTICE)으로 상담문의/QNA/공지사항을 한 테이블에서 구분해 쓴다.
// app/sql/2026_08_11_board_table.sql 매핑.
class Board_Entity
{
    private $board_no;
    private $board_type;
    private $title;
    private $content;
    private $writer_name;
    private $customer_no;
    private $status;
    private $reply_content;
    private $replied_at;
    private $view_count;
    private $use_yn;
    private $created_at;
    private $updated_at;

    /**
     * @return mixed
     */
    public function getBoardNo()
    {
        return $this->board_no;
    }

    /**
     * @param mixed $board_no
     */
    public function setBoardNo($board_no): void
    {
        $this->board_no = $board_no;
    }

    // INQUIRY / QNA / NOTICE
    /**
     * @return mixed
     */
    public function getBoardType()
    {
        return $this->board_type;
    }

    /**
     * @param mixed $board_type
     */
    public function setBoardType($board_type): void
    {
        $this->board_type = $board_type;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title): void
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param mixed $content
     */
    public function setContent($content): void
    {
        $this->content = $content;
    }

    /**
     * @return mixed
     */
    public function getWriterName()
    {
        return $this->writer_name;
    }

    /**
     * @param mixed $writer_name
     */
    public function setWriterName($writer_name): void
    {
        $this->writer_name = $writer_name;
    }

    /**
     * @return mixed
     */
    public function getCustomerNo()
    {
        return $this->customer_no;
    }

    /**
     * @param mixed $customer_no
     */
    public function setCustomerNo($customer_no): void
    {
        $this->customer_no = $customer_no;
    }

    // WAIT(답변대기) / DONE(답변완료)
    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status): void
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getReplyContent()
    {
        return $this->reply_content;
    }

    /**
     * @param mixed $reply_content
     */
    public function setReplyContent($reply_content): void
    {
        $this->reply_content = $reply_content;
    }

    /**
     * @return mixed
     */
    public function getRepliedAt()
    {
        return $this->replied_at;
    }

    /**
     * @param mixed $replied_at
     */
    public function setRepliedAt($replied_at): void
    {
        $this->replied_at = $replied_at;
    }

    /**
     * @return mixed
     */
    public function getViewCount()
    {
        return $this->view_count;
    }

    /**
     * @param mixed $view_count
     */
    public function setViewCount($view_count): void
    {
        $this->view_count = $view_count;
    }

    /**
     * @return mixed
     */
    public function getUseYn()
    {
        return $this->use_yn;
    }

    /**
     * @param mixed $use_yn
     */
    public function setUseYn($use_yn): void
    {
        $this->use_yn = $use_yn;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @param mixed $created_at
     */
    public function setCreatedAt($created_at): void
    {
        $this->created_at = $created_at;
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * @param mixed $updated_at
     */
    public function setUpdatedAt($updated_at): void
    {
        $this->updated_at = $updated_at;
    }
}
