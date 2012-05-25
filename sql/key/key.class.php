<?

	class Oxygen_SQL_Key extends Oxygen_SQL_Columns {
        public function getDefaultView() {
            return 'view';
        }

        function rpc_Hello($whom) {
            $this->flash('Hi!');
            $this->flashError('Other!' . $whom);
            return "Hi, {$whom}!";
        }
        
        public function configure($x) {
            $x['{COLUMN_NAME:url}']->Column($this->model['columns']);
        }
	}

?>