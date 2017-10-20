<?php 

	class NoticeAction extends Action{
	    
	    //补贴列表
	    public function bouns(){
            $level = A('Level');
            $level -> viewUserNotice('bounsNotice');
            $userNotice = getUserBouns('1');
            $this -> assign('userNotice',$userNotice);
	        $this -> display();
        }
        
        //分红列表
        public function fenHong(){
            $level = A('Level');
            $level -> viewUserNotice('fenHongNotice');
            $userNotice = getUserBouns('2');
            $this -> assign('userNotice',$userNotice);
            $this -> display();
        }
        
        //签到列表
        public function lottery(){
            $level = A('Level');
            $level -> viewUserNotice('lotteryNotice');
            $userNotice = getUserBouns('3');
            $this -> assign('userNotice',$userNotice);
            $this -> display();
        }
	    
    }