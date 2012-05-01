<?

    class Oxygen_SQL_Table extends Oxygen_Controller {

    	public $connection = null;
    	public $database = null;

    	public $columns = false;
    	public $key = false;

    	public $data = array();
        public $meta = array();

        public $name = '';
        public $fullName = '';

        public function getPolicy($alias = false, $intent = 'select') {
            if ($alias === false) $alias = $this->name;
            return $this->database->security->getPolicy($this->name, $alias, $intent);
        }

    	public final function getColumns($alias = false, $intent = 'select') {
            $policy = $this->getPolicy($intent, $alias);
            $regexp = $policy['columns-regexp'];
            if ($alias !== false) $intent .= '::' . $alias;
    		if (isset($this->columns[$intent])) return $this->columns[$intent];
            $columns = $this->connection->paramQueryAssoc(
    			'select * from INFORMATION_SCHEMA.COLUMNS
    			 where
    			 	TABLE_SCHEMA = {TABLE_SCHEMA}
    			 	AND TABLE_NAME = {TABLE_NAME}
                ',
    			 $this->model,
    			 'COLUMN_NAME'
    		);

            $newColumns = array();
            foreach ($columns as $name => $def) {
                $qualified = $alias === false
                    ? $name
                    : $alias . '.' . $name
                ;
                if($regexp && preg_match($regexp, $qualified)) {
                    $newColumns[$qualified] = $def;
                }
            }
            $this->columns[$intent] = $newColumns;
            $this->key = array();
            foreach($this->columns as $name => $column) {
                if($column['COLUMN_KEY'] === 'PRI') {
                    $this->key[$name] = $column;
                }
            }
            return $this->columns;
    	}

        public final function getKey() {
            if ($this->key === false) $this->getColumns();
            return $this->key;
        }

        public function resolveAlias($alias = false) {
            return $alias === false
                ? $this->name
                : $alias
            ;
        }

    	public final function getData($alias = false) {
            $alias = $this->resolveAlias($alias);
            if(isset($this->data[$alias])) return $this->data[$alias];
    		return $this->data[$alias] = $this->new_DataSet($this->getMeta($alias));
    	}

        public function configure($x) {
        	$x['columns']->Columns($this->model['columns']);
            $x['key']->Key($this->getKey());
        	$x['data']->Data($this->getData());
        }

        public function __complete() {
        	$this->database = $this->SCOPE_DATABASE;
        	$this->connection = $this->database->connection;
        	$this->SCOPE_TABLE = $this;
        }
        
        
        private function getMeta($alias) {
            if(isset($this->meta[$alias])) return $this->meta[$alias];
            $domain = array();
            $domain[$alias] = array(
                $this->fullName,
                false,    // empty join type
                array()   // no join expression
            );
            return $this->meta[$alias] = array(
                'connection' => $this->connection,
                'columns'    => $this->getColumns($alias),
                'domain'     => $domain,
                'filter'     => $this->getPolicyPredicate(),  //TODO: Here we can implement row-level security by policies !!!
                'key'        => $this->getKey($alias),
                'grouping'   => false,
                'offset'     => false,
                'limit'      => false,
                'order'      => false,
                'having'     => false
            );

            $this->key = $this->table->getKey();
            $this->columns = $this->table->getColumns();
            $columns = "";
            $source = self::safeName($this->table->name) . ' as ' . MASTER_ALIAS;
            $first = true;
            foreach($this->columns as $c) {
                $columns .= ($first ? '' : ',') . $indent;
                $first = false;
                $columnAlias = self::safeName($c['COLUMN_NAME']);
                $safeName = MASTER_ALIAS . '.' . $columnAlias;
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
                    $columnName = $c['COLUMN_NAME'];
                    $safeName = $safeAlias . '.' . self::safeName($columnName);
                    $columnAlias = self::safeName($alias . '.' . $columnName);
                    $columns .= '   ' . $safeName . ' as ' . $columnAlias;
                }
            }
        }

    }


?>