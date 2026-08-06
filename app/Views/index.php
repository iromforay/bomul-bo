<section class="hero" id="about">
    <div class="width_con hero__inner">
        <p class="hero__eyebrow">공인중개사를 위한 매물 홈페이지</p>
        <h1 class="hero__title">우리 사무소만의<br>매물 홈페이지를 만들어보세요</h1>
        <p class="hero__desc">보이는 매물은 공인중개사무소 전용 홈페이지 제작·관리 서비스입니다.<br>매물 등록부터 고객 상담까지, 필요한 기능을 한 곳에서 관리하세요.</p>
        <div class="hero__actions">
            <a href="/signup" class="btn btn--primary">가입 신청하기</a>
            <a href="#features" class="btn btn--outline">주요 기능 보기</a>
        </div>
    </div>
</section>

<!-- 1. 서비스 설명(주요기능) -->
<section class="section" id="features">
    <div class="width_con">
        <h2 class="section__title">주요 기능</h2>
        <p class="section__desc">매물 등록부터 고객 상담까지, 중개사무소 운영에 필요한 기능을 한 곳에서</p>

        <div class="feature-grid">
            <div class="feature-card">
                <div class="feature-card__icon">🏠</div>
                <h3 class="feature-card__title">매물 홈페이지 제작</h3>
                <p class="feature-card__desc">우리 사무소만의 도메인으로 매물을 소개하는 홈페이지를 손쉽게 만들 수 있습니다.</p>
            </div>
            <div class="feature-card">
                <div class="feature-card__icon">📋</div>
                <h3 class="feature-card__title">매물 관리</h3>
                <p class="feature-card__desc">등록·수정·노출 순서까지 매물 정보를 직관적인 화면에서 관리합니다.</p>
            </div>
            <div class="feature-card">
                <div class="feature-card__icon">💬</div>
                <h3 class="feature-card__title">고객 상담 관리</h3>
                <p class="feature-card__desc">방문객의 문의와 상담 이력을 한눈에 확인하고 놓치지 않고 응대합니다.</p>
            </div>
        </div>
    </div>
</section>

<!-- 2. 요금안내 -->
<section class="section section--alt" id="pricing">
    <div class="width_con">
        <h2 class="section__title">요금 안내</h2>

        <?php $applyBase = (session('customer_login')['is_login'] ?? false) ? '/service-apply' : '/signup'; ?>

        <?= view('unit/product', ['applyBase' => $applyBase]) ?>
    </div>
</section>

<!-- 3. 자주묻는질문 -->
<section class="section" id="faq">
    <div class="width_con">
        <h2 class="section__title">자주 묻는 질문</h2>
        <p class="section__desc">궁금하신 점을 먼저 확인해보세요</p>

        <div class="faq-list">
            <details class="faq-item">
                <summary>가입 후 바로 서비스를 이용할 수 있나요?</summary>
                <p>회원가입 후 사업자등록증·중개사무소등록증 검증이 완료되어야 서비스 신청이 가능합니다. 검증에는 영업일 기준 1~2일 정도 소요됩니다.</p>
            </details>
            <details class="faq-item">
                <summary>결제 방식은 어떻게 되나요?</summary>
                <p>1개월/3개월 단위 결제와 매월 자동 결제되는 정기구독 중 선택할 수 있습니다.</p>
            </details>
            <details class="faq-item">
                <summary>중도 해지가 가능한가요?</summary>
                <p>정기구독은 언제든 해지할 수 있으며, 해지 후에도 결제한 기간까지는 정상적으로 이용하실 수 있습니다.</p>
            </details>
            <details class="faq-item">
                <summary>도메인(2차도메인)은 어떻게 정하나요?</summary>
                <p>서비스 신청 단계에서 원하시는 주소를 직접 입력해 선택하실 수 있습니다.</p>
            </details>
        </div>
    </div>
</section>

<!-- 4. 상담신청 -->
<section class="section section--alt" id="inquiry">
    <div class="width_con">
        <h2 class="section__title">상담 신청</h2>
        <p class="section__desc">서비스가 궁금하신가요? 편하게 문의를 남겨주시면 담당자가 안내해드립니다.</p>

        <form class="inquiry-form" method="post" action="#">
            <input type="text" name="inquiry_name" placeholder="성함" required>
            <input type="text" name="inquiry_phone" placeholder="연락처" required>
            <textarea name="inquiry_message" placeholder="문의하실 내용을 남겨주세요"></textarea>
            <button type="submit" class="btn btn--primary">상담 신청하기</button>
        </form>
    </div>
</section>

<!-- 5. 회원가입 및 서비스 신청(결제) -->
<section class="section" id="apply">
    <div class="width_con">
        <h2 class="section__title">회원가입 및 서비스 신청</h2>
        <p class="section__desc">4단계면 우리 사무소만의 매물 홈페이지를 시작할 수 있습니다</p>

        <div class="apply-steps">
            <div class="apply-step">
                <div class="apply-step__num">1</div>
                <p class="apply-step__title">회원가입</p>
                <p class="apply-step__desc">기본 정보로 계정을 생성합니다</p>
            </div>
            <div class="apply-step">
                <div class="apply-step__num">2</div>
                <p class="apply-step__title">서류 검증</p>
                <p class="apply-step__desc">사업자등록증·중개사무소등록증 확인</p>
            </div>
            <div class="apply-step">
                <div class="apply-step__num">3</div>
                <p class="apply-step__title">서비스 신청</p>
                <p class="apply-step__desc">사용할 주소(도메인)를 선택합니다</p>
            </div>
            <div class="apply-step">
                <div class="apply-step__num">4</div>
                <p class="apply-step__title">결제</p>
                <p class="apply-step__desc">요금제 선택 후 바로 이용 시작</p>
            </div>
        </div>

        <div class="apply-cta">
            <a href="/signup" class="btn btn--primary">회원가입하고 시작하기</a>
        </div>
    </div>
</section>
