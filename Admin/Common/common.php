<?php
/**
 * print_r()函数优化
 * @param $str
 * @param $num
 */
function pp($str,$num = ''){
    echo '<div style="border: 1px solid bisque;border-bottom-color:red;border-right-color:red;color:green;background-color: bisque;"><pre>';
    if($num != ''){
        echo $num.'<br><br>';
    }
    print_r($str);
    echo '</pre></div>';
}


/**
 * var_dump()函数优化
 * @param $str
 * @param $num
 */
function vv($str,$num = ''){
    echo '<div style="border: 1px solid bisque;border-bottom-color:red;border-right-color:red;color:green;background-color: bisque;"><pre>';
    if($num != ''){
        echo $num.'<br><br>';
    }
    var_dump($str);
    echo '</pre></div>';
}


/**
 * dump()函数优化
 * @param $str
 */
function dd($str){
    echo '<div style="border: 1px solid bisque;border-bottom-color:red;border-right-color:red;color:green;background-color: bisque;"><pre>';
    dump($str);
    echo '</pre></div>';
}


/**
 * 打印最近使用的SQL语句
 * @param $model
 */
function ps($model){
    $re = $model -> getLastSql();
    pp($re);
}


/**
 * 获取后台配置比率等信息
 * @param   integer $id     配置项的ID
 * @param   string  $what   返回数据方式 value：返回值；config：返回具体配置项
 * @return  array|string
 */
function getUserConfig($id,$what = 'value'){
    $re = M('user_config') -> where("id = $id") -> find();
    switch($what){
        case 'value':
            $config = $re['value'] * $re['ratio'];
            return $re ? $config : '此配置项不存在';
            break;
        case 'config':
            $config = array(
                'value' => $re['value'],
                'ratio' => $re['ratio']
            );
            return $config;
            break;
        case 'all':
            return $re;
        default:
            return '参数错误';
    }
}