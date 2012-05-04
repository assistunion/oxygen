<?
    class SQL_ResultSet extends Oxygen_Object implements IteratorAggregate {

        const COUNT_IS_NOT_KNOWN = -1;

        public $sql     = '';
        public $wrapper = false;
        public $key     = false;

        public function __construct($sql, $key, $wrapper) {
            $this->wrapper = $wrapper;
            $this->sql     = $sql;
            $this->key     = $key;
        }

        public function getIterator() {
            return $this->new_DataIterator($this);
        }

    }
?>