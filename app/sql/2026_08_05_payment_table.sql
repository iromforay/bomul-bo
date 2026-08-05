CREATE TABLE `payment` (
    `payment_idx` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '결제 IDX',

    `apply_idx` BIGINT UNSIGNED NOT NULL COMMENT '서비스 신청 IDX (service_apply.apply_idx) - 월구독은 갱신마다 이 FK로 결제행이 추가됨',

    `pay_method` VARCHAR(20) NOT NULL COMMENT '결제수단(CARD:카드, VBANK:가상계좌, TRANSFER:계좌이체, NONE:무료/이벤트)',
    `pg_provider` VARCHAR(30) DEFAULT NULL COMMENT 'PG사',
    `pg_tid` VARCHAR(100) DEFAULT NULL COMMENT 'PG 거래번호',

    `pay_amount` INT UNSIGNED NOT NULL COMMENT '결제금액(신청 시점 실 청구금액 - product.price와 달라질 수 있어 스냅샷으로 보관)',
    `pay_status` VARCHAR(10) NOT NULL DEFAULT 'READY' COMMENT '결제상태(READY:결제대기, PAID:결제완료, FAILED:결제실패, CANCELLED:결제취소, REFUNDED:환불)',

    `paid_at` DATETIME DEFAULT NULL COMMENT '결제완료일시',
    `cancelled_at` DATETIME DEFAULT NULL COMMENT '취소/환불일시',
    `fail_reason` VARCHAR(255) DEFAULT NULL COMMENT '실패사유',

    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`payment_idx`),

    KEY `idx_apply` (`apply_idx`),
    KEY `idx_pay_status` (`pay_status`)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci
COMMENT='결제 내역 - service_apply 1건에 결제 N건(월구독 갱신 등)';
