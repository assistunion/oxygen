<?
	
	class Oxygen_SQL_Columns extends Oxygen_Controller {
		public function configure($x) {
			$x['{COLUMN_NAME:url}']->Column($this->model);
		}

        public function getDefaultView() {
            return 'as_table';
        }
	}

?>	