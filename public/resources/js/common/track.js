// #36 방문자 집계 - 페이지 로드마다 /track/visit(Track::postCollect)에 beacon 전송.
// document.referrer는 여기서만 원래 값(예: search.naver.com 검색결과)을 알 수 있다 -
// 서버가 받는 이 요청 자체의 Referer 헤더는 우리 사이트 자신이라 여기서 값을 실어 보내야 한다.
(function () {
    function send() {
        var data = new FormData();
        data.append('page_path', location.pathname);
        data.append('referrer', document.referrer || '');
        data.append('location_search', location.search || '');

        if (navigator.sendBeacon) {
            navigator.sendBeacon('/track/visit', data);
            return;
        }

        // sendBeacon 미지원 브라우저 대비 - keepalive로 페이지 이동 중에도 요청이 끊기지 않게
        fetch('/track/visit', {
            method: 'POST',
            body: data,
            keepalive: true
        }).catch(function () {});
    }

    if (document.readyState === 'complete') {
        send();
    } else {
        window.addEventListener('load', send);
    }
})();
