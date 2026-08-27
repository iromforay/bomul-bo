<?php

namespace App\Controllers;

use App\Service\BoardService;
use App\Service\CustomerService;

// www 공개 QNA 게시판 - 목록/상세는 비로그인도 볼 수 있고, 글쓰기만 로그인 고객으로 제한한다.
// 통합 게시판(board_type='QNA')을 그대로 씀 - Admin/Board.php와 같은 BoardService 공유.
class Qna extends BaseController
{
    private const BOARD_TYPE = 'QNA';

    public function getIndex()
    {
        $service = new BoardService();

        $data = [];
        $data['common']['title'] = 'Q&A';
        $data['list'] = $service->get_public_list(self::BOARD_TYPE);
        echo $this->view("default", "qna_list", $data);
    }

    public function getView($board_no = null)
    {
        $service = new BoardService();
        $row = $service->get_public_row(self::BOARD_TYPE, $board_no);

        if (!$row) {
            session()->setFlashdata('qna_error', "존재하지 않거나 비공개 처리된 글입니다.");
            return redirect()->to(SITE . '/qna');
        }

        $data = [];
        $data['common']['title'] = $row['title'] ?? 'Q&A';
        $data['row'] = $row;
        echo $this->view("default", "qna_view", $data);
    }

    public function getWrite()
    {
        $customer = $this->requireLogin();
        if ($customer === null) {
            return redirect()->to(SITE . '/login?redirect_url=' . urlencode($this->currentPathWithQuery()));
        }

        $data['common']['title'] = 'Q&A 작성';
        $data['common']['noindex'] = true;
        echo $this->view("default", "qna_write", $data);
    }

    public function postWrite()
    {
        $customer = $this->requireLogin();
        if ($customer === null) {
            return redirect()->to(SITE . '/login?redirect_url=' . urlencode($this->currentPathWithQuery()));
        }

        $service = new BoardService();
        $result = $service->save(self::BOARD_TYPE, [
            'title'       => $this->request->getPost('title'),
            'content'     => $this->request->getPost('content'),
            'writer_name' => $customer['company_name'] ?? $customer['customer_id'] ?? null,
            'customer_no' => $customer['customer_no'],
        ]);

        if ($result['SUCCESS'] === 'TRUE') {
            session()->setFlashdata('qna_notice', "문의가 등록되었습니다. 답변이 등록되면 이 게시판에서 확인하실 수 있습니다.");
            return redirect()->to(SITE . '/qna');
        }

        session()->setFlashdata('qna_error', $result['MSG']);
        return redirect()->to(SITE . '/qna/write');
    }

    // MyPage::requireLogin()과 동일한 로그인 게이트 - 공통 부모로 안 끌어올린 기존 관례를 따름.
    private function requireLogin(): ?array
    {
        $login = session('customer_login');
        if (!($login['is_login'] ?? false)) {
            session()->setFlashdata('login_error', "글쓰기는 로그인 후 이용 가능합니다.");
            return null;
        }

        $customer = (new CustomerService())->get_row($login['CUSTOMER_NO']);
        return $customer ?: null;
    }

    private function currentPathWithQuery(): string
    {
        $uri = $this->request->getUri();
        $path = '/' . ltrim($uri->getPath(), '/');
        $query = $uri->getQuery();
        return $path . ($query !== '' ? '?' . $query : '');
    }
}
