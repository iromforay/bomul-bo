<?php

namespace App\Controllers\Admin;

use App\Service\BoardService;

// 통합 게시판 Admin CRUD - URL의 {type}(inquiry/qna/notice)을 BoardService::resolveType()으로
// board_type 코드로 변환해서 공용 로직을 태운다. 정의되지 않은 타입이면 목록으로 돌려보낸다.
class Board extends BaseController
{
    public function getIndex($type = null)
    {
        $board_type = BoardService::resolveType($type);
        if (!$board_type) {
            return redirect()->to(SITE . '/Home');
        }

        $service = new BoardService();
        $data['type']      = $type;
        $data['typeLabel'] = BoardService::typeLabel($board_type);
        $data['list']      = $service->get_list($board_type);

        echo $this->view("Board/list", $data);
    }

    public function getWrite($type = null)
    {
        $board_type = BoardService::resolveType($type);
        if (!$board_type) {
            return redirect()->to(SITE . '/Home');
        }

        $data['type']      = $type;
        $data['typeLabel'] = BoardService::typeLabel($board_type);
        $data['row']       = null;
        echo $this->view("Board/write", $data);
    }

    public function postWrite($type = null)
    {
        $board_type = BoardService::resolveType($type);
        if (!$board_type) {
            return redirect()->to(SITE . '/Home');
        }

        $service = new BoardService();
        $result = $service->save($board_type, [
            'title'         => $this->request->getPost('title'),
            'content'       => $this->request->getPost('content'),
            'writer_name'   => $this->request->getPost('writer_name'),
            'writer_phone'  => $this->request->getPost('writer_phone'),
            'status'        => $this->request->getPost('status'),
            'reply_content' => $this->request->getPost('reply_content'),
            'use_yn'        => $this->request->getPost('use_yn'),
        ]);

        session()->setFlashdata($result['SUCCESS'] === 'TRUE' ? 'msg' : 'err', $result['MSG']);
        return redirect()->to(SITE . '/Board/' . $type);
    }

    public function getModify($type = null, $board_no = null)
    {
        $board_type = BoardService::resolveType($type);
        if (!$board_type) {
            return redirect()->to(SITE . '/Home');
        }

        $service = new BoardService();
        $row = $service->get_row($board_type, $board_no);

        if (!$row) {
            session()->setFlashdata('err', '존재하지 않는 글입니다.');
            return redirect()->to(SITE . '/Board/' . $type);
        }

        $data['type']      = $type;
        $data['typeLabel'] = BoardService::typeLabel($board_type);
        $data['row']       = $row;
        echo $this->view("Board/modify", $data);
    }

    public function postModify($type = null, $board_no = null)
    {
        $board_type = BoardService::resolveType($type);
        if (!$board_type) {
            return redirect()->to(SITE . '/Home');
        }

        $service = new BoardService();
        $result = $service->edit($board_type, $board_no, [
            'title'         => $this->request->getPost('title'),
            'content'       => $this->request->getPost('content'),
            'writer_name'   => $this->request->getPost('writer_name'),
            'writer_phone'  => $this->request->getPost('writer_phone'),
            'status'        => $this->request->getPost('status'),
            'reply_content' => $this->request->getPost('reply_content'),
            'use_yn'        => $this->request->getPost('use_yn'),
        ]);

        session()->setFlashdata($result['SUCCESS'] === 'TRUE' ? 'msg' : 'err', $result['MSG']);
        return redirect()->to(SITE . '/Board/' . $type);
    }

    public function postDelete($type = null, $board_no = null)
    {
        $board_type = BoardService::resolveType($type);
        if (!$board_type) {
            return redirect()->to(SITE . '/Home');
        }

        $service = new BoardService();
        $result = $service->delete($board_type, $board_no);

        session()->setFlashdata($result['SUCCESS'] === 'TRUE' ? 'msg' : 'err', $result['MSG']);
        return redirect()->to(SITE . '/Board/' . $type);
    }
}
