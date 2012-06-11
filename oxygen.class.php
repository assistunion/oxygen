<?

    function o($tag = 'div', $data=array()) {
        if(is_array($tag)) {
            $data = $tag;
            $tag = 'div';
        }
        if($tag{0}=='/'){
            Oxygen::close();
        } else {
            Oxygen::open($tag, $data);
        }
    }

    function _($translatable) {
        return $translatable;
    }

    class Oxygen_FileNotFoundException extends Exception {
        public function __construct($fileName) {
            parent::__construct("File $fileName is not found");
        }
    }

    class Oxygen_ClassNotFoundException extends Exception {
        public function __construct($className) {
            parent::__construct("Class $className is not found");
        }
    }

    class Oxygen_AssertionFailed extends Exception {
        public function __construct($assertion, $file, $line) {
            parent::__construct("Assertion failed: $assertion in $file at line $line");
        }
    }

    require "oxygen.oxy.php";

	class Oxygen extends Oxygen_ {

        public $assets = null;
        public $og = null;

        private static $mime = array(
            'css'  => 'Content-Type: text/css; charset=UTF-8',
            'less' => 'Content-Type: text/css; charset=UTF-8',
            'js'   => 'Content-Type: text/javascript; charset=UTF-8'
        );

        public $scope;

        private static $stack = array();
        private static $sp = 0;

        public static function push($instance, $view) {
            self::$stack[self::$sp++] = (object)array(
                'instance' => $instance,
                'view' => $view,
                'component' => false,
                'css' => 'css-' . get_class($instance) . '-' . $view,
                'stack' => array(),
                'sp' => 0
            );
        }

        public static function pop() {
            $result = self::$stack[--self::$sp];
            self::$stack[self::$sp] = null;
            return $result;
        }

        public static function open($tag = 'div', $data = array()){
            if(is_array($tag)) {
                $data = $tag;
                $tag = 'div';
            }
            preg_match_all('/(([A-Za-z_]+)="([^"]+)")/', $tag, $attrs);
            preg_match_all('/\.([A-Za-z_0-9\-]+)/', $tag, $classes);
            $classes = $classes[1];
            preg_match('/^[A-Za-z:_0-9]+/', $tag, $tagm);
            $tag  = $tagm[0];
            $attrs = $attrs[1];
            $call = self::$stack[self::$sp-1];
            $call->component = true;
            $data['remote'] = $call->instance->go();
            $data['view'] = $call->view;
            $call->stack[$call->sp++] = $tag;
            echo '<' . $tag . ' class="' . $call->css;
            foreach($classes as $class) {
                echo ' '. $class;
            }
            echo '"';
            foreach ($attrs as $a) {
                echo ' '.$a;
            }
            if(is_array($data)) {
                foreach ($data as $key => $value) {
                    if(!is_string($value)){
                        $value = json_encode($value);
                    }
                    echo ' data-' . $key . '="' . htmlspecialchars($value) . '"';
                }
            }
            echo '>';
        }

        public static function close() {
            $call = self::$stack[self::$sp-1];
            $tag = $call->stack[--$call->sp];
            echo '</' . $tag . '>';
        }

        public static function closeAll() {
            $call = self::$stack[self::$sp-1];
            while ($call->sp > 0) {
                $tag = $call->stack[--$call->sp];
                echo '</' . $tag . '>';
            }
        }

        public static function pathFor($path) {
            return OXYGEN_ROOT . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
        }

        public static function requireFile($relative) {
            return require_once self::pathFor($relative);
        }


		public static function getWritableDir($base, $dir = '.') {
			if(is_array($dir)) $dir = implode(DIRECTORY_SEPARATOR, $dir);
			assert('is_string($dir)');
			if ($dir !== '.') {
				$next = $base . DIRECTORY_SEPARATOR . $dir;
			} else {
				$next = $base;
			}
            if(!is_writable($next) || !is_dir($next)) {
                self::getWritableDir(dirname($next));
                if(!file_exists($next)) {
                    mkdir($next);
                }
            }
			return $next;
		}

        public static function once($lib) {
            $path = dirname(__FILE__).implode(DIRECTORY_SEPARATOR,explode('/','/lib/'.$lib));
            require_once $path;
        }

        public function loadClass($class) {
            return Oxygen_Class::make($class);
        }



        private static function serveAssets($type, $bundle) {
            try {
                $bundle_path = OXYGEN_ASSET_ROOT
                . DIRECTORY_SEPARATOR . $type
                . DIRECTORY_SEPARATOR . $bundle
                . '.' . $type;
                $m = filemtime($bundle_path);
                $etag = '"'.$m.'"';
                if(isset($_SERVER['HTTP_IF_NONE_MATCH'])){
                    if ($etag === $_SERVER['HTTP_IF_NONE_MATCH']) {
                        header('HTTP/1.1 304 Not modified');
                        exit;
                    }
                }
                header("Etag: $etag");
                header(self::$mime[$type]);
                readfile($bundle_path);
            } catch (Oxygen_FileNotFoundException $e) {
                header('HTTP/1.1 404 Not found');
            }
        }

		public function __construct($privateCacheRoot, $compileRoot = false, $assetRoot = false) {

            $startedAt = time();

            define('OXYGEN_ROOT', dirname(dirname(__FILE__)));

            self::requireFile('oxygen/class/class.class.php');

            set_error_handler('Oxygen::handleError');
            spl_autoload_register(array($this,'loadClass'));

            define('OXYGEN_BASE', dirname(__FILE__) . DIRECTORY_SEPARATOR);
            define('OXYGEN_ICONS_ROOT', OXYGEN_BASE . 'lib/silk-icons/icons');

            assert('is_string($privateCacheRoot)');
            self::getWritableDir($privateCacheRoot);
            define('OXYGEN_CACHE_ROOT', $privateCacheRoot);

            if ($compileRoot === false) {
                $compileRoot = OXYGEN_ROOT;
            }

            assert('is_string($compileRoot)');
            self::getWritableDir($compileRoot);
            define('OXYGEN_COMPILE_ROOT',$compileRoot);

			if ($assetRoot === false) {
				$assetRoot = self::getWritableDir($compileRoot, array('assets'));
			}

			assert('is_string($assetRoot)');
            self::getWritableDir($assetRoot);
            define('OXYGEN_ASSET_ROOT', $assetRoot);

            define('OXYGEN_CSS_ROOT',  $this->getWritableDir(OXYGEN_ASSET_ROOT,  array('css')));
            define('OXYGEN_LESS_ROOT', $this->getWritableDir(OXYGEN_ASSET_ROOT,  array('less')));
            define('OXYGEN_JS_ROOT',   $this->getWritableDir(OXYGEN_ASSET_ROOT,  array('js')));

            $requset = isset($_SERVER['REQUEST_URI'])
                ? $_SERVER['REQUEST_URI']
                : ''
            ;

            $scope = new Oxygen_Scope();

            $scope->DOCUMENT_ROOT = $DOCUMENT_ROOT = isset($_SERVER['DOCUMENT_ROOT'])
                ? $_SERVER['DOCUMENT_ROOT']
                : OXYGEN_ROOT
            ;

            $HTTP_HOST = $scope->HTTP_HOST = isset($_SERVER['HTTP_HOST'])
                ? $_SERVER['HTTP_HOST']
                : 'localhost'
            ;

            $is_https = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) === 'on';

            $dr = str_replace(DIRECTORY_SEPARATOR, '/', $DOCUMENT_ROOT);
            $or = str_replace(DIRECTORY_SEPARATOR, '/', OXYGEN_ROOT);
            $uri = substr($or, strlen($dr));

            $requset = isset($_SERVER['REQUEST_URI'])
                ? $_SERVER['REQUEST_URI']
                : ''
            ;

            //SERVE ASSETS;
            if (preg_match("#^$uri/([0-9a-f]{32})\.(css|less|js)$#", $requset, $match)) {
                $bundle = $match[1];
                $type  = $match[2];
                self::serveAssets($type, $bundle);
                exit;
            }

            define('OXYGEN_ROOT_URL',
                ($is_https ? 'https://' : 'http://') . $HTTP_HOST . $uri
            );
            $scope->Oxygen    = array($this,'itself');
            $scope->o         = $this;
            $scope->_SESSION  = $scope->Oxygen_Session();
            $scope->_SERVER   = $_SERVER;
            $scope->_POST     = $_POST;
            $scope->_GET      = $_GET;
            $scope->_ENV      = $_ENV;
            $scope->_FILES    = $_FILES;
            $scope->startedAt = $startedAt;
            $this->scope      = $scope;
            $this->scope->__set(self::$__oxygenScope);
            $this->loadClass('Oxygen_');
            $this->og = $scope->og = $scope->Oxygen_OpenGraph(
                'Oxygen', 'Oxygen powered website', '', 'oxygen, php, web, framework'
            );
            $this->assets = $scope->assets = new ArrayObject(array(
                'css'  => array(),
                'less' => array(),
                'js'   => array()
            ));
		}

        public function itself() {
            return $this;
        }

        public static function handleError($code, $message, $file, $line) {
            if (preg_match("/^include\(([^)]+)\)/", $message, $m)) {
                throw new Oxygen_FileNotFoundException($m[1]);
            } else if (preg_match("/^file[am]time\(\).*\s([^\s]+)$/", $message, $m)) {
                throw new Oxygen_FileNotFoundException($m[1]);
            } else if (preg_match("/Assertion &quot;(.*)&quot; failed/",$message, $m)){
                throw new Oxygen_AssertionFailed($m[1],$file, $line);
            } else {
                throw new Exception("Error($code) $message in  $file at line $line");
            }
        }

        public function run($name) {
			try {
                $o = $this;
                $scope = $this->scope;
				include OXYGEN_ROOT . DIRECTORY_SEPARATOR . $name;
			} catch (Exception $e) {
				$this->put_exception($e);
			}
            exit;
        }

        public function handleHttpRequest($start) {
			try {
				include OXYGEN_ROOT . DIRECTORY_SEPARATOR . 'start.php';
			} catch (Exception $e) {
				$this->put_exception($e);
			}
        }

        # templates {

            # exception -------------------------------------------------

            public function get_exception($ex) {
                ob_start(); try { $this->put_exception($ex); }
                catch (Exception $_) {}
                if(isset($_)) {ob_end_clean(); throw $_;}
                return ob_get_clean();
            }

            public function put_exception($ex) {
        		$result =  include OXYGEN_BASE . 'exception.php';
        		return $result;
        	}

            # exception.trace -----------------------------------------------

            public function get_exception_trace($trace) {
                ob_start(); try {$this->put_exception_trace($ex);}
                catch (Exception $_) {}
                if(isset($_)) {ob_end_clean(); throw $_;}
                return ob_get_clean();
            }

        	public function put_exception_trace($trace) {
        		$result = include OXYGEN_BASE . 'exception_trace.php';
        		return $result;
        	}

            # inspected -----------------------------------------------------

            public function get_inspected($value) {
                ob_start(); try {$this->put_inspected($value);}
                catch (Excpetion $_) {}
                if(isset($_)) {ob_end_clean(); throw $_;}
                return ob_get_clean();
            }

        	public function put_inspected($value) {
        		$result = include OXYGEN_BASE . 'inspected.php';
        		return $result;
        	}

        # }

	}





?>