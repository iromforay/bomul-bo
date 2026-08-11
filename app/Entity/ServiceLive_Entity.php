<?php

namespace App\Entity;

// app/sql/2026_08_05_service_live_table.sql 의 service_live 테이블 매핑 - 고객별 "지금 라이브 상태"의
// 유일한 진실 소스. service_apply/payment는 그 상태가 어떻게 만들어졌는지의 이력(로그)일 뿐이고,
// 여기는 결제정보 없이 현재값(만료일/서비스여부/설치서버)만 담는다.
class ServiceLive_Entity
{
    private $live_no;

    private $customer_no;
    private $apply_no;
    private $server_no;

    private $fid;
    private $license_key;

    private $status;
    private $suspended_at;

    private $install_status;
    private $install_requested_at;
    private $installed_at;
    private $install_error;

    private $start_date;
    private $expire_date;

    private $created_at;
    private $updated_at;

    /**
     * @return mixed
     */
    public function getLiveNo()
    {
        return $this->live_no;
    }

    /**
     * @param mixed $live_no
     */
    public function setLiveNo($live_no): void
    {
        $this->live_no = $live_no;
    }

    /**
     * @return mixed
     */
    public function getCustomerNo()
    {
        return $this->customer_no;
    }

    /**
     * @param mixed $customer_no
     */
    public function setCustomerNo($customer_no): void
    {
        $this->customer_no = $customer_no;
    }

    /**
     * @return mixed
     */
    public function getApplyNo()
    {
        return $this->apply_no;
    }

    /**
     * @param mixed $apply_no
     */
    public function setApplyNo($apply_no): void
    {
        $this->apply_no = $apply_no;
    }

    /**
     * @return mixed
     */
    public function getServerNo()
    {
        return $this->server_no;
    }

    /**
     * @param mixed $server_no
     */
    public function setServerNo($server_no): void
    {
        $this->server_no = $server_no;
    }

    /**
     * @return mixed
     */
    public function getFid()
    {
        return $this->fid;
    }

    /**
     * @param mixed $fid
     */
    public function setFid($fid): void
    {
        $this->fid = $fid;
    }

    /**
     * @return mixed
     */
    public function getLicenseKey()
    {
        return $this->license_key;
    }

    /**
     * @param mixed $license_key
     */
    public function setLicenseKey($license_key): void
    {
        $this->license_key = $license_key;
    }

    // REQUEST(요청 - service:collect-request 크론이 픽업해 큐에 세팅 적재) / ACTIVE(정상) /
    // SUSPENDED(유예) / TERMINATED(종료)
    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status): void
    {
        $this->status = $status;
    }

    // status가 SUSPENDED로 바뀐 시점 - "유예 15일" 유예기간 만료(→TERMINATED) 기준점.
    // ACTIVE로 되돌아가면 NULL로 초기화된다 (ServiceLiveModel::updateStatus() 참고).
    /**
     * @return mixed
     */
    public function getSuspendedAt()
    {
        return $this->suspended_at;
    }

    /**
     * @param mixed $suspended_at
     */
    public function setSuspendedAt($suspended_at): void
    {
        $this->suspended_at = $suspended_at;
    }

    // PENDING / INSTALLING / DONE / FAILED
    /**
     * @return mixed
     */
    public function getInstallStatus()
    {
        return $this->install_status;
    }

    /**
     * @param mixed $install_status
     */
    public function setInstallStatus($install_status): void
    {
        $this->install_status = $install_status;
    }

    /**
     * @return mixed
     */
    public function getInstallRequestedAt()
    {
        return $this->install_requested_at;
    }

    /**
     * @param mixed $install_requested_at
     */
    public function setInstallRequestedAt($install_requested_at): void
    {
        $this->install_requested_at = $install_requested_at;
    }

    /**
     * @return mixed
     */
    public function getInstalledAt()
    {
        return $this->installed_at;
    }

    /**
     * @param mixed $installed_at
     */
    public function setInstalledAt($installed_at): void
    {
        $this->installed_at = $installed_at;
    }

    /**
     * @return mixed
     */
    public function getInstallError()
    {
        return $this->install_error;
    }

    /**
     * @param mixed $install_error
     */
    public function setInstallError($install_error): void
    {
        $this->install_error = $install_error;
    }

    /**
     * @return mixed
     */
    public function getStartDate()
    {
        return $this->start_date;
    }

    /**
     * @param mixed $start_date
     */
    public function setStartDate($start_date): void
    {
        $this->start_date = $start_date;
    }

    /**
     * @return mixed
     */
    public function getExpireDate()
    {
        return $this->expire_date;
    }

    /**
     * @param mixed $expire_date
     */
    public function setExpireDate($expire_date): void
    {
        $this->expire_date = $expire_date;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @param mixed $created_at
     */
    public function setCreatedAt($created_at): void
    {
        $this->created_at = $created_at;
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * @param mixed $updated_at
     */
    public function setUpdatedAt($updated_at): void
    {
        $this->updated_at = $updated_at;
    }
}
