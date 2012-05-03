<?

	class Oxygen_SQL_Connection extends Oxygen_Controller {

		private $link = null;
        private $initDbCallback = null;
        private $refreshSchemata = false;
        private $cache = null;
        private $policyCache = array();
        private $policyCallback = null;

        private static $intents = array(
            'select' => true, 
            'insert' => true,
            'delete' => true,
            'update' => true
        );

        public $builder = null;

        const CENSORED_PASSWORD = '******';

		private static $implementations = array(
			'Connection' => 'Oxygen_SQL_Connection',
			'Database'   => 'Oxygen_SQL_Database',
			'Table'      => 'Oxygen_SQL_Table',
			'Columns'    => 'Oxygen_SQL_Columns',
			'Column'     => 'Oxygen_SQL_Column',
			'Key'        => 'Oxygen_SQL_Key',
			'Data'       => 'Oxygen_SQL_Data',
            'Row'        => 'Oxygen_SQL_Row',
            'DataSet'    => 'Oxygen_SQL_DataSet',
			'Builder'    => 'Oxygen_SQL_Builder',
        );

        private function registerEntries() {
            foreach(self::$implementations as $name => $implementation) {
                $this->register($name, $implementation);
            }
            $this->SCOPE_CONNECTION = $this;
        }

        public function __construct($config, $refreshSchemata = false) {
            parent::__construct($config);
            $this->model['databases'] = array();
            $this->refreshSchemata = $refreshSchemata;
        }

        public function __complete() {
            $this->link = @mysql_connect(
                $this->model['host'],
                $this->model['user'],
                $this->model['pass']
            );
            $this->model['pass'] = self::CENSORED_PASSWORD;
            $this->model['databases'] = array();
            $this->__assert($this->link, mysql_error());
            $this->cache = $this->SCOPE_CACHE;
            $this->registerEntries();
            $this->builder = $this->new_Builder();
            $this->rawQuery('set names utf8');
        }

        public function configure($x) {
            $this->reflectDatabases($this->refreshSchemata);
            $x['{database:url}']->Database($this->model['databases']);
        }

        public function __toString() {
            return $this->host;
        }

        public function getPolicy($table) {
            if ($this->policyCallback !== null) {
                $fullName = $table['database']['name'] . '.' . $table['name'];
                if (isset($this->policyCache[$fullName])) return $this->policyCache[$fullName];
                $policy = array();
                foreach (self::$intents as $intent => $default) {
                    $policy[$intent] = call_user_func(
                        $this->policyCallback,
                        $fullName,
                        $table,
                        $intent,
                        $default
                    );
                }
                return $this->policyCache[$fullName] = $policy;
            } else {
                return self::$intents;
            }
        }

        public function setPolicyCallback($callback, $method = null) {
            $this->__assert(
                $this->policyCallback === null,
                'Can\'t set policyCallback twice'
            );
            $this->policyCallback = ($method === null)
                ? $callback
                : array($callback, $method)
            ;
        }

		public function rawQuery($sql) {
			$this->__assert(
				$result = mysql_query($sql, $this->link),
				mysql_error($this->link)
			);
			return $result;
		}

		public function paramQuery($sql, $params = array()) {
            $sql = preg_replace('/([{<])([A-Za-z0-9_]*?)([>}])/e',
                "'\\1' === '{' ? ('\\''.mysql_escape_string(\$params['\\2'],\$this->link).'\\'') : \$params['<\\2>']",$sql);			
            return $this->rawQuery($sql);
		}

        public function paramQueryArray($sql, $params = array(), $key = false) {
            return $this->resultToArray($this->paramQuery($sql, $params),$key);
        }

        public function rawQueryArray($sql, $key = false) {
            return $this->resultToArray($this->rawQuery($sql),$key);
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

        private function getDbKey($dbName) {
            return "mysql://{$this->model['user']}@{$this->model['host']}/$dbName";
        }

        private function getReflectedDb($name) {
            return $this->cache->deserialize(
                $this->getDbKey($name),
                false
            );
        }

        public function cacheReflectedDb($name, $data) {
            $this->cache->serialize(
                $this->getDbKey($name),
                $data
            );
        }

        private function reflectDatabases($refresh) {
            //if not forced to refresh schemata - exclude already reflected databases
            if($refresh) {
                $toReflect = $this->model['uses'];
            } else {
                $toReflect = array();
                foreach($this->model['uses'] as $db) {
                    $data = $this->getReflectedDb($db);
                    if($data === false) {
                        $toReflect[] = $db;
                    } else {
                        $data['connection'] = &$this->model;
                        $this->model['databases'][$db] = $data;
                    }
                }
            }

            if (count($toReflect) === 0) return;

            $list = $this->builder->buildValueList($toReflect);
            $columns = $this->rawQuery("SELECT
                    TABLE_SCHEMA             as `database`,
                    TABLE_NAME               as `table`,
                    COLUMN_NAME              as `column`,
                    ORDINAL_POSITION         as `ordinal`,
                    COLUMN_DEFAULT           as `default`,
                    IS_NULLABLE              as `nullable`,
                    DATA_TYPE                as `type`,
                    CHARACTER_MAXIMUM_LENGTH as `max`,
                    NUMERIC_PRECISION        as `precision`,
                    NUMERIC_SCALE            as `scale`,
                    CHARACTER_SET_NAME       as `charset`,
                    COLLATION_NAME           as `collation`,
                    COLUMN_TYPE              as `def`,
                    EXTRA                    as `extra`
                FROM
                    INFORMATION_SCHEMA.COLUMNS
                WHERE
                    TABLE_SCHEMA IN {$list}
                ORDER BY
                    TABLE_SCHEMA, TABLE_NAME, ORDINAL_POSITION
            ");
            $path = array('database' => 'tables', 'table' => 'columns', 'column' => '*');
            while($row = mysql_fetch_assoc($columns)){
                $this->structurize($row, $path, $this->model['databases']);
            }
            mysql_free_result($columns);

            $keys = $this->rawQuery("SELECT
                c.TABLE_SCHEMA                                              as `database`,
                c.TABLE_NAME                                                as `table`,
                'keys'                                                      as `feature`,
                c.CONSTRAINT_NAME                                           as `key`,
                case c.CONSTRAINT_TYPE when 'PRIMARY KEY' then 1 else 0 end as `primary`,
                u.COLUMN_NAME                                               as `column`
            FROM
                INFORMATION_SCHEMA.TABLE_CONSTRAINTS as c
                INNER JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE as u
                    ON c.TABLE_SCHEMA = u.TABLE_SCHEMA
                      AND c.TABLE_NAME = u.TABLE_NAME
                      AND c.CONSTRAINT_NAME = u.CONSTRAINT_NAME
            WHERE
                c.CONSTRAINT_TYPE IN ('UNIQUE','PRIMARY KEY')
                AND c.TABLE_SCHEMA in {$list}
            ORDER BY
                c.TABLE_SCHEMA,
                c.TABLE_NAME,
                `primary` DESC,
                c.CONSTRAINT_NAME,
                u.ORDINAL_POSITION
            ");
            $path = array('database' => 'tables', 'table' => 'keys', 'key' => '*');
            while($row = mysql_fetch_assoc($keys)){
                $this->structurize($row, $path, $this->model['databases']);
            }
            mysql_free_result($keys);

            foreach($toReflect as $db) {
                $this->cacheReflectedDb($db, $this->model['databases'][$db]);
            }

            $tid = 0;
            foreach($this->model['databases'] as &$db) {
                foreach($db['tables'] as &$table) {
                    $table['id'] = $tid++;
                }
            }

        }

        private function structurize($row, $path, &$root) {
            $prevProp = 'connection';
            $prevObj = &$this->model;
            $x = &$root;
            foreach($path as $prop => $collection) {
                $key = $row[$prop];
                unset($row[$prop]);
                if(!isset($x[$key])) {
                    if($collection === '*') {
                        $row[$prevProp] = &$prevObj;
                        $row['name'] = $key;
                        $x[$key] = $row;
                        break;
                    } else {
                        $new = array();
                        $new['name'] = $key;
                        $new[$collection] = array();
                        $new[$prevProp] = &$prevObj;
                        $x[$key] = $new;
                    }
                }
                $prevProp = $prop;
                $x = &$x[$key];
                $prevObj = &$x;
                $x = &$x[$collection];
            }
        }


	}

?>