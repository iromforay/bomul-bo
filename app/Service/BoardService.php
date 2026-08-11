<?php

namespace App\Service;

use App\Entity\Board_Entity;
use App\Libraries\AT_Result;

// 통합 게시판(상담문의/QNA/공지사항) - 테이블 하나(board)를 board_type으로 나눠 쓴다.
// URL은 타입별로 분리해서 쓰되(/Board/inquiry, /Board/qna, /Board/notice) 실제 CRUD 로직은 공용.
class BoardService extends CI_Service
{
    // URL 세그먼트 => DB board_type 코드
    public const TYPES = [
        'inquiry' => 'INQUIRY',
        'qna'     => 'QNA',
        'notice'  => 'NOTICE',
    ];

    public const TYPE_LABELS = [
        'INQUIRY' => '상담문의',
        'QNA'     => 'QNA',
        'NOTICE'  => '공지사항',
    ];

    private $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = model('App\Models\BoardModel', true, $this->db);
    }

    // URL 세그먼트를 DB board_type 코드로 변환 - 정의되지 않은 타입이면 null
    public static function resolveType($urlType): ?string
    {
        return self::TYPES[$urlType] ?? null;
    }

    public static function typeLabel($board_type): string
    {
        return self::TYPE_LABELS[$board_type] ?? $board_type;
    }

    public function get_list($board_type)
    {
        return $this->model->get_list_by_type($board_type);
    }

    public function get_row($board_type, $board_no)
    {
        $row = $this->model->findbyid((int) $board_no);
        $row = $row[0] ?? null;

        // 다른 게시판 타입의 글 번호로 접근하는 것 방지
        if (!$row || $row['board_type'] !== $board_type) {
            return null;
        }

        return $row;
    }

    public function save($board_type, array $data): array
    {
        $title   = trim($data['title'] ?? '');
        $content = trim($data['content'] ?? '');

        if ($title === '' || $content === '') {
            return AT_Result::toFailData("제목과 내용을 입력해주세요.");
        }

        $entity = new Board_Entity();
        $entity->setBoardType($board_type);
        $entity->setTitle($title);
        $entity->setContent($content);
        $entity->setWriterName(trim($data['writer_name'] ?? '') ?: null);
        $entity->setStatus(($data['status'] ?? 'WAIT') === 'DONE' ? 'DONE' : 'WAIT');
        $entity->setUseYn(($data['use_yn'] ?? 'Y') === 'N' ? 'N' : 'Y');

        $reply_content = trim($data['reply_content'] ?? '');
        if ($reply_content !== '') {
            $entity->setReplyContent($reply_content);
            $entity->setRepliedAt(date('Y-m-d H:i:s'));
        }

        if ($this->model->sql_save($entity)) {
            return AT_Result::toSuccessData(self::typeLabel($board_type) . " 글이 등록되었습니다.");
        }

        return AT_Result::toFailData("등록에 실패했습니다.");
    }

    public function edit($board_type, $board_no, array $data): array
    {
        $board_no = (int) $board_no;

        if (!$this->get_row($board_type, $board_no)) {
            return AT_Result::toFailData("존재하지 않는 글입니다.");
        }

        $title   = trim($data['title'] ?? '');
        $content = trim($data['content'] ?? '');

        if ($title === '' || $content === '') {
            return AT_Result::toFailData("제목과 내용을 입력해주세요.");
        }

        $entity = new Board_Entity();
        $entity->setBoardNo($board_no);
        $entity->setTitle($title);
        $entity->setContent($content);
        $entity->setWriterName(trim($data['writer_name'] ?? '') ?: null);
        $entity->setStatus(($data['status'] ?? 'WAIT') === 'DONE' ? 'DONE' : 'WAIT');
        $entity->setUseYn(($data['use_yn'] ?? 'Y') === 'N' ? 'N' : 'Y');

        $reply_content = trim($data['reply_content'] ?? '');
        if ($reply_content !== '') {
            $entity->setReplyContent($reply_content);
            $entity->setRepliedAt(date('Y-m-d H:i:s'));
        }

        if ($this->model->sql_edit($entity)) {
            return AT_Result::toSuccessData("수정되었습니다.");
        }

        return AT_Result::toFailData("수정에 실패했습니다.");
    }

    public function delete($board_type, $board_no): array
    {
        $board_no = (int) $board_no;

        if (!$this->get_row($board_type, $board_no)) {
            return AT_Result::toFailData("존재하지 않는 글입니다.");
        }

        if ($this->model->sql_delete($board_no)) {
            return AT_Result::toSuccessData("삭제되었습니다.");
        }

        return AT_Result::toFailData("삭제에 실패했습니다.");
    }
}
