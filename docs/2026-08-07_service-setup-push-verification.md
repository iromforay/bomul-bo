# 서비스 신청 → 세팅 Push/Receiver 파이프라인 점검 (2026-08-07)

이 문서는 `bomul-bo`(서비스 서버)와 `bomul-api`(운영서버) 사이의 "서비스 신청 승인 → 테넌트 자동 설치" 파이프라인을 처음부터 끝까지 점검하고 실제로 동작 확인한 세션 기록이다. 시간이 지나 기억이 흐려졌을 때 이 문서만 보고 전체 구조를 복원할 수 있도록 정리한다.

## 1. 전체 아키텍처 (최종 검증된 흐름)

```
[bomul-bo] 고객이 서비스 신청 (service_apply, status=REQUEST)
    │
    ▼ 관리자 승인 (ServiceApplyService::approve())
[bomul-bo] service_apply.status=APPROVED
           service_live 생성 (install_status=PENDING, fid=희망도메인)
           queue 생성 (status=PENDING)
    │
    ▼ crontab 매분 (bomul-bo): php spark queue:push
[bomul-bo] QueuePush 커맨드 → queue의 PENDING 조회
           → server_list.receiver_url로 curl POST (fire-and-forget, 300/500ms 타임아웃)
           → queue.status=SENT (전송 성공 여부와 무관하게 무조건 SENT로 마킹됨 - 주의!)
    │
    ▼ HTTPS
[bomul-api] nginx → Routes.php: host !== 'api.bomul.ai.kr'일 때만 /receiver 등록
           → Receiver::postIndex() (Web 네임스페이스 아님, SHOP_CODE 로직 안 탐, db_connect('default') 고정)
           → setting_queue에 INSERT (status=PENDING)
    │
    ▼ crontab 매분 (bomul-api): php app/batch/batch.php → setting_callback.php (시간대 조건 없음, 매분)
[bomul-api] setting_queue PENDING 조회
           → TenantProvisioner::provision(fid, payload)
               - CREATE DATABASE {fid}
               - tenant_schema_template.sql 실행 (구조)
               - tenant_default_data.sql 실행 (menu/function_menu/basic 기본데이터)
               - basic 테이블 UPDATE (상호명/대표자/등록번호)
               - 업로드 디렉토리 생성
           → setting_queue.status = DONE/FAILED
    │
    ▼ 같은 setting_callback.php 안에서 이어서 콜백 전송
[bomul-api] status IN (DONE,FAILED) AND callback_sent_at IS NULL 조회
           → curl POST https://ad.bomul.ai.kr/Callback/complete
             (server_code, queue_no, status, error_message / Bearer RECEIVER_API_KEY)
           → 200 응답이면 setting_queue.callback_sent_at 채움
    │
    ▼
[bomul-bo] Callback::postComplete() (skipAuth, api_key로 인증)
           → queue.status = DONE/FAILED
           → service_live.install_status = DONE/FAILED
```

**최종 확인 완료** (2026-08-07): `queue.status = DONE`, `service_live.install_status = DONE` 까지 실제 데이터(apply_no=1, fid=demo)로 엔드투엔드 검증됨.

## 2. 이번 세션에서 발견하고 고친 문제들

### 2-1. 코드 (bomul-bo)

| 문제 | 조치 |
|---|---|
| `customer.fid`와 `service_apply.sub_domain`이 같은 값을 중복 저장(고객이 신청서를 여러 번 내면 항상 같은 값 복사) - 무결성 설계 결함 | `customer.fid` 완전 제거. `service_apply.sub_domain`을 유일 키로 통일. 신청 단계에서 직접 입력받고, `ServiceApplyModel::existsBySubDomain()`으로 중복 체크 (REQUEST/REVIEW/APPROVED만 점유로 간주, REJECTED/CANCEL은 재사용 허용) |
| `approve()`가 `service_live`/`queue` 생성 실패를 검사 안 하고 무조건 성공 메시지를 반환 | `createLive()`/`enqueue()` 반환값 체크, 실패 시 명확한 실패 메시지 반환 |
| `service_live`가 이미 있는데 `approve()`를 다시 호출하면 Duplicate entry로 죽는 문제 | `createLive()`가 기존 `service_live`를 먼저 조회해서 있으면 재사용(재생성 안 함) |
| APPROVED인데 `service_live`/`queue`가 안 만들어진 건을 복구할 방법이 없음 | `ServiceApplyService::requeue()` 추가 + Admin `/Service` 목록에 "큐 적재" 버튼 |
| `QueuePush::push()`가 curl 결과(에러/HTTP코드)를 전혀 확인 안 해서 실패해도 `SENT`로 마킹됨 | 진단용으로 curl 재현 커맨드 + errno/HTTP코드/응답을 화면·로그에 출력하도록 추가 (근본 수정은 아님 - 여전히 실패해도 SENT로 마킹되는 fire-and-forget 설계는 유지) |

