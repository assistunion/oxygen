<?

    class Oxygen_Object extends Oxygen_Object_ {

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

        public function __icon() {
            return 'bullet_green';
        }

        public function __iconSource() {
            return OXYGEN_ICONS_URL . '/' . $this->__icon() . '.png';
        }

        public function __defaultView() {
            return 'view';
        }

        public function __toString() {
            return '[Object'.get_class($this).']';
        }

        public static function __oxygen_info(&$info) {
        }
    }

?>