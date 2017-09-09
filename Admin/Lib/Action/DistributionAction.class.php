<?php


/**
 *  类名:ClassAction 后台规格规格信息控制类
 *  功能:实现后台对规格规格数据的显示,增删改查等操作
 */

class DistributionAction extends Action{

		/**
		* 函数名:set
		* 功能:分销设置
		*  @param no
		*  @return void
		*/
		function set(){

		    if(IS_POST)
            {
                R('Level/distset'); // 验证权限
                $dbUserSet = M('user_set');
                $refUserSet = array(
                    'reg_jifen'      =>  $_POST['disSet'],
                    'rat_yidebi'     =>  $_POST['ydbi'],
                    'first_jifen'    =>  $_POST['yijijifen'],
                    'second_jifen'   =>  $_POST['erjijifen'],
                    'first_yidebi'   =>  $_POST['yijiydbi'],
                    'second_yidebi'  =>  $_POST['erjiydbi'],
                );

                    if($dbUserSet->where('id=1')->save($refUserSet)){
                        $this->success("修改成功!",__URL__."/set");exit();
                    }else{
                        $this->error("修改失败!",__URL__."/set");exit();
                    }
            }
		    $this->display();
		}


    /**
     * 函数名：relation
     * 功能：分销关系表
     * @param no
     *  @return void
     */

		function relation(){ //需要获得参数为某个用户id然后查询他所有的下级
            $relationData = array();
            $channels = M('user_relation')->where(1)->select();

            function getChild(&$html,$parid,$channels,$dep)
            {
                for ($i = 0; $i < count($channels); $i++) {
                    if ($channels[$i]['pid'] == $parid) {
                        $user   = M('user');
                        $dbuser = $user->where('id='.$channels[$i]['uid'])->find();
                        $channels[$i]['name'] = $dbuser['username'];
                        $html[] = array('uid' => $channels[$i]['uid'], 'name' => $channels[$i]['name'], 'dep' => $dep);
                        getChild($html,$channels[$i]['uid'],$channels,$dep+1); //递归
                    }
                }
            }
            if($_POST){//如果有输入值
                getChild($relationData, $_POST['pid'], $channels, 0);
            }
            else{//如果没有输入值
                getChild($relationData, 0, $channels, 0);
            }

            //var_dump($relationData);die();
            $this->assign('relationData',$relationData);
            $this->display();
        }


    /**
     * 函数名：refree
     * 功能：推荐关系表
     * @param no
     *  @return void
     */

    function refree(){ //需要获得参数为某个用户id然后查询他所有的下级
        $relationData = array();
        $channels = M('user_relation')->where(1)->select();


        function getChild(&$html,$parid,$channels,$dep)
        {
            for ($i = 0; $i < count($channels); $i++) {
                if ($channels[$i]['pid'] == $parid) {
                    $user   = M('user');
                    $dbuser = $user->where('id='.$channels[$i]['uid'])->find();
                    $channels[$i]['name'] = $dbuser['username'];
                    $html[] = array('uid' => $channels[$i]['uid'], 'name' => $channels[$i]['name'], 'dep' => $dep);
                    getChild($html,$channels[$i]['uid'],$channels,$dep+1); //递归
                }
            }
        }
        if($_POST){//如果有输入值
            $user   = M('user');
            $dbuser = $user->where(array('username'=>$_POST['username']))->find();
            getChild($relationData, $dbuser['id'], $channels, 0);
        }
//        else{//如果没有输入值
//            getChild($relationData, 0, $channels, 0);
//        }

        //var_dump($relationData);die();
        $searchName[0]['username'] = $_POST['username'];
        $this->assign('searchName',$searchName);
        $this->assign('relationData',$relationData);
        $this->display();
    }
}