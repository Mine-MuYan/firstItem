<?php

class RegpayAction extends Action {
    
    public function pay(){
        $this -> display();
    }

    public function payCheck(){
        $id         = $_SESSION['id'];
        //更新用户状态
        $userData   = array(
            'ustatus'   => 1,
            'paystatus' => 2,
        );
        $userStatus = M('user') -> where("id = $id") -> save($userData);
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
        $userJfLog  = M('jifenyide_log') -> add($logData);
        $userNotice = regNotice($id);
        if($userStatus && $reYd && $reJf && $userNotice && $userJfLog){
            $this -> success('成功','__APP__/Index/index');
        }
        /*using when debugging
        elseif(!$userStatus){
            pp('用户状态更改失败');
        }elseif(!$reYd){
            pp('用户易得币发放失败');
        }elseif(!$reJf){
            pp('用户积分发放失败');
        }elseif(!$userNotice){
            pp('用户注册消息写入失败');
        }elseif(!$userJfLog){
            pp('log写入失败');
        }
        */
        else{
            $this -> error('失败','__APP__/Index/index');
        }
        //注册完成,销毁session
        unset($_SESSION['uid']);
        unset($_SESSION['Username']);
    }
    
    public function test(){
        pp('-----------------------');
    
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