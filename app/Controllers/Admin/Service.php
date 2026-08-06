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
}
