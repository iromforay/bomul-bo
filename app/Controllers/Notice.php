<?php

namespace App\Controllers;

use App\Service\BoardService;

// www 공개 공지사항 - 읽기 전용(글쓰기는 Admin "/Board/notice"에서만). 통합 게시판(board_type='NOTICE').
class Notice extends BaseController
{
    private const BOARD_TYPE = 'NOTICE';

    public function getIndex()
    {
        $service = new BoardService();

        $data = [];
        $data['list'] = $service->get_public_list(self::BOARD_TYPE);
        echo $this->view("default", "notice_list", $data);
    }

    public function getView($board_no = null)
    {
        $service = new BoardService();
        $row = $service->get_public_row(self::BOARD_TYPE, $board_no);

        if (!$row) {
            session()->setFlashdata('notice_error', "존재하지 않거나 비공개 처리된 글입니다.");
            return redirect()->to(SITE . '/notice');
        }

        $data = [];
        $data['row'] = $row;
        echo $this->view("default", "notice_view", $data);
    }
}
