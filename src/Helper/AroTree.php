<?php
namespace  Apacl\Helper;

use  models;
class  AroTree{

   // 获取末节点到根节点的路径
   static public function  getPath($noteid){
	   
	     $nid=$noteid;
		 
		 $path[]=$noteid;
		 while($pid=Mnote::getPid($nid)){
			 
			   $nid=$pid;
			   $path[]=$pid;   			 
		 }
		 
		 return  $path;
	    
   }
   
   
   static public function getNotePermissions($noteid){
	   
	    // $path=self::getPath($noteid);	 
		 
   }
   
}


?>
