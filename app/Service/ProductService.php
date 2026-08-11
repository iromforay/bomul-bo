<?php

namespace App\Service;

use App\Entity\Product_Entity;
use App\Libraries\AT_Result;

// CI_Service를 상속하지 않는다 - ad(이 백오피스)는 SHOP_CODE를 안 쓰는데 CI_Service 생성자는
// SHOP_CODE가 정의돼 있다고 가정한다 (ManagerService/LoginService와 동일한 이유).
class ProductService extends CI_Service
{
    private $model;
    private $serviceApplyModel;

    public function __construct()
    {
        parent::__construct();
        $this->model = model('App\Models\ProductModel', true, $this->db);
        $this->serviceApplyModel = model('App\Models\ServiceApplyModel', true, $this->db);
    }

    public function get_list()
    {
        return $this->model->get_list();
    }

    public function get_row($product_no)
    {
        return $this->model->findByIdx($product_no);
    }

    // www 요금 안내/신청 화면에 노출할 목록 - 중지(STOP)된 상품 제외, 정렬순
    public function get_usable_list()
    {
        return $this->model->get_usable_list();
    }

    // www 요금 그리드용 - 기본상품(BASE)만. 부가서비스(ADDON)는 아직 단독 그리드에 안 섞임.
    public function get_usable_base_list()
    {
        return $this->model->get_usable_base_list();
    }

    // ?product=... 쿼리파라미터 하나로 PK든 product_code든 다 받아주는 통합 조회.
    // 숫자(예: "3")면 product_no로, 문자열(예: "1month")이면 product_code로 찾는다 -
    // unit/product.php 카드의 링크가 product_code를 쓰고, 재신청/에러복구 흐름(ServiceApply)은
    // product_no(hidden input)를 그대로 되돌려보내기 때문에 둘 다 지원해야 한다.
    // 중지된 상품이거나 존재하지 않으면 null - 호출부가 "상품 미지정" 취급하도록.
    public function resolve($productParam)
    {
        $productParam = trim((string) $productParam);
        if ($productParam === '') {
            return null;
        }

        if (ctype_digit($productParam)) {
            $row = $this->model->findByIdx((int) $productParam);
            return ($row && $row['status'] === 'USE') ? $row : null;
        }

        return $this->model->findByCode($productParam);
    }

    public function save(array $data): array
    {
        [$entity, $error] = $this->buildEntity(null, $data);
        if ($error) {
            return $error;
        }

        if ($this->model->sql_save($entity)) {
            return AT_Result::toSuccessData("상품을 등록했습니다.");
        }

        return AT_Result::toFailData("등록에 실패했습니다.");
    }

    public function edit($product_no, array $data): array
    {
        [$entity, $error] = $this->buildEntity((int) $product_no, $data);
        if ($error) {
            return $error;
        }

        if ($this->model->sql_edit($entity)) {
            return AT_Result::toSuccessData("상품 정보를 수정했습니다.");
        }

        return AT_Result::toFailData("수정에 실패했습니다.");
    }

    public function delete($product_no): array
    {
        $product_no = (int) $product_no;

        if ($this->serviceApplyModel->existsByProductNo($product_no)) {
            return AT_Result::toFailData("이미 신청 이력이 있는 상품은 삭제할 수 없습니다. 상태를 '중지'로 바꿔주세요.");
        }

        if ($this->model->sql_delete($product_no)) {
            return AT_Result::toSuccessData("상품을 삭제했습니다.");
        }

        return AT_Result::toFailData("삭제에 실패했습니다.");
    }

    // 등록/수정 공통 검증 + Entity 조립. 실패 시 [null, AT_Result 실패배열], 성공 시 [Entity, null]
    private function buildEntity(?int $product_no, array $data): array
    {
        $product_code = trim($data['product_code'] ?? '');
        $product_type = $data['product_type'] ?? 'BASE';
        $product_name = trim($data['product_name'] ?? '');
        $price        = (int) ($data['price'] ?? 0);
        $listPriceRaw = trim((string) ($data['list_price'] ?? ''));
        $list_price   = $listPriceRaw !== '' ? (int) $listPriceRaw : null;
        $billing_type = $data['billing_type'] ?? '';
        $period_day   = (int) ($data['period_day'] ?? 0);
        $status       = $data['status'] ?? 'USE';
        $is_event     = ((string) ($data['is_event'] ?? '0')) === '1' ? 1 : 0;
        $sort         = (int) ($data['sort'] ?? 0);

        if ($product_code === '' || $product_name === '') {
            return [null, AT_Result::toFailData("상품코드와 상품명을 입력해주세요.")];
        }
        if (!in_array($product_type, ['BASE', 'ADDON'], true)) {
            return [null, AT_Result::toFailData("상품유형을 선택해주세요.")];
        }
        if (!in_array($billing_type, ['MONTHLY', 'ONCE', 'NONE'], true)) {
            return [null, AT_Result::toFailData("결제방식을 선택해주세요.")];
        }
        if (!in_array($status, ['USE', 'STOP'], true)) {
            return [null, AT_Result::toFailData("잘못된 상태값입니다.")];
        }
        if ($period_day <= 0) {
            return [null, AT_Result::toFailData("이용기간(일)을 1일 이상으로 입력해주세요.")];
        }
        if ($price < 0) {
            return [null, AT_Result::toFailData("가격은 0원 이상이어야 합니다.")];
        }
        if ($list_price !== null && $list_price < $price) {
            return [null, AT_Result::toFailData("정가는 판매가보다 낮을 수 없습니다.")];
        }

        if ($this->model->existsByCode($product_code, $product_no)) {
            return [null, AT_Result::toFailData("이미 사용중인 상품코드입니다.")];
        }

        $entity = new Product_Entity();
        if ($product_no !== null) {
            $entity->setProductNo($product_no);
        }
        $entity->setProductCode($product_code);
        $entity->setProductType($product_type);
        $entity->setProductName($product_name);
        $entity->setPrice($price);
        $entity->setListPrice($list_price);
        $entity->setBillingType($billing_type);
        $entity->setPeriodDay($period_day);
        $entity->setDescription($data['description'] ?? null);
        $entity->setStatus($status);
        $entity->setIsEvent($is_event);
        $entity->setSort($sort);

        return [$entity, null];
    }
}
