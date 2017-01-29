<?php
/**
 * Created by PhpStorm.
 * User: uoouki
 * Date: 2017/1/29
 * Time: 19:56
 */

namespace Uooki\Apacl;


class Role
{
     protected  $name;
     protected  $alisa;
     protected  $description;

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getAlisa()
    {
        return $this->alisa;
    }

    /**
     * @param mixed $alisa
     */
    public function setAlisa($alisa)
    {
        $this->alisa = $alisa;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }


    public function add(){


    }

    public function delete(){


    }

    public function disable(){


    }
}