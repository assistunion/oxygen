<?

	class Oxygen_SQL_DataSet extends Oxygen_Object
		//implements Countable, ArrayAccess, IteratorAggregate
	{

		const MAX_ROWS = 1000000;
		const MASTER_ALIAS = '_';
		const EMPTY_FILTER = '(1=1)';

		public $connection = null;

		private static $defaults = array(
			'select' => false,
			'from'   => false,
			'where'  => false,
			'order'  => false,
			'group'  => false,
			'having' => false,
			'limit'  => false,
			'offset' => false,
			'keys'   => false
		);

		public $meta = array();
		public function getMeta() {
			return $this->meta;
		}

		public function getColumnNames() {	}
		public function getKeyNames() {	}

		public function __construct($meta) {
			$this->meta = array_merge(self::$defaults, $meta);
		}

		public function __complete() {
			$this->connection = $this->scope->connection;
		}


	}



?>