<?php

namespace App\Service;

use App\Entity\Add_menu_Entity;
use App\Entity\Basic_Entity;
use App\Entity\Img_Entity;
use App\Libraries\AT_Result;
use App\Libraries\Curl;
use App\Libraries\File_Upload;

class BasicService extends CI_Service
{
    private $model;
    private $memberModel;
    private $entity;
    private $row;
    private $log;
    public function __construct($log="",$session_id="")
    {
        $this->log = $log;
        parent::__construct($session_id);
        $this->entity = new Basic_Entity();
        $this->model = model('App\Models\BasicModel',true,$this->db);
        $this->helpModel = model('App\Models\HelpModel',true,$this->db);
        return $this;
    }

    // 저장된 기본설정 원본row. 필수값 검증에서 "이번 요청엔 없지만 기존에 저장돼 있는 값"을 판단할 때 사용.
    public function get_row()
    {
        $rows = $this->model->findbyid(1);
        return $rows[0] ?? [];
    }

    public function get_by_basic()
    {
        $rows = $this->model->findbyid(1);

        $data = $rows[0];

        if(isset($data['logo_dir']) and isset($data['logo_name'])){
            $data['logo_url'] = SITE_PDS.$data['logo_dir'].$data['logo_name'];
        }
        $data['sub_menu_color'] = $data['sub_menu_focus_color'];
        unset($data['sub_menu_focus_color']);
        unset($data['logo_dir']); unset($data['logo_name']); unset($data['basic_no']);

        $add_model = model('App\Models\AddMenuModel',true,$this->db);

        $add_data = $add_model->findbyCodeandShow('basic',"Y");
        foreach($add_data as $k=>$v){

            $v['link'] = $v['text'];
            $v['img_url'] = (isset($v['img_dir']) and isset($v['img_name']) )?  SITE_PDS.$v['img_dir'].$v['img_name']: "";

            unset($v['code']); unset($v['text']); unset($v['show']);
            unset($v['text']);  unset($v['write_date']); unset($v['add_menu_no']);
            unset($v['img_dir']); unset($v['img_name']);
            $add_data[$k] = $v;
        }
        $data['sns_link'] = $add_data;

        return $data;
    }
    // 기본설정 저장
    public function save($data,$file,$sns_file)
    {
    //사이트 로고 설정
        $rows = $this->model->findbyid(1);


        $this->row = isset($rows[0]) ? $rows[0] : "";

        //사이트명, 로고설정
        $this->log->info("사이트명, 로고설정");
        $this->sitename_and_logo($data,$file);
        //메뉴&페이지 설정
        $this->log->info("메뉴&페이지 설정");
        $this->menu_setting($data);


        //하단 고정메뉴설정
        $this->log->info("하단 고정메뉴설정");
        $this->button_menu($data);

        //타 사이트 링크 메뉴 설정
        $this->log->info("타 사이트 링크 메뉴 설정");
        $this->sns_link_save($data,$sns_file);

        //중개사무소 법정 표시항목 설정
        $this->log->info("중개사무소 정보 설정");
        $this->broker_setting($data);

        //푸터 설정
        $this->log->info("푸터 설정");
        $this->footer_setting($data);

        $this->entity->setBasicNo(1);
        if($this->model->sql_edit($this->entity)){
            $this->log->info("query :".$this->model->getQuery()." ture");
            return AT_Result::toSuccessData();
        }else{
            $this->log->info("query :".$this->model->getQuery()." false");
            return AT_Result::toFailData();
        }
        exit;

    }

    // 네이버 예약위젯 노출여부/사이트 인증코드만 부분저장. save()와 달리 basic 폼 전체(중개사무소 정보 등) 없이 단독 호출 가능.
    public function save_verification_code($data)
    {
        $entity = new Basic_Entity();
        $entity->setBasicNo(1);
        $entity->setNaverReserveShow($data['naver_reserve_show'] ?? null);
        $entity->setNaverSiteVerification($data['naver_site_verification'] ?? null);

        if($this->model->sql_edit($entity)){
            $this->log->info("query :".$this->model->getQuery()." ture");
            return AT_Result::toSuccessData();
        }else{
            $this->log->info("query :".$this->model->getQuery()." false");
            return AT_Result::toFailData();
        }
    }

