<?php
use Xy\Application\Models\Defined\AdminErrorDef;
use Xy\Application\Models\Defined\AdminConstDef;
defined('BASEPATH') or exit('No direct script access allowed');
include_once 'Base.php';

class Users extends Base
{
    /**
     * 初始化
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 首页/列表页
     */
    public function index()
    {
        $page   =   $this->input->get_post('pageNum', true);
        $page   =   $page ? (int)$page : 1;
        $page_list  =  $this->input->get_post('numPerPage', true);
        $page_list  =   $page_list ? (int)$page_list : self::DEFAULT_PAGE_LIST;
        $user_name  = $this->input->get_post('user_name', true);
        $user_name  = $user_name ? $user_name : null;

        $where = array();

        if (!empty($user_name)) {
            $where['like']['user_name'] = $user_name;
        }

        $data = $this->AdminUsers->getPage($page, $page_list, $where, 'id asc');

        $data['user_name']      = $user_name;

        $this->display($data);
    }

    /**
     * 添加
     */
    public function add()
    {
        if (is_ajax('post')) {
            $user_name  = $this->input->post('user_name', true);
            $password   = $this->input->post('password', true);
            $repassword     = $this->input->post('repassword', true);
            $status     = $this->input->post('status', true);
            $email      = $this->input->post('email', true);
            //$tel        = $this->input->post('tel', true);
            $tel = '';

            $info = array(
                'user_name'     => $user_name,
                'status'        => $status,
                'email'         => $email,
//                'tel'           => $tel,
                'last_ip'       => ip2long($this->input->ip_address()),
                'last_time'     => time(),
                'add_time'      => time()
            );

            if ($password == $repassword) {
                $info['password'] =$this->pwdMd5($password);
            } else {
                $this->dwzAjaxReturn(self::AJ_RET_FAIL, '密码与确认密码不一致');
            }


            if ($this->checkUserInfo($user_name, $tel, $email) == true) {
                $this->dwzAjaxReturn(self::AJ_RET_FAIL, '账号/电话/邮箱 有重复信息');
            }

            $ret = $this->AdminUsers->add($info);

            if ($ret) {
                //设置管理员的所属角色
                $this->setRoles($ret);

                $this->dwzAjaxReturn(self::AJ_RET_SUCC, '添加成功', $this->_data['controller']);
            } else {
                $this->ajaxJsonReturn(self::AJ_RET_FAIL, '添加失败');
            }
        } else {
            $info = array(
                'id'            => '',
                'user_name'     => '',
                'password'      => '',
                'status'        => '',
                'email'         => '',
                'tel'           => ''
            );

            $data = array(
                'info' => $info,
                'role_ids' => null,
                'role_names' => null
            );

            $this->display($data, 'info');
        }
    }

    /**
     * 修改
     */
    public function edit()
    {
        if (is_ajax('post')) {
            $id     = $this->input->post('id', true);

            if (in_array($id, $this->super_admin_ids) && !in_array($this->admin_user_info['id'], $this->super_admin_ids)) {
                $this->dwzAjaxReturn(self::AJ_RET_FAIL, '只有超级管理员可以操作超级管理员信息');
            }

            $newpassword    = $this->input->post('newpassword', true);
            $repassword     = $this->input->post('repassword', true);
            $status         = $this->input->post('status', true);
            $email          = $this->input->post('email', true);
            $tel            = $this->input->post('tel', true);

            $info = array(
                'last_ip'       => ip2long($this->input->ip_address()),
                'last_time'     => time()
            );

            if ($newpassword == $repassword && !empty($newpassword)) {
                $info['password'] =$this->pwdMd5($newpassword);
            }

            if ($status) {
                $info['status'] = $status;
            }

            if ($email) {
                $info['email'] = $email;
            }

            if($tel && $this->_data['is_super_admin']){
                $info['tel'] = $tel;
            }

            if ($this->checkUserInfo(null, $tel, $email, $id) == true) {
                $this->dwzAjaxReturn(self::AJ_RET_FAIL, '电话/邮箱 有重复信息');
            }

            $ret = $this->AdminUsers->edit($info, array('id' => $id));

            if ($ret) {
                //设置管理员的所属角色
                $this->setRoles($id);

                $this->dwzAjaxReturn(self::AJ_RET_SUCC, '修改成功', $this->_data['controller']);
            } else {
                //设置管理员的所属角色
                $role_ret = $this->setRoles($id);

                if ($role_ret) {
                    $this->dwzAjaxReturn(self::AJ_RET_SUCC, '修改成功', $this->_data['controller']);
                } else {
                    $this->dwzAjaxReturn(self::AJ_RET_FAIL, '修改失败');
                }
            }
        } else {
            $id = $this->input->get('id', true);

            $where = array('id' => $id);
            $info = $this->AdminUsers->getOne($where);

            $roles = $this->AdminUserHasRoles->getRolesByUserId($id);

            $_role_ids = $_role_names = array();

            foreach ((array)$roles as $v) {
                $_role_ids[] = $v['id'];
                $_role_names[] = $v['title'];
            }


            $data = array(
                'info' => $info,
                'role_ids'      =>  implode(',', $_role_ids),
                'role_names'    =>  implode(',', $_role_names)
            );

            $this->display($data, 'info');
        }
    }



