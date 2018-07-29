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
		if( isset($getData["machine_no"]) ) {
			$machine_no = $getData["machine_no"];
		} else {
			$this->error("lost param machine_no", null, 401);
		}
		$msgList = MessageCache::getMessages($machine_no);

		$code = 0;
		if( $msgList && count($msgList) > 0 ) {
			$code = 100;
		}

		return $this->success("Beat", $msgList, $code);
    }

}

