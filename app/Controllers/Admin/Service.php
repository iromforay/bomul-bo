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

    public function getView($apply_no = null)
    {
        $service = new ServiceApplyService();
        $row = $service->get_row($apply_no);

        if (!$row) {
            session()->setFlashdata('err', '존재하지 않는 신청입니다.');
            return redirect()->to(SITE . '/Service');
        }

        $data['row'] = $row;
        echo $this->view("Service/view", $data);
    }

    public function getModify($apply_no = null)
    {
        $service = new ServiceApplyService();
        $row = $service->get_row($apply_no);

        if (!$row) {
            session()->setFlashdata('err', '존재하지 않는 신청입니다.');
            return redirect()->to(SITE . '/Service');
        }

        $data['row'] = $row;
        echo $this->view("Service/modify", $data);
    }

    public function postModify($apply_no = null)
    {
        $service = new ServiceApplyService();
        $result = $service->edit($apply_no, [
            'company_name'  => $this->request->getPost('company_name'),
            'ceo_name'      => $this->request->getPost('ceo_name'),
            'license_no'    => $this->request->getPost('license_no'),
            'biz_no'        => $this->request->getPost('biz_no'),
            'manager_name'  => $this->request->getPost('manager_name'),
            'manager_phone' => $this->request->getPost('manager_phone'),
            'manager_email' => $this->request->getPost('manager_email'),
            'tel'           => $this->request->getPost('tel'),
            'fax'           => $this->request->getPost('fax'),
            'zipcode'       => $this->request->getPost('zipcode'),
            'addr1'         => $this->request->getPost('addr1'),
            'addr2'         => $this->request->getPost('addr2'),
            'homepage_name' => $this->request->getPost('homepage_name'),
            'sub_domain'    => $this->request->getPost('sub_domain'),
            'custom_domain' => $this->request->getPost('custom_domain'),
            'memo'          => $this->request->getPost('memo'),
        ]);

        session()->setFlashdata($result['SUCCESS'] === 'TRUE' ? 'msg' : 'err', $result['MSG']);
        return redirect()->to(SITE . '/Service/view/' . (int) $apply_no);
    }

    // 승인 - 서버배정 + service_live 생성 + queue 적재까지 ServiceApplyService::approve()가 전부 처리
    public function postApprove($apply_no = null)
    {
        $service = new ServiceApplyService();
        $result = $service->approve($apply_no);

        session()->setFlashdata($result['SUCCESS'] === 'TRUE' ? 'msg' : 'err', $result['MSG']);
        return redirect()->to(SITE . '/Service');
    }

    // 승인완료(APPROVED)인데 service_live/queue가 안 만들어진 신청서를 뒤늦게 큐에 적재
    public function postRequeue($apply_no = null)
    {
        $service = new ServiceApplyService();
        $result = $service->requeue($apply_no);

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
