<?
	class Oxygen_SQL_Database extends Oxygen_Controller {

        public $connection = null;

        public function __complete() {
            $this->connection = $this->SCOPE_CONNECTION;
            $this->SCOPE_DATABASE = $this;
        }

		public function configure($x){
            $x['{table:url}']->Table($this->model['tables']);
		}
	}

?>