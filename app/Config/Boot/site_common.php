<?php

// SITE에서 파생되는 상수들 - site_ad.php/site_www.php로 SITE가 정해진 다음에 require돼야 한다.
define('SITE_PDS', SITE.'/pds/');
define('COMMON_PDS', 'http://api.bomul.ai.kr/pds/');
define('LOGIN_SERVER', 'https://');
define('RS', SITE.'/resources');
define('RS_IMG', SITE.'/resources/img');
define('RS_CSS', SITE.'/resources/css');
define('RS_JS', SITE.'/resources/js');
define('RS_TH', RS.'/third_party');
define('PDS_URL', SITE.'pds/');
define('FRESH_TIME', date("YmdHis"));
