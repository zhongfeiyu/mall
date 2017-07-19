<?php
namespace app\index\model;

use think\Model;

class User extends Model
{
    protected $pk = 'uid';

    public function newUser($uname, $password, $phone){
        $salt = strval(rand(100,999));
        $this->save([
            'uname' => $uname,
            'password' => md5($password.$salt),
            'phone' => $phone,
            'salt' => $salt,
        ]);
    }

    public static function checkNameExist($uname){
        $result = self::get(['uname'=>$uname]);
        return isset($result->uid);
    }

    public static function checkPhoneExist($phone){
        $result = self::get(['phone'=> $phone]);
        return isset($result->uid);
    }

    public static function userLogin($uname, $password){
        $userInDb = self::get(['uname'=> $uname]);
        return md5($password.$userInDb->salt) == $userInDb->password;
    }
}