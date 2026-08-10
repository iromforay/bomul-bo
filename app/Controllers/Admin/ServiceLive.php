<?php

namespace App\Controllers\Admin;

use App\Service\ServiceApplyService;

// 사이드메뉴 "서비스 라이브 현황" - 설치 완료되어 실제 운영중인 service_live 목록/상세와
// 기간 연장(빠른 버튼)/만료일 직접 지정(캘린더)/서비스 상태/설치진행상태 변경을 상세화면에서 처리한다.
// 신청/승인 흐름은 Service 컨트롤러가 담당.
class ServiceLive extends BaseController
{
    public function getIndex()
    {
        $service = new ServiceApplyService();
        $data['list'] = $service->get_live_list();

        echo $this->view("ServiceLive/list", $data);
    }

    public function getView($apply_no = null)
    {
        $service = new ServiceApplyService();
        $row = $service->get_live_row($apply_no);

        if (!$row) {
            session()->setFlashdata('err', '존재하지 않는 서비스입니다.');
            return redirect()->to(SITE . '/ServiceLive');
        }

        $data['row'] = $row;
        echo $this->view("ServiceLive/view", $data);
    }

    // 상세화면의 "30일/60일 연장" 버튼
    public function postExtend($apply_no = null)
    {
        $service = new ServiceApplyService();
        $days = (int) $this->request->getPost('days');
        $result = $service->extendByDays($apply_no, $days);

        session()->setFlashdata($result['SUCCESS'] === 'TRUE' ? 'msg' : 'err', $result['MSG']);
        return redirect()->to(SITE . '/ServiceLive/view/' . (int) $apply_no);
    }

    // 상세화면의 만료일 캘린더 직접 지정
    public function postSetExpire($apply_no = null)
    {
        $service = new ServiceApplyService();
        $expireDate = (string) $this->request->getPost('expire_date');
        $result = $service->setExpireDate($apply_no, $expireDate);

        session()->setFlashdata($result['SUCCESS'] === 'TRUE' ? 'msg' : 'err', $result['MSG']);
        return redirect()->to(SITE . '/ServiceLive/view/' . (int) $apply_no);
    }

    // 상세화면의 "서비스 상태" 드롭다운(요청/정상/유예/종료) 변경
    public function postSetStatus($apply_no = null)
    {
        $service = new ServiceApplyService();
        $status = (string) $this->request->getPost('status');
        $result = $service->changeStatus($apply_no, $status);

        session()->setFlashdata($result['SUCCESS'] === 'TRUE' ? 'msg' : 'err', $result['MSG']);
        return redirect()->to(SITE . '/ServiceLive/view/' . (int) $apply_no);
    }

    // 상세화면의 "설치진행상태" 드롭다운(대기/설치중/완료/실패) 변경
    public function postSetInstallStatus($apply_no = null)
    {
        $service = new ServiceApplyService();
        $installStatus = (string) $this->request->getPost('install_status');
        $result = $service->changeInstallStatus($apply_no, $installStatus);

        session()->setFlashdata($result['SUCCESS'] === 'TRUE' ? 'msg' : 'err', $result['MSG']);
        return redirect()->to(SITE . '/ServiceLive/view/' . (int) $apply_no);
    }
}
