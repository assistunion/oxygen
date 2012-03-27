<?

    class Oxygen_Exception extends Exception {

        //TODO: Change in php 5.3.0 to native previous
        protected $previous;

        protected $scope = null;
        protected $arg = null;

        public function __construct($message = "", $code=0, $previous = null) {
            parent::__construct($message, $code);
            $this->scope = Oxygen_Scope::root();
            $this->previous = $previous; //TODO: See above.
            $this->get = new Oxygen_Getter($this);
            $this->put = new Oxygen_Putter($this);
        }

        // Wraps given $exception into Oxygen_Exception_Wrapper
        // unless $exception is instance of Oxygen_Excpeion itself
        public static function wrap($exception) {
            if ($exception instanceof Oxygen_Excpeion) {
                return $exception;
            } else {
                return Oxygen_Scope::root()->Oxygen_Exception_Wrapper($exception);
            }
        }

        public function getScope() {
            return $this->scope;
        }
        
        public function getWrapTrace() {
            return $this->getTrace();
        }
        
        public function getName() {
            return get_class($this);
        }

        public function executeResource($path,$resource,$args) {
            $class = get_class($this);
            $this->scope->css->add($class,$resource);
            $this->scope->js->add($class,$resource);
            include($path);
        }

        public function __complete() {
            if($this->previous != null && !($this->previous instanceof Oxygen_Exception)) {
                $this->previous = $this->scope->Oxygen_Exception_Wrapper($this->previous);
            }
        }

        public function __depend($scope, $arg = false) {
            $this->arg = $arg;
            $this->scope = $scope;
        }
        
    }

?>