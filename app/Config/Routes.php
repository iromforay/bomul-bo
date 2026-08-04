<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
//$routes->setAutoRoute(true);
//$routes->get('/', 'Home::index');

$host = $_SERVER['HTTP_HOST'] ?? '';
if ($host === 'ad.bomul.ai.kr') {


    // 🌐 관리자 전용: 모든 요청을 Web namespace로 자동 라우팅
    $routes->setDefaultNamespace('App\Controllers\Admin');

    $routes->get('/',   'Home::getindex');
    $routes->get('Home',   'Home::getindex');

    $routes->get('Login',  'Login::getIndex');
    $routes->post('Login', 'Login::postIndex');
    $routes->get('Logout', 'Login::getLogout');
    // 기본 자동 라우터 활성화
    //$routes->setAutoRoute(true);

} else {

    // OPTIONS(preflight)는 어떤 경로든 여기서 바로 처리 - 컨트롤러마다 optionsXxx() 안 만들어도 됨
    $routes->options('(:any)', static function () {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Max-Age: 86400');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, Content-Type, X-Requested-With, Authorization');
        return service('response')->setStatusCode(200);
    });

    // 🔥 API는 기존 그대로 (Api/Admin 자동 라우팅 유지)
    $routes->setDefaultNamespace('App\Controllers');
    $routes->setAutoRoute(true);
}

