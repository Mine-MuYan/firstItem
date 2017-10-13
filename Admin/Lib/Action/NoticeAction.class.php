<?php 

	class NoticeAction extends Action{
	    
	    //补贴列表
	    public function bouns(){
            $userNotice = getUserBouns('1');
            $this -> assign('userNotice',$userNotice);
	        $this -> display();
        }
        
        //分红列表
        public function fenHong(){
            $userNotice = getUserBouns('2');
            $this -> assign('userNotice',$userNotice);
            $this -> display();
        }
        
        public function flow(){
        
        }
	    
    }