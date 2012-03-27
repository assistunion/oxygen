<?

    class Oxygen_Model extends Oxygen_Object implements ArrayAccess {

        const MISSING_DATA = 'Part of data is missing with key "{0}"';
        const WRONG_ARGUMENT_COUNT = 'Wrong argument count';

        private $original = array();
        private $current = array();
        private $new = false;
        
        public function getDefaults() {
            return array();
        }
        
        public function __submit() {
        }

        public function __construct($original = false) {
            parent::__construct();
            if($original === false) {
                $new = true;
                $original = array();
                $current = $this->getDefaults();
            }
            $this->original = $original;
            $this->current = $original;
        }

        public function offsetExists($data) {
            if(is_string($data)) {
                return isset($this->current[$data]);
            } else if(is_array($data)) {
                foreach($data as $d) {
                    if(!$this->offsetExists($d)) {
                        return false;
                    }
                }
                return true;
            } else {
                return false;
            }
        }

        public function offsetUnset($data) {
            
            if(is_string($data)) {
                unset($this->current[$data]);
            } else if(is_array($data)) {
                foreach($data as $d) {
                    $this->offsetUnset($d);
                }
            }
        }

        public function offsetSet($data,$value) {
            if(is_string($data)){
                $this->current[$data] = $value;
            } else if(is_array($data)) {
                foreach($data as $k => $d) {
                    if(!isset($value[$k])) {
                        $this->throwException(
                            Oxygen_Utils_Text::format(self::MISSING_DATA,$k)
                        );
                    }
                    $this->offsetSet($d, $value[$k]);
                }
            }
        }

        public function offsetGet($data) {
            if(is_string($data)) {
                if(!isset($this->current[$data])) {
                    $this->throwException(
                        Oxygen_Utils_Text::format(self::MISSING_DATA,$k)
                    );
                }
                $result = $this->current[$data];
            } else if(is_array($data)) {
                $result = array();
                foreach($data as $k => $d) {
                    $result[$k] = $this->offsetGet($d);
                }
            }
            return $result;
        }
    }



?>