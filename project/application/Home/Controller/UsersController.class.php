<?php
namespace Home\Controller;
use Common\Controller\HomebaseController;
use Common\Tool\Device;
use Common\Tool\Sms;

class UsersController extends HomebaseController {


    //
    /**
     * 用户水机购买前-信息录入
     */
    public function userbuy()
    {
        try {

            $data = I('post.');
            if (empty(session('waterOrder.code'))) {
                E('邀请码不能为空', 201);
            } else {
               $users_code =  M('users')->field('code,to_code')->where(['code'=>session('waterOrder.code')])->find();
               if( empty($users_code['code'])) {
                   E('无法找到该邀请码', 201);
               }
            }
            if (empty($data['uname'])) {
                E('姓名不能为空', 201);
            } else {
                $reg['name'] = $data['uname'];
            }

            if (empty($data['uphone'])) {
                E('手机号不能为空', 201);
            } else {
                $reg['user'] = $data['uphone'];
            }

            if (isset($data['has'])) {
                if (!empty($data['upwd'])) {
                    $reg['password'] = md5(md5($data['upwd']));
                }
            }else{
                if (empty($data['upwd'])) {
                    E('密码不能为空', 201);
                } else {
                    $reg['password'] = md5(md5($data['upwd']));
                }
            }


            if (empty($data['address'])) {
                E('地址不能为空', 201);
            } else {
                session('waterOrder.address',$data['address']);
            }


            $m =  M('users');
            $info = $m->where('user='.$reg['user'])->find();

            if (empty($info)) {
                $data['created_at']=time();
                $reg['code'] = $this->create_guid();
                //老父亲
                $reg['to_code'] = $users_code['code'];
                //老爷爷
                $reg['parent_code'] = $users_code['to_code'];

                $res = $m->add($reg);

                if($res)$uid = $res;
            } else {
                $reg['updated_at']=time();
                $res = $m->where('id='.$info['id'])->save($reg);
                $uid = $info['id'];
            }

            if($res){
                session('waterOrder.sid',$data['sid']);
                session('waterOrder.uid',$uid);
                session('waterOrder.name',$reg['name']);
                session('waterOrder.phone',$reg['user']);
            } else {
                //用户注册失败
                E('用户注册失败', 201);
            }
            E('注册成功', 200);

        } catch (\Exception $e) {
            $this->to_json($e);
        }
    }
    
    /**
     * 用户录入
     */
    public function reg()
    {
        try {
            $data = I('post.');

            if (empty($data['user'])) {
                E('账号不能为空', 201);
            }
            if (empty($data['password'])) {
                E('密码不能为空', 201);
            }
            if (empty($data['code'])) {
                E('验证码不能为空', 201);
            }
            $code = Sms::getInfo($data['user']);
            if ($code  != $data['code']) {
                E('验证码不正确', 201);
            }

            if ($data['password'] != $data['re_password']) {
                E('密码不一至', 201);
            }

            $data['password'] = md5(md5($data['password']));
            $m =  M('users');
            $info = $m->where('user='.$data['user'])->find();
            if (empty($info)) {
                $data['created_at']=time();
                $res = $m->add($data);
            } else {
                $res = $m->where('id='.$info['id'])->save($data);
            }

            if ($res) {
                E('添加成功', 200);
            } else{
                E('添加失败,请联系管理员!', 201);
            }
        } catch (\Exception $e) {
            $this->to_json($e);
        }
    }

    /**
     * 用户登录
     */
    public function login()
    {
        try {
            $data = I('post.');

            if (empty($data['user'])) {
                E('账号不能为空!', 201);
            }
            if (empty($data['password'])) {
                E('密码不能为空!', 201);
            }

            $m =  M('users');
            $info = $m->where('user='.$data['user'])->find();
            if (empty($info)) {
                E('账号不存在!', 201);
            }

            $data['password'] = md5(md5($data['password']));
            if ($data['password'] != $info['password']) {
                E('密码错误!', 201);
            } else {
                $_SESSION['homeuser'] = $info;

                $this->ajaxReturn(array(
                    'PHPSESSID'=>cookie('PHPSESSID'),
                    'status'=>200,
                    'msg'=>'登录成功',
                ),'JSON');

            }

        } catch (\Exception $e) {
            $this->to_json($e);
        }
    }

    /**
     * 短信发送
     */
    public function send()
    {
        try{
            $data = I('post.');
            if(empty($data['phone']) || empty($data['tocken'])){
                E('参数错误!',201);
            }
            $tocken = md5(substr($data['phone'],2,8));
            if ($data['tocken'] != $tocken) {
                E('参数错误!',202);
            }

            if(Sms::send($data['phone'])){

                E('发送成功!',200);
            }
            E('发送失败,请稍后重试!',201);

        } catch (\Exception $e) {
            $this->to_json($e);
        }
    }

    public function buyinfo()
    {
        if(session('waterOrder.has')==1){
            $homeuser = session('homeuser');
            session('waterOrder.uid',$homeuser['id']);
            session('waterOrder.name',$homeuser['name']);
            session('waterOrder.phone',$homeuser['user']);
        }
        $this->display();
    }

    /**
     * 用户中心
     */
    public function mine()
    {
        $did = session('homeuser.did');
        $device_code = Device::get_devices_sn($did);
        $info['device_code'] = $device_code;
        $this->assign('info',$info);
        $this->display();
    }
    //每个用户的邀请码
    function create_guid($namespace = '') {
        static $guid = '';
        $uid = uniqid("", true);
        $data = $namespace;
        $data .= $_SERVER['REQUEST_TIME'];
        $data .= $_SERVER['HTTP_USER_AGENT'];
        $data .= $_SERVER['LOCAL_ADDR'];
        $data .= $_SERVER['LOCAL_PORT'];
        $data .= $_SERVER['REMOTE_ADDR'];
        $data .= $_SERVER['REMOTE_PORT'];
        $hash = strtoupper(hash('ripemd128', $uid . $guid . md5($data)));
        $guid = substr($hash,  0,  8) .
            '-' .
            substr($hash,  8,  4) .
            '-' .
            substr($hash, 12,  4) .
            '-' .
            substr($hash, 16,  4) .
            '-' .
            substr($hash, 20, 12) ;


        return $guid;

    }
}


