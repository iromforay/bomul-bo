<?php
$active = $active ?? '';
//$shopCode = defined('SHOP_CODE') ? SHOP_CODE : '';
?>
<div class="util-bar">
    <div class="left">
        <span>ID: <b style="color:#fff"></b></span>
        <span></span>
    </div>
    <div class="right">
        <span>관리자님 환영합니다</span>
        <span>내정보</span>
        <a href="<?= Admin_SITE ?? '' ?>/Logout">로그아웃</a>
    </div>
</div>

<div class="top-nav">
    <button type="button" class="side-toggle" id="jsSideToggle" aria-label="메뉴 열기">≡</button>
    <div class="brand">보이는 매물 · ADMIN</div>
    <ul>
        <li class="<?= $active === 'dashboard' ? 'active' : '' ?>"><a href="<?= Admin_SITE ?? '' ?>/Home">대시보드</a></li>
        <li class="<?= $active === 'customer' ? 'active' : '' ?>"><a href="<?= Admin_SITE ?? '' ?>/Customer">고객관리</a></li>
        <li class="<?= $active === 'inquiry' ? 'active' : '' ?>"><a href="<?= Admin_SITE ?? '' ?>/Inquiry">상담문의</a></li>
        <li class="<?= $active === 'setup' ? 'active' : '' ?>"><a href="<?= Admin_SITE ?? '' ?>/Setup">사이트설정</a></li>
        <li class="<?= $active === 'manager' ? 'active' : '' ?>"><a href="<?= Admin_SITE ?? '' ?>/Manager">관리자</a></li>
        <li class="<?= $active === 'stat' ? 'active' : '' ?>"><a href="<?= Admin_SITE ?? '' ?>/Stat">통계</a></li>
    </ul>
</div>

<aside class="side" id="jsSide">
    <div class="grp-title">대시보드</div>
    <ul>
        <li class="on"><a href="<?= Admin_SITE ?? '' ?>/Home"><span class="ico">■</span>전체 현황</a></li>
        <li><a href="<?= Admin_SITE ?? '' ?>/Home/log"><span class="ico">■</span>최근 활동 로그</a></li>
    </ul>
    <div class="grp-title">고객관리</div>
    <ul>
        <li><a href="<?= Admin_SITE ?? '' ?>/Customer"><span class="ico">▸</span>고객 목록(중개사)</a></li>
        <li><a href="<?= Admin_SITE ?? '' ?>/Service"><span class="ico">▸</span>서비스 신청 현황</a></li>
        <li><a href="<?= Admin_SITE ?? '' ?>/Customer/payment"><span class="ico">▸</span>결제 현황</a></li>
    </ul>
    <div class="grp-title">상담문의</div>
    <ul>
        <li><a href="<?= Admin_SITE ?? '' ?>/Inquiry"><span class="ico">▸</span>상담 문의 내역</a></li>
    </ul>
    <div class="grp-title">사이트설정</div>
    <ul>
        <li><a href="<?= Admin_SITE ?? '' ?>/Setup"><span class="ico">▸</span>기본정보/로고</a></li>
        <li><a href="<?= Admin_SITE ?? '' ?>/Banner"><span class="ico">▸</span>배너/팝업</a></li>
        <li><a href="<?= Admin_SITE ?? '' ?>/Notice"><span class="ico">▸</span>공지사항</a></li>
        <li><a href="<?= Admin_SITE ?? '' ?>/Faq"><span class="ico">▸</span>FAQ</a></li>
    </ul>
    <div class="grp-title">관리자</div>
    <ul>
        <li><a href="<?= Admin_SITE ?? '' ?>/Manager"><span class="ico">▸</span>관리자 목록</a></li>
        <li><a href="<?= Admin_SITE ?? '' ?>/Manager/write"><span class="ico">▸</span>관리자 등록</a></li>
    </ul>
</aside>
