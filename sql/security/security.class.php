<?

    class Oxygen_SQL_Security extends Oxygen_Object {

        private $policyCallback = null;
        private $policyCache = array();

        const ALL_ROWS = false;
        const ALL_COLUMNS = '*';

        public function setPolicyCallback($callback, $method = null) {
            $this->policyCallback = ($method === null)
                ? $callback
                : array($callback, $method)
            ;
        }

        public function getRowPredicate($table, $alias, $intent = 'select') {
            $policy = $this->getPolicy($table, $alias, $intent);
            return $policy['rows'];
        }

        public function getColumnFilter($table, $alias, $intent = 'select') {
            $policy = $this->getPolicy($table, $alias, $intent);
            return $policy['columns'];
        }

        public function getPolicy($table, $alias, $intent = 'select') {
            $key = $table . '::' . $alias . '::' . $intent;
            if (isset($this->policyCache[$key])) return $this->policyCache[$key];
            if ($this->policyCallback === null) {
                $result = array(
                    'rows'    => self::ALL_ROWS,
                    'columns' => self::ALL_COLUMNS
                );
            } else {
                $result = call_user_func(
                    $this->policyCallback,
                    $table, $alias, $intent
                );
                $this->__assert(
                    isset($result['columns']),
                    'Security policyCallback should provide $policy[\'columns\']'
                );
                $this->__assert(
                    isset($result['rows']),
                    'Security policyCallback should $policy[\'rows\']'
                );
            }
            return $this->policyCache[$key] = $result;
        }

    }

?>