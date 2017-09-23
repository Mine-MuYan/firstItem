<?php

/**
 * 类名：RegisterAction
 * 功能：实现注册以及注册需要的验证跳转操作
 */
class RegisterAction extends Action{
    public function register(){
        R('Base/header');
        R('Base/footer');
        $this -> display();
    }
    
    /**
     * 函数名：regist
     * 功能：用户注册方法
     */
    function regist(){
        $user = M('user');            //用户注册表
        $info = M('userinfo');        //用户详细信息表
        $relation = M('user_relation');   //用户关系表
        $isset = $user->where(array('username'=>$_POST['username']))->find(); // 查询用户名是否被注册
        if(empty($isset)){
            if($_POST['refreeName']){//如果推荐人有值
                if($_POST['refreeName'] == $_POST['username']){
                    $this -> error('推荐人不能是自己!','__APP__/Register/register');
                }else{
                    $searchRefree = $user -> where(array( 'username' => $_POST['refreeName'] )) -> find();
                    if($searchRefree){
                        $datas = array(
                            'username'  => $_POST['username'],
                            'userpwd'   => md5($_POST['userpwd']),
                            'ustatus'   => 1,
                            'paystatus' => 3,
                            'regtime'   => time(),
                            'regip'     => $_SERVER['REMOTE_ADDR'],
                            'eamil'     => $_POST['email'],
                            'class'     => $searchRefree['class'] - 1,    //会员等级
                            'classes'   => $searchRefree['class'],      //相对会员等级
                            'referee'   => $searchRefree['id'],
                        );
                        $id = $user -> add($datas);
                        $data['uid'] = $id;
                        //构造关系数据
                        $relationData = array(
                            'uid' => $id,
                            'pid' => $searchRefree['id'],
                        );
                        //注册关系表
                        $reRela = $relation -> add($relationData);
                        $reInfo = $info -> add($data);
                        if($reRela && $reInfo){
                            //给上级发奖励（关键代码）
                            $this -> assign('waitSecond','3');
                            $this -> success('注册成功,请等待审核','__APP__/Index/index');
                        }else{
                            $this -> error('注册失败，请重新注册!','__APP__/Register/register');
                        }
                    }else{
                        $this -> error('该推荐人不存在!','__APP__/Register/register');
                    }
                }
            }else{//如果推荐人没有值,则直接写入注册表
                $datas = array(
                    'username'  => $_POST['username'],
                    'userpwd'   => md5($_POST['userpwd']),
                    'ustatus'   => 1,
                    'paystatus' => 0,
                    'regtime'   => time(),
                    'regip'     => $_SERVER['REMOTE_ADDR'],
                    'class'     => 0,
                    'classes'   => 0,
                    'eamil'     => $_POST['email'],
                    'referee'   => 0,
                );
                //user表
                $id = $user -> add($datas);
                //关系表
                $relationData = array(
                    'uid' => $id,
                    'pid' => 0,
                );
                $reRela = $relation -> add($relationData);
                //用户信息表
                $data['uid'] = $id;
                $reInfo = $info -> add($data);
                //数据表
                $jifenData = array(
                    'uid'   => $id,
                );
                $reJifen = M('user_jifenyide') -> add($jifenData);
                if($id && $reInfo && $reRela && $reJifen){
                    $regData = $user -> where("id=$id") -> find();
                    $_SESSION['id'] = $regData['id'];
                    $_SESSION['Username'] = $regData['username'];
                    $this -> assign('waitSecond','3');
                    $this -> success('注册成功,请支付','__APP__/Regpay/pay');
                }else{
                    $this -> error('注册失败，请重新注册!','__APP__/Register/register');
                }
            }
        }else{
            $this -> error('此用户名已被注册过','__APP__/Register/register');
        }
    }
    
    /**
     * 函数名：Act
     * 功能：用户激活URL地址
     */
    function Act(){
        
        $user = M('user');
        
        $id = intval($_GET['u']);                                                 // 获取用户的id
        $email = trim($_GET['e']);                                                // 获取MD5加密信息
        $data['ustatus'] = 1;                                                     // 将用户的状态改为正常登陆
        
        $e = $user -> where(array( 'id' => $id,'ustatus' => 2 )) -> field('email') -> find(); // 查询用户邮箱地址
        
        if(md5($e['email']) == $email && !empty($e)){                             // 判断加密信息是否正确
            if($user -> where(array( 'id' => $_GET['u'] )) -> save($data)){
                $this -> success('激活成功','__APP__/Index/index');
            }else{
                $this -> error('激活失败');
            }
        }else{
            $this -> error('参数错误');
        }
    }
    
    
    /**
     * 函数名：verify
     * 功能：产生验证码
     */
    function verify(){
        import("ORG.Util.Image");
        Image ::buildImageVerify(4,1);
    }
    
    
    /**
     * 函数名：ajaxReg
     * 功能：ajax验证注册数据
     */
    Public function ajaxReg(){
        
        $username = trim($_GET['username']);
        $email = trim($_GET['email']);
        $vcode = trim($_GET['vcode']);
        $user = M('user');
        
        // 用户名验证
        if( !empty($username)){
            
            $condition['username'] = $username;
            $result = $user -> where($condition) -> find();
            
            // 如果返回的有数据说明此用户名已存在
            if(count($result)){
                $this -> ajaxReturn("UsernameFalse");
            }else{
                $this -> ajaxReturn("UsernameTrue");
            }
            unset($condition);
            
        }elseif( !empty($email)){
            
            // 邮箱验证
            $condition['email'] = $email;
            $result = $user -> where($condition) -> find();
            
            // 如果返回的有数据说明此邮箱已注册
            if(count($result)){
                $this -> ajaxReturn("EmailFalse");
            }else{
                $this -> ajaxReturn("EmailTrue");
            }
            unset($condition);
            
        }elseif( !empty($vcode)){
            
            // 注册码验证
            if($_SESSION['verify'] == md5($vcode)){
                $this -> ajaxReturn("VcodeTrue");
            }else{
                $this -> ajaxReturn("VcodeFalse");
            }
        }
    }
}