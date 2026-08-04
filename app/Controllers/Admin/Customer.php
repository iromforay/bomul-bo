<?php

namespace App\Controllers\Admin;

use App\Service\CustomerService;

class Customer extends BaseController
{
    public function getIndex()
    {
        $service = new CustomerService();
        $data['list'] = $service->get_list();

        echo $this->view("Customer/list", $data);
    }

    public function getWrite()
    {
        echo $this->view("Customer/write", []);
    }

    public function postWrite()
    {
        $service = new CustomerService();
        $result = $service->save([
            'customer_id'   => $this->request->getPost('customer_id'),
            'customer_pw'   => $this->request->getPost('customer_pw'),
            'customer_name' => $this->request->getPost('customer_name'),
            'company_name'  => $this->request->getPost('company_name'),
            'biz_reg_no'    => $this->request->getPost('biz_reg_no'),
            'broker_reg_no' => $this->request->getPost('broker_reg_no'),
            'phone'         => $this->request->getPost('phone'),
            'email'         => $this->request->getPost('email'),
        ]);

        session()->setFlashdata($result['SUCCESS'] === 'TRUE' ? 'msg' : 'err', $result['MSG']);
        return redirect()->to(Admin_SITE . '/Customer');
    }
}
