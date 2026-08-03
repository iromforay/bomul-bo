<footer class="footer_con folding">

    <div class="width_con">

        <div class="tit">
            <h5>
                <?=$common['company_name']??"회사"?>
                <span>▲</span>
            </h5>
            <p>Copyrightⓒ 2023 <a href="https://www.bomul.ai.kr" target="_blank" rel="noopener noreferrer">보이는 매물</a> All rights reserved.</p>
        </div>

        <p class="footer_desc">
            <?if(strlen($common['footer_copy'])>0){
                echo $common['footer_copy'];
                }else{?>
                    Copyrightⓒ 2023 <a href="https://www.bomul.ai.kr" target="_blank" rel="noopener noreferrer">보이는 매물</a> All rights reserved.
            <?}?>
            <br>
            Copyrightⓒ 2023 <a href="https://www.bomul.ai.kr" target="_blank" rel="noopener noreferrer">보이는 매물</a> All rights reserved.
        </p>
        <?if($common['link_menu_show']=="Y"){?>
        <div class="bottom_btn_con">
            <div class="inner">
                <div class="naver_con">
                    <a href="" target="_blank" data-track-click="reserve">
                        <img src="<?= RS_IMG ?>/header/naver.png" alt="네이버 예약">
                        <p>예약하기</p>
                    </a>
                </div>

                <div class="kakao_con">
                    <a href="" target="_blank" data-track-click="kakao">
                        <img src="<?= RS_IMG ?>/header/kakao.png" alt="카카오톡 채널">
                        <p>카톡채널추가</p>
                    </a>
                </div>
            </div>
        </div>
        <?}?>
    </div>

</footer>

<script>
    // 페이지별 값만 여기서 심고, 실제 수집 로직은 정적 파일(common/track.js)에서 처리한다.
    var BOMUL_TRACK_CONFIG = {
        pageType: <?= json_encode($common['code'] ?? '', JSON_UNESCAPED_UNICODE) ?>,
        propertyNo: <?= json_encode(isset($row['property_no']) ? (int)$row['property_no'] : null) ?>,
        // 반드시 "이 페이지" 요청의 Referer - 트래킹 요청 자체의 Referer(document.referrer)는 신뢰 불가
        landingReferrer: <?= json_encode($_SERVER['HTTP_REFERER'] ?? '', JSON_UNESCAPED_UNICODE) ?>
    };
</script>
<script src="<?= RS_JS ?>/common/track.js?var=<?=FRESH_TIME?>"></script>