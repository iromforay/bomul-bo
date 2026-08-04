<?php

namespace App\Controllers\Admin;

use App\Service\ManagerService;

class Manager extends BaseController
{
    public function getIndex()
    {
        $service = new ManagerService();
        $data['list'] = $service->get_list();

        echo $this->view("Manager/list", $data);
    }

    public function getWrite()
    {
        $data['row'] = null;
        echo $this->view("Manager/write", $data);
    }

    public function postWrite()
    {
        $service = new ManagerService();
        $result = $service->save([
            'manager_id'   => $this->request->getPost('manager_id'),
            'manager_pw'   => $this->request->getPost('manager_pw'),
            'manager_name' => $this->request->getPost('manager_name'),
            'use_yn'       => $this->request->getPost('use_yn'),
        ]);

        session()->setFlashdata($result['SUCCESS'] === 'TRUE' ? 'msg' : 'err', $result['MSG']);
        return redirect()->to(Admin_SITE . '/Manager');
    }

    public function getModify($manager_no = null)
    {
        $service = new ManagerService();
        $row = $service->get_row($manager_no);

        if (!$row) {
            session()->setFlashdata('err', '존재하지 않는 관리자입니다.');
            return redirect()->to(Admin_SITE . '/Manager');
        }

        $data['row'] = $row;
        echo $this->view("Manager/modify", $data);
    }

    public function postModify($manager_no = null)
    {
        $service = new ManagerService();
        $result = $service->edit($manager_no, [
            'manager_id'   => $this->request->getPost('manager_id'),
            'manager_pw'   => $this->request->getPost('manager_pw'),
            'manager_name' => $this->request->getPost('manager_name'),
            'use_yn'       => $this->request->getPost('use_yn'),
        ]);

        session()->setFlashdata($result['SUCCESS'] === 'TRUE' ? 'msg' : 'err', $result['MSG']);
        return redirect()->to(Admin_SITE . '/Manager');
    }

    public function postDelete($manager_no = null)
    {
        $service = new ManagerService();
        $current_no = session('login')['MB_NO'] ?? null;
        $result = $service->delete($manager_no, $current_no);

        session()->setFlashdata($result['SUCCESS'] === 'TRUE' ? 'msg' : 'err', $result['MSG']);
        return redirect()->to(Admin_SITE . '/Manager');
    }
}
