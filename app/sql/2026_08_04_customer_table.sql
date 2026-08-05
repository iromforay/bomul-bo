-- 고객(중개사) 가입~검증~서비스신청 단계 테이블.
-- 중앙 member(bo.bomul.ai.kr 로그인용, 별도 DB에 이미 존재)는 건드리지 않는다.
-- 결제까지 끝나 실사용자가 되면 이 테이블 정보를 member로 이관하고 migrated_yn='Y' 처리한다.
--
-- 참고: 중앙 member 실제 스키마
--   member_no, user_id, user_pwd(VARCHAR64=SHA-256 해시 추정), fid(2차도메인), service(Y/N),
--   fname, ftel, faddress1, faddress2, regist_num(단일 등록번호), mb_name, mb_id, mb_hp, mb_email
-- customer_pw를 SHA-256(64자)로 맞춘 이유: user_pwd가 VARCHAR(64)라 이관 시 해시를 그대로
-- 복사해야 하는데, 원문 비밀번호 없이는 재해시가 불가능하기 때문(SHA-512 128자는 애초에 안 들어감).
-- broker_reg_no(중개사무소등록번호)는 중앙 member에 대응 컬럼이 없다 - 이관 시 테넌트별 basic
-- 테이블(broker_reg_no 등 법정표시 필드 보유)로 시딩하는 걸로 추정, 아직 미구현.

CREATE TABLE IF NOT EXISTS `customer` (
    `customer_idx`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `customer_id`             VARCHAR(50)  NOT NULL COMMENT '로그인 아이디',
    `customer_pw`             CHAR(64)     NOT NULL COMMENT 'SHA-256 해시(hex) - 중앙 member.user_pwd 이관 호환',
    `customer_name`           VARCHAR(50)  NOT NULL COMMENT '담당자명',
    `company_name`            VARCHAR(100) NOT NULL COMMENT '중개사무소명',
    `biz_reg_no`               VARCHAR(20) NOT NULL COMMENT '사업자등록번호',
    `broker_reg_no`            VARCHAR(30) NOT NULL COMMENT '중개사무소등록번호 (공인중개사법 제18조의2)',
    `phone`                   VARCHAR(20)  NOT NULL COMMENT '휴대폰번호',
    `email`                   VARCHAR(100) NOT NULL COMMENT '이메일',
    `biz_reg_verified_yn`     CHAR(1)      NOT NULL DEFAULT 'N' COMMENT '사업자등록번호 검증상태 N=대기,Y=검증완료,W=보류,F=검증실패',
    `broker_reg_verified_yn`  CHAR(1)      NOT NULL DEFAULT 'N' COMMENT '중개사무소등록번호 검증상태 N=대기,Y=검증완료,W=보류,F=검증실패',
    `use_yn`                  CHAR(1)      NOT NULL DEFAULT 'Y' COMMENT '계정 사용여부 Y/N (사용자 계정 자체를 정지시킬 때 사용, 서비스 이용여부와 무관)',
    `service_yn`              CHAR(1)      NOT NULL DEFAULT 'N' COMMENT '서비스 사용여부 Y/N - 결제로 활성화, 만료되면 N',
    `service_expire_date`     DATE         NULL     COMMENT '서비스 만료일 - 결제(1회성/기간제/정기) 결과로 갱신',
    `biz_reg_file_dir`        VARCHAR(191) NULL     COMMENT '사업자등록증 파일 경로 (File_Upload 라이브러리 file_path)',
    `biz_reg_file_name`       VARCHAR(191) NULL     COMMENT '사업자등록증 파일명 (File_Upload 라이브러리 file_name)',
    `broker_reg_file_dir`     VARCHAR(191) NULL     COMMENT '중개사무소등록증 파일 경로',
    `broker_reg_file_name`    VARCHAR(191) NULL     COMMENT '중개사무소등록증 파일명',
    `fid`                     VARCHAR(32)  NULL     COMMENT '2차도메인 - 가입 시점엔 NULL, 서비스 신청 단계에서 고객이 입력/선택',
    `member_no`               INT UNSIGNED NULL     COMMENT '이관된 중앙 member.member_no (이관 전 NULL)',
    `migrated_yn`             CHAR(1)      NOT NULL DEFAULT 'N' COMMENT '중앙 member 이관 완료여부 Y/N',
    `migrated_date`           DATETIME     NULL     COMMENT '이관 처리일시',
    `reg_date`                DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`customer_idx`),
    UNIQUE KEY `uk_customer_id` (`customer_id`),
    KEY `ix_biz_reg_no` (`biz_reg_no`),
    KEY `ix_broker_reg_no` (`broker_reg_no`),
    KEY `ix_email` (`email`),
    KEY `ix_fid` (`fid`),
    KEY `ix_member_no` (`member_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
