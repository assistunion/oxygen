<?

    class Oxygen_SQL_Table extends Oxygen_ScopeController {

    	public $connection = null;
    	public $database = null;

    	public $data = array();
        public $meta = array();

        public $keys = array();

        public $name = '';
        public $fullName = '';
        private $policy = false;

        public function getInstance($alias) {
            $domain[$alias] = &$this->model;
            return $this->scope->DataSet(array(
                'keys'   => $this->getKeys($alias),
                'select' => $this->getPolicyColumns($alias),
                'from'   => $domain,
                'where'  => $this->getPolicyPredicates($alias),
                'group'  => false,
                'having' => false,
                'order'  => false,
                'offset' => false,
                'limit'  => false
            ));
        }

        public function getPolicyColumns() {

        }

        public function getPolicyPredicates() {

        }

        public function configure($x) {
        	$x['columns']->Columns($this->model['columns']);
            //  $x['data']->Data($this->model['data']);
            $x['{name:url}-KEY']->Key($this->model['keys']);
        }

        public function __complete() {
        	$this->database = $this->scope->database;
        	$this->connection = $this->database->connection;
            $this->policy = $this->connection->getPolicy($this->model);
        	$this->scope->table = $this;
        }


    }


?>