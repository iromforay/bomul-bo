<?php
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