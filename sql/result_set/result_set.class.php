<?
    class SQL_ResultSet extends Oxygen_Object implements IteratorAggregate, Countable, ArrayAccess {

        const COUNT_IS_NOT_KNOWN = -1;

        private $sql     = '';
        private $wrapper = '';
        private $count   = self::COUNT_IS_NOT_KNOWN;

        public function __construct($sql,$wrapper) {
            parent::__construct();
            $this->wrapper = $wrapper;
            $this->sql = $sql;
        }

        public function getIterator() {
            return $this->scope->SQL_ResultSet_Iterator($this->sql,$this->wrapper);
        }

        public function count() {
            if($this->count == self::COUNT_IS_NOT_KNOWN) {
                $this->count = $this->scope->query("valueof count(*) from ({$this->sql}) as _");
            }
            return $this->count;
        }

        public function offsetExists($offset) {
            return ($offset >= 0) && (offset <= count($this));
        }

        public function offsetGet($offset) {
            return $this->scope->query("get * from ({$this->sql}) as _ limit 1 offset {$offset}");
        }

        private function throwReadOnly() {
            throw $this->scope->SQL_Excpetion(get_class($this) . " is read only");
        }

        public function offsetSet($offset, $value) {
            $this->throwReadOnly();
        }

        public function offsetUnset($offset) {
            $this->trhowReadOnly();
        }
    }
?>