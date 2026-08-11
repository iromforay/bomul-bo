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

- [x] 1. 사용자(중개사) 가입 시 필수서류 추가 — 사업자 등록증, 중개사무소 개설등록증 (마이페이지에서 제출받는 방식으로 구현)
- [ ] 2. bomul-api 수신 핸들러 — `action='extend'`/`action='status'` (별도 저장소, bomul-api 작업)
- [x] 3. cron 사이트운영 상태 갱신 — 만료일 지난 건 자동 종료 배치 (현재는 관리자 수동 변경 시에만 반영)
- [ ] 4. 자동 로그인 — 회원/라이브서비스 리스트에서 `bo.bomul.ai.kr`로 토큰 기반 자동 로그인
- [x] 5. 서버 리스트 관리 — `server_list` 추가/변경/삭제 CRUD (Admin)
- [ ] 6. 결제상황 리스트 — 결제 모듈 연동 이후 진행
- [ ] 7. crontab을 `app/batch/batch.php` 한 줄로 교체 (인프라 작업, 기존 spark 크론 대체)
- [x] 8. 서비스 라이브(service_live) 리스트 / 상세 / 기간 연장·축소 / 상태 변경
- [x] 9. 통합 배치 진입점 `app/batch/batch.php` 신설
- [x] 10. 운영 DB에 `service_live.suspended_at` 컬럼 반영 
- [ ] 11. 실결제 연동.
- [x] 12. 서비스 리스트(상품) 관리 
- [x] 13. 웹 서비스 하드코딩을 ad 서비스 정보로 코딩.
- [ ] 14. 약관, 개인정보 처리방침
- [ ] 15. 웹페이지 변경.
- [x] 16. 축약형 마이페이지 .
- [x] 17. 서비스 연장/정기결제 신청. — 연장(마이페이지 0원 신청)만 완료, 정기결제는 미착수(#11 대기)
- [x] 18. 상담문의   — #24 통합 게시판으로 구조 완료(board_type=INQUIRY)
- [x] 19. QNA   — #24 Admin CRUD + #25 www 목록/상세/글쓰기까지 완료(board_type=QNA)
   ```
  https://www.cafe24.com/commerce/start/founder.html
  -> 홈페이지를 만들어주는곳 가격비교와 기능비교
  비교적 저렴하면서, 공인중개사를 위한 홈피는없었다 강조
  네이버 부동산에 올리긴 하지만, 내정보를 네이버에게 주는 느낌?
  온라인 이벤트를 하고 싶지만 랜딩 페이지가 없어서 고민
  나만의 홈페이지가 갖고 싶지만 , 만드는방법도 유지할 방법도 모르는 사람
  ``` 
- [x] 16. 서비스 신청 상세 - 승인완료 시 세팅 진행 현황(대기/세팅중/완료·실패) 표시
- [x] 17. 서비스 신청 현황 리스트에도 신청상태 옆 "세팅현황" 컬럼 추가
- [x] 18. 상품 entity 재구성 — product_name에서 기간 분리, product_type(기본상품/부가서비스) 신설
- [x] 19. 상품에 정가(원가) 추가 — 할인 강조 표시
- [x] 20. service_apply 신규/연장 신청 구분 — 승인 시 queue action 분기(new/extend)
- [x] 21. 마이페이지 서비스 연장 신청 화면(www) — 상품선택 + 0원/유료 결제 분기점
- [x] 22. 0원 신청도 결제 이력(payment)에 기록되도록
- [x] 23. Admin 서비스 신청 상세에 신청 상품 정보(가격/기간/정가) 표시
- [x] 24. 통합 게시판(상담문의/QNA/공지사항) Admin CRUD 구조 신설 — www 작성 화면은 미포함(다음 단계)
- [x] 25. www QNA 게시판(목록/상세/글쓰기) — 통합 게시판(#24) board_type=QNA 그대로 사용
- [x] 26. www 상담문의(홈 리드폼 실연동) + 공지사항(목록/상세) — 통합 게시판(#24) board_type=INQUIRY/NOTICE

---

## 처리내용

### #26 (2026-08-11) — www 상담문의(홈 리드폼 실연동) + 공지사항(목록/상세)

**상담문의(INQUIRY) — 해피콜 모델로 결정**: 비회원이 이름+연락처만 남기는 리드폼이라 로그인 개념이 없음. "나중에 본인이 남긴 글을 다시 볼 방법"을 고민했는데, 글번호+비밀번호 조회 기능을 새로 만드는 대신 **관리자가 남긴 연락처로 직접 회신하는 해피콜 방식으로 확정**(작성자 확인 요청, 승인) — 그래서 자기글조회 기능은 없음, 필요해지면 비밀번호 컬럼+조회화면을 별도로 추가해야 함.

- [2026_08_11_board_writer_phone.sql](app/sql/2026_08_11_board_writer_phone.sql) 신규 — `board.writer_phone VARCHAR(20)` 추가(콜백 연락처). **운영 DB에 수동 반영 필요**
- [Board_Entity.php](app/Entity/Board_Entity.php)/[BoardModel.php](app/Models/BoardModel.php)/[BoardService.php](app/Service/BoardService.php) — `writer_phone` 필드 배관(save/edit 양쪽)
- [Controllers/Home.php](app/Controllers/Home.php) — `postInquiry()` 신규. 원래 `action="#"`로 아무 데도 안 가던 홈 "상담 신청" 섹션(`#inquiry`) 폼을 `board_type='INQUIRY'`로 실제 저장하게 연결. 제목은 "{이름}님의 상담 신청"으로 자동 생성(원래 폼에 제목 입력란이 없어서)
- [Views/index.php](app/Views/index.php) — 폼 action을 `/inquiry`로 변경, 접수/에러 안내 문구 표시 추가
- [Admin/Board/write.php](app/Views/Admin/Board/write.php)/[modify.php](app/Views/Admin/Board/modify.php)/[list.php](app/Views/Admin/Board/list.php) — "작성자 연락처" 입력란/목록 표시 추가, [Controllers/Admin/Board.php](app/Controllers/Admin/Board.php) 저장 시 같이 넘기도록 반영

**공지사항(NOTICE) — 읽기 전용**: 관리자만 작성(`/Board/notice`), www는 목록/상세만.

- [Controllers/Notice.php](app/Controllers/Notice.php), [Views/notice_list.php](app/Views/notice_list.php)/[notice_view.php](app/Views/notice_view.php) 신규 — QNA와 동일하게 `BoardService::get_public_list/get_public_row` 재사용(숨김글 제외, 조회수 증가)

**공통**
- [Routes.php](app/Config/Routes.php) — `POST inquiry`, `notice`, `notice/view/(:num)` 라우트 추가
- [header_menu.php](app/Views/common/header_menu.php) — 데스크톱/모바일 메뉴에 "공지사항" 링크 추가(QNA 옆)
- 이번 범위 밖: 상담문의 자기글조회(위 결정으로 보류), 공지사항 상단고정/게시기간, 답변 알림

### #25 (2026-08-11) — www QNA 게시판(목록/상세/글쓰기)

통합 게시판(#24)의 `board_type='QNA'`를 그대로 써서 www 공개 화면을 붙임. 목록/상세는 비로그인도 열람 가능, 글쓰기만 로그인 고객으로 제한(마이페이지 `requireLogin()`과 동일 패턴).

- [BoardModel.php](app/Models/BoardModel.php) — `get_list_by_type()`에 `$onlyVisible` 파라미터 추가(www 목록은 `use_yn='Y'`만), `incrementViewCount()` 신규
- [BoardService.php](app/Service/BoardService.php) — `get_public_list()`/`get_public_row()`(숨김글 URL 직접접근 차단 + 조회수 증가) 신규, `save()`가 `customer_no`도 저장하도록 보강(지금까지는 Admin 작성만 있어서 항상 NULL이었음)
- [Controllers/Qna.php](app/Controllers/Qna.php) 신규 — `getIndex`(목록)/`getView`(상세, 조회수 증가)/`getWrite`+`postWrite`(로그인 필요, 작성자명은 회사명/아이디로 자동 채움)
- [Views/qna_list.php](app/Views/qna_list.php)/[qna_view.php](app/Views/qna_view.php)/[qna_write.php](app/Views/qna_write.php) 신규 — mypage 화면과 동일한 `.signup-wrap/.signup-card` 마크업 재사용
- [Routes.php](app/Config/Routes.php) — `qna`, `qna/write`, `qna/view/(:num)` 라우트 추가
- [header_menu.php](app/Views/common/header_menu.php) — 데스크톱/모바일 상단 메뉴에 "QNA" 링크 추가
- 이번 범위 밖: 내가 쓴 문의만 보기(마이페이지 연동), 답변 알림(이메일/문자), 페이징(현재 전체 목록을 한 번에 다 보여줌 — 글이 많아지면 `calPaging()`을 붙여야 함, 이 프로젝트에서 아직 어디서도 실사용된 적 없는 헬퍼)

### #24 (2026-08-11) — 통합 게시판(상담문의/QNA/공지사항) Admin CRUD 신설

세 게시판을 각각 따로 만들지 않고 테이블 하나(`board`)를 `board_type`(INQUIRY/QNA/NOTICE)으로 나눠 쓰는 구조. Manager/Product CRUD와 동일한 패턴(raw SQL Model + Service + Admin Controller)을 그대로 따름. **CRUD 구조만 우선 구현** — www(고객)쪽 작성/조회 화면은 이번 범위 밖.

- [2026_08_11_board_table.sql](app/sql/2026_08_11_board_table.sql) 신규. **운영 DB에 수동 반영 필요**. 컬럼: `board_type`, `title`, `content`, `writer_name`(비회원/관리자 작성 시 표시용), `customer_no`(로그인 작성자, 현재 미사용 — 향후 www 연동 시 채움), `status`(WAIT/DONE, 공지사항은 의미없음), `reply_content`/`replied_at`, `view_count`(현재 증가 로직 없음 — www 상세화면 붙일 때 추가 필요), `use_yn`(노출/숨김)
- [Board_Entity.php](app/Entity/Board_Entity.php), [BoardModel.php](app/Models/BoardModel.php)(`get_list_by_type`/`sql_save`/`sql_edit`/`sql_delete`), [BoardService.php](app/Service/BoardService.php) 신규
  - `BoardService::TYPES`(URL세그먼트→DB코드: inquiry→INQUIRY, qna→QNA, notice→NOTICE)/`TYPE_LABELS`로 타입 매핑 일원화. 정의 안 된 타입 접근 시 컨트롤러가 `/Home`으로 돌려보냄
  - `get_row()`는 `board_type`도 같이 검증해서 다른 게시판 글 번호로 직접 접근하는 것 방지
- [Controllers/Admin/Board.php](app/Controllers/Admin/Board.php) 신규 — 컨트롤러 하나가 `{type}` 세그먼트로 세 게시판 전부 처리 (list/write/modify/delete)
- [Views/Admin/Board/list.php](app/Views/Admin/Board/list.php)/[write.php](app/Views/Admin/Board/write.php)/[modify.php](app/Views/Admin/Board/modify.php) 신규 — Manager 목록/폼 마크업 그대로 재사용
- [Routes.php](app/Config/Routes.php) — `Board/(:any)`로 타입별 CRUD 라우트 등록
- [admin_menu.php](app/Views/common/admin_menu.php) — 기존에 라우트 없이 링크만 있던 "상담문의"(`/Inquiry`)·"공지사항"(`/Notice`)을 `/Board/inquiry`·`/Board/notice`로 교체 연결, "QNA 게시판"(`/Board/qna`) 링크 신규 추가. `/Faq`는 이번 범위 밖이라 그대로 둠(아직 미구현)
- 다음 단계로 필요할 것: www(고객) 작성/목록 화면, 상세 조회+조회수 증가, 상담문의 비밀글(작성자 본인+관리자만 열람) 여부 결정, 공지사항 노출기간(게시시작/종료일) 필요 여부

### #22 후속 (2026-08-11) — 0원 결제이력 누락 버그 진단/수정 + 배포 동기화 이슈 발견

마이페이지에서 0원 서비스 연장 신청 시 `service_apply`는 생성되는데 `payment`엔 행이 안 남는다는 제보 → 코드 추적.

- **코드 자체는 정상**이었음(로컬 기준) — 원인은 **운영 서버가 이 저장소의 최신 커밋을 실행하고 있지 않았던 것**(배포 동기화 누락). 파일을 하나씩 개별 업로드하다 보니 `ServiceApplyService.php`/`PaymentModel.php`/`Payment_Entity.php`가 서로 다른 시점 버전으로 섞여 있었고, 그 결과 `Call to undefined method PaymentModel::sql_save()` 에러까지 발생 — 관련 파일 재업로드로 해소
- **재발 방지용 코드 보강**([ServiceApplyService.php](app/Service/ServiceApplyService.php)):
  - `apply()`/`applyRenew()` — `service_apply` INSERT와 `recordFreePayment()`(payment INSERT)를 `db->transStart()/transComplete()`로 묶어서, 향후 payment INSERT만 실패하는 경우에도 신청서만 반쪽으로 남는 상태 방지
  - `recordFreePayment()` — `paymentModel->sql_save()` 실패 시 조용히 넘어가지 않고 `log_message('error', ...)`로 apply_no/DB에러/실행 SQL을 `writable/logs`에 남기도록 변경

### #21~23 (2026-08-11) — 마이페이지 연장 신청 화면 + 0원 결제이력 + Admin 상품정보 표시

**#21 www 연장 신청 화면**
- `/mypage/extend`(GET) — 고객의 현재 서비스(설치완료 건만) 확인 후 상품(기간) 선택 그리드 표시. `unit/product.php`와 동일한 카드 스타일(할인율/기간배지) 재사용
- `/mypage/extend`(POST) — 상품 선택 제출. **결제 분기점**: `price>0`이면 신청서를 만들지 않고 `/mypage/extend/pay`(결제 스텁)로만 보냄, `price=0`이면 [ServiceApplyService::applyRenew()](app/Service/ServiceApplyService.php) 즉시 호출해 신청 접수
- `applyRenew()` — 신규 신청과 달리 사업자정보/도메인을 다시 안 받고, `service_live.apply_no`로 찾은 원본 신청서 스냅샷을 그대로 복사해 `apply_type=RENEW` 신청서만 새로 생성
- `/mypage/extend/pay`(GET) — 결제 모듈(#11) 연동 전까지의 스텁 화면. "선택 상품/가격 확인 → 준비중 안내"만 하고, 나중에 여기에 PG 호출을 붙이면 되는 자리로 잡아둠
- 신규/변경: [Controllers/MyPage.php](app/Controllers/MyPage.php), [Views/mypage_extend.php](app/Views/mypage_extend.php), [Views/mypage_extend_pay.php](app/Views/mypage_extend_pay.php), [Routes.php](app/Config/Routes.php)
- 테스트 중 발견: 특정 `service_live`가 `install_status=FAILED`(운영서버 `bomul.member`에 `user_id='demo'` 중복으로 설치 실패)라 연장 신청이 막힘 — 이 저장소 밖(bomul-api/운영DB) 조치 필요, 해결 후 Admin "큐 적재" 버튼으로 재설치 재시도하면 됨

**#22 0원 신청도 결제 이력(payment)에 기록**
- [PaymentModel::sql_save()](app/Models/PaymentModel.php) 신규 — 이 모델에 처음 생기는 INSERT 메서드(기존엔 조회 전용)
- [ServiceApplyService::recordFreePayment()](app/Service/ServiceApplyService.php) 신규 — `product.price<=0`일 때만 `pay_method='NONE', pay_amount=0, pay_status='PAID'`로 즉시 기록. `apply()`(신규)/`applyRenew()`(연장) 성공 직후 둘 다 호출
- 유료 신청은 아직 호출 지점 자체가 없음(#11 실결제 연동 후 결제완료 콜백에서 같은 메서드로 pg_provider/pg_tid까지 채워 기록하면 됨)

**#23 Admin 서비스 신청 상세에 상품 정보 표시**
- [ServiceApplyModel::get_detail_by_apply_no()](app/Models/ServiceApplyModel.php) — `product`에서 `product_type/price/list_price/billing_type/period_day/is_event/description`까지 조인 확장
- [Views/Admin/Service/view.php](app/Views/Admin/Service/view.php) — "신청 상품 정보" 패널 신규(상품명+기간배지+이벤트배지, 상품코드/유형, 판매가+정가 취소선, 상품설명). "신청 정보" 패널에도 신청유형(신규/연장) 배지 + 연장이면 대상 live_no 표시 추가

### #20 (2026-08-11) — service_apply 신규/연장 신청 구분, 승인 시 queue action 분기

`service_apply`에 `apply_type`(NEW/RENEW)과 `renew_live_no`(연장 대상 service_live.live_no, RENEW일 때만) 컬럼 신설. 연장 신청이 어떤 기존 서비스를 연장하는지는 명시적 FK로 물고 감(고객당 서비스가 여러 개로 늘어나도 안전).

- [2026_08_11_service_apply_renew.sql](app/sql/2026_08_11_service_apply_renew.sql) 신규. **운영 DB에 수동 반영 필요**
- [ServiceApply_Entity.php](app/Entity/ServiceApply_Entity.php), [ServiceApplyModel::sql_save()](app/Models/ServiceApplyModel.php) — 두 필드 추가(기본값 apply_type='NEW')
- [ServiceApplyService::approve()](app/Service/ServiceApplyService.php) — `apply_type`으로 분기:
  - NEW: 기존 그대로(서버배정 + service_live 생성 + `queue.action='new'`)
  - RENEW: 새 서버/새 service_live 없이 `renew_live_no`가 가리키는 기존 service_live 1건의 만료일만 연장 — **#8/#3에서 만든 `applyExpireDate()`를 그대로 재사용**해서 날짜기준 계산·3단계 상태 자동판정(정상/유예/종료)·`queue.action='extend'` 적재까지 상세화면의 "빠른 연장"과 정책이 항상 일치하도록 함(신규 `approveRenew()` 메서드)
- **버그 예방**: RENEW로 승인된 건은 `service_live.apply_no`가 이 apply_no를 가리키지 않아(원래 신청 건의 apply_no를 그대로 유지) `requeue()`/목록의 "큐 적재"·"정지"·"재개" 버튼이 그대로 걸리면 엉뚱하게 새 서버를 배정해버리는 사고가 날 수 있었음 → `requeue()`에 RENEW 조기 차단 추가, [Service/list.php](app/Views/Admin/Service/list.php) 액션 버튼도 신규(NEW) 건에만 노출되도록 가드 + "유형" 배지(신규/연장) 컬럼 추가
- 참고: 연장 신청을 실제로 "제출"하는 화면(마이페이지 `/mypage/extend`)은 아직 준비중 placeholder 그대로임(#11 실결제 연동 대기) — 이번엔 승인 처리 로직/스키마만 준비됨. 나중에 신청 폼을 만들 때 `apply_type='RENEW'`+`renew_live_no`를 채워서 `apply()`처럼 저장하는 새 서비스 메서드가 필요함

### #19 (2026-08-11) — 상품에 정가(list_price) 추가, 할인 강조 표시

- [2026_08_11_product_list_price.sql](app/sql/2026_08_11_product_list_price.sql) 신규 — `product.list_price INT UNSIGNED NULL`(할인 없으면 NULL). **운영 DB에 수동 반영 필요**
- [Product_Entity.php](app/Entity/Product_Entity.php), [ProductModel.php](app/Models/ProductModel.php), [ProductService.php](app/Service/ProductService.php) — `list_price` 필드 추가, "정가는 판매가보다 낮을 수 없다" 검증 추가
- Admin 상품 등록/수정화면 — "정가(원)" 입력란 추가(비워두면 할인 미표시), 목록에 취소선 정가+판매가 표시
- www 요금카드([unit/product.php](app/Views/unit/product.php)) — `list_price > price`면 취소선 정가 + 강조(featured) 스타일로 표시(과거 하드코딩 카드에 있던 `pricing-card__price-original` 클래스 재사용)
- 참고: 상세 신청 확인화면(`service_apply.php`의 `product-locked` 영역)은 이번 범위에서 정가 표시 안 붙임 — 필요하면 추가 요청

### #18 (2026-08-11) — 상품 entity 재구성: product_name에서 기간 분리 + product_type 신설

상품명에 기간을 끼워넣던 방식("1개월 이용권" 등)이 향후 부가서비스(디자인 템플릿 적용 등)와 상충한다는 문제 제기 → product_name은 순수 명칭만 담고, 기간은 기존 컬럼에서 계산한 배지로 별도 표시, 부가서비스 구분은 신규 컬럼으로 분리.

- [2026_08_11_product_type.sql](app/sql/2026_08_11_product_type.sql) 신규 — `product.product_type VARCHAR(10) DEFAULT 'BASE'`만 추가(스키마 변경 최소화). **운영 DB에 수동 반영 필요**
- [render_helper.php::product_period_label()](app/Helpers/render_helper.php) 신규 — 새 컬럼 없이 `billing_type`+`period_day`에서 "1개월/6개월/12개월/구독형·자동연장" 라벨 계산. Admin 상품목록·수정화면, www 요금카드 양쪽에서 공용으로 사용
- [Product_Entity.php](app/Entity/Product_Entity.php), [ProductModel.php](app/Models/ProductModel.php), [ProductService.php](app/Service/ProductService.php) — `product_type`(BASE/기본상품 · ADDON/부가서비스) 필드 추가, 검증 추가
- [ProductModel::get_usable_base_list()](app/Models/ProductModel.php) 신규 — www 요금 그리드는 `product_type='BASE'`만 노출(부가서비스는 아직 단독 신청 그리드에 안 섞임, 향후 "기본상품 신청 후 추가" 흐름으로 갈 예정이라 이번엔 데이터 모델만 준비)
- [Controllers/Home.php](app/Controllers/Home.php), [Controllers/ServiceApply.php](app/Controllers/ServiceApply.php) — `get_usable_list()` → `get_usable_base_list()` 교체
- [Views/Admin/Product/list.php](app/Views/Admin/Product/list.php)/[write.php](app/Views/Admin/Product/write.php)/[modify.php](app/Views/Admin/Product/modify.php) — 상품유형 select 추가, 상품명 입력란에 "기간은 상품명에 넣지 마세요" 안내 + 현재 기간 라벨 표시
- [Views/unit/product.php](app/Views/unit/product.php) — www 카드의 상품명 옆에 기간 배지 추가
- 참고: 부가서비스를 실제로 판매하는 흐름(장바구니, 기본상품+부가서비스 묶음결제 등)은 이번 범위 밖 — 데이터 모델만 준비됨

### #1 (2026-08-10) — 마이페이지에서 필수 서류(사업자등록증/중개사무소등록증) 제출

기존에 이미 `CustomerService`/`CustomerModel`/`Customer_Entity`에 등록번호·검증상태·파일 필드(`biz_reg_no`, `broker_reg_no`, `*_verified_yn`, `*_file_dir`, `*_file_name`)와 드래그드랍 temp 업로드 로직(`uploadTemp()`, `finalizeCertificateFiles()`)이 다 있었음 — Admin 고객수정 화면에서만 쓰이고 있었고, www(고객 본인) 쪽엔 입력받는 화면이 없었던 게 갭이었음. 그 기존 인프라를 마이페이지에 그대로 연결.

- **파일 저장 경로 변경**: `CustomerService::applyCertificateFile()`의 `$toDir`를 `customer/{customer_no}/{type}_reg/`에서 **`{customer_no}/`**로 변경(2026-08-10 확정) — 결과적으로 `public/pds/{customer_no}/`에 저장됨. 파일명이 md5 해시라 사업자/중개소 두 파일이 같은 폴더에 섞여도 충돌 없음. 이 변경은 기존 Admin 고객수정 업로드에도 동일하게 적용됨(같은 메서드 공유)
- [CustomerService::saveDocuments()](app/Service/CustomerService.php) 신규 — 담당자명/이메일은 안 건드리고 등록번호+서류만 처리. 등록번호나 파일이 바뀌면 기존 검증상태를 무조건 '대기(N)'로 리셋(서류가 바뀐 이상 관리자 재확인 필요)
- [Controllers/MyPage.php](app/Controllers/MyPage.php) — `postDocuments()`(제출), `postUploadTemp()`(드롭존 temp 업로드, `Signup::postuploadtemp()`와 동일 패턴)
- [Views/mypage.php](app/Views/mypage.php) — Admin 고객수정 화면의 dropzone 마크업을 그대로 재사용(공용 `common/upload.js`), 검증상태(대기/보류/검증완료/실패) 표시
- [Routes.php](app/Config/Routes.php) — `POST mypage/documents`, `POST mypage/uploadTemp` 추가
- **삭제 방지(2026-08-10)**: 검증완료(Y)된 서류는 마이페이지에서 삭제 불가 — [mypage.php](app/Views/mypage.php)에서 검증완료 시 "삭제" 버튼 자체를 안 보여주고, [CustomerService::saveDocuments()](app/Service/CustomerService.php)가 요청 조작으로 `del_img`가 넘어와도 서버에서 거부(재제출/교체 업로드는 계속 허용)
- **버그 수정(2026-08-10)**: `saveDocuments()`가 재제출 때마다 `verified_yn`을 무조건 'N'으로 리셋해서, Admin이 검증완료(Y) 처리해둬도 고객이 마이페이지를 다시 열기만 하면(내용 변경 없이) 조용히 초기화되던 문제. 등록번호/파일이 **실제로 바뀐 항목만** 리셋하도록 수정 — Admin 쪽 검증여부 변경 UI/로직(`Customer/modify.php`, `CustomerService::edit()`) 자체는 원래부터 손댄 적 없음
- **미완성 기능 연결(2026-08-10)**: `ad.bomul.ai.kr/Customer/view/{no}`(고객 상세, 읽기전용)에는 검증상태를 바꿀 방법이 아예 없었음 — `CustomerService::verify()`는 이미 구현돼 있었지만(코멘트: "관리자가 서류 확인 후 검증 처리만 빠르게 할 때 사용") 어떤 라우트/화면에도 연결된 적이 없던 죽은 코드였음. `Customer::postVerify()` 신규 + `Customer/view.php`에 검증상태 변경 select+저장 폼 추가로 연결 완료 ([Controllers/Admin/Customer.php](app/Controllers/Admin/Customer.php), [Views/Admin/Customer/view.php](app/Views/Admin/Customer/view.php), [Routes.php](app/Config/Routes.php) `POST Customer/verify/(:num)`)

### #16 축약형 마이페이지 (2026-08-10) — 번호 중복 주의: 아래 "#16 서비스 신청 상세" 항목과는 다른 건

www 로그인 고객 전용 `/mypage` 신설 — 내 서비스 현황(상품/도메인/이용상태/만료일) + 내 결제 리스트(전체, 최신순) 표시. "상품/서비스는 추후 여러 개로 늘어날 예정"이라 지금은 고객당 가장 최근 `service_live` 1건만 "현재 서비스"로 취급(다중 서비스 지원은 별도 과제).

- [PaymentModel.php](app/Models/PaymentModel.php) 신규 — 이 저장소에 없던 첫 Payment 모델. `get_list_by_customer()`로 결제 이력 전체 조회
- [ServiceLiveModel::getCurrentByCustomerNo()](app/Models/ServiceLiveModel.php) 신규
- [MyPageService.php](app/Service/MyPageService.php) 신규
- [Controllers/MyPage.php](app/Controllers/MyPage.php) 신규 — `requireLogin()`은 `ServiceApply.php`와 동일 패턴(복붙, 공통화는 안 함 — 기존 관례 따름)
- [Views/mypage.php](app/Views/mypage.php) 신규 — SUSPENDED(유예) 상태면 완전종료 예정일(suspended_at+15일)도 같이 안내
- "서비스 연장 신청"/"정기결제 신청하기" 버튼(`/mypage/extend`, `/mypage/subscribe`) — **실제 로직 없음, 준비중 안내 후 마이페이지로 리다이렉트만 함** (#11 실결제 연동, #17 서비스 연장 완료 후 채울 자리)
- [header_menu.php](app/Views/common/header_menu.php) — 로그인 상태 헤더/모바일 메뉴에 "마이페이지" 링크 추가

### #17 (2026-08-10) — 서비스 신청 현황 리스트에 "세팅현황" 컬럼 추가

- [ServiceApplyModel::get_list_with_details()](app/Models/ServiceApplyModel.php) — `#16`과 동일 기준(action='new' 최신 큐)으로 `queue_status` 조인 추가
- [Views/Admin/Service/list.php](app/Views/Admin/Service/list.php) — "신청상태" 컬럼 옆에 "세팅현황" 컬럼 신설, 대기/세팅중/완료/실패 배지 표시(큐 없으면 '-')

### #16 (2026-08-10) — 서비스 신청 상세에 세팅 진행 현황 표시

신청상태가 '승인완료(APPROVED)'인 건은 상세화면 하단에 큐(action='new') 상태 기준 3단계 스텝퍼를 표시: 대기(PENDING) › 세팅중(SENT) › 완료(DONE)｜실패(FAILED).

- [ServiceApplyModel::get_detail_by_apply_no()](app/Models/ServiceApplyModel.php) — `service_live` + 해당 live의 **최초 세팅용 큐(action='new')만** 최신 1건 조인(연장/상태변경 같은 이후 큐는 제외해야 "세팅 진행"과 안 섞임)
- [Views/Admin/Service/view.php](app/Views/Admin/Service/view.php) — APPROVED일 때만 패널 노출. 큐가 아직 없으면 "큐 적재 버튼으로 재시도" 안내, 있으면 스텝퍼 + 적재일시/완료(실패)일시 + 실패사유 표시
- 큐 자체가 없는 경우(승인 직후 enqueue 실패 등)는 별도 안내 문구로 처리 — 목록의 기존 "큐 적재"(requeue) 버튼과 자연히 연결됨

### #5 추가 (2026-08-10) — tenant_count 실시간 집계로 전환

`server_list.tenant_count`가 승인 시 +1만 되고 감소 로직이 전혀 없어(큐 적재 실패/설치 실패/서비스 종료돼도 그대로 누적) 실제 배정 수와 계속 어긋나는 문제 발견 → 저장 컬럼 신뢰를 버리고 조회 시마다 `service_live`를 실시간 COUNT()하는 방식으로 전환.

- [ServerListModel.php](app/Models/ServerListModel.php) — `findLeastLoaded()`/`findByIdx()`/`get_list()` 전부 `(SELECT COUNT(*) FROM service_live WHERE server_no=... AND status != 'TERMINATED') AS tenant_count` 서브쿼리로 계산해서 반환(반환 키는 그대로 `tenant_count`라 호출부/뷰 수정 불필요). `incrementTenantCount()` 메서드 제거
- [ServiceApplyService.php](app/Service/ServiceApplyService.php) — `approve()`/`requeue()`에서 `incrementTenantCount()` 호출 제거(더 이상 필요 없음 — service_live INSERT만으로 다음 조회부터 자동 반영)
- 저장된 `server_list.tenant_count` 컬럼 자체는 스키마에 남아있지만 이제 아무 데서도 읽거나 갱신하지 않음(죽은 컬럼) — 나중에 컬럼 자체를 DROP할지는 별도 판단 필요

### #5 (2026-08-10) — 서버 리스트(server_list) 관리 CRUD 신설

- Admin 사이드메뉴 "서버관리" 그룹 신설(`/ServerList`) — `Product`/`Manager`와 동일한 단순 CRUD 패턴
- 목록/등록/수정/삭제, 필드: 서버코드/서버명/receiver URL/API 키(비워두면 자동 발급)/DB host·port/설치가능여부(Y·N)/상태(정상·중지)/메모. `tenant_count`는 승인 시 자동 증가하는 값이라 수정화면에서 읽기전용으로만 표시
- 서버코드 중복 체크(`ServerListModel::existsByCode()`), 이미 배정된 service_live가 있는 서버는 삭제 대신 '설치가능=N'으로 유도(고아 참조 방지 — [ServiceLiveModel::existsByServerNo()](app/Models/ServiceLiveModel.php) 신규)
- `ServerListModel`에 없던 `sql_save`/`sql_edit`/`sql_delete`/`get_list`/`existsByCode` 신규 추가(기존엔 배정용 조회 전용이었음)
- 신규/변경 파일: [ServerListModel.php](app/Models/ServerListModel.php), [ServerListService.php](app/Service/ServerListService.php), [Controllers/Admin/ServerList.php](app/Controllers/Admin/ServerList.php), [Views/Admin/ServerList/list.php](app/Views/Admin/ServerList/list.php), [Views/Admin/ServerList/write.php](app/Views/Admin/ServerList/write.php), [Views/Admin/ServerList/modify.php](app/Views/Admin/ServerList/modify.php), [Routes.php](app/Config/Routes.php), [admin_menu.php](app/Views/common/admin_menu.php)
- 참고: `server_list` 테이블도 product처럼 CREATE 문이 이 저장소에 없어(`ServerList_Entity` 주석은 `2026_08_05_server_list_table.sql`을 언급하나 실제 파일은 부재) Entity 필드를 근거로 구현함 — product 때처럼 실제 DDL을 확인해주시면 타입 불일치 여부 검증 가능

### #13 (2026-08-10) — www 요금카드를 product 테이블 DB 연동으로 교체

- [unit/product.php](app/Views/unit/product.php) — 하드코딩된 카드 3장(1개월/2개월/정기구독) 제거, `$products`를 `foreach`로 순회하며 렌더링. 가격 0원→'무료', `billing_type='MONTHLY'`면 '원 / 월' 접미사, `is_event=1`이면 '이벤트' 뱃지+featured 스타일. 카드 링크는 `product_code` 기준(`?product={code}`)
- [ProductService.php](app/Service/ProductService.php) — `get_usable_list()`(www 노출용, STOP 제외) 신규, `resolve($productParam)` 신규(숫자면 product_no, 문자면 product_code로 통합 조회) — **[ServiceApply.php](app/Controllers/ServiceApply.php)의 `getindex()`가 이미 `resolve()`를 호출하고 있었는데 정의가 없어 `?product=1month`류 접근 시 Fatal error가 나던 버그를 같이 수정함**
- [Controllers/Home.php](app/Controllers/Home.php) — `getindex()`에서 `$data['products']` 전달(index.php 요금안내 섹션용)
- [Controllers/ServiceApply.php](app/Controllers/ServiceApply.php) — `getindex()`/`showError()` 둘 다 `$data['products']` 전달(상품 미지정 시 카드 그리드 렌더링용)
- [index.php](app/Views/index.php), [service_apply.php](app/Views/service_apply.php) — `unit/product` 호출 시 `products` 파라미터 추가 전달
- 참고: `description` 컬럼이 varchar(255) 단일 텍스트라 기존 카드의 기능 불릿 목록(`<ul><li>...`)은 재현 못 함 — 상품 설명 한 줄로 대체. 여러 줄 소개가 필요하면 상품관리(#12) 쪽에 별도 필드 추가 검토

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
