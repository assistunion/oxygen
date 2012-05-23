<?

	class Oxygen_Common_Application extends Oxygen_ScopeController {

        public $auth;
        public $config;

        public function __complete() {
            $this->auth = $this->scope->__authenticated();
        }

        public function rpc_clearFlash() {
            $this->scope->SESSION['oxygen-flash-messages'] = array();
        }
        
        public function rpc_getFlash(){
            return $this->scope->SESSION->get('oxygen-flash-messages',array());
        }

		public function configure($x) {
			$x['public']->Dummy('Public page');
            switch($this->auth->role) {
            case 'admin':
    			$x['files']->Dummy('Files','folder_explore');				
            case 'user':
      			$x['users']->Dummy('Users','user');
            default:
        		$x['{url:any}']->LogonPage('login');
			}
		}
	}

?>