# bomul-bo 작업 맵

> 마지막 정리: 2026-08-10

## 프로젝트 개요

CodeIgniter 4 기반 **부동산 중개사(고객) 대상 서비스의 백오피스/서비스 서버**.
고객이 서비스를 신청(`service_apply`)하면 관리자가 승인하고, 승인 시 `service_live`/`queue`가 생성되어
매분 크론으로 별도 운영 서버(`bomul-api`)에 push되어 테넌트(상점) DB가 자동 프로비저닝되는 구조.
Admin(BO)과 일반 고객용 사용자 페이지가 분리되어 있으며, 회원/로그인/상품/결제/서비스신청/서비스운영 엔티티가 존재.

---
### 미해결 업무
- [ ] **미해결** 사용자(중개사) 가입시 필수 서류 — 사업자 등록증, 중개사무소 등록에서 사무소 개설등록증 추가
- [ ] api 수신 핸들러 : `action='extend'`/`action='status'` (bomul-api 저장소, 이 저장소 범위 밖) — payload의 `status`는 BO 로컬 4단계가 아니라 **운영서버용 3단계 코드**로 보냄(2026-08-10 확정, [ServiceApplyService.php](app/Service/ServiceApplyService.php)의 `remoteStatus()`): 정상→`OPERATING`(운영, 전체 접근 가능) / 유예→`USER_CLOSED`(사용자 화면만 차단, 중개사 관리자는 로그인 가능) / 종료→`ALL_CLOSED`(관리자·사용자 전체 차단). bomul-api는 이 3개 코드를 받아 `bomul.member.status`에 반영하는 핸들러를 만들어야 함.
- [ ] cron 사이트운영 상태 갱신 — 매일 배치로 만료된(expire_date < 오늘) ACTIVE 건을 자동으로 TERMINATED 전환 + queue 통보 (지금은 관리자가 수동으로 만료일/상태를 바꿀 때만 반영됨, 자동 배치는 아직 없음 — [ServiceApplyService.php:257](app/Service/ServiceApplyService.php:257) 참고)
- [x] **서비스 라이브 상세 — 세팅된 서버 정보 / 세팅 정보 / 상태·설치진행상태·만료일 수정** — 위 60~74행 "3차 수정" 항목에서 구현 완료. 요청사항 재확인:
  - 세팅된 서버 정보(서버명/서버host/설치된 홈페이지) ✅, 세팅 정보(install_status/install_requested_at/installed_at/install_error) ✅
  - 수정 가능 정보 = 서비스 상태 / 설치진행상태 / 만료일 ✅
  - 서비스 상태 요청(REQUEST) 추가 + cron이 수집해 queue 등록 ✅ ([ServiceRequestCollect.php](app/Commands/ServiceRequestCollect.php))
  - 만료일 변경 시 오늘 기준 유지/종료 판단해 queue 등록(→ 운영서버 bomul.member.status 반영) ✅ (`applyExpireDate()`)
  - **참고**: "서버내 설치된 홈페이지수"는 URL(설치된 홈페이지 링크)로 해석해 구현함 — 만약 해당 서버에 설치된 테넌트 개수(예: `server_list.tenant_count`)를 의미한 것이라면 별도 요청 필요
- [ ] **자동 로그인** — 회원/라이브서비스 리스트에서 `bo.bomul.ai.kr`로 토큰 기반 자동 로그인 연동
- [ ] **서버 리스트 관리** — `server_list` 추가/변경/삭제 CRUD (Admin)
- [ ] **결제상황 리스트** — 결제 모듈 연동 이후 진행 (선행 조건: 결제 모듈 연동 자체가 아직 없음)
- [ ]`service_live.status`에 **REQUEST(요청)** 상태 신설 — 관리자가 요청으로 바꾸면 새 크론 [ServiceRequestCollect.php](app/Commands/ServiceRequestCollect.php)(`spark service:collect-request`, 매분 crontab 등록 필요)가 수집해 큐(action='new')에 적재하고 상태를 ACTIVE로 되돌림

---

## 해결 업무
