<?

	class Oxygen_SQL_DataSet extends Oxygen_Object
		implements IteratorAggregate
		//implements Countable, ArrayAccess, IteratorAggregate
	{

		const MAX_ROWS = 1000000;
		const MASTER_ALIAS = '_';
		const EMPTY_FILTER = '(1=1)';

		public $connection = null;
		public $builder = null;
		public $iterationKey = array();
		public $sql = array();

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

		public function getIterationKey() {
			return $this->meta['keys'][0];
		}

		public function __construct($meta) {
			$this->meta = array_merge(self::$defaults, $meta);
		}

		public function __complete() {
			$this->connection = $this->scope->connection;
			$this->builder = $this->connection->builder;
			$this->sql['select'] = $this->builder->buildSql($this->meta,'select');
		}

		public function makeRow($data) {
			return $this->scope->Row($data);
		}

		public function getIterator() {
			return $this->scope->DataIterator(
				$this->sql['select'],
				$this->getIterationKey(),
				array($this,'makeRow')
			);
		}


	}



?>