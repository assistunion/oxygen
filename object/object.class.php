<?

    class Oxygen_Object {

        const TO_STRING_DEFAULT = '[{0} Object]';

        protected $scope = null;
        protected $arg = null;

        public function __construct() {
            $this->scope = Oxygen_Scope::root();
            $this->get = new Oxygen_Getter($this);
            $this->put = new Oxygen_Putter($this);
        }

        public function executeResource($path,$resource,$args) {
            $class = get_class($this);
            $scope = $this->getScope();
            $scope->less->add($class,$resource);
            $scope->css->add($class,$resource);
            $scope->js->add($class,$resource);
            include($path);
        }

        public function __toString() {
            return Oxygen_Utils_Text::format(self::TO_STRING_DEFAULT, get_class($this));
        }

        public function throwException($message, $code = 0, $previous = null) {
            if($this->scope !== null) {
                throw $this->scope->Oxygen_Exception($message, $code, $previous);
            } else {
                throw Oxygen_Scope::root()->Oxygen_Exception($message, $code, $previous);
            }
        }

        public function getScope() {
            return $this->scope;
        }

        public function __complete() {
        }

        public function __depend($scope, $arg = false) {
            $this->arg = $arg;
            $this->scope = $scope;
        }
        
    }

?>