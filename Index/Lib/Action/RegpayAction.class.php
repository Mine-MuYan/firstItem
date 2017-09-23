<?php

class RegpayAction extends Action {
    
    public function pay(){
        $this -> display();
    }

    public function payCheck(){
        $id = $_SESSION['id'];
        $userCoin = M('user_jifenyide');
        $re = $userCoin -> where("uid = $id") -> setInc('yide',10000);
        if($re){
            $this -> success('成功','__APP__/Index/index');
        }else{
            $this -> error('失败','__APP__/Index/index');
        }
        //注册完成,销毁session
        unset($_SESSION['uid']);
        unset($_SESSION['Username']);
    }
    
    public function test(){
        $re = getUserReferee(45);
        pp($re);
        $this -> display();
    }
}