<?php
namespace Home\Controller;
use Common\Controller\HomebaseController;

class WechatController extends HomebaseController
{

    /**
     * 统一下单订单支付并返回数据 JsApi
     * @return string json格式的数据，可以直接用于js支付接口的调用
     * @param  [string] $openId    用户openid
     * @param  [type] $money     订单金额(原金额 未乘100的)
     * @param  [type] $order_id  订单id
     * @param  [type] $content    订单详情
     * @param  [type] $notify_url 回调地址
     */
    public static function uniformOrder($openId,$money,$order_id,$content,$notify_url)
    {
        $content = substr($content,0,80);
        $money = $money * 100;                          // 将金额强转换整数

        $money = 1;                                     // 冲值测试额1分钱 上线取消此行

        vendor('WxPay.jsapi.WxPay#JsApiPay');
        $tools = new \JsApiPay();

        vendor('WxPay.jsapi.WxPay#JsApiPay');
        $input = new \WxPayUnifiedOrder();
        //$input->SetDetail($uid);

        $input->SetBody($content);                      // 产品内容

        $input->SetAttach($order_id);                   // 唯一订单ID

        $input->SetOut_trade_no(gerOrderId());          // 设置商户系统内部的订单号,32个字符内、可包含字母, 其他说明见商户订单号
        $input->SetTotal_fee($money);                   // 产品金额单位为分

        //$input->SetTime_start(date("YmdHis"));        // 设置订单生成时间
        //$input->SetTime_expire(date("YmdHis", time() + 300));// 设置订单失效时间
        //$input->SetGoods_tag($uid);

        $input->SetNotify_url($notify_url);             // 微信充值回调地址
        $input->SetTrade_type("JSAPI");           // 支付方式 JS-SDK 类型是：JSAPI
        // 用户在公众号的唯一标识
        $input->SetOpenid($openId);

        $order = \WxPayApi::unifiedOrder($input);       // 统一下单

        // 返回支付需要的对象JSON格式数据
        $jsApiParameters = $tools->GetJsApiParameters($order);

        echo $jsApiParameters;
        exit;
    }


    // 请先关注微信公众号
    public function follow()
    {
        // 显示模板
        $this->display('follow');
        // echo '请先关注微信公众号！';
    }

    /**
     * 生成自定义菜单
     * @return bool true or false
     */
    public function create_menu()
    {
        // $appid = C('APPID');
        // $appsecret = C('APPSECRET');
        // $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$appsecret";

        // $output = $this->https_request($url);
        // $jsoninfo = json_decode($output, true);

        // $access_token = $jsoninfo["access_token"];
        // 
        // 实例化微信JSSDK类对象
        $wxJSSDK = new \Org\Util\WeixinJssdk;
        // 调用获取公众号的全局唯一接口调用凭据
        $access_token = $wxJSSDK->getAccessToken();
    

        $jsonmenu = '{
            "button":[{
                "name":"我的水机",
                "type":"view",
                
                "url":"http://test.dianqiukj.com/index.php"
            },
            {
                "name":"商城",
                "type":"view",
                
                "url":"http://test.dianqiukj.com/index.php/Home/Shop/index"
            },
            {
			   "name":"用户入口",
			           "sub_button":[
			            {
			               "type":"view",
			                "name":"分销中心",
			                "url":"http://test.dianqiukj.com/index.php/Home/Vendors/index"
			            },
			            {
			               "type":"view",
			                "name":"会员中心",
			                "url":"http://test.dianqiukj.com/index.php/Home/VipCenter/index"
			    		}]
            }]
        }';

        $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$access_token;
        $result = $this->https_request($url, $jsonmenu);
        var_dump($result);

    }

    /**
     * CURL使用
     * @param  string $url  URL地址
     * @param  Array $data 传递数据
     * @return string  $output     传递数据时返回的结果
     */
    public function https_request($url,$data = null){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }

}