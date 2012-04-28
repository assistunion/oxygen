<?

	class Oxygen_SQL_DataSet extends Oxygen_Object
		implements Countable, ArrayAccess, IteratorAggregate {

		const MAX_ROWS = 1000000;
		const MASTER_ALIAS = '_';
		const EMPTY_FILTER = '(1=1)';

		public $connection = null;

		private static $defaults = array(
			'base'   => false,
			'with'   => array(),
			'filter' => array(),
			'order'  => array(),
			'group'  => array(),
			'having' => array(),
			'limit'  => MAX_ROWS,
			'offset' => 0
		);

		public $base  = false;
		public $with   = array();
		public $filter = array();
		public $order  = array();
		public $group  = array();
		public $having = array();
		public $limit  = MAX_ROWS;
		public $offset = 0;

		private $columns = array();
		private $key = array();
		private $relations = array();


		public function getColumns() {
			return $this->columns;
		}

		public function getKey() {
			return $this->key;
		}

		public function getRelations() {
			$return $this->relations;
		}

		public function __construct($config) {
			if($config instanceof Oxygen_SQL_Table || $config instanceof Oxygen_SQL_DataSet) {
				$config = array('base' => $config);
			}
			$config = array_merge(self::$defaults, $config);
			$this->base   = $config['base'];
			$this->with   = $config['with'];
			$this->filter = $config['filter'];
			$this->order  = $config['order'];
			$this->group  = $config['group'];
			$this->having = $config['having'];
			$this->limit  = $config['limit'];
			$this->offset = $config['offset'];
			$this->__assert($this->base instanceof Oxygen_SQL_Table, 'Should be a table');
			$this->connection = $this->base->connection;
			foreach($this->with as $alias => $relation) {
				$this->__assert(
					$relation->data->connection === $this->connection,
					'Doesn\'t support joins from different connections'
				);
			}
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
			$indent = "\n    ";
			$this->key = $this->table->getKey();
			$this->columns = $this->table->getColumns();
			$columns = "";
			$source = self::safeName($this->table->name) . ' as ' . MASTER_ALIAS;
			$first = true;
			foreach($this->columns as $c) {
				$columns .= ($first ? '' : ',') . $indent;
				$first = false;
				$columnAlias = self::safeName($c['COLUMN_NAME']);
				$safeName = MASTER_ALIAS . '.' $columnAlias;
				$columns .= '   ' . $safeName . ' as ' . $columnAlias;
			}
			foreach($this->with as $alias => $relation) {
				$related = $relation->table; 
				$joined = $related->getColumns();
				$source .= $indent . self::safeName($related->name) . ' as ' . $alias;
				$safeAlias = self::safeName($alias);
				foreach ($joined as $c) {
					$columns .= ($first ? '' : ',') . $indent;
					$first = false;
					$columnName = $['COLUMN_NAME'];
					$safeName = $safeAlias . '.' . self::safeName($columnName);
					$columnAlias = self::safeName($alias . '.' . $columnName);
					$columns .= '   ' . $safeName . ' as ' . $columnAlias;
				}
			}
			if (isString($this->))
		}

		public function offsetGet($offset) {
			
		}

	}



?>