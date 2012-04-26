<?
	class Oxygen_SQL_Database extends Oxygen_Controller {


        private $tables = false;
        public $connection = null;
        
        public function __complete() {
            $this->connection = $this->SCOPE_CONNECTION;
            $this->SCOPE_DATABASE = $this;
        }

        public function getTables() {
            if($this->tables === false) {
                $this->tables = $this->connection->paramQueryAssoc(
                    'select * from INFORMATION_SCHEMA.TABLES where TABLE_SCHEMA={SCHEMA_NAME}',
                    $this->model,
                    'TABLE_NAME'
                );
            }
            return $this->tables;
        }


		public function configure($x){
            $x['{TABLE_NAME:url}']->Table($this->getTables());
		}
	}

?>