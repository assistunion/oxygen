<?

    class Oxygen_Entity extends Oxygen_Object implements ArrayAccess {

        const MISSING_DATA = 'Part of data is missing with key "{0}"';
        const WRONG_ARGUMENT_COUNT = 'Wrong argument count';

        private $original = array();
        private $current = array();
        private $new = false;
        private $owner = null;
        private $mainAlias = '';

        public function getDefaults() {
            return array();
        }

        public function getData() {
            return $this->current;
        }

        public function __submit() {
            $conn = $this->scope->connection;
            $update = array();
            foreach ($this->current as $key => $value) {
                if($this->original[$key] != $value) {
                    $update[$key] = $value;
                }
            }
            $where = array();
            foreach($this->owner->meta['keys'][0] as $c) {
                $where[$c] = $this->original[$c];
            }
            $conn->rawQuery($this->owner->update($update,$where));
            return $conn->lastAffectedRows();
        }

        public function __construct($owner, $original = false) {
            if($original === false) {
                $new = true;
                $original = array();
                $current = $this->getDefaults();
            }
            $this->original = $original;
            $this->current = $original;
            $this->owner = $owner;
            $this->mainAlias = $owner->mainAlias;
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
                    if(!array_key_exists($k,$value)) {
                        throw $this->scope->Exception(
                            Oxygen_Utils_Text::format(self::MISSING_DATA,$k)
                        );
                    }
                    $this->offsetSet($d, $value[$k]);
                }
            }
        }

        public function offsetGet($data) {
            if(is_string($data)) {
                $trial = $this->mainAlias . '.' . $data;
                if(array_key_exists($trial,$this->current)) return $this->current[$trial];
                if(array_key_exists($data,$this->current)) return $this->current[$data];
                throw $this->scope->Exception(
                    Oxygen_Utils_Text::format(self::MISSING_DATA,$data)
                );
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