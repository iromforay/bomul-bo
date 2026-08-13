<?php

namespace App\Controllers;

use App\Libraries\AT_Result;
use App\Service\BoardService;

// 독립 "상담 신청" 페이지 - 홈 "#inquiry" 섹션 리드폼과 같은 목적이지만 서비스신청(ServiceApply)처럼
// 별도 URL(/inquiry)로도 접근 가능하게 분리. 비회원도 이름+연락처만으로 남길 수 있다(해피콜 모델 -
// 자기글조회는 없음, 관리자가 남긴 연락처로 직접 회신). 통합 게시판(board_type='INQUIRY')에 저장,
// 회신은 Admin "/Board/inquiry"에서 처리.
//
// postindex()는 페이지 이동 없이 제출하는 게 좋다는 요청으로 AJAX 전용 - JSON만 반환한다
// (public/resources/js/common/inquiry.js가 fetch로 호출, 두 폼(홈 #inquiry 섹션 + 이 페이지)이 공유).
class Inquiry extends BaseController
{
    public function getindex()
    {
        echo $this->view("default", "inquiry", []);
    }

    public function postindex()
    {
        $name    = trim((string) $this->request->getPost('inquiry_name'));
        $phone   = trim((string) $this->request->getPost('inquiry_phone'));
        $message = trim((string) $this->request->getPost('inquiry_message'));

        if ($name === '' || $phone === '') {
            echo AT_Result::toJson(AT_Result::toFailData("성함과 연락처를 입력해주세요."));
            exit;
        }

        $result = (new BoardService())->save('INQUIRY', [
            'title'        => $name . '님의 상담 신청',
            'content'      => $message !== '' ? $message : '(문의 내용 없음)',
            'writer_name'  => $name,
            'writer_phone' => $phone,
        ]);

        if ($result['SUCCESS'] === 'TRUE') {
            $result['MSG'] = "상담 신청이 접수되었습니다. 담당자가 곧 연락드리겠습니다.";
        }

        echo AT_Result::toJson($result);
        exit;
    }
}
