<div id="header">
    <div class="sections section_00">
        <div class="width_con no_pad">
            <div class="row row1">
                <div class="logo">
                    <a href="<?php echo Admin_SITE; ?>"><img src="<?php echo RS_IMG; ?>/common/logo.png" /></a>
                </div>
                <div class="extra_con">
                    <ul class="login_menu">
                        <?php if(0){ ?>

                            <li>
                                <a href=" <?php echo Admin_SITE; ?>/Mypage">
                                    <span>내정보</span>
                                </a>
                                <span class="news"><i class="fa-solid fa-bell"></i></span>
                                <span class="my_news">6</span>
                                <div class="gnb_notice">
                                    <div id="notice"></div>
                                    <div class="all_notice"><span>내 알림 전체보기</span></div>
                                </div>
                            </li>
                            <li>
                                <a href="javascript:logout();">
                                    <span>로그아웃</span>
                                </a>
                            </li>
                        <?php }else{ ?>
                            <li>
                                <a href="javascript:login();">
                                    <span>로그인</span>
                                </a>
                            </li>
                            <li>
                                <a href="https://help.all-teaching.com/">
                                    <span>회원가입</span>
                                </a>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
                <div class="mob_con">
                    <div class="btn_open">
                        <div></div>
                    </div>
                </div>
            </div>
            <div class="row row2">
                <ul class="nav">
                    <li><a href="<?php echo Admin_SITE; ?>/Setup/Home">기본설정</a></li>
                    <li><a href="<?php echo Admin_SITE; ?>/Boards/list">게시판관리</a></li>
                    <li><a href="<?php echo Admin_SITE; ?>/Pager/list">페이지관리</a></li>
                    <li><a href="<?php echo Admin_SITE; ?>/Menu/list">메뉴관리</a></li>
                    <li><a href="<?php echo Admin_SITE; ?>/Member/list">회원 관리</a></li>
                    <li><a href="<?php echo Admin_SITE; ?>/Service/list">부가 서비스</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="h_modal"></div>
    <dov class="h_post"></dov>
</div>
