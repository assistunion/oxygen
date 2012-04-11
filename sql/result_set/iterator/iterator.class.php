<?
    class SQL_ResultSet_Iterator extends Oxygen_Object implements Iterator {

        private $sql = "";
        private $wrapper = null;
        private $current = null;
        private $result = null;
        private $n = 0;

        public function __construct($sql, $wrapper) {
            $this->sql = $sql;
            $this->warpper = $wrapper;
        }

        public function current() {
            return $this->current;
        }

        public function next() {
            $object = mysql_fetch_object($this->result);
            if($this->wrapper != Oxygen_SQL::STDCLASS) {
                $this->current = $this->scope->{$this->$wrapper}($object);
            } else {
                $this->current = $object;
            }
            $this->n++;
        }

        public function key() {
            return $this->n;
        }

        public function rewind() {
            if($this->result) mysql_free_result($this->result);
            $this->result = $this->scope->rawQuery($this->sql);
            $this->current = mysql_fetch_object($this->result);
            $this->n = 0;
        }

        public function valid() {
            return !!$this->current;
        }
    }
?>