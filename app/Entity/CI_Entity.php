<?php

namespace App\Entity;

use CodeIgniter\Entity\Entity;

class CI_Entity extends Entity
{


    public function toSqlSet($db,$primary_key=""): string
    {

        $sets = [];

        foreach ($this->toRawArray() as $key => $value) {

            if($this->{$key} != "" && $key != $primary_key) { // 값이 존재할때만 쿼리에 실행
                $sets[] = "{$key} = " . $db->escape($this->{$key});
            }
        }

        return implode(",\n", $sets);
    }
}