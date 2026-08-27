// #35 마이페이지 "매물 관리자 바로가기" 버튼 - 본인 세션으로 session_token을 생성한 뒤
// bo.bomul.ai.kr/session_login으로 새 탭에서 연다. customer_session_login.js(어드민 고객목록)와
// 같은 패턴이지만 customer_no를 URL에 안 실음(본인 것만 되도록 서버가 세션에서 판단).
(function () {
    var cfg = window.BOMUL_SESSION_LOGIN_CONFIG || {};

    document.querySelectorAll('.js-mypage-session-login').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (!cfg.sessionUrl || !cfg.boLoginUrl) {
                return;
            }

            btn.disabled = true;

            fetch(cfg.sessionUrl)
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (data.SUCCESS === 'TRUE') {
                        var params = new URLSearchParams(data.RECORD);
                        window.open(cfg.boLoginUrl + '?' + params.toString(), '_blank');
                    } else {
                        alert(data.MSG || '자동로그인 생성에 실패했습니다.');
                    }
                })
                .catch(function () {
                    alert('자동로그인 요청 중 오류가 발생했습니다. 잠시 후 다시 시도해주세요.');
                })
                .finally(function () {
                    btn.disabled = false;
                });
        });
    });
})();
