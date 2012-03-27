<?
    
    class Oxygen_Scope extends Oxygen_Object {

        const DEPENDENCY_METHOD = '__depend';
        const COMPLETION_METHOD = '__complete';

        private $data = array();
        private $impl = array();

        private function hasPublicConstructor($class) {
           try {
                $m = new ReflectionMethod($class, $class);
                if ($m->isPublic()) {
                    return true;
                }
           } catch (ReflectionException $e) {
           }
           try {
                $m = new ReflectionMethod($class,'__construct');
                if ($m->isPublic()) {
                     return true;
                }
           } catch (ReflectionException $e) {
           }
           return false;
       }

       public function register($interface,$implementation,$arg = null) {
            if($this->implementsInterface($interface, false)) {
                throw new Oxygen_Scope_Exception_InterfaceReimplemented($this,$interface);
            }
            if(is_string($implementation)) {
                $class = new ReflectionClass($implementation);
                $this->impl[$interface] = (object)array(
                    'class'     => $class,
                    'arg'       => $arg,
                    'depend'    => $class->hasMethod(self::DEPENDENCY_METHOD),
                    'complete'  => $class->hasMethod(self::COMPLETION_METHOD),
                    'construct' => self::hasPublicConstructor($implementation)
                );
            } else {
                $this->impl[$interface] = (object)array(
                    'class'      => $implementation,
                    'arg'        => $arg,
                    'complete'   => false,
                    'depend'     => false,
                    'construct'  => false
                );
            }
        }

        public function resolve($interface) {
            if(isset($this->impl[$interface])){
                return $this->impl[$interface];
            } else if($this->scope !== null) {
                return $this->scope->resolve($interface);
            } else {
                $class = new ReflectionClass($interface);
                return (object)array(
                    'class'     => $class,
                    'arg'       => false,
                    'depend'    => $class->hasMethod(self::DEPENDENCY_METHOD),
                    'complete'  => $class->hasMethod(self::COMPLETION_METHOD),
                    'construct' => self::hasPublicConstructor($interface)
                );
            }
        }

        public function __call($interface,$args) {
            $impl = $this->resolve($interface);
            if(get_class($impl->class) === 'ReflectionClass') {
                   if($impl->construct) {
                       $instance = $impl->class->newInstanceArgs($args);
                   } else {
                       $instance = $impl->class->newInstance();
                   }
                   if($impl->depend) {
                       $instance->{self::DEPENDENCY_METHOD}($this,$impl->arg);
                   }
                   if($impl->complete) {
                       $instance->{self::COMPLETION_METHOD}();
                   }
                   return $instance;
            } else if(is_array($impl->class)) {
                return call_user_func($impl->class,$this,$args,$impl->arg);
            } else {
                return $impl->class;
            }
        }

        public function implementsInterface($name,$recursive = true) {
            if(isset($this->impl[$name])) {
                return true;
            } else if ($recursive && $this->scope != null) {
                return $this->scope->implementsInterface($name);
            } else {
                return false;
            }
        }

        public function hasData($name, $recursive = true) {
            if(isset($this->data[$name])) {
                return true;
            } else if ($recursive && $this->scope != null) {
                return $this->scope->hasData($name);
            } else {
                return false;
            }
        }
        
        public function __get($name) {
            if(isset($this->data[$name])){
                return $this->data[$name];
            } else if ($this->scope !== null) {
                return $this->scope->$name;
            } else {
                throw $this->Oxygen_Scope_Exception_ConstantNotSet($name);
            }
        }

        public function query($sql, $params = array(), $wrapper = Oxygen_SQL::STDCLASS) {
            return $this->db->run($sql, $params, $wrapper, $this);
        }

        public function rawQuery($sql) {
            return $this->db->raw($sql);
        }

        public function newScope() {
            return $this->Oxygen_Scope();
        }

        private static $root = null;

        public static function root(){
            return self::$root;
        }

        public function load($class) {
            if(!class_exists($class)) {
                Oxygen_Loader::loadClass($class);
            }
        }

        public function getScope() {
            return $this;
        }
        
        public static function __class_construct(){
           $scope = new Oxygen_Scope();
           $scope->css = $scope->Oxygen_Asset_CSS();
           $scope->js = $scope->Oxygen_Asset_JS();
           $scope->less = $scope->Oxygen_Asset_LESS();
           self::$root = $scope;
        }

        public function __set($name,$value) {
            if($this->hasData($name,false)){
                throw $this->Oxygen_Scope_Exception_ConstantRedeclared($name);
            }
            $this->data[$name] = $value;
        }
    }


?>