<?php
namespace app\api\service;

use app\api\model\FileRecordDao;
use app\api\model\Util;
use app\api\model\FileRecordCache;

class FileRecordService {

	const FILE_STATUS_MARK = 1000;
	const FILE_STATUS_UPLOADING = 2000;
	const FILE_STATUS_UPLOADED = 3000;

	const FILE_DELETED = 2;
	const FILE_VAILD = 1;

	public static function createFileRecord($machine_no, $user_no, $file_path) {
		$where = ["machine_no" => $machine_no,
			"user_no" => $user_no,
			"client_path" => $file_path,
			"is_deleted" => self::FILE_VAILD
				];

		$exists = self::queryFileRecord($where);

		if( $exists && count($exists) > 0 ) return null;

		$file_no = $machine_no . "_" . $user_no . date("YmdHis");
		$data = ["id" => Util::create_guid(), 
			"file_no" => $file_no,
			"machine_no" => $machine_no,
			"user_no" => $user_no,
			"client_path" => $file_path,
			"file_status" => "1000",
			"is_deleted" => self::FILE_VAILD,
			"created_time" => date("Y-m-d H:i:s")
				];

		FileRecordDao::save($data);

		FileRecordCache::setFileRecord($file_no, $data);
		return $data;
	}

	public static function updateFileRecord($file_no, $data) {
		$where = ["file_no" => $file_no, "is_deleted" => self::FILE_VAILD];
		
		return FileRecordDao::update($where, $data);
	}
	

	/**
	* 从数据库中查询文件记录
	*/
	public static function queryFileRecord($where) {
		$where["is_deleted"] = self::FILE_VAILD;
		return FileRecordDao::query($where);
	}

	/**
	* 直接从数据库中，根据文件编号查询文件记录
	*/
	public static function queryFileRecordByNo($file_no) {
		$where = ["file_no" => $file_no, "is_deleted" => self::FILE_VAILD];
		$data = FileRecordDao::query($where);

		if( isset($data) && count($data) > 0 ) {
			return $data[0];
		}

		return null;
	}

	/**
	* 根据文件编号获取文件记录，先查询缓存，再查询数据库
	*/
	public static function getFileRecord($file_no) {
		$data = FileRecordCache::getFileRecord($file_no);

		if( !$data || !isset($data["file_no"]) ) {
			$data = self::queryFileRecordByNo($file_no);
			if( !$data ) {
				FileRecordCache::setFileRecord($file_no, $data);
			}
		}

		return $data;
	}

	/**
	* 根据文件编号更新文件记录
	*/
	public static function upateFileRecord($file_no, $data) {
		if( !$file_no && !$data && !isset($data["file_no"]) ) return null;

		if( self::updateFileRecord($file_no, $data) ) {
			FileRecordCache::updateFileRecord($file_no, $data["file_status"], $data["server_path"]);
		}
	}

	/**
	* 根据文件编号删除文件记录
	*/
	public static function delFileRecord($file_no) {
		if( !$file_no && !$data && !isset($data["file_no"]) ) return null;

		$where = ["file_no" => $file_no, "is_deleted" => self::FILE_VAILD];
		$data = ["is_deleted" => self::FILE_DELETED];

		return FileRecordDao::update($file_no, $data);
	}

	/**
	* 创建文件消息
	*/
	public static function createFileMessage($data) {
		$uploadPath = self::getUploadUri() . "machine_no=" . $data["machine_no"] . "&file_no=" . $data["file_no"];
		$msg = ["cmd"=>"101", "file_no"=>$data["file_no"], "file_path"=>$data["client_path"], "upload_path"=>$uploadPath];

		return $msg;
	}

	/**
	* 创建上传文件地址
	*/
	public static function createUploadPath($data) {

	}

    /**
     * 获取服务器URL前缀
     * @return string
     */
    public static function getUploadUri()
    {
    	$uriRoot = self::getRootPath();
        return "{$uriRoot}/api/file_record/uploadfile";
    }

    public static function getRootPath() {
        $appRoot = request()->root(true); // 如果你想获取相对url地址，这里改成 false
        //$uriRoot = preg_match('/\.php$/', $appRoot) ? dirname($appRoot) : $appRoot;

        return $appRoot;
    }

    public static function clearFileCache($file_no) {
    	FileRecordCache::clearFileRecord($file_no);
    }
}
?>
