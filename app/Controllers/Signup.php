<?php

namespace App\Controllers;

use App\Libraries\AT_Result;
use App\Service\CustomerService;

// 공개 회원가입 페이지 - 결제 없이 바로 활성화되는 1/2개월 무료 티어용으로,
// 회원가입 + 서비스신청(fid 선택)을 3단계 위저드로 처리한다.
// 서류(사업자등록증/중개사무소등록증) 제출은 이 단계에서 뺐다 - 언제 받을지(가입 직후 별도 안내 vs
// 검증 단계에서 별도 페이지) 아직 미정. CustomerService::uploadTemp()/postuploadtemp()는 그대로
// 남겨뒀으니 나중에 어느 화면에 붙이든 재사용 가능.
// (관리자 검증 완료 전까지는 서비스가 활성화되지 않음 - Admin > 고객관리에서 처리)
class Signup extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        return $this;
    }

    public function getindex()
    {
        $data = [];
        echo $this->view("default", "signup", $data);
    }

    public function postindex()
    {
        $service = new CustomerService();
        $result = $service->save([
            'customer_id'          => $this->request->getPost('customer_id'),
            'customer_pw'          => $this->request->getPost('customer_pw'),
            'customer_name'        => $this->request->getPost('customer_name'),
            'company_name'         => $this->request->getPost('company_name'),
            'biz_reg_no'           => $this->request->getPost('biz_reg_no'),
            'broker_reg_no'        => $this->request->getPost('broker_reg_no'),
            'phone'                => $this->request->getPost('phone'),
            'email'                => $this->request->getPost('email'),
            'fid'                  => $this->request->getPost('fid'),
        ]);

        if ($result['SUCCESS'] !== 'TRUE') {
            $data = [];
            $data['error'] = $result['MSG'];
            $data['old']   = $this->request->getPost();
            echo $this->view("default", "signup", $data);
            return;
        }

        $data = [];
        $data['done'] = true;
        echo $this->view("default", "signup", $data);
    }

    // 드래그드랍 위젯이 드롭 즉시 호출 - CustomerService::uploadTemp()를 그대로 재사용
    // (admin쪽 Admin\Customer::postUploadTemp()와 동일 서비스 메서드, 공개용 엔드포인트만 별도)
    public function postuploadtemp()
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
