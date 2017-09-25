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
            /*待确认
            $referee1   = getUserReferee($id);
            $referee2   = getUserReferee($referee1);
            $referee3   = getUserReferee($referee2);
            $referees   = $referee3.','.$referee2.','.$referee1;
            $referee    = explode(',',$referees);
            */
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
 */
function regGiveUserBonus($id){
    $tbUserCoin = M('user_jifenyide');
    $tbCoinLog  = M('jifenyide_log');
    $referee    = getUserReferees($id);
    $count      = count($referee);
    $bouns0     = '';
    $bouns1     = '';
    $bouns2     = '';
    $logData = array(
        'uid'   => $id,
        'time'  => date('Y-m-d H:i:s'),
        'type'  => 2,
    );
    if(is_string($referee)){
        die;
    }else{
        switch($count){
            case 0:
                $bouns0 = 0;
                break;
            case 1:
                $bouns0 = 1000;
                $logData['info'] = '原始会员：'.$referee[0].','.$bouns0;
                break;
            case 2:
                $bouns0 = 1000;
                $bouns1 = 1000;
                $logData['info'] = '原始会员：'.$referee[0].',奖励'.$bouns0.',一级会员:'.$referee[1].',奖励：'.$bouns1;
                break;
            case 3:
                $bouns0 = 400;
                $bouns1 = 1000;
                $bouns2 = 1000;
                $logData['info'] = '原始会员：'.$referee[0].',奖励'.$bouns0.',一级会员:'.$referee[1].','.$bouns1.',一级会员（推荐人）:'.$referee[2].',奖励：'.$bouns2;
                break;
        }
        //推荐人发放奖励
        $re1 = $tbUserCoin -> where("uid = $referee[0]") -> setInc('yide',$bouns0);
        $re2 = $tbUserCoin -> where("uid = $referee[1]") -> setInc('yide',$bouns1);
        $re3 = $tbUserCoin -> where("uid = $referee[2]") -> setInc('yide',$bouns2);
        //写入记录表
        $re4 = $tbCoinLog -> add($logData);
        if(($re1 || $re2 || $re3) && $re4){
            pp('奖励发放成功，且记录已写入');
        }else{
            pp('奖励发放失败');
        }
    }
}


/**
 * 注册时构建关系，写入relation表中的relation字段
 * @param $id   integer     用户ID
 */
function regInsertRelation($id){
    $tbUserRelation = M('user_relation');
    $referee    = getUserReferees($id);
    $count      = count($referee);
    if(is_string($referee)){
        die;
    }else{
        switch($count){
            case 0:
                $data['relation'] = '';
                break;
            case 1:
                $data['relation'] = $referee[0].',';
                break;
            case 2:
                $data['relation'] = $referee[0].','.$referee[1].',';
                break;
            case 3:
                $data['relation'] = $referee[0].','.$referee[1].','.$referee[2].',';
                break;
        }
        //推荐人
        $re = $tbUserRelation -> where("uid = $id") -> save($data);
        if($re){
            pp('构建关系成功');
        }else{
            pp('构建关系失败');
        }
    }
}


/**
 * 注册时根据推荐人，写入此用户的等级
 * @param $id   integer 当前注册用户的ID
 */
function regInsertClass($id){
    $tbUser = M('user');
    $refId  = getUserReferee($id);
    $class  = getUserClass($refId,'class');
    if($class == 3){
        $data = array(
            'class'     => $class,
            'classes'   => $class //待定
        );
    }else{
        $data = array(
            'class'     => $class + 1,
            'classes'   => $class + 1
        );
    }
    $re = $tbUser -> where("id = $id") -> save($data);
    if($re){
        pp('写入用户等级成功');
    }else{
        pp('写入用户等级失败');
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


















