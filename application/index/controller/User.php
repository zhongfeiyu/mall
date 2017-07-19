<?php
namespace app\index\controller;

use think\View;
use think\Controller;
use app\index\model\User as UserModel;
use app\index\model\Cart as CartModel;
use app\index\model\Commodity as CommodityModel;
use app\index\model\Specification as SpecificationModel;

class User extends Controller
{
    public function __construct(){
        header("Access-Control-Allow-Origin:*");
    }

    public function logout(){
        session('uid', '');
        return ['success' => 1];
    }

    public function registerSubmit(){
        $uname = input('uname','');
        $phone = input('phone','');
        $password = input('password','');

        if(empty($uname)) return ['success' => 0, 'err_msg' => '缺少用户名！'];
        if(empty($phone)) return ['success' => 0, 'err_msg' => '缺少电话号码！'];
        if(empty($password)) return ['success' => 0, 'err_msg' => '缺少密码！'];
        if(strlen($uname) > 20) return ['success' => 0, 'err_msg' => '用户名过长！'];
        if(strlen($phone) > 25) return ['success' => 0, 'err_msg' => '电话号过长！'];
        if(strlen($password) > 30) return ['success' => 0, 'err_msg' => '密码过长！'];

        if(UserModel::checkNameExist($uname)) return ['success' => 0, 'err_msg' => '用户名已被注册！'];
        if(UserModel::checkPhoneExist($phone)) return ['success' => 0, 'err_msg' => '电话号码已被注册！'];

        (new UserModel())->newUser($uname, $password, $phone);
        return ['success' => 1];
    }

    public function loginSubmit(){
        $uname = input('uname','');
        $password = input('password','');

        if(empty($uname)) return ['success' => 0, 'err_msg' => '缺少用户名！'];
        if(empty($password)) return ['success' => 0, 'err_msg' => '缺少密码！'];

        if(!UserModel::userLogin($uname, $password)) return ['success' => 0, 'err_msg' => '用户名或密码错误！'];
        else {
            $user = UserModel::get(['uname' => $uname]);
            session('uid', $user->uid);
            session('uname', $user->uname);
            return ['success' => 1];
        }
    }

    public function cart(){
        $user = input('user', '');
        if(empty($user)) return ['success' => 0, 'err_msg' => '缺少user'];
        $sm = new SpecificationModel();
        $cm = new CommodityModel();
        $cart = array();
        if(($u=UserModel::get(['uname'=>$user])) == null) return ['success' => 0, 'err_msg' => 'user不存在'];
        $uid = $u->uid;
        foreach(CartModel::all(['uid' => $uid]) as $value){
            $specification = $sm->get(['sid' => $value->sid]);
            $commodity = $cm->get(['cid' => $specification->cid]);
            array_push($cart, [
                'amount' => $value->amount,
                'sid' => $specification->sid,
                'cid' => $specification->cid,
                'price' => $specification->price,
                'specification' => $specification->specification,
                'cname' => $commodity->cname,
                'description' => $commodity->description,
                'cover' => $commodity->cover,
                'tag1' => $commodity->tag1,
                'tag2' => $commodity->tag2,
            ]);
        }
        return $cart;
    }

    public function cartIncre(){
        $user = input('user', '');

        $sid = input('sid', '');
        $iAmount = input('iAmount', '');

        if(empty($user)) return ['success' => 0, 'err_msg' => '缺少user'];
        if(empty($iAmount)) return ['success' => 0, 'err_msg' => '缺少iAmount'];
        if(is_int($sid)) return ['success' => 0, 'err_msg' => 'sid不是整数！'];
        if(is_int($iAmount)) return ['success' => 0, 'err_msg' => 'iAmount不是整数！'];

        if(($u=UserModel::get(['uname'=>$user])) == null) return ['success' => 0, 'err_msg' => 'user不存在'];
        $uid = $u->uid;
        $cm = new CartModel();
        $sm = new SpecificationModel();
        $c = $cm->get(['uid' => $uid, 'sid' => $sid]);
        $nowAmount = $c==null ? 0 : $c->amount;
        $totalAmount = intval($sm->get(['sid' => $sid])->amount);

        if($nowAmount + $iAmount > $totalAmount) return ['success' => 0, 'err_msg' => '超过该商品总数！'];
        if($c != null) $cm->where(['uid' => $uid, 'sid' => $sid])->update(['amount' => $nowAmount + $iAmount]);
        else $cm->save(['uid' => $uid, 'sid' => $sid, 'amount'=>$iAmount]);
        return ['success' => 1];
    }

    public function cartDecre(){
        $user = input('user', '');

        $sid = input('sid', '');
        $dAmount = input('dAmount', '');

        if(empty($user)) return ['success' => 0, 'err_msg' => '缺少user'];
        if(empty($sid)) return ['success' => 0, 'err_msg' => '缺少sid'];
        if(empty($dAmount)) return ['success' => 0, 'err_msg' => '缺少dAmount'];
        if(is_int($sid)) return ['success' => 0, 'err_msg' => 'sid不是整数！'];
        if(is_int($dAmount)) return ['success' => 0, 'err_msg' => 'dAmount不是整数！'];

        if(($u=UserModel::get(['uname'=>$user])) == null) return ['success' => 0, 'err_msg' => 'user不存在'];
        $uid = $u->uid;
        $cm = new CartModel();
        $c = $cm->get(['uid' => $uid, 'sid' => $sid]);
        $nowAmount = $c==null ? 0 : $c->amount;

        if($nowAmount - $dAmount < 0) return ['success' => 0, 'err_msg' => '该商品总数将为负！'];
        $cm->where(['uid' => $uid, 'sid' => $sid])->update(['amount' => $nowAmount - $dAmount]);
        return ['success' => 1];
    }

    public function cartClear(){
        $user = input('user', '');
        if(empty($user)) return ['success' => 0, 'err_msg' => '缺少user'];
        if(($u=UserModel::get(['uname'=>$user])) == null) return ['success' => 0, 'err_msg' => 'user不存在'];
        $uid = $u->uid;
        CartModel::destroy(['uid' => $uid]);
        return ['success' => 1];
    }
}