    //사이트명,로고 설정
    public function sitename_and_logo($data,$file)
    {
    //사이트명
        $this->entity->setSiteName($data['site_name']);

        /*파일 등록*/
        if(isset($file['name']) and $file['name'] !="") {//파일 있음

            $oResult = $this->file_upload($file);

            if($oResult['SUCCESS'] == "TRUE") {
                if($oResult['RECORD']['file_path'] !="") $this->entity->setLogoDir($oResult['RECORD']['file_path']);
                if($oResult['RECORD']['file_name'] !="") $this->entity->setLogoName($oResult['RECORD']['file_name']);
            }
            else{ //등록파일 업로드 실패
                return AT_Result::toFailData($oResult['MSG']);
            }
            if(isset($img[0]['img_no']) and $img[0]['img_no']>0) { //로고 추가인데, 기존 자료가 있을때, 기존 파일 삭제
                $file['img_dir'] = $this->row['logo_dir'];
                $file['img_name'] = $this->row['logo_name'];
                $this->file_del($file);
            }
            //저장 또는 수정
        }else{
            $img_entity = new Img_Entity();
            if(isset($data['logo_del']) and $data['logo_del']=="Y"){ //기존파일 삭제일때
                //파일 실삭제
                $file['img_dir'] = $this->row['logo_dir'];
                $file['img_name'] = $this->row['logo_name'];
                $this->file_del($file);

                $this->entity->setLogoDir("");
                $this->entity->setLogoName("");
            }else { //유지
                //기존 데이터 조회
                $this->entity->setLogoDir($this->row['logo_dir']??"");
                $this->entity->setLogoName($this->row['logo_name']??"");
            }
        }

        $this->entity->setLogoShow($data['logo_show']);
        return $this;
    }
    //각 메뉴별 활성여부 세팅및 폰트색상
    public function menu_setting($data)
    {
        $this->entity->setMainMenuColor($data['main_menu_color']);
        $this->entity->setSubMenuFocusColor($data['sub_menu_focus_color']);

        $this->entity->setMenuBgColor($data['menu_back_color']);
        $this->entity->setMenuBgTransparent($data['menu_back_transparent']);

        $this->log->info("푸터 설정");
        $this->entity->setNoticeShow($data['notice_show']);
        $this->entity->setAroundShow($data['around_show']);
        $this->entity->setChargeShow($data['charge_show']);
        $this->entity->setReviewShow($data['review_show']);
        $this->entity->setVodShow($data['vod_show']);
        $this->entity->setMapShow($data['map_show']);
        $this->entity->setPropertyShow($data['property_show']);

        return $this;

    }
    public function button_menu($data)
    {
        $this->entity->setNaverReserve($data['naver_reserve'] );
        $this->entity->setNaverReserveShow($data['naver_reserve_show']);
        $this->entity->setKakaoChannel($data['kakao_channel'] );
        $this->entity->setKakaoChannelShow($data['kakao_channel_show']);

        return $this;
    }
    // 공인중개사법 제18조의2 - 중개사무소/개업공인중개사 법정 표시항목
    public function broker_setting($data)
    {
        $this->entity->setBrokerOfficeName($data['broker_office_name']);
        $this->entity->setBrokerAddress($data['broker_address']);
        $this->entity->setBrokerPhone($data['broker_phone']);
        $this->entity->setBrokerRegNo($data['broker_reg_no']);
        $this->entity->setBrokerCeoName($data['broker_ceo_name']);
        $this->entity->setBrokerAgentName($data['broker_agent_name']);
        $this->entity->setBrokerAgentPhone($data['broker_agent_phone']);

        return $this;
    }

    public function footer_setting($data)
    {
        $this->entity->setCompanyName($data['company_name']);
        $this->entity->setFooterCopy($data['footer_copy']);
        // 네이버 서치어드바이저 사이트 소유권 확인용 코드 - 등록 안 했으면 비워두면 됨
        $this->entity->setNaverSiteVerification($data['naver_site_verification'] ?? null);

        return $this;
    }
    public function sns_link_save($data,$sns_file)
    {
        $this->del_sns_file($data['sns_del_key']);
        $add_model = model('App\Models\AddMenuModel',true,$this->db);
        //$sns_file
        $this->entity->setLinkMenuShow( $data['link_menu_show']);

        //add_menu
        //수정하기
        if(isset($data['sns_key'])) {
            foreach ($data['sns_key'] as $k => $v) {
                $entity = new Add_menu_Entity();
                $entity->setCode("basic");
                $link = $data['sns_link'][$k];
                $entity->setText($link);
                if (isset($sns_file['name'][$k])) {
                    $files['name'] = $sns_file['name'][$k];
                    $files['type'] = $sns_file['type'][$k];
                    $files['tmp_name'] = $sns_file['tmp_name'][$k];
                    $files['error'] = $sns_file['error'][$k];
                    $files['size'] = $sns_file['size'][$k];
                } else $files = null;
                $this->log->info("sns_link_save file upload");
                if ($v != "") { //수정
                    $entity->setKey($v);
                    $row = $add_model->findbyCodeAndKey($entity->getCode(), $entity->getKey());
                    $entity->setAddMenuNo($row['add_menu_no']);
                    $this->log->info("수정/sns_link_save add_menu_no: ".$row['add_menu_no']);

                } else {//추가... 키 할당
                    // 등록할 키값 선정.
                    $row = $add_model->findByCode_order_idx_asc_limitone($entity->getCode());
                    if(! isset($row['add_menu_no'])) { //저장 코드가 없음.
                        return AT_Result::toFailData("사이트 링크 메뉴는 최대 5개 까지 설정 가능합니다.");
                        exit;
                    }
                    $entity->setKey($row['key']);
                    $entity->setAddMenuNo($row['add_menu_no']);
                    $this->log->info("추가/sns_link_save add_menu_no: ".$row['add_menu_no']);
                }

                //이미지 처리
                if (isset($files['name']) and $files['name'] != "") {//파일 있음
                    $this->log->info("파일등록");
                    $oResult = $this->file_upload($files);

                    if ($oResult['SUCCESS'] == "TRUE") {
                        //기존 파일 삭제
                        //파일 실삭제
                        $file['img_dir'] = $row['img_dir'];
                        $file['img_name'] = $row['img_name'];
                        $this->file_del($file);

                        if ($oResult['RECORD']['file_path'] != "") $entity->setImgDir($oResult['RECORD']['file_path']);
                        if ($oResult['RECORD']['file_name'] != "") $entity->setImgName($oResult['RECORD']['file_name']);
                    } else { //등록파일 업로드 실패
                        return AT_Result::toFailData($oResult['MSG']);
                    }
                } else {//파일 없음
                    $this->log->info("파일 없음");
                    //등록파일 없을땐 현상유지 .. 삭제는 따로 진행.
                    //기존 데이터 조회
                    $entity->setImgDir($row['img_dir'] ?? "");
                    $entity->setImgName($row['img_name'] ?? "");

                }
                $entity->setShow('Y');
                $add_model->sql_edit($entity);
                $this->log->info("add_menu query:".$add_model->getQuery());
            }
        }

        return $this;
    }

