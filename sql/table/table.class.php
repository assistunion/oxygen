<?

    class Oxygen_SQL_Table extends Oxygen_Controller {

    	public $connection = null;
    	public $database = null;
    	public $columns = false;
    	public $key = false;
    	public $relations = false;
    	public $data = false;

    	public function getColumns() {
    		if ($this->columns !== false) return $this->columns;
    		return $this->columns = $this->connection->paramQueryAssoc(
    			'select * from INFORMATION_SCHEMA.COLUMNS 
    			 where 
    			 	TABLE_SCHEMA = {TABLE_SCHEMA}
    			 	AND TABLE_NAME = {TABLE_NAME}',
    			 $this->model,
    			 'COLUMN_NAME'
    		);
    	}

    	public function getKey() {
    		return $this->key;
    	}

    	public function getRelations() {
    		return $this->relations;
    	}

    	public function getData() {
    		return $this->data;
    	}

        public function configure($x) {
        	$x['columns']->Columns($this->getColumns());
        	$x['data']->Data($this->getData());
        	$x['key']->Key($this->getKey());
        	$x['relations']->Relations($this->getRelations());
        }

        public function __complete() {
        	$this->database = $this->SCOPE_DATABASE;
        	$this->connection = $this->database->connection;
        	$this->SCOPE_TABLE = $this;
        }
    }


?>