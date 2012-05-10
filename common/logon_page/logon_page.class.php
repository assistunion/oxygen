<?

	class Oxygen_Common_LogonPage extends Oxygen_Controller {
		public function configure($x) {
			$x['{url:any}']->LogonPage();
		}
		public function getIcon() {
			return 'key';
		}
		public function __toString() {
			if($this->scope->auth->role) {
				return 'Roles';
			} else {
				return 'Login';
			}
		}
		public function handlePost() {
			return $this->scope->auth->process($this->scope->POST);
		}
	}

?>