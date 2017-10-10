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
        if($field == '*'){
            $res = $re;
        }else{
            $res = $re[$field];
        }
    }else{
        $res = '获取数据失败';
    }
    return $res;
}


//根据ID查找用户的推荐信息、金额
function getUserRefCash($id){
    if(count($id) == 1){
        $re = getUser($id,'*');
        $rr = getUserCash($id);
        $re['jifen']    = $rr['jifen'];
        $re['yide']     = $rr['yide'];
        $re['cash']     = $rr['cash'];
        
    }else{
        $tbUser = M('user');
        $map['id'] = array('in',$id);
        $re = $tbUser -> where($map) -> order('id desc , regtime desc') -> select();
        foreach($re as $k => $v){
            $rr = getUserCash($v['id']);
            $re[$k]['jifen']    = $rr['jifen'];
            $re[$k]['yide']     = $rr['yide'];
            $re[$k]['cash']     = $rr['cash'];
            $ref = getUser($v['referee'],'username');
            $re[$k]['refName']  = $ref;
        }
    }
    return $re;
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
 * @return mixed/array    数组：原始会员，一级会员，二级会员
 * 推荐人的会员等级是二级或者三级，只找到上两级即可。
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
            $referees   = $referee2.','.$referee1.','.$refID;
            $referee    = explode(',',$referees);
            break;
        case -1 :
            $referee    = '推荐人没有等级';
            break;
    }
    return $referee;
}


/**
 * 获取用户的资金
 * @param $id   integer     用户ID
 * @return mixed|string
 */
