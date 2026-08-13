// 고객 목록 "로그인" 버튼 - ad 자신에게 session_token 생성을 요청한 뒤, bo.bomul.ai.kr/session_login으로
// session_token/id/time 3개 값을 GET으로 넘겨 새 탭에서 연다. 설정값(sessionUrl/boLoginUrl)은
// window.BOMUL_SESSION_LOGIN_CONFIG로 list.php가 미리 심어둔다(정적 리소스라 PHP 상수를 직접 못 씀).
(function () {
    var cfg = window.BOMUL_SESSION_LOGIN_CONFIG || {};

    document.querySelectorAll('.js-session-login').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            var customerNo = btn.getAttribute('data-customer-no');
            if (!customerNo || !cfg.sessionUrl || !cfg.boLoginUrl) {
                return;
            }

            btn.disabled = true;

            fetch(cfg.sessionUrl + customerNo)
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
