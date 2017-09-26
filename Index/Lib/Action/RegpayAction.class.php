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
    
        $id = 65;
        regGiveUserBonus($id);
        $this -> display();
    }
}