<?php

namespace App\Service;

use App\Entity\Manager_Entity;
use App\Libraries\AT_Result;

// CI_Service를 상속하지 않는다 - ad(이 백오피스)는 SHOP_CODE를 안 쓰는데 CI_Service 생성자는
// SHOP_CODE가 정의돼 있다고 가정한다 (LoginService와 동일한 이유).
class ManagerService
{
    private $db;
    private $model;

    public function __construct()
    {
        $this->db = db_connect("default");
        $this->model = model('App\Models\ManagerModel', true, $this->db);
    }

    public function get_list()
    {
        return $this->model->get_list();
    }

    public function get_row($manager_no)
    {
        $rows = $this->model->findbyid((int) $manager_no);
        return $rows[0] ?? null;
    }

    public function save(array $data): array
    {
        $manager_id   = trim($data['manager_id'] ?? '');
        $manager_pw   = (string) ($data['manager_pw'] ?? '');
        $manager_name = trim($data['manager_name'] ?? '');
        $use_yn       = ($data['use_yn'] ?? 'Y') === 'N' ? 'N' : 'Y';

        if ($manager_id === '' || $manager_pw === '' || $manager_name === '') {
            return AT_Result::toFailData("아이디, 비밀번호, 이름을 모두 입력해주세요.");
        }

        if ($this->model->existsByLoginId($manager_id)) {
            return AT_Result::toFailData("이미 사용중인 아이디입니다.");
        }

        $entity = new Manager_Entity();
        $entity->setManagerId($manager_id);
        $entity->setManagerPw(hash('sha512', $manager_pw));
        $entity->setManagerName($manager_name);
        $entity->setUseYn($use_yn);

        if ($this->model->sql_save($entity)) {
            return AT_Result::toSuccessData("관리자 등록에 성공했습니다.");
        }

        return AT_Result::toFailData("등록에 실패했습니다.");
    }

    public function edit($manager_no, array $data): array
    {
        $manager_no   = (int) $manager_no;
        $manager_id   = trim($data['manager_id'] ?? '');
        $manager_name = trim($data['manager_name'] ?? '');
        $use_yn       = ($data['use_yn'] ?? 'Y') === 'N' ? 'N' : 'Y';

        if ($manager_id === '' || $manager_name === '') {
            return AT_Result::toFailData("아이디와 이름을 입력해주세요.");
        }

        if ($this->model->existsByLoginId($manager_id, $manager_no)) {
            return AT_Result::toFailData("이미 사용중인 아이디입니다.");
        }

        $entity = new Manager_Entity();
        $entity->setManagerNo($manager_no);
        $entity->setManagerId($manager_id);
        $entity->setManagerName($manager_name);
        $entity->setUseYn($use_yn);

        // 비밀번호는 입력했을 때만 변경
        if (!empty($data['manager_pw'])) {
            $entity->setManagerPw(hash('sha512', $data['manager_pw']));
        }

        if ($this->model->sql_edit($entity)) {
            return AT_Result::toSuccessData("관리자 정보를 수정했습니다.");
        }

        return AT_Result::toFailData("수정에 실패했습니다.");
    }

    // $current_no: 현재 로그인한 관리자 번호 - 자기 자신은 삭제할 수 없게 막는다.
    public function delete($manager_no, $current_no = null): array
    {
        $manager_no = (int) $manager_no;

        if ($current_no !== null && $manager_no === (int) $current_no) {
            return AT_Result::toFailData("현재 로그인중인 계정은 삭제할 수 없습니다.");
        }

        if ($this->model->sql_delete($manager_no)) {
            return AT_Result::toSuccessData("관리자를 삭제했습니다.");
        }

        return AT_Result::toFailData("삭제에 실패했습니다.");
    }
}
