<?
	class Oxygen_SQL_Database extends Oxygen_Controller {

        private $connection = null;
        private $tables = false;

        public function getTables() {
            if($this->tables === false) {
                $this->tables = $this->connection->resultToArray(
                    $this->connection->paramQuery(
                        'select * from INFORMATION_SCHEMA.TABLES where TABLE_SCHEMA={SCHEMA_NAME}',
                        $this->model
                    ),
                    'TABLE_NAME'
                );
            }
            return $this->tables;
        }

        public function __construct($db, $connection) {
            parent::__construct($db);
            $this->connection = $connection;
        }

		public function configure($x){
            $x['{TABLE_NAME:str}']->Oxygen_SQL_Table($this->getTables(),$this);
		}
	}

?>