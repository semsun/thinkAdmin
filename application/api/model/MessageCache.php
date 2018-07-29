<?php
namespace app\api\model;

use think\facade\Cache;
use app\api\model\CacheLock;

class MessageCache {
	const MACHINE_MESSAGE_PRE = "machine_message_";
	
	public static function setMessage($machine_no, $data) {
		if( CacheLock::tryLock($machine_no) ) {
			$key = self::MACHINE_MESSAGE_PRE . $machine_no;
			$msgList = Cache::get($key);
			if( $msgList ) {
				if( !in_array($data, $msgList) ) {
					array_push($msgList, $data);
				}
			} else {
				$msgList = array($data);
			}
			Cache::set($key, $msgList);

			CacheLock::unLock($machine_no);
		}
	}

	public static function getMessages($machine_no) {
		$key = self::MACHINE_MESSAGE_PRE . $machine_no;
		
		$ret = Cache::get($key);
		if( !$ret ) {
			return [];
		}

		return $ret;
	}
	
	public static function delMessage($machine_no, $data) {
		$key = self::MACHINE_MESSAGE_PRE . $machine_no;
		
		if( CacheLock::tryLock($machine_no) ) {
			$msgList = Cache::get($key);
			$k = array_search($data, $msgList);
			if( isset($msgList[$k]) && $msgList[$k] == $data) {
				unset($msgList[$k]);
			}
			$newMsgList = array_values($msgList);

			Cache::set($key, $newMsgList); 
			CacheLock::unLock($machine_no);
		}
	}

	public static function clearMessage($machine_no) {
		$key = self::MACHINE_MESSAGE_PRE . $machine_no;
		Cache::rm($key);
	}
}

?>
