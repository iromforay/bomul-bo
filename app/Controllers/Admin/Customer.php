<?php

namespace App\Controllers\Admin;

use App\Libraries\AT_Result;
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

    public function getView($customer_no = null)
    {
        $service = new CustomerService();
        $row = $service->get_row($customer_no);

        if (!$row) {
            session()->setFlashdata('err', '존재하지 않는 고객입니다.');
            return redirect()->to(SITE . '/Customer');
        }

        $data['row'] = $row;
        echo $this->view("Customer/view", $data);
    }

    public function getModify($customer_no = null)
    {
        $service = new CustomerService();
        $row = $service->get_row($customer_no);

        if (!$row) {
            session()->setFlashdata('err', '존재하지 않는 고객입니다.');
            return redirect()->to(SITE . '/Customer');
        }

        $data['row'] = $row;
        echo $this->view("Customer/modify", $data);
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
            // 현재 등록 화면엔 드롭존 UI가 없어 항상 비어있지만, 나중에 붙이면 바로 동작하도록 미리 연결
            'biz_reg_file_name'    => $this->request->getPost('biz_reg_file_name'),
            'broker_reg_file_name' => $this->request->getPost('broker_reg_file_name'),
        ]);

        session()->setFlashdata($result['SUCCESS'] === 'TRUE' ? 'msg' : 'err', $result['MSG']);
        return redirect()->to(SITE . '/Customer');
    }

    public function postModify($customer_no = null)
    {
        $service = new CustomerService();
        $result = $service->edit($customer_no, [
            'customer_id'            => $this->request->getPost('customer_id'),
            'customer_pw'            => $this->request->getPost('customer_pw'),
            'customer_name'          => $this->request->getPost('customer_name'),
            'company_name'           => $this->request->getPost('company_name'),
            'biz_reg_no'             => $this->request->getPost('biz_reg_no'),
            'broker_reg_no'          => $this->request->getPost('broker_reg_no'),
            'phone'                  => $this->request->getPost('phone'),
            'email'                  => $this->request->getPost('email'),
            'biz_reg_verified_yn'    => $this->request->getPost('biz_reg_verified_yn'),
            'broker_reg_verified_yn' => $this->request->getPost('broker_reg_verified_yn'),
            'use_yn'                 => $this->request->getPost('use_yn'),
            'service_yn'             => $this->request->getPost('service_yn'),
            'service_expire_date'    => $this->request->getPost('service_expire_date'),
            // 드래그드랍 위젯이 채워 넣은 temp 파일 경유 필드 (customer/temp/에 이미 올라가 있는 파일명)
            'biz_reg_file_name'      => $this->request->getPost('biz_reg_file_name'),
            'broker_reg_file_name'   => $this->request->getPost('broker_reg_file_name'),
            // 삭제 대상 - 값이 'biz'/'broker'인 hidden input이 활성화된 것만 배열로 들어옴
            'del_img'                => $this->request->getPost('del_img') ?? [],
        ]);

        session()->setFlashdata($result['SUCCESS'] === 'TRUE' ? 'msg' : 'err', $result['MSG']);
        return redirect()->to(SITE . '/Customer/view/' . (int) $customer_no);
    }

    // 고객 상세에서 등록번호/서류 확인 후 검증상태만 빠르게 바꿀 때 사용 - 전체 수정폼(edit()) 없이
    // CustomerService::verify() 단독 호출. 다른 필드(이름/연락처 등)는 안 건드림.
    public function postVerify($customer_no = null)
    {
        $service = new CustomerService();
        $result = $service->verify(
            $customer_no,
            (string) $this->request->getPost('biz_reg_verified_yn'),
            (string) $this->request->getPost('broker_reg_verified_yn')
        );

        session()->setFlashdata($result['SUCCESS'] === 'TRUE' ? 'msg' : 'err', $result['MSG']);
        return redirect()->to(SITE . '/Customer/view/' . (int) $customer_no);
    }

    // 드래그드랍 위젯이 드롭 즉시 호출 - customer/temp/에만 올려두고, 실제 저장은 안 건드린다.
    // customer_no를 몰라도 되는 구조라 등록(신규 가입) 화면에서도 그대로 재사용 가능.
    public function postUploadTemp()
    {
        $type = (string) $this->request->getPost('type');
        $file = $_FILES['file'] ?? null;

        if (!$file) {
            echo AT_Result::toJson(AT_Result::toFailData("파일이 없습니다."));
            exit;
        }

        $service = new CustomerService();
        echo AT_Result::toJson($service->uploadTemp($type, $file));
        exit;
    }
}
