<?php

namespace app\index\controller;

use think\DB;
use think\Request;
use think\Controller;
use think\facade\Cookie;
use think\facade\Session;

class Index extends Controller
{
    public function index()
    {
        Cookie::set("unum", 1);

        $type = DB::table('vue_type')->where('pid', 0)->order("click", "desc")->paginate(8);
        $retime = DB::table('vue_type')->where('pid', 0)->order('time', 'desc')->paginate(4);
        return view('/layout/public', ['type' => $type, 'ret' => $retime]);
    }
    //查看子类
    public function getList(Request $request)
    {
        $get = $request->param('id');
        $p = DB::table('vue_type')->where("id={$get} or path like '%{$get}%' ")->select();
        // dump($p);
        $str = '';
        foreach ($p as $k => $v) {
            $str .= $v['id'] . ',';
        }
        $str = '(' . trim($str, ',') . ')';
        // dump($str);
        $res = DB::table('vue_type')->where("id in {$str}")->where('status', 1)->find();
        $course = DB::table('course')->where("cid in {$str}")->where('status', 1)->select();
        $counum = DB::table('course')->where("cid in {$str}")->where('status', 1)->count();
        $tea = DB::table('teacher')->where("pid in {$str}")->where('status', 1)->select();
        $news = DB::table('news')->where("pid in {$str}")->where('status', 1)->select();
        $type = DB::table('vue_type')->paginate(4);
        return view('/frontDesk/show', ['res' => $res, 'cour' => $course, 'tea' => $tea, 'counum' => $counum, 'type' => $type, 'news' => $news]);
    }
    //全部课程
    public function getAll(Request  $request)
    {
        $par = $request->param();
        // $row=DB::table('')
    }

    //注册
    public function postLogin(Request $request)
    {
        $par = $request->except(["/index/login", 'action']);
        $par['password'] = md5($par['password']);
        $par['time'] = time();
        $res = DB::table('vue_user')->insert($par);
        if ($res) {
            return $this->success('注册成功');
        } else {
            return $this->error('注册失败');
        }
    }
    // 登录
    public function postEnroll(Request $request)
    {



        $par = $request->param();
        $par['password'] = md5($par['password']);
        $res = DB::table('vue_user')->where('name', $par['name'])->find();
        if ($res) {
            if (!$res['status']) {
                return $this->error('账号异常');
            }
            if ($par['password'] === $res['password']) {
                Session::set('uid', $res['id']);
                Session::set('user', $res['name']);
                return  $this->redirect('/');
            } else {
                return $this->error('密码不正确');
            }
        } else {
            return $this->error('用户名不存在');
        }
    }

    //退出登录
    public function getLogout()
    {
        Session::delete('user');
        return $this->success('退出成功');
    }

    //讨论区
    public function getDiscuss(Request $request)
    {
        $desc = $request->param('desc', 'asc');
        $view = $request->param('view', 'asc');
        $can = $request->param();
        $type = DB::table('vue_type')->paginate(4);
        if ($view == "desc") {
            $res = DB::table('posts')->where("status=1")->order('view', $view)->paginate(6);
        } else if ($desc == "desc") {
            $res = DB::table('posts')->where("status=1")->order('time', $desc)->paginate(6);
        } else {
            $res = DB::table('posts')->where("status=1")->order('level', 'desc')->paginate(6);
        }
        // dump($res);
        // die();
        return view('/frontDesk/discuss', ['res' => $res, 'can' => $can, 'type' => $type]);
    }

    //提交帖子
    public function postSubdis(Request $request)
    {
        $par = $request->except(["/index/subdis", 'action']);
        $par['time'] = time();

        if (isset($par['name'])) {
        } else {
            $par['name'] = Session::get('user');
            $par['uid'] = Session::get('uid');
        }
        $res = DB::table('posts')->insert($par);
        if ($res) {
            return $this->success('发帖成功');
        } else {
            return $this->error('发帖失败');
        }
    }

    //查看指定帖子
    public function getShowdisu(Request $request)
    {
        $par = $request->param('id');
        $res = DB::table('posts')->where('id', $par)->find();
        $row = DB::table('vue_reply')->where("status=1 and pid={$res['id']}")->select();
        $r = DB::table('vue_onreply')->select();
        $likes = DB::table('vue_likes')->where("carid", $par)->select();
        $arr = ["超管", "二级管理员", "三级管理员"];
        foreach ($row as $k => $v) {
            if ($v['level'] != NULL) {
                if ($v['level'] == "0") {
                    $row[$k]['level'] = "超管";
                } else if ($v['level'] == "1") {
                    $row[$k]['level'] = "一级管理员";
                } else if ($v['level'] == "2") {
                    $row[$k]['level'] = "二级管理员";
                }
            }
            $like = DB::table('vue_likes')->where("wid", $v['id'])->where('likes', 1)->count();
            $li = DB::table("vue_likes")->where("wid", $v['id'])->where('tread', 1)->count();
            $wid = DB::table("vue_likes")->where('carid', $par)->find();
            $row[$k]['like'] = $like;
            $row[$k]['cai'] = $li;
            $row[$k]['uid'] = $wid['uid'];
            $row[$k]['likes'] = $wid['likes'];
            $row[$k]['tread'] = $wid['tread'];
            //  dump($wid);
        }



        return view('/frontDesk/showdis', ['likes' => $likes, 'res' => $res, 'row' => $row, 'r' => $r, 'arr' => $arr]);
    }
    // 添加回复
    public function postSubreply(Request $request)
    {
        if (Session::has('user')) {
            $par = $request->except(["/index/subreply", 'action', 'tid']);
        } else {
            $par = $request->except(["/index/subreply", 'action']);
        }
        $ra = $request->param('tid');
        if ($ra != '0' && Session::has('user')) {
            $pa['time'] = time();
            $pa['pid'] = $ra;
            $pa['hname'] = Session::get('user');
            $pa['cont'] = $par['cont'];
            $r = DB::table('vue_onreply')->insert($pa);
            if ($r) {
                return $this->success('回复成功');
            } else {
                return $this->error('回复失败');
            }
        }

        $par['time'] = time();
        if (Session::has('user')) {
            $par['name'] = Session::get('user');
        }
        $res = DB::table('vue_reply')->insert($par);
        $row['endname'] = $par['name'];
        $row['id'] = $par['pid'];
        if ($res) {
            DB::query("update posts set reply=reply+1 where id={$par['pid']}");
            DB::table('posts')->update($row);
            return $this->success('回复成功');
        } else {
            return $this->error('回复失败');
        }
    }

