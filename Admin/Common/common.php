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


/**
 * 获取团购人数（包含下级推荐的总人数）
 * @param   integer $id     用户ID
 * @param   string  $what   返回数据方式
 * @param   string  $field  仅返回某个字段
 * @return  int|mixed|string
 */
function refereeCounts($id,$what,$field = '*'){
    $tbRealtion = M('user_relation');
    $re = $tbRealtion -> where("FIND_IN_SET($id, relation)") -> field($field) -> select();
    switch($what){
        case 'count':
            $res = count($re);
            break;
        case 'select':
            $res = $re;
            break;
        case 'uid':
            $ids = [];
            foreach($re as $k => $v){
                $ids[] = $re[$k]['uid'];
            }
            $res = $ids;
            break;
        default:
            $res = '参数错误';
            break;
    }
    return $res;
}


/**
 * 获取用户直推数量/列表
 * @param $id   integer 用户ID
 * @param $what string  返回数据的方式 count：数量；select：列表
 * @return int|mixed|string
 */
function refereeCount($id,$what){
    $tbUser     = M('user');
    $re = $tbUser -> where("referee = $id") -> order('id desc , regtime desc') -> select();
    if($re){
        switch($what){
            case 'count':
                $res = count($re);
                break;
            case 'select':
                foreach($re as $k => $v){
                    $cash = getUserCash($v['id']);
                    $re[$k]['jifen']    = $cash['jifen'];
                    $re[$k]['yide']     = $cash['yide'];
                    $re[$k]['cash']     = $cash['cash'];
                    $ref = getUser($v['referee'],'username');
                    $re[$k]['refName']  = $ref;
                }
                $res = $re;
                break;
            default:
                $res = '参数错误';
        }
    }else{
        switch($what){
            case 'count':
                $res = 0;
                break;
            case 'select':
                $res = '';
                break;
            case 'ssss':
                $res = '';
                break;
            default:
                $res = '参数错误';
        }
    }
    return $res;
}


/**
 * 可获得分红的用户列表
 * 传值时，只有当第一个参数为1是，第二个参数才有效
 * @param int       $re     返回部分数据 0：返回所有数据；1：仅返回需要用到的数据
 * @param string    $field  要返回的字段
 * @return array|bool   0：展示用户信息；1：返回关键数据，做分红发放
 */
function fenHongUser($re = 0,$field = 'id,username,refcount,fenhtime'){
    $dbUser = M('user');
    $refCount = getUserConfig('10');
    $allCount = getUserConfig('11');
    $map['refcount'] = array('egt',$refCount);
    if($re == 1){
        $map['fenhtime'] = array('lt',date('Y-m-01 00:00:00', strtotime(date("Y-m-d"))));
        $users = $dbUser -> where($map) -> field($field) -> select();
    }else{
        $users = $dbUser -> where($map) -> select();
    }
    foreach($users as $k => $v){
        $id = $v['id'];
        $count = refereeCounts($id,'count');
        $users[$k]['allCount'] = $count;
    }
    $userArr = array();
    foreach($users as $kk => $vv){
        if($users[$kk]['allCount'] >= $allCount){
            $userArr[] = $vv;
        }
    }
    if(empty($userArr)){
        return false;
    }else{
        return $userArr;
    }
}

//给用户奖励时，写入积分表、log表、消息表
function notice(){

}