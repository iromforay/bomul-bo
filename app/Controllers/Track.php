<?php

namespace App\Controllers;

use App\Libraries\AT_Result;
use App\Service\VisitService;

// #36 방문자 집계(검색어) 수집 API - 공통 JS(track.js, 다음 단계)가 페이지 로드마다 beacon으로 호출한다.
// referrer는 이 요청 자체의 HTTP Referer 헤더가 아니라 JS가 넘겨주는 document.referrer 값을 써야 한다
// (beacon 요청의 Referer는 우리 사이트 자신이라 원래 유입 검색어를 알 수 없음).
class Track extends BaseController
{
    public function postCollect()
    {
        $service = new VisitService();
        $userAgent = $this->request->getUserAgent()->getAgentString();

        if ($service->isBot($userAgent)) {
            echo AT_Result::toJson(['SUCCESS' => 'TRUE']); // 봇은 조용히 무시(에러 아님)
            return;
        }

        $visitorToken = $_COOKIE[VisitService::COOKIE_TOKEN] ?? '';
        if ($visitorToken === '') {
            $visitorToken = $service->generateToken();
        }
        $isLanding = !isset($_COOKIE[VisitService::COOKIE_SESSION]);

        $service->collectPageView($visitorToken, $isLanding, [
            'page_path'       => (string) $this->request->getPost('page_path'),
            'referrer'        => (string) $this->request->getPost('referrer'),
            'location_search' => (string) $this->request->getPost('location_search'),
            'user_agent'      => $userAgent,
        ]);

        $secure = (strtolower($_SERVER['HTTPS'] ?? '') !== '' && strtolower($_SERVER['HTTPS']) !== 'off');

        $this->response->setCookie([
            'name'     => VisitService::COOKIE_TOKEN,
            'value'    => $visitorToken,
            'expire'   => 60 * 60 * 24 * 365, // 1년 - 같은 방문자를 장기간 식별(가입 전환 매칭용)
            'httponly' => true,
            'secure'   => $secure,
            'samesite' => 'Lax',
        ]);
        $this->response->setCookie([
            'name'     => VisitService::COOKIE_SESSION,
            'value'    => '1',
            'expire'   => 0, // 세션쿠키(브라우저 종료 시 소멸) - "세션 내 첫 페이지뷰" 판별용
            'httponly' => true,
            'secure'   => $secure,
            'samesite' => 'Lax',
        ]);

        echo AT_Result::toJson(['SUCCESS' => 'TRUE']);
    }
}