    public function del_sns_file($del_file)
    {
        if(! isset($del_file) or count($del_file)<=0) return $this;
        $add_model = model('App\Models\AddMenuModel',true,$this->db);
        foreach($del_file as $k=>$v){
            if($v!=""){
                $entity =  new Add_menu_Entity();
                $entity->setCode("basic");
                $entity->setKey($v);
                $entity->setShow('N');
                $row = $add_model->findbyCodeAndKey($entity->getCode(),$entity->getKey());

                if(isset($row['img_dir']) and $row['img_dir']!="" and isset($row['img_name']) and $row['img_name'] !=""){
                    $file['img_dir'] = $row['img_dir'];
                    $file['img_name'] = $row['img_name'];
                    $this->file_del($file);
                    $entity->setImgDir("");
                    $entity->setImgName("");
                }
                $entity->setText("");

                $entity->setAddMenuNo($row['add_menu_no']);
                $add_model->sql_edit($entity);

            }
        }
        return $this;
    }

    private function file_del($file)
    {
        $File_Upload = new File_Upload();

         $files['file_path'] = $file['img_dir'];
         $files['file_name'] = $file['img_name'];

        $File_Upload->delete($files);
    }
    private function file_upload($file)
    {
        if(!isset($file['name'])) {return AT_Result::toFailData("파일이 없습니다.");}
        $File_Upload = new File_Upload();
        $files = array();
        $dir =  SHOP_CODE."/basic/"; //.$this->code;
        // $this->set_dir($dir);

        $oResult = $File_Upload->setTargetDir($dir)->upload($file);

        return $oResult;
    }

    public function get_service_status($session_id)
    {

            $log_row = unserialize($this->getCache($session_id));
            $this->log->info("[REV] getCache : ".$session_id." , data : ".json_encode($log_row,JSON_UNESCAPED_UNICODE));
            if (isset($log_row['SUCCESS'])) { //캐시 있을때
                $now = date('YmdHis');
                if($now > $log_row['EXPIRE'] ) {//시간 비교--10분 초과시엔 다시 캐시
                    $log_row = $this->conn_service_status($session_id);
                }
            }else{
                $log_row = $this->conn_service_status($session_id);
            }

            $this->log->info("[REV] get_service_status : ".$session_id." , data : ".json_encode($log_row,JSON_UNESCAPED_UNICODE));

            if ($log_row['SUCCESS'] == "TRUE") { //로그인 성공 쿠키 굽기
                return true;
            } else {
                return false;
            }

        return $this;
    }
    private function conn_service_status($session_id)
    {
        $this->set_is_cache(true);
        $row = $this->helpModel->get_session_status($session_id);
        if($row['service']=="Y"){
            $log_row = AT_Result::toSuccessData();
        }else{
            $log_row = AT_Result::toFailData();
        }
        return $log_row;

        if(0){
            $curl = new Curl();
            $host = "https://help.all-teaching.com/User/Login/getHomePageStatus";
            $curl->set_url($host)->set_method("POST")->setdata("userid", $session_id);
            $this->log->info("login connetction host:" . $host . ", userid : " . $session_id);
            $curl->exec();
            $log_row = $curl->return_data();
        }
        //$log_row['EXPIRE'] = ; //10분
        $this->log->info("help reuslt :" . json_encode($log_row, JSON_UNESCAPED_UNICODE));
        $log_row['EXPIRE'] =  date('YmdHis',strtotime('+10 minute'));
        $this->setCache($session_id ."_status", serialize($log_row));
        $this->log->info("[REV] setCache : ".$session_id." , is_cache  : ".($this->get_is_cache()?"T":"F").", data : ".json_encode($log_row,JSON_UNESCAPED_UNICODE));
        return $log_row;
    }

}