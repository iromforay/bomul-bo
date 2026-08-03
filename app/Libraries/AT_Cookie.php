<?php

namespace App\Libraries;

use App\Service\LoginService;

class AT_Cookie{

    private $mb_id;
    private $aes;
    private $login;
    private $LoginModel;
    private $debug;
    public function __construct()
    {
        $this->aes = new Aes();
        $this->debug = false;
        return $this;
    }
    public function get_id()
    {
        return $this->mb_id;
    }
    /*쿠키로 로그인 */
    public function login_cookie()
    {
        $this->LoginModel = model('User/LoginModel',true,$this->db);

        $cookie = $this->getCookie();
        if(!$this->_is_login()) { //세션 로그인 아닐때만
            if($this->debug) echo "1.세션 로그아웃";
            if (isset($cookie['IS_LOGIN']) and $cookie['IS_LOGIN']) { //쿠키 로그인 : Y
                if($this->debug) echo "2.쿠키 로그인";

                //쿠키 검증
                if($this->hash_check($cookie)){
                    $aPara['mb_id'] = $cookie['MB_ID'];
                    $aPara['SESSION_ID'] = $cookie['SESSION_ID'];
                    //$this->CI->LoginModel->login($aPara['mb_id']);
//                    $this->CI->ath_login->auto_login();
                    return true;

                }else {
                    return false;
                }


            } else if (isset($cookie)) { //쿠키 로그인 : N
                if($this->debug) echo "2.쿠키 로그아웃";
                $this->logout_cookie(); //쿠키 로그아웃
                return false;
            }
        }else {//그외는 세션 로그인 상태
            if($this->debug) echo "1.세션 로그인";
        }
        if($this->debug) echo "9.끝";
        return $this;
    }
    public function hash_check($cookie)
    {
        $hash = $cookie['HASH'];
        $checksum = md5($cookie['SESSION_ID'] . $cookie['MB_ID'] . $cookie['TIME']);
        $now_time = time();
        $expired_time = $cookie['TIME'] * 1;
        $diff = $expired_time - $now_time;
        //쿠키의 아이디와 로그인 시도한 아이디 비교..
        //위변조 확인
        if ($hash === $checksum) {
            if($this->debug)echo "3.위변조";
            //쿠키 유효값 확인
            if ($diff > 0) {
                if($this->debug)echo "4.쿠키 유효값";
                $this->mb_id = $cookie['MB_ID'];
                //   $this->setCookie($cookie['SESSION_ID'], $cookie['MB_ID']); //연장
                //세션 로그인
                if($this->debug) echo "5.세션 로그인";

                return true;
            } else return false;

        } else { //위변조된 쿠키값으로 폐기함.
            if($this->debug) echo "3.쿠키 변조";
            $this->logout_cookie(); //위변조로 쿠키
            return false;
        }
    }

    /**
     * @return mixed
     */
    /*쿠키 불러오기 - 쿠키 호출후 복호화*/
    public function getCookie()
    {

        if(isset($_COOKIE['cookie_session']) and strlen($_COOKIE['cookie_session'])>0) {
            try {
                $cookie_data = $_COOKIE['cookie_session'];
                d($cookie_data);
                $this->aes->set_key(AES_KEY);
                $decrypted_text = $this->aes->decrypt($cookie_data);
                d($decrypted_text);
                $login_cookie =unserialize($decrypted_text);


            }
            catch(ErrorException $e){
                $login_cookie = array(
                    'IS_LOGIN' => false,
                    'SESSION_ID' => "",
                    'MB_ID' => "",
                );
            }
        }else {
            $login_cookie = array(
                'IS_LOGIN' => false,
                'SESSION_ID' => "",
                'MB_ID' => "",
            );
        }
        return (array)$login_cookie;
    }

    /*쿠키 저장 - 값생성후 암호화*/
    public function setCookie( $session_id ,$mb_id, $access_site)
    {
        $time = time()+getenv('COOKIE_TIME');
        $hash = md5($session_id.$mb_id.$time);

        $session_data = array(
            'IS_LOGIN' => true,
            'SESSION_ID' => $session_id,
            'MB_ID' => $mb_id,
            'TIME' =>$time,
            'HASH' =>$hash,
            'LASTTIME' => time(),
            'ACCESS_SITE'=>$access_site
        );
        $serial_data = serialize($session_data);

        $this->aes->set_key(AES_KEY);
        $ciphertext = $this->aes->encrypt($serial_data);
        $this->burn_cookie($ciphertext,$time);

        return TRUE;
    }

    //실제 쿠키 굽기
    public function burn_cookie($data,$time="")
    {
        if($time=="") $time=  time()+getenv('COOKIE_TIME');
        $domain = ".all-teaching.com";//str_replace(array("https://","http://"), "", ATH);
        setcookie('cookie_session', $data, $time, "/", $domain, 1);

    }
    //로그아웃을 쿠키 삭제
    public function logout_cookie(){

        $this->burn_cookie("",0);
        return $this;
    }

    /*쿠키 연장 - 미완성*/
    public function extend_cookie()
    {
        $time = time()+getenv('COOKIE_TIME');
        $cookie = $this->getCookie();

        $cookie['LASTTIME'] = time();

        $this->burn_cookie(json_encode($cookie),$time);
    }
}