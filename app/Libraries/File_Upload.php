<?php

namespace App\Libraries;
class File_Upload
{

    private $CI;
    private $upload_file;
    private $_type;
    private $target_file_dir;
    public function __construct()
    {
        // Assign the CodeIgniter super-object
      //  $this->CI =& get_instance();
        return $this;
    }
    public function setTargetDir($dir)
    {
        $this->target_file_dir = $dir;
        return $this;
    }
    public function getTargetDir()
    {
        return $this->target_file_dir;
    }

    public function upload($file)
    {
        if($file['name']==""){
            return AT_Result::toFailData( '업로드할 파일이 없습니다.' );
            exit;
        }
        $ext_allowed = array("png","jpg","jpge","pdf","xls","hwp","ppt","doc","docx","xlsx","pptx","txt","mp4");

        $tmp_file_info2 = pathinfo($file['tmp_name'] . '/' . $file['name']);
        $tmp_file_path = $file['tmp_name'];

        $file_name_ext = $file['name'];
        $file_name = $tmp_file_info2['filename'];
        $file_ext = $tmp_file_info2['extension'];
        $file_size = $file['size'];


        // 확장자 확인
        if( !in_array(strtolower($file_ext), $ext_allowed) ){
            $msg = '확장자 ' . implode(', ', $ext_allowed) . '만 업로드 가능합니다. 확인 후 업로드 하여 주십시요.';
            return AT_Result::toFailData( $msg );
            exit;
        }

        // 용량 확인
        if($file_size<=0){
            $msg = "최소 1Byte 이상 파일만 업로드 가능합니다.";
            return AT_Result::toFailData( $msg );
            exit;
        }
        if($file_size >= 104857600){
            $msg = "파일 용량은 100MB까지만 업로드 가능합니다.";
            return AT_Result::toFailData( $msg );
            exit;
        };

        // 파일 업로드
        /* 업로드할 디렉토리 설정 */
        // 폴더 생성
        //$target_file_dir_return =  date("/Y/m/d/", time());

        // $target_file_dir = ATH_SYS_PDS . "/board/".$this->get_code()."/" . $target_file_dir_return;


        $target_file_dir = APP_PDS .$this->getTargetDir();

        if(!file_exists($target_file_dir)){
            mkdir($target_file_dir, 0775, true);
            chmod($target_file_dir, 0775); // umask에 걸려 mkdir 모드가 그대로 반영 안 될 수 있어 명시적으로 재설정
        }
        // 파일명 생성 및 중복 체크 확인
        $i=0;
        do{
            //$target_file_name = date("YmdHis") . str_pad(rand(0, 9999), 4, "0", STR_PAD_LEFT);
            if($i>0) $add_name = "(".$i.")";
            else $add_name="";
            $target_file_name = md5(microtime().env("REMOTE_ADDR"));
            $target_file_name_ext = sprintf("%s%s.%s", $target_file_name,$add_name,  $file_ext);
            $target_file_path = $target_file_dir . $target_file_name_ext;
            if(!file_exists($target_file_path)){ break; }
        }while(true);
        // $this->set_upload_file($target_file_dir_return.$target_file_name_ext);
        $target_file_path = $target_file_dir . $target_file_name_ext;

        $result = array(
            'file_path' => $this->getTargetDir(),
            'file_name' => $target_file_name_ext,
            'file_name_original' => $file_name_ext,
            'file_size' => $file_size
        );
        if(move_uploaded_file($tmp_file_path, $target_file_path)){
            chmod($target_file_path, 0775);


            return AT_Result::toSuccessData( '업로드 되었습니다.', $result );
            //return TRUE;
        } else {
            return AT_Result::toFailData( '파일업로드에 실패하였습니다.' ,$result);
        }
        exit;

    }
    // 이미 업로드된 파일을 다른 디렉토리로 이동 (temp -> 실제 경로)
    public function move($from_dir, $file_name, $to_dir)
    {
        if ($file_name == "") return false;

        $from_dir = rtrim($from_dir, '/').'/';
        $to_dir = rtrim($to_dir, '/').'/';

        $from_path = APP_PDS . $from_dir . $file_name;
        $to_dir_full = APP_PDS . $to_dir;

        if (!file_exists($to_dir_full)) {
            mkdir($to_dir_full, 0775, true);
            chmod($to_dir_full, 0775); // umask에 걸려 mkdir 모드가 그대로 반영 안 될 수 있어 명시적으로 재설정
        }

        $to_path = $to_dir_full . $file_name;

        if (file_exists($from_path)) {
            return rename($from_path, $to_path);
        }
        return false;
    }

    //등록된 파일 삭제
    public function delete($file)
    {
        $file_name = $file['file_name'];
        // path/dir 저장값에 마지막 슬래시가 빠져있는 경우를 대비해 정규화
        $file_path = rtrim($file['file_path'] ?? '', '/').'/';

        if($file_name!="" and file_exists(APP_PDS.$file_path.$file_name)){ //파일 존재시
            return unlink(APP_PDS.$file_path.$file_name);//삭제
        }
        return false;
    }

    public function download($file_name){

        //$this->CI->yield = FALSE;

        $file_path = $this->getTargetDir();
        //$file_path = ATH_SYS_PDS . '/' . $this->input->post('file_path');
        //$file_name = $this->input->post('file_name');
        $file = $file_path."/".$file_name;
        if(!file_exists($file) || !is_file($file)){
            $this->_warning('파일을 찾을수 없습니다.');
            return;
        }

        $file_size = filesize($file);

        header("Content-Type: application/octet-stream; name=" . $file_name);
        header("Content-Disposition: attachment; filename=" . $file_name);
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: $file_size");

        $bufsize = 20000;

        if ($fh = fopen($file, "rb")) {
            while($buf = fread($fh, $bufsize)) {
                print $buf;
            }
            fclose($fh);
            exit;
        } else {
            //header("HTTP/1.1 404 Not Found");
            exit;
        }
    }
    public function _warning ($MSG )
    {
        $this->layout = 'default_empty';

        echo "<script type='text/javascript'>";
        echo "	alert('" . $MSG . "');";
        echo "</script>";

        exit;
    }
}