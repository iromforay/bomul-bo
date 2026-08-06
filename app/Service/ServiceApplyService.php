<?php

namespace App\Service;

use App\Entity\ServiceApply_Entity;
use App\Libraries\AT_Result;

class ServiceApplyService extends CI_Service
{
    private $model;
    private $productModel;

    public function __construct()
    {
        parent::__construct();
        $this->model = model('App\Models\ServiceApplyModel', true, $this->db);
        $this->productModel = model('App\Models\ProductModel', true, $this->db);
    }

    // 어드민 "서비스 신청 현황" 목록
    public function get_list()
    {
        return $this->model->get_list_with_details();
    }

    // $data['customer_idx']는 항상 서버(세션)에서 채워서 넘겨야 한다 - 폼 입력값으로 받으면 안 됨(타 계정 신청 위조 방지)
    public function apply(array $data): array
    {
        $customer_idx  = (int) ($data['customer_idx'] ?? 0);
        $product_idx   = (int) ($data['product_idx'] ?? 0);
        $company_name  = trim($data['company_name'] ?? '');
        $ceo_name      = trim($data['ceo_name'] ?? '');
        $manager_phone = trim($data['manager_phone'] ?? '');
        $manager_email = trim($data['manager_email'] ?? '');

        if ($customer_idx <= 0) {
            return AT_Result::toFailData("로그인 후 이용해주세요.");
        }
        if ($product_idx <= 0 || $company_name === '' || $ceo_name === '' || $manager_phone === '' || $manager_email === '') {
            return AT_Result::toFailData("필수 항목을 입력해주세요.");
        }

        $product = $this->productModel->findByIdx($product_idx);
        if (!$product || $product['status'] !== 'USE') {
            return AT_Result::toFailData("신청할 수 없는 상품입니다.");
        }

        // 이벤트 상품은 고객당 1회 - 상태 무관 기존 신청이력이 있으면 재신청 차단
        // (재신청 허용 정책이 REJECTED/CANCEL 예외를 두는 쪽으로 바뀌면 existsByCustomerProduct에 status 조건 추가)
        if (((string) $product['is_event']) === '1' && $this->model->existsByCustomerProduct($customer_idx, $product_idx)) {
            return AT_Result::toFailData("이미 신청한 이벤트 상품입니다.");
        }

        $entity = new ServiceApply_Entity();
        $entity->setCustomerIdx($customer_idx);
        $entity->setProductIdx($product_idx);
        $entity->setCompanyName($company_name);
        $entity->setCeoName($ceo_name);
        $entity->setLicenseNo($data['license_no'] ?? null);
        $entity->setBizNo($data['biz_no'] ?? null);
        $entity->setManagerName($data['manager_name'] ?? null);
        $entity->setManagerPhone($manager_phone);
        $entity->setManagerEmail($manager_email);
        $entity->setTel($data['tel'] ?? null);
        $entity->setFax($data['fax'] ?? null);
        $entity->setZipcode($data['zipcode'] ?? null);
        $entity->setAddr1($data['addr1'] ?? null);
        $entity->setAddr2($data['addr2'] ?? null);
        $entity->setHomepageName($data['homepage_name'] ?? null);
        $entity->setSubDomain($data['sub_domain'] ?? null);
        $entity->setCustomDomain($data['custom_domain'] ?? null);
        $entity->setMemo($data['memo'] ?? null);
        $entity->setStatus('REQUEST');

        $apply_idx = $this->model->sql_save($entity);
        if (!$apply_idx) {
            return AT_Result::toFailData("서비스 신청에 실패했습니다.");
        }

        return AT_Result::toSuccessData("서비스 신청이 완료되었습니다.", ['apply_idx' => $apply_idx]);
    }
}
