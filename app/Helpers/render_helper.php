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

// product_name에서 분리된 "기간" 표시용 - billing_type/period_day 조합에서 계산한다(2026-08-11,
// 상품 재구성 - product_name은 순수 상품명만, 기간은 여기서 별도 배지로 붙인다).
// MONTHLY는 매달 자동결제(구독형)라 period_day 숫자보다 "자동연장" 여부가 더 중요한 정보라 고정 라벨.
if ( ! function_exists('product_period_label'))
{
    function product_period_label(?string $billingType, $periodDay): string
    {
        if ($billingType === 'MONTHLY') {
            return '구독형·자동연장';
        }

        $days = (int) $periodDay;
        $presets = [30 => '1개월', 180 => '6개월', 365 => '12개월'];

        return $presets[$days] ?? "{$days}일";
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