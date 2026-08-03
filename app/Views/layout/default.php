<!doctype html>
<html lang="ko">
    <head>
        <!-- start header -->
    <?=$header?>
        <!-- //end header -->
    </head>
    <body>
         <!-- start header menu -->
    <?=$header_menu?>
        <!-- //end header menu -->

        <!-- start content -->
    <?php echo $content; ?>
        <!-- ends content -->
        <!-- start footer -->
    <?=$footer?>
     <!-- end footer -->
    </body>
</html>

<style>
.sns_list li.kakao a {background: url(<?php echo RS_IMG; ?>/common/sns_chanl_off.png) center no-repeat; background-size: cover;}
.sns_list li.naver a {background: url(<?php echo RS_IMG; ?>/common/sns_blog_off.png) center no-repeat; background-size: cover;}
.sns_list li.face a {background: url(<?php echo RS_IMG; ?>/common/sns_facebook_off.png) center no-repeat; background-size: cover;}
.sns_list li.insta a {background: url(<?php echo RS_IMG; ?>/common/sns_instargram_off.png) center no-repeat; background-size: cover;}
.sns_list li.youtube a {background: url(<?php echo RS_IMG; ?>/common/sns_youtube_off.png) center no-repeat; background-size: cover;}

#footer .f_inner .row.row2 .family_con .sns_list li.kakao a {background: url(<?php echo RS_IMG; ?>/common/sns_chanl_off_foot.png) center no-repeat; background-size: cover;}
#footer .f_inner .row.row2 .family_con .sns_list li.naver a {background: url(<?php echo RS_IMG; ?>/common/sns_blog_off_foot.png) center no-repeat; background-size: cover;}
#footer .f_inner .row.row2 .family_con .sns_list li.face a {background: url(<?php echo RS_IMG; ?>/common/sns_facebook_off_foot.png) center no-repeat; background-size: cover;}
#footer .f_inner .row.row2 .family_con .sns_list li.insta a {background: url(<?php echo RS_IMG; ?>/common/sns_instargram_off_foot.png) center no-repeat; background-size: cover;}
#footer .f_inner .row.row2 .family_con .sns_list li.youtube a {background: url(<?php echo RS_IMG; ?>/common/sns_youtube_off_foot.png) center no-repeat; background-size: cover;}
</style>