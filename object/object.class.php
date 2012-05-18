<?

	class Oxygen_Object {
		
		public $scope = null;

		public function __construct() {
		}

		public function __complete($scope) {
		}

    	public function __call($method, $args) {
        	if(method_exists($this, $method)) {
          		return call_user_func_array(array($this, $method), $args);
        	} else {
          		throw $this->scope->Exception("Method {$method} does not exist");
          	}
        }

        public static function __oxygen_info(&$info) {
        }
	}

?>