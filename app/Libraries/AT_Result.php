<?php

namespace App\Libraries;

class AT_Result
{
    public static function toSuccessData( $msg = '', $record = array() )
    {
        $oResult['SUCCESS'] = 'TRUE';
        $oResult['MSG'] = empty($msg) ? '성공입니다.' : $msg;
        if( !empty($record) ){
            $oResult['RECORD'] = $record;
        }

        return $oResult;
    }

    public static function toSuccessJson( $msg = '', $record = array() )
    {
        $oResult['SUCCESS'] = 'TRUE';
        $oResult['MSG'] = empty($msg) ? '성공입니다.' : $msg;
        if( !empty($record) ){
            $oResult['RECORD'] = $record;
        }

        header('Content-type: application/json');
        header('Content-type: text/plain; charset=UTF-8');

        return json_encode($oResult);
    }

    public static function toFailData( $msg = '', $code = '' )
    {
        $oResult['SUCCESS'] = 'FALSE';
        $oResult['MSG'] = $msg;
        $oResult['CODE'] = $code;

        return $oResult;
    }

    public static function toFailJson( $msg = '', $code = '' )
    {
        $oResult['SUCCESS'] = 'FALSE';
        $oResult['MSG'] = $msg;
        $oResult['CODE'] = $code;

        header('Content-type: application/json');
        header('Content-type: text/plain; charset=UTF-8');

        return json_encode($oResult);
    }

    public static function toJson($data){
        header('Content-type: application/json');
        header('Content-type: text/plain; charset=UTF-8');

        return json_encode($data);
    }
}
