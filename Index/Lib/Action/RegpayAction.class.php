<?php

class RegpayAction extends Action {
    
    public function pay(){
        $this -> display();
    }

    public function payCheck(){
        $id         = $_SESSION['id'];
        $userCoin   = M('user_jifenyide');
        $configJf   = getUserConfig(7);
        $configYd   = getUserConfig(8);
        $reYd       = $userCoin -> where("uid = $id") -> setInc('yide',$configYd);
        $reJf       = $userCoin -> where("uid = $id") -> setInc('jifen',$configJf);
        $username   = getUser($id,'username');
        $logData    = array(
            'uid'   => $id,
            'info'  => '原始会员'.$username.'注册成功，奖励'.$configJf.'积分，并赠送'.$configYd.'易得币。',
            'time'  => date('Y-m-d H:i:s'),
            'type'  => 1
        );
        M('jifenyide_log') -> add($logData);
        if($reYd && $reJf){
            $this -> success('成功','__APP__/Index/index');
        }else{
            $this -> error('失败','__APP__/Index/index');
        }
        //注册完成,销毁session
        unset($_SESSION['uid']);
        unset($_SESSION['Username']);
    }
    
    public function test(){
//        $city = array('f','s');
//        $this -> sss($city);
        
//        pp(getUserReferees(81));
//        pp(getUserReferee(98));
        /*
        $arr = "array('name'=>'张三','age'=>'16')";
        $str = [];
        eval("\$str = ".$arr.'; ');
        pp($str);
        pp($str['name']);
        */
        
        
        //列出所有直推人数大于10 且团购人数大于30的用户
        
        $dbUser = M('user');
        $refCount = getUserConfig('13');
        $allCount = getUserConfig('14');
        $map['refcount'] = array('egt',$refCount);
        $userId = $dbUser -> where($map) -> field('id') -> select();
        $ids = [];
        foreach($userId as $k){
            $allNum = refereeCounts($k['id'],'count');
            if($allNum >= $allCount){
                $ids[] = $k['id'];
            }
         }
         pp($userId);
         pp($ids);
        $this -> display();
    }
    
    public function sss($city){
        $user   = M('user');
        if(count($city) == 1){
            $map['username'] = array('like',"%$city%");
            $reUser = $user -> where($map) -> select();
        }else{
            foreach($city as $k => $v){
                $map['username'] = array('like',"%$city[$k]%");
                $reUser[] = $user -> where($map) -> select();
            }
        }
        return $reUser;
    }
}