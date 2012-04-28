<?

	interface Oxygen_SQL_Source {

		public function getColumns();
		public function getOffset();
		public function getLimit();
		public function getKey();
		public function getFilter();
		public function getHaving();
		public function getOrder();

		public function getKeyNames();
		public function getColumnNames();

		public function getDomain();

		public function select($projection);
		public function where($condition);
		public function groupBy($newKey);
		public function orderBy($order);
		public function slice($offset, $limit);
		public function join($data, $alias, $join);

	}


?>