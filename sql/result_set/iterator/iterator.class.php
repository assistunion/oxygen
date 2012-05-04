<?
    class SQL_ResultSet_Iterator extends Oxygen_Object implements Iterator {

        private $current    = null;
        private $data       = null;
        private $result     = null;
        private $connection = null;
        private $sql        = '';
        private $wrapper    = false;
        private $key        = false;
        private $n          = 0;

        public function __construct($sql, $wrapper, $key) {
            $this->wrapper = $wrapper;
            $this->sql     = $sql;
            $this->key     = $key;
            $this->connection = $this->SCOPE_CONNECTION;
        }

        public function current() {
            return $this->current;
        }

        public function next() {
            if ($this->data = mysql_fetch_assoc($this->result)) {
                $this->current = $this->connection->wrapData(
                    $this->data,
                    $this->wrapper
                );
            }
            $this->n++;
        }

        public function key() {
            if ($this->key === false) return $this->n;
            if (is_string($this->key)) return $this->data[$this->key];
            else {
                $res = array();
                foreach ($this->key as $key) {
                    $res[$key] = $this->data[$key];
                }
                return $res;
            }
        }

        public function rewind() {
            if($this->result) mysql_free_result($this->result);
            $this->result = $this->connection->rawQuery($this->sql);
            $this->n = -1;
            next();
        }

        public function valid() {
            return !!$this->data;
        }
    }
?>