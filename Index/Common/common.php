<?php
/**
 * Created by PhpStorm.
 * User: z
 * Date: 2017/9/22
 * Time: 下午 3:55
 */
function pp($str){
    echo '<div style="border: 1px solid bisque;border-bottom-color:red;border-right-color:red;color:green;background-color: bisque;"><pre>';
    print_r($str);
    echo '</pre></div>';
}


/**
 * var_dump()函数优化
 * @param $str
 */
function vv($str){
    echo '<div style="border: 1px solid bisque;border-bottom-color:red;border-right-color:red;color:green;background-color: bisque;"><pre>';
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
