<?php

namespace App\Controllers\Admin;

use App\Libraries\AT_Result;
use App\Service\CI_Service;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;
    protected $layout;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var array
     */
    protected $helpers = [];
    private $debug=0;
    protected $session_id;
    private $log;
    protected $body;
    protected $sub_body;
    protected $title;
    protected $description;

    // Login 화면 자체는 세션이 없는 게 정상이라 auth() 대상에서 빼야 한다 - 안 빼면
    // 로그인 화면 접근 -> auth() 실패 -> 로그인 화면으로 리다이렉트 -> auth() 실패 ... 로 무한 루프.
    // Login 컨트롤러에서 true로 오버라이드해서 사용.
    protected $skipAuth = false;

    public function __construct()
    {
        header('Access-Control-Allow-Origin: *' );
        header('Access-Control-Max-Age: 86400');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, Content-Type, X-Requested-With, Authorization');

        if (! $this->skipAuth) {
            $this->auth();
        }

        return $this;
    }
    public function is_auth()
    {
        $session_id = $_SERVER['HTTP_AUTHORIZATION']??"";
        $session_id = str_replace("Bearer ","",$session_id);

        if($session_id=="") {
            echo AT_Result::toFailJson("로그인후 이용가능합니다.","NEED_LOGIN");exit;
            return $this;
        }else if($session_id!=""){
            $this->session_id = $session_id;
            new CI_Service($session_id);

            // 토큰이 login_session에 매칭 안 되면 SHOP_CODE가 정의되지 않은 채로 통과되니 여기서 명시적으로 거부
            if (!defined('SHOP_CODE') || SHOP_CODE == "") {
                echo AT_Result::toFailJson("잘못된 접근입니다.", "INVALID_TOKEN");
                exit;
            }
            return $this;
        }else{
            echo AT_Result::toFailJson("서비스 세팅중입니다. 세팅 완료후 이용가능합니다.","NOT_SERVICE");
            exit;
        }

        return $this;
    }
    public function set_debug($debug=0)
    {
        $this->debug=$debug;
        return $this;
    }

    public function get_debug()
    {
        return $this->debug;
    }
    public function getMonoLog($log_name="api")
    {
        if(!defined('FID')){
            $file_name = "COMMON_log_".date("Ymd").".log";
        }else{
            $file_name = FID."_log_".date("Ymd").".log";
        }
        $log = new Logger($log_name);

        $log->pushHandler(new StreamHandler(LOG_DIR.'/'.date("Ym")."/".$file_name, Logger::DEBUG));

        return $log;
    }
    // 리스트 API 페이징 계산 (Board/Property/Note 등에서 공통으로 씀 - 예전엔 각 컨트롤러에 복붙돼 있었음)
    public function calPaging($page_num=1, $total_items=0, $block_size=10, $limit=10)
    {
        $items_per_page = $limit;
        $total_pages = ceil($total_items / $items_per_page);

        $block_start = max(1, $page_num - $block_size);
        $block_end = min($total_pages, $page_num + $block_size);

        $item_num_start = $total_items - ($page_num-1)*$limit;

        return array(
            'item_num_start' => $item_num_start,
            'now_page'       => $page_num,
            'total_items'    => $total_items,
            'total_page'     => $total_pages,
            'page_per_items' => $items_per_page,
            'block_start'    => $block_start,
            'block_end'      => $block_end
        );
    }

    // 로그인 세션이 있으면 통과, 없으면 로그인 화면으로 리다이렉트.
    // SHOP_CODE는 여기서 다루지 않는다 - ad(이 백오피스)는 미사용, 중개사용 bo 쪽 개념.
    // $_SESSION을 직접 보지 않고 session() 헬퍼를 쓴다 - 이 요청에서 session()이 한 번도
    // 호출된 적 없으면 CI4가 세션을 아직 시작하지 않아 $_SESSION 자체가 비어있을 수 있다.
    public function auth()
    {
        $login = session('login');

        //if (! empty($login['is_login'])) {
        if (isset($login['is_login']) && $login['is_login'] ) {
            return $this;
        }

        $this->_redirect(Admin_SITE . "/Login");
    }

    // Admin은 layout이 admin_default 하나뿐이라 layout 인자를 따로 받지 않는다 -
    // (layout, name, data) 3인자 시그니처였을 때 컨트롤러가 2인자로 호출하며 타입 불일치가 반복됐음.
    public function view(string $name = "", array $data = [], array $options = [])
    {
        $data['common']= $this->body??array(""); // 공통 변수값 입력.
        $data['sub']= $this->sub_body??""; // 전용 변수값 입력.
        $data['common']['title']= $this->title??""; //헤더영역내 title
        $data['common']['description']= $this->description??""; //meta description / og:description

        $data['FRESH_TIME'] = isset($data['FRESH_TIME'])? DATE('YmdHis') : FRESH_TIME;

        $temp_layout = array(
            'header_menu' =>view("common/admin_menu",$data),
            'header'=>view("common/admin_header",$data),
            'content' => view("Admin/".$name, $data,$options),
            'footer'=>view("common/admin_footer",$data)
        );
        echo  view("layout/admin_default",$temp_layout,$options);

    }
    // protected $session;

    /**
     * Constructor.
     */

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);


        // Preload any models, libraries, etc, here.

        // E.g.: $this->session = \Config\Services::session();
    }

    public function _warning_redirect( $MSG, $URL = '' )
    {

        echo "<script type='text/javascript'>";
        echo "	alert('" . $MSG . "');";
        if( empty($URL) ){
            echo "	location.href = window.history.back();";
        }else{
            echo "	location.href = '" . $URL . "';";
        }
        echo "</script>";

        exit;
    }

    public function _warning_close( $MSG )
    {

        echo "<script type='text/javascript'>";
        echo "	alert('" . $MSG . "');";
        echo "	window.close();";
        echo "</script>";

        exit;
    }
    public function alert_msg($MSG)
    {

        echo "<script type='text/javascript'>";
        echo "	alert('" . $MSG . "');";
        echo "</script>";

        exit;
    }

    public function _redirect(  $URL = '' )
    {

        echo "<script type='text/javascript'>";

        if( empty($URL) ){
            echo "	location.href = window.history.back();";
        }else{
            echo "	location.href = '" . $URL . "';";
            echo "console.log('a');";
        }
        echo "</script>";

        exit;
    }
}
/*
 * SELECT user, host FROM mysql.user;

 * -- 1. 기존에 존재하던 두 계정 깔끔하게 삭제
DROP USER IF EXISTS 'bomul'@'%';
DROP USER IF EXISTS 'bomul'@'localhost';
FLUSH PRIVILEGES;

-- 2. 웹 서버(Localhost) 접속용 계정 생성 및 모든 DB 권한 부여
CREATE USER 'bomul'@'localhost' IDENTIFIED BY '1djraks';
GRANT ALL PRIVILEGES ON *.* TO 'bomul'@'localhost' WITH GRANT OPTION;

-- 3. 작업용 PC IP 접속용 계정 생성 및 모든 DB 권한 부여
-- (※ IP가 자주 바뀌는 환경이거나 편하게 쓰시려면 '작업자_PC_IP' 대신 '%'를 넣으셔도 됩니다.)
CREATE USER 'bomul'@'%' IDENTIFIED BY '1djraks';
GRANT ALL PRIVILEGES ON *.* TO 'bomul'@'%' WITH GRANT OPTION;

-- 4. 최종 변경 사항 적용
FLUSH PRIVILEGES;
 */