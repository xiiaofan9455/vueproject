<?php

namespace app\admin\controller;

use think\Request;
use think\DB;
use think\Controller;

class Index extends Controller
{

	public function getAddmin(Request $request)
	{
		$p = DB::table('vue_admin')->where('power', '0')->find();
		if ($_GET['power'] == $p['power']) {
			echo  3;
			die();
		}
		$res = ['name' => $_GET['username'], 'pass' => $_GET['password'], 'power' => $_GET['power']];
		$res['time'] = time();
		$res['pass'] = md5($res['pass']);
		$res = DB::table('vue_admin')->insert($res);
		if ($res) {
			echo 1;
		} else {
			echo 0;
		}
	}
	//查看管理员
	public function getMinshow(Request $request)
	{
		$res = DB::table('vue_admin')->select();
		foreach ($res as $k => $v) {
			$res[$k]['time'] = date('Y-m-d H:i:s', $v['time']);
		}
		echo json_encode($res);
	}

	// 修改状态
	public function getEditsta(Request $request)
	{
		$par = $request->except(["/admin/editsta", 'action']);
		$res['id'] = $par['id'];
		if ($par['status'] == 'true') {
			$res['status'] = 1;
		}
		if ($par['status'] == 'false') {
			$res['status'] = 0;
		}
		$re = DB::table($par['tname'])->update($res);
		dump($re);
		// echo $par['status'];
	}

	//查看类别
	public function getShowtype(Request $request)
	{
		$par = $request->param('id', 0);
		$res = DB::table('vue_type')->where('pid', $par)->select();
		foreach ($res as $k => $v) {
			$row = DB::table('vue_type')->where('pid', $v['id'])->count();
			$res[$k]['num'] = $row;
		}
		echo json_encode($res);
	}

	//修改管理员
	public function getEditmin()
	{
		$id = $_GET['id'];
		$res = DB::table('vue_admin')->where('id', $id)->find();
		echo  json_encode($res);
	}
	//真正修改
	public function getDoeditmin(Request $request)
	{
		// $par=$_GET[''];
		$par = $request->except(["/admin/doeditmin", 'action']);
		$p = DB::table('vue_admin')->where('power', '0')->find();
		if ($p['power'] == $par['power']) {
			echo 3;
			die();
		}
		$res = DB::table('vue_admin')->update($par);
		if ($res) {
			echo 1;
		} else {
			echo 0;
		}
	}

	// 删除管理员的判断
	public function getDelmin(Request $request)
	{
		$id = $request->param('id');
		$re = DB::table('vue_admin')->delete($id);
		if ($re) {
			echo 1;
		} else {
			echo 0;
		}
	}
	//添加类别
	public function getAddtype(Request $request)
	{

		$par = $request->except(["/admin/addtype", 'action']);
		$par['pic'] = trim($par['pic'], '.');
		$par['time'] = time();
		if ($par['pid'] == 0) {
			$par['path'] = '0,';
		} else {
			$p = DB::table('vue_type')->where('id', $par['pid'])->find();
			$par['path'] = $p['path'] . $p['id'] . ',';
		}
		$res = DB::table('vue_type')->insert($par);
		if ($res) {
			echo 1;
		} else {
			echo 0;
		}
	}
	//课程自动加载的方法
	public function getAddcs()
	{
		$res = DB::table('vue_type')->select();
		echo json_encode($res);
	}
	// 添加课程的方法
	public function getDoaddcs(Request $request)
	{
		$par = $request->except(["/admin/doaddcs", 'action']);
		$par['time'] = time();
		$res = DB::table('course')->insert($par);
		if ($res) {
			echo 1;
		} else {
			echo 0;
		}
	}
	//查看课程的方法
	public function getShowcs(Request $request)
	{
		$tname = $request->param('tname');
		$par = DB::table($tname)->select();
		foreach ($par as $k => $v) {
			$par[$k]['time'] = date('Y-m-d H:i:s', $v['time']);
		}
		// echo 111;
		echo json_encode($par);
		// dump($par);
	}
	//删除课程id的方法
	public function getDelcs(Request $request)
	{
		$par = $request->param();
		dump($par);
		$res = DB::table($par['tname'])->delete($par['id']);
		if ($res) {
			echo 1;
		} else {
			echo 0;
		}
	}
	// 优质讲师
	public function getAddch()
	{
		$res = DB::table('vue_type')->where('pid', 0)->select();
		echo json_encode($res);
	}
	// 添加优质讲师
	public function getDoaddch(Request $request)
	{
		$par = $request->except(["/admin/doaddch", 'action']);
		$par['time'] = time();
		$par['pic'] = trim($par['pic'], '.');
		// dump($par);
		$res = DB::table('teacher')->insert($par);
		if ($res) {
			echo 1;
		} else {
			echo 0;
		}
	}
	// 查看优质讲师
	public function getShowch()
	{
		$type = DB::table('teacher')->select();
		foreach ($type as $k => $v) {
			$res = DB::table('vue_type')->where('id', $v['pid'])->find();
			$type[$k]['pid'] = $res['name'];
		}
		echo json_encode($type);
	}

