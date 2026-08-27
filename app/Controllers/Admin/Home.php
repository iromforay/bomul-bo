<?php

namespace App\Controllers\Admin;

use App\Libraries\AT_Cookie;

// 관리자 대시보드 - "사용자의 사용여부를 볼 수 있도록" 요청으로, 그동안 자리만 잡고 있던 정적
// 목업(매물/문의 목업 데이터 - 이 서비스의 실제 도메인과 무관)을 실데이터 위젯으로 교체.
// 주간 방문자 그래프/표(#36 visit_stat_daily), 가입사용자 추이(customer.reg_date)를 보여준다.
class Home extends BaseController
{
    private  $vdir;
    public function __construct()
    {
        parent::__construct();
        $this->vdir="Admin/";
        return $this;
    }

    public function getindex()
    {
        $db = db_connect('default');
        $visitStatModel = model('App\Models\VisitStatDailyModel', true, $db);
        $customerModel = model('App\Models\CustomerModel', true, $db);

        // 최근 7일(오늘 제외 - 오늘치는 아직 하루가 안 끝나서 그래프가 들쭉날쭉해 보임) + 그 이전 7일은
        // 가입 추이 비교용으로 좀 더 길게(14일) 본다.
        $today = date('Y-m-d');
        $weekAgo = date('Y-m-d', strtotime('-7 day'));
        $twoWeeksAgo = date('Y-m-d', strtotime('-14 day'));
        $yesterday = date('Y-m-d', strtotime('-1 day'));

        $data = [];
        $data['visitDaily'] = $visitStatModel->get_daily_totals($weekAgo, $yesterday);
        $data['topKeywords'] = $visitStatModel->get_top_keywords($weekAgo, $yesterday, 10);
        $data['signupDaily'] = $customerModel->get_daily_signup_counts($twoWeeksAgo, $yesterday);
        $data['periodFrom'] = $weekAgo;
        $data['periodTo'] = $yesterday;

        echo $this->view("index", $data);
    }
}
