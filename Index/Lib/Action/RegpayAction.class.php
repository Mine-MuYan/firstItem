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
            'uids'  => $id,
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
    
    //test页面
    public function test(){
//        pp('-----------------------');
//
//        pp($this -> numOfChoice(3,2));
//        pp($this -> triangle(3,4,5));
        pp($_SESSION['Username']);
        $this -> display();
    }
    
    //test功能
    public function tests(){
        $arr = array(
            'name'  => 'zhangsan',
            'ages'  => '18',
            'type'  => '111',
        );
        
        $this -> ajaxReturn($arr,'success','1','json');
    }
    
    //三角形
    public function triangle($a,$b,$c)
    {
        if(($a+$b>$c) && ($a+$c>$b) && ($b+$c>$a) && (abs($a-$b)<$c)  && (abs($a-$c)<$b)  && (abs($b-$c)<$a)){
            return "yes";
        }else{
            return "no";
        }
    }
    
    //从m个数中取出n个，有多少种可能
    public function numOfChoice($m,$n){
        $fenzi = 1; $zi = $m;
        $fenmu = 1; $mu = $n;
        //循环n次
        for($i=1;$i<=$n;$i++){
            //累乘
            $fenzi = $zi*$fenzi;
            //自减
            $zi--;
        }
        
        //循环m次
        for($j=1;$j<=$n;$j++){
            //累乘
            $fenmu = $mu*$fenmu;
            //自减
            $mu--
            ;
        }
        //求商
        $result = $fenzi/$fenmu;
        return $result;
    }
    
    
    public function one($num){
        $arr = [];
        $k=0;
        if($num < 1 || $num >50){
            return '请输入1~50之间的正整数';
        }else{
            for($i=1;$i<500;$i++){
                $count=0;
                for($j=1;$j<$i;$j++){
                    if($i%$j==0){
                        $count++;
                    }
                }
                if($count==1){
                    $arr[]=$i;
                    if(count($arr) <= $num){
                        $k+=$i;
                    }
                }
            }
            return $k;
        }
    }
    
}