    //后台管理：用户列表页
    public function usersList(){

        //todo
        // search condition  :  name , role / group
        //获取用户列表数据
        $page           = (int)$this->input->request('pageNum', true);
        $page           = $page ? $page : 1;
        $page_list      = (int)$this->input->request('numPerPage', true);
        $page_list      = $page_list ? $page_list : self::DEFAULT_PAGE_LIST;

        $find = (int)$this->input->get('find');
        $order_by = 'id desc';
        $data = $this->AdminUsers->getUserList($page, $page_list, array(), $order_by);
        $data['find'] = $find;

        //获取用户其他信息：角色
        $roles = $this->AdminRoles->getRoles();
        $data['roles'] = $roles;
        $this->display($data);
    }

    //后台管理：添加用户 和 编辑用户
    public  function userEdit(){

        // method get :to add
        $id = $this->input->get('id');
        if (is_ajax('post')) {
            $postData = $this->input->post(null, true);
            $newData = array();
            if(!empty($postData)) {
                // check form data username unique
                $this->load->helper(array('form', 'url'));
                $this->load->library('form_validation');
                $this->form_validation->set_rules('u_name', 'Username', 'trim|required',
                    array('required'=>AdminErrorDef::USERNAME_EMPTY)
                );

                if(isset($postData['role_id'])){
                    $this->form_validation->set_rules('role_id', 'role_id', 'callback_check_rid');
                }
                
                if(isset($postData['pwd'])){
                    $this->form_validation->set_rules('pwd', 'Password', 'trim|required|callback_check_pwd',
                        array('required' =>AdminErrorDef::PASSWORD_EMPTY)
                    );
                }
                if(isset($postData['repwd'])){
                    $this->form_validation->set_rules('repwd', 'Password Confirmation', 'trim|required|matches[pwd]',
                        array('required' =>AdminErrorDef::CONFIRMATION_PASSWORD_EMPTY,
                            'matches' => AdminErrorDef::PASSWORD_NO_SAME));
                }
                $postData['email'] = trim($postData['email']);
                if(!empty($postData['email'])){
                    $this->form_validation->set_rules('email', 'Email', 'trim|valid_email|callback_check_email',
                        array('valid_email'=>AdminErrorDef::EMAIL_FORMAT_ERROR));
                }
                if ($this->form_validation->run() == FALSE) {
                    $msg = validation_errors();
                    $this->dwzAjaxReturn(self::AJ_RET_FAIL,$msg);
                }
                if(!empty($postData['email'])) {
                    $newData['email'] = $postData['email'];
                }
                $newData['name'] = $postData['u_name'];
                $newData['tel'] = $postData['tel'];
                $newData['updateDate'] = time();
                if(isset($postData['pwd'])) {
                    $pwd = $this->encryptPwd($postData['pwd']);
                    $newData['password'] = $pwd;
                }
                // 检查角色是否是超级管理员
                // id 待编辑用户id
                if($id>0){
                    $ret = $this->AdminUsers->edit($newData, array('id'=>$id));  // 更新记录
                }else{
                    $newData['role_id'] = $postData['role_id'];
                    $newData['createDate'] = time();
                    $ret = $this->AdminUsers->checkAdd($newData,array('name'=>$newData['name']));                    //新增记录
                }
            }

            if ($ret) {
                $this->dwzAjaxReturn(self::AJ_RET_SUCC, $id>0 ? AdminErrorDef::MODIFY_SUCCESS : AdminErrorDef::ADD_SUCCESS, dwz_rel($this->_data['controller'], 'usersList'));
            } else {
                $this->dwzAjaxReturn(self::AJ_RET_FAIL, $id>0 ? AdminErrorDef::MODIFY_FAIL : AdminErrorDef::ADD_FAIL,dwz_rel($this->_data['controller'], 'usersList'));
            }
        }else {
            $userInfo = array();
            if($id > 0) {
                $userInfo = $this->AdminUsers->getOne(array('id' => $id));
            }
            //获取角色信息
            $roles = $this->AdminRoles->getAll(array());
            $data['userInfo'] = $userInfo;
            $data['roles'] = $roles;
            $this->display($data);
        }

    }

