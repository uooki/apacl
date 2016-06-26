<?php
namespace  Apacl;
use Apacl/Role;
class  RolePermission{
   
    protected  $role=null;
	protected  $permissions=null;
   
	public function  __construct(Role $role){
				
		if($role instanceof Role){
			
	         $this->role=$role;
		}
	
	}
	
    public  function getPermissions(){
		
		  return $this->permissions;
	}    

	
	/*
	* 为角色添加权限
	*/
	public function  addPermission($permissions){
		
		
	}
	/*
	* 为角色删除权限
	*/
	public  function delPermissions($permissions){
		
		
	}
	
   
}


?>
