<?php
namespace app\index\controller;

use think\View;
use think\Controller;
use app\index\model\User as UserModel;
use app\index\model\Cart as CartModel;
use app\index\model\Commodity as CommodityModel;
use app\index\model\Specification as SpecificationModel;

class Index extends Controller
{
    public function index(){
	echo $_SERVER['PATH_INFO'];
   }
    public function page(){
        $tag1 = input('tag1', '');
        $tag2 = input('tag2', '');
        if(!empty($tag2)) $result = CommodityModel::all(['tag2' => $tag2]);
        elseif(!empty($tag1)) $result = CommodityModel::all(['tag1' => $tag1]);
        else $result = CommodityModel::all();
        $data = array();
        foreach($result as $value){
            $specifications = array();
            $sResult = SpecificationModel::all(['cid'=>$value->cid]);
            foreach($sResult as $v){
                array_push($specifications, [
                    'sid' => $v->sid,
                    'amount' => $v->amount,
                    'price' => $v->price,
                    'specification' => $v->specification,
                ]);
            }
            array_push($data, [
                'cid' => $value->cid,
                'cname' => $value->cname,
                'description' => $value->description,
                'cover' => $value->cover,
                'tag1' => $value->tag1,
                'tag2' => $value->tag2,
                'specifications' => $specifications,
            ]);
        }
        return json_encode($data);
    }

}