	//添加新闻
	public function getDoaddnews(Request $request)
	{
		$par = $request->except(["/admin/doaddnews", 'action']);
		$par['time'] = time();
		$res = DB::table('news')->insert($par);
		if ($res) {
			echo 1;
		} else {
			echo 0;
		}
	}

	//查看新闻
	public function getShowNews(Request $request)
	{
		$res = DB::table('news')->select();
		foreach ($res as $k => $v) {
			$row = DB::table('vue_type')->where('id', $v['pid'])->find();
			$res[$k]['pid'] = $row['name'];
			$res[$k]['time'] = date('Y-m-d H:i:s', $v['time']);
		}
		echo json_encode($res);
	}

	public function postUploadfe(Request $request)
	{
		// echo json_encode($_FILES);
		// echo json_encode($_POST);


		// die();
		$tmpname = json_encode($_FILES['apic']['tmp_name']);  //1.jpg
		$name = json_encode($_FILES['apic']['name']);  //临时文件
		// echo $_FILES['apic']['tmp_name'];
		// return $tmpname;
		// die();

		// 移动到框架应用根目录/uploads/ 目录下
		$suffix = strrchr($name, '.');  //.jpg
		//重命名
		do {
			$newname = md5(date('Y-m-d H:i:s') . mt_rand(1, 1000) . uniqid()) . $suffix;
		} while (file_exists($newname));
		$newname = substr($newname, 1, 35);
		// return $newname;
		//移动文件
		$info = './static/upload';
		if (!file_exists($info)) {
			mkdir($info, 0777, true);
		}
		if (move_uploaded_file($_FILES['apic']['tmp_name'], $info . '/' . "$newname")) {
			echo $info . "/" . $newname;
		} else {
			echo "失败";
		}
	}
	// 查看用户
	public function getUser(Request $request)
	{
		$par = $request->param('tname');
		$res = DB::table($par)->select();
		foreach ($res as $k => $v) {
			$res[$k]['time'] = date("Y-m-d H:i:s", $v['time']);
		}
		echo json_encode($res);
	}
	//用户详情
	public function getUserinfo(Request $request)
	{
		$par = $request->except(["/admin/userinfo", 'action']);

		$res = DB::table($par['tname'])->where('uid', $par['id'])->select();
		foreach ($res as $k => $v) {
			$res[$k]['time'] = date('Y-m-d H:i:s', $v['time']);
			$row = DB::table('vue_user')->where('id', $v['uid'])->find();
			$res[$k]['email'] = $row['email'];
		}
		echo json_encode($res);
	}
	//查看前台的消息
	public function getConnect()
	{
		$res = DB::table('vue_connect')->select();
		foreach ($res as $k => $v) { {
				$res[$k]['time'] = date('Y-m-d H:i:s', $v['time']);
			}
		}
		echo json_encode($res);
	}

	public function getEditcon(Request $reqeust)
	{
		$id = $reqeust->except(["/admin/editcon", 'action']);
		$id['status'] = 1;
		$res = DB::table('vue_connect')->update($id);
		if ($res) {
			echo 1;
		} else {
			echo 0;
		}
	}

