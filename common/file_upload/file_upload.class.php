<?

	class Oxygen_Common_FileUpload extends Oxygen_Controller {

		public $fileScope;

		public function __construct($fileScope = false) {
            if ($fileScope === false) {
              $this->fileScope = $this;
            } else {
	   		  $this->fileScope = $fileScope;
            }
		}

		public function post() {
    		throw new Exception(print_r($this->scope->POST,true));
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
			$this->flash('Hello');
			return array('hello-from-rpc'=>$arg);
		}

		public function getFileFormats() {

			//$ff = $this->scope->connection['fpngw2d/file_upload_formats'].getData('ff');

			return $this->scope->connection->runQuery(
				'select * from <db>.file_upload_formats',
				array_merge($this->scope->dbParam,array()),
				'id',
				$this, 'makeFileFormat'
			);
		}

		public function getFakeHistory() {
			return json_decode($this->get_('history.json'));
		}

		public function configure($x) {
			$x['{id:int}']->TPRO_File($this->getFakeHistory());
		}


	}


?>