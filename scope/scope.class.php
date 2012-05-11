<?

    class Oxygen_Scope extends Oxygen_Object {

        const FACTORY_REDEFINED = 'Factory {0} is redefined in this scope';

        const STATIC_CONSTRUCTOR = '__class_construct';

        private $entries = array();
        private $introduced = array();
        protected $parent = null;

        private static $defaultEnvironment = array(
            'TMP_DIR' => '/tmp',
            'SERVER'  => array(),
            'SESSION' => array(),
            'COOKIE'  => array(),
            'REQUEST' => array(),
            'FILES'   => array(),
            'ENV'     => array(),
            'GET'     => array(),
            'POST'    => array()
        );
        
        public function throw_Exception($message) {
            throw $this->Exception($message);
        }

        private static $implementations = array(
            'InstanceFactory'  => 'Oxygen_Factory_Instance',
            'CallableFactory'  => 'Oxygen_Factory_Callable',
            'Exception'        => 'Oxygen_Exception',
            'ExceptionWrapper' => 'Oxygen_Exception_Wrapper',
            'Scope'            => 'Oxygen_Scope',
            'AssetManager'     => 'Oxygen_Asset_Manager',
            'Session'          => 'Oxygen_Session',
            'LibraryManager'   => 'Oxygen_Lib',
            // 'ClassFactory'     => 'Oxygen_Factory_Class'  -- REGISTERED AUTOMATICALLY
        );

        public static function __class_construct($scope){
            $scope->null = null;
            $scope->assets = $scope->AssetManager();
            $scope->lib = $scope->LibraryManager();
        }


        public function __depend($scope){
            $this->scope = $this;
            $this->parent = $scope;
        }

        private function __assertFreshName($name){
            $this->__assert(
                !isset($this->entries[$name]),
                self::FACTORY_REDEFINED,
                $name
            );
        }

        public function callable($name, $callable) {
            $this->__assertFreshName($name);
            return $this->entries[$name] = $this->CallableFactory($callable);
        }

        public function __introduce($class) {
            if (self::isOxygenClass($class)
            && !isset($this->introduced[$class])
            ) {
                $this->__introduce(self::getOxygenParentClass($class));
                $constructor = new ReflectionMethod($class, self::STATIC_CONSTRUCTOR);
                $this->introduced[$class] = true;
                if ($constructor->getDeclaringClass()->getName() === $class) {
                    call_user_func(array($class, self::STATIC_CONSTRUCTOR), $this);
                }
            }
        }

        public function registerAll($entries) {
            foreach($entries as $name => $class) {
                $this->register($name, $class);
            }
        }

        public function register($name, $class) {
            $this->__assertFreshName($name);
            return $this->entries[$name] = $this->ClassFactory($class);
        }

        public function instance($name, $instance) {
            $this->__assertFreshName($name);
            return $this->entries[$name] = $this->InstanceFactory($instance);
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
            return $this->resolve($name, true)->getInstance($args, $this);
        }

        // Wraps given $exception into Oxygen_Exception_Wrapper
        // unless $exception is instance of Oxygen_Excpeion itself
        public function __wrapException($exception) {
            if ($exception instanceof Oxygen_Exception) {
                return $exception;
            } else {
                return $this->ExceptionWrapper($exception);
            }
        }

        public function __setPaths() {
            $oxygen  = $this->OXYGEN_ROOT;
            if (isset($this->SERVER['DOCUMENT_ROOT'])) {
                $root = $this->SERVER['DOCUMENT_ROOT'];
                $root = rtrim(str_replace('/', DIRECTORY_SEPARATOR, $root),'/');
            } else {
                $root = '';
            }
            if(isset($this->SERVER['REQUEST_URI'])) {
                $request = $this->SERVER['REQUEST_URI'];
                $request = $root . str_replace('/', DIRECTORY_SEPARATOR, $request);
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

        public function __setAssets() {
            $a = $this->assets;
            $a->register('css','Oxygen_Asset_CSS');
            $a->register('less','Oxygen_Asset_LESS');
            $a->register('js','Oxygen_Asset_JS');
        }
        
        public function __bootstrap($root) {
            
            $this->__depend($this);
            $this->__complete();
            
            $factory = new Oxygen_Factory_Class('Oxygen_Factory_Class');
            $factory->__depend($this);
            $factory->__complete();
            
            $loader = new Oxygen_Loader($root);
            $loader->__depend($this);
            $loader->__complete();
            
            $loader->register();            
            $this->entries['ClassFactory'] = $factory;
            
            $this->registerAll(self::$implementations);
            
            $this->loader = $loader;
            $this->OXYGEN_ROOT = $root;
            
            $this->__introduce(get_class($this));
            $this->__introduce('Oxygen_Factory_Class');
            $this->__introduce('Oxygen_Loader');
            
            return $this;
        }

        public static function newRoot($root) {
            $scope = new Oxygen_Scope();
            return $scope->__bootstrap($root);
        }

        public function __authenticated() {
            if (!$this->has('auth')) {
                $this->auth = $this->Authenticator();
            };
            return $this->auth;
        }

        public function __setEnvironment($env) {
            $env = array_merge(self::$defaultEnvironment, $env);
            $temp = $this->TMP_DIR = $env['TMP_DIR'];
            $this->SERVER  = $env['SERVER'];
            $this->SESSION = $env['SESSION'];
            $this->REQUEST = $env['REQUEST'];
            $this->GET     = $env['GET'];
            $this->POST    = $env['POST'];
            $this->COOKIE  = $env['COOKIE'];
            $this->FILES   = $env['FILES'];
            $this->ENV     = $env['ENV'];

            $this->register('Cache','Oxygen_Cache_File');
            $this->register('Connection','Oxygen_SQL_Connection');

            $this->__setPaths();
            $this->cache = $this->Cache($temp);
            $this->__setAssets();
        }

    }


?>