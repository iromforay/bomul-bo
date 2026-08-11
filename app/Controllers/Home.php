<?php

namespace App\Controllers;

use App\Service\BoardService;
use App\Service\ProductService;

class Home extends BaseController
{
    private  $vdir;
    public function __construct()
    {
        parent::__construct();
      //  $this->vdir="Admin/";
        return $this;
    }

    public function getindex()
    {

        $data=array();
        $data['products'] = (new ProductService())->get_usable_base_list();
        $data['inquiry_notice'] = session()->getFlashdata('inquiry_notice');
        $data['inquiry_error']  = session()->getFlashdata('inquiry_error');
        echo $this->view("default","index",$data);
    }

    // 홈페이지 "상담 신청" 섹션(#inquiry) 폼 - 비회원도 이름+연락처만으로 남길 수 있는 리드폼.
    // 통합 게시판(board_type='INQUIRY')에 저장, 회신은 Admin "/Board/inquiry"에서 처리.
    public function postInquiry()
    {
        $name    = trim((string) $this->request->getPost('inquiry_name'));
        $phone   = trim((string) $this->request->getPost('inquiry_phone'));
        $message = trim((string) $this->request->getPost('inquiry_message'));

        if ($name === '' || $phone === '') {
            session()->setFlashdata('inquiry_error', "성함과 연락처를 입력해주세요.");
            return redirect()->to(SITE . '/#inquiry');
        }

        $result = (new BoardService())->save('INQUIRY', [
            'title'        => $name . '님의 상담 신청',
            'content'      => $message !== '' ? $message : '(문의 내용 없음)',
            'writer_name'  => $name,
            'writer_phone' => $phone,
        ]);

        session()->setFlashdata($result['SUCCESS'] === 'TRUE' ? 'inquiry_notice' : 'inquiry_error', $result['SUCCESS'] === 'TRUE' ? "상담 신청이 접수되었습니다. 담당자가 곧 연락드리겠습니다." : $result['MSG']);
        return redirect()->to(SITE . '/#inquiry');
    }
}