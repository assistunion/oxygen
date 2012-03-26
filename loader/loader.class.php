<?

    define('CLASS_PATH', dirname(dirname(dirname(__file__))));

    class Oxygen_Loader {

        const OBJECT_CLASS = 'Oxygen_Object';
        const EXCEPTION_CLASS = 'Oxygen_Exception';
        const CLASS_PATH = CLASS_PATH;
        const UPPERCASE_FILE = '.uppercase';
        const CLASS_EXTENSION = '.class.php';
        const BASE_EXTENSION = '.base.php';
        const BASE_SUFFIX = '__';
        const SAFE_FILENAME = '/^[A-Za-z0-9_\-]+(\.[A-Za-z0-9_\-]+)*$/';
        const TEMPLATE = '/^[A-Za-z0-9_\-]+$/';
        const TEMPLATE_EXTENSION = '.php';
        const STATIC_CONSTRUCTOR = '__class_construct';

        private static $path_cache = array();

        private static function throwClassNotFound($class) {
            $scope = Oxygen_Scope::root();
            throw $scope->Oxygen_Loader_Exception_ClassNotFound($class);
        }
        
        private static function throwResourceNotFound($class,$resource) {
            $scope = Oxygen_Scope::root();
            throw $scope->Oxygen_Loader_Exception_ResourceNotFound($class,$resource);
        }
        
        private static function throwClassNotResolved($path) {
            $scope = Oxygen_Scope::root();
            throw $scope->Oxygen_Exception('Class-file can not be resolved for path='.$path);
        }
        
        public static function correctCase($class) {
            return self::classFor(dirname(self::pathFor($class)));
        }

        public static function loadClass($class) {
            $path = self::pathFor($class);
            ob_start();
            require_once $path;
            echo $loader = trim(ob_get_clean());
            if(!class_exists($class,false)) {
                self::throwClassNotFound($class);
                
            } else {
                if(method_exists($class,self::STATIC_CONSTRUCTOR)) {
                    call_user_func(array($class,self::STATIC_CONSTRUCTOR));
                }
            }
        }
        
        public static function classFor($path) {
            $path = realpath($path);
            if(substr($path,0,$len = strlen(self::CLASS_PATH)) == self::CLASS_PATH) {
                $parts = explode(DIRECTORY_SEPARATOR,substr($path, $len));
                $dir = self::CLASS_PATH;
                $class = '';
                foreach($parts as $part) {
                    if($class !== '') $class .= '_';
                    $dir .= DIRECTORY_SEPARATOR . $part;
                    $uppercase = $dir . DIRECTORY_SEPARATOR . self::UPPERCASE_FILE;
                    $chunks = explode('_',$part);
                    $cls = '';
                    foreach($chunks as $chunk) {
                        $cls .= ucfirst($chunk);
                    }
                    if(file_exists($uppercase)) {
                        $cls = strtoupper($cls);
                    }
                    $class .= $cls;
                }
                return $class;
            } else {
                self::throwClassNotResolved($path);
            }
        }

        public static function pathFor($class,$resource = false, $ext = false, $must_exist = true){
            if(!$class){
                if($ext !== false) return false;
                else self::throwResourceNotFound($class,$resource);
            }
            if($resource === false) {
                $key = $class;
            } elseif($ext === false) {
                $key = $class . '::' . $resource;
            } else {
                $key = $class . '::' . $resource . $ext;
            }
            if(!isset(self::$path_cache[$key])) {
                list($dir,$name,$base) = self::parse($class);
                if($resource === false) {
                    $trial_path = $dir 
                        . DIRECTORY_SEPARATOR 
                        . $name 
                        . ($base ? self::BASE_EXTENSION : self::CLASS_EXTENSION)
                    ;
                    if($must_exist && !file_exists($trial_path)) {
                        self::throwClassNotFound($class);
                    }
                    self::$path_cache[$key] = $trial_path;
                } else {
                    
                    if(!preg_match(self::SAFE_FILENAME, $resource)) {
                        self::throwResourceNotFound($class,$resource);
                    } 
                    $trial_path = $dir . DIRECTORY_SEPARATOR . $resource;
                    if($ext !== false) {
                        $trial_path .= $ext;
                    } else if(preg_match(self::TEMPLATE,$resource)){
                        $trial_path .= self::TEMPLATE_EXTENSION;
                    }
                    if(file_exists($trial_path)) {
                        self::$path_cache[$key] = $trial_path;
                    } else {
                        try {
                            // Small inheritance hack:
                            if ($class === self::EXCEPTION_CLASS) {
                                // Let system think that EXCEPTION_CLASS 
                                // is inherited from OBJECT_CLASS (not from Exception)
                                $parent = self::OBJECT_CLASS;
                            } else {
                                $parent = get_parent_class($class);
                            }
                            self::$path_cache[$key] = self::pathFor($parent,$resource, $ext, $must_exist);
                        } catch (Oxygen_Loader_Exception_ResourceNotFound $e) {
                            self::throwResourceNotFound($class,$resource);
                        }
                    }
                    
                }
            }
            return self::$path_cache[$key];
            
        }

        private static function parse($class) {
            $len = max(0,strlen($class)-strlen(self::BASE_SUFFIX));
            $base = substr($class,$len)==self::BASE_SUFFIX;
            if($base) $class = substr($class,0,$len);
            $parts = explode('_',$class);
            $path = '';
            foreach($parts as $part){
                if(preg_match("/^[A-Z0-9]+$/",$part)) {
                    $last = strtolower($part);
                } else {
                    preg_match_all("/[A-Z][a-z0-9]+/",$part,$match);
                    $subparts = $match[0];
                    if(count($subparts)==0) {
                        self::throwClassNotFound($class);
                    } else {
                        $separator = '';
                        $last = '';
                        foreach($subparts as $subpart) {
                            $last .= $separator . strtolower($subpart);
                            $separator = '_';
                        }
                    }
                }
                $path .= DIRECTORY_SEPARATOR . $last;
            }

            $path = self::CLASS_PATH . $path;
            return array($path,$last,$base);
        }

    }

    function __autoload($class){
        Oxygen_Loader::loadClass($class);
    }



?>