    // 后台管理： 设置用户为role leader
    function  setRoleLeader(){

         $uid = $this->input->get('id',true);
         $is = $this->input->get('is',true);
         $url = $this->uri->ruri_string();
         if(!is_numeric($uid)){
             $this->dwzAjaxReturn(self::AJ_RET_FAIL,AdminErrorDef::USER_ID_ERROR ,dwz_rel($this->_data['controller'], 'usersList'),null,'forward',$url);
         }
         $leader = (int)$is?0:1;
         $ret = $this->AdminUsers->edit(array('is_role_leader'=>$leader), array('id'=>$uid));
         if ($ret) {
            $this->dwzAjaxReturn(self::AJ_RET_SUCC, AdminErrorDef::SET_ROLE_LEADER_OK , dwz_rel($this->_data['controller'], 'usersList'),null,'forward',$url);
         } else {
            $this->dwzAjaxReturn(self::AJ_RET_FAIL, AdminErrorDef::SET_ROLE_LEADER_FAIL ,dwz_rel($this->_data['controller'], 'usersList'),null,'forward',$url);
         }
    }

    //后台管理： 修改用户的角色 （高权限操作）
    function  modifyUserRole(){

    }
    // 后台管理： 修改指定用户密码
    function  modifyPwd(){

        $id = $this->input->get('id');

        if (is_ajax('post')) {
            $postData = $this->input->post(null, true);
            $newData = array();
            if(!empty($postData)) {

                $ret = false;
                if($id>0){

                    // check form data username unique
                    $this->load->helper(array('form', 'url'));
                    $this->load->library('form_validation');

                    if(isset($postData['pwd'])){
                        $this->form_validation->set_rules('pwd', 'Password', 'trim|required|callback_check_pwd',
                            array('required' =>AdminErrorDef::PASSWORD_EMPTY)
                        );
                    }
                    if(isset($postData['repwd'])){
                        $this->form_validation->set_rules('repwd', 'Password Confirmation', 'trim|required|matches[pwd]',
                            array('required' =>AdminErrorDef::CONFIRMATION_PASSWORD_EMPTY,
                                'matches' => AdminErrorDef::PASSWORD_NO_SAME));
                    }
                    if ($this->form_validation->run() == FALSE) {
                        $msg = validation_errors();
                        $this->dwzAjaxReturn(self::AJ_RET_FAIL,$msg);
                    }

                    $newData['updateDate'] = time();
                    if(isset($postData['pwd'])) {
                        $pwd = $this->encryptPwd($postData['pwd']);
                        $newData['password'] = $pwd;
                    }
                    $ret = $this->AdminUsers->edit($newData, array('id'=>$id));  //
                }
            }
            if ($ret) {
                $this->dwzAjaxReturn(self::AJ_RET_SUCC,AdminErrorDef::MODIFY_PWD_SUCCESS, dwz_rel($this->_data['controller'], 'user'));
            } else {
                $this->dwzAjaxReturn(self::AJ_RET_FAIL,AdminErrorDef::MODIFY_PWD_FAIL);
            }

        }else {
            $userInfo = array();
            if($id > 0) {
                $userInfo = $this->AdminUsers->getOne(array('id' => $id));
            }
            $data['userInfo'] = $userInfo;
            $this->display($data);
        }

    }

//后台管理：当前用户详情
    public  function  userDetail(){
        
          $id =$this->input->get('id');

          if(is_numeric($id)){
                $user_info = $this->AdminUsers->getUserById($id);
                if (empty($user_info)) {
                     $this->dwzAjaxReturn(self::AJ_RET_FAIL, AdminErrorDef::CURRENT_USER_INFO_ERROR);
                }
                $data['info'] = $user_info;
                $this->display($data);
          }else{
              $this->dwzAjaxReturn(self::AJ_RET_FAIL,AdminErrorDef::USER_ID_ERROR);
          }

   }

