<?php
/**
 * Created by PhpStorm.
 * User: uoouki
 * Date: 2017/1/29
 * Time: 19:23
 */

namespace Uooki\Apacl;


abstract class ResourceAbstract
{
    public $name;
    public $uri;

    abstract protected  function  setName();
    abstract protected  function  getName();


}