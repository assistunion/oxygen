<?
	class Oxygen_SQL_Database extends Oxygen_ScopeController {

        public $connection = null;

        public function __complete() {
            $this->connection = $this->scope->connection;
            $this->scope->database = $this;
        }

        public function getIcon() {
            return 'database';
        }

		public function configure($x){
            $x['{table:url}']->Table($this->model['tables']);
		}
	}

?>