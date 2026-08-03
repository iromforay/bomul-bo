<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=0">

<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">

<?php
// 페이지별 title/description은 각 Web 컨트롤러가 set_title()/set_description()으로 채운다
// (예: Property::getview()가 매물 요약을 description으로 넣음). 둘 다 안 채워졌으면
// site_name/company_name 기반 기본값으로 폴백한다 - 빈 문자열로 나가는 것보단 낫다.
$pageTitle = $common['title'] ?? ($common['site_name'] ?? '');
$pageDescription = $common['description'] ?? '';
if ($pageDescription === '') {
    $pageDescription = ($common['company_name'] ?? $common['site_name'] ?? '') . ' 홈페이지입니다.';
}

// http/https는 실제 요청 스킴을 그대로 따라간다 - 하드코딩하면 HTTPS로 서비스할 때 canonical/og:url이
// 실제 접속 주소와 달라져 검색엔진이 신뢰도를 낮게 볼 수 있다.
$scheme = (($_SERVER['HTTPS'] ?? 'off') !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? '';
$path = strtok($_SERVER['REQUEST_URI'] ?? '', '?'); // 쿼리스트링 제외 - canonical은 경로만
$canonicalUrl = $scheme . '://' . $host . $path;
$currentFullUrl = $scheme . '://' . $host . ($_SERVER['REQUEST_URI'] ?? '');

// 매물 상세페이지(row에 main_image_url이 있는 경우)는 실제 매물 사진을 og:image로 - 공유 시
// 미리보기가 그 매물 사진으로 뜨게. 그 외 페이지는 고정 이미지로 폴백.
$ogImage = !empty($row['main_image_url'])
    ? $row['main_image_url']
    : $scheme . '://' . $host . '/favicon/og.jpg';
?>
<link rel="canonical" href="<?=htmlspecialchars($canonicalUrl, ENT_QUOTES)?>">

<meta name="Author" content="">
<meta name="Keywords" content="">
<meta name="Description" content="<?=htmlspecialchars($pageDescription, ENT_QUOTES)?>">

<?php if (!empty($common['naver_site_verification'])): ?>
<meta name="naver-site-verification" content="<?=htmlspecialchars($common['naver_site_verification'], ENT_QUOTES)?>" />
<?php endif; ?>

<meta property="og:type" content="website"/>
<meta property="og:title" content="<?=htmlspecialchars($pageTitle, ENT_QUOTES)?>"/>
<meta property="og:description" content="<?=htmlspecialchars($pageDescription, ENT_QUOTES)?>"/>
<meta property="og:image" content="<?=htmlspecialchars($ogImage, ENT_QUOTES)?>"/>
<meta property="og:url" content="<?=htmlspecialchars($currentFullUrl, ENT_QUOTES)?>"/>

<title><?=$common['title']?></title>
<style>

</style>
<link rel="stylesheet" href="<?=RS_CSS?>/common/style.css?var=<?=FRESH_TIME?>" >

<script src="<?=RS_JS?>/common/index.js?var=<?=FRESH_TIME?>"></script>

<!-- Font Awesome :: Start -->
<!--<link rel="stylesheet" href="<?php /*=RS_TH*/?>/fontawesome_free_6_1_1/css/all.css" />-->
<!--<script type="text/javascript" src="<?php /*=RS_TH*/?>/fontawesome_free_6_1_1/js/all.js"></script>-->
<!-- Font Awesome :: End -->

<!-- Slick CSS -->
<link rel="stylesheet" href="<?= RS_CSS ?>/slick.css">
<link rel="stylesheet" href="<?= RS_CSS ?>/slick-theme.css">

<!-- jQuery -->
<script src="<?=RS_TH?>/jquery3.7/jquery-3.7.0.min.js"></script>


<!-- Slick JS -->
<script src="<?= RS_JS ?>/slick.min.js"></script>