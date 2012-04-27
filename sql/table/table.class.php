<?

    class Oxygen_SQL_Table extends Oxygen_Controller {

    	public $connection = null;
    	public $database = null;
    	public $columns = false;
    	public $key = false;
    	public $relations = false;
    	public $data = false;
        public $name = ''
        public $fullName = '';

    	public function getColumns() {
    		if ($this->columns !== false) return $this->columns;
    		$this->columns = $this->connection->paramQueryAssoc(
    			'select * from INFORMATION_SCHEMA.COLUMNS 
    			 where 
    			 	TABLE_SCHEMA = {TABLE_SCHEMA}
    			 	AND TABLE_NAME = {TABLE_NAME}',
    			 $this->model,
    			 'COLUMN_NAME'
    		);
            $this->key = array();
            foreach($this->columns as $name => $column) {
                if($column['COLUMN_KEY'] === 'PRI') {
                    $this->key[$name] = $column;
                }
            }
            return $this->columns;
    	}

        public function getKey() {
            if ($this->key === false) $this->getColumns();
            return $this->key;
        }

    	public function getRelations() {
    		return $this->relations;
    	}

    	public function getData() {
            if($this->data !== false) return $this->data;
    		return $this->data = new DataSet($this);
    	}

        public function configure($x) {
        	$x['columns']->Columns($this->getColumns());
            $x['key']->Key($this->getKey());
        	$x['data']->Data($this->getData());
        	$x['relations']->Relations($this->getRelations());
        }

        public function __complete() {
        	$this->database = $this->SCOPE_DATABASE;
        	$this->connection = $this->database->connection;
        	$this->SCOPE_TABLE = $this;
            $this->name = $this->model['TABLE_NAME'];
            $this->fullName = $this->database->name . '.' $this->name;
        }
    }


?>