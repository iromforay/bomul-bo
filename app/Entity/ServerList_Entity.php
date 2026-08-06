<?php

namespace App\Entity;

// app/sql/2026_08_05_server_list_table.sql 의 server_list 테이블 매핑 - 테넌트 DB를 호스팅하는
// 운영서버 목록. installable_yn으로 신규 배정 가능 여부를, status로 서버 자체의 가동 여부를 관리한다.
class ServerList_Entity
{
    private $server_no;

    private $server_code;
    private $server_name;

    private $db_host;
    private $db_port;

    private $receiver_url;
    private $api_key;

    private $tenant_count;

    private $installable_yn;
    private $status;

    private $memo;

    private $created_at;
    private $updated_at;

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
    public function getServerCode()
    {
        return $this->server_code;
    }

    /**
     * @param mixed $server_code
     */
    public function setServerCode($server_code): void
    {
        $this->server_code = $server_code;
    }

    /**
     * @return mixed
     */
    public function getServerName()
    {
        return $this->server_name;
    }

    /**
     * @param mixed $server_name
     */
    public function setServerName($server_name): void
    {
        $this->server_name = $server_name;
    }

    /**
     * @return mixed
     */
    public function getDbHost()
    {
        return $this->db_host;
    }

    /**
     * @param mixed $db_host
     */
    public function setDbHost($db_host): void
    {
        $this->db_host = $db_host;
    }

    /**
     * @return mixed
     */
    public function getDbPort()
    {
        return $this->db_port;
    }

    /**
     * @param mixed $db_port
     */
    public function setDbPort($db_port): void
    {
        $this->db_port = $db_port;
    }

    // push 보낼 운영서버 receiver 엔드포인트 (예: https://demo1.bomul.ai.kr/receiver)
    /**
     * @return mixed
     */
    public function getReceiverUrl()
    {
        return $this->receiver_url;
    }

    /**
     * @param mixed $receiver_url
     */
    public function setReceiverUrl($receiver_url): void
    {
        $this->receiver_url = $receiver_url;
    }

    // 이 서버로 push할 때 쓰는 인증 토큰(Bearer)
    /**
     * @return mixed
     */
    public function getApiKey()
    {
        return $this->api_key;
    }

    /**
     * @param mixed $api_key
     */
    public function setApiKey($api_key): void
    {
        $this->api_key = $api_key;
    }

    // 현재 배정된 테넌트 수 - 신규 배정시 least-loaded 판단용
    /**
     * @return mixed
     */
    public function getTenantCount()
    {
        return $this->tenant_count;
    }

    /**
     * @param mixed $tenant_count
     */
    public function setTenantCount($tenant_count): void
    {
        $this->tenant_count = $tenant_count;
    }

    // 신규 설치 가능 여부(Y/N)
    /**
     * @return mixed
     */
    public function getInstallableYn()
    {
        return $this->installable_yn;
    }

    /**
     * @param mixed $installable_yn
     */
    public function setInstallableYn($installable_yn): void
    {
        $this->installable_yn = $installable_yn;
    }

    // USE(정상) / STOP(점검·중지)
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

    /**
     * @return mixed
     */
    public function getMemo()
    {
        return $this->memo;
    }

    /**
     * @param mixed $memo
     */
    public function setMemo($memo): void
    {
        $this->memo = $memo;
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
