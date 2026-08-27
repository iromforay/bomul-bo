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
        <main>
    <?php echo $content; ?>
        </main>
        <!-- ends content -->
        <!-- start footer -->
    <?=$footer?>
     <!-- end footer -->

    <script src="<?= RS_JS ?>/common/nav.js?var=<?=FRESH_TIME?>"></script>
    <script src="<?= RS_JS ?>/common/track.js?var=<?=FRESH_TIME?>"></script>
    </body>
</html>