<?php

namespace App\Controllers;

use App\Libraries\AT_Result;
use App\Service\CustomerService;
use App\Service\MyPageService;

// www 로그인 고객 전용 "마이페이지"(축약형, 워크맵 #16) - 내 서비스 현황(만료일 등) + 내 결제 리스트만
// 보여준다. "서비스 연장 신청"/"정기결제 신청하기" 버튼은 실결제 연동(#11) 전까지는 준비중 안내만 한다.
class MyPage extends BaseController
{
    public function getIndex()
    {
        $customer = $this->requireLogin();
        if ($customer === null) {
            return redirect()->to(SITE . '/login?redirect_url=' . urlencode($this->currentPathWithQuery()));
        }

        $service = new MyPageService();

        $data = [];
        $data['customer'] = $customer;
        $data['service'] = $service->get_current_service($customer['customer_no']);
        $data['payments'] = $service->get_payment_list($customer['customer_no']);
        $data['notice'] = session()->getFlashdata('mypage_notice');
        $data['error'] = session()->getFlashdata('mypage_error');
        echo $this->view("default", "mypage", $data);
    }

    // 서비스 연장 신청 - 카드결제(#11)가 붙기 전까지는 준비중 안내만 하고 마이페이지로 되돌린다.
    public function getExtend()
    {
        session()->setFlashdata('mypage_notice', "서비스 연장 신청 기능은 준비중입니다. 곧 만나보실 수 있어요.");
        return redirect()->to(SITE . '/mypage');
    }

    // 정기결제(구독) 신청 - 위와 동일한 이유로 준비중 안내만.
    public function getSubscribe()
    {
        session()->setFlashdata('mypage_notice', "정기결제 신청 기능은 준비중입니다. 곧 만나보실 수 있어요.");
        return redirect()->to(SITE . '/mypage');
    }

    // 필수 서류(사업자등록증/중개사무소등록증) 제출 - Admin/Customer 수정화면과 동일한 dropzone 위젯을
    // 그대로 재사용(공용 upload.js). 저장은 CustomerService::saveDocuments()가 처리.
    public function postDocuments()
    {
        $customer = $this->requireLogin();
        if ($customer === null) {
            return redirect()->to(SITE . '/login?redirect_url=' . urlencode($this->currentPathWithQuery()));
        }

        $service = new CustomerService();
        $result = $service->saveDocuments($customer['customer_no'], [
            'biz_reg_no'          => $this->request->getPost('biz_reg_no'),
            'broker_reg_no'       => $this->request->getPost('broker_reg_no'),
            'biz_reg_file_name'   => $this->request->getPost('biz_reg_file_name'),
            'broker_reg_file_name' => $this->request->getPost('broker_reg_file_name'),
            'del_img'             => $this->request->getPost('del_img'),
        ]);

        session()->setFlashdata($result['SUCCESS'] === 'TRUE' ? 'mypage_notice' : 'mypage_error', $result['MSG']);
        return redirect()->to(SITE . '/mypage');
    }

    // 드래그드랍 위젯이 드롭 즉시 호출 - Signup::postuploadtemp()와 동일한 패턴, customer/temp/에만 저장.
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

    // ServiceApply::requireLogin()과 동일한 로그인 게이트 - 공통 부모로 안 끌어올린 기존 관례를 따름.
    private function requireLogin(): ?array
    {
        $login = session('customer_login');
        if (!($login['is_login'] ?? false)) {
            session()->setFlashdata('login_error', "마이페이지는 로그인 후 이용 가능합니다.");
            return null;
        }

        $customer = (new CustomerService())->get_row($login['CUSTOMER_NO']);
        return $customer ?: null;
    }

    private function currentPathWithQuery(): string
    {
        $uri = $this->request->getUri();
        $path = '/' . ltrim($uri->getPath(), '/');
        $query = $uri->getQuery();
        return $path . ($query !== '' ? '?' . $query : '');
    }
}
