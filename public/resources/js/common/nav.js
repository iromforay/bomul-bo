(function () {
    var toggleBtn = document.getElementById('jsNavToggle');
    var closeBtn = document.getElementById('jsNavClose');
    var mobNav = document.getElementById('jsMobNav');
    var dim = document.getElementById('jsNavDim');

    if (!toggleBtn || !mobNav || !dim) return;

    function openNav() {
        mobNav.classList.add('is-open');
        dim.classList.add('is-open');
        document.body.classList.add('nav-open');
        toggleBtn.setAttribute('aria-expanded', 'true');
    }

    function closeNav() {
        mobNav.classList.remove('is-open');
        dim.classList.remove('is-open');
        document.body.classList.remove('nav-open');
        toggleBtn.setAttribute('aria-expanded', 'false');
    }

    toggleBtn.addEventListener('click', openNav);
    if (closeBtn) closeBtn.addEventListener('click', closeNav);
    dim.addEventListener('click', closeNav);
    mobNav.querySelectorAll('a').forEach(function (a) {
        a.addEventListener('click', closeNav);
    });

    var header = document.getElementById('header');
    if (header) {
        var onScroll = function () {
            header.classList.toggle('on', window.scrollY > 4);
        };
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
    }
})();
