<?php
namespace app\api\model;

use think\facade\Cache;
use app\api\model\CacheLock;
use app\api\model\FileRecordDao;

class FileRecordCache {
	const FILE_RECORD_PRE = "file_record_";
	const CACHE_EXPIRE = 3600;

	public static function getFileRecord($file_no) {
		$key = self::FILE_RECORD_PRE . $file_no;
		$data = Cache::get($key);

		return $data;
	}

	public static function setFileRecord($file_no, $data) {
		$key = self::FILE_RECORD_PRE . $file_no;
		if( CacheLock::tryLock($key) ) {
			Cache::set($key, $data, self::CACHE_EXPIRE);
			CacheLock::unLock($key);
		}
	}

	public static function updateFileRecord($file_no, $status = null, $path = null) {
		$key = self::FILE_RECORD_PRE . $file_no;
		if( CacheLock::tryLock($key) ) {
			$data = self::getFileRecord($file_no);
			if( $status ) {
				$data["file_status"] = $status;
			}
			if( $path ) {
				$data["server_path"] = $path;
			}
			Cache::set($key, $data, self::CACHE_EXPIRE);
			CacheLock::unLock($key);
		}
	}

	public static function clearFileRecord($file_no) {
		$key = self::FILE_RECORD_PRE . $file_no;
		if( CacheLock::tryLock($key) ) {
			Cache::rm($key);
			CacheLock::unLock($key);
		}
	}
}
?>