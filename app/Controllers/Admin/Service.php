<?php

namespace App\Controllers\Admin;

use App\Service\ServiceApplyService;

// 사이드메뉴 "서비스 신청 현황" - 원래 Customer::getApply()였는데, 결제상태/승인처리 등으로
// 계속 커질 화면이라 Customer 하위가 아니라 독립 리소스로 분리했다.
class Service extends BaseController
{
    public function getIndex()
    {
        $service = new ServiceApplyService();
        $data['list'] = $service->get_list();

        echo $this->view("Service/list", $data);
    }

    // 승인 - 서버배정 + service_live 생성 + queue 적재까지 ServiceApplyService::approve()가 전부 처리
    public function postApprove($apply_no = null)
    {
        $service = new ServiceApplyService();
        $result = $service->approve($apply_no);

        session()->setFlashdata($result['SUCCESS'] === 'TRUE' ? 'msg' : 'err', $result['MSG']);
        return redirect()->to(SITE . '/Service');
    }

    public function postReject($apply_no = null)
    {
        $service = new ServiceApplyService();
        $result = $service->reject($apply_no, (string) $this->request->getPost('reject_reason'));

        session()->setFlashdata($result['SUCCESS'] === 'TRUE' ? 'msg' : 'err', $result['MSG']);
        return redirect()->to(SITE . '/Service');
    }
}
