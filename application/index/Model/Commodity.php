<?php
namespace app\index\model;

use think\Model;

class Commodity extends Model
{
    protected $pk = 'cid';

    public function specifications(){
        return $this->hasMany('Specification');
    }
}