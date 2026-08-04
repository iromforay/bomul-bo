<?php

// ad.bomul.ai.kr(관리자 백오피스) 전용 SITE. common.php/production.php/development.php에서
// 호스트가 ad.bomul.ai.kr일 때만 require된다.
define('SITE', bomul_normalize_site_url(getenv('ad_site')));
