CREATE TABLE `product` (
    `product_idx` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '상품 IDX',

    `product_code` VARCHAR(30) NOT NULL COMMENT '상품코드',
    `product_name` VARCHAR(100) NOT NULL COMMENT '상품명',

    `price` INT UNSIGNED NOT NULL COMMENT '판매금액',

    `billing_type` VARCHAR(20) NOT NULL COMMENT '결제방식(MONTHLY:월구독,  ONCE:1회결제, NONE:미결제[이벤트/무통장])',

    `period_day` SMALLINT UNSIGNED NOT NULL  COMMENT '사용기간(일-30)',
    `description` VARCHAR(255) DEFAULT NULL COMMENT '설명',

    `status` VARCHAR(10) NOT NULL DEFAULT 'USE' COMMENT '상태(USE:사용, STOP:중지)',
    `is_event` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '프로모션상품(1:프로모션, 0: 일반상품)',

    `sort` SMALLINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '정렬',

    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`product_idx`),

    UNIQUE KEY `uk_product_code` (`product_code`),
    KEY `idx_status` (`status`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COMMENT='서비스 상품';


INSERT INTO product
(product_name, product_code, price, billing_type, period_day, description, is_event)
VALUES
    ('구독형', 'billing', 220000, 'MONTHLY', 30, '월구독형 - 2개월 무료', 0),
    ('1개월', '1month', 220000, 'ONCE', 30, '1개월 이용권', 0),
    ('3개월', '3month', 630000, 'ONCE', 90, '월 210,000원', 0),
    ('2개월', '2month', 0, 'NONE', 60, '이벤트 상품', 1);

/*
1개월 이용권: 220,000원 (진입장벽 최소화)
3개월 이용권: 630,000원 (약간의 할인으로 단기 선택지 제공)
2개월 무료 체험 : 0원 (⭐ 이벤트)
월 구독: 220,000원/월 + 첫 2개월 무료는 신규 가입 즉시보다 1개월 이용권 종료 직전의 업셀링 혜택으로 제시
*/
