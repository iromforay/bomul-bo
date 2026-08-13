<div class="signup-wrap">
    <div class="signup-card">
        <h1 class="signup-title">상담 신청</h1>
        <p class="signup-desc">서비스가 궁금하신가요? 편하게 문의를 남겨주시면 담당자가 남기신 연락처로 안내해드립니다.</p>
    </div>

    <div class="signup-card">
        <form class="inquiry-form" method="post" action="/inquiry">
            <input type="text" name="inquiry_name" placeholder="성함" required>
            <input type="text" name="inquiry_phone" placeholder="연락처" required>
            <textarea name="inquiry_message" placeholder="문의하실 내용을 남겨주세요"></textarea>
            <button type="submit" class="btn btn--primary">상담 신청하기</button>
            <p class="inquiry-form__msg"></p>
        </form>
    </div>
</div>

<script src="<?= RS_JS ?>/common/inquiry.js?var=<?= FRESH_TIME ?>"></script>