    //ajax增加浏览量
    public function getVienum(Request $request)
    {
        $id = $request->param('id');
        DB::query("update posts set view=view+1 where id={$id}");
    }
    //联系我们
    public function getConnect()
    {
        return view('/frontDesk/connect');
    }
    //提交的联系我们
    public function postAddcon(Request $request)
    {
        $par = $request->except(["/index/addcon", 'action']);
        $par['time'] = time();
        $res = DB::table('vue_connect')->insert($par);
        if ($res) {
            return $this->success('发送成功');
        } else {
            return $this->error('发送失败');
        }
    }

    //路径
    public function getPaths()
    {
        $type = DB::table('vue_type')->paginate(4);
        $res = DB::table('vue_type')->where("status=1")->select();
        foreach ($res as $k => $v) {
            $counum = DB::table('course')->where("cid", $v['id'])->where('status', 1)->count();
            $res[$k]['num'] = $counum;
        }
        return view('/frontDesk/paths', ['res' => $res, 'type' => $type]);
    }

    public function getClick(Request $request)
    {
        $id = $request->param('id');
        $unum = Cookie::get("unum");
        $num = [];
        array_push($num, $id);
        $id = $request->param('id');
        // dump($id);
        DB::query("update vue_type set `click`=click+1 where id={$id}");
        Cookie::set("unum", 2);
        $res = DB::table('vue_type')->where("id", $id)->find();;
        echo  $res['click'];
    }


    public function getTables()
    {
        $res = DB::table('posts')
            ->alias('p')
            ->join('vue_reply i', 'p.id=i.pid')
            ->field('i.sname,i.scont,i.stime')
            ->select();
        dump($res);

        //多表联查
        // public function blogs()
        // {
        // //以blogs为主表
        // $res = Db::name('blogs')
        // ->alias("a") //取一个别名
        // //与category表进行关联，取名i，并且a表的categoryid字段等于category表的id字段
        // ->join('category i', 'a.categoryid = i.id')
        // ->join('user u', 'a.authorid = u.id')
        // //想要的字段
        // ->field('a.id,a.title,a.content,u.username,a.createtime,i.category,a.look,a.like')
        // //查询
        // ->select();
        // return json($res);
        // }
        // }
    }
    public function getLikes(Request $request)
    {
        $par = $request->except(["action", "/index/likes"]);
        $par['uid'] = Session::get("uid");
        $par['likes'] = 1;

        $like = DB::table("vue_likes")->where("wid={$par['wid']} and uid={$par['uid']}")->find();
        if ($like) {
            if ($like['tread']) {
                $p['tread'] = 0;
            }
            $p['likes'] = !$like['likes'];
            $p['id'] = $like['id'];
            $res = DB::table('vue_likes')->update($p);
        } else {
            $res = DB::table("vue_likes")->insert($par);
        }
        $lik = DB::table("vue_likes")->where("wid={$par['wid']} and uid={$par['uid']}")->find();
        $r = DB::table("vue_likes")->where("wid={$par['wid']} and likes=1")->count();
        $r1 = DB::table("vue_likes")->where("wid={$par['wid']} and tread=1")->count();
        $lik['num'] = $r;
        $lik['tread'] = $r1;

        echo json_encode($lik);

        // dump($lik);

    }
    public function getTread(Request $request)
    {
        $par = $request->only(['wid']);
        $par['uid'] = Session::get("uid");
        $par['tread'] = 1;
        $like = DB::table("vue_likes")->where("wid={$par['wid']} and uid={$par['uid']}")->find();
        if ($like) {
            if ($like['likes']) {
                $pa['likes'] = 0;
            }
            $pa['tread'] = !$like['tread'];
            $pa['id'] = $like['id'];
            DB::table('vue_likes')->update($pa);
        } else {
            $res = DB::table("vue_likes")->insert($par);
        }
        $lik = DB::table("vue_likes")->where("wid={$par['wid']} and uid={$par['uid']}")->find();
        $r = DB::table("vue_likes")->where("wid={$par['wid']} and tread=1")->count();
        $r1 = DB::table("vue_likes")->where("wid={$par['wid']} and likes=1")->count();
        $lik['num'] = $r;
        $lik['likes'] = $r1;
        echo json_encode($lik);
    }
}