### 2-2. 인프라 (실서버, 코드 문제 아님)

| # | 문제 | 조치 |
|---|---|---|
| 1 | `bomul-bo` crontab이 `/path/to/bomul-bo/spark`라는 **예시 경로 그대로** 등록되어 있어서 매분 "파일 없음"으로 조용히 실패 - push가 한 번도 실행된 적 없었음 | 실제 배포 경로(`/home/bomul/git/bomul-bo/spark`)로 수정 |
| 2 | `bomul-api` nginx가 `demo1.bomul.ai.kr` 같은 미등록 서브도메인 요청에 **`foray.bomul.ai.kr` 전용 인증서**를 잘못 물림 (`*.bomul.ai.kr` server_name이 여러 conf 파일에 중복 선언되어 있어 nginx가 먼저 매칭된 것만 채택, 나머지 무시) | 캐치올 서버 블록의 인증서를 와일드카드(`bomul.ai.kr`, SAN에 `*.bomul.ai.kr` 포함)로 교체 |
| 3 | `bomul-api` `.env`에 `SERVER_CODE`, `CENTRAL_CALLBACK_URL`이 아예 없었음 (`RECEIVER_API_KEY`만 있었음) → 설치는 성공해도 콜백이 빈 URL로 나가 실패 | `.env`에 `SERVER_CODE="demo1"`, `CENTRAL_CALLBACK_URL="https://ad.bomul.ai.kr/Callback/complete"` 추가 |
| 4 | `tenant_schema_template.sql`, `tenant_default_data.sql`이 로컬 저장소에는 있지만 **git에 커밋된 적이 없어서** 서버에 없었음 → `TenantProvisioner`가 `file_get_contents()` 실패로 크래시 | 두 파일 git add/commit/push 후 서버에서 pull |
| 5 | `tenant_default_data.sql`의 블록 주석(`/* ... */`, 비활성화해둔 add_menu INSERT) 안에 세미콜론이 들어있어서, `TenantProvisioner::runSqlFile()`의 단순 `;\n` 기준 문장 분리 로직이 주석 중간에서 잘못 잘림 → `*/`로 시작하는 깨진 SQL이 실행되어 SQL 문법 에러로 배치 전체 크래시 (menu만 들어가고 function_menu/basic부터 안 들어감) | 파일의 문제 주석을 라인 주석(`--`)으로 변경 + `runSqlFile()`이 분리 전에 블록/라인 주석을 전부 제거하도록 수정 + PHP 8.1+ mysqli 예외모드 대응으로 `try/catch` 추가 (한 문장 실패로 전체 시드데이터가 안 들어가는 것 방지) |

## 3. 테이블 정의 현황 — **스케일아웃 전 반드시 정리 필요**

### 핵심 문제
`service_apply`, `service_live`, `server_list`, `queue`, `setting_queue` 전부 **실제 DB에는 존재하지만 이 두 저장소 어디에도 CREATE TABLE이 커밋되어 있지 않다.** 지금까지 스키마가 코드로 추적이 안 되고 있어서, 이번 세션에서 여러 번 "실제 DB 구조를 코드로 확인할 수 없어서 에러 메시지로 추측해야 하는" 상황이 반복됐다. 서버가 여러 대로 늘어나기 전에(스케일아웃 전에) 아래 테이블들의 CREATE TABLE을 마이그레이션 파일이나 `app/sql/*.sql`로 반드시 커밋해둬야 한다.

### bomul-bo(서비스 서버)가 소유해야 하는 테이블 — 중앙 `bomul` DB

