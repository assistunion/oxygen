<?

    class Oxygen_SQL_Table extends Oxygen_Controller {
        private $db = null;
        public function __construct($table, $db) {
            parent::__construct($table);
            $this->db = $db;
        }
        public function configure($x){

        }
    }


?>