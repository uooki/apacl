<?php
namespace  Apacl;


class  Permission{




    public function __construct(){
                   

    }
    
    public  function  getPermissions($notes){
    
	    $permiss=null;
	    foreach($notes as $k=>$v){
			 // $v 是note id
			 $res = Mpermissions::getPermissonsByNoteId($v);
			 $permiss[$v]=$res;
		}
		
		return $permiss;
		
    }
	
	
	


    
}


?>
