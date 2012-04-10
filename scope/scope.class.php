<?

    class Oxygen_Scope extends Oxygen_Object {

        const FACTORY_REDEFINED = 'Factory {0} is redefined in this scope';
        const DEFAULT_FACTORY = 'Oxygen_Factory_Class';

        private $data = array();
        private $impl = array();
        private $parent = null;
        private static $root = null;

        public function __depend($scope){
            $this->scope = $this;
            $this->parent = $scope;
        }

        private function __assertFreshName($name){
            $this->__assert(!isset($this->impl[$name]), self::FACTORY_REDEFINED, $name);
        }

        public function callable($name, $callable) {
            $this->__assertFreshName($name);
            $this->ensureFreshName($name);
            return $this->impl[$name] = $this->new_Oxygen_Factory_Callable($callable);
        }

        public function register($name, $class) {
            $this->__assertFreshName($name);
            if($name === self::DEFAULT_FACTORY) {
                // Manually registering class factory to prevent infinite recursion
                $factory = new $class($class);
                $factory->__depend($this);
                $factory->__complete();
                return $this->impl[$name] = $factory;
            } else {
                return $this->impl[$name] = $this->new_Oxygen_Factory_Class($class);
            }
        }

        public function instance($name, $instance) {
            $this->__assertFreshName($name);
            return $this->impl[$name] = $this->new_Oxygen_Factory_Instance($instance);
        }

        public function resolve($name) {
            if(isset($this->impl[$name])){
                return $this->impl[$name];
            } else if($this->parent !== null) {
                return $this->impl[$name] = $this->parent->resolve($name);
            } else {
                return $this->register($name,$name);
            }
        }

        public function has($name, $recursive = true) {
            if(isset($this->impl[$name])) {
                return true;
            } else if ($recursive && $this->parent != null) {
                return $this->parent->has($name);
            } else {
                return false;
            }
        }

        public function __get($name) {
            return $this->resolve($name)->getDefinition();
        }
        public function __set($name, $value) {
            $this->instance($name, $value);
        }

        public static function root(){
            return self::$root;
        }

        public function load($class) {
            if(!class_exists($class)) {
                Oxygen_Loader::loadClass($class);
            }
        }

        public static function __class_construct(){
           $scope = self::$root = new Oxygen_Scope();
           $scope->register('Exception','Oxygen_Exception');
           $scope->register('Scope','Oxygen_Scope');
           $scope->assets = $scope->new_Oxygen_Asset_Manager();
        }

    }


?>