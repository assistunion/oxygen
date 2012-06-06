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

        public function getSourceExpression() {
            return $this->fullName;
        }

        public function getData($alias) {
            $from[$alias] = array($this,false,array());
            return $this->scope->DataSet(array(
                'keys'   => $this->getKeys($alias),
                'select' => $this->getPolicyColumns($alias),
                'from'   => $from,
                'where'  => $this->getPolicyPredicates($alias),
                'group'  => false,
                'having' => true,
                'order'  => false,
                'offset' => false,
                'limit'  => false
            ));
        }

        public function getKeys($alias) {
            $result = array();
            foreach($this->model['keys'] as $name => $key) {
                $k = array();
                foreach($key['columns'] as $col) {
                    $k[$col['column']] = $alias . '.' . $col['column'];
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
            $result = array();
            foreach($this->policy as $intent => $resolution){
                if ($resolution === true) {
                    $resolution = array('predicate' => true, 'columns' => true);
                }
                $predicate = $resolution['predicate'];
                if ($predicate === true) {
                    $result[$intent] = true;
                } else if ($predicate === false) {
                    $result[$intent] = false;
                } else {
                    $pred = array();
                    foreach ($predicate as $key => $value) {
                        if(is_integer($key)) {
                            $pred[] = $value; //TODO parse and add aliases!
                        } else {
                            $columnAlias = Oxygen_SQL_Builder::compoundAlias($alias, $key);
                            $pred[$columnAlias] = $value;
                        }
                    }
                    $result[$intent] = $pred;
                }
            }
            return $result;
        }

        public function configure($x) {
        	$x['columns']->Columns($this->model['columns']);
            $x['keys']->Keys($this->model['keys']);
            $x['rows']->Oxygen_Entity_Collection($this->getData($this->model['name']));
        }

        public function __complete() {
        	$this->database = $this->scope->database;
        	$this->connection = $this->database->connection;
            $this->policy = $this->connection->getPolicy($this->model);
        	$this->scope->table = $this;
            $this->fullName = $this->database->model['name'] . '.' . $this->model['name'];
        }


    }


?>