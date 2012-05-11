<?

    class Oxygen_Object {

        const OBJECT_CLASS    = 'Oxygen_Object';
        const EXCEPTION_CLASS = 'Oxygen_Exception';
        const SCOPE_CLASS     = 'Oxygen_Scope';

        const DEFAULT_TO_STRING = '[{0} Object]';
        const ASSERTION_FAILED = 'Assertion failed';

        const CALL_REGEXP = '/^(parent_)?(get_|put_|throw_|new_)(.*)$/';
        const UNKNOWN_METHOD = 'Unknown method {0}->{1}';

        const CLAZZ     = 0;
        const RESOURCE  = 1;
        const COMPONENT = 2;

        public $scope = null;
        private $stack = array();

        public function __call($method, $args) {
            if($method=='download'){
                echo 'HERE';
            }
            $this->__assert(
                preg_match(self::CALL_REGEXP, $method, $match),
                self::UNKNOWN_METHOD,
                get_class($this),
                $method
            );
            $class = get_class($this);
            if ($match[1] !== '') $class = get_parent_class($this);
            if(!is_string($match[2])){
                echo 'HERE';
            }            
            return $this->{$match[2]}($match[3],$args);
        }

        public final function new_($class, $args = array()) {
            return $this->scope->resolve($class)->getInstance($args, $this->scope);
        }

        public final function throw_($class, $args) {
            throw $this->new_($class, $args);
        }

        public final function get_($name, $args = array(), $class = false) {
            ob_start();
            try {
                $this->put_($name, $args, $class);
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
            $scope = $this->scope;
            $assets = $scope->assets;
            array_push($this->stack, $call);
            $resource = (strpos($name,'.') === false) 
                ? $name . Oxygen_Loader::TEMPLATE_EXTENSION
                : $name
            ;
            try {
                include $scope->loader->pathFor(
                    $class,
                    $resource
                );
                $ex = null;
            } catch(Exception $e){
                $ex = $e;
            }
            if ($ex !== null) {
                array_pop($this->stack);
                throw $ex;
            } else {
                $assets->add(array_pop($this->stack));
            }
        }

        public static function templateClassFor($class,$resource) {
            return 'css-' . $class . '-' . $resource;
        }


        public final function getTemplateClass() {
            if(($count = count($this->stack)) == 0) {
                $this->throwException('getTemplateClass() call is valid only within template code');
            } else {
                $call = &$this->stack[$count-1];
                if($call[self::COMPONENT] === false) {
                    return $call[self::COMPONENT] = self::templateClassFor(
                        $call[self::CLAZZ],
                        $call[self::RESOURCE]
                    );
                } else {
                    return $call[self::COMPONENT];
                }
            }
        }

        public function __toString() {
            return Oxygen_Utils_Text::format(self::DEFAULT_TO_STRING, get_class($this));
        }

        public final function __assert(
            $condition,
            $message = false,
            $arg0 = '', $arg1 = '', $arg2 = '', $arg3 = '', $arg4 = ''
        ) {
            if (!$condition) {
                $this->throw_Exception(
                    Oxygen_Utils_Text::format(
                        ($message === false ? self::ASSERTION_FAILED : $message),
                        $arg0, $arg1, $arg2, $arg3, $arg4
                    )
                );
            }
        }

        public function __complete() {
        }

        public static function __class_construct($scope) {
            /* Intentionally left blank. No code here */
        }

        public function __depend($scope) {
            $this->scope = $scope;
        }

        // Small inheritance hack:
        // Let system think that EXCEPTION_CLASS
        // is inherited from OBJECT_CLASS (not from Exception)
        public static function getOxygenParentClass($class) {
            return $class === self::EXCEPTION_CLASS
                ? self::OBJECT_CLASS
                : get_parent_class($class)
            ;
        }

        public static function isOxygenClass($class) {
            return (is_subclass_of($class, self::OBJECT_CLASS)
            || is_subclass_of($class, self::EXCEPTION_CLASS)
            || $class === self::OBJECT_CLASS
            || $class === self::EXCEPTION_CLASS
            );
        }

    }

?>