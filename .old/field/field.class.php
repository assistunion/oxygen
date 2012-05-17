<?

    abstract class Oxygen_Field extends Oxygen_Object implements ArrayAccess {

        const DATA = 'data';
        const I18N = 'i18n';

        public $yaml  = null;
        public $owner = '';
        public $name  = '';
        public $data  = '';
        public $i18n  = false;

        public function option($name,$default = null) {
            if(isset($this->yaml[$name])) {
                return $this->yaml[$name];
            } else {
                return $default;
            }
        }

        public function __construct($owner,$name, $yaml) {
            $this->yaml  = $yaml;
            $this->owner = $owner;
            $this->name  = $name;
            $this->data  = $this->option(self::DATA, $name);
            $this->i18n  = $this->option(self::I18N, false);
        }


        private function wrapAll($value) {
            if($this->i18n) {
                $result = array();
                foreach($value as $lang => $v) {
                    $result[$lang] = $this->wrap($v);
                }
            } else {
                $result = $this->wrap($value);
            }
            return $result;
        }

        private function unwrapAll($value) {
            if($this->i18n) {
                $result = array();
                foreach($value as $lang => $v) {
                    $result[$lang] = $this->unwrap($v);
                }
            } else {
                $result = $this->unwrap($value);
            }
            return $result;
        }

        protected function wrap($value) {
            return $value;
        }

        protected function unwrap($value) {
            return $value;
        }

        public function offsetSet($instance, $value) {
            return $instance[$this->data] = $this->unwrapAll($value);
        }

        public function offsetGet($instance) {
            return $this->wrapAll($instance[$this->data]);
        }

        public function offsetUnset($instance) {
            unset($instance[$this->data]);
        }

        public function offsetExists($instance) {
            return isset($instance[$this->data]);
        }

    }


?>