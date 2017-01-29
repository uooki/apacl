<?php
/**
 * Created by PhpStorm.
 * User: zhouyang
 * Date: 2015/8/11
 * Time: 15:43
 */
namespace Xy\Application\Models;
use  Xy\Application\Models\AdminRolesModel;

class AdminResourcesModel extends BaseModel
{

    /**
     * 初始化
     */
    public function __construct()
    {
        parent::__construct();

        $this->_db_obj      = new \Xy\Application\Models\DB\AdminResourcesDB();
    }
    

    /**
     * 获取资源列表信息
     */
    public function  getResourceList($page,$page_list,$where=array(),$orderby='id desc'){

        $list =null ;
        $cond = is_array($where)?$where:array();
        $this->_db_obj->setSqlFiled('id,title,name,desc,uri,createDate,updateDate');
        $list = $this->_db_obj->getPage($page,$page_list,$cond,$orderby);
        return $list;
    }

    /**
     * 根据id 获取资源信息
     */
    public function  getResourceById($rid){
        $res = null;
        if(is_numeric($rid)) {
            $res =  $this->_db_obj->getOne(array('id'=>$rid));
        }
        return $res;
    }
    // 根据uri 获取资源信息
    public function  getResourceByUri($uri){

    }
    



}
