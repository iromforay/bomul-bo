<?php

namespace App\Entity;

use org\bovigo\vfs\visitor\vfsStreamPrintVisitor;

class Basic_Entity
{
    private $basic_no;
    private $site_name;
    private $logo_dir;
    private $logo_name;
    private $logo_show;

    private $menu_bg_color;
    private $menu_bg_transparent;
    private $main_menu_color;
    private $sub_menu_focus_color;
    private $property_show;
    private $notice_show;
    private $around_show;
    private $charge_show;
    private $review_show;
    private $vod_show;
    private $map_show;
    private $link_menu_show;
    private $naver_reserve;
    private $naver_reserve_show;
    private $kakao_channel;
    private $kakao_channel_show;
    private $company_name;
    private $broker_office_name;
    private $broker_address;
    private $broker_phone;
    private $broker_reg_no;
    private $broker_ceo_name;
    private $broker_agent_name;
    private $broker_agent_phone;
    private $footer_copy;
    private $naver_site_verification;

    /**
     * @return mixed
     */
    public function getBasicNo()
    {
        return $this->basic_no;
    }

    /**
     * @param mixed $basic_no
     */
    public function setBasicNo($basic_no): void
    {
        $this->basic_no = $basic_no;
    }

    /**
     * @return mixed
     */
    public function getPropertyShow()
    {
        return $this->property_show;
    }

    /**
     * @param mixed $property_show
     */
    public function setPropertyShow($property_show): void
    {
        $this->property_show = $property_show;
    }

    /**
     * @return mixed
     */
    public function getSiteName()
    {
        return $this->site_name;
    }

    /**
     * @param mixed $site_name
     */
    public function setSiteName($site_name): void
    {
        $this->site_name = $site_name;
    }

    /**
     * @return mixed
     */
    public function getLogoDir()
    {
        return $this->logo_dir;
    }

    /**
     * @param mixed $logo_dir
     */
    public function setLogoDir($logo_dir): void
    {
        $this->logo_dir = $logo_dir;
    }

    /**
     * @return mixed
     */
    public function getLogoName()
    {
        return $this->logo_name;
    }

    /**
     * @param mixed $logo_name
     */
    public function setLogoName($logo_name): void
    {
        $this->logo_name = $logo_name;
    }

    /**
     * @return mixed
     */
    public function getNoticeShow()
    {
        return $this->notice_show;
    }

    /**
     * @param mixed $notice_show
     */
    public function setNoticeShow($notice_show): void
    {
        $this->notice_show = $notice_show;
    }

    /**
     * @return mixed
     */
    public function getAroundShow()
    {
        return $this->around_show;
    }

    /**
     * @param mixed $around_show
     */
    public function setAroundShow($around_show): void
    {
        $this->around_show = $around_show;
    }

    /**
     * @return mixed
     */
    public function getChargeShow()
    {
        return $this->charge_show;
    }

    /**
     * @param mixed $charge_show
     */
    public function setChargeShow($charge_show): void
    {
        $this->charge_show = $charge_show;
    }

    /**
     * @return mixed
     */
    public function getReviewShow()
    {
        return $this->review_show;
    }

    /**
     * @param mixed $review_show
     */
    public function setReviewShow($review_show): void
    {
        $this->review_show = $review_show;
    }

    /**
     * @return mixed
     */
    public function getVodShow()
    {
        return $this->vod_show;
    }

    /**
     * @param mixed $vod_show
     */
    public function setVodShow($vod_show): void
    {
        $this->vod_show = $vod_show;
    }

    /**
     * @return mixed
     */
    public function getMapShow()
    {
        return $this->map_show;
    }

    /**
     * @param mixed $map_show
     */
    public function setMapShow($map_show): void
    {
        $this->map_show = $map_show;
    }

    /**
     * @return mixed
     */
    public function getLinkMenuShow()
    {
        return $this->link_menu_show;
    }

    /**
     * @param mixed $link_menu_show
     */
    public function setLinkMenuShow($link_menu_show): void
    {
        $this->link_menu_show = $link_menu_show;
    }

    /**
     * @return mixed
     */
    public function getNaverReserve()
    {
        return $this->naver_reserve;
    }

    /**
     * @param mixed $naver_reserve
     */
    public function setNaverReserve($naver_reserve): void
    {
        $this->naver_reserve = $naver_reserve;
    }

    /**
     * @return mixed
     */
    public function getNaverReserveShow()
    {
        return $this->naver_reserve_show;
    }

    /**
     * @param mixed $naver_reserve_show
     */
    public function setNaverReserveShow($naver_reserve_show): void
    {
        $this->naver_reserve_show = $naver_reserve_show;
    }

    /**
     * @return mixed
     */
    public function getKakaoChannel()
    {
        return $this->kakao_channel;
    }

