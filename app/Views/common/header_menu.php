<style>
    :root {
            --bgcolor : rgba(0,0,0,0);
            --sec_bgurl : #f5f5f5;

            --main_menucolor : <?= $common['main_menu_color'] ?? '#fff' ?>;
            --sub_menubgcolor : <?= $common['sub_menu_color'] ?? '#E72D2B' ?>;
            --menu_back_color : <?= $common['menu_back_color'] ?? '#fff' ?>;
            --menu_back_transparent : <?= $common['menu_back_transparent'] ?? '0' ?>;
            --transparent: <?= $common['main_transparent'] ?? '0' ?>;
            --sec_bgcolor: <?= $common['sec_bgcolor'] ?? '#f5f5f5' ?>;
    }
</style>

<link rel="stylesheet"  href="<?= RS_CSS ?>/common/header_menu.css?var=<?=FRESH_TIME?>">

<header id="header" class="<?= $active ?? '' ?> on">
    <div class="width_con">
        <?php if (($common['logo_show'] ?? 'Y') == "Y"): ?>
        <div class="logo_con">
            <a href="/" title="메인바로가기">
                <img src="<?= $common['logo_url'] ?? '' ?>" alt="logo">
            </a>
        </div>
        <?php else: ?>
        <a href="/"><h2><?= $common['site_name'] ?? '' ?></h2></a>
        <?php endif; ?>

        <nav class="nav_con">
            <ul>
            <?php if (($common['property_show'] ?? 'Y') == "Y"): ?>
                <li class="property"><a href="/property" title="매물보기">매물보기</a></li>
            <?php endif; ?>
            <?php if (($common['notice_show'] ?? 'Y') == "Y"): ?>
                <li class="notice"><a href="/notice" title="공지&이벤트">공지&이벤트</a></li>
            <?php endif; ?>
            <?php if (($common['around_show'] ?? 'Y') == "Y"): ?>
                <li class="around"><a href="/around" title="둘러보기">둘러보기</a></li>
            <?php endif; ?>
            <?php if (($common['charge_show'] ?? 'Y') == "Y"): ?>
                <li class="charge"><a href="/charge" title="이용요금">이용요금</a></li>
            <?php endif; ?>
            <?php if (($common['review_show'] ?? 'Y') == "Y"): ?>
                <li class="review"><a href="/review" title="이용후기">이용후기</a></li>
            <?php endif; ?>
                <!-- if($common['vod_show'] =="Y" ) :
                <li class="video"> <a href="/" title="홍보영상">홍보영상</a> </li>
                endif;-->
            <?php if (($common['map_show'] ?? 'Y') == "Y"): ?>
                <li class="map"><a href="/map" title="오시는길">오시는길</a></li>
            <?php endif; ?>

            </ul>

            <div class="fn_btn">
                <div class="sns_con">
                    <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="link"
                        class="svg-inline--fa fa-link sns_btn" role="img"
                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512">
                        <path fill="currentColor"
                            d="M579.8 267.7c56.5-56.5 56.5-148 0-204.5c-50-50-128.8-56.5-186.3-15.4l-1.6 1.1c-14.4 10.3-17.7 30.3-7.4 44.6s30.3 17.7 44.6 7.4l1.6-1.1c32.1-22.9 76-19.3 103.8 8.6c31.5 31.5 31.5 82.5 0 114L422.3 334.8c-31.5 31.5-82.5 31.5-114 0c-27.9-27.9-31.5-71.8-8.6-103.8l1.1-1.6c10.3-14.4 6.9-34.4-7.4-44.6s-34.4-6.9-44.6 7.4l-1.1 1.6C206.5 251.2 213 330 263 380c56.5 56.5 148 56.5 204.5 0L579.8 267.7zM60.2 244.3c-56.5 56.5-56.5 148 0 204.5c50 50 128.8 56.5 186.3 15.4l1.6-1.1c14.4-10.3 17.7-30.3 7.4-44.6s-30.3-17.7-44.6-7.4l-1.6 1.1c-32.1 22.9-76 19.3-103.8-8.6C74 372 74 321 105.5 289.5L217.7 177.2c31.5-31.5 82.5-31.5 114 0c27.9 27.9 31.5 71.8 8.6 103.9l-1.1 1.6c-10.3 14.4-6.9 34.4 7.4 44.6s34.4 6.9 44.6-7.4l1.1-1.6C433.5 260.8 427 182 377 132c-56.5-56.5-148-56.5-204.5 0L60.2 244.3z">
                        </path>
                    </svg>
                    <p>SNS</p>
                </div>

                <ul class="sns_list"></ul>
            </div>
        </nav>

        <div class="mob_con">
            <div class="open_nav">
                <div></div>
            </div>
            <span>메뉴</span>
        </div>

    </div>
</header>
<!-- //header -->

<!-- mobile menu -->
<div class="mob_nav">

    <div class="row1">
        <div class="close_nav"></div>
    </div>

    <div class="row3 rows">
        <h6>전체메뉴</h6>

        <ul>
        <?php if (($common['property_show'] ?? 'Y') == "Y"): ?>
            <li class="property"><a href="/property" title="매물보기">매물보기</a></li>
        <?php endif; ?>
        <?php if (($common['notice_show'] ?? 'Y') == "Y"): ?>
            <li class="notice"><a href="/notice" title="공지&이벤트">공지&이벤트</a></li>
        <?php endif; ?>
        <?php if (($common['around_show'] ?? 'Y') == "Y"): ?>
            <li class="around"><a href="/around" title="둘러보기">둘러보기</a></li>
        <?php endif; ?>
        <?php if (($common['charge_show'] ?? 'Y') == "Y"): ?>
            <li class="charge"><a href="/charge" title="이용요금">이용요금</a></li>
        <?php endif; ?>
        <?php if (($common['review_show'] ?? 'Y') == "Y"): ?>
            <li class="review"><a href="/review" title="이용후기">이용후기</a></li>
        <?php endif; ?>
        <?php if (($common['map_show'] ?? 'Y') == "Y"): ?>
            <!--<li class="video"> <a href="/" title="홍보영상">홍보영상</a> </li>-->
            <li class="map"><a href="/map" title="오시는길">오시는길</a></li>
        <?php endif; ?>
        </ul>
    </div>

    <div class="row4 rows">
        <h6>SNS</h6>
    </div>

</div>
<!-- //mobile menu -->
