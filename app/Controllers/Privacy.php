<?php

namespace App\Controllers;

// www 공개 개인정보처리방침 페이지. 정적 콘텐츠(app/Views/privacy.php)만 렌더링.
class Privacy extends BaseController
{
    public function getIndex()
    {
        echo $this->view("default", "privacy");
    }
}
