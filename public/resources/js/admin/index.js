$(document).ready(function () {

    $('.slider').slick({
        arrows: true,
        dots: false,
        infinite: true,
        slidesToShow: 1,
        slidesToScroll: 1
    });

    $('.slick-slider').slick({
        arrows: true,
        dots: false,
        infinite: true,
        appendArrows: '.slide_nav',
        slidesToShow: 4,
        slidesToScroll: 1,
        adaptiveHeight: true,

        responsive: [
            {
                breakpoint: 1400,
                settings: {
                    slidesToShow: 3,
                    slidesToScroll: 1,
                    centerMode: false
                }
            },
            {
                breakpoint: 1024,
                settings: {
                    slidesToShow: 2,
                    slidesToScroll: 1,
                    centerMode: false
                }
            },
            {
                breakpoint: 768,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    centerMode: true,
                    centerPadding: 'clamp(24px, 8vw, 80px)'
                }
            },
            {
                breakpoint: 520,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    centerMode: true,
                    centerPadding: '12px'
                }
            }
        ]
    });

    $('.footer_con .tit').on('click', function () {
        let $footer = $('.footer_con');
        $footer.toggleClass('folding');
        $footer.find('.tit span').text($footer.hasClass('folding') ? '▲' : '▼');
    });

    // 1. 햄버거 버튼 클릭 토글
    $('.mob_con').on('click', function (e) {
        e.stopPropagation(); // 버튼 클릭 시 바깥 클릭 이벤트가 바로 실행되는 것 방지
        $('body').toggleClass('open_nav');
    });

    // 2. 닫기 버튼 클릭
    $('.mob_nav .close_nav').on('click', function () {
        $('body').removeClass('open_nav');
    });

    // 3. 네비게이션 외부(배경) 클릭 시 닫기
    $(document).on('click', function (e) {
        // 클릭한 요소가 .mob_nav나 .mob_con 내부가 아니라면 메뉴 닫기
        if (!$(e.target).closest('.mob_nav, .mob_con').length) {
            $('body').removeClass('open_nav');
        }
    });

});