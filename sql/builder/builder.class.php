<?

	class Oxygen_SQL_Builder extends Oxygen_Object {

		// function safeName($name) relies on the assumption
		// that nobody will use a dot (.) within column names.
		// Even in escaped form. Also we assume that there are
		// no escaped leading and trailing spaces.
		// (If it's the case for you - my regrets!)
		// So we will treat any dots in names as dots in qualified names.
		public static function safeName($name) {
			if(!preg_match('/^[A-Z_][\.A-Z0-9_]*$/i', $name)){
				$pieces = explode('.', $name);
				foreach($pieces as $i => $piece) {
					$pieces[$i] = '`'. str_replace('`', '``', $piece) . '`';
				}
				return implode('.', $pieces);
			} else {
				return $name;
			}
		}

		// TODO: Check for correctness in edge cases (!)
		public static function compoundAlias($alias, $name) {
			return '`' . $alias . '.' . $name . '`';
		}

		public static function buildValueList($values) {
			$sql = '';
			foreach ($values as $value) {
				$sql .= $sql === '' ? '(' : ','	;
				$sql .= self::escapeValue($value);
			}
			$sql .= $sql === '' ? '()' : ')';
			return $sql;
		}

		public static function escapeValue($val) {
			return '\'' . mysql_real_escape_string($val) . '\'';
		}

		public static function indent($text) {
			return preg_replace('/^/m','    ',$text);
		}

		public static function buildOn($on) {
			$result = '';
			foreach ($on as $key => $value) {
				$result .= $result === '' ? '' :'and ';
				$result .= is_integer($key)
					? $value
					: '(' . self::safeName($key) . ' = ' . self::escapeValue($value) . ')'
				;
				$result .= "\n";
			}
			return $result;
		}

		public static function buildDomain($domain) {
			$result = '';
			foreach ($domain as $alias => $part) {
				list($data, $joinType, $joins) = $part;
				if ($joinType !== false) {
					$result .= $joinType . ' ';
				}
				$result .= self::indent($data->getSelectExpression()) . ' as ' . $alias;
				$on = self::buildOn($joins);
				if ($on !== '') {
					$result .= "on \n" . self::indent($on);
				}
				$result .= "\n";
			}
		}

		public static function buildColumns($columns) {

		}



		public function buildSql($meta, $update = false) {

			$parts = array(
				'select'   => self::buildColumns($meta['columns']),
				'from'     => self::buildDomain($meta['domain']),
				'where'    => self::buildFilter($meta['filter']),
				'group by' => self::buildColumns($meta['key']),
				'having'   => self::buildFilter($meta['having']),
				'order by' => self::buildOrder($meta['order']),
				'limit'    => self::buildLimit($meta['limit']),
				'offset'   => self::buildOffset($meta['offset'])
			);
			$sql = '';
			foreach ($parts as $section => $content) {
				if($content !== false) {
					$sql .= $section . "\n" . self::indent($content) . "\n";
				}
			}
			return $sql;
		}

		public function buildSelect($base, $projection) {
			if (is_string($projection)) $projection = array($projection); //single column;
			$re = '';
			$columns = array();
			foreach ($projection as $alias => $expression){
				if(is_integer($alias)) {
					$re .= ($re === '')	? '/' : '|';
					$re .= $expression;
				} else {
					$this->__assert(
						!isset($columns[$alias]),
						'Name {0} is already taken',
						$alias
					);
					$columns[$alias] = $expression;
				}
			}
			$re .= ($re === '')	? '/' : '';
			$re .= '/';
			$re  = '/'.str_replace(array('.','*'),array('\.','.*'), $re).'/';
			foreach ($base->getColumnNames() as $columnName) {
				if(preg_match($re, $columnName)) {
					$this->__assert(
						!isset($columns[$columnName]),
						'Name {0} is already taken',
						$columnName
					);
					$columns[$columnName] = $columnName;
				}
			}
			foreach ($base->getKeyNames() as $keyName) {
				if(isset($columns[$keyName])) {
					$this->__assert(
						$columns[$keyName] === $keyName,
						'Key name {0} is redefined by some alias',
						$keyName
					);
				} else {
					$columns[$keyName] = $keyName;
				}
			}
			$meta = $base->getMetaData();
			$meta['columns'] = $columns;
			$sql = $this->buildSql($meta);
			return $base->connection->getData($meta, $sql);
		}

		public function buildWhere($base, $condition) {
			$where = $base->getFilter();
			if(is_string($condition)) $condition = array($condition);
			foreach($condition as $key => $value) {
				if(is_integer($key)) {
					$where[] = $value;
				} else {
					$where[] = $key . ' = \'' . mysql_real_escape_string($value) . '\'';
				}
			}
			$meta = $base->getMetaData();
			$meta['filter'] = $where;
			$sql = $this->buildSql($meta);
			return $base->connection->getData($meta, $sql);
		}

		public function buildGroupBy($base, $newKey, $aggregates) {
		}

		public function buildOrderBy($base, $order, $stable = false) {
		}

		public function buildSlice($base, $offset, $limit) {
		}

		public function buildJoin($base, $alias, $join) {
		}


	}

?>