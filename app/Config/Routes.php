<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
//$routes->setAutoRoute(true);
//$routes->get('/', 'Home::index');

$host = $_SERVER['HTTP_HOST'] ?? '';
if ($host === 'ad.bomul.ai.kr') { //관리자


    // 🌐 관리자 전용: 모든 요청을 Web namespace로 자동 라우팅
    $routes->setDefaultNamespace('App\Controllers\Admin');

    $routes->get('/',   'Home::getindex');
    $routes->get('Home',   'Home::getindex');

    $routes->get('Login',  'Login::getIndex');
    $routes->post('Login', 'Login::postIndex');
    $routes->get('Logout', 'Login::getLogout');

    $routes->get('Manager',                 'Manager::getIndex');
    $routes->get('Manager/write',           'Manager::getWrite');
    $routes->post('Manager/write',          'Manager::postWrite');
    $routes->get('Manager/modify/(:num)',   'Manager::getModify/$1');
    $routes->post('Manager/modify/(:num)',  'Manager::postModify/$1');
    $routes->post('Manager/delete/(:num)',  'Manager::postDelete/$1');

    $routes->get('Customer',                    'Customer::getIndex');
    $routes->get('Customer/write',              'Customer::getWrite');
    $routes->post('Customer/write',             'Customer::postWrite');
    $routes->get('Customer/view/(:num)',        'Customer::getView/$1');
    $routes->get('Customer/modify/(:num)',      'Customer::getModify/$1');
    $routes->post('Customer/modify/(:num)',     'Customer::postModify/$1');
    $routes->post('Customer/uploadTemp',        'Customer::postUploadTemp');

    $routes->get('Service',                     'Service::getIndex');
    $routes->get('Service/view/(:num)',         'Service::getView/$1');
    $routes->get('Service/modify/(:num)',       'Service::getModify/$1');
    $routes->post('Service/modify/(:num)',      'Service::postModify/$1');
    $routes->post('Service/approve/(:num)',     'Service::postApprove/$1');
    $routes->post('Service/requeue/(:num)',     'Service::postRequeue/$1');
    $routes->post('Service/suspend/(:num)',     'Service::postSuspend/$1');
    $routes->post('Service/resume/(:num)',      'Service::postResume/$1');
    $routes->post('Service/reject/(:num)',      'Service::postReject/$1');

    $routes->get('ServiceLive',                        'ServiceLive::getIndex');
    $routes->get('ServiceLive/view/(:num)',            'ServiceLive::getView/$1');
    $routes->post('ServiceLive/extend/(:num)',         'ServiceLive::postExtend/$1');
    $routes->post('ServiceLive/setExpire/(:num)',      'ServiceLive::postSetExpire/$1');
    $routes->post('ServiceLive/setStatus/(:num)',      'ServiceLive::postSetStatus/$1');
    $routes->post('ServiceLive/setInstallStatus/(:num)', 'ServiceLive::postSetInstallStatus/$1');

    $routes->get('Product',                 'Product::getIndex');
    $routes->get('Product/write',           'Product::getWrite');
    $routes->post('Product/write',          'Product::postWrite');
    $routes->get('Product/modify/(:num)',   'Product::getModify/$1');
    $routes->post('Product/modify/(:num)',  'Product::postModify/$1');
    $routes->post('Product/delete/(:num)',  'Product::postDelete/$1');

    $routes->get('ServerList',                 'ServerList::getIndex');
    $routes->get('ServerList/write',           'ServerList::getWrite');
    $routes->post('ServerList/write',          'ServerList::postWrite');
    $routes->get('ServerList/modify/(:num)',   'ServerList::getModify/$1');
    $routes->post('ServerList/modify/(:num)',  'ServerList::postModify/$1');
    $routes->post('ServerList/delete/(:num)',  'ServerList::postDelete/$1');

    $routes->post('Callback/complete',          'Callback::postComplete');
    // 기본 자동 라우터 활성화
    //$routes->setAutoRoute(true);

} else { //서비스 페이지

    // OPTIONS(preflight)는 어떤 경로든 여기서 바로 처리 - 컨트롤러마다 optionsXxx() 안 만들어도 됨
    $routes->options('(:any)', static function () {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Max-Age: 86400');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, Content-Type, X-Requested-With, Authorization');
        return service('response')->setStatusCode(200);
    });

    // 자동 라우팅 안 씀 - 명시적 라우트만 사용 (auto-route는 컨트롤러명이 그대로 URL로 노출되고,
    // 의도치 않은 public 메서드까지 라우팅되는 위험이 있어 ad와 동일하게 명시적으로 통일)
    $routes->setDefaultNamespace('App\Controllers');
    $routes->get('/',     'Home::getindex');
    $routes->get('Home',  'Home::getindex');

    $routes->get('signup',            'Signup::getindex');
    $routes->post('signup',           'Signup::postindex');
    $routes->post('signup/uploadTemp','Signup::postuploadtemp');

    $routes->get('login',   'Login::getindex');
    $routes->post('login',  'Login::postindex');
    $routes->get('logout',  'Login::getlogout');

    $routes->get('service-apply',  'ServiceApply::getindex');
    $routes->post('service-apply', 'ServiceApply::postindex');
}

