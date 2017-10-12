<?php 

	/**
	 * 类名：UserAction
	 * 功能：用户管理模块
	 */
	class UserAction extends Action{

        /**
         * 函数名：new
         * 功能：遍历状态为2的用户列表
         */
        function application(){
            R('Level/listuser'); // 验证权限
            $user = D('user')->where('ustatus=2')->select();
            $userinfo = D('userinfo');
            $userrelation = D('user_relation'); //读取用户关系表
            $userjifenyide= D('user_jifenyide');//读取积分易得表

            foreach ($user as $row => $value)
            {
               if( $relationData = $userrelation->where(array('uid'=>$value['id']))->find()){//获得关系表的pid
                   if($puser =D('user')->where(array('id'=>$relationData['pid']))->find()){  //获得关系表中的pid对应的username
                       $user[$row]['pusername'] = $puser['username'];
               }
               }
               else{$user[$row]['pusername'] = '没有推荐人';}
                $jifenyideData= $userjifenyide->where(array('uid'=>$value['id']))->find();//获得积分易得表的uid
                if($jifenyideData){
                    $user[$row]['jifen'] = $jifenyideData['jifen'];
                    $user[$row]['yide'] = $jifenyideData['yide'];
                }

            }

            $this->assign('info',$userinfo->select());  // 将用户附表信息赋值到模板
            $this->assign('data',$user); 		        // 将用户基本信息赋值到模板

            $this->display();

        }

		/**
		 * 函数名：lists
		 * 功能：遍历用户列表
		 */
		function lists(){
			R('Level/listuser'); // 验证权限
			$user = D('user')->select();
			$userinfo = D('userinfo');
            $userrelation = D('user_relation'); //读取用户关系表
            $userjifenyide= D('user_jifenyide');//读取积分易得表

            foreach ($user as $row => $value)
            {
                if($relationData = $userrelation->where(array('uid'=>$value['id']))->find())//获得关系表的pid
                {
                    if($puser =D('user')->where(array('id'=>$relationData['pid']))->find()){  //获得关系表中的pid对应的username
                        $user[$row]['pusername'] = $puser['username'];
                    }
                }else{$user[$row]['pusername'] = '没有推荐人';}

                $jifenyideData= $userjifenyide->where(array('uid'=>$value['id']))->find();//获得积分易得表的uid
                if($jifenyideData){
                    $user[$row]['jifen'] = $jifenyideData['jifen'];
                    $user[$row]['yide'] = $jifenyideData['yide'];
                }

            }
			$this->assign('info',$userinfo->select());  // 将用户附表信息赋值到模板
			$this->assign('data',$user); 		// 将用户基本信息赋值到模板
			$this->display();
		}

		/**
		 * 函数名：add
		 * 功能：添加用户调用模板
		 */
		function add(){

			
			$this->display();
		}

		/**
		 * 函数名：inse
		 * 功能：添加用户至数据库
		 * @return bool 判断用户是否添加成功
		 */
		function inse(){
			
			R('Level/adduser'); // 验证权限
			$user = D('user');
			$info = D('userinfo');

			/* 判断是否上传了头像 */
			if(!empty($_FILES['headpic']['name'])){
				$pic = $this->uploads();
				$_POST['headpic'] = $pic[0]['savename'];
			}

			$_POST['uid'] = $user->addu();												 // 调用自定义Model类下的addu函数实现自动完成

			$_POST['birthday'] = $_POST['year'].'-'.$_POST['month'].'-'.$_POST['day'];   // 拼接会员生日
			$_POST['city'] = $_POST['province'].'-'.$_POST['city'].'-'.$_POST['county']; // 拼接居住地
			if($_POST['city'] == '省份-地级市-市、县级市'){ 							 // 判断是否修改了居住地
				unset($_POST['city']);
			}
			if($_POST['uid'] && $info->add($_POST)){
				$this->success("注册成功","lists");
			}else{
				$this->error("注册失败");
			}
		}

		/**
		 * 函数名：upda
		 * 功能：将要修改的用户查询信息并赋值到修改模板
		 * @param  string $id 要修改的用户id
		*/
		function upda($id){
			$id = intval($id);
			
			$user = D('user');
			$info = M('userinfo');
			$region = M('region');

			$uinfo = $info->where(array('uid'=>$id))->field('loginip,logintime',true)->find();
			$uinfo['birthday'] = explode('-',$uinfo['birthday']);								// 拼接生日
			$uinfo['city'] = explode('-',$uinfo['city']);									    // 拼接居住地

			$prodata = $region->where("region_type=1")->select();
			//获取省份
			$this->assign("prodata",$prodata);
			$this->assign('info',$uinfo);
			$this->assign('data',$user->where(array('id'=>$id))->find());
			$this->display();
		}

		/**
		 * 函数名：edit
		 * 功能：修改用户信息至数据库
		 * @return bool 判断是否修改成功
		 */
		function edit(){

			R('Level/saveuser'); // 验证权限
			$id = intval($_POST['id']);

			$user = D('user');
			$info = M('userinfo');
            $region = M('region');
            $set    = M('user_set');                //设置表
            $jifenyidelog  = M('jifenyide_log');  //用户积分易得表
            $userjifenyide  = M('user_jifenyide');  //用户积分易得表
            $relation       = M('user_relation');   //用户关系表

            if($_POST['ustatus']==1){//判断用户登录状态是否为1
                //查询积分易得表
               if($userjifenyide->where('uid='.$_POST['id'])->find()){
                   //说明该用户积分已经送达不必再加积分了
               }
               else{
                  //查询用户表
                   $userData= $user->where('id='.$id)->find();
                  //获取用户名
                   $userName = $userData['username'];
                  //查询设置表
                   $setData = $set->where('id=1')->find();
                   //获得设置的注册奖励积分值
                   $regjifen = $setData['reg_jifen'];
                   //构造用户积分易得表记录
                   $jifenData = array(
                       'uid'  => $_POST['id'],
                       'jifen'=> $regjifen,
                   );
                   //新增用户注册积分
                   $userjifenyide->add($jifenData);
                   //写入积分易得记录表
                   $jfydLog = array(
                       'uid' => $_POST['id'],
                       'info'=> "用户{$userName}成功注册获得积分{$regjifen}",
                       'time'=> date( 'y-m-d h:i:s', time()),
                       'type'=> 1,
                    );
                   $jifenyidelog->add($jfydLog);
                   //该用户的上级也要获得积分奖励
                   $jifenSecond = $setData['second_jifen'];       //获得设置的二级用户积分奖励比例
                   $amountSecond= $regjifen*$jifenSecond/100;     //获得二级会员实际奖励积分值
                   //查询该用户的上级id
                   $staffSecond = $relation->where('uid='.$_POST['id'])->find();
                   if($staffSecond['pid'])
                   {//说明该用户存在上级id,就可以根据这个上级id更新积分表
                       $pid = $staffSecond['pid'];
                       $staffSecondData = $userjifenyide->where('uid='.$staffSecond['pid'])->find();
                       $userNameData    = $user->where('id='.$pid)->find();//查询该pid在user表中的信息
                       $puserName =$userNameData['username'];              //查询该pid的username信息
                       if($staffSecondData){
                           $total = $staffSecondData['jifen']+$amountSecond;
                           $refData = array(
                               'jifen' => $total,
                           );
                           $userjifenyide->where('uid='.$staffSecond['pid'])->save($refData);
                           //写入积分易得记录表
                           $jfydLog = array(
                               'uid' => $_POST['id'],
                               'info'=> "用户{$userName}的上级推荐者{$puserName}成功获得注册积分抽成{$amountSecond}",
                               'time'=> date( 'y-m-d h:i:s', time()),
                               'type'=> 1,
                           );
                           $jifenyidelog->add($jfydLog);
                       }

                   }
                   //该用户的上上级也要获得积分奖励
                   $jifenFirst  = $setData['first_jifen'];        //获得设置的一级用户积分奖励比例
                   $amountFirst = $regjifen*$jifenFirst/100;  //获得一级会员实际奖励积分值
                   //查询该用户的上上级id
                   $staffSecond = $relation->where('uid='.$_POST['id'])->find();
                   if($staffSecond['pid'])
                   {//说明该用户存在上级id,就可以根据这个上级id查询上上级id
                       $staffFirst = $relation->where('uid='.$staffSecond['pid'])->find();
                            if($staffFirst['pid']){
                                $ppid = $staffFirst['pid'];
                                //说明存在这个上上级id,则根据这个上上级id更新积分表
                                $staffFirstData = $userjifenyide->where('uid='.$staffFirst['pid'])->find();
                                $userNameData   = $user->where('id='.$ppid)->find();//查询该ppid在user表中的信息
                                $ppuserName =$userNameData['username'];             //查询该ppid的username信息
                                if($staffFirstData){
                                    $total = $staffFirstData['jifen']+$amountFirst;
                                    $refData = array(
                                        'jifen' => $total ,
                                    );
                                    $userjifenyide->where('uid='.$staffFirst['pid'])->save($refData);
                                    //写入积分易得记录表
                                    $jfydLog = array(
                                        'uid' => $_POST['id'],
                                        'info'=> "用户{$userName}的上上级推荐者{$ppuserName}成功获得注册积分抽成{$amountFirst}",
                                        'time'=> date( 'y-m-d h:i:s', time()),
                                        'type'=> 1,
                                    );
                                    $jifenyidelog->add($jfydLog);
                                }

                            }
                   }
               }
            }

			if(empty($_POST['userpwd'])){ 										 // 判断是否修改了密码
				unset($_POST['userpwd']);
			}else{
				$lpwd = $user->where(array('id'=>$id))->field('userpwd')->find();// 获取原有密码
				if($lpwd['userpwd'] != md5($_POST['userpwd'])){					 // 判断原密码是否与要修改的密码相同
					$_POST['userpwd'] = md5($_POST['userpwd']);
				}else{
					$this->error('密码没有被修改');
				}
			}

			// 判断是否修改了头像
			if(!empty($_FILES['headpic']['name'])){

				$lpic = $user->where(array('id'=>$id))->field('headpic')->find();
				
				if($lpic['headpic'] != 'headpic.gif' && !empty($lpic['headpic'])){ // 判断之前的头像不是自定义头像
					unlink('Public/Uploads/headpic/'.$lpic['headpic']); 		   // 删除原有头像
				}

				$headpic = $this->uploads();
				$_POST['headpic'] = $headpic[0]['savename'];
			}

			$province = $region->where(array('region_id'=>$_POST['province']))->field('region_name')->find();
			$city = $region->where(array('region_id'=>$_POST['city']))->field('region_name')->find();
			$county = $region->where(array('region_id'=>$_POST['county']))->field('region_name')->find();


			$_POST['birthday'] = $_POST['year'].'-'.$_POST['month'].'-'.$_POST['day'];   // 拼接会员生日
			$_POST['city'] = $province['region_name'].'-'.$city['region_name'].'-'.$county['region_name'];	 // 拼接居住地

			if($info->where(array('uid'=>$id))->save($_POST) | $user->edit()){ 			 // 如果修改了任意一个表信息则为成功
				$this->success('修改成功','lists');
			}else{
				$this->error('修改失败','lists');
			}
		}

		/**
		 * 函数名：del
		 * 功能：删除用户信息
		 * @param  string $id 要删除的用户的id
		 * @return bool     判断是否删除成功
		 */
		function del($id){

			R('Level/deluser'); // 验证权限
			$id = intval($id);

			$user = D('user');
			$info = M('userinfo');

			$pic = $user->where(array('id'=>$id))->field('headpic')->find();

			if($pic['headpic'] != 'headpic.gif' && !empty($pic['headpic'])){ // 判断原有头像是否是默认头像
				unlink('Public/Uploads/headpic/'.$pic['headpic']);			 // 删除用户头像
			}

			if($user->where(array('id'=>$id))->delete() && $info->where(array('uid'=>$id))->delete()){
				$this->success('删除成功');
			}else{
				$this->error('删除失败');
			}

		}

		/**
		 * 函数名：dels
		 * 功能：批量删除用户信息
		 */
		function dels(){

			R('Level/delsuser'); // 验证权限
			$id = join(',',$_POST['check']); // 拼接要删除的用户的id

			$user = M('user');
			$info = M('userinfo');

			$pics = $user->where(array('id'=>array('exp','IN('.$id.')')))->select(); // 获取要删除的用户信息
			
			foreach($pics as $row){													 // 循环删除用户头像
				if($row['headpic'] != 'headpic.gif' && !empty($row['headpic'])){

					unlink('Public/Uploads/headpic/'.$row['headpic']);				 // 删除用户头像
				}
			}

			$us = $user->where(array('id'=>array('exp','IN('.$id.')')))->delete();	 // 删除用户表内容
			$in = $info->where(array('uid'=>array('exp','IN('.$id.')')))->delete();	 // 删除用户附表内容

			if($us && $in){
				$this->success('删除成功');
			}else{
				$this->error('删除失败');
			}
		}

		//获取省
		function getprivince(){
			$region = M('region');
			$arr = $region->where("region_type=1")->select();
			$str=json_encode($arr);
			echo $str;
		}

		//获取城市
		function getcity(){
			$region_id = $_GET['region_id'];
			$region = M('region');
			$arr = $region->where("parent_id=$region_id")->select();
			$str=json_encode($arr);
			echo $str;
		}

		//获取地区
		function getarea(){
			$region_id = $_GET['region_id'];
			$region = M('region');
			$arr = $region->where("parent_id=$region_id")->select();
			$str=json_encode($arr);
			echo $str;
		}

		/**
		 * 函数名：uploads
		 * 功能：图片上传函数
		 * @return  array 上传后的文件详细信息
		 */
		function uploads(){

			import('ORG.Net.UploadFile');
			$upload = new UploadFile();
			$upload->maxSize  = 3145728999;
			$upload->allowExts  = array('jpg', 'gif', 'png', 'jpeg');
			$upload->savePath =  './Public/Uploads/headpic/';
			

			if(!$upload->upload()){// 上传错误提示错误信息
				$this->error($upload->getErrorMsg());
			}else{// 上传成功 获取上传文件信息
				return $upload->getUploadFileInfo();
			}
		}
        
        /**
         * 获取用户拨比设置
         */
        function userConfig(){
		    $re = M('user_config')-> select();
		    foreach($re as $k => $v){
                $re[$k]['values'] = $re[$k]['value'] * $re[$k]['ratio'];
            }
		    $this -> assign('re',$re);
            $this -> display();
        }
        
        /**
         * 编辑用户拨比页面
         */
        function userConfigEdit(){
            $id = $_GET['id'];
            $re = getUserConfig($id,'all');
            $this -> assign('re',$re);
            $this -> display();
        }
        
        /**
         * 编辑用户拨比功能
         */
        function userConfigEdits(){
            $id     = I('id');
            $value  = I('value');
            if(!is_numeric($value)){
                $this -> error('值要是数字哦');
            }
            $ratio = I('ratio');
            if(!is_numeric($ratio)){
                $this -> error('比率要是数字哦');
            }
            $data = array(
                'value'     => $value,
                'ratio'     => $ratio,
                'comment'   => I('comment'),
            );
            M('user_config')-> where("id = $id") -> save($data);
            $this -> success('修改成功','__URL__/userConfig');
        }
        
        /**
         * 添加编辑用户拨比页面
         */
        function userConfigAdd(){
            
            $this -> display();
        }
        
        /**
         * 添加编辑用户拨比功能
         */
        function userConfigAdds(){
            $value = I('value');
            if(!is_numeric($value)){
                $this -> error('值要是数字哦');
            }
            $ratio = I('ratio');
            if(!is_numeric($ratio)){
                $this -> error('比率要是数字哦');
            }
            $data = array(
                'value'     => $value,
                'ratio'     => $ratio,
                'comment'   => I('comment'),
            );
            M('user_config') -> add($data);
            $this -> success('添加成功','__URL__/userConfig');
        }
        
        /**
         * 可获得补贴的用户列表
         */
        function userGiven(){
            $dbUser = M('user');
            $map = array(
                'carcount' => array('gt',0),
                'ustatus' => 1,
                'bounsday' => array('lt',101),
            );
            $re = $dbUser -> where($map) -> limit('100') -> order('id asc') -> select();
            $this -> assign('re',$re);
            $this -> display();
        }
        
        /**
         * 给已购车用户一键发放补贴（in 写法）
         */
        function bounsGivenOnce(){
            $dbUser     = M('user');
            $vipCount   = getUserConfig('14');
            $map = array(
                'carcount' => array('gt',0),
                'ustatus' => 1,
                'bounsday' => array('elt',100),
            );
            $userIds    = $dbUser -> where($map) -> limit($vipCount) -> field('id') -> select();
            $ydCount    = getUserConfig('13');
            $uids = [];
            if(empty($userIds)){
                $this -> error('暂时没有可发放分红的用户哦');
            }else{
                foreach($userIds as $k){
                    $uids[] = $k['id'];
                }
                $uids = implode(',',$uids);
                $mapUserDay = array(
                    'id'        => array('in',$uids),
                    'bounstime' => array('lt',date('Y-m-d 00:00:00'))
                );
                $reUserDay = $dbUser -> where($mapUserDay) -> setInc('bounsday',1); //天数+1
                if($reUserDay){
                    $mapUserTime['id'] = array('in',$uids);
                    $dataUserTime['bounstime'] = date('Y-m-d H:i:s');
                    $reUserTime = $dbUser -> where($mapUserTime) -> save($dataUserTime); //时间改为当前时间
                    if($reUserTime){
                        $dataCoin['uid'] = array('in',$uids);
                        $reCoin = M('user_jifenyide') -> where($dataCoin) -> setInc('yide',$ydCount);
                        if($reCoin){
                            $logData = array(
                                'uid'   => 0,
                                'uids'  => $uids,
                                'info'  => "您的购车补贴$ydCount 易得币已发放。",
                                'time'  => date('Y-m-d H:i:s'),
                                'type'  => 5
                            );
                            $reLog = M('jifenyide_log') -> add($logData);
                            $noticeData = array(
                                'uid'   => $uids,
                                'info'  => "您的购车补贴$ydCount 易得币已发放。",
                                'time'  => date('Y-m-d H:i:s'),
                                'type'  => 4
                            );
                            $reNotice = M('user_notice') -> add($noticeData);
                            $bounsData = array(
                                'uid'   => $uids,
                                'admin' => $_SESSION['username'],
                                'money' => $ydCount,
                                'time'  => date('Y-m-d H:i:s'),
                                'type'  => 1,
                                'mtype' => 1
                            );
                            $reBouns = M('user_bouns') -> add($bounsData);
                            if($reLog && $reNotice && $reBouns){
                                $this -> success('发放成功','__URL__/userGiven');
                            }
                            /***  using when debugging
                            elseif(!$reLog){
                                pp('记录表写入失败');
                            }elseif(!$reNotice){
                                pp('通知表写入失败');
                            }elseif(!$reBouns){
                                pp('奖励表写入失败');
                            }
                             */
                            else{
                                $this -> error('发放失败');
                            }
                        }else{
                            $this -> error('易得币发放失败');
                        }
                    }
                }else{
                    $this -> error('今天已发放过补贴，请勿多次点击','__URL__/userGiven');
                }
            }
            
        }
        
        
        /**
         * 给已购车用户一键发放补贴（循环 写法）
         */
        function bounsGivenOnces(){
            $dbUser     = M('user');
            $vipCount   = getUserConfig('14');
            $map = array(
                'carcount' => array('gt',0),
                'ustatus' => 1,
                'bounsday' => array('elt',100),
            );
            $userIds    = $dbUser -> where($map) -> limit($vipCount) -> field('id') -> select();
            $ydCount    = getUserConfig('13');
            $reNotice   = $reLog = $reBouns = [];
            if(empty($userIds)){
                $this -> error('暂时没有可发放补贴的用户哦');
            }else{
                foreach($userIds as $k){
                    $uid = $k['id'];
                    $map = array(
                        'id'        => $uid,
                        'bounstime' => array('lt',date('Y-m-d 00:00:00'))
                    );
                    $reUserDay = $dbUser -> where($map) -> setInc('bounsday',1); //天数+1
                    if(!$reUserDay){
                        $this -> error('今天已经发放过补贴，不要重复点击哦');
                    }else{
                        $reUserTime = $dbUser -> where("id = $uid") -> save(array('bounstime' => date('Y-m-d H:i:s'))); //时间改为当前时间
                        if(!$reUserTime){
                            $this -> error('时间修改失败且补贴发放失败');
                        }else{
                            $reCoin = M('user_jifenyide') -> where("uid = $uid") -> setInc('yide',$ydCount);
                            if(!$reCoin){
                                $this -> error('补贴写入失败');
                            }else{
                                $logData = array(
                                    'uid'   => $uid,
                                    'uids'  => $uid,
                                    'info'  => "您的购车补贴$ydCount 易得币已发放。",
                                    'time'  => date('Y-m-d H:i:s'),
                                    'type'  => 5
                                );
                                $reLog[] = M('jifenyide_log') -> add($logData);
                                $noticeData = array(
                                    'uid'   => $uid,
                                    'info'  => "您的购车补贴$ydCount 易得币已发放。",
                                    'time'  => date('Y-m-d H:i:s'),
                                    'type'  => 4
                                );
                                $reNotice[] = M('user_notice') -> add($noticeData);
                                $bounsData = array(
                                    'uid'   => $uid,
                                    'aid'   => $_SESSION['uid'],
                                    'money' => $ydCount,
                                    'type'  => 1,
                                    'mtype' => 1
                                );
                                $reBouns[] = M('mss_user_bouns') -> add($bounsData);
                            }
                        }
                    }
                }
                if(!in_array(0,$reLog) && !in_array(0,$reNotice) && !in_array(0,$reBouns)){
                    $this -> success('补贴发放成功','__URL__/userGiven');
                }
                /** using when debugging
                elseif(in_array(0,$reLog)){
                pp($reLog,'log写入失败');
                }elseif(in_array(0,$reNotice)){
                pp($reNotice,'通知写入失败');
                }elseif(in_array(0,$reBouns)){
                pp($reBouns,'分红表写入失败');
                }
                 */
                else{
                    $this -> error('补贴发放失败','__URL__/userGiven');
                }
            }
        }
        
        
    
        /**
         * 可获得分红的用户列表
         */
        public function userFenHong(){
            $res = fenHongUser();
            $this -> assign('re',$res);
            $this -> display();
        }
        
        /**
         * 给可获得分红的用户一键发分红（in 写法）
         */
        public function GiveFenHong(){
            $users      = fenHongUser(1);
            $fenHong    = getUserConfig('15');
            $uids       = [];
            if(!$users){
                $this -> error('暂时没有可发放分红的用户或本月分红已经发放过');
            }else{
                foreach($users as $k => $v){
                    $uids[] = $v['id'];
                }
                $uids = implode(',',$uids);
                $fenHongData['fenhtime'] = date('Y-m-d H:i:s');
                $fenHongMap['id'] = array('in',$uids);
                $reUsers = M('user') -> where($fenHongMap) -> save($fenHongData);
                if($reUsers){
                    $coinMap['uid'] = array('in',$uids);
                    $reCoin = M('user_jifenyide') -> where($coinMap) -> setInc('cash',$fenHong);
                    if($reCoin){
                        $logData = array(
                            'uid'  => 0,
                            'uids' => $uids,
                            'info' => "恭喜您本月获得公司加权分红，现金$fenHong 。",
                            'time' => date('Y-m-d H:i:s'),
                            'type' => 6
                        );
                        $reLog = M('jifenyide_log') -> add($logData);
                        $noticeData = array(
                            'uid'  => $uids,
                            'info' => "恭喜您本月获得公司加权分红，现金$fenHong 。",
                            'time' => date('Y-m-d H:i:s'),
                            'type' => 5
                        );
                        $reNotice = M('user_notice') -> add($noticeData);
                        $bounsData = array(
                            'uid'   => $uids,
                            'admin' => $_SESSION['username'],
                            'money' => $fenHong,
                            'time'  => date('Y-m-d H:i:s'),
                            'type'  => 2,
                            'mtype' => 2
                        );
                        $reBouns = M('user_bouns') -> add($bounsData);
                        if($reLog && $reNotice && $reBouns){
                            $this -> success('分红发放成功','__URL__/userFenHong');
                        }
                        /***  using when debugging
                        elseif(!$reLog){
                        pp('记录表写入失败');
                        }elseif(!$reNotice){
                        pp('通知表写入失败');
                        }elseif(!$reBouns){
                        pp('奖励表写入失败');
                        }
                         */
                        else{
                            $this -> error('分红发放失败');
                        }
                    }else{
                        $this -> error('分红发放失败');
                    }
                }else{
                    $this -> error('本月已发放过分红，请勿多次点击');
                }
            }
        }
        
        
        /**
         * 给可获得分红的用户一键发分红（循环 写法）
         */
        public function GiveFenHongs(){
            $users      = fenHongUser(1);
            $fenHong    = getUserConfig('15');
            $reNotice   = $reLog = $reBouns = 0;
            if(!$users){
                $this -> error('暂时没有可发放分红的用户或本月分红已经发放过');
            }else{
                foreach($users as $k => $v){
                    $uid = $v['id'];
                    $fenHongData['fenhtime'] = date('Y-m-d H:i:s');
                    $reUsers = M('user') -> where("id = $uid") -> save($fenHongData);
                    if(!$reUsers){
                        $this -> error('时间更改失败');
                    }else{
                        $reCoin = M('user_jifenyide') -> where("uid = $uid") -> setInc('cash',$fenHong);
                        if(!$reCoin){
                            $this -> error('分红写入失败');
                        }else{
                            $logData = array(
                                'uid'  => $uid,
                                'uids' => $uid,
                                'info' => "恭喜您本月获得公司加权分红，现金$fenHong 。",
                                'time' => date('Y-m-d H:i:s'),
                                'type' => 6
                            );
                            $reLog[] = M('jifenyide_log') -> add($logData);
                            $noticeData = array(
                                'uid'  => $uid,
                                'info' => "恭喜您本月获得公司加权分红，现金$fenHong 。",
                                'time' => date('Y-m-d H:i:s'),
                                'type' => 5
                            );
                            $reNotice[] = M('user_notice') -> add($noticeData);
                            $bounsData = array(
                                'uid'   => $uid,
                                'aid'   => $_SESSION['uid'],
                                'money' => $fenHong,
                                'type'  => 2,
                                'mtype' => 2
                            );
                            $reBouns[] = M('mss_user_bouns') -> add($bounsData);
                        }
                    }
                }
                if(!in_array(0,$reLog) && !in_array(0,$reNotice) && !in_array(0,$reBouns)){
                    $this -> success('分红发放成功','__URL__/userFenHong');
                }
                /** using when debugging
                elseif(in_array(0,$reLog)){
                pp($reLog,'log写入失败');
                }elseif(in_array(0,$reNotice)){
                pp($reNotice,'通知写入失败');
                }elseif(in_array(0,$reBouns)){
                pp($reBouns,'分红表写入失败');
                }
                 */
                else{
                    $this -> error('分红发放失败','__URL__/userGiven');
                }
            }
        }
	}