	//类别添加logo
	public function postUploadlog(Request $request)
	{
		// echo json_encode($_FILES);
		// echo json_encode($_POST);


		// die();
		$tmpname = json_encode($_FILES['apic']['tmp_name']);  //1.jpg
		$name = json_encode($_FILES['apic']['name']);  //临时文件
		// echo $_FILES['apic']['tmp_name'];
		// return $tmpname;
		// die();

		// 移动到框架应用根目录/uploads/ 目录下
		$suffix = strrchr($name, '.');  //.jpg
		//重命名
		do {
			$newname = md5(date('Y-m-d H:i:s') . mt_rand(1, 1000) . uniqid()) . $suffix;
		} while (file_exists($newname));
		$newname = substr($newname, 1, 35);
		// return $newname;
		//移动文件
		$info = './static/typelog';
		if (!file_exists($info)) {
			mkdir($info, 0777, true);
		}
		if (move_uploaded_file($_FILES['apic']['tmp_name'], $info . '/' . "$newname")) {
			echo $info . "/" . $newname;
		} else {
			echo "失败";
		}
	}
	//统计
	public function getCount()
	{
		$res['admin'] = DB::table('vue_admin')->count();
		$res['user'] = DB::table('vue_user')->count();
		echo json_encode($res);
	}

	//********************************************后台管理员登录***********************************************
	public function getLogin(Request $reqeust)
	{
		$par = $reqeust->except(["/admin/login", 'action']);
		// dump($par);
		// die();
		$par['pass'] = md5($par['pass']);
		$res = DB::table('vue_admin')->where('name', $par['name'])->find();
		if ($res) {
			if (!$res['status']) {
				echo 0; //被禁用
			}
			if ($par['pass'] == $res['pass']) {
				// echo $res['power'];
				$res['endtime'] = date('Y-m-d H:i:s', $res['endtime']);
				echo json_encode($res);
				$r['endtime'] = time();
				$r['id'] = $res['id'];
				DB::table('vue_admin')->update($r);
			} else {
				echo 2; //密码不正确
			}
		} else {
			echo 4;  //没有该管理员
		}
	}
	// 帖子详情
	public function getCard(Request  $request)
	{
		$id = $request->param('');
		// dump($id);
		$res = DB::table($id['tname'])->where('id', $id['id'])->select();
		foreach ($res as $k => $v) {
			$p = DB::table('vue_reply')->where('pid', $v['id'])->select();
			// dump($p);
			$res[$k]['hinfo'] = $p;
			// $res[$k]['hinfo']=$p;

		}
		echo json_encode($res);
	}

	public function getEditcard(Request $request)
	{
		$par = $request->param();
		// dump($par);
		//走删除分支
		if (isset($par['par'])) {
			DB::table('posts')->delete($par['id']);
			$res = DB::table('vue_reply')->where('pid', $par['id'])->select();
			foreach ($res as $k => $v) {
				DB::table('vue_reply')->delete($v['id']);
			}
		} else {
			$r = DB::table('posts')->where('id', $par['id'])->find();
			$p['id'] = $par['id'];
			$p['status'] = !$r['status'];
			DB::table('posts')->update($p);
			$re = DB::table('posts')->where('id', $par['id'])->find();
			echo $re['status'];
		}
	}

	// 公共删除的方法
	public function getPublicdel(Request $request)
	{
		$id = $request->param();
		dump($id);
		$res = DB::table($id['tname'])->delete($id['id']);
		echo $res;
	}

	//置顶
	public function getEdittop(Request $request)
	{
		$id = $request->param('id');
			$r = DB::table('posts')->where('id',$id)->find();
			if($r['level']){
				$p['level'] = null;
				$p['id'] = $id;
				$res = DB::table('posts')->update($p);
				echo 0;
			}else{
				$p['id'] = $id;
				$p['level']='1';
				$res = DB::table('posts')->update($p);
				echo 1;
			}				
		
	}
}
