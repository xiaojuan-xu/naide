<?php
namespace Admin\Controller;
use Think\Controller;
use Admin\Controller\CommonController;
class IndexController extends CommonController {
    public function index(){
    	if (IS_AJAX) {
    		// 充值数额统计数量 （本月列表显示）
	    	$flows = D('Flow')->getTotalByEveryDay();

			$devices = D('Devices')->getTotalByEveryDay();
	    	// 滤芯订单数量（已发货及未发货数量->以发货及未发货列表）
            if(empty(session('adminuser.is_admin'))){
                $map['d.vid'] = $_SESSION['adminuser']['id'];
                $order_filters = D('Order')
                    ->where($map)
                    ->alias('o')
                    ->join('__DEVICES__ d on o.did = d.id','LEFT')
                    ->field('distinct(order_id)')
                    ->select();
                $order_filter['total'] = count($order_filters);

                // 保修数量统计->保修列表 work
                $repairs['total'] = D('Work')
                    ->where($map)
                    ->alias('r')
                    ->join('__DEVICES__ d on r.did = d.id','LEFT')
                    ->count();

                // 建议数量统计->建议列表
                // $feeds['total'] = D('Feeds')
                //     ->where($map)
                //     ->alias('f')
                //     ->join('__DEVICES__ d on f.did = d.did','LEFT')
                //     ->count();
                $feeds['total'] = M('vendors')->where("examine=1 and id=".session('adminuser.id'))->count();
                
                
            } else {
                $order_filters = D('Order')
                    ->field('distinct(order_id)')
                    ->select();

                $order_filter['total'] = count($order_filters);

                // 保修数量统计->保修列表
                $repairs['total'] = D('Work')->count();

                // // 经销商数据审核统计->建议列表
                // $feeds['total'] = D('Feeds')->count();
                $feeds['total'] = M('vendors')->where(['examine=1'])->count();
                
            }
	    	$data = [
				'flows' => $flows,
				'devices'=> $devices,
	    		'order_filters' => $order_filter,
	    		'repairs' => $repairs,
	    		'feeds' => $feeds
	    	];
	    	$this->ajaxReturn($data);
    	}

        $this->assign('data',$data);
        $this->display('index');

    }

    public function welcome()
    {
        $this->show('<h1>欢迎回来！</h1>');
    }
}

