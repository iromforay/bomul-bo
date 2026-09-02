<?php

namespace App\Service;

// 매물보 신규 등록 알림 배치(#38) - 회원가입/서비스신청/Q&A 등록을 텔레그램으로 알린다.
// 조회 구간([$from, $to])은 호출자(app/batch/telegram_notify.php)가 정한다 - 09시 정각엔 전날
// 18시~당일 9시, 그 외 09~18시엔 직전 1분치. 3종을 각각 조회하되, 발송은 한 메시지로 묶어서
// 한 번만 하고(텔레그램 도배 방지), 셋 다 없으면 아예 발송을 스킵한다.
class TelegramNotifyService extends CI_Service
{
    private $customerModel;
    private $serviceApplyModel;
    private $boardModel;
    private $telegram;

    public function __construct()
    {
        parent::__construct();
        $this->customerModel     = model('App\Models\CustomerModel', true, $this->db);
        $this->serviceApplyModel = model('App\Models\ServiceApplyModel', true, $this->db);
        $this->boardModel        = model('App\Models\BoardModel', true, $this->db);
        $this->telegram          = new TelegramService();
    }

    public function runAll(string $from, string $to): void
    {
        $sections = array_filter([
            $this->buildSignupSection($from, $to),
            $this->buildServiceApplySection($from, $to),
            $this->buildQnaSection($from, $to),
        ]);

        if (empty($sections)) {
            return;
        }

        $message = implode("\n\n", $sections);
        if (!$this->telegram->send($message)) {
            log_message('error', "[telegram_notify] 전송 실패: {$message}");
        }
    }

    private function buildSignupSection(string $from, string $to): ?string
    {
        $rows = $this->customerModel->get_registered_between($from, $to);
        if (empty($rows)) {
            return null;
        }

        $lines = ["[신규 가입] " . count($rows) . "건"];
        foreach ($rows as $row) {
            $lines[] = "- {$row['company_name']} / {$row['phone']} / {$row['customer_id']} ({$row['reg_date']})";
        }
        return implode("\n", $lines);
    }

    private function buildServiceApplySection(string $from, string $to): ?string
    {
        $rows = $this->serviceApplyModel->get_applied_between($from, $to);
        if (empty($rows)) {
            return null;
        }

        $lines = ["[서비스 신청] " . count($rows) . "건"];
        foreach ($rows as $row) {
            $lines[] = "- {$row['company_name']}({$row['ceo_name']}) / {$row['manager_phone']} ({$row['created_at']})";
        }
        return implode("\n", $lines);
    }

    private function buildQnaSection(string $from, string $to): ?string
    {
        $rows = $this->boardModel->get_registered_between('QNA', $from, $to);
        if (empty($rows)) {
            return null;
        }

        $lines = ["[Q&A 등록] " . count($rows) . "건"];
        foreach ($rows as $row) {
            $writer = $row['writer_name'] ?: '회원';
            $lines[] = "- {$writer}: {$row['title']} ({$row['created_at']})";
        }
        return implode("\n", $lines);
    }
}
