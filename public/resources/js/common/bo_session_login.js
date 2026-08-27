// bo.bomul.ai.kr session_login 공통 트리거 - "매물 관리자 바로가기"(#35, 마이페이지)와
// "관리자 체험"(#39, 홈 데모) 둘 다 이 스크립트 하나를 공유한다. 어느 세션(본인/데모)의
// session_token을 만들지는 cfg.sessionUrl이 가리키는 서버쪽 엔드포인트가 결정하고,
// 이 스크립트는 그 결과로 새 탭을 여는 것만 담당(customer_session_login.js와 같은 패턴).
(function () {
    var cfg = window.BOMUL_SESSION_LOGIN_CONFIG || {};

    document.querySelectorAll('.js-bo-session-login').forEach(function (btn) {
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
