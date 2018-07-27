<?php
namespace app\api\model;

use think\facade\Cache;


interface ILock
{
    const EXPIRE = 5;
    public static function tryLock($key, $timeout=self::EXPIRE);
    public static function unLock($key);
}

class MessageLock implements ILock { 
	const LOCK_PRE = "message_lock_";
	
	public static function tryLock($mechine_no, $timeout=self::EXPIRE) {
		$key = self::LOCK_PRE . $mechine_no;
		
		$waitime = 20000;
		$totalWaitime = 0;
		$time = $timeout*1000000;
		
		while ($totalWaitime < $time && !Cache::get($key)) 
		{
			Cache::set($key, "LOCK", $timeout);
			return true;

			usleep($waitime);
			$totalWaitime += $waitime;
		}

		if ($totalWaitime >= $time) return false;
	}

	public static function unLock($mechine_no) {
		$key = self::LOCK_PRE . $mechine_no;

		Cache::rm($key);
	}
}

?>

