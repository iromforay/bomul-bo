<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;


abstract class BaseController extends Controller
{
    private $debug = 0;
    public function __construct()
    {
        header('Access-Control-Allow-Origin: *' );
        header('Access-Control-Max-Age: 86400');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, Content-Type, X-Requested-With, Authorization');

        $this->layout="/layout/default";

        $session_id = $_SERVER['HTTP_AUTHORIZATION']??"";
        $SHOP_CODE = str_replace("Bearer ","",$session_id);

      //  define('SHOP_CODE', $SHOP_CODE); //상점코드 - help 아이디
      //  $this->set_domain();
        return $this;
    }
    public function set_domain()
    {
        return $this;
    }

    public function set_debug($debug=0)
    {
        $this->debug=$debug;
        return $this;
    }
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);


        // Preload any models, libraries, etc, here.

        // E.g.: $this->session = \Config\Services::session();
    }

    // www 공개 사이트용 - header_menu/header/footer + layout/default로 감싸서 렌더링.
    // (예전엔 Home.php가 실수로 Admin\BaseController를 상속해서 admin 레이아웃 + 로그인 게이트가
    // 씌워지는 버그가 있었음 - www 방문자가 ad.bomul.ai.kr/Login으로 리다이렉트되던 원인)
    public function view(string $layout = "", string $name = "", array $data = [], array $options = [])
    {
        $layout = $layout !== "" ? $layout : "default";
        $data['common'] = $data['common'] ?? [];
        $data['common']['title'] = $data['common']['title'] ?? '';
        $data['common']['description'] = $data['common']['description']
            ?? '보이는 매물은 공인중개사무소 전용 홈페이지 제작·관리 서비스입니다. 매물 등록부터 고객 상담까지, 필요한 기능을 한 곳에서 관리하세요.';
        $data['common']['noindex'] = $data['common']['noindex'] ?? false;
        $data['FRESH_TIME'] = $data['FRESH_TIME'] ?? FRESH_TIME;

        $temp_layout = [
            'header_menu' => view("common/header_menu", $data),
            'header'      => view("common/header", $data),
            'content'     => view($name, $data, $options),
            'footer'      => view("common/footer", $data),
        ];

        echo view("layout/" . $layout, $temp_layout, $options);
    }
    /*
    * $page_num : 현재페이지 (기본 :1)
    * $total_items : 전체게시물수
    * $block_size : 페이지 블럭사이즈(기본:10)
    * $limit : 페이지당 게시물수 (기본:10)
    * */
    public function calPaging($page_num=1, $total_items, $block_size = 10, $limit = 5)
    {

        $items_per_page=$limit; //페이지당 게시물수
        $total_pages=0; //젠체 페이지수

        $total_pages = ceil($total_items / $items_per_page);

        // 페이지 블럭의 시작 번호와 끝 번호 계산
        $block_start = max(1, $page_num - $block_size);
        $block_end = min($total_pages, $page_num + $block_size);

        $item_num_start = $total_items - ($page_num-1)*$limit ;
        $paging = array(
            'item_num_start' =>$item_num_start,
            'now_page' =>$page_num,
            'total_items' => $total_items,
            'total_page'=>$total_pages,
            'page_per_items' =>$items_per_page,
            'block_start' => $block_start,
            'block_end'=>$block_end
        );
        return $paging;
    }
}