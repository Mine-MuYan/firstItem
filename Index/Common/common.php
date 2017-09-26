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
 * 获取用户的推荐人
 * @param $id   integer     用户ID
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
 * 获取用户的某项信息
 * @param $id       integer 用户ID
 * @param $field    string  字段名
 * @return int  推荐人ID
 */
function getUser($id,$field){
    $tbUser = M('user');
    $re = $tbUser -> where("id = $id") -> find();
    if($re){
        $res = $re[$field];
    }else{
        $res = '获取数据失败';
    }
    return $res;
}


/**
 * 获取用户的等级/相对等级
 * @param $id       integer     用户ID
 * @param $class    integer     返回数据：class 等级；classes 相对等级
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
 * @param $id       integer     用户ID
 * @return array    数组：原始会员，一级会员，二级会员
 */
function getUserReferees($id){
    $refID      = getUserReferee($id);              //获取推荐人ID
    $class      = getUserClass($refID,'class');     //获取推荐人等级
    $referee    = '';
    switch($class){
        case 0:
            $referee    = $refID;
            break;
        case 1:
            $referee1   = getUserReferee($refID);
            $referees   = $referee1.','.$refID;
            $referee    = explode(',',$referees);
            break;
        case 2:
            $referee1   = getUserReferee($refID);
            $referee2   = getUserReferee($referee1);
            $referees   = $referee2.','.$referee1.','.$refID;
            $referee    = explode(',',$referees);
            break;
        case 3:
            $referee1   = getUserReferee($refID);
            $referee2   = getUserReferee($referee1);
            $referee3   = getUserReferee($referee2);
            $referees   = $referee3.','.$referee2.','.$referee1;
            $referee    = explode(',',$referees);
            break;
        case -1 :
            $referee    = '推荐人没有等级';
            break;
    }
    return $referee;
}


/**
 * 注册时给推荐人发奖励
 * @param $id   integer     用户ID
 * @return boolean  true: 奖励发放成功，且记录已写入 / false:奖励发放失败
 */
function regGiveUserBonus($id){
    $tbUserCoin = M('user_jifenyide');
    $tbCoinLog  = M('jifenyide_log');
    $referee    = getUserReferees($id);
    $count      = count($referee);
    $bouns00    = $bouns01 = $bouns11 = $bouns12 = $bouns21 = $bouns22 ='';
    $configMon1 = getUserConfig(2);
    $configMon2 = getUserConfig(3);
    $configVal1 = getUserConfig(4);
    $configVal2 = getUserConfig(5);
    $configVal3 = getUserConfig(6);
    $username   = getUser($id,'username');
    $username0  = getUser($referee[0],'username');
    $username1  = getUser($referee[1],'username');
    $username2  = getUser($referee[2],'username');
    $logData = array(
        'uid'   => $id,
        'time'  => date('Y-m-d H:i:s'),
    );
    switch($count){
        case 0:
            $bouns00 = 0;
            $bouns01 = 0;
            break;
        case 1:
            $username0 = getUser($referee,'username');
            $bouns00 = $configVal1*$configMon1;
            $bouns01 = $configVal1*$configMon2;
            $logData['info'] = $username.'注册成功，奖励原始会员'.$username0.'现金'.$bouns00.'，积分'.$bouns01.'。';
            $logData['type'] = 2;
            break;
        case 2:
            $bouns00 = $configVal2*$configMon1;
            $bouns01 = $configVal2*$configMon2;
            $bouns11 = $configVal1*$configMon1;
            $bouns12 = $configVal1*$configMon2;
            $logData['info'] = $username.'注册成功，奖励原始会员'.$username0.'现金'.$bouns00.'，积分'.$bouns01.',奖励一级会员'.$username1.'现金'.$bouns11.'，积分'.$bouns12.'。';
            $logData['type'] = 3;
            break;
        case 3:
            $bouns00 = $configVal3*$configMon1;
            $bouns01 = $configVal3*$configMon2;
            $bouns11 = $configVal2*$configMon1;
            $bouns12 = $configVal2*$configMon2;
            $bouns21 = $configVal1*$configMon1;
            $bouns22 = $configVal1*$configMon2;
            $logData['info'] = $username.'注册成功，奖励一级会员'.$username0.'现金'.$bouns00.'，积分'.$bouns01.',奖励二级会员'.$username1.'现金'.$bouns11.'，积分'.$bouns12.',奖励三级会员（推荐人）'.$username2.'现金'.$bouns21.'，积分'.$bouns22.'。';
            $logData['type'] = 4;
            break;
    }
    //推荐人发放奖励
    $re00 = $tbUserCoin -> where("uid = $referee") -> setInc('cash',$bouns00);
    $re01 = $tbUserCoin -> where("uid = $referee") -> setInc('cash',$bouns00);
    $re11 = $tbUserCoin -> where("uid = $referee[0]") -> setInc('cash',$bouns00);
    $re12 = $tbUserCoin -> where("uid = $referee[0]") -> setInc('jifen',$bouns01);
    $re21 = $tbUserCoin -> where("uid = $referee[1]") -> setInc('cash',$bouns11);
    $re22 = $tbUserCoin -> where("uid = $referee[1]") -> setInc('jifen',$bouns12);
    $re31 = $tbUserCoin -> where("uid = $referee[2]") -> setInc('cash',$bouns21);
    $re32 = $tbUserCoin -> where("uid = $referee[2]") -> setInc('jifen',$bouns22);
    //写入记录表
    $re4 = $tbCoinLog -> add($logData);
    if((($re00 && $re01) || ($re11 && $re12) || ($re21 && $re22) || ($re31 && $re32)) && $re4){
        return true;
//        pp('奖励发放成功，且记录已写入');
    }else{
        return false;
//        pp('奖励发放失败');
    }
}


