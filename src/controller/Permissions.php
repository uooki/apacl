<?php
use Xy\Application\Models\Defined\AdminErrorDef;
use Xy\Application\Models\Defined\AdminConstDef;
defined('BASEPATH') or exit('No direct script access allowed');
include_once 'Base.php';

/**
 * Class Permissions
 */
class Permissions extends Base
{
    /**
     * 初始化
     */
    public function __construct()
    {
        parent::__construct();
    }


    public  function permissionsList(){

        //todo
        // search condition  :  name , role / group
        $page           = (int)$this->input->request('pageNum', true);
        $page           = $page ? $page : 1;
        $page_list      = (int)$this->input->request('numPerPage', true);
        $page_list      = $page_list ? $page_list : self::DEFAULT_PAGE_LIST;

        $find = (int)$this->input->get('find');
        $order_by = 'id desc';
        $data = $this->AdminPermissions->getPermissionList($page, $page_list, array(), $order_by);
        $data['find'] = $find;

        $this->display($data);

    }
    /**
     * 获取授予指定用户的权限
     */
    public function  getPermissionWithUser(){


    }
    //授权用户
    /**
     *
     */
    public function accredit(){
       
        $id = $this->input->get('id');
        
        if (is_ajax('post')) {
            $postData = $this->input->post(null, true);
            $newData = array();
            if(!empty($postData)) {
               // var_dump($postData);exit;
                $this->load->helper(array('form', 'url'));
                $this->load->library('form_validation');
                $this->form_validation->set_rules('resource_id', 'resource_id', 'trim|required|callback_check_resource_id',
                    array('required'=>AdminErrorDef::RESOURCE_NAME_EMPTY)
                );

                if ($this->form_validation->run() == FALSE) {
                    $msg = validation_errors();
                    $this->dwzAjaxReturn(self::AJ_RET_FAIL,$msg);
                }

                // resource id ;  roles_id ; user_id
                // 授权给角色
                $ret = 0;
                $count = 0;
                if(isset($postData['role_id'])){
                     $count = count($postData['role_id']);
                      foreach($postData['role_id'] as $k=>$v){
                           if(is_numeric($v)){
                               $d['allow'] = 1;
                               $d['resource_id'] =(int)$postData['resource_id'];
                               $d['caller_id'] =(int)$v;
                               $d['caller_type'] = AdminConstDef::CALLER_TYPE_ROLE;
                               $d['createDate'] =time();
                               $d['updateDate'] =time();
                               $r=$this->AdminPermissions->checkAdd($d,array('resource_id'=>(int)$postData['resource_id'],'caller_id'=>(int)$v,'caller_type'=> AdminConstDef::CALLER_TYPE_ROLE));
                               $ret =( $r && is_numeric($r))?($ret+1):$ret;
                           }
                      }
                }
                //授权给用户
                if(isset($postData['user_id'])){
                    //todo
                }
            }
            if ($ret&&$ret==$count) {
                $this->dwzAjaxReturn(self::AJ_RET_SUCC, AdminErrorDef::ACCREDIT_ROLE_SUCCESS, dwz_rel($this->_data['controller'], 'resourcesList'));
            } else {
                $this->dwzAjaxReturn(self::AJ_RET_FAIL, AdminErrorDef::ACCREDIT_ROLE_FAIL, dwz_rel($this->_data['controller'], 'resourcesList'));
            }
        }else {
            $resourceInfo = array();
            if($id > 0) {
                $resourceInfo = $this->AdminResources->getOne(array('id' => $id));
            }
            $data['resourceInfo'] =$resourceInfo;

            //获取可以授权角色信息
            $roles = $this->AdminRoles->getValidRoleForAccredit($id);
            $data['roles'] = $roles;
            $data['users'] = array();
            $this->display($data);
        }
        

    }


    /**
     *  切换权限：允许或禁止
     */
    public  function  changeAllow(){

        $id  = $this->input->get('id',true);
        $allow  = $this->input->get('allow',true);
        $toAllow  =  $allow?0:1;
        if(!is_numeric($id)){
             return  false;
        }
        $ret = $this->AdminPermissions->edit(array('allow'=>$toAllow),array('id'=>$id));
        $url = $this->uri->ruri_string();
        if ($ret) {
            $this->dwzAjaxReturn(self::AJ_RET_SUCC, AdminErrorDef::MODIFY_SUCCESS, dwz_rel($this->_data['controller'], 'permissionsList'),null,'forward',$url);
        } else {
            $this->dwzAjaxReturn(self::AJ_RET_FAIL,  AdminErrorDef::MODIFY_FAIL, dwz_rel($this->_data['controller'], 'permissionsList'),null,'forward',$url);
        }

    }

    /**
     * 获取指定用户所有权限
     */
    public  function getUserPermissions(){
         //todo
    }


    /**
     * 获取授予指定角色的权限
     */
    public function  getPermissionWithRole(){
         //todo
    }

    /**
     * 授权给用户: 包括允许和禁用
     */
    public function  permissionToUser(){
        //todo
    }

    /**
     *  授权给角色：包括允许和禁用
     */
    public function  permissionToRole(){
        //todo
    }

}
