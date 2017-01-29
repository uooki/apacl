<?php
use Xy\Application\Models\Defined\AdminErrorDef;
use Xy\Application\Models\Defined\AdminConstDef;
defined('BASEPATH') or exit('No direct script access allowed');
include_once 'Base.php';

class Roles extends Base
{
    /**
     * 初始化
     */
    public function __construct()
    {
        parent::__construct();
    }

    //后台管理：角色列表
    public function rolesList(){

        //todo
        // search condition : name , id
        $page           = (int)$this->input->request('pageNum', true);
        $page           = $page ? $page : 1;
        $page_list      = (int)$this->input->request('numPerPage', true);
        $page_list      = $page_list ? $page_list : self::DEFAULT_PAGE_LIST;

        $find = (int)$this->input->get('find');
        $order_by = 'id desc';
        $data['find'] = $find;

        //获取用户其他信息：角色
        $data = $this->AdminRoles->getRoleList($page, $page_list,array(),$order_by);
        $this->display($data);

    }
    //后台管理：添加和编辑角色
    public function  roleEdit(){

        // method get :to add
        $id = $this->input->get('id');

        if (is_ajax('post')) {
            $postData = $this->input->post(null, true);
            $newData = array();
            if(!empty($postData)) {

                $this->load->helper(array('form', 'url'));
                $this->load->library('form_validation');
                $this->form_validation->set_rules('r_name', 'rolename', 'trim|required',
                    array('required'=>AdminErrorDef::ROLENAME_EMPTY)
                );

                if(isset($postData['p_role_id'])){
                    $this->form_validation->set_rules('p_role_id', 'parent role id', 'callback_check_prid');
                }
                if ($this->form_validation->run() == FALSE) {
                    $msg = validation_errors();
                    $this->dwzAjaxReturn(self::AJ_RET_FAIL,$msg);
                }

                $newData['name'] = $postData['r_name'];
                $newData['show_name'] = $postData['show_name'];
                $newData['desc'] = $postData['desc'];
                $newData['updateDate'] = time();

                $newData['type'] = isset($postData['type'])? $postData['type'] : AdminConstDef::ROLE_TYPE_OTHER;

                // 检查角色是否是超级管理员
                // id 待编辑用户id
                if($id>0){
                    $ret = $this->AdminRoles->edit($newData, array('id'=>$id));  // 更新记录
                }else{
                    $newData['pid'] =  isset($postData['p_role_id'])? $postData['p_role_id']:0;
                    $newData['createDate'] = time();
                    $ret = $this->AdminRoles->checkAdd($newData,array('name'=>$newData['name']));                    //新增记录
                }
            }

            if ($ret) {
                $this->dwzAjaxReturn(self::AJ_RET_SUCC, $id>0 ? AdminErrorDef::MODIFY_SUCCESS : AdminErrorDef::ADD_SUCCESS, dwz_rel($this->_data['controller'], 'rolesList'));
            } else {
                $this->dwzAjaxReturn(self::AJ_RET_FAIL, $id>0 ? AdminErrorDef::MODIFY_FAIL : AdminErrorDef::ADD_FAIL, dwz_rel($this->_data['controller'], 'rolesList'));
            }
        }else {
            $roleInfo = array();
            if($id > 0) {
                $roleInfo = $this->AdminRoles->getOne(array('id' => $id));
            }
            //获取可用父角色信息
            $roles = $this->AdminRoles->getValidParentRole();
            //获取可用角色类型 :
            // todo
            $data['roleInfo'] = $roleInfo;
            $data['roles'] = $roles;
            $this->display($data);
        }
    }


    /**
     * 首页/列表页
     */
    /*
    public function index()
    {
        $page   =   $this->input->get_post('pageNum', true);
        $page   =   $page ? (int)$page : 1;
        $page_list  =  $this->input->get_post('numPerPage', true);
        $page_list  =   $page_list ? (int)$page_list : self::DEFAULT_PAGE_LIST;
        $title  = $this->input->get_post('title', true);
        $title  = $title ? $title : null;

        $where = array();

        if (!empty($title)) {
            $where['like']['title'] = $title;
        }

        $data = $this->AdminRoles->getPage($page, $page_list, $where);

        $data['title']      = $title;

        $this->display($data);
    }
    */

