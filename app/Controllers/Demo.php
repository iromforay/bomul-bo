<?php

namespace App\Controllers;

use App\Libraries\AT_Result;
use App\Service\CustomerService;

// #39 홈 "관리자 체험" 버튼 - 로그인 없이도 데모 테넌트(fid=demo1)의 bo.bomul.ai.kr 세션을 발급.
// MyPage::getSessionLogin()과 같은 CustomerService::createSessionLogin() 재사용이지만, 거기는
// "세션에 로그인된 본인" customer_no만 쓰는 반면 여기는 비로그인 방문자용이라 customer_no를
// 요청값이 아니라 서버가 DEMO_FID로 직접 찾은 값으로 고정한다(임의 고객 토큰 생성 방지).
class Demo extends BaseController
{
    private const DEMO_FID = 'demo1';

    public function getSessionLogin()
    {
        $db = db_connect('default');
        $liveModel = model('App\Models\ServiceLiveModel', true, $db);

        $live = $liveModel->findByFid(self::DEMO_FID);
        if (!$live) {
            echo AT_Result::toJson(AT_Result::toFailData("데모 사이트를 찾을 수 없습니다."));
            return;
        }

        $service = new CustomerService();
        echo AT_Result::toJson($service->createSessionLogin($live['customer_no']));
    }
}
