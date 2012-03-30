<?

    class Oxygen_Exception extends Exception {

        //TODO: Change in php 5.3.0 to native previous
        protected $previous;

        protected $scope = null;
        protected $arg = null;
        protected $stack = array();        

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

        public function getComponentClass() {
            if(($count = count($this->stack)) == 0) {
                $this->throwException('getComponentClass() call is valid only within template code');
            } else {
                $usage = $this->stack[$count-1];
                $usage->isVirtual = true;
                return $usage->componentClass;
            }
        }

        public function executeResource($path,$resource,$args) {
            $class = get_class($this);
            $scope = $this->getScope();
            array_push($this->stack,(object)array(
                'componentClass'=>Oxygen_Object::componentClassFor($class,$resource),
                'isVirtual'=>false
            ));
            try {
                include($path);
            } catch(Exception $e) {
                array_pop($this->stack);
                throw $e;
            }
            $usage = array_pop($this->stack);
            $scope->less->add($class,$resource,$usage);
            $scope->css->add($class,$resource,$usage);
            $scope->js->add($class,$resource,$usage);
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