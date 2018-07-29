<?php
namespace app\api\controller;

use controller\BasicApi;
use service\LogService;
use service\FileService;
use app\api\service\FileRecordService;
use app\api\model\MessageCache;

class FileRecord extends BasicApi {
	public function markFile() {
		$getData = $this->request->get();
		if( isset($getData["machine_no"]) ) {
			$machine_no = $getData["machine_no"];
		} else {
			$this->error("miss param machine_no", null, 401);
		}

		if( isset($getData["user_no"]) ) {
			$user_no = $getData["user_no"];
		} else {
			$this->error("miss param user_no", null, 402);
		}

		if( isset($getData["file_path"]) ) {
			$file_path = $getData["file_path"];
		} else {
			$this->error("miss param file_path", null, 402);
		}

		$ret = FileRecordService::createFileRecord($machine_no, $user_no, $file_path);

		if( !$ret ) {
			$this->error("File Record was Exists!", [], 30100);
		}

		$rootPath = FileRecordService::getRootPath();
		$downloadPath = "{$rootPath}//api/file_record/downloadFile?file_no={$ret['file_no']}";

		$this->success("Operation Successful", $downloadPath, 0);
	}

	public function uploadFile() {
		$getData = $this->request->get();
		if( isset($getData["machine_no"]) ) {
			$machine_no = $getData["machine_no"];
		} else {
			$this->error("miss param machine_no", null, 30101);
		}

		if( isset($getData["file_no"]) ) {
			$file_no = $getData["file_no"];
		} else {
			$this->error("miss param file_no", null, 30102);
		}

		$file = $this->request->file('file');

		$ext = strtolower(pathinfo($file->getInfo('name'), 4));
        $filename = "$machine_no/$file_no.{$ext}";

        // 文件上传处理
        if (($info = $file->move("static/upload/$machine_no", "$file_no.{$ext}", true))) {
            if (($site_url = FileService::getFileUrl($filename, 'local'))) {
            	/* 删除消息 */
            	$data = FileRecordService::getFileRecord($file_no);
				$msg = FileRecordService::createFileMessage($data);
            	MessageCache::delMessage($data["machine_no"], $msg);

				/* 更新文件状态 */
				$updateData = ["file_status"=>FileRecordService::FILE_STATUS_UPLOADED, 
							"server_path"=>$site_url, 
							"upload_time" => date("Y-m-d H:i:s")];
				FileRecordService::upateFileRecord($file_no, $updateData);

                return $this->success("Upload successful", $site_url, 0);
            }
        }

        $this->error("Upload failed", [], 30103);
	}

	public function checkFile() {
		$getData = $this->request->get();

		if( isset($getData["file_no"]) ) {
			$file_no = $getData["file_no"];
		} else {
			$this->error("miss param file_no", null, 401);
		}

		$data = FileRecordService::getFileRecord($file_no);

		if( !$data ) {
			$this->error("Not found", [], 400);
		}

		if( $data["file_status"] == FileRecordService::FILE_STATUS_UPLOADED ) {
			$url = $data["server_path"];
		} else if ($data["file_status"] == FileRecordService::FILE_STATUS_MARK) {
			/* 添加消息 */
			$msg = FileRecordService::createFileMessage($data);
			MessageCache::setMessage($data["machine_no"], $msg);

			/* 更新文件状态 */
			$updateData = ["file_status"=>FileRecordService::FILE_STATUS_UPLOADING, "server_path"=>null];
			FileRecordService::upateFileRecord($file_no, $updateData);
		}

		if( isset($url) && $url ) {
			return $this->success("Downloadable", $url, 100);
		}

		return $this->success("Uploading", null, 0);

	}

	public function clearCache() {
		$getData = $this->request->get();

		if( isset($getData["file_no"]) ) {
			$file_no = $getData["file_no"];
		} else {
			$this->error("miss param file_no", null, 401);
		}

		FileRecordService::clearFileCache($file_no);

		return $this->success("Clear " . $file_no, 0);
	}

	public function downloadFile() {
		$getData = $this->request->get();

		if( isset($getData["file_no"]) ) {
			$file_no = $getData["file_no"];
			$rootPath = FileRecordService::getRootPath();
			$url = "{$rootPath}/api/file_record/checkfile?file_no={$file_no}";
		} else {
			$url = "error";
		}

		return view("download")->assign(["url"=>$url]);
	}
}
?>