| 테이블 | 역할 | 비고 |
|---|---|---|
| `customer` | 고객(중개사) 계정 | `fid` 컬럼은 이번에 제거함 - **실제 DB에서도 DROP COLUMN 필요** (아직 수동 작업 남음) |
| `product` | 상품 | |
| `payment` | 결제 이력 | |
| `service_apply` | 서비스 신청서 (스냅샷) | `sub_domain`이 유일 키 역할 - **DB 레벨 UNIQUE 인덱스는 못 검) - REJECTED/CANCEL 재사용 허용 정책 때문에 앱 레벨 체크로만 방어 중 |
| `service_live` | 승인된 서비스 인스턴스의 현재 상태 (유일 진실 소스) | `apply_no`당 1건 |
| `server_list` | 운영서버 목록 (`receiver_url`, `api_key`, `tenant_count`, `installable_yn`) | **`receiver_url`은 테넌트 서브도메인이 아니라 서버 자체를 가리키는 안정적인 도메인이어야 함** - 여러 서버로 늘어날 때 특히 중요 |
| `queue` | push 대기열 (PENDING→SENT→DONE/FAILED) | |
| `manager` | 관리자(bo 로그인) 계정 | LOGIN 별도 DB 쓰는지 확인 필요 (`.env`에 `database.login.*` 있음) |

### bomul-api(운영서버)가 소유해야 하는 테이블 — 중앙 `bomul` DB (서버 1대당 1세트)

| 테이블 | 역할 | 비고 |
|---|---|---|
| `setting_queue` | push 수신 후 실제 설치 처리 대기열 (PENDING→DONE/FAILED, callback_sent_at) | `live_setting_queue_table.sql`로 존재는 하나 커밋 상태 재확인 필요 |
| (테넌트별 DB, `{fid}` 이름) | 상점마다 새로 생성되는 개별 DB. `menu`/`function_menu`/`basic`/`property` 등 | `tenant_schema_template.sql` + `tenant_default_data.sql`로 이번에 커밋함 |

### 스케일아웃 시 고려할 것
- `server_list`가 여러 행이 될 때 `receiver_url`이 각 서버별로 안정적으로 구분되는 고정 도메인인지 (테넌트 서브도메인에 의존하면 안 됨 - 이번 세션에서 실제로 SSL 문제로 발현됨)
- `findLeastLoaded()`가 여러 서버 중 `tenant_count` 최소인 곳을 고르는 로직이므로, 서버 추가 시 `server_list`에 행만 추가하면 자동으로 로드밸런싱됨 (코드 변경 불필요)
- 각 서버의 `.env`에 `SERVER_CODE`(고유해야 함)/`CENTRAL_CALLBACK_URL`/`RECEIVER_API_KEY`가 서버마다 올바르게 세팅됐는지 체크리스트화 필요

## 4. 아직 남은 작업 (커밋 안 됨, TODO)

- [ ] `bomul-bo`: 이번 세션 코드 변경사항 커밋 (`ServiceApplyService`, `ServiceApplyModel`, `ServiceLiveModel`, `ServerListModel`, `Customer_Entity`, `CustomerModel`, `CustomerService`, `CustomerLoginService`, `Controllers/ServiceApply.php`, `Controllers/Admin/Customer.php`, `Controllers/Admin/Service.php`, `Views/*`, `Config/Routes.php`, `Commands/QueuePush.php`)
- [ ] `bomul-api`: 이번 세션 변경사항 커밋 (`Controllers/Receiver.php`, `Libraries/TenantProvisioner.php`, `Models/SettingQueueModel.php`, `batch/setting_callback.php`, `batch/batch.php`, `sql/tenant_schema_template.sql`, `sql/tenant_default_data.sql`, `sql/live_setting_queue_table.sql`, `Config/Routes.php`)
- [ ] 실제 DB에서 `customer.fid` 컬럼 DROP (코드는 이미 안 씀)
- [ ] `service_apply`/`service_live`/`server_list`/`queue`/`setting_queue`의 CREATE TABLE을 `app/sql/`에 정식으로 커밋 (스케일아웃 전 필수)
- [ ] `server_list.receiver_url`을 테넌트 서브도메인이 아닌 서버 고정 도메인으로 교체 검토
