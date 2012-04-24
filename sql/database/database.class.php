<?
	class Oxygen_SQL_Database extends Oxygen_Controller {


        private $tables = false;

        public function getTables() {
            if($this->tables === false) {
                $this->tables = $this->resultToArray(
                    $this->paramQuery(
                        'select * from INFORMATION_SCHEMA.TABLES where TABLE_SCHEMA={SCHEMA_NAME}',
                        $this->model
                    ),
                    'TABLE_NAME'
                );
            }
            return $this->tables;
        }

        private function registerEntries() {
            $this->db = $this;
        }

        public function __complete() {
            $this->registerEntries();
        }

		public function configure($x){
            $x['{TABLE_NAME:str}']->Oxygen_SQL_Table($this->getTables());
		}
	}

?>