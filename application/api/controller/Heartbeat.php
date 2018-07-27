<?php
namespace app\api\controller;

use controller\BasicApi;
use service\LogService;
use app\api\service\FileRecordService;
use app\api\model\MessageCache;

class Heartbeat extends BasicApi
{

    public function index()
    {
		$getData = $this->request->get();
		if( isset($getData["mechine_no"]) ) {
			$mechine_no = $getData["mechine_no"];
		} else {
			$this->error("lost param mechine_no", null, 401);
		}
		$msgList = MessageCache::getMessages($mechine_no);
		
		return $this->success("OK", $msgList, 0);
    }

    public function saveFileRecord() {
		$data = FileRecordService::createFileRecord("123", "456", "filePath");
		return implode("_", $data);
    }

    public function queryFileRecord() {
		$where = ["file_no"=>"1"];
		//$where = [];
		$ret = FileRecordService::queryFileRecord($where);
		$str = json_encode($ret);
		return $str;
    }

    public function updateFileRecord() {
		$data = ["upload_time" => date("Y-m-d H:i:s")];

		$ret = FileRecordService::updateFileRecord("1", $data);
		
		if( $ret ) {
			return "OK";
		}
		return "Failed";
    }

}

