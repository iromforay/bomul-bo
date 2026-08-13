// 상담 신청 폼(.inquiry-form) - 홈 "#inquiry" 섹션과 독립 /inquiry 페이지 둘 다에서 공용으로 씀.
// 페이지 이동 없이 제출하도록 fetch로 /inquiry(Inquiry::postindex, JSON 응답)를 호출한다.
(function () {
    document.querySelectorAll('.inquiry-form').forEach(function (form) {
        var msgBox = form.querySelector('.inquiry-form__msg');
        if (!msgBox) {
            msgBox = document.createElement('p');
            msgBox.className = 'inquiry-form__msg';
            form.appendChild(msgBox);
        }

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            var submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;
            msgBox.textContent = '';

            fetch(form.getAttribute('action') || '/inquiry', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: new FormData(form)
            })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (data.SUCCESS === 'TRUE') {
                        msgBox.style.color = '#2D7DD2';
                        msgBox.textContent = data.MSG;
                        form.reset();
                    } else {
                        msgBox.style.color = '#B3261E';
                        msgBox.textContent = data.MSG || '문의 등록에 실패했습니다.';
                    }
                })
                .catch(function () {
                    msgBox.style.color = '#B3261E';
                    msgBox.textContent = '문의 등록 중 오류가 발생했습니다. 잠시 후 다시 시도해주세요.';
                })
                .finally(function () {
                    if (submitBtn) submitBtn.disabled = false;
                });
        });
    });
})();
