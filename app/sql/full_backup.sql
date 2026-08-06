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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='범용 배치 진행상황 모니터링';

/*Data for the table `batch_schedule` */

insert  into `batch_schedule`(`id`,`job_type`,`run_date`,`fid`,`status`,`sent_at`,`callback_at`,`created_at`) values 
(1,'analytics_daily','2026-07-29','foray','done','2026-07-30 02:19:13','2026-07-30 02:19:13','2026-07-30 02:19:13'),
(2,'analytics_daily','2026-08-03','foray','sent','2026-08-04 02:00:02',NULL,'2026-08-04 02:00:02');

/*Table structure for table `customer` */

DROP TABLE IF EXISTS `customer`;

CREATE TABLE `customer` (
  `customer_no` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` varchar(50) NOT NULL COMMENT '로그인 아이디',
  `customer_pw` char(64) NOT NULL COMMENT 'SHA-256 해시(hex) - 중앙 member.user_pwd 이관 호환',
  `customer_name` varchar(50) NOT NULL COMMENT '담당자명',
  `company_name` varchar(100) NOT NULL COMMENT '중개사무소명',
  `biz_reg_no` varchar(20) NOT NULL COMMENT '사업자등록번호',
  `broker_reg_no` varchar(30) NOT NULL COMMENT '중개사무소등록번호 (공인중개사법 제18조의2)',
  `phone` varchar(20) NOT NULL COMMENT '휴대폰번호',
  `email` varchar(100) NOT NULL COMMENT '이메일',
  `biz_reg_verified_yn` char(1) NOT NULL DEFAULT 'N' COMMENT '사업자등록번호 검증여부 Y/N',
  `broker_reg_verified_yn` char(1) NOT NULL DEFAULT 'N' COMMENT '중개사무소등록번호 검증여부 Y/N',
  `use_yn` char(1) NOT NULL DEFAULT 'Y' COMMENT '사용여부 Y/N',
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `customer` */

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
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

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
(51,'bc661b01124300e9ac3180068060ffc4520e8594c766509729454ad5272e67c3','a:14:{s:9:\"member_no\";s:1:\"1\";s:7:\"user_id\";s:5:\"foray\";s:8:\"user_pwd\";s:64:\"fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78\";s:3:\"fid\";s:5:\"foray\";s:7:\"service\";s:1:\"Y\";s:5:\"fname\";s:5:\"foray\";s:4:\"ftel\";N;s:9:\"faddress1\";N;s:9:\"faddress2\";N;s:10:\"regist_num\";N;s:7:\"mb_name\";N;s:5:\"mb_id\";s:5:\"foray\";s:5:\"mb_hp\";N;s:8:\"mb_email\";N;}','foray',NULL,'2026-08-04 02:54:59');

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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `manager` */

insert  into `manager`(`manager_no`,`manager_id`,`manager_pw`,`manager_name`,`use_yn`,`reg_date`) values 
(1,'foray','13bf8529fcb28256f01131cecac11d63d4471119fb9e11fd5bdac6209fb18ee09bfadc879728692c6c455066d929bfd5e3be9aa5d6e3aa5fb7e6a095bca33248','관리자','Y','2026-08-04 01:50:12'),
(2,'test','402df85a5e31845b5672a17ad0b4f7263b9bd7aa1802a7ab15789fc2f63e5f8832bf5ad0aedd05c88e4f6b121cb96eb610d88744557f044778779705f3a873fa','테스터','Y','2026-08-04 03:10:19');

/*Table structure for table `member` */

DROP TABLE IF EXISTS `member`;

CREATE TABLE `member` (
  `member_no` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` varchar(16) DEFAULT NULL COMMENT '아이디',
  `user_pwd` varchar(64) DEFAULT NULL COMMENT '패스워드',
  `fid` varchar(32) DEFAULT NULL,
  `service` varchar(4) DEFAULT NULL COMMENT '서비스여부(Y,N)',
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `member` */

insert  into `member`(`member_no`,`user_id`,`user_pwd`,`fid`,`service`,`fname`,`ftel`,`faddress1`,`faddress2`,`regist_num`,`mb_name`,`mb_id`,`mb_hp`,`mb_email`) values 
(1,'foray','fb90e380976a9d5e84d41bf80eaced96d81026c6f5a4fe43b9c70a26ebbdaf78','foray','Y','foray',NULL,NULL,NULL,NULL,NULL,'foray',NULL,NULL);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
