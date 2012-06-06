<?

	class Oxygen_Common_FileUpload extends Oxygen_Controller {

		public $fileScope;

		public function __construct($fileScope = false) {
            if ($fileScope === false) {
              $this->fileScope = $this;
            } else {
	   		  $this->fileScope = $fileScope;
            }
            $this->hasErrors = false;
		}

		public function post() {
    		try {
    			$format = $this->requireInt('format');
    			$file = $this->requireFile('file');
    			if(!$this->hasErrors){
    				$this->flash("File uploaded");
    			}
    			$this->flash($_FILES,'debug');
    		} catch (Exception $e) {
    			$this->flashError("Upload failed:" . $e->getMessage());
    		}
			return '';
		}

		public function getIcon() {
			return 'folder_go';
		}

		public function __toString() {
			return 'File upload';
		}

		public function makeFileFormat($obj) {
			return $this->scope->{$obj['handler_class']}($obj['handler_args'],$obj['title'],$obj['id']);
		}

		public function rpc_RpcDemo($arg) {
			//throw new Exception('ZZZ');
			$this->flash('Upload message','warning');
			$this->flash('For debug','debug');
			return array('hello-from-rpc'=>$arg);
		}

		public function getFileFormats() {

			$ff = $this->scope->connection['intranet/file_upload_formats'];
			$ff->scope->register('Row','TPRO_File');
			$data = $ff->getData('ff');
			return $data;
		}

		public function getFakeHistory() {
			return json_decode($this->get_('history.json'));
		}

		public function configure($x) {
			$x['{id:int}']->TPRO_File($this->getFakeHistory());
		}


	}


?>