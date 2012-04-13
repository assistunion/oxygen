<?

    class Oxygen_Exception extends Exception {

        //TODO: Change in php 5.3 to native previous
        protected $previous;

        public function __construct($message = "", $code=0, $previous = null) {
            parent::__construct($message, $code);
            $this->previous = $previous; //TODO: See above.
        }
        
        public function getWrapTrace() {
            return $this->getTrace();
        }
        
        public function getName() {
            return get_class($this);
        }
        
        // COPY-PASTE BLOCK FROM Oxygen_Object (Here we have to inherit from Exception) 
        // TODO: in php 5.4 this should be refactored with traits
        // begin Copy-Paste block:
        
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
            if(preg_match(self::CALL_REGEXP, $method, $match)){
                $class = get_class($this);
                if ($match[1] !== '') $class = get_parent_class($this);
                return $this->{$match[2]}($match[3],$args);
            } else {
                $this->throw_Exception(
                    self::UNKNOWN_METHOD,
                    get_class($this),
                    $method
                );
            }
        }

        public final function new_($class, $args = array()) {
            return $this->scope->resolve($class)->getInstance($args);
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
            try {
                include $scope->loader->pathFor(
                    $class,
                    $name . Oxygen_Loader::TEMPLATE_EXTENSION
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

        public static function componentClassFor($class,$resource) {
            return 'css-' . md5($class . '-' . $resource);
        }


        public final function getComponentClass() {
            if(($count = count($this->stack)) == 0) {
                $this->throwException('getComponentClass() call is valid only within template code');
            } else {
                $call = &$this->stack[$count-1];
                if($call[self::COMPONENT] === false) {
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
        
        // end Copy-Paste block.
        
        
    }

?>