function getUserCash($id){
    $re = M('user_jifenyide') -> where("uid = $id") -> find();
    if($re){
        return $re;
    }else{
        return '无此用户';
    }
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
    $logData    = array(
        'uid'   => $id,
        'time'  => date('Y-m-d H:i:s'),
    );
    $bounsValue = checkUserBouns($id);
    $extraMon1  = $bounsValue*$configMon1;
    $extraMon2  = $bounsValue*$configMon2;
    switch($count){
        case 0:
            $bouns00    = 0;
            $bouns01    = 0;
            break;
        case 1:
            $username0  = getUser($referee,'username');
            $bouns00    = ($configVal1+$bounsValue)*$configMon1;
            $bouns01    = ($configVal1+$bounsValue)*$configMon2;
            $logData['uids'] = $referee;
            if($extraMon1 == 0){
                $logData['info'] = $username.'注册成功，奖励邀请人（'.$username0.'）现金'.$bouns00.'，积分'.$bouns01.'。';
            }else{
                $logData['info'] = $username.'注册成功，奖励邀请人（'.$username0.'）现金'.$bouns00.'，积分'.$bouns01.'，额外奖励现金'.$extraMon1.'，积分'.$extraMon2.'。';
            }
            $logData['type'] = 2;
            break;
        case 2:
            $bouns00    = ($configVal2+$bounsValue)*$configMon1;
            $bouns01    = ($configVal2+$bounsValue)*$configMon2;
            $bouns11    = ($configVal1+$bounsValue)*$configMon1;
            $bouns12    = ($configVal1+$bounsValue)*$configMon2;
            $logData['uids'] = $referee[0].','.$referee[1];
            if($extraMon1 == 0){
                $logData['info'] = $username.'注册成功，奖励原始会员'.$username0.'现金'.$bouns00.'，积分'.$bouns01.',奖励邀请人（'.$username1.'）现金'.$bouns11.'，积分'.$bouns12.'。';
            }else{
                $logData['info'] = $username.'注册成功，奖励原始会员'.$username0.'现金'.$bouns00.'，积分'.$bouns01.',奖励邀请人（'.$username1.'）现金'.$bouns11.'，积分'.$bouns12.'，额外奖励邀请人现金'.$extraMon1.'，积分'.$extraMon2.'。';
            }
            $logData['type'] = 3;
            break;
        case 3:
            $bouns00    = ($configVal3+$bounsValue)*$configMon1;
            $bouns01    = ($configVal3+$bounsValue)*$configMon2;
            $bouns11    = ($configVal2+$bounsValue)*$configMon1;
            $bouns12    = ($configVal2+$bounsValue)*$configMon2;
            $bouns21    = ($configVal1+$bounsValue)*$configMon1;
            $bouns22    = ($configVal1+$bounsValue)*$configMon2;
            $logData['uids'] = $referee[0].','.$referee[1].','.$referee[2];
            if($extraMon1 == 0){
                $logData['info'] = $username.'注册成功，奖励一级会员'.$username0.'现金'.$bouns00.'，积分'.$bouns01.',奖励二级会员'.$username1.'现金'.$bouns11.'，积分'.$bouns12.',奖励邀请人（'.$username2.'）现金'.$bouns21.'，积分'.$bouns22.'。';
            }else{
                $logData['info'] = $username.'注册成功，奖励一级会员'.$username0.'现金'.$bouns00.'，积分'.$bouns01.',奖励二级会员'.$username1.'现金'.$bouns11.'，积分'.$bouns12.',奖励邀请人（'.$username2.'）现金'.$bouns21.'，积分'.$bouns22.'，额外奖励邀请人现金'.$extraMon1.'，积分'.$extraMon2.'。';
            }
            $logData['type'] = 4;
            break;
    }
    //写入记录表
    $res0   = $tbCoinLog -> add($logData);
    //推荐人发放奖励
    $re00   = $tbUserCoin -> where("uid = $referee") -> setInc('cash',$bouns00);
    $re01   = $tbUserCoin -> where("uid = $referee") -> setInc('jifen',$bouns01);
    $re11   = $tbUserCoin -> where("uid = $referee[0]") -> setInc('cash',$bouns00);
    $re12   = $tbUserCoin -> where("uid = $referee[0]") -> setInc('jifen',$bouns01);
    $re21   = $tbUserCoin -> where("uid = $referee[1]") -> setInc('cash',$bouns11);
    $re22   = $tbUserCoin -> where("uid = $referee[1]") -> setInc('jifen',$bouns12);
    $re31   = $tbUserCoin -> where("uid = $referee[2]") -> setInc('cash',$bouns21);
    $re32   = $tbUserCoin -> where("uid = $referee[2]") -> setInc('jifen',$bouns22);
    if((($re00 && $re01) || ($re11 && $re12) || ($re21 && $re22) || ($re31 && $re32))){
        if($res0){
//            pp('奖励发放成功，且记录已写入','1');
            return true;
        }else{
//            pp('奖励发放成功，但记录写入失败','2');
            return false;
        }
    }else{
//        pp('奖励发放失败','3');
        return false;
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
    switch($count){
        case 0:
            $data['relation'] = '';
            break;
        case 1:
            $data['relation'] = $referee;
            break;
        case 2:
            $data['relation'] = $referee[0].','.$referee[1];
            break;
        case 3:
            $data['relation'] = $referee[0].','.$referee[1].','.$referee[2];
            break;
    }
    //推荐人
    $re = $tbUserRelation -> where("uid = $id") -> save($data);
    if($re){
//        pp('构建关系成功');
        return true;
    }else{
//        pp('构建关系失败');
        return false;
    }
}


/**
 * 注册时根据推荐人，写入此用户的等级，并给邀请人的邀请人数+1
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
    $re     = $tbUser -> where("id = $id") -> save($data);
    $res    = $tbUser -> where("id = $refId") -> setInc('refcount',1);
    if($re && $res){
//        pp('写入用户等级成功，且邀请人的邀请人数+1成功');
        return true;
    }
    /** using when debugging
     elseif(!$re){
        pp('写入用户等级失败');
     }elseif(!$res){
        pp('邀请人的邀请人数+1写入失败');
     }
     */
    else{
//        pp('写入用户等级失败，且邀请人的邀请人数+1写入失败');
        return false;
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
 * 获取此用户的推荐人，一共推荐了多少人
 * @param $id   integer     用户ID
 * @return int|mixed|string
 */
function getRefereeCount($id){
    $ref        = getUserReferee($id);
    $refCount   = refereeCount($ref,'count');
    return $refCount;
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
 * 获取注册时的消息
 * @param $id   integer 用户ID
 * @return bool|mixed
 */
function getJifenNotice($id){
    $tbRealtion = M('jifenyide_log');
    $re = $tbRealtion -> where("FIND_IN_SET($id, uids)") -> order('time desc')-> select();
    if($re){
        return $re;
    }else{
        return false;
    }
}


/**
 * 获取用户的注册、充值、提现消息
 * @param $id
 * @return bool|mixed
 */
function getNotice($id){
    $tbNotice = M('user_notice');
    $re = $tbNotice -> where("uid = $id") -> order('time desc') -> select();
    if($re){
        return $re;
    }else{
        return false;
    }
}


/**
 * 检测用户是否可以享受广告补贴（加权分红待完善）
 * @param $id   integer 推荐人ID
 * @return int  0：没有资格，不奖励；1：有，奖励广告费；2：奖励广告费并且可以享受加权分红；3：代码错误
 */
function checkUserBouns($id){
    $count      = getRefereeCount($id);
    $total      = refereeCounts($id,'count');
    $configVal1 = getUserConfig(9);
    $configVal2 = getUserConfig(10);
    $configVal3 = getUserConfig(11);
    $confExtra  = getUserConfig(12);    //广告费
    if($count < $configVal1){
        $re = 0;
    }elseif($count >= $configVal1 && $count < $configVal2 ){
        $re = $confExtra;
    }elseif($count >= $configVal2 && $total >= $configVal3){
        $re = $confExtra + 1;
    }else{
        $re = 0;
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
        case 'all':
            return $re;
        default:
            return '参数错误';
    }
}


/**
 * 注册时写入消息表
 * @param $id   integer     用户ID
 * @return bool true：成功；false：失败
 */
function regNotice($id){
    $tbNotice   = M('user_notice');
    //通知用户注册成功
    $noticeData = array(
        'uid'   => $id,
        'info'  => '恭喜您注册成功，推荐人奖励已发，快去邀请小伙伴注册吧。',
        'time'  => date('Y-m-d H:i:s'),
        'type'  => 1
    );
    $res  = $tbNotice -> add($noticeData);
    if($res){
        return true;
    }else{
        return false;
    }
}














