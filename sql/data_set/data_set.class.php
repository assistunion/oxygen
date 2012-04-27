<?

	class Oxygen_SQL_DataSet extends Oxygen_Object
		implements Countable, ArrayAccess, IteratorAggregate {

		const MAX_ROWS = 1000000;
		const MASTER_ALIAS = '_';

		public $connection = null;
		public $table = null;
		public $with = array();
		public $key = null;
		public $sql = '';

		public function __construct(
			$table,
			$with   = array(),
			$filter = array(), 
			$order  = array(), 
			$offset = 0, 
			$limit  = self::MAX_ROWS
		) {
			$this->table = $table;
			$this->with = $with;
			$this->connection = $table->connection;
			$this->reflectMetadata();
		}

		private static function safeName($name) {
			if(!preg_match('/^[A-Z_][A-Z0-9_]*$/i', $name)){
				return '`'. str_replace('`','``',$name) . '`';
			} else {
				return $name;
			}
		}

		private function reflectMetadata() {
			$this->key = $this->table->getKey();
			$this->columns = $this->table->getColumns();
			$sql = "select";
			$first = true;
			foreach($this->columns as $c) {
				$sql .= ($first ? '' : ',') . "\n    ";
				$first = false;
				$columnAlias = self::safeName($c['COLUMN_NAME']);
				$safeName = MASTER_ALIAS . '.' $columnAlias;
				$sql .= '   ' . $safeName . ' as ' . $columnAlias;
			}
			foreach($this->with as $alias => $relation) {
				$joined = $relation->table->getColumns();
				$safeAlias = self::safeName($alias);
				foreach ($joined as $c) {
					$sql .= ($first ? '' : ',') . "\n    ";
					$first = false;
					$columnName = $['COLUMN_NAME'];
					$safeName = $safeAlias . '.' . self::safeName($columnName);
					$columnAlias = self::safeName($alias . '.' . $columnName);
					$sql .= '   ' . $safeName . ' as ' . $columnAlias;
				}
			}
		}

		public function offsetGet($offset) {
			
		}

	}



?>