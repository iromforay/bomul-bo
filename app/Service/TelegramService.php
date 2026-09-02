<?php

namespace App\Service;

// 텔레그램 메시지 발송 - .env telegram.BOT_ID("bot123...") + telegram.Secret("AAH...:/sendMessage")를
// 이어붙이면 https://api.telegram.org/{BOT_ID}:{Secret} 형태의 sendMessage 엔드포인트가 된다
// (.env telegram.Secret 옆 주석에 적힌 예시 URL 참고). CHAT_ID는 텔레그램 그룹방 IDX.
class TelegramService
{
    private const TIMEOUT_SEC = 5;

    public function send(string $message): bool
    {
        $botId  = getenv('telegram.BOT_ID');
        $secret = getenv('telegram.Secret');
        $chatId = getenv('telegram.CHAT_ID');

        if (!$botId || !$secret || !$chatId) {
            log_message('error', '[telegram] .env telegram.BOT_ID/Secret/CHAT_ID 설정이 없습니다.');
            return false;
        }

        $url = "https://api.telegram.org/{$botId}:{$secret}?" . http_build_query([
            'chat_id' => $chatId,
            'text'    => $message,
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_HTTPGET        => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => self::TIMEOUT_SEC,
        ]);
        $response = curl_exec($ch);
        $errno    = curl_errno($ch);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($errno !== 0) {
            log_message('error', "[telegram] 전송 실패 (errno={$errno}): {$error}");
            return false;
        }

        $result = json_decode((string) $response, true);
        if (!($result['ok'] ?? false)) {
            log_message('error', "[telegram] API 응답 실패: {$response}");
            return false;
        }

        return true;
    }
}
