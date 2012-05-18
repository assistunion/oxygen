<?

	define('OXYGEN_ROOT', dirname(dirname(__FILE__)));
	define('OXYGEN_BASE', dirname(__FILE__) . DIRECTORY_SEPARATOR);

	require(OXYGEN_BASE.'scope/scope.class.php');

	function o() {

	}

	class Oxygen {

		private $classPaths = array();

		public function getWritableDir($base, $dir) {
			if(is_array($dir)) $dir = implode(DIRECTORY_SEPARATOR, $dir);
			assert('is_string($dir)');
			if ($dir !== '.') {
				$this->getWritableDir($base, dirname($dir));
				$next = $base . DIRECTORY_SEPARATOR . $dir;
			} else {
				$next = $base;
			}
			if(!file_exists($next)) {
				mkdir($next);
			}
			assert('is_dir($next)');
			assert('is_writable($next)');
			return $next;
		}

		public function __construct($publicDir, $privateDir = false) {
			assert('is_string($publicDir)');
			assert('is_dir($publicDir)');
			assert('is_writable($publicDir)');
			if ($privateDir === false) {
				$privateDir = $publicDir;
			} else {
				assert('is_string($privateDir)');
				assert('is_dir($privateDir)');
				assert('is_writable($privateDir)');
			}
			define('OXYGEN_CSS_ROOT',   $this->getWritableDir($publicDir,  array('oxygen', 'assets', 'css')));
			define('OXYGEN_JS_ROOT',    $this->getWritableDir($publicDir,  array('oxygen', 'assets', 'js')));
			define('OXYGEN_LESS_ROOT',  $this->getWritableDir($publicDir,  array('oxygen', 'assets', 'less')));
			define('OXYGEN_GEN_ROOT',   $this->getWritableDir($privateDir, array('oxygen', 'gen')));
			define('OXYGEN_CACHE_ROOT', $this->getWritableDir($privateDir, array('oxygen', 'cache')));
		}

		public function compile() {
		}

        public static function handleError($errno, $errstr, $errfile, $errline) {
            throw new Exception("Error($errno) $errstr in  $errfile at line $errline");
        }

        public function run($name) {
			try {
				require OXYGEN_ROOT . DIRECTORY_SEPARATOR . $name;
			} catch (Exception $e) {
				$this->put_exception($e);
			}
        }

        public function handleHttpRequest($start) {
			try {
				require OXYGEN_ROOT . DIRECTORY_SEPARATOR . 'start.php';
			} catch (Exception $e) {
				$this->put_exception($e);
			}
        }

        # templates {

        	public function put_exception($ex) {
        		$result =  include OXYGEN_BASE . 'exception.php';
        		return $result;
        	}

        	public function put_exception_trace($trace) {
        		$result = include OXYGEN_BASE . 'exception_trace.php';
        		return $result;
        	}

        	public function put_inspected($value) {
        		$result = include OXYGEN_BASE . 'inspected.php';
        		return $result;
        	}

        # }

	}

	set_error_handler(array('Oxygen', 'handleError'));	

	return new Oxygen(OXYGEN_PUBLIC_DIR, OXYGEN_PRIVATE_DIR);	


?>