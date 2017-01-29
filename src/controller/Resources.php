<?php
use Xy\Application\Models\Defined\AdminErrorDef;
use Xy\Application\Models\Defined\AdminConstDef;
defined('BASEPATH') or exit('No direct script access allowed');
include_once 'Base.php';

/**
 * Class Permissions
 */
class Resources extends Base
{
    /**
     * 初始化
     */
    public function __construct()
    {
        parent::__construct();
    }
    

    /**
     * 资源列表
     */
    public function  resourcesList(){


        //todo
        // search condition  :  name , role / group
        $page           = (int)$this->input->request('pageNum', true);
        $page           = $page ? $page : 1;
        $page_list      = (int)$this->input->request('numPerPage', true);
        $page_list      = $page_list ? $page_list : self::DEFAULT_PAGE_LIST;

        $find = (int)$this->input->get('find');
        $order_by = 'id desc';
        $data = $this->AdminResources->getResourceList($page, $page_list, array(), $order_by);
        $data['find'] = $find;

        $this->display($data);

    }

    /**
     * 添加或编辑资源
     */
    public  function resourceEdit(){
        // method get:to add
        $id = $this->input->get('id');
        if (is_ajax('post')) {
            $postData = $this->input->post(null, true);
            $newData = array();
            if(!empty($postData)) {
                $postData['uri'] = trim($postData['uri'],'  /\\');
                $this->load->helper(array('form', 'url'));
                $this->load->library('form_validation');
                $this->form_validation->set_rules('title', 'resource_title', 'trim|required',
                    array('required'=>AdminErrorDef::RESOURCE_TITLE_REQUIRE)
                );
                
                $this->form_validation->set_rules('r_name', 'resource_name', 'callback_check_resource_name');
                
                $this->form_validation->set_rules('uri', 'resource_uri', 'trim|required|callback_check_resource_uri',
                    array('required'=>AdminErrorDef::RESOURCE_URI_EMPTY));
                
                if ($this->form_validation->run() == FALSE) {
                    $msg = validation_errors();
                    $this->dwzAjaxReturn(self::AJ_RET_FAIL,$msg);
                }

                $newData['title'] = $postData['title'];
                $newData['name'] = !empty($postData['r_name'])?$postData['r_name']:'';
                $newData['desc'] = $postData['desc'];
                $newData['uri']  = $postData['uri'];
                $newData['controller'] = breakUri($postData['uri']);
                $newData['action'] = breakUri($postData['uri'],2);
                $newData['updateDate'] = time();

                // id 待编辑用户id
                if($id>0){
                    $ret = $this->AdminResources->edit($newData, array('id'=>$id));  // 更新记录
                }else{
                    $newData['createDate'] = time();
                    $ret = $this->AdminResources->add($newData);    //新增记录
                    //附加：授权角色访问该资源
                    if(isset($postData['role_id']) && $ret && is_numeric($ret)){
                        $ds=null;
                        foreach($postData['role_id'] as $k=>$v){
                            $d['allow'] = 1;
                            $d['resource_id'] =$ret;
                            $d['caller_id'] =(int)$v;
                            $d['caller_type'] = AdminConstDef::CALLER_TYPE_ROLE;
                            $d['createDate'] =time();
                            $d['updateDate'] =time();
                            $ds[]=$d;
                        }
                        $ret=$this->AdminPermissions->addPermissions($ds);
                    }
                }
            }
            if ($ret) {
                $this->dwzAjaxReturn(self::AJ_RET_SUCC, $id>0 ? AdminErrorDef::MODIFY_SUCCESS : AdminErrorDef::ADD_SUCCESS, dwz_rel($this->_data['controller'], 'resourcesList'));
            } else {
                $this->dwzAjaxReturn(self::AJ_RET_FAIL, $id>0 ? AdminErrorDef::MODIFY_FAIL : AdminErrorDef::ADD_FAIL, dwz_rel($this->_data['controller'], 'resourcesList'));
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
            $this->display($data);
        }

    }


    /**
     * 资源详情
     */
    public function  resourceDetail(){


    }



}
