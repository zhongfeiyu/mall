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

    public function __construct(){
        header("Access-Control-Allow-Origin:*");
    }

    public function page(){
        $tag1 = input('tag1', '');
        $tag2 = input('tag2', '');
        $page = input('page', 1);
        $page_size = input('page_size', 12)/3;
        if(!empty($tag2)) {
            $result = \think\Db::table('commodity')->where('tag2', $tag2)->page($page, $page_size)->select();
            $total = \think\Db::table('commodity')->where('tag2', $tag2)->count();
        }
        elseif(!empty($tag1)) {
            $result = \think\Db::table('commodity')->where('tag1', $tag1)->page($page, $page_size)->select();
            $total = \think\Db::table('commodity')->where('tag1', $tag1)->count();
        }
        else {
            $result = \think\Db::table('commodity')->page($page, $page_size)->select();
            $total = \think\Db::table('commodity')->count();
        }

        $data = array();
        for($i=0; $i<sizeof($result); $i++) {
            $specifications = array();
            $sResult = SpecificationModel::all(['cid'=>$result[$i]['cid']]);
            foreach($sResult as $v){
                array_push($specifications, [
                    'sid' => $v->sid,
                    'amount' => $v->amount,
                    'price' => $v->price,
                    'specification' => $v->specification,
                ]);
            }
            array_push($data, [
                'cid' => $result[$i]['cid'],
                'cname' => $result[$i]['cname'],
                'description' => $result[$i]['description'],
                'cover' => $result[$i]['cover'],
                'tag1' => $result[$i]['tag1'],
                'tag2' => $result[$i]['tag2'],
                'specifications' => $specifications,
            ]);
        }
        return json_encode(['data' => $data, 'total_page'=>ceil($total/$page_size)]);
    }

    public function search(){
        $search = input('search','');
        $page = input('page', 1);
        $page_size = input('page_size', 12)/3;
        $result = \think\Db::table('commodity')->where('cname', 'like', '%'.$search.'%')->select();
        $data = array();
        for($i=$page_size*($page-1); $i<$page_size*$page; $i++) {
            $specifications = array();
            $sResult = SpecificationModel::all(['cid'=>$result[$i]['cid']]);
            foreach($sResult as $v){
                array_push($specifications, [
                    'sid' => $v->sid,
                    'amount' => $v->amount,
                    'price' => $v->price,
                    'specification' => $v->specification,
                ]);
            }
            array_push($data, [
                'cid' => $result[$i]['cid'],
                'cname' => $result[$i]['cname'],
                'description' => $result[$i]['description'],
                'cover' => $result[$i]['cover'],
                'tag1' => $result[$i]['tag1'],
                'tag2' => $result[$i]['tag2'],
                'specifications' => $specifications,
            ]);
        }
        return json_encode(['data' => $data, 'total_page'=>(sizeof($result)/$page_size)+1]);
    }

}
