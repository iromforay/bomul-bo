/*
SQLyog Community v13.3.1 (64 bit)
MySQL - 11.8.6-MariaDB-5ubuntu0.1 from Ubuntu : Database - bomul
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`bomul` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `bomul`;

/*Table structure for table `batch_schedule` */

DROP TABLE IF EXISTS `batch_schedule`;

CREATE TABLE `batch_schedule` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `job_type` varchar(32) NOT NULL COMMENT '배치 종류 (analytics_daily/log_collect/image_process 등)',
  `run_date` date NOT NULL COMMENT '집계 대상 날짜',
  `fid` varchar(64) NOT NULL COMMENT '중개사 shop_code',
  `status` enum('pending','sent','done','failed') NOT NULL DEFAULT 'pending',
  `sent_at` datetime DEFAULT NULL,
  `callback_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_job_run_fid` (`job_type`,`run_date`,`fid`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='범용 배치 진행상황 모니터링';

/*Data for the table `batch_schedule` */

insert  into `batch_schedule`(`id`,`job_type`,`run_date`,`fid`,`status`,`sent_at`,`callback_at`,`created_at`) values 
(1,'analytics_daily','2026-07-29','foray','done','2026-07-30 02:19:13','2026-07-30 02:19:13','2026-07-30 02:19:13'),
(2,'analytics_daily','2026-08-03','foray','sent','2026-08-04 02:00:02',NULL,'2026-08-04 02:00:02'),
(3,'analytics_daily','2026-08-04','foray','sent','2026-08-05 02:00:02',NULL,'2026-08-05 02:00:02'),
(4,'analytics_daily','2026-08-05','foray','sent','2026-08-06 02:00:01',NULL,'2026-08-06 02:00:01'),
(5,'analytics_daily','2026-08-06','foray','sent','2026-08-07 02:00:02',NULL,'2026-08-07 02:00:02'),
(6,'analytics_daily','2026-08-07','foray','sent','2026-08-08 02:00:02',NULL,'2026-08-08 02:00:02'),
(7,'analytics_daily','2026-08-08','foray','sent','2026-08-09 02:00:01',NULL,'2026-08-09 02:00:01'),
(8,'analytics_daily','2026-08-09','foray','sent','2026-08-10 02:00:01',NULL,'2026-08-10 02:00:01');

/*Table structure for table `bo_usage_daily` */

DROP TABLE IF EXISTS `bo_usage_daily`;

CREATE TABLE `bo_usage_daily` (
  `bo_usage_daily_no` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `stat_date` date NOT NULL COMMENT '집계 대상일(전일)',
  `live_no` bigint(20) unsigned NOT NULL COMMENT '어느 라이브 서비스(=테넌트)인지 (service_live.live_no)',
  `customer_no` int(10) unsigned NOT NULL COMMENT '고객 IDX - live_no 조인 없이 바로 조회(payment 패턴과 동일 비정규화)',
  `fid` varchar(50) NOT NULL COMMENT '테넌트 식별자(=접속한 DB명)',
  `login_yn` char(1) NOT NULL DEFAULT 'N' COMMENT '그 날짜에 bo 로그인 여부(중앙 login_session 기준)',
  `setup_touch_yn` char(1) NOT NULL DEFAULT 'N' COMMENT '그 날짜에 기본세팅(basic/menu/img/add_menu) 테이블을 손댔는지',
  `content_add_count` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '그 날짜에 새로 등록된 컨텐츠(property+note+post) 건수',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`bo_usage_daily_no`),
  UNIQUE KEY `uk_stat_date_live_no` (`stat_date`,`live_no`),
  KEY `idx_customer_no` (`customer_no`),
  KEY `idx_stat_date` (`stat_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='#37 테넌트(bo) 일별 사용 현황 - bomul-bo 배치가 직접 스캔한 전일치 로그';

/*Data for the table `bo_usage_daily` */

/*Table structure for table `board` */

DROP TABLE IF EXISTS `board`;

CREATE TABLE `board` (
  `board_no` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '게시글 IDX',
  `board_type` varchar(10) NOT NULL COMMENT '게시판 구분(INQUIRY:상담문의, QNA:QNA, NOTICE:공지사항)',
  `title` varchar(200) NOT NULL COMMENT '제목',
  `content` text NOT NULL COMMENT '내용',
  `writer_name` varchar(50) DEFAULT NULL COMMENT '작성자명(비회원 상담문의 등, 관리자 작성이면 NULL)',
  `writer_phone` varchar(20) DEFAULT NULL COMMENT '작성자 연락처(비회원 상담문의용)',
  `customer_no` bigint(20) unsigned DEFAULT NULL COMMENT '작성 회원 IDX(customer.customer_no) - 비회원/관리자 작성이면 NULL',
  `status` varchar(10) NOT NULL DEFAULT 'WAIT' COMMENT '처리상태(WAIT:답변대기, DONE:답변완료) - 공지사항은 미사용',
  `reply_content` text DEFAULT NULL COMMENT '답변 내용',
  `replied_at` datetime DEFAULT NULL COMMENT '답변일시',
  `view_count` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '조회수',
  `use_yn` char(1) NOT NULL DEFAULT 'Y' COMMENT '노출여부(공지 게시/비게시 등)',
  `created_at` datetime NOT NULL DEFAULT current_timestamp() COMMENT '등록일시',
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '수정일시',
  PRIMARY KEY (`board_no`),
  KEY `idx_board_type` (`board_type`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='통합 게시판 - board_type으로 상담문의/QNA/공지사항 구분';

/*Data for the table `board` */

insert  into `board`(`board_no`,`board_type`,`title`,`content`,`writer_name`,`writer_phone`,`customer_no`,`status`,`reply_content`,`replied_at`,`view_count`,`use_yn`,`created_at`,`updated_at`) values 
(1,'INQUIRY','test님의 상담 신청','test 상담 신청','test','123123',NULL,'WAIT',NULL,NULL,0,'Y','2026-08-11 07:59:03','2026-08-11 07:59:03');

/*Table structure for table `customer` */

DROP TABLE IF EXISTS `customer`;

CREATE TABLE `customer` (
  `customer_no` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` varchar(50) NOT NULL COMMENT '로그인 아이디',
  `customer_pw` char(64) NOT NULL COMMENT 'SHA-256 해시(hex) - 중앙 member.user_pwd 이관 호환',
  `customer_name` varchar(50) DEFAULT NULL COMMENT '담당자명 - 서비스 신청 단계에서 입력',
  `company_name` varchar(100) NOT NULL COMMENT '중개사무소명',
  `biz_reg_no` varchar(20) DEFAULT NULL COMMENT '사업자등록번호 - 서비스 신청 단계에서 입력',
  `broker_reg_no` varchar(30) DEFAULT NULL COMMENT '중개사무소등록번호 - 서비스 신청 단계에서 입력',
  `phone` varchar(20) NOT NULL COMMENT '휴대폰번호',
  `email` varchar(100) DEFAULT NULL COMMENT '이메일 - 서비스 신청 단계에서 입력',
  `biz_reg_verified_yn` char(1) NOT NULL DEFAULT 'N' COMMENT '사업자등록번호 검증여부(대기 : N , 검증 완료 : Y , 보류 : W ,검증실패 : F)',
  `broker_reg_verified_yn` char(1) NOT NULL DEFAULT 'N' COMMENT '중개사무소등록번호 검증여부 (대기 : N , 검증 완료 : Y , 보류 : W ,검증실패 : F)',
  `use_yn` char(1) NOT NULL DEFAULT 'Y' COMMENT '계정 사용여부 Y/N (사용자 계정 자체를 정지시킬 때 사용, 서비스 이용여부와 무관)',
  `service_yn` char(1) NOT NULL DEFAULT 'N' COMMENT '서비스 사용여부 Y/N - 결제로 활성화, 만료되면 N',
  `service_expire_date` date DEFAULT NULL COMMENT '서비스 만료일 - 결제(1회성/기간제/정기) 결과로 갱신',
  `biz_reg_file_dir` varchar(256) DEFAULT NULL COMMENT '사업자등록증 파일 경로',
  `biz_reg_file_name` varchar(256) DEFAULT NULL COMMENT '사업자등록증 파일명',
  `broker_reg_file_dir` varchar(256) DEFAULT NULL COMMENT '중개사무소등록증 파일 경로',
  `broker_reg_file_name` varchar(256) DEFAULT NULL COMMENT '중개사무소등록증 파일명',
  `member_no` int(10) unsigned DEFAULT NULL COMMENT '이관된 중앙 member.member_no (이관 전 NULL)',
  `migrated_yn` char(1) NOT NULL DEFAULT 'N' COMMENT '중앙 member 이관 완료여부 Y/N',
  `migrated_date` datetime DEFAULT NULL COMMENT '이관 처리일시',
  `reg_date` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`customer_no`),
  UNIQUE KEY `uk_customer_id` (`customer_id`),
  KEY `ix_biz_reg_no` (`biz_reg_no`),
  KEY `ix_broker_reg_no` (`broker_reg_no`),
  KEY `ix_email` (`email`),
  KEY `ix_member_no` (`member_no`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `customer` */

insert  into `customer`(`customer_no`,`customer_id`,`customer_pw`,`customer_name`,`company_name`,`biz_reg_no`,`broker_reg_no`,`phone`,`email`,`biz_reg_verified_yn`,`broker_reg_verified_yn`,`use_yn`,`service_yn`,`service_expire_date`,`biz_reg_file_dir`,`biz_reg_file_name`,`broker_reg_file_dir`,`broker_reg_file_name`,`member_no`,`migrated_yn`,`migrated_date`,`reg_date`) values 
(2,'demo','29838e96a4a6f7f053937dc30924bd2c6b9ef8d68e1a2542a6afaf7ebed1a1f4','홍길동','데모공인중개사사무소','123-45-67890','11110-2024-00099','010-1234-5678','test@example.com','Y','Y','Y','N',NULL,'2/','b11a25ff2c4069ca940d258ce8ff5395.png','2/','0ba45b3dab28c88e7adad31c09897615.png',NULL,'N',NULL,'2026-08-05 01:40:46'),
(3,'demo2','29838e96a4a6f7f053937dc30924bd2c6b9ef8d68e1a2542a6afaf7ebed1a1f4',NULL,'데모2 공인중개사사무소',NULL,NULL,'010-1234-5678',NULL,'N','N','Y','N',NULL,NULL,NULL,NULL,NULL,NULL,'N',NULL,'2026-08-05 08:42:30'),
(4,'Xodid9811','d3d31557a6ab23c02cfcfdc7038a2bf1c7565e359839e9d2ac4112e662aa6b85','최연화','마포공인중개사사무소','2826633332','7645677827','01047475987','yhchoi5987@naver.com','Y','Y','Y','N',NULL,NULL,NULL,NULL,NULL,NULL,'N',NULL,'2026-08-12 01:20:23'),
(5,'ljh','17fa2c7ca93fcdc0fd57ef7ecd47d8a503e1365377a529545324e4b9e1c1a23c','이정희','이정희','11111111111111','111111111111111','010-4029-4349','junghele@naver.com','N','N','Y','N',NULL,NULL,NULL,NULL,NULL,NULL,'N',NULL,'2026-08-26 05:20:51');

/*Table structure for table `login_session` */

DROP TABLE IF EXISTS `login_session`;

CREATE TABLE `login_session` (
  `login_session_no` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` varchar(255) DEFAULT NULL,
  `data` text DEFAULT NULL,
  `fid` varchar(100) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `write_date` datetime DEFAULT NULL,
  PRIMARY KEY (`login_session_no`)
) ENGINE=InnoDB AUTO_INCREMENT=118 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

/*Data for the table `login_session` */

insert  into `login_session`(`login_session_no`,`session_id`,`data`,`fid`,`status`,`write_date`) values 
(1,'foray',NULL,'foray','Y','2026-06-30 00:32:49'),
(2,'95210d54ac3b07576575893f55cf6fb44e2c734c4934904be65dde944de0c3e2','a:12:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-06-30 01:19:14'),
(3,'badd795f8821e43d6577f98c125ddc3000b1d20664554f03ff374db68b37407e','a:12:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-06-30 01:32:27'),
(4,'e48fc7d1d9fd2dcb70a7f7adec11ddfdbd244c1d1e5ee6e2aecd7ee250c5f458','a:12:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-06-30 01:33:46'),
(5,'cb74c8e0b73b1d401848dcd8675afa40519b20b99c958706659be96149d468f8','a:12:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-06-30 01:34:10'),
(6,'72316987832a89734e5e6c8b7c6cc646e2789349477785a0b83424281ca337b2','a:12:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-06-30 01:34:24'),
(7,'f71ed4110e66998b87d751039cee2e7c141df61500477af6be7b14fcc51c5454','a:12:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-06-30 01:34:36'),
(8,'c966548b4a97097b096cb311ee72bc8e617240617c77791c6c78240c5d9d65af','a:12:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-06-30 01:34:58'),
(9,'9246092361d3203a62a35169445cfb16cd724dcf3d988135a93eaee3d1fbf8c0','a:13:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-06-30 01:40:29'),
(10,'61c5662f8551d22b28c3d03d35ac177e34f51a3196c174d564cde00265bf4ff4','a:13:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-06-30 02:13:32'),
(11,'9a008a6bc74b1fc97ae43bdc383f693f0c612c6f53d8662e574574c491836256','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:1:\"Y\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-06-30 05:34:09'),
(12,'38b2665e0da6196d654598987d4dca66d4b321253c3f8c2351835b250d28285d','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:1:\"Y\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-07-01 05:23:36'),
(13,'61b84d24ea929505d2b1635566c007fbde1f9db0e1e1a1a73bba03d91ca9d717','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:1:\"Y\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-07-02 07:03:12'),
(14,'3368d32711bc06bf71eac29f2e3ccb9f31a3f983f09a31fb07728f72440e55a6','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:1:\"Y\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-07-02 09:02:33'),
(15,'10b3f97c301b19a2274b94c75d4d40ffea40ad7d155c3c8cc2e4c9a4b2669eb8','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:1:\"Y\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-07-02 09:04:00'),
(16,'84d6128e426c0f5a59bf9ac0af619c81fd8d9af40fd1b4d6c1f05819588614b2','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:1:\"Y\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-07-03 05:13:20'),
(17,'2aa27d230f57df6e0441b9c27e7b48088151d90a7333395848535c18f9f700d1','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:1:\"Y\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-07-03 07:18:34'),
(18,'cfc13d8e2b3e2c7020bff1131e169216e533cc90364a2806377b932a8e8d266a','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:1:\"Y\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-07-08 05:05:55'),
(19,'9eb5e16640317cb79da84f07cf46bc8a1c38787ff870b9dfa25b74f4627d4894','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:1:\"Y\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-07-08 05:46:21'),
(20,'ced91a6c1bb38dc57b606917d6d3090e4411143b9d00f5cb5f55c3ab6bb7c0a8','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:1:\"Y\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-07-08 06:18:21'),
(21,'25670daa9098c516e4bccb3f9f4be58948e777d9565269976a9b46ad1fb9d856','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:1:\"Y\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-07-14 03:21:37'),
(22,'78d0c7bdfbdfdc4f45512d14f95854d8593c04d93b6a9c671c44d14f3771c4d3','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:1:\"Y\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-07-15 08:08:04'),
(23,'0c43445a761889534a472980ffe643e338b229f5073c32bfc69410e8300ae353','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:1:\"Y\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-07-21 07:47:15'),
(24,'b8ab0c377b0bda2cc0f284322387784b50f710bce9084dfc276bb5499abd1151','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:1:\"Y\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-07-27 00:26:03'),
(25,'ceb71eaca94c3774f987ada181db5a09842811386ac2738e7fba0ae5cad48b79','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:1:\"Y\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-07-27 00:26:08'),
(26,'0ef5fc3fefc8c7d2711b29ce3dcb0756d1f23b9763226e13d8d30dec40de98fe','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:1:\"Y\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-07-27 00:34:53'),
(27,'452fc3cd14c16a7c7ab53f03ed50bdf05eea109f89d1be7a77eb53f489dcee21','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:1:\"Y\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-07-27 11:50:57'),
(28,'3f1788e481ba28225a78f401bf9ea8bba8045208a0b2d4ca9a3db2d948feb9e2','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:1:\"Y\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-07-27 14:07:16'),
(29,'873e290551de9609e80d59a6b997d1ddc08beec01ac1f327a41c50ef1345eac0','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:1:\"Y\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-07-28 01:53:11'),
(30,'27d0a7f2b29eaf0fc942529dcfe2bd15199dbb87ddf002cb508600621b85217a','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:1:\"Y\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-07-28 05:44:56'),
(31,'2409be07fe94a1285d8adc85dc1ed34ae0bf8c8d12b4d47330193dc6dc08d31f','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:1:\"Y\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-07-28 05:51:01'),
(32,'d871c0976924f2c414f7d36a94e87c24e581dafcc891b5e5cf61c4229ffa9ce6','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:1:\"Y\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-07-28 07:02:36'),
(33,'2c27cf27dd02775577e4b26359a23265cee36d759e6dc42f1c3a797a634f6043','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:1:\"Y\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-07-30 03:53:26'),
(34,'70f31145771af7ea29ac9fb46dfec1d47e41ff76d6fa5d7674d6ea0cb2cf5249','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:1:\"Y\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-07-31 07:24:17'),
(35,'909d7c2eec5c612a2ac7894ff377bf6b0a36fbadda427e5629872019872251e4','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:1:\"Y\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-08-01 02:31:15'),
(36,'4ff7d07297fddf2e71c3a9922d2732df55569603522cdc147a5a448fec2828a6','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:1:\"Y\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-08-01 04:18:49'),
(37,'873bfd003cb0311be00e9686f60cfa6b38730f4ea932f05d14dabaa3a460a284','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:1:\"Y\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-08-01 04:24:12'),
(38,'82a8a28cfaa0fe8ef186670e1e3244da6accbf3862f03e650f868a0e436e346f','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:1:\"Y\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-08-01 05:34:27'),
(39,'8e32fd4f68e3c0e4c7a2dfbb8804bd135494b518e37f5289189221f700842e47','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:1:\"Y\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-08-01 05:34:31'),
(40,'0a564b376d004fa91208eea20613bc54d877091d48cbf4e5702173dd43308304','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:1:\"Y\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-08-02 03:58:54'),
(41,'40b332a9ff1f3047bee5ba4124f1014bc1ad33142ad0a121333a60cca111be93','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:1:\"Y\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-08-02 03:58:54'),
(42,'8ffc21da7fa9b706b6050bb0d97b10b0c98e85595fd3acc43d244722abe8f0f3','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:1:\"Y\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-08-03 00:13:30'),
(43,'7978e96d91166e971f2d0a323d307960dbfae182ddfd847926a4a4c60d89f475','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:1:\"Y\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-08-03 00:18:40'),
(44,'72423e4a1e03ae8fc7a090b5c6efd85a8be3c1705f2668e530244f08f1703941','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:1:\"Y\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-08-03 00:40:09'),
(45,'18a5b57165a3381fece14513a1855f7f9ecaf837308d62e8922c421aeda87da7','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:1:\"Y\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-08-03 00:42:14'),
(46,'10d12d8579edf92a7d672ffbf2aee08866c49b8377ca4befaae94a90f6fdda5c','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:1:\"Y\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-08-03 03:31:23'),
(47,'89ea85343328fa39d7a52ef471bcce9a8c039bca16ae5be785e42cfd1ac6aa2b','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:1:\"Y\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-08-03 03:34:52'),
(48,'4d6aff6774a9070f8580b0aabc67685b81f61bc8736bc88ceaf4fdfc2fd36678','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:1:\"Y\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-08-03 03:50:21'),
(49,'abaae5bb82f24b05e30a9a438d4fce0763d8e7e7c39f5e8562d00a76f774f0ef','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:1:\"Y\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-08-03 04:47:31'),
(50,'768077aa743e679e4ac3daf5a47ca8c6e18e9c504896c05229070924d695254b','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:1:\"Y\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-08-03 05:29:33'),
(51,'bc661b01124300e9ac3180068060ffc4520e8594c766509729454ad5272e67c3','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:1:\"Y\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-08-04 02:54:59'),
(52,'867f19d69047fdbf11d4a3cfdcd2eeac49ae8f5bd53855f598537071ca416cda','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:1:\"Y\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-08-04 03:55:55'),
(53,'b97c4ac1904706c1aedc65de1e21576ec630bfb5ac726998ee2870bdd1adf133','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:1:\"Y\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-08-04 08:50:45'),
(54,'874b9b920f4669de31126cb249920863832372901944e9221fabe20d4c3a0166','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:1:\"Y\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-08-06 07:20:18'),
(55,'aa065d8a9409a82d3a225245e1d8a1c168b731b2f0d1b03b0e09b713c717d4fd','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:1:\"Y\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-08-09 21:19:10'),
(56,'1b670ceda5f63a44f40c88d654226df74729b65dc436e6f5013f4a42b9fbb2b0','a:14:{s:9:\"member_no\";s:1:\"2\";s:7:\"user_id\";s:4:\"demo\";s:8:\"user_pwd\";s:64:\"29838e96a4a6f7f053937dc30924bd2c6b9ef8d68e1a2542a6afaf7ebed1a1f4\";s:3:\"fid\";s:5:\"demo2\";s:7:\"service\";N;s:5:\"fname\";N;s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";N;s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','demo2',NULL,'2026-08-10 01:39:03'),
(57,'f53de1b7124acd5291a75a35ce9a3d18611fc0d764d2512c6edbfdd6372ee7e5','a:14:{s:9:\"member_no\";s:1:\"2\";s:7:\"user_id\";s:4:\"demo\";s:8:\"user_pwd\";s:64:\"29838e96a4a6f7f053937dc30924bd2c6b9ef8d68e1a2542a6afaf7ebed1a1f4\";s:3:\"fid\";s:5:\"demo2\";s:7:\"service\";N;s:5:\"fname\";N;s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";N;s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','demo2',NULL,'2026-08-10 01:39:19'),
(58,'5a10b29061b222f9a27f222e6cd0ba1b5f434662d11c5525ed9dda1c7478028c','a:14:{s:9:\"member_no\";s:1:\"2\";s:7:\"user_id\";s:4:\"demo\";s:8:\"user_pwd\";s:64:\"29838e96a4a6f7f053937dc30924bd2c6b9ef8d68e1a2542a6afaf7ebed1a1f4\";s:3:\"fid\";s:5:\"demo2\";s:7:\"service\";N;s:5:\"fname\";N;s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";N;s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','demo2',NULL,'2026-08-10 01:41:21'),
(59,'4a0e19617c743961d2ea7cf6f10d7f111872d6be64838e8e70dce2ef798b6b28','a:14:{s:9:\"member_no\";s:1:\"2\";s:7:\"user_id\";s:4:\"demo\";s:8:\"user_pwd\";s:64:\"29838e96a4a6f7f053937dc30924bd2c6b9ef8d68e1a2542a6afaf7ebed1a1f4\";s:3:\"fid\";s:5:\"demo2\";s:7:\"service\";N;s:5:\"fname\";N;s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";N;s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','demo2',NULL,'2026-08-10 01:44:22'),
(60,'758ad2d02dee430f6a2c7ecf1a7f4f59a4de8cd8f360f5b0e5b3fc5c884e697b','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:1:\"Y\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-08-10 01:45:49'),
(61,'f2b8cddb5e01bac720c500bb4864c62748b2e6bf62973bade97e72fa5feb22b6','a:14:{s:9:\"member_no\";s:1:\"2\";s:7:\"user_id\";s:4:\"demo\";s:8:\"user_pwd\";s:64:\"29838e96a4a6f7f053937dc30924bd2c6b9ef8d68e1a2542a6afaf7ebed1a1f4\";s:3:\"fid\";s:5:\"demo2\";s:7:\"service\";N;s:5:\"fname\";N;s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";N;s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','demo2',NULL,'2026-08-10 01:56:50'),
(62,'8a387b9047e7464854e02f9c51fd2e6f779f311f999a39b73c50065056ea40a3','a:14:{s:9:\"member_no\";s:1:\"2\";s:7:\"user_id\";s:4:\"demo\";s:8:\"user_pwd\";s:64:\"29838e96a4a6f7f053937dc30924bd2c6b9ef8d68e1a2542a6afaf7ebed1a1f4\";s:3:\"fid\";s:5:\"demo2\";s:7:\"service\";N;s:5:\"fname\";N;s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";N;s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','demo2',NULL,'2026-08-10 02:02:49'),
(63,'2d024b438f7503566c8b7ad914dda8e4fb66fcaa2855113d36b1dfa464d05634','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:1:\"Y\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-08-10 06:13:59'),
(64,'29cc2d2123c6ee34de627bcf0818ace9b081d3c4c1f3aa13dc3ab24a7613e4c0','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:1:\"Y\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-08-10 06:14:19'),
(65,'9fcd3951640c3f1835f1f3de5aff997cf1deb6e560d96fc22a7f823aa17a6192','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:9:\"OPERATING\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-08-11 10:56:29'),
(66,'7223b7d000038c100e6a9da152ee3b00be54ff60a0339e8915c210b97ef6beb0','a:14:{s:9:\"member_no\";s:1:\"2\";s:7:\"user_id\";s:4:\"demo\";s:8:\"user_pwd\";s:64:\"29838e96a4a6f7f053937dc30924bd2c6b9ef8d68e1a2542a6afaf7ebed1a1f4\";s:3:\"fid\";s:5:\"demo2\";s:7:\"service\";s:10:\"ALL_CLOSED\";s:5:\"fname\";N;s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";N;s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','demo2',NULL,'2026-08-13 00:54:12'),
(67,'e39992b1afbe7e3cf74c16d6d9f4f0601c9137624bc22f27f01eb2aa09575bae','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:9:\"OPERATING\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-08-13 01:16:12'),
(68,'6556e42d60b59a802f6a152d2c9a7e287b661ad303e8cbbc5c1281023e1e156d','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:9:\"OPERATING\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-08-13 01:17:21'),
(69,'74fc84af66f0176a4d203746ba2402366f1495150ed613b9d1ad9e3f3893d9e5','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:9:\"OPERATING\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-08-13 02:54:11'),
(70,'814beb839647275827a4a4d0a7d8013aa60569668f60083aa0a4f75addb29c18','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:9:\"OPERATING\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-08-13 04:06:38'),
(71,'a386c439ce1f39b8c0c9c6404da8697c077349f440dc6f4ef53cc67f24d89dba','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:9:\"OPERATING\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-08-13 04:54:06'),
(72,'c1320c3fdaa55ae3a86be82d97b8034be3266451d00c60e4b990d5715f884ce8','a:14:{s:9:\"member_no\";s:1:\"2\";s:7:\"user_id\";s:4:\"demo\";s:8:\"user_pwd\";s:64:\"29838e96a4a6f7f053937dc30924bd2c6b9ef8d68e1a2542a6afaf7ebed1a1f4\";s:3:\"fid\";s:5:\"demo2\";s:7:\"service\";s:10:\"ALL_CLOSED\";s:5:\"fname\";N;s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";N;s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','demo2',NULL,'2026-08-13 06:00:44'),
(73,'66cde98d50add6b6f2f639106ca2350efa8b931c6ec7ff26f34c05f940c37612','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:9:\"OPERATING\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-08-13 06:01:00'),
(74,'1771b8ebde0ccefb56d42fe736be6fd64a32e78fa503d246483341fc80759163','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:9:\"OPERATING\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-08-13 06:01:53'),
(75,'7c88b9a090d7f0ef3dda6670e1cb074d7802253692eda6c8c406af92a19501fa','a:14:{s:9:\"member_no\";s:1:\"2\";s:7:\"user_id\";s:4:\"demo\";s:8:\"user_pwd\";s:64:\"29838e96a4a6f7f053937dc30924bd2c6b9ef8d68e1a2542a6afaf7ebed1a1f4\";s:3:\"fid\";s:5:\"demo2\";s:7:\"service\";s:10:\"ALL_CLOSED\";s:5:\"fname\";N;s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";N;s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','demo2',NULL,'2026-08-13 06:05:44'),
(76,'3e3c1f2cf234afbf042b9565261a62a3d1314d84e618693ee09172eab9f73dc8','a:14:{s:9:\"member_no\";s:1:\"2\";s:7:\"user_id\";s:4:\"demo\";s:8:\"user_pwd\";s:64:\"29838e96a4a6f7f053937dc30924bd2c6b9ef8d68e1a2542a6afaf7ebed1a1f4\";s:3:\"fid\";s:5:\"demo2\";s:7:\"service\";s:10:\"ALL_CLOSED\";s:5:\"fname\";N;s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";N;s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','demo2',NULL,'2026-08-13 06:06:13'),
(77,'7aa2dd0c1c149aed3bd2a5f1169e79059a56b42172699d823eef0488813b2ba9','a:14:{s:9:\"member_no\";s:1:\"2\";s:7:\"user_id\";s:4:\"demo\";s:8:\"user_pwd\";s:64:\"29838e96a4a6f7f053937dc30924bd2c6b9ef8d68e1a2542a6afaf7ebed1a1f4\";s:3:\"fid\";s:5:\"demo2\";s:7:\"service\";s:10:\"ALL_CLOSED\";s:5:\"fname\";N;s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";N;s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','demo2',NULL,'2026-08-13 06:06:28'),
(78,'5cfb346e133b80b512702c46886f5884537eb072177c3aca27c9e3aa3b023e05','a:14:{s:9:\"member_no\";s:1:\"2\";s:7:\"user_id\";s:4:\"demo\";s:8:\"user_pwd\";s:64:\"29838e96a4a6f7f053937dc30924bd2c6b9ef8d68e1a2542a6afaf7ebed1a1f4\";s:3:\"fid\";s:5:\"demo2\";s:7:\"service\";s:10:\"ALL_CLOSED\";s:5:\"fname\";N;s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";N;s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','demo2',NULL,'2026-08-13 06:07:05'),
(79,'209b3938204aaff47ac57ea3b7d00260923f12d4918949cc2540a85d1b28e093','a:14:{s:9:\"member_no\";s:1:\"2\";s:7:\"user_id\";s:4:\"demo\";s:8:\"user_pwd\";s:64:\"29838e96a4a6f7f053937dc30924bd2c6b9ef8d68e1a2542a6afaf7ebed1a1f4\";s:3:\"fid\";s:5:\"demo1\";s:7:\"service\";s:10:\"ALL_CLOSED\";s:5:\"fname\";N;s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";N;s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','demo1',NULL,'2026-08-13 06:07:54'),
(80,'77474b04e8b95062e0712e830e943d68f278d332635b5dc743fa09ae4d1391ec','a:14:{s:9:\"member_no\";s:1:\"2\";s:7:\"user_id\";s:4:\"demo\";s:8:\"user_pwd\";s:64:\"29838e96a4a6f7f053937dc30924bd2c6b9ef8d68e1a2542a6afaf7ebed1a1f4\";s:3:\"fid\";s:5:\"demo1\";s:7:\"service\";s:10:\"ALL_CLOSED\";s:5:\"fname\";N;s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";N;s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','demo1',NULL,'2026-08-13 06:18:16'),
(81,'faa6135f42ee0e0494296e3bca0eb7f58e9a0ce5562f25871601219910ef55d4','a:14:{s:9:\"member_no\";s:1:\"3\";s:7:\"user_id\";s:9:\"Xodid9811\";s:8:\"user_pwd\";s:64:\"d3d31557a6ab23c02cfcfdc7038a2bf1c7565e359839e9d2ac4112e662aa6b85\";s:3:\"fid\";s:4:\"mapo\";s:7:\"service\";N;s:5:\"fname\";N;s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";N;s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','mapo',NULL,'2026-08-13 06:18:23'),
(82,'897c6b86db3837796772d8f40123ea6d25e0fe613375281c9918c7d9aba9abbe','a:14:{s:9:\"member_no\";s:1:\"3\";s:7:\"user_id\";s:9:\"Xodid9811\";s:8:\"user_pwd\";s:64:\"d3d31557a6ab23c02cfcfdc7038a2bf1c7565e359839e9d2ac4112e662aa6b85\";s:3:\"fid\";s:4:\"mapo\";s:7:\"service\";N;s:5:\"fname\";N;s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";N;s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','mapo',NULL,'2026-08-13 06:18:56'),
(83,'2cd86f0e8c630e9999bc0a9d240d20afd2a4c95104cee96d0768a9d75115f873','a:14:{s:9:\"member_no\";s:1:\"3\";s:7:\"user_id\";s:9:\"Xodid9811\";s:8:\"user_pwd\";s:64:\"d3d31557a6ab23c02cfcfdc7038a2bf1c7565e359839e9d2ac4112e662aa6b85\";s:3:\"fid\";s:4:\"mapo\";s:7:\"service\";N;s:5:\"fname\";N;s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";N;s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','mapo',NULL,'2026-08-13 06:19:11'),
(84,'4d536e579fa60c1fb384b984534915cfd848b44415dc9f24cb4366214d5f635f','a:14:{s:9:\"member_no\";s:1:\"3\";s:7:\"user_id\";s:9:\"Xodid9811\";s:8:\"user_pwd\";s:64:\"d3d31557a6ab23c02cfcfdc7038a2bf1c7565e359839e9d2ac4112e662aa6b85\";s:3:\"fid\";s:4:\"mapo\";s:7:\"service\";N;s:5:\"fname\";N;s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";N;s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','mapo',NULL,'2026-08-13 06:20:30'),
(85,'21d7c1c6915c4e270b09e0b9055cf91dd99dc1063cdaef2d289dd00f1b088204','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:9:\"OPERATING\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-08-13 06:33:39'),
(86,'8b41c9f56512a064fa9b4ee478f3689e4f48532094abcaebd50c595956c32165','a:14:{s:9:\"member_no\";s:1:\"3\";s:7:\"user_id\";s:9:\"Xodid9811\";s:8:\"user_pwd\";s:64:\"d3d31557a6ab23c02cfcfdc7038a2bf1c7565e359839e9d2ac4112e662aa6b85\";s:3:\"fid\";s:4:\"mapo\";s:7:\"service\";N;s:5:\"fname\";N;s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";N;s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','mapo',NULL,'2026-08-13 06:52:27'),
(87,'153db64a1410504320e22b7db76f1739d46492a2edb517988b8f5f6c782c8fe3','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:9:\"OPERATING\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-08-13 15:11:00'),
(88,'99c8a2eb1c7ce65ebbacd3b03a428f4a04652eabf88103cff9a1f923b8b0704a','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:9:\"OPERATING\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-08-13 15:11:39'),
(89,'e6ab0365acc4cc00159806b0c208fac813b4e21e81e0fed3f3132260c222c813','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:9:\"OPERATING\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-08-13 15:13:04'),
(90,'98ddd98da6e251112e127b733dded2cd4dd42f5679b18973e56c6eb218204f39','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:9:\"OPERATING\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-08-13 15:13:54'),
(91,'89516a7a97c4833da9e73b0f214fd6597b5d72f0d88e6bc8f1bae7d7946bafbb','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:9:\"OPERATING\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-08-13 15:54:57'),
(92,'df19b55431ef47bcda394e44fe114e706ff95b95be3d4fbc80c2154dc56a5892','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:9:\"OPERATING\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-08-13 15:55:17'),
(93,'5ce7bfd6c0e07395f35885e89612fc86c3e1188f3a63035ecea767f53eba5dd3','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:9:\"OPERATING\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-08-13 16:09:15'),
(94,'8fba20ae3c8a98ff8de4817693ae81ac3bcd4fb8aa8a8d6822257215a921d967','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:9:\"OPERATING\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-08-13 16:14:34'),
(95,'d510d517f1823c7bbd9934bdbdf7b6848c431fb70b3044db99e04eadf6de1ec1','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:9:\"OPERATING\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-08-13 16:18:25'),
(96,'fd10742aefefddf2bd782c883cfee19a5715d7e0e63f33ea88bc5a4d536e7549','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:9:\"OPERATING\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-08-13 16:33:13'),
(97,'135860d72668d6dccfcdb72adc4ab98d458cd44140aabad3a4b4a191cf417ec6','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:9:\"OPERATING\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-08-13 16:40:57'),
(98,'db015b11d5a7e4b0d3a2168d9157cb6d0c95614bf8b5a7abffd78b394ec3dedf','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:9:\"OPERATING\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-08-13 16:43:29'),
(99,'3946fa48c1c3e91984494714192efae987f4df162266281b31cc3835665370c9','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:9:\"OPERATING\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-08-13 16:49:51'),
(100,'aba7d4697fc1f80a58492f1016a1aa48e86f7676834f8ecc6dc11b527932f293','a:14:{s:9:\"member_no\";s:1:\"3\";s:7:\"user_id\";s:9:\"Xodid9811\";s:8:\"user_pwd\";s:64:\"d3d31557a6ab23c02cfcfdc7038a2bf1c7565e359839e9d2ac4112e662aa6b85\";s:3:\"fid\";s:4:\"mapo\";s:7:\"service\";N;s:5:\"fname\";N;s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";N;s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','mapo',NULL,'2026-08-14 01:04:32'),
(101,'de2376b4ef62241dec3d4622d794e44373b4938ece36e6a6c1582fb252eebd81','a:14:{s:9:\"member_no\";s:1:\"3\";s:7:\"user_id\";s:9:\"Xodid9811\";s:8:\"user_pwd\";s:64:\"d3d31557a6ab23c02cfcfdc7038a2bf1c7565e359839e9d2ac4112e662aa6b85\";s:3:\"fid\";s:4:\"mapo\";s:7:\"service\";N;s:5:\"fname\";N;s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";N;s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','mapo',NULL,'2026-08-14 01:04:38'),
(102,'2ae87eaee669349177c5c83b6a2635caee8abc817d78c851db2132b8d42e6189','a:14:{s:9:\"member_no\";s:1:\"2\";s:7:\"user_id\";s:4:\"demo\";s:8:\"user_pwd\";s:64:\"29838e96a4a6f7f053937dc30924bd2c6b9ef8d68e1a2542a6afaf7ebed1a1f4\";s:3:\"fid\";s:5:\"demo1\";s:7:\"service\";s:10:\"ALL_CLOSED\";s:5:\"fname\";N;s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";N;s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','demo1',NULL,'2026-08-14 01:06:04'),
(103,'eba4795c331cb085dc33ce0f6e584d775aca6608caeb0878be5a991ebeee7de0','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:9:\"OPERATING\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-08-14 05:35:32'),
(104,'561bb537163576642ab7d5a930f5b9f8d5446d45e386ffdc68848cc91e066f77','a:14:{s:9:\"member_no\";s:1:\"3\";s:7:\"user_id\";s:9:\"Xodid9811\";s:8:\"user_pwd\";s:64:\"d3d31557a6ab23c02cfcfdc7038a2bf1c7565e359839e9d2ac4112e662aa6b85\";s:3:\"fid\";s:4:\"mapo\";s:7:\"service\";N;s:5:\"fname\";N;s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";N;s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','mapo',NULL,'2026-08-14 07:15:36'),
(105,'97fd6a678e9c315067f48e35e48713d5f8482ada9dbd52e84d11d9f5f32428e1','a:14:{s:9:\"member_no\";s:1:\"3\";s:7:\"user_id\";s:9:\"Xodid9811\";s:8:\"user_pwd\";s:64:\"d3d31557a6ab23c02cfcfdc7038a2bf1c7565e359839e9d2ac4112e662aa6b85\";s:3:\"fid\";s:4:\"mapo\";s:7:\"service\";N;s:5:\"fname\";N;s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";N;s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','mapo',NULL,'2026-08-16 04:15:49'),
(106,'fd9e1262c7a2520b58fb66e62f76a4b3e95b07d51a83e79b6eac852c7b00b47d','a:14:{s:9:\"member_no\";s:1:\"3\";s:7:\"user_id\";s:9:\"Xodid9811\";s:8:\"user_pwd\";s:64:\"d3d31557a6ab23c02cfcfdc7038a2bf1c7565e359839e9d2ac4112e662aa6b85\";s:3:\"fid\";s:4:\"mapo\";s:7:\"service\";N;s:5:\"fname\";N;s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";N;s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','mapo',NULL,'2026-08-18 03:43:46'),
(107,'25fd76b2a7fb5da2c723475a86879d14015aa66430148757e2d5e0ff7eb6f6d5','a:14:{s:9:\"member_no\";s:1:\"3\";s:7:\"user_id\";s:9:\"Xodid9811\";s:8:\"user_pwd\";s:64:\"d3d31557a6ab23c02cfcfdc7038a2bf1c7565e359839e9d2ac4112e662aa6b85\";s:3:\"fid\";s:4:\"mapo\";s:7:\"service\";N;s:5:\"fname\";N;s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";N;s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','mapo',NULL,'2026-08-18 07:31:47'),
(108,'58bc9e952a3570359ebbb1ea00b657a15df0e900ea4e1a03f47a665346f92c29','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:9:\"OPERATING\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-08-19 01:44:36'),
(109,'3bbb4e04264e0f3fa70f4f66faa0b304785c75fbb5685224bbc33856b92ed95f','a:14:{s:9:\"member_no\";s:1:\"3\";s:7:\"user_id\";s:9:\"Xodid9811\";s:8:\"user_pwd\";s:64:\"d3d31557a6ab23c02cfcfdc7038a2bf1c7565e359839e9d2ac4112e662aa6b85\";s:3:\"fid\";s:4:\"mapo\";s:7:\"service\";N;s:5:\"fname\";N;s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";N;s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','mapo',NULL,'2026-08-19 04:15:25'),
(110,'ebbd9c8a356f0315ebf4c0852275c0e56440ea72835f75344cab2f7ea86dc993','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:9:\"OPERATING\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-08-19 05:19:57'),
(111,'677e4bb667610a0cfe0b4d81cf7495c51207c026b9b6a5a200400214e637ac28','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:9:\"OPERATING\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-08-19 07:00:06'),
(112,'47b0f09ecb699d2ab451ee27d92b5d7086a9745b91f90c5ef32fa299f21b9e07','a:14:{s:9:\"member_no\";s:1:\"3\";s:7:\"user_id\";s:9:\"Xodid9811\";s:8:\"user_pwd\";s:64:\"d3d31557a6ab23c02cfcfdc7038a2bf1c7565e359839e9d2ac4112e662aa6b85\";s:3:\"fid\";s:4:\"mapo\";s:7:\"service\";N;s:5:\"fname\";N;s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";N;s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','mapo',NULL,'2026-08-20 07:05:34'),
(113,'d652ec865d87bddec3c8fbcb46677de9776b366f951052dc0c51535e75934f74','a:14:{s:9:\"member_no\";s:1:\"3\";s:7:\"user_id\";s:9:\"Xodid9811\";s:8:\"user_pwd\";s:64:\"d3d31557a6ab23c02cfcfdc7038a2bf1c7565e359839e9d2ac4112e662aa6b85\";s:3:\"fid\";s:4:\"mapo\";s:7:\"service\";N;s:5:\"fname\";N;s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";N;s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','mapo',NULL,'2026-08-22 08:26:22'),
(114,'ed5b9634588efaf047c961af0096f5add69c4ab524cf28614dabefeca970331d','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:9:\"OPERATING\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-08-26 00:59:58'),
(115,'c0d618f8a3eeda198969acb6513dfd8625a060ea3fdd350574edfb7ed490039e','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:9:\"OPERATING\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-08-26 02:36:33'),
(116,'7e3555caf1b49e9cec7fbdaa499b66f4908dd1d4972187c2c38658f6d5988fc8','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:9:\"OPERATING\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-08-26 04:35:20'),
(117,'42a98f1e23d4cf3254e12c6b203c49e54babb6d5530d39b094b95737ceeac6eb','a:14:{s:9:\"member_no\";s:1:\"2\";s:7:\"user_id\";s:4:\"demo\";s:8:\"user_pwd\";s:64:\"29838e96a4a6f7f053937dc30924bd2c6b9ef8d68e1a2542a6afaf7ebed1a1f4\";s:3:\"fid\";s:5:\"demo1\";s:7:\"service\";s:9:\"OPERATING\";s:5:\"fname\";N;s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";N;s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','demo1',NULL,'2026-08-27 07:19:17');

/*Table structure for table `manager` */

DROP TABLE IF EXISTS `manager`;

CREATE TABLE `manager` (
  `manager_no` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `manager_id` varchar(50) NOT NULL COMMENT '로그인 아이디',
  `manager_pw` varchar(128) NOT NULL COMMENT '암호화 비번',
  `manager_name` varchar(50) NOT NULL COMMENT '이름',
  `use_yn` char(1) NOT NULL DEFAULT 'Y' COMMENT '사용여부 Y/N',
  `reg_date` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`manager_no`),
  UNIQUE KEY `uk_manager_id` (`manager_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `manager` */

insert  into `manager`(`manager_no`,`manager_id`,`manager_pw`,`manager_name`,`use_yn`,`reg_date`) values 
(1,'foray','13bf8529fcb28256f01131cecac11d63d4471119fb9e11fd5bdac6209fb18ee09bfadc879728692c6c455066d929bfd5e3be9aa5d6e3aa5fb7e6a095bca33248','관리자','Y','2026-08-04 01:50:12'),
(2,'test','402df85a5e31845b5672a17ad0b4f7263b9bd7aa1802a7ab15789fc2f63e5f8832bf5ad0aedd05c88e4f6b121cb96eb610d88744557f044778779705f3a873fa','테스터','Y','2026-08-04 03:10:19'),
(3,'byl','bc6393fdd67338add735e092c36d9207787434e5f2e8b6f9712e1c07309d2f9afada37277e85531d711cbcb6531638ad9473e4d854166406a0d4faff6f04eb9d','방예린','Y','2026-08-06 00:22:57');

/*Table structure for table `member` */

DROP TABLE IF EXISTS `member`;

CREATE TABLE `member` (
  `member_no` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` varchar(16) DEFAULT NULL COMMENT '아이디',
  `user_pwd` varchar(64) DEFAULT NULL COMMENT '패스워드',
  `fid` varchar(32) DEFAULT NULL,
  `service` varchar(20) DEFAULT NULL COMMENT '서비스상태(OPERATING,USER_CLOSED,ALL_CLOSED)',
  `fname` varchar(16) DEFAULT NULL,
  `ftel` varchar(16) DEFAULT NULL,
  `faddress1` varchar(32) DEFAULT NULL,
  `faddress2` varchar(64) DEFAULT NULL,
  `regist_num` varchar(16) DEFAULT NULL,
  `mb_name` varchar(16) DEFAULT NULL,
  `mb_id` varchar(16) DEFAULT NULL,
  `mb_hp` varchar(16) DEFAULT NULL,
  `mb_email` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`member_no`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `member` */

insert  into `member`(`member_no`,`user_id`,`user_pwd`,`fid`,`service`,`fname`,`ftel`,`faddress1`,`faddress2`,`regist_num`,`mb_name`,`mb_id`,`mb_hp`,`mb_email`) values 
(1,'foray','fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78','foray','OPERATING','foray',NULL,NULL,NULL,NULL,NULL,'foray',NULL,NULL),
(2,'demo','29838e96a4a6f7f053937dc30924bd2c6b9ef8d68e1a2542a6afaf7ebed1a1f4','demo1','OPERATING',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),
(3,'Xodid9811','d3d31557a6ab23c02cfcfdc7038a2bf1c7565e359839e9d2ac4112e662aa6b85','mapo','ALL_CLOSED',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),
(4,'ljh','17fa2c7ca93fcdc0fd57ef7ecd47d8a503e1365377a529545324e4b9e1c1a23c','ljh','OPERATING',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);

/*Table structure for table `payment` */

DROP TABLE IF EXISTS `payment`;

CREATE TABLE `payment` (
  `payment_no` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '결제 IDX',
  `apply_no` bigint(20) unsigned NOT NULL COMMENT '서비스 신청 IDX (service_apply.apply_idx) - 월구독은 갱신마다 이 FK로 결제행이 추가됨',
  `customer_no` int(11) DEFAULT NULL,
  `pay_method` varchar(20) NOT NULL COMMENT '결제수단(CARD:카드, VBANK:가상계좌, TRANSFER:계좌이체, NONE:무료/이벤트)',
  `pg_provider` varchar(30) DEFAULT NULL COMMENT 'PG사',
  `pg_tid` varchar(100) DEFAULT NULL COMMENT 'PG 거래번호',
  `pay_amount` int(10) unsigned NOT NULL COMMENT '결제금액(신청 시점 실 청구금액 - product.price와 달라질 수 있어 스냅샷으로 보관)',
  `pay_status` varchar(10) NOT NULL DEFAULT 'READY' COMMENT '결제상태(READY:결제대기, PAID:결제완료, FAILED:결제실패, CANCELLED:결제취소, REFUNDED:환불)',
  `paid_at` datetime DEFAULT NULL COMMENT '결제완료일시',
  `cancelled_at` datetime DEFAULT NULL COMMENT '취소/환불일시',
  `fail_reason` varchar(255) DEFAULT NULL COMMENT '실패사유',
  `pg_response` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`payment_no`),
  KEY `idx_pay_status` (`pay_status`),
  KEY `idx_apply` (`apply_no`),
  KEY `idx_payment_customer_no` (`customer_no`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='결제 내역 - service_apply 1건에 결제 N건(월구독 갱신 등)';

/*Data for the table `payment` */

insert  into `payment`(`payment_no`,`apply_no`,`customer_no`,`pay_method`,`pg_provider`,`pg_tid`,`pay_amount`,`pay_status`,`paid_at`,`cancelled_at`,`fail_reason`,`pg_response`,`created_at`,`updated_at`) values 
(1,7,NULL,'NONE',NULL,NULL,0,'PAID','2026-08-11 06:46:53',NULL,NULL,NULL,'2026-08-11 06:46:53','2026-08-11 06:46:53'),
(2,8,NULL,'NONE',NULL,NULL,0,'PAID','2026-08-12 07:34:10',NULL,NULL,NULL,'2026-08-12 07:34:10','2026-08-12 07:34:10'),
(3,9,NULL,'NONE',NULL,NULL,0,'PAID','2026-08-26 05:21:59',NULL,NULL,NULL,'2026-08-26 05:21:59','2026-08-26 05:21:59');

/*Table structure for table `product` */

DROP TABLE IF EXISTS `product`;

CREATE TABLE `product` (
  `product_no` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '상품 IDX',
  `product_code` varchar(30) NOT NULL COMMENT '상품코드',
  `product_type` varchar(10) NOT NULL DEFAULT 'BASE' COMMENT '상품유형(BASE:기본상품, ADDON:부가서비스)',
  `product_name` varchar(100) NOT NULL COMMENT '상품명',
  `price` int(10) unsigned NOT NULL COMMENT '판매금액',
  `list_price` int(10) unsigned DEFAULT NULL COMMENT '정가(원가) - price보다 크게 넣으면 할인 강조 표시, NULL이면 미표시',
  `billing_type` varchar(20) NOT NULL COMMENT '결제방식(MONTHLY:월구독,  ONCE:1회결제, NONE:미결제[이벤트/무통장])',
  `period_day` smallint(5) unsigned NOT NULL COMMENT '사용기간(일-30)',
  `description` varchar(255) DEFAULT NULL COMMENT '설명',
  `status` varchar(10) NOT NULL DEFAULT 'USE' COMMENT '상태(USE:사용, STOP:중지)',
  `is_event` tinyint(1) NOT NULL DEFAULT 0 COMMENT '프로모션상품(1:프로모션, 0: 일반상품)',
  `sort` smallint(5) unsigned NOT NULL DEFAULT 0 COMMENT '정렬',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`product_no`),
  UNIQUE KEY `uk_product_code` (`product_code`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci COMMENT='서비스 상품';

/*Data for the table `product` */

insert  into `product`(`product_no`,`product_code`,`product_type`,`product_name`,`price`,`list_price`,`billing_type`,`period_day`,`description`,`status`,`is_event`,`sort`,`created_at`,`updated_at`) values 
(1,'billing','BASE','중개사 사이트 개설',220000,NULL,'MONTHLY',30,'월구독형 - 2개월 무료','USE',0,0,'2026-08-05 08:08:35','2026-08-11 04:30:28'),
(2,'1month','BASE','중개사 사이트 개설',220000,NULL,'ONCE',30,'1개월 이용권','USE',0,0,'2026-08-05 08:08:35','2026-08-11 04:30:28'),
(3,'3month','BASE','중개사 사이트 개설',630000,660000,'ONCE',90,'월 210,000원','USE',0,0,'2026-08-05 08:08:35','2026-08-11 04:36:04'),
(4,'2month','BASE','중개사 사이트 개설',0,NULL,'NONE',60,'이벤트 상품','USE',1,0,'2026-08-05 08:08:35','2026-08-11 04:30:28'),
(5,'6month','BASE','중개사 사이트 개설',1200000,1320000,'ONCE',180,NULL,'STOP',0,0,'2026-08-11 04:37:26','2026-08-11 04:37:51');

/*Table structure for table `queue` */

DROP TABLE IF EXISTS `queue`;

CREATE TABLE `queue` (
  `queue_no` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '발송 큐 IDX',
  `live_no` bigint(20) unsigned NOT NULL COMMENT '대상 service_live.live_no',
  `server_no` int(10) unsigned NOT NULL COMMENT '배정된 운영서버 (server_list.server_no)',
  `fid` varchar(50) NOT NULL COMMENT '테넌트 식별자',
  `action` varchar(20) NOT NULL DEFAULT 'new',
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '운영서버로 push할 데이터(license_key, product_code 등)' CHECK (json_valid(`payload`)),
  `status` varchar(10) NOT NULL DEFAULT 'PENDING' COMMENT 'PENDING(대기) / SENT(push완료,콜백대기) / DONE(완료) / FAILED(실패)',
  `sent_at` datetime DEFAULT NULL COMMENT 'push 발송 시각',
  `completed_at` datetime DEFAULT NULL COMMENT '콜백으로 완료 확인된 시각',
  `fail_reason` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`queue_no`),
  KEY `idx_status` (`status`),
  KEY `idx_server` (`server_no`,`status`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci COMMENT='승인된 서비스신청을 운영서버로 push하는 중앙 발송 큐 (1회성 이벤트, bomul-api의 batch_schedule과 별개)';

/*Data for the table `queue` */

insert  into `queue`(`queue_no`,`live_no`,`server_no`,`fid`,`action`,`payload`,`status`,`sent_at`,`completed_at`,`fail_reason`,`created_at`,`updated_at`) values 
(1,3,1,'demo1','new','{\"fid\":\"demo1\",\"license_key\":\"1e38c91b53f293c5c8c7e2a49d642ea4\",\"product_code\":\"1month\",\"company_name\":\"데모공인중개사사무소\",\"ceo_name\":\"test\",\"biz_no\":\"123-45-67890\",\"license_no\":\"11110-2024-00099\",\"customer_id\":\"demo\",\"customer_pw\":\"29838e96a4a6f7f053937dc30924bd2c6b9ef8d68e1a2542a6afaf7ebed1a1f4\"}','FAILED','2026-08-11 02:10:01',NULL,'member 등록 실패 - user_id 중복(demo)','2026-08-11 02:09:48','2026-08-11 03:00:02'),
(2,4,1,'mapo','new','{\"fid\":\"mapo\",\"license_key\":\"d90bbba8cbd80b7e65e5686b5047428f\",\"product_code\":\"2month\",\"company_name\":\"마포공인중개사사무소\",\"ceo_name\":\"최언화\",\"biz_no\":\"2826633332\",\"license_no\":\"7645677827\",\"customer_id\":\"Xodid9811\",\"customer_pw\":\"d3d31557a6ab23c02cfcfdc7038a2bf1c7565e359839e9d2ac4112e662aa6b85\"}','DONE','2026-08-12 07:35:02','2026-08-12 07:36:02',NULL,'2026-08-12 07:34:45','2026-08-12 07:36:02'),
(3,4,1,'mapo','status','{\"fid\":\"mapo\",\"status\":\"ALL_CLOSED\"}','DONE','2026-08-22 08:28:01','2026-08-22 08:29:01',NULL,'2026-08-22 08:27:12','2026-08-22 08:29:01'),
(4,5,1,'ljh','new','{\"fid\":\"ljh\",\"license_key\":\"fc01757abdc8b5ef15ac129af720a5f9\",\"product_code\":\"2month\",\"company_name\":\"이정희\",\"ceo_name\":\"이정희\",\"biz_no\":\"11111111111111\",\"license_no\":\"111111111111111\",\"customer_id\":\"ljh\",\"customer_pw\":\"17fa2c7ca93fcdc0fd57ef7ecd47d8a503e1365377a529545324e4b9e1c1a23c\"}','DONE','2026-08-26 05:23:02','2026-08-26 05:24:02',NULL,'2026-08-26 05:22:55','2026-08-26 05:24:02'),
(5,3,1,'demo1','extend','{\"fid\":\"demo1\",\"expire_date\":\"2026-12-31\",\"status\":\"OPERATING\"}','FAILED','2026-08-27 07:09:02',NULL,'member 등록 실패 - payload에 customer_id/customer_pw가 없습니다','2026-08-27 07:08:48','2026-08-27 07:10:01'),
(6,3,1,'demo1','status','{\"fid\":\"demo1\",\"status\":\"OPERATING\"}','DONE','2026-08-27 07:12:01','2026-08-27 07:13:02',NULL,'2026-08-27 07:11:31','2026-08-27 07:13:02');

/*Table structure for table `server_list` */

DROP TABLE IF EXISTS `server_list`;

CREATE TABLE `server_list` (
  `server_no` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '운영서버 IDX',
  `server_code` varchar(30) NOT NULL COMMENT '서버코드(예: OPS01)',
  `server_name` varchar(100) NOT NULL COMMENT '서버명',
  `db_host` varchar(100) NOT NULL COMMENT '접속 호스트',
  `db_port` smallint(5) unsigned NOT NULL DEFAULT 3306 COMMENT '접속 포트',
  `receiver_url` varchar(255) NOT NULL COMMENT 'push 보낼 운영서버 receiver 엔드포인트 (예: https://demo1.bomul.ai.kr/receiver)',
  `api_key` varchar(100) NOT NULL COMMENT '이 서버로 push할 때 쓰는 인증 토큰(Bearer)',
  `tenant_count` smallint(5) unsigned NOT NULL DEFAULT 0 COMMENT '현재 배정된 테넌트 수 - 신규 배정시 least-loaded 판단용',
  `installable_yn` char(1) NOT NULL DEFAULT 'Y' COMMENT '신규 설치 가능 여부(Y/N) - 관리자가 켜고 끄는 토글',
  `status` varchar(10) NOT NULL DEFAULT 'USE' COMMENT '서버 상태(USE:정상, STOP:점검/중지 - 기존 테넌트 포함 전체 가동 여부)',
  `memo` varchar(255) DEFAULT NULL COMMENT '비고',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`server_no`),
  UNIQUE KEY `uk_server_code` (`server_code`),
  KEY `idx_installable` (`installable_yn`,`status`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='매물 서비스 운영서버(테넌트 DB 호스팅) 목록';

/*Data for the table `server_list` */

insert  into `server_list`(`server_no`,`server_code`,`server_name`,`db_host`,`db_port`,`receiver_url`,`api_key`,`tenant_count`,`installable_yn`,`status`,`memo`,`created_at`,`updated_at`) values 
(1,'demo1','demo1 운영서버','localhost',3306,'https://demo1.bomul.ai.kr/receiver','9879e4aeb532c0912d3eb420019e08776be7d540eee4fee1a74b7672ebd306da',9,'Y','USE',NULL,'2026-08-07 01:12:31','2026-08-10 01:16:59');

/*Table structure for table `service_apply` */

DROP TABLE IF EXISTS `service_apply`;

CREATE TABLE `service_apply` (
  `apply_no` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '서비스 신청 IDX',
  `customer_no` int(10) unsigned NOT NULL COMMENT '회원 IDX',
  `product_no` int(10) unsigned NOT NULL COMMENT '상품 IDX - 이벤트(2개월 무료 등) 상품 중복신청 체크용으로도 사용',
  `apply_type` varchar(10) NOT NULL DEFAULT 'NEW' COMMENT '신청유형(NEW:신규, RENEW:연장)',
  `renew_live_no` int(10) unsigned DEFAULT NULL COMMENT '연장 대상 service_live.live_no (apply_type=RENEW일 때만 사용)',
  `company_name` varchar(100) NOT NULL COMMENT '상호명',
  `ceo_name` varchar(50) NOT NULL COMMENT '대표자명',
  `license_no` varchar(50) DEFAULT NULL COMMENT '공인중개사 등록번호',
  `biz_no` varchar(20) DEFAULT NULL COMMENT '사업자등록번호',
  `manager_name` varchar(50) DEFAULT NULL COMMENT '담당자명',
  `manager_phone` varchar(20) NOT NULL COMMENT '담당자 연락처',
  `manager_email` varchar(100) NOT NULL COMMENT '담당자 이메일',
  `tel` varchar(20) DEFAULT NULL COMMENT '대표전화',
  `fax` varchar(20) DEFAULT NULL COMMENT '팩스',
  `zipcode` varchar(10) DEFAULT NULL COMMENT '우편번호',
  `addr1` varchar(255) DEFAULT NULL COMMENT '기본주소',
  `addr2` varchar(255) DEFAULT NULL COMMENT '상세주소',
  `homepage_name` varchar(100) DEFAULT NULL COMMENT '홈페이지명',
  `sub_domain` varchar(100) DEFAULT NULL COMMENT '희망 서브도메인',
  `custom_domain` varchar(255) DEFAULT NULL COMMENT '사용자 도메인',
  `memo` text DEFAULT NULL COMMENT '요청사항',
  `status` varchar(10) NOT NULL DEFAULT 'REQUEST' COMMENT '신청상태 (REQUEST: 서비스 신청 완료,REVIEW : 관리자 검토 중,APPROVED :승인 완료,REJECTED : 승인 거절,CANCEL  : 신청 취소)',
  `request_date` datetime NOT NULL DEFAULT current_timestamp() COMMENT '신청일',
  `approve_date` datetime DEFAULT NULL COMMENT '승인일',
  `reject_reason` varchar(255) DEFAULT NULL COMMENT '반려사유',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`apply_no`),
  KEY `idx_status` (`status`),
  KEY `idx_customer` (`customer_no`),
  KEY `idx_product` (`product_no`),
  KEY `idx_customer_product` (`customer_no`,`product_no`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='서비스 신청';

/*Data for the table `service_apply` */

insert  into `service_apply`(`apply_no`,`customer_no`,`product_no`,`apply_type`,`renew_live_no`,`company_name`,`ceo_name`,`license_no`,`biz_no`,`manager_name`,`manager_phone`,`manager_email`,`tel`,`fax`,`zipcode`,`addr1`,`addr2`,`homepage_name`,`sub_domain`,`custom_domain`,`memo`,`status`,`request_date`,`approve_date`,`reject_reason`,`created_at`,`updated_at`) values 
(1,2,2,'NEW',NULL,'데모공인중개사사무소','test','11110-2024-00099','123-45-67890','홍길동','010-1234-5678','test@example.com',NULL,NULL,NULL,NULL,NULL,NULL,'demo1',NULL,NULL,'APPROVED','2026-08-05 08:12:24','2026-08-11 02:09:48',NULL,'2026-08-05 08:12:24','2026-08-11 02:09:48'),
(2,2,2,'NEW',NULL,'데모공인중개사사무소','test','11110-2024-00099','123-45-67890','홍길동','010-1234-5678','test@example.com',NULL,NULL,NULL,NULL,NULL,NULL,'demo2',NULL,NULL,'APPROVED','2026-08-05 08:41:40','2026-08-10 01:16:59',NULL,'2026-08-05 08:41:40','2026-08-10 01:16:59'),
(3,2,4,'RENEW',3,'데모공인중개사사무소','test','11110-2024-00099','123-45-67890','홍길동','010-1234-5678','test@example.com',NULL,NULL,NULL,NULL,NULL,NULL,'demo1',NULL,'서비스 연장 신청','REJECTED','2026-08-11 05:53:37',NULL,'','2026-08-11 05:53:37','2026-08-12 05:24:49'),
(4,2,4,'RENEW',3,'데모공인중개사사무소','test','11110-2024-00099','123-45-67890','홍길동','010-1234-5678','test@example.com',NULL,NULL,NULL,NULL,NULL,NULL,'demo1',NULL,'서비스 연장 신청','REJECTED','2026-08-11 05:59:01',NULL,'','2026-08-11 05:59:01','2026-08-12 05:24:48'),
(5,2,4,'RENEW',3,'데모공인중개사사무소','test','11110-2024-00099','123-45-67890','홍길동','010-1234-5678','test@example.com',NULL,NULL,NULL,NULL,NULL,NULL,'demo1',NULL,'서비스 연장 신청','REJECTED','2026-08-11 06:08:23',NULL,'','2026-08-11 06:08:23','2026-08-12 05:24:47'),
(7,2,4,'RENEW',3,'데모공인중개사사무소','test','11110-2024-00099','123-45-67890','홍길동','010-1234-5678','test@example.com',NULL,NULL,NULL,NULL,NULL,NULL,'demo1',NULL,'서비스 연장 신청','REJECTED','2026-08-11 06:46:53',NULL,'','2026-08-11 06:46:53','2026-08-12 05:24:45'),
(8,4,4,'NEW',NULL,'마포공인중개사사무소','최언화','7645677827','2826633332','최연화','01047475987','yhchoi5987@naver.com',NULL,NULL,NULL,NULL,NULL,NULL,'mapo',NULL,NULL,'APPROVED','2026-08-12 07:34:10','2026-08-12 07:34:45',NULL,'2026-08-12 07:34:10','2026-08-12 07:34:45'),
(9,5,4,'NEW',NULL,'이정희','이정희','111111111111111','11111111111111','이정희','010-4029-4349','junghele@naver.com',NULL,NULL,NULL,NULL,NULL,NULL,'ljh',NULL,NULL,'APPROVED','2026-08-26 05:21:59','2026-08-26 05:22:55',NULL,'2026-08-26 05:21:59','2026-08-26 05:22:55');

/*Table structure for table `service_live` */

DROP TABLE IF EXISTS `service_live`;

CREATE TABLE `service_live` (
  `live_no` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '라이브 서비스 IDX',
  `customer_no` int(10) unsigned NOT NULL COMMENT '회원 IDX (customer.customer_idx) - 고객당 라이브 서비스 1건 가정',
  `apply_no` bigint(20) unsigned NOT NULL COMMENT '이 라이브를 만든 마지막 승인 신청 (service_apply.apply_idx)',
  `server_no` int(10) unsigned NOT NULL COMMENT '설치 배정된 운영서버 (server_list.server_idx)',
  `fid` varchar(50) NOT NULL COMMENT '테넌트 식별자(2차 도메인)',
  `license_key` varchar(100) DEFAULT NULL COMMENT '발급된 라이선스 키',
  `status` varchar(10) NOT NULL DEFAULT 'ACTIVE' COMMENT '서비스 상태(ACTIVE:정상, SUSPENDED:만료유예, TERMINATED:종료)',
  `install_status` varchar(10) NOT NULL DEFAULT 'PENDING' COMMENT '설치 진행상태(PENDING:설치대기, INSTALLING:설치중, DONE:설치완료, FAILED:설치실패)',
  `install_requested_at` datetime NOT NULL DEFAULT current_timestamp() COMMENT '설치 요청(=신청 승인) 시각',
  `installed_at` datetime DEFAULT NULL COMMENT '설치 완료 시각',
  `install_error` varchar(255) DEFAULT NULL COMMENT '설치 실패 사유',
  `start_date` date DEFAULT NULL COMMENT '서비스 시작일(과금 시작 기준 - 설치완료일과 다를 수 있음)',
  `expire_date` date DEFAULT NULL COMMENT '만료일 - 갱신될 때마다 연장',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `suspended_at` datetime DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '만료유예 상태 변경일',
  PRIMARY KEY (`live_no`),
  UNIQUE KEY `uk_apply` (`apply_no`),
  KEY `idx_status` (`status`),
  KEY `idx_expire` (`expire_date`),
  KEY `idx_apply` (`apply_no`),
  KEY `idx_server_install` (`server_no`,`install_status`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='고객별 현재 라이브 서비스 현황';

/*Data for the table `service_live` */

insert  into `service_live`(`live_no`,`customer_no`,`apply_no`,`server_no`,`fid`,`license_key`,`status`,`install_status`,`install_requested_at`,`installed_at`,`install_error`,`start_date`,`expire_date`,`created_at`,`suspended_at`,`updated_at`) values 
(3,2,1,1,'demo1','1e38c91b53f293c5c8c7e2a49d642ea4','ACTIVE','DONE','2026-08-11 02:08:52',NULL,'member 등록 실패 - user_id 중복(demo)','2026-08-11','2026-12-31','2026-08-11 02:08:52',NULL,'2026-08-27 07:08:48'),
(4,4,8,1,'mapo','d90bbba8cbd80b7e65e5686b5047428f','TERMINATED','DONE','2026-08-12 07:34:45','2026-08-12 07:36:02',NULL,'2026-08-12','2026-10-11','2026-08-12 07:34:45',NULL,'2026-08-22 08:27:12'),
(5,5,9,1,'ljh','fc01757abdc8b5ef15ac129af720a5f9','ACTIVE','DONE','2026-08-26 05:22:55','2026-08-26 05:24:02',NULL,'2026-08-26','2026-10-25','2026-08-26 05:22:55',NULL,'2026-08-26 05:24:02');

/*Table structure for table `setting_queue` */

DROP TABLE IF EXISTS `setting_queue`;

CREATE TABLE `setting_queue` (
  `setting_no` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue_no` bigint(20) unsigned NOT NULL COMMENT '중앙 queue.queue_no - 콜백 보낼 때 이 값으로 매칭',
  `fid` varchar(50) NOT NULL COMMENT '테넌트 식별자',
  `action` varchar(20) NOT NULL DEFAULT 'new',
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'push로 받은 원본 데이터(license_key, product_code 등)' CHECK (json_valid(`payload`)),
  `status` varchar(10) NOT NULL DEFAULT 'PENDING' COMMENT 'PENDING(수신됨,처리대기) / DONE(설치완료) / FAILED(설치실패)',
  `result_message` varchar(255) DEFAULT NULL COMMENT '실패 사유 등',
  `callback_sent_at` datetime DEFAULT NULL COMMENT 'NULL이면 아직 중앙에 콜백 안 보낸 것 - 감시 로직이 이 조건으로 대상을 찾는다',
  `created_at` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'receiver가 push 받은 시각',
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '설치 cron이 처리결과 기록하며 갱신',
  PRIMARY KEY (`setting_no`),
  KEY `idx_status` (`status`),
  KEY `idx_pending_callback` (`status`,`callback_sent_at`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci COMMENT='운영서버 로컬 - receiver가 push 수신 적재, 로컬 cron이 처리, 완료되면 콜백 감시 대상';

/*Data for the table `setting_queue` */

insert  into `setting_queue`(`setting_no`,`queue_no`,`fid`,`action`,`payload`,`status`,`result_message`,`callback_sent_at`,`created_at`,`updated_at`) values 
(1,1,'demo1','new','{\"fid\":\"demo1\",\"license_key\":\"1e38c91b53f293c5c8c7e2a49d642ea4\",\"product_code\":\"1month\",\"company_name\":\"데모공인중개사사무소\",\"ceo_name\":\"test\",\"biz_no\":\"123-45-67890\",\"license_no\":\"11110-2024-00099\",\"customer_id\":\"demo\",\"customer_pw\":\"29838e96a4a6f7f053937dc30924bd2c6b9ef8d68e1a2542a6afaf7ebed1a1f4\",\"queue_no\":1,\"action\":\"new\"}','FAILED','member 등록 실패 - user_id 중복(demo)','2026-08-11 03:00:02','2026-08-11 02:10:01','2026-08-11 03:00:02'),
(2,2,'mapo','new','{\"fid\":\"mapo\",\"license_key\":\"d90bbba8cbd80b7e65e5686b5047428f\",\"product_code\":\"2month\",\"company_name\":\"마포공인중개사사무소\",\"ceo_name\":\"최언화\",\"biz_no\":\"2826633332\",\"license_no\":\"7645677827\",\"customer_id\":\"Xodid9811\",\"customer_pw\":\"d3d31557a6ab23c02cfcfdc7038a2bf1c7565e359839e9d2ac4112e662aa6b85\",\"queue_no\":2,\"action\":\"new\"}','DONE','설치 완료(mapo) / member 등록 완료','2026-08-12 07:36:02','2026-08-12 07:35:02','2026-08-12 07:36:02'),
(3,3,'mapo','status','{\"fid\":\"mapo\",\"status\":\"ALL_CLOSED\",\"queue_no\":3,\"action\":\"status\"}','DONE','member.service=ALL_CLOSED 변경 완료(mapo)','2026-08-22 08:29:01','2026-08-22 08:28:01','2026-08-22 08:29:01'),
(4,4,'ljh','new','{\"fid\":\"ljh\",\"license_key\":\"fc01757abdc8b5ef15ac129af720a5f9\",\"product_code\":\"2month\",\"company_name\":\"이정희\",\"ceo_name\":\"이정희\",\"biz_no\":\"11111111111111\",\"license_no\":\"111111111111111\",\"customer_id\":\"ljh\",\"customer_pw\":\"17fa2c7ca93fcdc0fd57ef7ecd47d8a503e1365377a529545324e4b9e1c1a23c\",\"queue_no\":4,\"action\":\"new\"}','DONE','설치 완료(ljh) / member 등록 완료','2026-08-26 05:24:02','2026-08-26 05:23:02','2026-08-26 05:24:02'),
(5,5,'demo1','extend','{\"fid\":\"demo1\",\"expire_date\":\"2026-12-31\",\"status\":\"OPERATING\",\"queue_no\":5,\"action\":\"extend\"}','FAILED','member 등록 실패 - payload에 customer_id/customer_pw가 없습니다','2026-08-27 07:10:01','2026-08-27 07:09:02','2026-08-27 07:10:01'),
(6,6,'demo1','status','{\"fid\":\"demo1\",\"status\":\"OPERATING\",\"queue_no\":6,\"action\":\"status\"}','DONE','member.service=OPERATING 변경 완료(demo1)','2026-08-27 07:13:02','2026-08-27 07:12:01','2026-08-27 07:13:02');

/*Table structure for table `visit_conversion` */

DROP TABLE IF EXISTS `visit_conversion`;

CREATE TABLE `visit_conversion` (
  `visit_conversion_no` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `visitor_token` varchar(64) NOT NULL COMMENT '가입 시점 쿠키에 있던 방문자 토큰 - visit_log 조인용',
  `customer_no` int(10) unsigned NOT NULL COMMENT '가입 완료된 회원 (customer.customer_no)',
  `created_at` datetime NOT NULL DEFAULT current_timestamp() COMMENT '가입 완료 시각',
  PRIMARY KEY (`visit_conversion_no`),
  UNIQUE KEY `uk_customer_no` (`customer_no`),
  KEY `idx_visitor_token` (`visitor_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='방문자 토큰 - 가입회원 매핑. visit_log와 조인해 검색어별 가입 전환율 산출';

/*Data for the table `visit_conversion` */

/*Table structure for table `visit_log` */

DROP TABLE IF EXISTS `visit_log`;

CREATE TABLE `visit_log` (
  `visit_log_no` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `visitor_token` varchar(64) NOT NULL COMMENT '익명 방문자 식별 토큰(쿠키) - 랜덤값, 개인정보 아님',
  `page_path` varchar(255) NOT NULL COMMENT '요청 경로 (예: /, /qna)',
  `referrer_host` varchar(100) DEFAULT NULL COMMENT '리퍼러 호스트만 저장 (예: search.naver.com) - 전체 URL 미저장',
  `search_keyword` varchar(200) DEFAULT NULL COMMENT '검색엔진 리퍼러 쿼리스트링에서 추출한 검색어 (네이버 query, 구글 q 등)',
  `utm_source` varchar(100) DEFAULT NULL COMMENT '마케팅 링크 utm_source',
  `utm_medium` varchar(100) DEFAULT NULL COMMENT '마케팅 링크 utm_medium',
  `utm_campaign` varchar(100) DEFAULT NULL COMMENT '마케팅 링크 utm_campaign',
  `device_type` varchar(10) NOT NULL DEFAULT 'PC' COMMENT 'PC/MOBILE - User-Agent 원본은 저장하지 않고 분류값만 저장',
  `is_landing` char(1) NOT NULL DEFAULT 'N' COMMENT '해당 방문자의 방문 세션 내 첫 페이지뷰 여부(Y/N) - 유입경로/검색어 집계는 Y행만 사용',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`visit_log_no`),
  KEY `idx_visitor_token` (`visitor_token`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_landing_keyword` (`is_landing`,`search_keyword`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='www 방문 로그 - 검색어/유입경로 집계용';

/*Data for the table `visit_log` */

insert  into `visit_log`(`visit_log_no`,`visitor_token`,`page_path`,`referrer_host`,`search_keyword`,`utm_source`,`utm_medium`,`utm_campaign`,`device_type`,`is_landing`,`created_at`) values 
(1,'b184cc18e7f8ad89de67c4cc50e50d99','/',NULL,NULL,NULL,NULL,NULL,'PC','Y','2026-08-27 05:05:55'),
(2,'b184cc18e7f8ad89de67c4cc50e50d99','/',NULL,NULL,NULL,NULL,NULL,'PC','N','2026-08-27 05:06:04'),
(3,'b184cc18e7f8ad89de67c4cc50e50d99','/',NULL,NULL,NULL,NULL,NULL,'PC','N','2026-08-27 07:02:37'),
(4,'b184cc18e7f8ad89de67c4cc50e50d99','/',NULL,NULL,NULL,NULL,NULL,'PC','N','2026-08-27 07:02:43'),
(5,'b184cc18e7f8ad89de67c4cc50e50d99','/',NULL,NULL,NULL,NULL,NULL,'PC','N','2026-08-27 07:02:45'),
(6,'b184cc18e7f8ad89de67c4cc50e50d99','/',NULL,NULL,NULL,NULL,NULL,'PC','N','2026-08-27 07:03:09');

/*Table structure for table `visit_stat_daily` */

DROP TABLE IF EXISTS `visit_stat_daily`;

CREATE TABLE `visit_stat_daily` (
  `visit_stat_daily_no` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `stat_date` date NOT NULL COMMENT '집계 대상일',
  `search_keyword` varchar(200) NOT NULL DEFAULT '' COMMENT '검색어 (visit_log.search_keyword, 없으면 빈 문자열) - UNIQUE KEY가 NULL을 서로 다른 값으로 취급해 중복행이 생기는 걸 막기 위해 NOT NULL',
  `referrer_host` varchar(100) NOT NULL DEFAULT '' COMMENT '리퍼러 호스트 (visit_log.referrer_host, 없으면 빈 문자열 - 직접방문 등)',
  `visit_count` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '해당일 해당 검색어/유입경로의 방문자수 (is_landing=Y distinct visitor_token)',
  `signup_count` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '그 중 가입 전환된 방문자수 (visit_conversion 조인)',
  `created_at` datetime NOT NULL DEFAULT current_timestamp() COMMENT '집계 적재 시각',
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '재집계로 갱신된 시각',
  PRIMARY KEY (`visit_stat_daily_no`),
  UNIQUE KEY `uk_stat_date_keyword_host` (`stat_date`,`search_keyword`,`referrer_host`),
  KEY `idx_stat_date` (`stat_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='방문/검색어/가입전환 일별 집계 - visit_log 배치 집계 결과, 관리자 통계 화면 조회용';

/*Data for the table `visit_stat_daily` */

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
