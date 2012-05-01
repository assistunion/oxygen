<?
	class Oxygen_SQL_Database extends Oxygen_Controller {

        public $connection = null;

        public function __complete() {
            $this->connection = $this->SCOPE_CONNECTION;
            $this->SCOPE_DATABASE = $this;
            $this->security = $this->new_Security();
            $this->connection->initDb($this);
        }

		public function configure($x){
            $x['{table:url}']->Table($this->model['tables']);
		}
	}

?>