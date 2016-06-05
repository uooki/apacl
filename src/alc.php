<?php
namespace  Apacl;
use   Helper\AroTree;
class  Apacl{

  
	
    public $caller=null;

    public function __construct(caller $caller){
        
              $this->caller=$caller;   //caller 是一个实现了caller interface 的user modul

    }
	
	
	public function isallow($route){
		
		    if("/"==$route){
				
			
			}elseif("all"==$route){
				
				
			}
		
	}
    
    public  function  getPermissions(){
    
	   if($this->caller){
          
		   // 获取note 在树中的路径
             $tree=Helper\AroTree::getPath($this->note->getId());		  
		   // 计算出caller 的权限
		   
	   }else{
		   
		   // do something
	   }
	
	
	
    }

    
    
}


?>
