/*
 * 방문자 트래킹 - 수집 스크립트 (app/Controllers/Web/Track.php / TrackService.php 설계 문서 참고)
 * visit_id는 세션(방문) 단위로 sessionStorage에 저장해 페이지 이동 간 재사용한다.
 *
 * client_key: visit_id는 서버 응답을 받아야 알 수 있는데, 그 응답 전에 페이지를 이동하면
 * 다음 페이지도 여전히 visit_id가 없다. 그래서 visit_id가 없는 동안엔 이 스크립트가 즉시 만들어
 * sessionStorage에 저장해둔 client_key를 대신 실어보낸다(같은 세션이면 항상 같은 값이라 서버가
 * 중복 신규등록을 걸러낼 수 있음 - TrackService::resolveVisitId() 참고). visit_id가 응답으로
 * 확정되면 그 뒤로는 안 보낸다.
 *
 * 페이지별 값(page_type/property_no/referrer)은 common/footer.php가 BOMUL_TRACK_CONFIG로
 * 미리 심어둔다. 이 파일은 정적 리소스라 PHP 값을 직접 embed할 수 없어서 그렇게 분리했다.
 */
(function(){
    var cfg = window.BOMUL_TRACK_CONFIG || {};

    var TRACK_URL    = "/track/collect"; // SITE(api.bomul.co.kr)가 아닌 현재 접속 도메인 기준 상대경로 - 이 라우트는 non-api 호스트에만 등록되어 있음
    var VISIT_KEY    = "bomul_visit_id";
    var CLIENT_KEY   = "bomul_client_key";
    var RETURN_KEY   = "bomul_returning";
    var PAGE_TYPE    = cfg.pageType || '';
    var PROPERTY_NO  = cfg.propertyNo || null;
    // 반드시 "이 페이지" 요청의 Referer - 트래킹 요청 자체의 Referer(document.referrer)는 신뢰 불가
    var LANDING_REFERRER = cfg.landingReferrer || '';

    function pad(n){ return n < 10 ? '0' + n : '' + n; }
    function nowIso(){
        var d = new Date();
        return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + ' '
             + pad(d.getHours()) + ':' + pad(d.getMinutes()) + ':' + pad(d.getSeconds());
    }
    function toFormData(payload){
        var fd = new FormData();
        Object.keys(payload).forEach(function(k){
            if (payload[k] !== null && payload[k] !== undefined && payload[k] !== '') fd.append(k, payload[k]);
        });
        return fd;
    }
    function getVisitId(){ return sessionStorage.getItem(VISIT_KEY) || ''; }
    function setVisitId(id){ sessionStorage.setItem(VISIT_KEY, id); }

    // visit_id가 없는 동안만 사용할 FE 발급 임시키. 한 번 만들면 세션 내내(sessionStorage) 재사용
    function getClientKey(){
        var key = sessionStorage.getItem(CLIENT_KEY);
        if (!key) {
            key = (window.crypto && crypto.randomUUID) ? crypto.randomUUID()
                : (Date.now().toString(36) + Math.random().toString(36).slice(2));
            sessionStorage.setItem(CLIENT_KEY, key);
        }
        return key;
    }

    function basePayload(eventType){
        var visitId = getVisitId();
        var payload = {
            visit_id: visitId,
            event_type: eventType,
            occurred_at: nowIso(),
            page_type: PAGE_TYPE,
            property_no: PROPERTY_NO,
            request_url: location.pathname + location.search
        };
        if (!visitId) payload.client_key = getClientKey();
        return payload;
    }

    // unload 시점(dwell/click)엔 유실 없는 sendBeacon, 그 외(pageview 응답 필요)엔 fetch
    function beacon(payload){
        if (navigator.sendBeacon) navigator.sendBeacon(TRACK_URL, toFormData(payload));
        else fetch(TRACK_URL, { method: 'POST', body: toFormData(payload), keepalive: true });
    }

    var isNewVisit = getVisitId() === '';
    var pageview = basePayload('pageview');

    if (isNewVisit) {
        var params = new URLSearchParams(location.search);
        pageview.referrer_url = LANDING_REFERRER;
        pageview.utm_source   = params.get('utm_source') || '';
        pageview.utm_medium   = params.get('utm_medium') || '';
        pageview.utm_campaign = params.get('utm_campaign') || '';
        pageview.viewport_w   = window.innerWidth;
        pageview.viewport_h   = window.innerHeight;
        pageview.is_returning = localStorage.getItem(RETURN_KEY) ? 'Y' : 'N';
        localStorage.setItem(RETURN_KEY, '1');

        // 신규 방문은 서버가 발급한 visit_id를 응답으로 받아야 하므로 sendBeacon이 아닌 fetch 사용.
        // keepalive: true 필수 - 없으면 응답 전에 페이지가 언로드(뒤로가기 등)될 때 요청 자체가
        // 중단돼 방문/첫 pageview가 통째로 유실된다 (매물상세 직링크 진입처럼 빠른 이탈이 흔한
        // 페이지에서 특히 치명적).
        fetch(TRACK_URL, { method: 'POST', body: toFormData(pageview), keepalive: true })
            .then(function(res){ return res.json(); })
            .then(function(json){
                if (json && json.RECORD && json.RECORD.visit_id) setVisitId(json.RECORD.visit_id);
            })
            .catch(function(){});
    } else {
        beacon(pageview);
    }

    // dwell: 진입~이탈 체류시간. 폴링 없이 이탈 시점(visibilitychange/pagehide)에만 1회 전송
    var enteredAt = Date.now();
    var dwellSent = false;
    function sendDwell(){
        if (dwellSent) return;
        dwellSent = true;
        var payload = basePayload('dwell');
        payload.dwell_ms = Date.now() - enteredAt;
        beacon(payload);
    }
    document.addEventListener('visibilitychange', function(){
        if (document.visibilityState === 'hidden') sendDwell();
    });
    window.addEventListener('pagehide', sendDwell);

    // click: data-track-click="tel|reserve|kakao|favorite|share 등" 속성이 붙은 요소만 위임 수집
    document.addEventListener('click', function(e){
        var el = e.target.closest('[data-track-click]');
        if (!el) return;
        var payload = basePayload('click');
        payload.click_action = el.getAttribute('data-track-click');
        beacon(payload);
    });
})();
