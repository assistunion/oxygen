<?

    class Oxygen_SQL_Table extends Oxygen_Controller {

    	public $connection = null;
    	public $database = null;

    	public $data = array();
        public $meta = array();

        public $columns = array(
            'select' => 'false',
            'update' => 'false',
            'delete' => 'false',
            'insert' => 'false'
        );
        public $keys = array();

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
            $newColumns = array();
            foreach ($this->model['columns'] as $name => $def) {
                $qualified = $alias === false
                    ? $name
                    : $alias . '.' . $name
                ;
                if($regexp && preg_match($regexp, $qualified)) {
                    $newColumns[$qualified] = $def;
                }
            }
            return $this->columns[$intent] = $newColumns;
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
            $x['{name:url}-key']->Key($this->model['constraints']);
        	$x['data']->Data($this->getData());
        }

        public function __complete() {
        	$this->database = $this->SCOPE_DATABASE;
        	$this->connection = $this->database->connection;
        	$this->SCOPE_TABLE = $this;
        }
        
        public function getKeys($alias) {
            if(isset($this->keys[$alias])) return $this->keys[$alias];
            $newKeys = array();
            foreach($this->model['keys'] as $name => $key) {
                $newKeys[$name] = array();
                foreach($key as $name => $column) {
                    $newKeys
                }
            }
        }


        public function getPolicyPredicate($alias, $intent) {
            $policy = $this->getPolicy($alias, $intent);
            return $policy['rows'];
        }

        private function getMeta($alias = false, $intent = 'select') {
            $alias = $this->resolveAlias($alias);
            if(isset($this->meta[$alias])) return $this->meta[$alias];
            $domain = array();
            $domain[$alias] = array(
                $this->fullName,
                false,    // empty join type
                array()   // no join expression
            );
            return $this->meta[$alias] = array(
                'connection' => $this->connection,
                'columns'    => $this->getColumns($alias, $intent),
                'domain'     => $domain,
                'filter'     => $this->getPolicyPredicate($alias, $intent),
                'keys'       => $this->getKeys($alias),
                'grouping'   => false,
                'offset'     => false,
                'limit'      => false,
                'order'      => false,
                'having'     => false
            );
        }

    }


?>