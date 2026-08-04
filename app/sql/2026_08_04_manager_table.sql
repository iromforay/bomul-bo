-- ad.bomul.ai.kr 백오피스 로그인 계정 테이블
-- 보이는 매물 서비스의 실사용자(중개사)와 겹치지 않도록 manager로 분리

CREATE TABLE IF NOT EXISTS `manager` (
    `manager_no`   INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `manager_id`   VARCHAR(50)  NOT NULL COMMENT '로그인 아이디',
    `manager_pw`   CHAR(128)    NOT NULL COMMENT 'SHA-512 해시(hex)',
    `manager_name` VARCHAR(50)  NOT NULL COMMENT '이름',
    `use_yn`       CHAR(1)      NOT NULL DEFAULT 'Y' COMMENT '사용여부 Y/N',
    `reg_date`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`manager_no`),
    UNIQUE KEY `uk_manager_id` (`manager_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 최초 관리자 계정 시드 (아이디: admin / 비밀번호: foray1!)
-- 아래 해시는 이 비밀번호의 SHA-512(hex) 값입니다. 운영 반영 후에는 비밀번호를 바꾸는 걸 권장합니다.
INSERT INTO `manager` (`manager_id`, `manager_pw`, `manager_name`, `use_yn`)
VALUES ('admin', '13bf8529fcb28256f01131cecac11d63d4471119fb9e11fd5bdac6209fb18ee09bfadc879728692c6c455066d929bfd5e3be9aa5d6e3aa5fb7e6a095bca33248', '관리자', 'Y');
