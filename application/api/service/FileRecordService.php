<?php
namespace app\api\service;

use app\api\model\FileRecordDao;
use app\api\model\Util;

class FileRecordService {
	public static function createFileRecord($machine_no, $user_no, $file_path) {
		$data = ["id" => Util::create_guid(), 
			"file_no" => Util::create_guid(),
			"machine_no" => $machine_no,
			"user_no" => $user_no,
			"client_path" => $file_path,
			"file_status" => "1000",
			"created_time" => date("Y-m-d H:i:s")
				];

		FileRecordDao::save($data);
		return $data;
	}

	public static function updateFileRecord($file_no, $data) {
		$where = ["file_no" => $file_no];
		
		return FileRecordDao::update($where, $data);
	}
	
	public static function queryFileRecord($where) {
		return FileRecordDao::query($where);
	}
}
?>
