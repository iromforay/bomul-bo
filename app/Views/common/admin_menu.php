<?php $active = $active ?? ''; ?>
<div class="util-bar">
    <div class="left">
        <span>공인중개사무소: <b style="color:#fff"><?= htmlspecialchars($common['company_name'] ?? SHOP_CODE ?? '', ENT_QUOTES) ?></b></span>
        <span>FID: <?= htmlspecialchars(SHOP_CODE ?? '', ENT_QUOTES) ?></span>
    </div>
    <div class="right">
        <span>관리자님 환영합니다</span>
        <span>내정보</span>
        <span>로그아웃</span>
    </div>
</div>

<div class="top-nav">
    <button type="button" class="side-toggle" id="jsSideToggle" aria-label="메뉴 열기">≡</button>
    <div class="brand">보이는 매물 · ADMIN</div>
    <ul>
        <li class="<?= $active === 'dashboard' ? 'active' : '' ?>"><a href="<?= Admin_SITE ?? '' ?>/Home">대시보드</a></li>
        <li class="<?= $active === 'property' ? 'active' : '' ?>"><a href="<?= Admin_SITE ?? '' ?>/Property">매물관리</a></li>
        <li class="<?= $active === 'inquiry' ? 'active' : '' ?>"><a href="<?= Admin_SITE ?? '' ?>/Inquiry">문의관리</a></li>
        <li class="<?= $active === 'member' ? 'active' : '' ?>"><a href="<?= Admin_SITE ?? '' ?>/Member">회원관리</a></li>
        <li class="<?= $active === 'setup' ? 'active' : '' ?>"><a href="<?= Admin_SITE ?? '' ?>/Setup">사이트설정</a></li>
        <li class="<?= $active === 'stat' ? 'active' : '' ?>"><a href="<?= Admin_SITE ?? '' ?>/Stat">통계</a></li>
    </ul>
</div>

<aside class="side" id="jsSide">
    <div class="grp-title">대시보드</div>
    <ul>
        <li class="on"><a href="<?= Admin_SITE ?? '' ?>/Home"><span class="ico">■</span>전체 현황</a></li>
        <li><a href="<?= Admin_SITE ?? '' ?>/Home/log"><span class="ico">■</span>최근 활동 로그</a></li>
    </ul>
    <div class="grp-title">매물관리</div>
    <ul>
        <li><a href="<?= Admin_SITE ?? '' ?>/Property"><span class="ico">▸</span>매물 목록</a></li>
        <li><a href="<?= Admin_SITE ?? '' ?>/Property/write"><span class="ico">▸</span>매물 등록</a></li>
        <li><a href="<?= Admin_SITE ?? '' ?>/Property/order"><span class="ico">▸</span>노출 순서 관리</a></li>
    </ul>
    <div class="grp-title">문의/신청</div>
    <ul>
        <li><a href="<?= Admin_SITE ?? '' ?>/Inquiry"><span class="ico">▸</span>상담 문의 내역</a></li>
        <li><a href="<?= Admin_SITE ?? '' ?>/Inquiry/visit"><span class="ico">▸</span>방문 예약</a></li>
    </ul>
    <div class="grp-title">사이트설정</div>
    <ul>
        <li><a href="<?= Admin_SITE ?? '' ?>/Setup"><span class="ico">▸</span>기본정보/로고</a></li>
        <li><a href="<?= Admin_SITE ?? '' ?>/Menu"><span class="ico">▸</span>메뉴 관리</a></li>
        <li><a href="<?= Admin_SITE ?? '' ?>/Banner"><span class="ico">▸</span>배너/팝업</a></li>
    </ul>
</aside>
