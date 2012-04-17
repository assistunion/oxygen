<?

    class Oxygen_Scope extends Oxygen_Object {

        const FACTORY_REDEFINED = 'Factory {0} is redefined in this scope';
        const DEFAULT_FACTORY = 'Oxygen_Factory_Class';
        
        const STATIC_CONSTRUCTOR = '__class_construct';
        

        private $entries = array();
        private $parent = null;
        private $introduced = array();

        public function __depend($scope){
            $this->scope = $this;
            $this->parent = $scope;
        }

        private function __assertFreshName($name){
            $this->__assert(!isset($this->entries[$name]), self::FACTORY_REDEFINED, $name);
        }

        public function callable($name, $callable) {
            $this->__assertFreshName($name);
            return $this->entries[$name] = $this->new_Oxygen_Factory_Callable($callable);
        }
        
        public function introduce($class) {
            if (self::isOxygenClass($class)
            && !isset($this->introduced[$class])
            ) {
                $this->introduce(self::getOxygenParentClass($class));
                $constructor = new ReflectionMethod($class, self::STATIC_CONSTRUCTOR);
                $this->introduced[$class] = true;
                if ($constructor->getDeclaringClass() === $class) {
                    call_user_func(array($class, self::STATIC_CONSTRUCTOR), $this);
                }
            }
        }

        public function register($name, $class) {
            $this->__assertFreshName($name);
            if($name === self::DEFAULT_FACTORY) {
                // Manually registering class factory to prevent infinite recursion
                $factory = new $class($class);
                $factory->__depend($this);
                $factory->__complete();
                return $this->entries[$name] = $factory;
            } else {
                return $this->entries[$name] = $this->new_Oxygen_Factory_Class($class);
            }
        }

        public function instance($name, $instance) {
            $this->__assertFreshName($name);
            return $this->entries[$name] = $this->new_Oxygen_Factory_Instance($instance);
        }

        public function resolve($name, $autoregister = true) {
            if(isset($this->entries[$name])){
                return $this->entries[$name];
            } else if($this->parent !== $this) {
                return $this->entries[$name] = $this->parent->resolve($name);
            } else {
                $this->__assert($autoregister,'Scoped element {0} is not found', $name);
                return $this->register($name,$name);
            }
        }

        public function has($name, $recursive = true) {
            if(isset($this->entries[$name])) {
                return true;
            } else if ($recursive && $this->parent !== $this) {
                return $this->parent->has($name);
            } else {
                return false;
            }
        }

        public function __get($name) {
            return $this->resolve($name, false)->getDefinition();
        }
        public function __set($name, $value) {
            $this->instance($name, $value);
        }

        // Wraps given $exception into Oxygen_Exception_Wrapper
        // unless $exception is instance of Oxygen_Excpeion itself
        public function wrapException($exception) {
            if ($exception instanceof Oxygen_Excpeion) {
                return $exception;
            } else {
                return $this->new_Oxygen_Exception_Wrapper($exception);
            }
        }

        public static function newRoot($classRoot) {
            $scope = new Oxygen_Scope();
            $scope->__depend($scope);
            $scope->__complete();
            $loader = new Oxygen_Loader($classRoot);
            $loader->__depend($scope);
            $loader->__complete();
            $loader->register();
            $scope->loader = $loader;
            $scope->OXYGEN_PATH = $classRoot;
            $scope->DOCUMENT_ROOT = str_replace('/',DIRECTORY_SEPARATOR, $_SERVER['DOCUMENT_ROOT']);
            self::__class_construct($scope);
            return $scope;
        }

        public static function __class_construct($scope){
            $scope->register('Exception','Oxygen_Exception');
            $scope->register('Scope','Oxygen_Scope');
            $scope->null = null;
            $scope->assets = $scope->new_Oxygen_Asset_Manager();
            $scope->lib = $scope->new_Oxygen_Lib();
        }

    }


?>