<?

    class Oxygen_SQL_Table extends Oxygen_ScopeController {

    	public $connection = null;
    	public $database = null;

    	public $data = array();
        public $meta = array();

        public $keys = array();

        public $name = '';
        public $fullName = '';

        private $policy = false;
        private $policyColumns = array();
        private $policyPredicates = array();


        private static $nextAlias = 0;
        public static function nextAlias() {
            return self::$nextAlias++;
        }


        public function getIcon() {
            return 'database_table';
        }

        public function getDefaultView() {
            return 'as_table';
        }

        public function getData($alias) {
            $from[$alias] = array(
                'name' => $this->fullName,
                'type' => 'init',
                'join' => array()
            );
            return $this->scope->DataSet(array(
                'keys'   => $this->getKeys($alias),
                'select' => $this->getPolicyColumns($alias),
                'from'   => $from,
                'where'  => $this->getPolicyPredicates($alias),
                'group'  => false,
                'having' => false,
                'order'  => false,
                'offset' => false,
                'limit'  => false
            ));
        }

        public function getKeys($alias) {
            $result = array();
            foreach($this['keys'] as $key => $columns) {
                $k = array();
                foreach($columns as $name => $col) {
                    $k[$name][] = $alias . '.' . $name;
                }
                $result[] = $k;
            }
            return $result;
        }

        private function ensurePolicyLoaded() {
            if(!$this->policy) $this->policy = $this->connection->getPolicy($this->model);
        }

        public function getPolicyColumns($alias) {
            if(isset($this->policyColumns[$alias])) {
                return $this->policyColumns[$alias];
            }
            $this->ensurePolicyLoaded();
            $result = array();
            $this->flash($this->policy,'debug');
            foreach($this->policy as $intent => $policy) {
                if($policy === true) {
                    $policy = array('columns'=>'*','predicate'=>true);
                }
                $columns = $policy['columns'];
                if ($columns === '*') $columns = array('*');
                $aliased = array();
                foreach($columns as $key => $column) {
                    $add = true;
                    if(is_integer($key)) {
                        // normal column
                        if ($column === '*') {
                            foreach($this->model['columns'] as $col) {
                                $columnValue = Oxygen_SQL_Builder::safeName($alias . '.' . $col['name']);
                                $columnAlias = Oxygen_SQL_Builder::compoundAlias($alias, $col['name']);
                                $this->__assert(
                                    !isset($aliased[$columnAlias]) || ($aliased[$columnAlias] === $columnValue),
                                    'Alias {0} is redefined',
                                    $columnAlias
                                );
                                $aliased[$columnAlias] = $columnValue;
                            }
                            $add = false;
                        } else {
                            $columnValue = Oxygen_SQL_Builder::safeName($alias . '.' . $column);
                            $columnAlias = Oxygen_SQL_Builder::compoundAlias($alias, $column);
                        }
                    } else {
                        $columnAlias = Oxygen_SQL_Builder::compoundAlias($alias, $key);
                        $columnValue = $column; // TODO: Document this behavior (no escaping in aliased expressions);
                    }
                    if ($add) {
                        $this->__assert(
                            !isset($aliased[$columnAlias]) || ($aliased[$columnAlias] === $columnValue),
                            'Alias {0} is redefined',
                            $columnAlias
                        );
                        $aliased[$columnAlias] = $columnValue;
                    }
                }
                $result[$intent] = $aliased;
            }
            return $this->policyColumns[$alias] = $result;
        }

        public function getPolicyPredicates($alias) {
            $this->ensurePolicyLoaded();
            return array();
        }

        public function configure($x) {
        	$x['columns']->Columns($this->model['columns']);
            //  $x['data']->Data($this->model['data']);
            $x['keys']->Keys($this->model['keys']);
        }

        public function __complete() {
        	$this->database = $this->scope->database;
        	$this->connection = $this->database->connection;
            $this->policy = $this->connection->getPolicy($this->model);
        	$this->scope->table = $this;
        }


    }


?>