<?

    class Oxygen_Scope extends Oxygen_Object {

        const FACTORY_REDEFINED = 'Factory {0} is redefined in this scope';
        const DEFAULT_FACTORY = 'Oxygen_Factory_Class';

        const STATIC_CONSTRUCTOR = '__class_construct';

        private $entries = array();
        private $introduced = array();
        protected $parent = null;

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
                if ($constructor->getDeclaringClass()->getName() === $class) {
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
                return $this->entries[$name] = $this->parent->resolve($name, $autoregister);
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
        
        public function __call($name, $args) {
            if(preg_match(self::CALL_REGEXP, $name, $match)) {
                $class = get_class($this);
                if ($match[1] !== '') $class = get_parent_class($this);
                return $this->{$match[2]}($match[3],$args);            
            } else {
                return $this->resolve($name, false)->getInstance($args);
            }
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

        public function setServer($SERVER) {

            $this->SCOPE_SERVER = $SERVER;
            $oxygen  = $this->OXYGEN_ROOT;
            $root    = rtrim(str_replace('/', DIRECTORY_SEPARATOR, $SERVER['DOCUMENT_ROOT']),'/');
            if(isset($SERVER['REQUEST_URI'])) {
                $request = $root . str_replace('/', DIRECTORY_SEPARATOR, $SERVER['REQUEST_URI']);
            } else {
                $request = $oxygen;
            }

            $oxylen  = strlen($oxygen);
            $this->__assert(
                substr($request, 0, $oxylen) === $oxygen,
                'Invalid oxygen path'
            );
            $oxygenRootURI = str_replace(DIRECTORY_SEPARATOR,'/',substr($oxygen, strlen($root)));
            $oxygenURI = str_replace(DIRECTORY_SEPARATOR,'/',substr($request, strlen($oxygen)));
            $q = strpos($oxygenURI,'?');
            if($q !== false) {
                $oxygenPath= substr($oxygenURI,0,$q);
                $qs = substr($oxygenURI,$q+1);
            } else {
                $oxygenPath = $oxygenURI;
                $qs = '';
            }
            $this->DOCUMENT_ROOT = $root;
            $this->OXYGEN_ROOT_URI = $oxygenRootURI;
            $this->OXYGEN_URI = $oxygenURI;
            $this->OXYGEN_PATH_INFO = $oxygenPath;
            $this->QUERY_STRING = $qs;
        }

        public function setStandardAssets() {
            $a = $this->assets;
            $a->register('css','Oxygen_Asset_CSS');
            $a->register('less','Oxygen_Asset_LESS');
            $a->register('js','Oxygen_Asset_JS');
        }

        public function setSerializer() {
            $serializer = $this->serializer = $this->new_Oxygen_Serializer();
            $this->callable('serialize',array($serializer,'add'));
        }

        public static function newRoot($classRoot) {
            $scope = new Oxygen_Scope();
            $scope->__depend($scope);
            $scope->__complete();
            $loader = new Oxygen_Loader($classRoot);
            $loader->__depend($scope);
            $loader->__complete();
            $loader->register();
            $scope->SCOPE_LOADER = $loader;
            $scope->OXYGEN_ROOT = $classRoot;
            $scope->introduce(self::SCOPE_CLASS);
            return $scope;
        }

        public function authenticated() {
            if (!$this->has(SCOPE_AUTHENTICATION_INFO)) {
                $this->SCOPE_AUTHENTICATION_INFO = $this->new_Authenticator;
            };
            return $this->SCOPE_AUTHENTICATION_INFO;
        }  

        public function setCommonHttpPrefs($temp) {
            $this->register('Cache','Oxygen_Cache_File');
            $this->register('Connection','Oxygen_SQL_Connection');
            $this->TMP_DIR = $temp;
            $this->SCOPE_CACHE    = $this->new_Cache($temp);
            $this->setServer($_SERVER);
            $this->setStandardAssets();
            $this->SCOPE_REQUEST  = $_REQUEST;
            $this->SCOPE_COOKIE   = $_COOKIE;
            $this->SCOPE_FILES    = $_FILES;
            $this->SCOPE_ENV      = $_ENV;
            $this->setSerializer();
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