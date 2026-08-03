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
