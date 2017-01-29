<?php
/**
 * Created by PhpStorm.
 * User: zhouyang
 * Date: 2015/8/11
 * Time: 15:43
 */
namespace Xy\Application\Models;

use Xy\Application\Models\Defined\AdminConstDef;
use Xy\Application\Models\AdminPermissionsModel;
class AdminRolesModel extends BaseModel
{
    /**
     * 初始化
     */
    public function __construct()
    {
        parent::__construct();

        $this->_db_obj      = new \Xy\Application\Models\DB\AdminRolesDB();
    }

    /**
     * 根据菜单编号 获取 角色信息
     *
     * @param $cate_id
     * @return bool
     */
    public function getRolesByCateId($cate_id)
    {
        return $this->_db_obj->getRolesByCateId($cate_id);
    }

    
    public  function  getRoleById($id){

        $rinfo = null;
        if(is_numeric($id)){
            $rinfo=$this->_db_obj->getOne(array('id'=>$id,'is_delete'=>0));
        }
        return $rinfo;
    }

    public function  getRoles(){

        $roles=$this->_db_obj->getAll(array('is_delete'=>0));
        return  $roles;

    }

    public function  getRoleList($page,$page_list,$where=array(),$orderby='id desc'){
        $list =null ;
        $cond = is_array($where)?$where:array();
        $this->_db_obj->setSqlFiled('id,name,show_name,desc,type,pid,createDate,updateDate');
        $list = $this->_db_obj->getPage($page,$page_list,$cond,$orderby);
        return $list;
    }

    // 获取角色的父路径
    public function  getRoleParentPath($rid){
          if(!is_numeric($rid)){
              return  false;
          }
          $path = $this->_db_obj->getRolePath($rid);
          return $path;
    }
    // 获取可用父角色:非超级管理员角色以及非普通用户
    // 添加角色为其选择父角色
    public function  getValidParentRole(){
          $all = $this->_db_obj->getAll(array('type'=>'other'));
          return $all;
    }

    public function getValidRoleForAccredit($rid){
        
          $all = $this->_db_obj->getAll(array('where_in'=>array('type'=>array(AdminConstDef::ROLE_TYPE_NORMAL,AdminConstDef::ROLE_TYPE_OTHER))));
          
          //过滤已拥有该资源权限的角色
          $per = new AdminPermissionsModel();
          $p_list = $per->getPermissionsByResourceId($rid);
          $ids = [];
          if(is_array($p_list)){
              foreach($p_list as $k=>$v){
                   if( AdminConstDef::CALLER_TYPE_ROLE == $v['caller_type']){
                          $ids[]=$v['caller_id'];
                   }
              }
          }

          if(is_array($all)&&is_array($ids)){

              foreach($all as $k1=>$v1){
                    if(in_array($v1['id'],$ids)){
                        unset($all[$k1]);
                    }
              }
          }
          return $all;
    }

    // 获取当前用户能够添加的角色
    //public function  getValidRoleForUser(){
    //}
    
    public function  isSuper($rid){
          $role = $this->_db_obj->getOne(['id'=>$rid]);
          $res = false;
          if(is_array($role)&&$role['type']==AdminConstDef::ROLE_TYPE_SUPER){
              $res= true;
          }
          return  $res;
    }
}
