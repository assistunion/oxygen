<?

    define('CLASS_PATH', dirname(dirname(dirname(__file__))));

    class Oxygen_Loader {

        const CLASS_PATH = CLASS_PATH;

        const OBJECT_CLASS    = 'Oxygen_Object';
        const EXCEPTION_CLASS = 'Oxygen_Exception';
        const SCOPE_CLASS     = 'Oxygen_Scope';

        const UPPERCASE_FILE     = '.uppercase';
        const CLASS_EXTENSION    = '.class.php';
        const BASE_EXTENSION     = '.base.php';
        const TEMPLATE_EXTENSION = '.php';

        const BASE_SUFFIX        = '__';

        const SAFE_FILENAME      = '/^[A-Za-z0-9_\-]+(\.[A-Za-z0-9_\-]+)*$/';
        const TEMPLATE           = '/^[A-Za-z0-9_\-]+$/';

        const CALL_REGEXP = '/^(throw_|new_)(.*)$/';

        const STATIC_CONSTRUCTOR = '__class_construct';

        const CLASS_NOT_RESOLVED = 'Class is not resolved for path {0}';
        const CLASS_NOT_FOUND = 'Class {0} is not found';
        const RESOURCE_NOT_FOUND = 'Resource {0} is not found for class {1}';

        private static $path_cache = array();
        private static $scope = null;

        public static function __class_construct(){
            self::loadClass(self::SCOPE_CLASS);
            self::$scope = Oxygen_Scope::root();
        }

        public static function new_($class, $args) {
            return self::$scope->resolve($class)->getInstance($args);
        }

        public static function throw_($class, $args) {
            throw self::new_($class, $args);
        }

        public static function __assert(
            $condition,
            $message = false,
            $arg0 = '', $arg1 = '', $arg2 = '', $arg3 = '', $arg4 = ''
        ) {
            if (!$condition) {
                self::throw_Exception(
                    Oxygen_Utils_Text::format(
                        ($message === false ? $message : Oxygen_Object::ASSERTION_FAILED),
                        $arg0, $arg1, $arg2, $arg3, $arg4
                    )
                );
            }
        }

        public function __call($name, $args) {
            if(preg_match(self::CALL_REGEXP, $method, $match)){
                $method = $match[1];
                return self::$method($match[2], $args);
            } else {
                self::throw_Exception(
                    Oxygen_Object::UNKNOWN_METHOD,
                    __CLASS__,
                    $method
                );
            }
        }

        public static function correctCase($class) {
            return self::classFor(dirname(self::pathFor($class)));
        }

        public static function loadClass($class) {
            $path = self::pathFor($class);
            ob_start();
            try {
                require_once $path;
                $ex = null;
            } catch (Exception $e) {
                $ex = $e;
            } /* finally */ {
                echo trim(ob_get_clean());
                if ($ex !== null) throw $ex;
            }
            self::__assert(
                class_exists($class,false),
                self::CLASS_NOT_FOUND,
                $class
            );
            if (method_exists($class, self::STATIC_CONSTRUCTOR)) {
                call_user_func(array($class, self::STATIC_CONSTRUCTOR));
            }
        }

        public static function classFor($path) {
            $path = realpath($path);
            self::__assert(
                substr($path, 0, $len = strlen(self::CLASS_PATH)) === self::CLASS_PATH,
                self::CLASS_NOT_RESOLVED, $path
            );
            $parts = explode(DIRECTORY_SEPARATOR, substr($path, $len));
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
        }

        public static function pathFor(
            $class,
            $resource = false,
            $required = true
        ) {

            $key = $class . '::' . $resource;

            // return from cache if found there
            if (isset(self::$path_cache[$key])) {
                return self::$path_cache[$key];
            }

            list($dir, $name, $base) = self::parse($class);

            // looking for a class
            if($resource === false) {
                $trial_path = $dir
                    . DIRECTORY_SEPARATOR
                    . $name
                    . ($base ? self::BASE_EXTENSION : self::CLASS_EXTENSION)
                ;
                self::__assert(
                    !$required || file_exists($trial_path),
                    self::CLASS_NOT_FOUND,
                    $class
                );
                return self::$path_cache[$key] = $trial_path;
            }

            // A bit of security
            self::__assert(
                preg_match(self::SAFE_FILENAME, $resource),
                self::RESOURCE_NOT_FOUND,
                $resource,
                $class
            );

            $trial_path = $dir . DIRECTORY_SEPARATOR . $resource;

            if (file_exists($trial_path)) {
                return self::$path_cache[$key] = $trial_path;
            }

            // Small inheritance hack:
            // Let system think that EXCEPTION_CLASS
            // is inherited from OBJECT_CLASS (not from Exception)
            $parent = $class === self::EXCEPTION_CLASS
                ? self::OBJECT_CLASS
                : get_parent_class($class)
            ;

            return self::$path_cache[$key] = self::pathFor(
                $parent, $resource, $required
            );
        }

        private static function parse($class) {
            $len = max(0, strlen($class) - strlen(self::BASE_SUFFIX));
            $base = substr($class, $len) === self::BASE_SUFFIX;
            if ($base) $class = substr($class, 0, $len);
            $parts = explode('_', $class);
            $path = '';
            foreach ($parts as $part){
                if (preg_match("/^[A-Z0-9]+$/", $part)) {
                    $last = strtolower($part);
                } else {
                    preg_match_all("/[A-Z][a-z0-9]+/", $part, $match);
                    $subparts = $match[0];
                    self::__assert(
                        count($subparts) > 0,
                        self::CLASS_NOT_FOUND,
                        $class
                    );
                    $separator = '';
                    $last = '';
                    foreach($subparts as $subpart) {
                        $last .= $separator . strtolower($subpart);
                        $separator = '_';
                    }
                }
                $path .= DIRECTORY_SEPARATOR . $last;
            }
            $path = self::CLASS_PATH . $path;
            return array($path, $last, $base);
        }

    }

    function __autoload($class){
        Oxygen_Loader::loadClass($class);
    }



?>
