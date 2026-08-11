<?php

/*
 * 마더 배치 - crontab이 매분 이 파일 하나만 호출한다 (* * * * * php app/batch/batch.php).
 * 새 배치가 늘어나도 crontab은 안 건드리고 여기에 시간대별 블록만 추가하면 된다.
 * bomul-api의 app/batch/batch.php와 동일한 패턴.
 *
 * 의도적으로 CI4 프레임웍을 부팅하지 않는다 - 매분 실행되니 가볍게 유지하고, 실제 작업이
 * 필요한 잡 파일만 require한다. 각 잡 파일이 필요한 만큼만 CI4를 부팅한다(bootConsole() -
 * spark가 쓰는 bootSpark()보다 가벼움. queue_push.php/service_request_collect.php 참고).
 */

$h = date('H');
$i = date('i');

// 큐 push - queue의 PENDING 건을 배정된 운영서버(server_list.receiver_url)로 전송.
// 승인/연장/상태변경은 아무 때나 일어나므로 시간대 조건 없이 항상 매분 호출.
require __DIR__ . '/queue_push.php';

// service_live.status='REQUEST' 건 수집 - 관리자가 상세화면에서 "요청"으로 바꾼 건을 모아
// 큐에 적재한다. 이것도 시간대 조건 없이 항상 매분 호출.
require __DIR__ . '/service_request_collect.php';

// 사이트운영 상태 갱신 - 매일 06시대, 만료일 지난 정상/유예 건을 종료 처리하고 queue로
// 운영서버에 통보. 02시 analytics처럼 활성 시간대 동안(06시 내내) 매분 재시도 - 대상 없으면
// 바로 조용히 종료하니 부담 없음.
if ($h === '06') {
    require __DIR__ . '/service_expire_check.php';
}

// 단발성/시간대 배치 예시 (시:분까지 정확히 매칭될 때 딱 한 번만, 또는 특정 시간대 안에서만):
// if ($h === '03' && $i === '00') {
//     require __DIR__ . '/example.php';
// }

return;
