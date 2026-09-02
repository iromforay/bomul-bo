<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=0">

<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<?php
// SEO 기본 태그 - title/description/canonical/OG. $common['title']/['description']/['noindex']는
// BaseController::view()에서 항상 기본값이 채워져 있음(컨트롤러가 안 넘겨도 안전).
$__siteName = '보이는 매물';
$__pageTitle = $common['title'] !== ''
    ? $common['title'] . ' | ' . $__siteName
    : $__siteName . ' - 공인중개사무소 전용 매물 홈페이지 제작·관리 서비스';
$__canonicalUrl = rtrim(SITE, '/') . '/' . ltrim($_SERVER['REQUEST_URI'] ?? '', '/');
?>
<title><?= htmlspecialchars($__pageTitle, ENT_QUOTES) ?></title>
<meta name="description" content="<?= htmlspecialchars($common['description'], ENT_QUOTES) ?>">
<?php if ($common['noindex']): ?>
<meta name="robots" content="noindex, nofollow">
<?php endif; ?>
<link rel="canonical" href="<?= htmlspecialchars($__canonicalUrl, ENT_QUOTES) ?>">
<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= htmlspecialchars($__siteName, ENT_QUOTES) ?>">
<meta property="og:title" content="<?= htmlspecialchars($__pageTitle, ENT_QUOTES) ?>">
<meta property="og:description" content="<?= htmlspecialchars($common['description'], ENT_QUOTES) ?>">
<meta property="og:url" content="<?= htmlspecialchars($__canonicalUrl, ENT_QUOTES) ?>">
<link rel="icon" type="image/x-icon" href="<?=RS_IMG?>/header/favicon.ico">
<meta name="naver-site-verification" content="3d0b45348b2234781e2510c72c8226589ce15b43" />
<style>

</style>
<link rel="stylesheet" href="<?=RS_CSS?>/common/style.css?var=<?=FRESH_TIME?>" >

<!-- AOS -->
<link rel="stylesheet" href="https://unpkg.com/aos@2.3.4/dist/aos.css?var=<?=FRESH_TIME?>" >

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