CREATE TABLE `server_list` (
    `server_idx` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '운영서버 IDX',

    `server_code` VARCHAR(30) NOT NULL COMMENT '서버코드(예: OPS01)',
    `server_name` VARCHAR(100) NOT NULL COMMENT '서버명',

    `db_host` VARCHAR(100) NOT NULL COMMENT '접속 호스트',
    `db_port` SMALLINT UNSIGNED NOT NULL DEFAULT 3306 COMMENT '접속 포트',

    `tenant_count` SMALLINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '현재 배정된 테넌트 수 - 신규 배정시 least-loaded 판단용',

    `installable_yn` CHAR(1) NOT NULL DEFAULT 'Y' COMMENT '신규 설치 가능 여부(Y/N) - 관리자가 켜고 끄는 토글',
    `status` VARCHAR(10) NOT NULL DEFAULT 'USE' COMMENT '서버 상태(USE:정상, STOP:점검/중지 - 기존 테넌트 포함 전체 가동 여부)',

    `memo` VARCHAR(255) DEFAULT NULL COMMENT '비고',

    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`server_idx`),

    UNIQUE KEY `uk_server_code` (`server_code`),
    KEY `idx_installable` (`installable_yn`, `status`)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci
COMMENT='매물 서비스 운영서버(테넌트 DB 호스팅) 목록';
