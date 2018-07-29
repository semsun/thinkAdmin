<?php
namespace app\api\controller;

use controller\BasicApi;
use service\LogService;
use think\facade\Cache;
use app\api\model\MessageCache;

class Index extends BasicApi
{

    public function index()
    {
		Cache::set("no_set", "test");
		Cache::rm("no_set");
		if( !Cache::get("no_set") ) $str = 1;
		else $str = 0;
		return $this->success("OK", $str);
    }

    public function messages() {
		$getData = $this->request->get();
		if( isset($getData["machine_no"]) ) {
			$machine_no = $getData["machine_no"];
		} else {
			$this->error("lost param machine_no", null, 401);
		}
		$msgList = MessageCache::getMessages($machine_no);
		
		$ret = json_encode($msgList);
		return $ret;
    }

    public function setMessage() {
		$getData = $this->request->get();
		if( isset($getData["machine_no"]) ) {
			$machine_no = $getData["machine_no"];
		} else {
			$this->error("lost param machine_no", null, 401);
		}

		if( isset($getData["msg"]) ) {
			$data = ["message"=>$getData["msg"]];
			MessageCache::setMessage($machine_no, $data);
		}

		return $this->success("OK");
    }

    public function delMessage() {
		$getData = $this->request->get();
		if( isset($getData["machine_no"]) ) {
			$machine_no = $getData["machine_no"];
		} else {
			$this->error("lost param machine_no", null, 401);
		}

		if( isset($getData["msg"]) ) {
			$data = ["message"=>$getData["msg"]];
			MessageCache::delMessage($machine_no, $data);
		}

		return $this->success("OK");
    }

    public function clearMessages() {
		$getData = $this->request->get();
		if( isset($getData["machine_no"]) ) {
			$machine_no = $getData["machine_no"];
		} else {
			$this->error("lost param machine_no", null, 401);
		}

		MessageCache::clearMessage($machine_no);

		return $this->success("OK");
    }
}

