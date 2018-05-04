<?php
namespace Home\Controller;
use Common\Controller\HomebaseController;
use Common\Tool\Sms;

class UsersController extends HomebaseController {

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
            if ($data['password'] == $data['re_password']) {
                E('密码不一至', 201);
            }

            $data['password'] = md5(md5($data['password']));
            $m =  M('users');
            $info = $m->where('user='.$data['user'])->find();
            if (empty($info)) {
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
     * 用户录入
     */
    public function login()
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

            $m =  M('users');
            $info = $m->where('user='.$data['user'])->find();
            if (empty($info)) {
                $res = $m->add($data);
            } else {
                $res = $m->where('id='.$info['id'])->save($data);
            }
            if ($data['password'] == $data['re_password']) {
                E('密码不一至', 201);
            }

            $data['password'] = md5(md5($data['password']));



            if ($res) {
                E('添加成功', 200);
            } else{
                E('添加失败,请联系管理员!', 201);
            }
        } catch (\Exception $e) {
            $this->to_json($e);
        }
    }

    public function send()
    {
        try{
            $data = I('post.');
            if(empty($data['phone']) || empty($data['tocken'])){
                $tocken = md5(substr($data['phone'],2,8));
                if ($data['tocken'] != $tocken) {
                    E('参数错误!',201);
                }
            }
            if(Sms::send($data['phone'])){
                E('发送成功!',200);
            }else{
                E('发送失败,请稍后重试!',201);
            }
        } catch (\Exception $e) {
            $this->to_json($e);
        }
    }


}


