<?php
namespace app\api\controller;

use controller\BasicApi;
use service\LogService;
use app\api\service\FileRecordService;

class Heartbeat extends BasicApi
{

    public function index()
    {
	$value = "123456";
	$value = base64_encode($value);
	$data = ["message" => $value];
	return $this->success("OK", $value, 0);
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

