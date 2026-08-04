<link rel="stylesheet" href="<?= RS_CSS ?>/common/header_menu.css?var=<?=FRESH_TIME?>">

<header id="header" class="header <?= $active ?? '' ?>">
    <div class="header__inner">
        <a href="/" class="header__logo" title="보이는 매물 홈으로">
            <span class="header__logo-mark">보이는</span><span>매물</span>
        </a>

        <nav class="header__nav" aria-label="주요 메뉴">
            <ul class="header__menu">
                <li><a href="/#about">서비스 소개</a></li>
                <li><a href="/#features">주요 기능</a></li>
                <li><a href="/#pricing">요금 안내</a></li>
                <li><a href="/#faq">자주 묻는 질문</a></li>
                <li><a href="/#inquiry">상담 신청</a></li>
            </ul>
        </nav>

        <div class="header__actions">
            <a href="/#apply" class="btn btn--primary">가입 신청하기</a>
            <button type="button" class="header__toggle" id="jsNavToggle" aria-label="메뉴 열기" aria-expanded="false" aria-controls="jsMobNav">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
</header>

<div class="mob-nav" id="jsMobNav">
    <div class="mob-nav__head">
        <span class="mob-nav__title">메뉴</span>
        <button type="button" class="mob-nav__close" id="jsNavClose" aria-label="메뉴 닫기">&times;</button>
    </div>
    <ul class="mob-nav__list">
        <li><a href="/#about">서비스 소개</a></li>
        <li><a href="/#features">주요 기능</a></li>
        <li><a href="/#pricing">요금 안내</a></li>
        <li><a href="/#faq">자주 묻는 질문</a></li>
        <li><a href="/#inquiry">상담 신청</a></li>
    </ul>
    <a href="/#apply" class="btn btn--primary btn--block">가입 신청하기</a>
</div>
<div class="mob-nav__dim" id="jsNavDim"></div>