  //后台管理：修改当前用户密码
    public function  modifyCurrUserPwd(){

        if (is_ajax('post')) {
            $id     =  Auth::user()->id;

            if (!$id || !is_numeric($id)) {
                $this->dwzAjaxReturn(self::AJ_RET_FAIL, AdminErrorDef::CURRENT_USER_INFO_ERROR);
            }

            $pwd    = $this->input->post('pwd', true);
            $repwd  = $this->input->post('repwd', true);

            $this->load->helper(array('form', 'url'));
            $this->load->library('form_validation');

            if(isset($pwd)){
                $this->form_validation->set_rules('pwd', 'Password', 'trim|required|callback_check_pwd',
                    array('required' =>AdminErrorDef::PASSWORD_EMPTY)
                );
            }
            if(isset($repwd)){
                $this->form_validation->set_rules('repwd', 'Password Confirmation', 'trim|required|matches[pwd]',
                    array('required' =>AdminErrorDef::CONFIRMATION_PASSWORD_EMPTY,
                        'matches' => AdminErrorDef::PASSWORD_NO_SAME));
            }
            if ($this->form_validation->run() == FALSE) {
                $msg = validation_errors();
                $this->dwzAjaxReturn(self::AJ_RET_FAIL,$msg);
            }
            $newData['updateDate'] = time();
            if(isset($pwd)) {
                $newpwd = $this->encryptPwd($pwd);
                $newData['password'] = $newpwd;
            }

            $ret = $this->AdminUsers->edit($newData, array('id'=>$id));  //

            if ($ret) {
                $this->dwzAjaxReturn(self::AJ_RET_SUCC, AdminErrorDef::MODIFY_PWD_SUCCESS,  dwz_rel($this->_data['controller'], 'modifyCurrUserPwd'));
            } else {
                $this->dwzAjaxReturn(self::AJ_RET_FAIL, AdminErrorDef::MODIFY_PWD_SUCCESS);
            }
        } else {
            $where = array('id' => Auth::user()->id);
            $info = $this->AdminUsers->getOne($where);
            if (empty($info)) {
                $this->dwzAjaxReturn(self::AJ_RET_FAIL, AdminErrorDef::CURRENT_USER_INFO_ERROR);
            }
            $data = array(
                'info' => $info
            );
            $this->display($data);
        }

    }

