<?

    class Oxygen_Object extends Oxygen_Object_ {

        public $scope = null;

        public function __construct() {
        }

        public function __complete($scope) {
        }

        public function __call($method, $args) {
            $m = $this->__getClass()->__getPublicInstanceMethod($method);
            return $m->invokeArgs($this, $args);
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

        public function __getSemantics() {
            return array('c' => get_class($this), 'd' => $this->__getData(), 'u' => $this->__getUrl());
        }

        public function __getData() {
            return $this->__toString();
        }

        public static function __oxygen_info(&$info) {
        }
    }

?>