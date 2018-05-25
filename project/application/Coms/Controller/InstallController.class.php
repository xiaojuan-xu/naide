<?php
namespace Coms\Controller;
use Common\Controller\ComsbaseController;

class InstallController extends ComsbaseController {

    //安装工人
    public function index() {
        $map['v_id'] = session('comsuser.id');
        $personnel_list =M('personnel')->field('id,name,phone,create_time')->where($map)->select();
        if ($personnel_list) {
            $this->assign('data', json_encode($personnel_list));
        }
        $this->display();
    }

    //安装工人搜索
    public function search() {

        try {
            $data = I('post.');
            if (!empty($data['search'])) {
                if (strlen($data['search'])==11) {
                    $map['phone'] = $data['search'];
                }else{
                    $map['name'] = array('like','%'.$data['search'].'%');
                }
            }

            $map['v_id'] = session('comsuser.id');
            $personnel_list =M('personnel')->field('id,name,phone,create_time')->where($map)->select();

            $this->ajaxReturn(array(
                'status'=>200,
                'list'=>$personnel_list,
                'msg'=>'创建成功',
            ),'JSON');
        } catch (\Exception $e) {
            $this->to_json($e);
        }
    }

    // 安装人员详情
    public function minstall_man_list() {

        try {
            $data = I('post.');
            if (empty($data['id'])) {
                E('数据不完整', 201);
            } else {
                $map['id'] = $data['id'];
            }
            $map['v_id'] = session('comsuser.id');
            $per = M('personnel')->where($map)->find();

            if(empty($per))E('未找到该用户',201);
            $this->ajaxReturn(array(
                'status'=>200,
                'data'=>$per,
                'msg'=>'获取成功',
            ),'JSON');
        } catch (\Exception $e) {
            $this->to_json($e);
        }
    }
    
    // 安装人员详情 编辑
    public function minstall_man_edit() {

        try {
            $data = I('post.');
            if (empty($data['id'])) {
                E('数据不完整', 201);
            } else {
                $map['id'] = $data['id'];
            }
            $map['v_id'] = session('comsuser.id');

            $savedata['name'] = $data['name'];
            $savedata['phone'] = $data['phone'];
            if(!empty($data['password'])){
                $savedata['password'] = md5($data['password']);
            }

            $per = M('personnel')->where($map)->save($savedata);

            if(empty($per))E('修改失败',201);
            E('修改成功',200);
        } catch (\Exception $e) {
            $this->to_json($e);
        }
    }

}


