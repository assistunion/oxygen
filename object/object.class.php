<?

    class Oxygen_Object {

        const TO_STRING_DEFAULT = '[{0} Object]';

        protected $scope = null;
        protected $arg = null;
        protected $stack = array();

        public function __construct() {
            $this->scope = Oxygen_Scope::root();
            $this->get = new Oxygen_Getter($this);
            $this->put = new Oxygen_Putter($this);
        }

        public static function componentClassFor($class,$resource) {
            return 'css-' . md5($class . '-' . $resource);
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

        public function getAssetClass() {
            $class = get_class($this);

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