/**
 * 注册时构建关系，写入relation表中的relation字段
 * @param $id   integer     用户ID
 * @return boolean  true: 构建关系成功 / false:构建关系失败
 */
function regInsertRelation($id){
    $tbUserRelation = M('user_relation');
    $referee    = getUserReferees($id);
    $count      = count($referee);
    if(is_string($referee)){
        $data['relation'] = $referee[0];
    }else{
        switch($count){
            case 0:
                $data['relation'] = '';
                break;
            case 1:
                $data['relation'] = $referee[0];
                break;
            case 2:
                $data['relation'] = $referee[0].','.$referee[1];
                break;
            case 3:
                $data['relation'] = $referee[0].','.$referee[1].','.$referee[2];
                break;
        }
    }
    //推荐人
    $re = $tbUserRelation -> where("uid = $id") -> save($data);
    if($re){
        return true;
//        pp('构建关系成功');
    }else{
        return false;
//        pp('构建关系失败');
    }
}


/**
 * 注册时根据推荐人，写入此用户的等级
 * @param $id   integer 当前注册用户的ID
 * @return boolean  true: 写入用户等级成功 / false:写入用户等级失败
 */
function regInsertClass($id){
    $tbUser = M('user');
    $refId  = getUserReferee($id);
    $class  = getUserClass($refId,'class');
    if($class == 3){
        $data = array(
            'class'     => $class,
            'classes'   => $class
        );
    }else{
        $data = array(
            'class'     => $class + 1,
            'classes'   => $class + 1
        );
    }
    $re = $tbUser -> where("id = $id") -> save($data);
    if($re){
        return true;
//        pp('写入用户等级成功');
    }else{
        return false;
//        pp('写入用户等级失败');
    }
    
}


/**
 * 获取用户直推数量/列表
 * @param $id   integer 用户ID
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
        default:
            $res = '参数错误';
            break;
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
    $total = refereeCounts($id,'count');
    switch($count){
        case $count < 5 :
            $re = 0;
            break;
        case $count >= 5 && $count < 10 :
            $re = 1;
            break;
        case $count >= 10 && $total >= 30:
            $re = 2;
            break;
        default:
            $re = 3;
    }
    return $re;
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
        default:
            return '参数错误';
    }
    
}
















