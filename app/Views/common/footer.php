<footer class="footer">
    <div class="width_con footer__inner">

        <div class="footer__top">
            <div class="footer__brand">
                <p class="footer__logo">보이는 매물</p>
                <p class="footer__desc">공인중개사를 위한 매물 홈페이지 제작 &amp; 관리 서비스</p>
            </div>

            <nav class="footer__links" aria-label="바로가기">
                <a href="/#about">서비스 소개</a>
                <a href="/#features">주요 기능</a>
                <a href="/#pricing">요금 안내</a>
                <a href="/signup">신청하기</a>
            </nav>
        </div>

        <hr class="footer__divider">

        <div class="footer__bottom">
            <ul class="footer__biz">
                <li><?= htmlspecialchars($common['company_name'] ?? '보이는 매물', ENT_QUOTES) ?></li>
                <li>대표 나병연</li>
                <li>사업자등록번호 [257-38-01646]</li>
                <li>주소 [주소를 입력하세요]</li>
                <li>고객센터 [번호를 입력하세요]</li>
            </ul>
            <p class="footer__copy">Copyright &copy; <?= date('Y') ?> 보이는 매물. All rights reserved.</p>
        </div>

    </div>
</footer>

<!-- AOS -->
<script src="https://unpkg.com/aos@2.3.4/dist/aos.js?var=<?=FRESH_TIME?>"></script>

<script>
    AOS.init({
        duration: 800,
        easing: 'ease-out-cubic',
        once: true,
        offset: 80,
        delay: 0,
        anchorPlacement: 'top-bottom'
    });
</script>
