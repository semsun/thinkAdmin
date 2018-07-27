<?php
namespace app\api\model;

use think\facade\Cache;
use app\api\model\MessageLock;

class MessageCache {
	const MACHINE_MESSAGE_PRE = "machine_message_";
	
	public static function setMessage($mechine_no, $data) {
		if( MessageLock::tryLock($mechine_no) ) {
			$key = self::MACHINE_MESSAGE_PRE . $mechine_no;
			$msgList = Cache::get($key);
			if( $msgList ) {
				if( !in_array($data, $msgList) ) {
					array_push($msgList, $data);
				}
			} else {
				$msgList = array($data);
			}
			Cache::set($key, $msgList);

			MessageLock::unLock($mechine_no);
		}
	}

	public static function getMessages($mechine_no) {
		$key = self::MACHINE_MESSAGE_PRE . $mechine_no;
		
		$ret = Cache::get($key);
		if( !$ret ) {
			return [];
		}

		return $ret;
	}
	
	public static function delMessage($mechine_no, $data) {
		$key = self::MACHINE_MESSAGE_PRE . $mechine_no;
		
		if( MessageLock::tryLock($mechine_no) ) {
			$msgList = Cache::get($key);
			$k = array_search($data, $msgList);
			if( $msgList[$k] == $data) {
				unset($msgList[$k]);
			}
			$newMsgList = array_values($msgList);

			Cache::set($key, $newMsgList); 
			MessageLock::unLock($mechine_no);
		}
	}

	public static function clearMessage($mechine_no) {
		$key = self::MACHINE_MESSAGE_PRE . $mechine_no;
		Cache::rm($key);
	}
}

?>
