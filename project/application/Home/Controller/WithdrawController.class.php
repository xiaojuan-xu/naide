<?php
namespace Home\Controller;
use Common\Controller\HomebaseController; 

class WithdrawController extends HomebaseController 
{
	/**
	 * [index 加载体现页面]
	 * @return [type] [description]
	 */
	public function index()
	{
		if (IS_POST) {
			//接收提现金额
			$data['money'];
			
			//开户名
			$data['name'];

			//银行名
			$data['choose'];
			
			//开户支行
			$data['acc_open'];
			
			//银行卡号
			$data['bank'];

			//用户id
			$data['uid'] = $_SESSION['homeuser']['id'];

			//创建时间
			$data['create_time'] = time();


		} else {
			//查询登录用户的的余额
			$balance = M('users')->field('balance')->where('id='.$_SESSION['homeuser']['id'])->find()['balance'];

			//分配可提现余额
			$this->assign('enmoney', $balance);
			$this->display();

			//查询用户是否使用过的银行账户
			$banks = M('bank')->where('uid='.$_SESSION['homeuser']['id'])->select();

			if (empty($banks)) {
				$this->ajaxReturn(array('code'=>'400','msg'=>'没有账户'));
			} else {
				$this->ajaxReturn(array('code'=>'200','msg'=>$banks));
				
			}

			
		}
		
	}

}


