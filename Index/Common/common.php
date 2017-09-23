<?php
/**
 * print_r()函数优化
 * @param $str
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


/**
 * 获取用户的推荐人
 * @param $id   integer ID
 * @return int  推荐人ID
 */
function getUserReferee($id){
    $tbUser = M('user');
    $re = $tbUser -> where("id = $id") -> find();
    if($re){
        $res = $re['referee'];
    }else{
        $res = 0;
    }
    return $res;
}


/**
 * 获取用户的等级
 * @param $id       integer ID
 * @param $class    integer 返回数据：class 等级；classes 相对等级
 * @return int      等级/相对等级
 */
function getUserClass($id,$class){
    $tbUser = M('user');
    $re = $tbUser -> where("id = $id") -> find();
    if($re){
        switch($class){
            case 'class':
                $res = $re['class'];
                break;
            case 'classes':
                $res =  $re['classes'];
                break;
            case 'both':
                $res = array(
                    'class'     => $re['class'],
                    'classes'   => $re['classes']
                );
                break;
            default:
                $res = '参数错误';
        }
    }else{
        $res = '无此用户';
    }
    return $res;
}


/**
 * 查找此用户的相关上级
 * @param $id   integer     id
 * @return array    数组：原始会员，一级会员，二级会员
 */
function getUserReferees($id){
    $class      = getUserClass($id,'calss');
    $referee    = '';
    switch($class){
        case 0:
            $referee    = '';
            break;
        case 1:
            $referee    = getUserReferee($id);
            break;
        case 2:
            $referee1   = getUserReferee($id);
            $referee2   = getUserReferee($referee1);
            $referees   = $referee2.','.$referee1;
            $referee    = explode($referees,',');
            break;
        case 3:
            $referee1   = getUserReferee($id);
            $referee2   = getUserReferee($referee1);
            $referee3   = getUserReferee($referee2);
            $referees   = $referee3.','.$referee2.','.$referee1;
            $referee    = explode($referees,',');
            break;
        case -1 :
            $referee    = '';
            break;
    }
    return $referee;
}


//注册时给推荐人发奖励
function regGiveUserBonus($id){
    $referee    = getUserReferees($id);
    $count      = count($referee);
    $re         = '';
    switch($count){
        case 0:
            $re = 1;
            break;
    }
    return $re;
}


//注册时构建关系，写入relation表中的relation字段
function regInsertRelation(){

}

//注册时根据推荐人，写入此用户的等级
function regInsertClass(){

}


/**
 * 获取用户直推数量/列表
 * @param $id   integer 推荐人ID
 * @param $what string  返回数据的方式 count：数量；select：列表
 * @return int|mixed|string
 */
function refereeCount($id,$what){
    $tbUser     = M('user');
    $re = $tbUser -> where("referee = $id") -> select();
    if($re){
        switch($what){
            case 'count':
                $res = count($re);
                break;
            case 'select':
                $res = $re;
                break;
            default:
                $res = '参数错误';
        }
    }else{
        $res = '此用户未邀请过其他人';
    }
    return $res;
}


/**
 * 检测用户是否可以享受广告补贴（加权分红待完善）
 * @param $id   integer 推荐人ID
 * @return int  0：没有资格；1：有；2：有并且可以享受加权分红；3：代码错误
 */
function checkUserBouns($id){
    $count = refereeCount($id,'count');
    switch($count){
        case $count < 5 :
            $re = 0;
            break;
        case $count >= 5 && $count < 10 :
            $re = 1;
            break;
        case $count >= 10:
            $re = 2;
            break;
        default:
            $re = 3;
    }
    return $re;
}


