   // 后台管理：其他用户详情
    public function  othersDetail(){
        $id =$this->input->get('id');
        if(is_numeric($id)){
            $user_info = $this->AdminUsers->getUserById($id);
            if (empty($user_info)) {
                $this->dwzAjaxReturn(self::AJ_RET_FAIL, AdminErrorDef::CURRENT_USER_INFO_ERROR);
            }
            $data['info'] = $user_info;
            $this->display($data);
        }else{
            $this->dwzAjaxReturn(self::AJ_RET_FAIL,AdminErrorDef::USER_ID_ERROR);
        }
    }
    /**
     * 修改当前用户自己信息
     */
    public function editCurrUser()
    {
        if (is_ajax('post')) {
            $id     =  $this->admin_user_info['id'];

            if (!$id || !is_numeric($id)) {
                $this->dwzAjaxReturn(self::AJ_RET_FAIL, '当前登录用户信息不存在');
            }

            $newpassword    = $this->input->post('newpassword', true);
            $repassword     = $this->input->post('repassword', true);
            $email          = $this->input->post('email', true);
            //$tel            = $this->input->post('tel', true);
            $tel = '';

            $info = array(
                'last_ip'       => ip2long($this->input->ip_address()),
                'last_time'     => time()
            );

            if ($newpassword == $repassword) {
                $info['password'] =$this->pwdMd5($newpassword);
            }

            if ($email) {
                $info['email'] = $email;
            }

//            if($tel){
//                $info['tel'] = $tel;
//            }

            if ($this->checkUserInfo(null, $tel, $email) == true) {
                $this->dwzAjaxReturn(self::AJ_RET_FAIL, '电话/邮箱 有重复信息');
            }

            $ret = $this->AdminUsers->edit($info, array('id' => $id));

            if ($ret) {
                $this->dwzAjaxReturn(self::AJ_RET_SUCC, '修改成功', $this->_data['controller']);
            } else {
                $this->dwzAjaxReturn(self::AJ_RET_FAIL, '修改失败');
            }
        } else {
            $where = array('id' => $this->admin_user_info['id']);

            $info = $this->AdminUsers->getOne($where);

            if (empty($info)) {
                $this->dwzAjaxReturn(self::AJ_RET_FAIL, '当前登录用户信息不存在');
            }

            $data = array(
                'info' => $info
            );

            $this->display($data);
        }
    }

    public function editUserPhone()
    {
        if (is_ajax('post')) {
            $id     =  $this->admin_user_info['id'];

            if (!$id || !is_numeric($id)) {
                $this->dwzAjaxReturn(self::AJ_RET_FAIL, '当前登录用户信息不存在');
            }

            $tel            = $this->input->post('tel', true);

            $info = array(
                'last_ip'       => ip2long($this->input->ip_address()),
                'last_time'     => time()
            );

            if ($tel) {
                $info['tel'] = $tel;
            }

            $ret = $this->AdminUsers->edit($info, array('id' => $id));

            if ($ret) {
                $this->dwzAjaxReturn(self::AJ_RET_SUCC, '验证手机成功', $this->_data['controller']);
            } else {
                $this->dwzAjaxReturn(self::AJ_RET_FAIL, '验证手机失败');
            }
        } else {
            $where = array('id' => $this->admin_user_info['id']);

            $info = $this->AdminUsers->getOne($where);

            if (empty($info)) {
                $this->dwzAjaxReturn(self::AJ_RET_FAIL, '当前登录用户信息不存在');
            }

            $data = array(
                'info' => $info
            );

            $this->display($data);
        }
    }

    /**
     * 删除
     */
    public function del()
    {
        $id = (int)$this->input->get('id', true);

        $ret = $this->AdminUsers->del(array('id' => $id));

        if ($ret) {
            $this->AdminCateHasUsers->del(array('user_id' => $id));
            $this->AdminUserHasRoles->del(array('user_id' => $id));

            $this->dwzAjaxReturn(self::AJ_RET_SUCC, '删除成功', $this->_data['controller'], null, 'no');
        } else {
            $this->dwzAjaxReturn(self::AJ_RET_FAIL, '删除失败');
        }
    }

    /**
     * 设置管理员的所属角色
     *
     * @param $id
     * @return bool
     */
    public function setRoles($id)
    {
        $this->AdminUserHasRoles->del(array('user_id' => $id));

        $role_ids   = $this->input->post('role_ids', true);

        if ($role_ids) {
            $add_batch_arr = array();
            $role_ids = explode(',', $role_ids);
            $role_ids = array_unique($role_ids);
            foreach ((array)$role_ids as $v) {
                if ($v) {
                    $add_batch_arr[] = array(
                        'role_id' => $v,
                        'user_id' => $id
                    );
                }
            }

            if ($add_batch_arr) {
                return $this->AdminUserHasRoles->addBatch($add_batch_arr);
            }
        }

        return false;
    }
}
