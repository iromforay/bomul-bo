<?php
// N=대기, Y=검증완료, W=보류, F=검증실패 (CustomerService::VERIFY_STATUSES와 동일 값)
if ( ! function_exists('verify_status_badge'))
{
    function verify_status_badge(?string $status): string
    {
        $map = [
            'Y' => ['done', '검증완료'],
            'W' => ['hold', '보류'],
            'F' => ['stop', '검증실패'],
        ];
        [$class, $label] = $map[$status] ?? ['wait', '대기'];

        return '<span class="badge ' . $class . '">' . $label . '</span>';
    }
}

if ( ! function_exists('render'))
{
    function render(string $layout="default" ,string $name, array $data = [], $options=[])
    {
        $layout_dir= VIEW."/layout/".$layout;
        if(! file_exists($layout_dir)){
            $layout="layout/default";
        }

        $data['FRESH_TIME'] = isset($data['FRESH_TIME'])? DATE('YmdHis') : FRESH_TIME;

        return view(
            $layout,
            [
                'header_menu' =>"",
                'header'=>"",
                'content' => view($name, $data, $options),
                'footer'=>""
            ],
            $options
        );

        exit;
        return view(
            $layout,
            [
                'header_menu' =>view("common/header_menu",$data),
                'header'=>view("common/header",$data),
                'content' => view($name, $data, $options),
                'footer'=>view("common/footer",$data)
            ],
            $options
        );
    }
}