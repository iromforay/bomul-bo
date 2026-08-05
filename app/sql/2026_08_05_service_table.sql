CREATE TABLE `service_apply` (
     `apply_idx` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '서비스 신청 IDX',

     `customer_idx` INT UNSIGNED NOT NULL COMMENT '회원 IDX',
     `product_idx` INT UNSIGNED NOT NULL COMMENT '상품 IDX - 이벤트(2개월 무료 등) 상품 중복신청 체크용으로도 사용',
        -- 결제정보는 payment 테이블에서 apply_idx FK로 연결 (1:N - 월구독은 갱신마다 결제행 추가)

     `company_name` VARCHAR(100) NOT NULL COMMENT '상호명',
     `ceo_name` VARCHAR(50) NOT NULL COMMENT '대표자명',

     `license_no` VARCHAR(50) DEFAULT NULL COMMENT '공인중개사 등록번호',
     `biz_no` VARCHAR(20) DEFAULT NULL COMMENT '사업자등록번호',

     `manager_name` VARCHAR(50) DEFAULT NULL COMMENT '담당자명',
     `manager_phone` VARCHAR(20) NOT NULL COMMENT '담당자 연락처',
     `manager_email` VARCHAR(100) NOT NULL COMMENT '담당자 이메일',

     `tel` VARCHAR(20) DEFAULT NULL COMMENT '대표전화',
     `fax` VARCHAR(20) DEFAULT NULL COMMENT '팩스',

     `zipcode` VARCHAR(10) DEFAULT NULL COMMENT '우편번호',
     `addr1` VARCHAR(255) DEFAULT NULL COMMENT '기본주소',
     `addr2` VARCHAR(255) DEFAULT NULL COMMENT '상세주소',

     `homepage_name` VARCHAR(100) DEFAULT NULL COMMENT '홈페이지명',
     `sub_domain` VARCHAR(100) DEFAULT NULL COMMENT '희망 서브도메인',
     `custom_domain` VARCHAR(255) DEFAULT NULL COMMENT '사용자 도메인',

     `memo` TEXT DEFAULT NULL COMMENT '요청사항',

     `status` VARCHAR(10) NOT NULL DEFAULT 'REQUEST' COMMENT '신청상태 (REQUEST: 서비스 신청 완료,REVIEW : 관리자 검토 중,APPROVED :승인 완료,REJECTED : 승인 거절,CANCEL  : 신청 취소)',

     `request_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '신청일',
     `approve_date` DATETIME DEFAULT NULL COMMENT '승인일',
     `reject_reason` VARCHAR(255) DEFAULT NULL COMMENT '반려사유',

     `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
     `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

     PRIMARY KEY (`apply_idx`),

     KEY `idx_customer` (`customer_idx`),
     KEY `idx_product` (`product_idx`),
     KEY `idx_status` (`status`),
     -- 특정 고객이 특정 상품(특히 이벤트 상품)을 이미 신청한 적 있는지 조회할 때 쓰는 복합 인덱스
     KEY `idx_customer_product` (`customer_idx`, `product_idx`)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci
COMMENT='서비스 신청';
