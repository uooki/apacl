<?php
/**
 * Created by PhpStorm.
 * User: zhouyang
 * Date: 2015/8/11
 * Time: 15:43
 */
namespace Xy\Application\Models;
use  Xy\Application\Models\AdminRolesModel;
use Xy\Application\Models\Defined\AdminConstDef;

class AdminPermissionsModel extends BaseModel
{

    /**
     * 初始化
     */
    public function __construct()
    {
        parent::__construct();

        $this->_db_obj      = new \Xy\Application\Models\DB\AdminPermissionsDB();
    }

    /**
     * 获取指定用户拥有的所有权限
     * $user 需要是数组或对象
     */
    public  function getUserPermissions($user){
        $all =null;

        if(is_array($user)){
            $user = (object)$user;
        }
        if(!is_object($user)) {
             return $all;
        }
        $u_permissions = $this->getPermissionWithUser($user->id);
        $r_permissions = $this->getPermissionWithRoleTree($user->role_id);
        if(!empty($u_permissions)){
            array_unshift($r_permissions,$u_permissions);
        }
        return $r_permissions;

    }


    /**
     * 获取授予指定用户的权限
     */
    public function  getPermissionWithUser($uid){

        $res = null;
        if(is_numeric($uid)){
            $res =  $this->_db_obj->getPermissions($uid);
        }
        return $res;
    }

    /**
     * 获取授予指定角色的权限
     */
    public function  getPermissionWithRole($rid){
        $res = null;
        if(is_numeric($rid)) {
            $res =  $this->_db_obj->getPermissions($rid,'role');
        }
        return $res;
    }
    // 获取所有角色（包括继承角色）的权限
    public function  getPermissionWithRoleTree($rid){

           if(!$rid){
               return null;
           }
           $role = new AdminRolesModel();
           $rids =  $role->getRoleParentPath($rid);
           $lists = array();
           // 获取所有父角色的的权限
           foreach($rids as $k=>$v){
               $lists[$k] = $this->getPermissionWithRole($v['id']);
           }
           //var_dump($lists);
           return  $lists;
    }


    public  function getPermissionList($page,$page_list,$where=array(),$orderby='id desc'){

        $list =null ;
        $cond = is_array($where)?$where:array();
        $this->_db_obj->setSqlFiled('id,resource_id,caller_id,caller_type,allow,createDate,updateDate');
        $list = $this->_db_obj->getPage($page,$page_list,$cond,$orderby);

        if(!empty($list['list'])){


            $ids=[AdminConstDef::CALLER_TYPE_ROLE=>[],AdminConstDef::CALLER_TYPE_USER=>[],'resource'=>[]];

            foreach($list['list'] as $k=>$v){

                  if($v['caller_type'] == AdminConstDef::CALLER_TYPE_ROLE){
                       $ids[AdminConstDef::CALLER_TYPE_ROLE][]=$v['caller_id'];
                  }else{
                      $ids[AdminConstDef::CALLER_TYPE_USER][]=$v['caller_id'];
                  }
                  $ids['resource'][]=$v['resource_id'];
            }

          //  var_dump($ids);exit;

            $r_obj =  new  AdminRolesModel();
            $u_obj = new AdminUsersModel();
            $res_obj = new AdminResourcesModel();

            if(!empty($ids[AdminConstDef::CALLER_TYPE_ROLE])){

                $r_obj->_db_obj->setSqlFiled('id,name');
                $res[AdminConstDef::CALLER_TYPE_ROLE]=$r_obj->_db_obj->getAll(array('where_in'=>array('id'=>$ids[AdminConstDef::CALLER_TYPE_ROLE])));

            }

            if(!empty($ids[AdminConstDef::CALLER_TYPE_USER])){
                $u_obj->_db_obj->setSqlFiled('id,name');
                $res[AdminConstDef::CALLER_TYPE_USER]=$u_obj->_db_obj->getAll(array('where_in'=>array('id'=>$ids[AdminConstDef::CALLER_TYPE_USER])));
            }
            if(!empty($ids['resource'])){
                $res_obj->_db_obj->setSqlFiled('id,title,desc,uri');
                $res['resource'] = $res_obj->_db_obj->getAll(array('where_in'=>array('id'=>$ids['resource'])));
            }

            foreach($list['list'] as $k=>&$v){

                 foreach($res[$v['caller_type']] as $k1=>$v1){

                       if($v1['id']==$v['caller_id']){
                           $v['caller_name'] = $v1['name'];
                       }
                 }
                 foreach($res['resource'] as $k2=>$v2){
                      if($v2['id']==$v['resource_id']){
                          $v['resource_title'] = $v2['title'];
                          $v['resource_desc'] = $v2['desc'];
                          $v['uri'] = $v2['uri'];
                      }
                 }
            }
            unset($v);

        }

        return $list;
    }

    public  function  getPermissionById($id){

          if(!is_numeric($id)){
              return  false;
          }
          $info  = $this->_db_obj->getOne(array('id'=>$id));
          return $info;
    }

    public  function  getPermissionsByResourceId($id){
        if(!is_numeric($id)){
            return  null;
        }

        $list = $this->_db_obj->getAll(array('resource_id'=>$id));
        return  $list;

    }

    public function addPermissions($data){

        $ret = false;
        if(!is_array($data)){
             return  $ret;
        }
        if(is_array($data[0])){
           // 添加多条
           // $ret = $this->_db_obj->addBatch($data);
            $count = 0;
            foreach($data as $k=>$v){
                $sig = $this->_db_obj->checkAdd($v,array('resource_id'=>$v['resource_id'],'caller_id'=>$v['caller_id'],'caller_type'=> $v['caller_type']));
                if($sig){
                    $count++;
                }
            }
            $ret = ($count==count($data))?1:0;
        }else{
            $ret = $this->_db_obj->checkAdd($data,array('resource_id'=>$data['resource_id'],'caller_id'=>$data['caller_id'],'caller_type'=> $data['caller_type']));
        }
        return $ret;

    }

    /**
     *  授权给角色/用户：包括允许和禁用
     */
    public function givePermission($id,$data,$type = 'role'){


         if(!is_numeric($id)){
              return false;
         }
         // 该角色/用户对该资源是否已配置权限？
         $has = $this->getPermissionByResource();
         if($type == 'role'){

              $this->_db_obj->add();

         }elseif($type == 'user'){

         }


    }

}
