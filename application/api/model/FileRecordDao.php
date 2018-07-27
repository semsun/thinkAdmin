<?php
namespace app\api\model;

use service\DataService;
use think\Db;

class FileRecordDao {
	private static $table = "FileRecord";

	public static function save($data) {
		DataService::save(self::$table, $data, "id");
	}

	public static function query($data) {
		return Db::name(self::$table)->where($data)->select();
	}

	public static function update($where, $data) {
		return Db::name(self::$table)->where($where)->update($data) !== false;
	}
}

?>
