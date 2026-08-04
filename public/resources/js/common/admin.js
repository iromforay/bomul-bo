(function () {
    var toggle = document.getElementById('jsSideToggle');
    var side = document.getElementById('jsSide');
    if (!toggle || !side) return;

    toggle.addEventListener('click', function () {
        side.classList.toggle('is-open');
    });

    side.querySelectorAll('a').forEach(function (a) {
        a.addEventListener('click', function () {
            side.classList.remove('is-open');
        });
    });
})();

// 성공/실패 알림 배너 - 닫기 버튼 클릭 또는 4초 후 자동으로 사라짐
(function () {
    document.querySelectorAll('.alert').forEach(function (alertEl) {
        var remove = function () {
            alertEl.classList.add('is-hiding');
            setTimeout(function () { alertEl.remove(); }, 300);
        };

        var closeBtn = alertEl.querySelector('.alert__close');
        if (closeBtn) closeBtn.addEventListener('click', remove);

        setTimeout(remove, 4000);
    });
})();
