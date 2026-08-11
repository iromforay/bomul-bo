<?php

namespace App\Controllers\Admin;

use App\Service\ProductService;

// 사이드메뉴 "상품(서비스) 관리" - 고객이 서비스 신청 시 선택하는 상품(product 테이블) CRUD.
// service_apply/service_live와 달리 신청/운영 상태가 아니라 "판매 중인 상품 자체"를 다룬다.
class Product extends BaseController
{
    public function getIndex()
    {
        $service = new ProductService();
        $data['list'] = $service->get_list();

        echo $this->view("Product/list", $data);
    }

    public function getWrite()
    {
        $data['row'] = null;
        echo $this->view("Product/write", $data);
    }

    public function postWrite()
    {
        $service = new ProductService();
        $result = $service->save([
            'product_code' => $this->request->getPost('product_code'),
            'product_name' => $this->request->getPost('product_name'),
            'price'        => $this->request->getPost('price'),
            'billing_type' => $this->request->getPost('billing_type'),
            'period_day'   => $this->request->getPost('period_day'),
            'description'  => $this->request->getPost('description'),
            'status'       => $this->request->getPost('status'),
            'is_event'     => $this->request->getPost('is_event'),
            'sort'         => $this->request->getPost('sort'),
        ]);

        session()->setFlashdata($result['SUCCESS'] === 'TRUE' ? 'msg' : 'err', $result['MSG']);
        return redirect()->to(SITE . '/Product');
    }

    public function getModify($product_no = null)
    {
        $service = new ProductService();
        $row = $service->get_row($product_no);

        if (!$row) {
            session()->setFlashdata('err', '존재하지 않는 상품입니다.');
            return redirect()->to(SITE . '/Product');
        }

        $data['row'] = $row;
        echo $this->view("Product/modify", $data);
    }

    public function postModify($product_no = null)
    {
        $service = new ProductService();
        $result = $service->edit($product_no, [
            'product_code' => $this->request->getPost('product_code'),
            'product_name' => $this->request->getPost('product_name'),
            'price'        => $this->request->getPost('price'),
            'billing_type' => $this->request->getPost('billing_type'),
            'period_day'   => $this->request->getPost('period_day'),
            'description'  => $this->request->getPost('description'),
            'status'       => $this->request->getPost('status'),
            'is_event'     => $this->request->getPost('is_event'),
            'sort'         => $this->request->getPost('sort'),
        ]);

        session()->setFlashdata($result['SUCCESS'] === 'TRUE' ? 'msg' : 'err', $result['MSG']);
        return redirect()->to(SITE . '/Product');
    }

    public function postDelete($product_no = null)
    {
        $service = new ProductService();
        $result = $service->delete($product_no);

        session()->setFlashdata($result['SUCCESS'] === 'TRUE' ? 'msg' : 'err', $result['MSG']);
        return redirect()->to(SITE . '/Product');
    }
}
