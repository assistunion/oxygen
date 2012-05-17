<?
	class Oxygen_Common_FileUpload extends Oxygen_Common_Controller {
		
		public function getFiles() {
			return $this->model->getFiles();
		}

		public function handlePost() {
			return '';
		}
	}
?>