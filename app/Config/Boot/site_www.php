<?php

// bomul.ai.kr / www.bomul.ai.kr(공개 서비스 사이트) 전용 SITE. ad.bomul.ai.kr이 아닌 모든
// 호스트(www, apex 도메인 등)에서 require된다.
define('SITE', bomul_normalize_site_url(getenv('www_site')));
