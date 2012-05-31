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
				$result .= self::indent($data->getSourceExpression()) . ' as ' . $alias;
				$on = self::buildOn($joins);
				if ($on !== '') {
					$result .= "on \n" . self::indent($on);
				}
				$result .= "\n";
			}
            return $result;
		}

		public static function buildColumns($columns) {
            $result = '';
            foreach($columns as $alias => $value) {
                $result .= $result === ''
                    ? ' '
                    : ",\n"
                ;
                $result .= $value . ' as ' . $alias;
            }
            return $result === '' ? false : $result;
		}

        public static function buildGroup($columns) {
        	if ($columns === false) return false;
            $result = '';
            foreach($columns as $alias => $value) {
                $result .= $result === ''
                    ? ' '
                    : ', '
                ;
                $result .= $value;
            }
            return $result === '' ? false : $result;
        }

		public static function buildFilter($predicate) {
            if($predicate === true) return false;
            if($predicate === false) return '(1=2)';
            if(is_string($predicate)) $predicate = array($predicate);
            if(count($predicate)===0) return false;
            $res = '';
            foreach($predicate as $key=>$value) {
                if (is_integer($key)) {
                    $c = $value;
                } else {
                    $c = $key . '=\'' . mysql_real_escape_string($value) . '\'';
                }
                $res .= $res === ''
                    ? ''
                    : ' and '
                ;
                $res .= '(' . $c . ')';
            }
            return $res;
		}

		public static function buildOrder($order) {
            return false;

		}

		public static function buildLimit($limit) {
            return $limit;

		}


		public static function buildOffset($offset) {
            return $offset;
		}

		public function getRealGroup($group, $keys) {
			if ($group === false) return false;
			foreach($keys as $key) {
				// if group contains all subparts of any key we can throw it away
				if(count(array_intersect($group, $key))===count(count($key))) {
					return false;
				}
			}
			return $group;
		}

		public function buildSql($meta, $intent, $onlyCount = false) {
			$realGroup = $this->getRealGroup($meta['group'],$meta['keys']);
            $moves = ($meta['offset'] !== false || $meta['limit'] !== false);
			if ($onlyCount) {
				if($realGroup === false && !$moves) {
					$select = ' count(*) as count ';
				} else {
					$select = ' 1 ';
				}
			} else {
				$select  = self::buildColumns($meta['select'][$intent]);
			}

            $from = self::buildDomain($meta['from']);

			$parts = array(
				'select'   => $select,
				'from'     => self::buildDomain($meta['from']),
				'where'    => self::buildFilter($meta['where'][$intent]),
				'group by' => self::buildGroup($realGroup),
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
			if ($onlyCount && $select === ' 1 ') {
				return "select count(*) as count from ($sql) as t";
			} else {
				return $sql;
			}
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

		public function addWhere($meta, $condition) {
			$where = $meta['where'];
            $cond = array();
			if(is_string($condition)) $condition = array($condition);
			foreach($condition as $key => $value) {
				if(is_integer($key)) {
					$cond[] = $value;
				} else {
					$cond[] = $key . ' = \'' . mysql_real_escape_string($value) . '\'';
				}
			}
            foreach($where as $intent => $w) {
                $p = $where[$intent];
                if ($p === true) {
                    $where[$intent] = $cond;
                } else if ($p !== false ){
                    $where[$intent] = array_merge($p,$cond);
                }
            }
			$meta['where'] = $where;
			return $meta;
		}

		public function addGroupBy($base, $newKey, $aggregates) {
		}

		public function addOrderBy($base, $order, $stable = false) {
		}

		public function addSlice($base, $offset, $limit) {
            $meta = $base;
            if($base['offset'] === false && $offset === 0) {
                $meta['limit'] = $limit;
                return $meta;
            }
            $meta['offset'] = $base['offset'] + $offset;
            if($offset + $limit < $base['limit'] || $base['limit'] === false) {
                $meta['limit'] = $limit;
            } else {
                $meta['limit'] = max(min($limit, $base['limit'] - $offset),0);
            }
            return $meta;
		}

		public function addJoin($base, $alias, $join) {
		}


	}

?>