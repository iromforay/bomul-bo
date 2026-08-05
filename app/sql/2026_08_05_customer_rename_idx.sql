-- 스키마 통일성(전 테이블 PK를 *_idx로 통일) 마이그레이션 - customer.customer_no -> customer_idx
-- 이미 운영중인 customer 테이블(app/sql/full_backup.sql 덤프 기준)에 실제로 적용해야 하는 ALTER문.
-- full_backup.sql은 과거 시점 백업이라 직접 고치지 않고, 여길 별도로 실행한다.

ALTER TABLE `customer`
    CHANGE COLUMN `customer_no` `customer_idx` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;
