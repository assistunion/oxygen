<?


    class Oxygen_Loader extends Oxygen_Object {

        const UPPERCASE_FILE     = '.uppercase';
        const CLASS_EXTENSION    = '.class.php';
        const BASE_EXTENSION     = '.base.php';
        const TEMPLATE_EXTENSION = '.php';

        const BASE_SUFFIX        = '__';

        const SAFE_FILENAME      = '/^[A-Za-z0-9_\-]+(\.[A-Za-z0-9_\-]+)*$/';
        const TEMPLATE           = '/^[A-Za-z0-9_\-]+$/';

        const CALL_REGEXP = '/^(throw_|new_)(.*)$/';

        const CLASS_NOT_RESOLVED = 'Class is not resolved for path {0}';
        const CLASS_NOT_FOUND = 'Class {0} is not found';
        const RESOURCE_NOT_FOUND = 'Resource {0} is not found for class {1}';
        
        private $classRoot = '';
        private $path_cache = array();
        private $loaded = array();
        
        public function __construct($classRoot) {
            $this->classRoot = $classRoot;
        }

        public function correctCase($class) {
            return $this->classFor(dirname($this->pathFor($class)));
        }
        
        public function register() {
            spl_autoload_register(array($this, 'loadClass'));
        }

        public function loadClass($class) {
            if (isset($this->loaded[$class])) return;
            if (!class_exists($class, false)) {
                $path = $this->pathFor($class);
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
                $this->__assert(
                    class_exists($class,false),
                    self::CLASS_NOT_FOUND,
                    $class
                );
            }
            $this->scope->introduce($class);
            $this->loaded[$class] = true;
        }

        public function classFor($path) {
            $path = realpath($path);
            $this->__assert(
                substr($path, 0, $len = strlen($this->classRoot)) === $this->classRoot,
                self::CLASS_NOT_RESOLVED, $path
            );
            $parts = explode(DIRECTORY_SEPARATOR, substr($path, $len));
            $dir = $this->classRoot;
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

        public function pathFor(
            $class,
            $resource = false,
            $required = true
        ) {
            // Exit from get_parent_class recursion for resources (see below)
            if ($class === false) return false;

            $key = $class . '::' . $resource;

            // return from cache if found there
            if (isset($this->path_cache[$key])) {
                return $this->path_cache[$key];
            }

            list($dir, $name, $base) = $this->parse($class);

            // looking for a class
            if($resource === false) {
                $trial_path = $dir
                    . DIRECTORY_SEPARATOR
                    . $name
                    . ($base ? self::BASE_EXTENSION : self::CLASS_EXTENSION)
                ;
                $this->__assert(
                    !$required || file_exists($trial_path),
                    self::CLASS_NOT_FOUND,
                    $class
                );
                return $this->path_cache[$key] = $trial_path;
            }

            // A bit of security
            $this->__assert(
                preg_match(self::SAFE_FILENAME, $resource),
                self::RESOURCE_NOT_FOUND,
                $resource,
                $class
            );

            $trial_path = $dir . DIRECTORY_SEPARATOR . $resource;

            if ($this->pathExists($trial_path)) {
                return $this->path_cache[$key] = $trial_path;
            }

            return $this->path_cache[$key] = $this->pathFor(
                self::getOxygenParentClass($class), $resource, $required
            );
        }
        
        private function pathExists($path) {
            return file_exists($path);
        }

        private function parse($class) {
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
                    $this->__assert(
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
            $path = $this->classRoot . $path;
            return array($path, $last, $base);
        }
    }

?>
