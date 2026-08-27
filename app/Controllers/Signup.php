<?php

namespace App\Controllers;

use App\Libraries\AT_Result;
use App\Service\CustomerService;
use App\Service\VisitService;

// 공개 회원가입 페이지 - id/pw/company_name/phone 4개만 받는 간편가입. 사업자정보·도메인·상품선택은
// 전부 로그인 이후 /service-apply에서 처리한다 (Signup은 계정 생성만 책임진다).
// 가입 성공 즉시 자동로그인시켜 /service-apply로 넘긴다 - "결제 전까지는 비회원" 규칙을,
// 신청 시점엔 이미 세션이 있는 상태로 만족시키는 절충안 - 사용자에게는 이탈 없는 단일 흐름으로 보인다.
class Signup extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        return $this;
    }

    public function getindex()
    {
        $product = (string) $this->request->getGet('product');

        if (session('customer_login')['is_login'] ?? false) {
            return redirect()->to('/service-apply' . ($product !== '' ? '?product=' . urlencode($product) : ''));
        }

        $data = [];
        $data['product'] = $product;
        echo $this->view("default", "signup", $data);
    }

    public function postindex()
    {
        $service = new CustomerService();
        $result = $service->save([
            'customer_id'   => $this->request->getPost('customer_id'),
            'customer_pw'   => $this->request->getPost('customer_pw'),
            'company_name'  => $this->request->getPost('company_name'),
            'phone'         => $this->request->getPost('phone'),
        ]);

        $product = (string) $this->request->getPost('product');

        if ($result['SUCCESS'] !== 'TRUE') {
            $data = [];
            $data['error']   = $result['MSG'];
            $data['old']     = $this->request->getPost();
            $data['product'] = $product;
            echo $this->view("default", "signup", $data);
            return;
        }

        $customer_no = (int) $result['RECORD']['customer_no'];

        // #36 방문자 집계 - vtok 쿠키가 있으면(=SEO/검색 등으로 유입된 방문자) 가입회원과 매핑해서
        // "검색어별 가입 전환율"을 나중에 조인 계산할 수 있게 남겨둔다. 쿠키 없으면(직접 URL 등) 스킵.
        (new VisitService())->recordConversion($_COOKIE[VisitService::COOKIE_TOKEN] ?? null, $customer_no);

        // customer insert 성공 = 계정 생성 완료 - 이 시점부터 세션상 "로그인된 회원"으로 취급해서
        // 뒤이은 /service-apply의 "비회원 신청 금지" 규칙을 그대로 지키게 한다.
        session()->set('customer_login', [
            'is_login'      => true,
            'CUSTOMER_NO'  => $customer_no,
            'CUSTOMER_ID'   => (string) $this->request->getPost('customer_id'),
            'CUSTOMER_NAME' => '',
            'COMPANY_NAME'  => (string) $this->request->getPost('company_name'),
        ]);

        return redirect()->to('/service-apply' . ($product !== '' ? '?product=' . urlencode($product) : ''));
    }

    // 드래그드랍 위젯이 드롭 즉시 호출 - CustomerService::uploadTemp()를 그대로 재사용
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
