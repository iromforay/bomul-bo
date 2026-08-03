<?php

namespace App\Models;

use CodeIgniter\Model;
class CI_Model extends Model
{
    protected $db;
    protected $table;
    protected $primaryKey;
    protected $query;
    protected $lastError;
    protected $lastAffectedRows;
    /**
     * @param $db
     */
    public function __construct($db,$table,$primaryKey)
    {
        $this->db = $db;
        $this->table=$table;
        $this->primaryKey= $primaryKey;
       // dd($this->db);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    public function getQuery()
    {
        return $this->query;
    }

    // safeExecQuery() 내부, transCommit() 이전 시점에 캡처해둔 값. transCommit() 이후엔
    // error()/affectedRows()가 COMMIT 자체의 결과로 덮어써져서 여기서 다시 조회하면 안 됨.
    public function getDbError()
    {
        return $this->lastError;
    }

    public function getAffectedRows()
    {
        return $this->lastAffectedRows;
    }
    public function get_list($start=0,$limit=0)
    {
        $_limit="";
        if($limit>0 and $start>0){
            $_limit = " LIMIT $start,$limit ";
        }
        $sql = "SELECT * FROM $this->table ORDER BY $this->primaryKey desc ".$_limit ;

        $rows = $this->getExecQuery($sql);
        return $rows;
    }
    public function findbyid($seq_no)
    {
        $sql="SELECT * FROM $this->table WHERE $this->primaryKey = ".$seq_no;
        $rows = $this->getExecQuery($sql);

        return $rows;
    }
    public function get_count()
    {
        $sql = "SELECT count(*) as 'cnt' FROM ".$this->table;
        $rows = $this->getExecQuery($sql);
        return $rows[0]['cnt'];

    }

    public function safeExecQuery($sql)
    {
        $seq_no=0;
        $deny_query = array("DROP","ALTER","CREATE");
        // \b(단어경계)를 안 걸면 created_at/is_deleted처럼 이 키워드를 부분포함하는
        // 흔한 컬럼명만으로도 오탐(false positive)이 나서 정상 쿼리가 막힘.
        $deny_str = "/\\b(?:".implode("|",$deny_query).")\\b/";
        $SQL = strtoupper($sql);
        $this->query=$sql;
        preg_match_all ("$deny_str" ,trim($SQL),$result); //$sql

        if(count($result[0]) >1){ //사용 불가 쿼리문 포함.
            //   print_r($result);
            d("사용불가 쿼리문 포함"); d($result);
            return $this;
        }else{
            $this->db->query($sql);
            $this->lastError = $this->db->error();
            $this->lastAffectedRows = $this->db->affectedRows();
            preg_match ("/INSERT/" ,trim($SQL),$insertquery); //$sql
            preg_match ("/UPDATE/" ,trim($SQL),$updatequery); //$sql
            preg_match ("/DELETE/" ,trim($SQL),$deletequery); //$sql

            if(isset($insertquery[0])){
                return $this->db->insertID();
            }else if(isset($updatequery[0]) or isset($deletequery)){
                return $this->lastError['code'] == 0;
            }

            if($this->lastError['code'] != 0) {
                return false;
            }else return true;
        }
    }
    public function getExecQuery($sql)
    {
        $deny_query = array("INSERT","DELETE","UPDATE","ALTER","CREATE");
        // \b(단어경계)를 안 걸면 created_at/is_deleted처럼 이 키워드를 부분포함하는
        // 흔한 컬럼명만으로도 오탐(false positive)이 나서 정상 쿼리가 막힘.
        $deny_str = "/\\b(?:".implode("|",$deny_query).")\\b/";
        $SQL = strtoupper($sql);
        $this->query=$sql;
        preg_match_all ("$deny_str" ,trim($SQL),$result); //$sql

        if(count($result[0]) >1){ //사용 불가 쿼리문 포함.
            //   print_r($result);
            d("사용불가 쿼리문 포함"); d($result);
            return $this;
        }else{
            $query = $this->db->query($sql);
            $rows = $query->getResultArray();
            return $rows;
        }
    }
}