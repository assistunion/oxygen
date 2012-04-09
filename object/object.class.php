<?

    class Oxygen_Object {

        const DEFAULT_TO_STRING = '[{0} Object]';
        const ASSERTION_FAILED = 'Assertion failed';

        const CALL_REGEXP = '/^(parent_)?(get_|put_|throw_|new_)(.*)$/';

        const CLAZZ     = 0;
        const RESOURCE  = 1;
        const COMPONENT = 2;

        private $scope = null;
        private $stack = array();

        public function __call($method, $args) {
            if(preg_match(self::RESOURCE_REGEXP, $method, $match)){
                $class = get_class($this);
                if ($match[1] !== '') $class = get_parent_class($this);
                $this->{$match[2]}($match[3],$args);
            } else {
                $this->throw_Exception(self::UNKNOWN_METHOD,$method);
            }
        }

        public final function new_($class, $args, $owner) {
            return $this->getScope()->$class($args);
        }

        public final function throw_($class, $args) {
            throw $this->getScope()->$class($args);
        }

        public final function get_($method, $args = array(), $class = false) {
            ob_start();
            try {
                $this->put($name, $args, $class);
                $ex = null;
            } catch(Exception $e) {
                $ex = $e;
            }
            if ($ex !== null) {
                ob_end_clean();
                throw $ex;
            } else {
                return ob_get_clean();
            }
        }

        public final function put_($name, $args = array(), $class = false) {
            $class = ($class === false) ? get_class($this) : $class;
            $call = array($class, $name, false);
            array_push($this->stack, $call);
            try {
                include Oxygen_Loader::pathFor($class, $name);
                $ex = null;
            } catch(Exception $e){
                $ex = $e;
            }
            if ($ex !== null) {
                array_pop($this->stack);
                throw $ex;
            } else {
                $this->getScope()->assets->add(array_pop($this->stack));
            }
        }

        public static function componentClassFor($class,$resource) {
            return 'css-' . md5($class . '-' . $resource);
        }


        public final function getComponentClass() {
            if(($count = count($this->stack)) == 0) {
                $this->throwException('getComponentClass() call is valid only within template code');
            } else {
                $call = &$this->stack[$count-1];
                if($call[self::COMPONENT] !== false) {
                    return $call[self::COMPONENT] = self::componentClassFor(
                        $call[self::CLAZZ],
                        $call[self::RESOURCE]
                    );
                } else {
                    return $call[self::COMPONENT];
                }
            }
        }

        public function __toString() {
            return Oxygen_Utils_Text::format(self::TO_STRING_DEFAULT, get_class($this));
        }


        public final function getScope() {
            return ($this->scope !== null)
                ? $this->scope
                : Oxygen_Scope::getRootScope()
            ;
        }

        public final function __assert(
            $condition,
            $message = false,
            $arg0 = '', $arg1 = '', $arg2 = '', $arg3 = '', $arg4 = ''
        ) {
            if ($condition !== true) {
                $this->throw_Exception(
                    Oxygen_Utils_Text::format(
                        ($message === false ? $message : self::ASSERTION_FAILED),
                        $arg0, $arg1, $arg2, $arg3, $arg4
                    )
                );
            }
        }

        public function __complete() {
        }

        public function __depend($scope) {
            $this->scope = $scope;
        }

    }

?>