CREATE TABLE `service_live` (
    `live_idx` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '라이브 서비스 IDX',

    `customer_idx` INT UNSIGNED NOT NULL COMMENT '회원 IDX (customer.customer_idx) - 고객당 라이브 서비스 1건 가정',
    `apply_idx` BIGINT UNSIGNED NOT NULL COMMENT '이 라이브를 만든 마지막 승인 신청 (service_apply.apply_idx)',
    `server_idx` INT UNSIGNED NOT NULL COMMENT '설치 배정된 운영서버 (server_list.server_idx)',

    `fid` VARCHAR(50) NOT NULL COMMENT '테넌트 식별자(2차 도메인)',
    `license_key` VARCHAR(100) DEFAULT NULL COMMENT '발급된 라이선스 키',

    `status` VARCHAR(10) NOT NULL DEFAULT 'ACTIVE' COMMENT '서비스 상태(ACTIVE:정상, SUSPENDED:만료유예, TERMINATED:종료)',

    `install_status` VARCHAR(10) NOT NULL DEFAULT 'PENDING' COMMENT '설치 진행상태(PENDING:설치대기, INSTALLING:설치중, DONE:설치완료, FAILED:설치실패)',
    `install_requested_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '설치 요청(=신청 승인) 시각',
    `installed_at` DATETIME DEFAULT NULL COMMENT '설치 완료 시각',
    `install_error` VARCHAR(255) DEFAULT NULL COMMENT '설치 실패 사유',

    `start_date` DATE DEFAULT NULL COMMENT '서비스 시작일(과금 시작 기준 - 설치완료일과 다를 수 있음)',
    `expire_date` DATE DEFAULT NULL COMMENT '만료일 - 갱신될 때마다 연장',

    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`live_idx`),

    UNIQUE KEY `uk_customer` (`customer_idx`),
    KEY `idx_apply` (`apply_idx`),
    -- 각 운영서버 cron이 "내 서버로 할당된 설치대기 건"을 폴링하는 패턴에 맞춘 복합 인덱스
    KEY `idx_server_install` (`server_idx`, `install_status`),
    KEY `idx_status` (`status`),
    KEY `idx_expire` (`expire_date`)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci
COMMENT='고객별 현재 라이브 서비스 현황';
