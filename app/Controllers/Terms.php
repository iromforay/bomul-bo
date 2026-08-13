<?php

namespace App\Controllers;

// www 공개 이용약관 페이지. 정적 콘텐츠(app/Views/terms.php)만 렌더링.
class Terms extends BaseController
{
    public function getIndex()
    {
        echo $this->view("default", "terms");
    }
}