    /**
     * 添加
     */
    /*
    public function add()
    {
        if (is_ajax('post')) {
            $title  = $this->input->post('title', true);

            $info = array(
                'title'         => $title
            );

            if (!$title) {
                $this->dwzAjaxReturn(self::AJ_RET_FAIL, '必须填写角色名');
            }

            $ret = $this->AdminRoles->add($info);

            if ($ret) {
                $this->dwzAjaxReturn(self::AJ_RET_SUCC, '添加成功', $this->_data['controller']);
            } else {
                $this->dwzAjaxReturn(self::AJ_RET_FAIL, '添加失败');
            }
        } else {
            $info = array(
                'id'            => '',
                'title'         => ''
            );

            $this->display(array('info' => $info), 'info');
        }
    }
    */

    /**
     * 修改
     */
    /*
    public function edit()
    {
        if (is_ajax('post')) {
            $id     = $this->input->post('id', true);

            $title  = $this->input->post('title', true);

            $info = array(
                'title'         => $title
            );

            if (!$id || !$title) {
                $this->dwzAjaxReturn(self::AJ_RET_FAIL, '参数错误');
            }

            $check_info = $this->AdminRoles->getOne($info);

            if($check_info && $info['id'] !== $id){
                $this->dwzAjaxReturn(self::AJ_RET_FAIL, '当前角色已存在');
            }

            $ret = $this->AdminRoles->edit($info, array('id' => $id));

            if ($ret) {
                $this->dwzAjaxReturn(self::AJ_RET_SUCC, '修改成功', $this->_data['controller']);
            } else {
                $this->dwzAjaxReturn(self::AJ_RET_FAIL, '修改失败');
            }
        } else {
            $id = $this->input->get('id', true);

            $where = array('id' => $id);
            $info = $this->AdminRoles->getOne($where);

            $data = array(
                'info' => $info
            );

            $this->display($data, 'info');
        }
    }
*/
    /**
     * 删除
     */
    /*
    public function del()
    {
        $id = (int)$this->input->get('id', true);

        $ret = $this->AdminRoles->del(array('id' => $id));

        if ($ret) {
            $this->AdminCateHasRoles->del(array('role_id' => $id));
            $this->AdminUserHasRoles->del(array('role_id' => $id));

            $this->dwzAjaxReturn(self::AJ_RET_SUCC, '删除成功', $this->_data['controller'], null, 'no');
        } else {
            $this->dwzAjaxReturn(self::AJ_RET_FAIL, '删除失败');
        }
    }
    */

    /**
     * 设置 菜单/管理员 角色
     */
    /*
    public function setRoles()
    {
        $title = $this->input->get_post('title', true);

        if (is_ajax('post') && empty($title)) {
            $role_ids = $this->input->get_post('role_ids');
            $user_id = $this->input->get_post('user_id');

            if (!$role_ids) {
                $this->dwzAjaxReturn(self::AJ_RET_FAIL, '至少选择一个角色');
            }

            $this->AdminUserHasRoles->del(array('user_id' => $user_id));

            $ret = false;
            if ($role_ids) {
                $add_arr = array();

                $role_ids = array_unique($role_ids);
                foreach ($role_ids as $v) {
                    if ($v) {
                        $add_arr[] = array(
                            'user_id'   => $user_id,
                            'role_id'   => $v
                        );
                    }
                }

                if ($add_arr) {
                    $ret = $this->AdminUserHasRoles->addBatch($add_arr);
                }
            }

            if ($ret) {
                $this->dwzAjaxReturn(self::AJ_RET_SUCC, '角色设置成功', $this->_data['controller']);
            } else {
                $this->dwzAjaxReturn(self::AJ_RET_FAIL, '修改失败');
            }
        } else {
            $user_id    = (int)$this->input->get_post('user_id', true);

            $dialog_rel = $this->input->get('dialog_rel', true);
            $parent_rel = $this->input->get('parent_rel', true);

            $set_type   = $this->input->get('set_type', true);


            $role_ids = array();
            if ($user_id) {
                $user_roles = $this->AdminUserHasRoles->getRolesByUserId($user_id);

                $role_ids = array();
                foreach ($user_roles as $v) {
                    $role_ids[] = $v['id'];
                }
            }

            $where = array();

            if ($title) {
                $where['like']['title'] = $title;
            }

            $list = $this->AdminRoles->getAll($where);

            $data = array(
                'parent_rel'    => $parent_rel,
                'dialog_rel'    => $dialog_rel,
                'list'          => $list,
                'title'         => $title,
                'set_type'      => $set_type,
                'user_id'       => $user_id,
                'role_ids'      => $role_ids
            );

            $this->display($data);
        }
    }

    */
}
