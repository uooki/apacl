<?php
/**
 * Created by PhpStorm.
 * User: uoouki
 * Date: 2017/1/29
 * Time: 17:12
 */
namespace Uooki\Apacl;


class Apacl
{

    protected  $acl;
    protected  $user;
    protected  $role;


    public function __construct($config,$target=null){
        // init acl to memory

        // role ,user Ӧ������Ϊ��Ŀ��һ���ֱȽϺ� ��usercontroller usermodule  usersystem
        // ��������ʵ�� aclist , ����user ������� ��Ȼ�����������acl   

    }


    public  function  acl(){

         return $this->acl;
    }

    public function check($resource,$target=null){

         if($target!=$this->user){
              return false;
         }

         if(in_array($resource,$this->acl)){
             return true;
         }else{
             return false;
         }

    }

    public function  allow($resource,$target=null){


    }

    public function unallow($resource,$target=null){


    }
}