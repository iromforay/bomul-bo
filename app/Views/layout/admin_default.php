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
<main class="admin-main">
    <?php echo $content; ?>
</main>
<!-- ends content -->
<!-- start footer -->
<?=$footer?>
<!-- end footer -->

<script src="<?= RS_JS ?>/common/admin.js?var=<?=FRESH_TIME?>"></script>
</body>
</html>