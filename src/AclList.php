<?php
use Xy\Application\Models\AdminUsersModel;
use Xy\Application\Models\AdminRolesModel;
use Xy\Application\Models\AdminPermissionsModel;
use Xy\Application\Models\Defined\AdminConstDef;
/**
 * Created by PhpStorm.
 * User: shaoxj
 * Date: 2016/6/14
 * Time: 11:06
 */
class AclList
{
    protected $CI;
    protected $user;
    protected $role;
    protected $permission;
    static  $list;

    public function __construct(){
        $this->CI =& get_instance();
    }

    /**
     * @param $resource
     * @param int $uid
     * @return bool|int
     *
     * 使用方法：$resource  可以是资源的名称，也可以是资源的uri  ,如:
     *
     * AclList::isAllow($name) 或 AclList::isAllow($uri)
     *
     */
    static public function  isAllow($resource, $uid=0){

             if(!Auth::isLogin()){
                 return false;
             }
             $super =  Auth::user()->is_super;
             if(empty(self::$list)){
                  self::initList();
             }
             if($uid){
                 // do something
             }else{
                 //检查当前用户
                 if($super || self::$list==AdminConstDef::ACL_LIST_ALL){
                     return true;
                 }
                 $is = self::check($resource);
                 return  $is;
             }

    }

    static  public  function  check($resource){

            if(empty(self::$list)){
                self::initList();
            }
            if(self::$list == AdminConstDef::ACL_LIST_ALL){
                return true;

            }
           // $caller_id = Auth::user()->id;
           // $caller_type = AdminConstDef::CALLER_TYPE_USER;
            $count =  count(self::$list);
            $has = 0;
            for($i=0;$i<$count;$i++){
                if($has!==0){
                     break;
                }else{
                    foreach(self::$list[$i] as $k => $v){
                        $pos = strpos($resource,'/');
                        if($pos===false && $resource == $v['name'] ){
                            $has = $v['allow']?true:false;
                            break;
                        }
                        if($pos!==false && $resource == $v['uri'] ){
                            $has = $v['allow']?true:false;
                            break;
                        }
                    }
                }
            }
            return $has;
    }

    static public  function initList(){
            if(!Auth::isLogin()){
                return false;
            }
            $super =  Auth::user()->is_super;
            if($super){
                self::$list = AdminConstDef::ACL_LIST_ALL;
            }
            if(!$super && empty(self::$list)){
                self::$list = self::getAclList();
            }
            return 0;
    }

    static protected  function getAclList($uid=null){

        if(!empty($uid)){
            //$m_user = new AdminUsersModel();
            //$m_role = new AdminRolesModel();
            //$m_permission = new AdminPermissionsModel();
        }else{
            $m_permission = new AdminPermissionsModel();
            $user = Auth::user();
            // 获取用户拥有的所有权限
            $list = $m_permission->getUserPermissions($user);
            return  $list;
        }

        return null;

    }



}