<?php

namespace App\Controllers\Admin;

use App\Service\ServerListService;

// 사이드메뉴 "서버 관리" - 테넌트 DB를 호스팅하는 운영서버(server_list) CRUD.
// 승인 시 최소부하 배정(findLeastLoaded)/push 대상 조회는 ServiceApplyService·QueuePush가 그대로 쓴다.
class ServerList extends BaseController
{
    public function getIndex()
    {
        $service = new ServerListService();
        $data['list'] = $service->get_list();

        echo $this->view("ServerList/list", $data);
    }

    public function getWrite()
    {
        $data['row'] = null;
        echo $this->view("ServerList/write", $data);
    }

    public function postWrite()
    {
        $service = new ServerListService();
        $result = $service->save([
            'server_code'    => $this->request->getPost('server_code'),
            'server_name'    => $this->request->getPost('server_name'),
            'db_host'        => $this->request->getPost('db_host'),
            'db_port'        => $this->request->getPost('db_port'),
            'receiver_url'   => $this->request->getPost('receiver_url'),
            'api_key'        => $this->request->getPost('api_key'),
            'installable_yn' => $this->request->getPost('installable_yn'),
            'status'         => $this->request->getPost('status'),
            'memo'           => $this->request->getPost('memo'),
        ]);

        session()->setFlashdata($result['SUCCESS'] === 'TRUE' ? 'msg' : 'err', $result['MSG']);
        return redirect()->to(SITE . '/ServerList');
    }

    public function getModify($server_no = null)
    {
        $service = new ServerListService();
        $row = $service->get_row($server_no);

        if (!$row) {
            session()->setFlashdata('err', '존재하지 않는 서버입니다.');
            return redirect()->to(SITE . '/ServerList');
        }

        $data['row'] = $row;
        echo $this->view("ServerList/modify", $data);
    }

    public function postModify($server_no = null)
    {
        $service = new ServerListService();
        $result = $service->edit($server_no, [
            'server_code'    => $this->request->getPost('server_code'),
            'server_name'    => $this->request->getPost('server_name'),
            'db_host'        => $this->request->getPost('db_host'),
            'db_port'        => $this->request->getPost('db_port'),
            'receiver_url'   => $this->request->getPost('receiver_url'),
            'api_key'        => $this->request->getPost('api_key'),
            'installable_yn' => $this->request->getPost('installable_yn'),
            'status'         => $this->request->getPost('status'),
            'memo'           => $this->request->getPost('memo'),
        ]);

        session()->setFlashdata($result['SUCCESS'] === 'TRUE' ? 'msg' : 'err', $result['MSG']);
        return redirect()->to(SITE . '/ServerList');
    }

    public function postDelete($server_no = null)
    {
        $service = new ServerListService();
        $result = $service->delete($server_no);

        session()->setFlashdata($result['SUCCESS'] === 'TRUE' ? 'msg' : 'err', $result['MSG']);
        return redirect()->to(SITE . '/ServerList');
    }
}
