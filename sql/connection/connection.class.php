<?

	class Oxygen_SQL_Connection extends Oxygen_Controller {

		private $user = '';
        private $password = '';
        private $host = '';
        
		private $databases = false;
		private $link = null;
        
        const CENSORED_PASSWORD = '******';

		public function rawQuery($sql) {
			$this->__assert(
				$result = mysql_query($sql, $this->link),
				mysql_error($this->link)
			);
			return $result;
		}

		public function resultToArray($res, $key = false) {
			$array = array();
			if($key === false) {
				while($row = mysql_fetch_assoc($res)) {
					$array[] = $row;
				}
			} else if ($row = mysql_fetch_assoc($res)) {
				$this->__assert(
					isset($row[$key]),
					'There is no key named {0}',
					$key
				);
				$array[$row[$key]] = $row;
				while($row = mysql_fetch_assoc($res)) {
					$array[$row[$key]] = $row;
				}
			}
			return $array;
		}

		public function getDatabases() {
			if($this->databases === false) {
				$this->databases = $this->resultToArray(
					$this->rawQuery('select * from INFORMATION_SCHEMA.SCHEMATA'),
					'SCHEMA_NAME'
				);
			}
			return $this->databases;
		}

		public function configure($x) {
			$x['{SCHEMA_NAME:str}']->Oxygen_SQL_Database($this->getDatabases());
		}

		public function __construct($host = 'localhost', $user = 'root', $password = '') {
			$this->host = $host;
			$this->user = $user;
            $this->password = $password;
		}
        
        public function __complete() {
             $this->link = @mysql_connect($this->host, $this->user, $this->password);
             $this->password = self::CENSORED_PASSWORD;
             $this->__assert($this->link, mysql_error());
        }
	}

?>