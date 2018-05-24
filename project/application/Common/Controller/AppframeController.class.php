<?php
namespace Common\Controller;
use Think\Controller;
use Common\Tool\WeiXin;
class AppframeController extends Controller {

    public function __call($a,$b)
    {
        $args=['_initialize','__construct'];
        if(!in_array($a,$args)){
            $this->display($a);
        }
    }
    function _initialize()
    {
        @parent::_initialize();
        $_GET['p'] = I('p', 1);

        //微信信息
        $is_weixin = is_weixin();
        $is_weixin_s = $is_weixin?'true':'false';
        $this->assign('is_weixin', $is_weixin_s);
        if($is_weixin){

            $signPackage = WeiXin::getSignPackage();
            $this->assign('wxinfo',$signPackage);

//            if(DEBUG){
//                $_SESSION['open_id'] = 'ocea2uHn9T1OEUQTuDVnfdtJT7wE';
//            }
            if(empty($_SESSION['open_id'])){
                $_SESSION['open_id'] = WeiXin::GetOpenid();
            }
            $this->assign('open_id',$_SESSION['open_id']);

        }
    }

    public function to_json($e)
    {
        $err = [
            'status' => $e->getCode(),
            'msg' =>   $e->getMessage(),
        ];
        $this->ajaxReturn($err,'JSON');
    }

    /**
     * Ajax方式返回数据到客户端
     * @access protected
     * @param mixed $data 要返回的数据
     * @param String $type AJAX返回数据格式
     * @return void
     */
    protected function ajaxReturn($data, $type = '',$json_option=0) {

        if(isset($data['url'])){
            $data['referer'] = $data['url'] ? $data['url'] : "";
        }
        $data['state']   = (!empty($data['status']) and $data['status']== 200) ? "success" : "fail";
        if(empty($type)) $type  =   C('DEFAULT_AJAX_RETURN');
        switch (strtoupper($type)){
        	case 'JSON' :
        		// 返回JSON数据格式到客户端 包含状态信息
        		header('Content-Type:application/json; charset=utf-8');
        		exit(json_encode($data,$json_option));
        	case 'XML'  :
        		// 返回xml格式数据
        		header('Content-Type:text/xml; charset=utf-8');
        		exit(xml_encode($data));
        	case 'JSONP':
        		// 返回JSON数据格式到客户端 包含状态信息
        		header('Content-Type:application/json; charset=utf-8');
        		$handler  =   isset($_GET[C('VAR_JSONP_HANDLER')]) ? $_GET[C('VAR_JSONP_HANDLER')] : C('DEFAULT_JSONP_HANDLER');
        		exit($handler.'('.json_encode($data,$json_option).');');
        	case 'EVAL' :
        		// 返回可执行的js脚本
        		header('Content-Type:text/html; charset=utf-8');
        		exit($data);
        	case 'AJAX_UPLOAD':
        		// 返回JSON数据格式到客户端 包含状态信息
        		header('Content-Type:text/html; charset=utf-8');
        		exit(json_encode($data,$json_option));
        	default :
        		// 用于扩展其他返回格式数据
        		Hook::listen('ajax_return',$data);
        }
        
    }

    //空操作
    public function _empty() {
        $this->error('该页面不存在！');
    }
    
    /**
     * 检查操作频率
     * @param int $duration 距离最后一次操作的时长
     */
    protected function check_last_action($duration){
    	
    	$action=MODULE_NAME."-".CONTROLLER_NAME."-".ACTION_NAME;
    	$time=time();
    	
    	$session_last_action=session('last_action');
    	if(!empty($session_last_action['action']) && $action==$session_last_action['action']){
    		$mduration=$time-$session_last_action['time'];
    		if($duration>$mduration){
    			$this->error("您的操作太过频繁，请稍后再试~~~");
    		}else{
    			session('last_action.time',$time);
    		}
    	}else{
    		session('last_action.action',$action);
    		session('last_action.time',$time);
    	}
    }
}