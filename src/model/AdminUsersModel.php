<?php
/**
 * Created by PhpStorm.
 * User: zhouyang
 * Date: 2015/8/11
 * Time: 15:43
 */
namespace Xy\Application\Models;

use  Xy\Application\Models\AdminRolesModel;
use  Xy\Application\Models\Defined\AdminConstDef;

class AdminUsersModel extends BaseModel
{
    
 
    
    /**
     * 初始化
     */
    public function __construct()
    {
        parent::__construct();

        $this->_db_obj          = new \Xy\Application\Models\DB\AdminUsersDB();

        $this->_memcache_obj    = new \Xy\Application\Models\Memcached\AdminUsersMemcache();

    }

    // 获取用户信息    
    public function  getUserById($uid){

         //todo: optimize  sql  to one
         //todo: 获取部分字段
         $uinfo = null;
         if(is_numeric($uid)){
             $condition = $this->_db_obj->getCondition(array('id'=>$uid));
             $uinfo = $this->getUserInfo($condition);
         }
         return $uinfo;
    }
    
    public  function getUserByName($name){

        $uinfo = null;
        if(!empty($name)){
            $condition = $this->_db_obj->getCondition(array('id'=>$name));
            $uinfo = $this->getUserInfo($condition);
        }
        return $uinfo;
    }
    // 后台用户信息统一从这里取
    public function  getUserInfo($condition){
        $uinfo = null;
        if(is_array($condition)){
            $this->_db_obj->setSqlFiled('id,name,nickname,email,tel,is_role_leader,role_id,is_root');
            $uinfo=$this->_db_obj->getOne($condition);
            if(is_array($uinfo)){
                $role_obj = new  AdminRolesModel();
                $rinfo =$role_obj->getRoleById($uinfo['role_id']);
                $uinfo['is_super'] = $rinfo['type']==AdminConstDef::ROLE_TYPE_SUPER?1:0;
                $uinfo['role_name'] = $rinfo['name'];
            }
        }
        return $uinfo;
    }


    public function  getUserList($page,$page_list,$where=array(),$orderby='id desc'){

        $list =null ;
        $cond = is_array($where)?$where:array(); 
        $condition = $this->_db_obj->getCondition($cond);
        $this->_db_obj->setSqlFiled('id,name,nickname,email,tel,is_role_leader,role_id,is_root,createDate,updateDate');
        $list = $this->_db_obj->getPage($page,$page_list,$condition,$orderby);
        return $list;
    }

    public  function  getUserRoleInfo($uid){

        /*
        $table = $this->_db_obj->_table_obj;
        $all=$this->_db_obj->_read_db_obj->get($table);

        foreach ($all->result() as $row) {
            //echo $row->title;
            //var_dump($row);
        }
        var_dump($aa);
        exit;
        */
    }

    /**
     * 设置 手机验证码 10分钟有效
     *
     * @param $tel
     * @param $val
     * @return mixed
     */
    public function setUserPhoneCode($tel, $val)
    {
        return $this->_memcache_obj->save($tel, $val, CACHE_TEN_MINUTE);
    }

    /**
     * 获取 手机验证码
     *
     * @param $tel
     * @return mixed
     */
    public function getUserPhoneCode($tel)
    {
        return $this->_memcache_obj->get($tel);
    }

    /**
     * 验证用户 请求次数
     *
     * @param $tel
     * @param int $nums
     * @return bool|mixed
     */
    public function checkUserPhoneCodeNums($tel, $nums = 10){
        return true;
        $curr_nums = $this->_memcache_obj->get($tel);

        if($curr_nums === false){
            $ret = $this->_memcache_obj->save($tel, 1, CACHE_HOUR);

            return $ret;
        }else{
            if($curr_nums < $nums){
                $ret = $this->_memcache_obj->increment($tel);

                return $ret;
            }else{
                return false;
            }
        }
    }
}
