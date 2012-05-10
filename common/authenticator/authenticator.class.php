<?

	class Oxygen_Common_Authenticator extends Oxygen_Object {

		private $session = null;
		const LOGON_SESSION_PREFIX = 'Logon::';
		private static $defaults = array(
			'message' => 'You are not signed in',
			'login' => false,
			'role' => false,
			'roles' => array(),
			'user' => false
		);

		public function __get($name){
			return $this->session->get(
				self::LOGON_SESSION_PREFIX . $name, 
				self::$defaults[$name]
			);
		}

		public function __complete() {
			$this->session = $this->scope->SESSION;
		}

		public function __set($name, $value) {
			$this->session->put(
				self::LOGON_SESSION_PREFIX . $name, 
				$value
			);
		}

		public function signOut() {
			foreach (self::$defaults as $key => $value) {
				$this->{$key} = $value;
			}
			return '';
		}

		public function getRolesFor($login, $password) {
			switch($login){
				case 'admin': return $password === '#admin#' ? array('admin','user') : array();
				case 'user': return $password === '#user#' ? array('user') : array();
				default:
				 return array();
			}
		}

		public function authenticate($data) {
			$login = $this->login = $data['login'];
			$password = $data['password'];
			$roles = $this->roles = $this->getRolesFor($login, $password);
			if(count($roles)>0) {
				$role = $this->role = $roles[0];
				$this->message = 'Successfully authenticated as ' . $role;
			} else {
				$this->message = 'Try again';
			}
			return '';
		}

		public function process($data) {
			$session = $this->scope->SESSION;
			if($this->role !== false) {
				if(isset($data['sign-out'])) return $this->signOut();
				if(isset($data['change-role'])) return $this->switchRoleTo($data[$role]);
            } else {
				if(isset($data['authenticate'])) return $this->authenticate($data);
			}
			return '';
		}
	}

?>	