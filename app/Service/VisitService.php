<?php

namespace App\Service;

use App\Entity\VisitConversion_Entity;
use App\Entity\VisitLog_Entity;
use App\Entity\VisitStatDaily_Entity;

// #36 방문자 집계(검색어)/가입 전환 - app/sql/2026_08_27_visit_log.sql 세 테이블을 다룬다.
// 쿠키(vtok/vses) 읽기·쓰기는 Controller 책임(Response 객체가 있어야 함) - 여기는 순수 로직 + DB 저장만.
class VisitService extends CI_Service
{
    public const COOKIE_TOKEN = 'vtok'; // 방문자 식별 쿠키명 - 장기 보관(1년), 익명 랜덤값
    public const COOKIE_SESSION = 'vses'; // 세션(브라우저 탭) 내 첫 페이지뷰 판별용 쿠키명 - 만료시각 없음(세션쿠키)

    // 검색엔진 리퍼러 호스트 => 검색어가 담긴 쿼리파라미터명. SEO 유입의 핵심이 네이버라 최우선 포함.
    private const SEARCH_ENGINES = [
        'search.naver.com'   => 'query',
        'm.search.naver.com' => 'query',
        'www.google.com'     => 'q',
        'google.co.kr'       => 'q',
        'www.google.co.kr'   => 'q',
        'search.daum.net'    => 'q',
    ];

    private $logModel;
    private $conversionModel;
    private $statModel;

    public function __construct()
    {
        parent::__construct();
        $this->logModel = model('App\Models\VisitLogModel', true, $this->db);
        $this->conversionModel = model('App\Models\VisitConversionModel', true, $this->db);
        $this->statModel = model('App\Models\VisitStatDailyModel', true, $this->db);
    }

    // 방문자 식별 토큰 신규 발급 - 개인정보 아님(서버가 만든 랜덤값일 뿐 어떤 개인정보와도 매핑 안 됨)
    public function generateToken(): string
    {
        return bin2hex(random_bytes(16));
    }

    // 검색엔진 크롤러/봇 UA는 방문 집계에서 제외(사람 트래픽만 카운트)
    public function isBot(?string $userAgent): bool
    {
        if (empty($userAgent)) {
            return true; // UA 자체가 없는 요청도 정상 브라우저로 보기 어려움
        }
        return (bool) preg_match('/bot|crawler|spider|slurp|bingpreview|facebookexternalhit/i', $userAgent);
    }

    public function detectDeviceType(?string $userAgent): string
    {
        if ($userAgent && preg_match('/Mobile|Android|iPhone|iPad/i', $userAgent)) {
            return 'MOBILE';
        }
        return 'PC';
    }

    // 리퍼러 URL에서 호스트/검색어를 뽑는다. 호스트는 항상 반환(집계용), 검색어는 검색엔진 리퍼러이고
    // 실제로 쿼리파라미터가 실려온 경우에만 채워진다(구글은 대부분 비어있음 - 자체 정책으로 제거).
    public function parseReferrer(?string $referrerUrl): array
    {
        $host = '';
        $keyword = '';

        if (!empty($referrerUrl)) {
            $parts = parse_url($referrerUrl);
            $host = strtolower($parts['host'] ?? '');

            if ($host !== '' && isset(self::SEARCH_ENGINES[$host]) && !empty($parts['query'])) {
                parse_str($parts['query'], $query);
                $param = self::SEARCH_ENGINES[$host];
                $keyword = trim((string) ($query[$param] ?? ''));
            }
        }

        return ['host' => $host, 'keyword' => $keyword];
    }

    // 현재 페이지 URL의 쿼리스트링에서 utm_* 추출 - 마케팅 링크 클릭 유입 식별용
    public function parseUtm(?string $locationSearch): array
    {
        $result = ['source' => '', 'medium' => '', 'campaign' => ''];
        if (empty($locationSearch)) {
            return $result;
        }

        parse_str(ltrim($locationSearch, '?'), $query);
        $result['source'] = trim((string) ($query['utm_source'] ?? ''));
        $result['medium'] = trim((string) ($query['utm_medium'] ?? ''));
        $result['campaign'] = trim((string) ($query['utm_campaign'] ?? ''));

        return $result;
    }

    // 페이지뷰 1건 저장. $isLanding은 Controller가 vses 쿠키 존재 여부로 판단해서 넘겨준다.
    public function collectPageView(string $visitorToken, bool $isLanding, array $input): void
    {
        $referrer = $this->parseReferrer($input['referrer'] ?? null);
        $utm = $this->parseUtm($input['location_search'] ?? null);

        $entity = new VisitLog_Entity();
        $entity->setVisitorToken($visitorToken);
        $entity->setPagePath((string) ($input['page_path'] ?? '/'));
        $entity->setReferrerHost($referrer['host']);
        $entity->setSearchKeyword($referrer['keyword']);
        $entity->setUtmSource($utm['source']);
        $entity->setUtmMedium($utm['medium']);
        $entity->setUtmCampaign($utm['campaign']);
        $entity->setDeviceType($this->detectDeviceType($input['user_agent'] ?? null));
        $entity->setIsLanding($isLanding ? 'Y' : 'N');

        $this->logModel->sql_save($entity);
    }

    // 가입 완료 시점 호출 - 가입자가 vtok 쿠키를 안 갖고 있으면(직접 URL 입력 등) 매핑할 게 없어 스킵.
    public function recordConversion(?string $visitorToken, int $customerNo): void
    {
        if (empty($visitorToken) || $customerNo <= 0) {
            return;
        }

        $entity = new VisitConversion_Entity();
        $entity->setVisitorToken($visitorToken);
        $entity->setCustomerNo($customerNo);

        $this->conversionModel->sql_save($entity);
    }

    // 배치(visit_stat_aggregate.php) 전용 - $stat_date(YYYY-MM-DD) 하루치를 검색어/유입경로별로
    // 집계해서 visit_stat_daily에 UPSERT. 재실행해도 안전(멱등) - 같은 날짜를 다시 돌리면 값만 덮어씀.
    // 반환값: [처리한 그룹 수, 실패한 그룹 수] - 배치가 로그 남길 때 사용.
    public function aggregateDaily(string $stat_date): array
    {
        $rows = $this->logModel->get_daily_keyword_stats($stat_date);

        $done = 0;
        $failed = 0;
        foreach ($rows as $row) {
            $entity = new VisitStatDaily_Entity();
            $entity->setStatDate($stat_date);
            $entity->setSearchKeyword($row['search_keyword']);
            $entity->setReferrerHost($row['referrer_host']);
            $entity->setVisitCount($row['visit_count']);
            $entity->setSignupCount($row['signup_count']);

            $this->statModel->sql_upsert($entity) ? $done++ : $failed++;
        }

        return [$done, $failed];
    }
}