    /**
     * @param mixed $kakao_channel
     */
    public function setKakaoChannel($kakao_channel): void
    {
        $this->kakao_channel = $kakao_channel;
    }

    /**
     * @return mixed
     */
    public function getKakaoChannelShow()
    {
        return $this->kakao_channel_show;
    }

    /**
     * @param mixed $kakao_channel_show
     */
    public function setKakaoChannelShow($kakao_channel_show): void
    {
        $this->kakao_channel_show = $kakao_channel_show;
    }

    /**
     * @return mixed
     */
    public function getCompanyName()
    {
        return $this->company_name;
    }

    /**
     * @param mixed $company_name
     */
    public function setCompanyName($company_name): void
    {
        $this->company_name = $company_name;
    }

    /**
     * @return mixed
     */
    public function getBrokerOfficeName()
    {
        return $this->broker_office_name;
    }

    /**
     * @param mixed $broker_office_name
     */
    public function setBrokerOfficeName($broker_office_name): void
    {
        $this->broker_office_name = $broker_office_name;
    }

    /**
     * @return mixed
     */
    public function getBrokerAddress()
    {
        return $this->broker_address;
    }

    /**
     * @param mixed $broker_address
     */
    public function setBrokerAddress($broker_address): void
    {
        $this->broker_address = $broker_address;
    }

    /**
     * @return mixed
     */
    public function getBrokerPhone()
    {
        return $this->broker_phone;
    }

    /**
     * @param mixed $broker_phone
     */
    public function setBrokerPhone($broker_phone): void
    {
        $this->broker_phone = $broker_phone;
    }

    /**
     * @return mixed
     */
    public function getBrokerRegNo()
    {
        return $this->broker_reg_no;
    }

    /**
     * @param mixed $broker_reg_no
     */
    public function setBrokerRegNo($broker_reg_no): void
    {
        $this->broker_reg_no = $broker_reg_no;
    }

    /**
     * @return mixed
     */
    public function getBrokerCeoName()
    {
        return $this->broker_ceo_name;
    }

    /**
     * @param mixed $broker_ceo_name
     */
    public function setBrokerCeoName($broker_ceo_name): void
    {
        $this->broker_ceo_name = $broker_ceo_name;
    }

    /**
     * @return mixed
     */
    public function getBrokerAgentName()
    {
        return $this->broker_agent_name;
    }

    /**
     * @param mixed $broker_agent_name
     */
    public function setBrokerAgentName($broker_agent_name): void
    {
        $this->broker_agent_name = $broker_agent_name;
    }

    /**
     * @return mixed
     */
    public function getBrokerAgentPhone()
    {
        return $this->broker_agent_phone;
    }

    /**
     * @param mixed $broker_agent_phone
     */
    public function setBrokerAgentPhone($broker_agent_phone): void
    {
        $this->broker_agent_phone = $broker_agent_phone;
    }

    /**
     * @return mixed
     */
    public function getFooterCopy()
    {
        return $this->footer_copy;
    }

    /**
     * @param mixed $footer_copy
     */
    public function setFooterCopy($footer_copy): void
    {
        $this->footer_copy = $footer_copy;
    }

    /**
     * @return mixed
     */
    public function getNaverSiteVerification()
    {
        return $this->naver_site_verification;
    }

    /**
     * @param mixed $naver_site_verification
     */
    public function setNaverSiteVerification($naver_site_verification): void
    {
        $this->naver_site_verification = $naver_site_verification;
    }
/**
 * @return mixed
 */
public function getLogoShow()
{
    return $this->logo_show;
}/**
 * @param mixed $logo_show
 */
public function setLogoShow($logo_show): void
{
    $this->logo_show = $logo_show;
}/**
 * @return mixed
 */
public function getMainMenuColor()
{
    return $this->main_menu_color;
}/**
 * @param mixed $main_menu_color
 */
public function setMainMenuColor($main_menu_color): void
{
    $this->main_menu_color = $main_menu_color;
}/**
 * @return mixed
 */
public function getSubMenuFocusColor()
{
    return $this->sub_menu_focus_color;
}/**
 * @param mixed $sub_menu_focus_color
 */
public function setSubMenuFocusColor($sub_menu_focus_color): void
{
    $this->sub_menu_focus_color = $sub_menu_focus_color;
}

    /**
     * @return mixed
     */
    public function getMenuBgColor()
    {
        return $this->menu_bg_color;
    }

    /**
     * @param mixed $menu_bg_color
     */
    public function setMenuBgColor($menu_bg_color): void
    {
        $this->menu_bg_color = $menu_bg_color;
    }

    /**
     * @return mixed
     */
    public function getMenuBgTransparent()
    {
        return $this->menu_bg_transparent;
    }

    /**
     * @param mixed $menu_bg_transparent
     */
    public function setMenuBgTransparent($menu_bg_transparent): void
    {
        $this->menu_bg_transparent = $menu_bg_transparent;
    }



}