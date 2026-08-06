<?php

namespace App\Service;

class ProductService extends CI_Service
{
    private $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = model('App\Models\ProductModel', true, $this->db);
    }

    public function get_usable_list()
    {
        return $this->model->get_usable_list();
    }

    public function get_row($product_no)
    {
        return $this->model->findByIdx($product_no);
    }

    // ?product= 쿼리로 넘어온 값이 숫자(idx)인지 코드(product_code)인지 몰라도 알아서 조회
    public function resolve($productIdxOrCode)
    {
        $productIdxOrCode = trim((string) $productIdxOrCode);
        if ($productIdxOrCode === '') {
            return null;
        }

        if (ctype_digit($productIdxOrCode)) {
            return $this->model->findByIdx((int) $productIdxOrCode);
        }

        return $this->model->findByCode($productIdxOrCode);
    }
}
