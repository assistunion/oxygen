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

        public function __getIconName() {
            return 'bullet_green';
        }

        public function __getIconSource() {
            return OXYGEN_ICONS_URL . '/' . $this->__getIconName() . '.png';
        }

        public function put() {
            $this->put_view();
        }

        public function get() {
            return $this->get_view();
        }

        public function __getTitle() {
            return '[Object'.get_class($this).']';
        }

        public function __getDescription() {
            return $this->scope->og->description;
        }

        public function __getImageSource() {
            return $this->scope->og->image;
        }

        public function __getKeywords() {
            return $this->scope->og->keywords;
        }

        public function __getUrl() {
            return OXYGEN_ROOT_URL;
        }

        public function __toString() {
            return htmlspecialchars(_($this->__getTitle()));
        }

        public function __lastModified() {
            return time();
        }

        public static function __oxygen_info(&$info) {
        }
    }

?>