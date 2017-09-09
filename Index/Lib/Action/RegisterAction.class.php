<?php
	
	/**
	* 类名：RegisterAction
	* 功能：实现注册以及注册需要的验证跳转操作
	*/
class RegisterAction extends Action{
		public function register(){
			R('Base/header');
			R('Base/footer');
			$this->display();
		}
	
		/**
		 * 函数名：regist
		 * 功能：用户注册方法
		 * @return [type] [description]
		 */
		function regist(){
			
			$user           = M('user');            //用户注册表
			$info           = M('userinfo');        //用户详细信息表
			$relation       = M('user_relation');   //用户关系表

			// $uname = $user->where(array('username'=>$_POST['username']))->field('username')->find(); // 查询用户名是否被注册
			// $uemail = $user->where(array('email'=>$_POST['email']))->field('email')->find(); // 查询邮箱是否被注册
			// if(empty($uname) && empty($uemail)){

           if($_POST['refreeName']){//如果推荐人有值
               if($_POST['refreeName'] == $_POST['username']){
                   $this->error('推荐人不能是自己!','__APP__/Register/register');
               }
               else{
                    $searchRefree = $user -> where(array('username'=>$_POST['refreeName']))->find();
                    if($searchRefree){
                        //注册用户表
                        $_POST['userpwd'] = md5($_POST['userpwd']);
                        $_POST['ustatus'] = 2;
                        $_POST['regtime'] = time();
                        $_POST['regip'] = $_SERVER['REMOTE_ADDR'];            // 获取ip地址
                        $id = $user->add($_POST);
                        $data['uid'] = $id;
                        //构造关系数据
                        $relationData = array(
                           'uid' =>  $id  ,
                           'pid' =>  $searchRefree['id'] ,
                       );
                        //注册关系表
                        $relation->add($relationData);
                    }
                    else{
                      $this->error('该推荐人不存在!','__APP__/Register/register');
                    }
               }
           }

           else {//如果推荐人没有值,则直接写入注册表
                    $_POST['userpwd'] = md5($_POST['userpwd']);
                    $_POST['ustatus'] = 2;
                    $_POST['regtime'] = time();
                    $_POST['regip'] = $_SERVER['REMOTE_ADDR'];            // 获取ip地址

                    $id = $user->add($_POST);                             // 注册user表
                    $data['uid'] = $id;                                   // 获取用户id

           }


				if($id && $info->add($data)){ 						// 注册userinfo表
                    $this->assign('waitSecond','3');
                    $this->success('注册成功,请等待审核','__APP__/Index/index');
//					$email = M('mail_template');
//					$con   = $email->where(array('id'=>1))->find();// 查询邮件模板
//
//					$username = $_POST['username'];                // 发送邮件时的显示的用户名
//					$jm = md5($_POST['email']);                    // 邮箱加密验证
//					$u = $id;                                      // 用户id
//
//					//$str = $con['content'];
//
//					$content= '
//			    <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%">
//				<tbody>
//				<tr>
//					<td style="padding:20px 11px 40px 11px;background-color:#ffffff;">
//					<table style="margin:0 auto;background-color:#ffffff" align="center" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="700">
//				  	<tbody><tr>
//				    <td valign="top" width="700">
//				      <table style="margin:0 auto;-webkit-border-top-left-radius:10px;-webkit-border-top-right-radius:10px;background-color:#ffffff" align="center" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="648">
//					    <tbody><tr><td><img src="http://r.mzstatic.com//email/apple_notification/top.gif" alt="" style="display:block;margin-top:0px;margin-right:0px;margin-bottom:0px;margin-left:0px" border="0" height="122" width="648"></td></tr>
//				      </tbody></table>
//				      <table style="margin:0 auto;-webkit-border-bottom-left-radius:10px;-webkit-border-bottom-right-radius:10px;background-color:#f1f1f1" align="center" border="0" cellpadding="0" cellspacing="0" width="630">
//					    <tbody><tr>
//						  <td>
//							<table style="margin:0 auto;background-color:#f1f1f1" align="center" border="0" cellpadding="0" cellspacing="0" width="490">
//								<tbody><tr>
//									<td style="padding:0 0 22px 0" align="left" width="490">
//										<div style="font-family: Lucida Grande, Lucida Sans, Lucida Sans Unicode, Arial, Helvetica, Verdana, sans-serif; color:#333333; font-size:12px;line-height: 1.25em;">
//										<span style="font-weight:bold">'.$username.'，您好：</span><br>
//										感谢你注册韩都衣舍，请点击以下链接完成激活账号（邮箱激活链接48小时以内有效）： <br>
//										<a _act="check_domail" target="_blank" href="http://localhost'.__APP__.'/Register/Act/u/'.$u.'/e/'.$jm.'/" class="" style="color:#0088cc">激活帐户</a><br>
//										<a _act="check_domail" target="_blank" href="http://localhost'.__APP__.'/Register/Act/u/'.$u.'/e/'.$jm.'/" class="" style="color:#0088cc">http://localhost'.__APP__.'/Register/Act/u/'.$u.'/key/'.$jm.'/</a> <br>
//										如果链接不能点击，请复制并粘贴到浏览器的地址栏，然后按回车键<br>
//										如果不是您本人操作，请忽略此邮件。<br>
//										<br>谨上<br>韩都衣舍</div>
//									</td>
//								</tr>
//							</tbody></table>
//						  </td>
//					    </tr>
//					    <tr><td style="padding-top:101px"><img src="http://r.mzstatic.com//email/apple_notification/btm.gif" alt="" style="display:block;margin-top:0px;margin-right:0px;margin-bottom:0px;margin-left:0px" border="0" height="21" width="630"></td></tr>
//				      </tbody></table>
//				      <table id="aapl-footer" style="margin:0 auto;" align="center" border="0" cellpadding="0" cellspacing="0" width="490">
//					    <tbody><tr><td style="padding:10px 20px 10px 0;">
//						  <div style="font-family: Geneva, Verdana, Arial, Helvetica, sans-serif; font-size:9px; line-height: 1.34em; color:#999999;">
//						 	 商标和版权 © 2014 yixiangit.Com. 意象网络工作室.
//						  </div>
//						  <div style="font-family: Geneva, Verdana, Arial, Helvetica, sans-serif; font-size:9px; line-height: 1.34em; color:#999999;">
//						  	<a _act="check_domail" target="_blank" style="color:#999999;text-decoration:underline;" href="http://www.eletgo.com/think/">保留所有权利。</a>
//						  	/ <a _act="check_domail" target="_blank" style="font-family: Geneva, Verdana, Arial, Helvetica, sans-serif; font-size:9px;line-height: 1.34em;color:#999999;text-decoration:underline;" href="http://http://www.eletgo.com/think/">及时通知</a>
//						  	/ <a _act="check_domail" target="_blank" style="font-family: Geneva, Verdana, Arial, Helvetica, sans-serif; font-size:9px;line-height: 1.34em;color:#999999;text-decoration:underline;" href="http://www.eletgo.com/think/">客户隐私政策</a> /
//						  	<a _act="check_domail" target="_blank" style="font-family: Geneva, Verdana, Arial, Helvetica, sans-serif; font-size:9px;line-height: 1.34em;color:#999999;text-decoration:underline;" href="http://www.eletgo.com/think/">我的ID</a>
//						  </div>
//					    </td></tr>
//				      </tbody></table>
//					</td>
//				  </tr>
//				</tbody></table>
//				</td>
//				</tr>
//				</tbody>
//				</table>
//				<img src="http://outsideapple.apple.com/img/APPLE_EMAIL_LINK/spacer4.gife">
//			';
//
//					eval("\$str = \"$str\";");                     // 执行邮件模板保证变量正确解析
//
//					import('ORG.Email');                           //导入email类
//					$mail = new Email();                           // 实例化email类
//					$data['mailto'] = $_POST['email'];             //收件人
//					$data['subject'] = $con['title'];              //邮件标题
//					$data['body'] = $content;                          //邮件正文内容
//
//					if($mail->send($data)){                        // 判断是否发送成功
//						$this->success('注册成功，请到邮箱激活','__APP__/Login/login');
//					}else{
//						$this->success('注册成功,但发送激活邮件失败','__APP__/Login/login');
//					}
				}else{
					$this->error('注册失败');
				}
			// }else{

			// 	$this->error('当前用户名与邮箱已被注册');
			// }
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

			$e = $user->where(array('id'=>$id,'ustatus'=>2))->field('email')->find(); // 查询用户邮箱地址

			if(md5($e['email']) == $email && !empty($e)){                             // 判断加密信息是否正确
				if($user->where(array('id'=>$_GET['u']))->save($data)){
					$this->success('激活成功','__APP__/Index/index');
				}else{
					$this->error('激活失败');
				}
			}else{
				$this->error('参数错误');
			}
		}


		/**
		 * 函数名：verify
		 * 功能：产生验证码
		 */
		function verify(){
   			import("ORG.Util.Image");
    		Image::buildImageVerify(4,1);
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
			if(!empty($username)){
				
				$condition['username'] = $username;
				$result = $user->where($condition)->find();

				// 如果返回的有数据说明此用户名已存在
				if(count($result)){
					$this->ajaxReturn("UsernameFalse");				
				}else{
					$this->ajaxReturn("UsernameTrue"); 					
				}
				unset($condition);

			}elseif(!empty($email)){

				// 邮箱验证
				$condition['email'] = $email;
				$result = $user->where($condition)->find();

				// 如果返回的有数据说明此邮箱已注册
				if(count($result)){
					$this->ajaxReturn("EmailFalse");				
				}else{
					$this->ajaxReturn("EmailTrue"); 					
				}
				unset($condition);

			}elseif(!empty($vcode)){

				// 注册码验证
				if($_SESSION['verify'] == md5($vcode)) {
   					$this->ajaxReturn("VcodeTrue");
				}else{
					$this->ajaxReturn("VcodeFalse");
				}
			}
		}
	}