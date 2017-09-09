<?php 
	
	
	/**
	* 类名：CartAction
	* 功能：显示购物车状态
	*/
	class CartAction extends Action
	{
		public function cart() 
		{
			R('Base/header');
			R('Base/footer');
            header("Content-type: text/html; charset=utf-8");
            $cartGoods = json_decode($_COOKIE['cart'], true);//购物车的商品
			$data = M("goods");
			$tdata = $data->limit(10,17)->select();
            foreach ($cartGoods as $item => $value) {
                $cardata = $data->where(array('id'=>$value['id']))->find();
                $cartGoods[$item]['jifen'] = $cardata['jifen'];
                $cartGoods[$item]['yidebi'] = $cardata['yidebi'];
            }

            $sumprice = 0;
            $sumyidebi= 0;
            $sumjifen = 0;
            foreach ($cartGoods as $item => $value){
                $arr=explode('￥',$value['price']);
                $sumprice = $sumprice + $value['qty']*$arr[1];
                $sumjifen = $sumjifen + $value['qty']*$value['jifen'];
                $sumyidebi= $sumyidebi+ $value['qty']*$value['yidebi'];

            }
            //获取总计
            $sumData = array(
                array(
                    'price' => $sumprice,
                    'jifen' => $sumjifen,
                    'yidebi'=> $sumyidebi,
                )

            );
            //thumbnail
			$this->assign("tdata",$tdata);
            $this->assign("cartGoods",$cartGoods);
            $this->assign("sumData",$sumData);
			$this->display();
		}
	}