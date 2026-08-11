# bomul-bo 작업 맵

> 마지막 정리: 2026-08-10

## 프로젝트 개요

CodeIgniter 4 기반 **부동산 중개사(고객) 대상 서비스의 백오피스/서비스 서버**.
고객이 서비스를 신청(`service_apply`)하면 관리자가 승인하고, 승인 시 `service_live`/`queue`가 생성되어
매분 크론으로 별도 운영 서버(`bomul-api`)에 push되어 테넌트(상점) DB가 자동 프로비저닝되는 구조.
Admin(BO)과 일반 고객용 사용자 페이지가 분리되어 있으며, 회원/로그인/상품/결제/서비스신청/서비스운영 엔티티가 존재.

---

## 업무 스택

할 일과 처리 여부만 짧게 기록. 번호는 고정 ID — [처리내용](#처리내용)에서 이 번호로 어떤 업무의 처리인지 연결한다. 완료돼도 번호/항목은 그대로 두고 체크만 표시.

- [ ] 1. 사용자(중개사) 가입 시 필수서류 추가 — 사업자 등록증, 중개사무소 개설등록증
- [ ] 2. bomul-api 수신 핸들러 — `action='extend'`/`action='status'` (별도 저장소, bomul-api 작업)
- [x] 3. cron 사이트운영 상태 갱신 — 만료일 지난 건 자동 종료 배치 (현재는 관리자 수동 변경 시에만 반영)
- [ ] 4. 자동 로그인 — 회원/라이브서비스 리스트에서 `bo.bomul.ai.kr`로 토큰 기반 자동 로그인
- [ ] 5. 서버 리스트 관리 — `server_list` 추가/변경/삭제 CRUD (Admin)
- [ ] 6. 결제상황 리스트 — 결제 모듈 연동 이후 진행
- [ ] 7. crontab을 `app/batch/batch.php` 한 줄로 교체 (인프라 작업, 기존 spark 크론 대체)
- [x] 8. 서비스 라이브(service_live) 리스트 / 상세 / 기간 연장·축소 / 상태 변경
- [x] 9. 통합 배치 진입점 `app/batch/batch.php` 신설
- [x] 10. 운영 DB에 `service_live.suspended_at` 컬럼 반영 
- [ ] 11. 실결제 연동.
- [x] 12. 서비스 리스트(상품) 관리 
- [ ] 13. 웹 서비스 하드코딩을 ad 서비스 정보로 코딩.
- [ ] 14. 약관, 개인정보 처리방침
- [ ] 15. 웹페이지 변경.
   ```
  https://www.cafe24.com/commerce/start/founder.html
  -> 홈페이지를 만들어주는곳 가격비교와 기능비교
  비교적 저렴하면서, 공인중개사를 위한 홈피는없었다 강조
  네이버 부동산에 올리긴 하지만, 내정보를 네이버에게 주는 느낌?
  온라인 이벤트를 하고 싶지만 랜딩 페이지가 없어서 고민
  나만의 홈페이지가 갖고 싶지만 , 만드는방법도 유지할 방법도 모르는 사람
  ``` 

---

## 처리내용

### #12 (2026-08-10) — 상품(서비스) 관리 CRUD 신설

- Admin 사이드메뉴 "상품관리" 그룹 신설(`/Product`) — 고객이 서비스 신청 시 선택하는 `product` 테이블(요금제) CRUD. `Manager` 컨트롤러의 가장 단순한 CRUD 패턴을 그대로 따름
- 목록/등록/수정/삭제, 필드: 상품코드/상품명/가격/결제방식(월구독·1회결제·미결제)/이용기간(일)/이벤트여부/상태(사용·중지)/정렬순서/설명
- 상품코드 중복 체크(`ProductModel::existsByCode()`), 이미 신청 이력이 있는 상품은 삭제 대신 '중지' 상태로 유도(고아 참조 방지 — [ServiceApplyModel::existsByProductNo()](app/Models/ServiceApplyModel.php) 신규)
- `ProductModel`에 없던 `sql_save`/`sql_edit`/`sql_delete`/`get_list` 신규 추가(기존엔 조회 전용이었음)
- 신규/변경 파일: [ProductModel.php](app/Models/ProductModel.php), [ProductService.php](app/Service/ProductService.php), [Controllers/Admin/Product.php](app/Controllers/Admin/Product.php), [Views/Admin/Product/list.php](app/Views/Admin/Product/list.php), [Views/Admin/Product/write.php](app/Views/Admin/Product/write.php), [Views/Admin/Product/modify.php](app/Views/Admin/Product/modify.php), [Routes.php](app/Config/Routes.php), [admin_menu.php](app/Views/common/admin_menu.php)
- 실제 스키마 확인 완료(2026-08-10) 

### #3 (2026-08-10, 3단계 유예 정책으로 수정) — 사이트운영 상태 갱신 배치 (매일 06시)

`service_live.status`와 운영서버 `bomul.member.status`를 3단계로 동기화: 정상(ACTIVE=OPERATING) →
유예(SUSPENDED=USER_CLOSED, 사용자웹만 차단·중개사 관리자는 로그인 가능) → 종료(TERMINATED=ALL_CLOSED, 전체 차단).

- 1단계 — 만료일 당일이 된 정상(ACTIVE) 건 → 유예(SUSPENDED) 전환. [ServiceLiveModel::get_expired_active()](app/Models/ServiceLiveModel.php)가 `expire_date <= CURDATE()`인 ACTIVE 건을 조회, 큐(action='status', `USER_CLOSED`) 성공 시에만 로컬 status를 SUSPENDED로 갱신
- 2단계 — 유예 시작 후 15일(`SUSPEND_GRACE_DAYS`) 경과한 건 → 종료(TERMINATED) 전환. [ServiceLiveModel::get_suspended_expired()](app/Models/ServiceLiveModel.php)가 `suspended_at <= NOW()-15일`인 SUSPENDED 건을 조회, 큐(action='status', `ALL_CLOSED`) 성공 시에만 TERMINATED로 갱신
- 두 단계 모두 **큐 적재 성공 후에만** 로컬 status 갱신 — 실패 시 조회 조건에 계속 남아 다음 실행 때 재시도됨
- 15일 경과 판정은 신규 컬럼 `service_live.suspended_at`(SUSPENDED로 바뀐 시점) 기준 — [ServiceLiveModel::updateStatus()](app/Models/ServiceLiveModel.php)가 상태 전환마다 자동 관리(SUSPENDED 진입 시 NOW() 기록, ACTIVE 복귀 시 NULL 초기화). 마이그레이션:  (→ #10, 운영 DB 반영 필요)
- 관리자가 상세화면에서 수동으로 만료일을 바꿀 때도 동일한 3단계 규칙 적용 — [ServiceApplyService::applyExpireDate()](app/Service/ServiceApplyService.php): 오늘<만료일→정상 / 만료일 당일~+15일→유예 / 그 이후→종료. 상세화면에 유예 마감일/남은 일수 표시 추가 ([Views/Admin/ServiceLive/view.php](app/Views/Admin/ServiceLive/view.php))
- `batch.php`에 `$h === '06'` 블록 추가 — analytics 패턴처럼 06시 내내 매분 재시도, 처리 대상 없으면 바로 종료

### #8 (2026-08-10) — 서비스 라이브 리스트/상세, 기간 연장·축소, 상태 변경

- 목록: Admin 사이드메뉴 "서비스 라이브 현황"(`/ServiceLive`) — 만료 임박순 정렬, 행 클릭 시 상세 이동
- 상세(`/ServiceLive/view/{apply_no}`):
  - 세팅된 서버 정보(서버명/서버host=`server_list.receiver_url`/설치된 홈페이지=`https://{fid}.bomul.ai.kr`)
  - 세팅 정보(install_status/install_requested_at/installed_at/install_error, 읽기전용)
  - 수정 가능 항목: 서비스 상태(요청/정상/유예/종료), 설치진행상태, 만료일
- 만료일 변경(30일/60일 빠른 연장 + 캘린더 직접 지정):
  - 연장 기준일: 오늘<만료일이면 만료일 기준, 오늘>=만료일이면 오늘 기준으로 +days
  - 변경 즉시 `service_live.expire_date`/`status` 반영(낙관적 갱신) 후 `queue.action='extend'`로 운영서버 통보
  - 새 만료일이 오늘 이후면 상태 자동으로 '정상', 이전이면 '종료'로 갱신
- 서비스 상태(요청/정상/유예/종료) 신설:
  - '요청'(REQUEST)으로 바꾸면 로컬 상태만 변경, 큐는 안 태움 — 크론 [ServiceRequestCollect.php](app/Commands/ServiceRequestCollect.php)가 수집해 큐(action='new')에 적재 후 상태를 정상으로 되돌림
  - 정상/유예/종료는 설치완료 건만 대상, 즉시 반영 + `queue.action='status'`로 운영서버 통보
- 큐 payload의 `status`는 BO 로컬 4단계가 아니라 **운영서버용 3단계 코드**로 변환해서 전송(`remoteStatus()`):
  정상→`OPERATING`(전체 접근 가능) / 유예→`USER_CLOSED`(사용자 화면만 차단, 중개사 관리자는 로그인 가능) / 종료→`ALL_CLOSED`(관리자·사용자 전체 차단)
- 큐 적재 실패 시 조용히 성공 처리되던 버그 수정 — 실패하면 "운영서버에는 아직 반영되지 않았습니다" 경고를 성공 메시지에 덧붙임
- 변경/신규 파일: [ServiceLiveModel.php](app/Models/ServiceLiveModel.php), [ServiceApplyService.php](app/Service/ServiceApplyService.php), [Controllers/Admin/ServiceLive.php](app/Controllers/Admin/ServiceLive.php), [Views/Admin/ServiceLive/list.php](app/Views/Admin/ServiceLive/list.php), [Views/Admin/ServiceLive/view.php](app/Views/Admin/ServiceLive/view.php), [Commands/ServiceRequestCollect.php](app/Commands/ServiceRequestCollect.php), [Callback.php](app/Controllers/Admin/Callback.php), [Routes.php](app/Config/Routes.php), [admin_menu.php](app/Views/common/admin_menu.php)
- 참고: "서버내 설치된 홈페이지수"는 URL(설치된 홈페이지 링크)로 해석해서 구현함 — 해당 서버의 테넌트 개수(`server_list.tenant_count`)를 의미한 것이었다면 별도 요청 필요(→ #3/#5와 연계 검토)

### #9 (2026-08-10) — 통합 배치 진입점 `app/batch/batch.php`

bomul-api의 마더 배치 패턴을 그대로 이식. crontab을 여러 줄(`spark queue:push`, `spark service:collect-request`) 대신 `* * * * * php app/batch/batch.php` 한 줄로 통합 가능 (실제 crontab 교체는 #7).

- [batch.php](app/batch/batch.php) — 마더. 프레임웍 안 부팅, 잡 파일을 require만 함
- [queue_push.php](app/batch/queue_push.php) — `QueuePush` 커맨드와 동일 로직, `bootConsole()`로 가볍게 부팅
- [service_request_collect.php](app/batch/service_request_collect.php) — `ServiceRequestCollect` 커맨드와 동일 로직 (#8의 REQUEST 상태 수집)
- 기존 `app/Commands/QueuePush.php`/`ServiceRequestCollect.php`(spark 커맨드)는 수동/디버그용으로 그대로 유지
