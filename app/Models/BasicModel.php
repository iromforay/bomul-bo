<?php

namespace App\Models;

use App\Entity\Basic_Entity;

class BasicModel extends CI_Model
{
        public function __construct($dbs)
    {
        parent::__construct($dbs,"basic","basic_no");
        return $this;
    }

    public function sql_save(Basic_Entity $entity)
    {


        $osql = " INSERT INTO `$this->table` SET
                        `code`=%s,`key`=%s, `text`=%s,
                        `show`=%s,`img_dir`=%s,`img_name`=%s ,`write_date`=now()
                ";
        $sql = sprintf($osql,  $this->db->escape( $entity->getCode()), $this->db->escape( $entity->getKey()), $this->db->escape( $entity->getText()),
            $this->db->escape( $entity->getShow()), $this->db->escape( $entity->getImgDir()), $this->db->escape( $entity->getImgName())
        );
        //   echo $sql."\n";
        $boards_no =  $this->safeExecQuery($sql);
        return $boards_no;
    }

    public function sql_edit(Basic_Entity $entity)
    {
        $set = array();
        $sql  = "UPDATE `$this->table` SET ";

        ! is_null($entity->getSiteName())               ?    $set[] = "`site_name`=".               $this->db->escape($entity->getSiteName())           : "";
        ! is_null($entity->getLogoDir())                ?    $set[] = "`logo_dir`=".                $this->db->escape($entity->getLogoDir())            : "";
        ! is_null($entity->getLogoName())               ?    $set[] = "`logo_name`=".               $this->db->escape($entity->getLogoName())           : "";
        ! is_null($entity->getLogoShow())               ?    $set[] = "`logo_show`=".               $this->db->escape($entity->getLogoShow())           : "";

        ! is_null($entity->getMenuBgColor())            ?    $set[] = "`menu_back_color`=".         $this->db->escape($entity->getMenuBgColor())        : "";
        ! is_null($entity->getMenuBgTransparent())      ?    $set[] = "`menu_back_transparent`=".   $this->db->escape($entity->getMenuBgTransparent())  : "";

        ! is_null($entity->getMainMenuColor())          ?    $set[] = "`main_menu_color`=".         $this->db->escape($entity->getMainMenuColor())      : "";
        ! is_null($entity->getSubMenuFocusColor())      ?    $set[] = "`sub_menu_focus_color`=".    $this->db->escape($entity->getSubMenuFocusColor())  : "";

        ! is_null($entity->getNoticeShow())             ?    $set[] = "`notice_show`=".             $this->db->escape($entity->getNoticeShow())         : "";
        ! is_null($entity->getAroundShow())             ?    $set[] = "`around_show`=".             $this->db->escape($entity->getAroundShow())         : "";
        ! is_null($entity->getChargeShow())             ?    $set[] = "`charge_show`=".             $this->db->escape($entity->getChargeShow())         : "";
        ! is_null($entity->getReviewShow())             ?    $set[] = "`review_show`=".             $this->db->escape($entity->getReviewShow())         : "";
        ! is_null($entity->getVodShow())                ?    $set[] = "`vod_show`=".                $this->db->escape($entity->getVodShow())            : "";
        ! is_null($entity->getMapShow())                ?    $set[] = "`map_show`=".                $this->db->escape($entity->getMapShow())            : "";
        ! is_null($entity->getPropertyShow())           ?    $set[] = "`property_show`=".           $this->db->escape($entity->getPropertyShow())       : "";

        ! is_null($entity->getLinkMenuShow())           ?    $set[] = "`link_menu_show`=".          $this->db->escape($entity->getLinkMenuShow())       : "";

        ! is_null($entity->getNaverReserve())           ?    $set[] = "`naver_reserve`=".           $this->db->escape($entity->getNaverReserve())       : "";
        ! is_null($entity->getNaverReserveShow())       ?    $set[] = "`naver_reserve_show`=".      $this->db->escape($entity->getNaverReserveShow())   : "";
        ! is_null($entity->getKakaoChannel())           ?    $set[] = "`kakao_channel`=".           $this->db->escape($entity->getKakaoChannel())       : "";
        ! is_null($entity->getKakaoChannelShow())       ?    $set[] = "`kakao_channel_show`=".      $this->db->escape($entity->getKakaoChannelShow())   : "";

        ! is_null($entity->getCompanyName())            ?    $set[] = "`company_name`=".            $this->db->escape($entity->getCompanyName())        : "";
        ! is_null($entity->getBrokerOfficeName())       ?    $set[] = "`broker_office_name`=".      $this->db->escape($entity->getBrokerOfficeName())   : "";
        ! is_null($entity->getBrokerAddress())          ?    $set[] = "`broker_address`=".          $this->db->escape($entity->getBrokerAddress())      : "";
        ! is_null($entity->getBrokerPhone())            ?    $set[] = "`broker_phone`=".            $this->db->escape($entity->getBrokerPhone())        : "";
        ! is_null($entity->getBrokerRegNo())             ?    $set[] = "`broker_reg_no`=".           $this->db->escape($entity->getBrokerRegNo())        : "";
        ! is_null($entity->getBrokerCeoName())          ?    $set[] = "`broker_ceo_name`=".         $this->db->escape($entity->getBrokerCeoName())      : "";
        ! is_null($entity->getBrokerAgentName())        ?    $set[] = "`broker_agent_name`=".       $this->db->escape($entity->getBrokerAgentName())    : "";
        ! is_null($entity->getBrokerAgentPhone())       ?    $set[] = "`broker_agent_phone`=".      $this->db->escape($entity->getBrokerAgentPhone())   : "";
        ! is_null($entity->getFooterCopy())             ?    $set[] = "`footer_copy`=".             $this->db->escape($entity->getFooterCopy())         : "";
        ! is_null($entity->getNaverSiteVerification())  ?    $set[] = "`naver_site_verification`=". $this->db->escape($entity->getNaverSiteVerification()) : "";


        $sql .= implode(",", $set);

        $sql .= " WHERE `$this->primaryKey` = ". $this->db->escape($entity->getBasicNo());
//echo $sql."\n";
        if($this->safeExecQuery($sql))
        {
            return true;
        }else{
            return false;
        }
    }
}