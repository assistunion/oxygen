<?

	class Oxygen_AuthController extends Oxygen_Controller {


		public function __complete() {
			$this->authData = $this->authenticated();
		}

		public final function configure($x) {
		}
	}


?>