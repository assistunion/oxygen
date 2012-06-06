<?

    define('OXYGEN_MODIFIED',filemtime(__FILE__));
    define('OXYGEN_MODIFIED_OXY',filemtime(dirname(__FILE__).DIRECTORY_SEPARATOR.'oxy.php'));

    function o() {

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

        const OXYGEN_SUFFIX = '_';

        public $scope;

        private static $stack = array();
        private static $sp = 0;

        public static function push($instance, $view) {
            self::$stack[self::$sp++] = (object)array(
                'instance' => $instance,
                'view' => $view,
                'component' => false
            );
        }

        public static function pop() {
            $result = self::$stack[--self::$sp];
            self::$stack[self::$sp] = null;
            return $result;
        }

        public static function open() {
        }

        public static function close() {
        }

        public static function pathFor($path) {
            return OXYGEN_ROOT . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
        }

        public static function requireFile($relative) {
            return require_once self::pathFor($relative);
        }

        public function compileAssets() {
            $result = array();
            foreach ($this->scope->assets as $type => $assets) {
                $names = array();
                $last = 0;
                foreach($assets as $css => $asset) {
                    $time = $asset['class']->compile(
                        $asset['source'],
                        $asset['destination'],
                        $css,
                        $type,
                        $asset['last']
                    );
                    $last = max($last, $time);
                    $names[] = $asset['destination'];
                }
                $bundle = md5(implode(':',$names));
                $bundale_path = OXYGEN_ASSET_ROOT 
                    . DIRECTORY_SEPARATOR . $type 
                    . DIRECTORY_SEPARATOR . $bundle
                    . '.' . $type;
                try {
                    $m = filemtime($bundale_path);
                } catch (Oxygen_FileNotFoundException $ex) {
                    $m = -1;
                }
                if ($last > $m) {
                    $f = fopen($bundale_path,'w');
                    ftruncate($f, 0);
                    foreach($names as $source) {
                        $s = substr($source,strlen(OXYGEN_ASSET_ROOT)+1);
                        fwrite($f, '/* ' . $s .  " */\n");
                        fwrite($f, file_get_contents($source));
                    }
                    fclose($f);
                }
                $result[$type] = $bundale_path;
            }
            return $result;
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

        private static function once($lib) {
            $path = dirname(__FILE__).implode(DIRECTORY_SEPARATOR,explode('/','/lib/'.$lib));
            require_once $path;
        }

        private static function parseName($class) {
            $len = max(0, strlen($class) - strlen(self::OXYGEN_SUFFIX));
            $base = substr($class, $len) === self::OXYGEN_SUFFIX;
            if ($base) $class = substr($class, 0, $len);
            $parts = explode('_', $class);
            $path = '';
            foreach ($parts as $part){
                if (preg_match("/^[A-Z0-9]+$/", $part)) {
                    $last = strtolower($part);
                } else {
                    preg_match_all("/[A-Z][a-z0-9]+/", $part, $match);
                    $subparts = $match[0];
                    if (count($subparts)==0) throw new Oxygen_ClassNotFoundException($class);
                    $separator = '';
                    $last = '';
                    foreach($subparts as $subpart) {
                        $last .= $separator . strtolower($subpart);
                        $separator = '_';
                    }
                }
                $path .= DIRECTORY_SEPARATOR . $last;
            }
            return array($path, $last, $base);
        }

        private static function modificationTime($path) {
            try {
                return filemtime($path);
            } catch (Oxygen_FileNotFoundException $e) {
                return 0;
            }
        }

        private static function getYaml($path) {
            self::once('yaml-php/lib/sfYaml.php');
            return sfYaml::load($path);
        }

        private function compileItem($normalDir, $genPath, $className, $yaml, $path, $both) {
            $files = glob($normalDir . DIRECTORY_SEPARATOR . '*');
            $all = array(
                '.class.php' => array(),
                '.class.yml' => array(),
                '.oxy.php'   => array(),
                '.php'       => array(),
                '.css'       => array(),
                '.js'        => array(),
                '.less'      => array(),
                '.yml'       => array(),
                '.json'      => array(),
                '*'          => array()
            );

            $pattern = '/(?:' . implode('|', array_map('preg_quote', array_keys($all))) . ')$/';
            foreach ($files as $file) {
                $name = basename($file);
                if (preg_match($pattern, $name, $m)) {
                    $ext = $m[0];
                    $name = substr($name, 0, -strlen($ext));
                    $all[$ext][$name] = $file;
                } else {
                    $all['*'][$name] = $file;
                }
            }

            $ancestor = isset($yaml['extends'])
                ? $yaml['extends']
                : null
            ;

            $ref = $ancestor
                ? new ReflectionClass($ancestor)
                : null
            ;            
            $ancestor_access = array();

            # ===  VIEWS ====
            $yamlViews = isset($yaml['views'])
                ? $yaml['views']
                : array()
            ;
            $views = array();
            foreach ($all['.php'] as $name => $file) {
                $defaultAccess = 'private';
                if ($ref) try {
                    $m = $ref->getMethod('put_' . $name);
                    if ($m->isPublic()) $defaultAccess = 'public';
                    else if ($m->isProtected()) $defaultAccess = 'protected';
                    else $defaultAccess = 'private';
                } catch (ReflectionException $re) {
                }
                $ancestor_access[$name] = $defaultAccess;
                if(isset($yamlViews[$name])) {
                    $yamlView = $yamlViews[$name];
                    $defaultAccess = 'public';
                } else {
                    $yamlView = array();
                }
                $views[$name] = (object)array(
                    'relPath' => str_replace(DIRECTORY_SEPARATOR,'/',$path . DIRECTORY_SEPARATOR . basename($file)),
                    'absPath' => str_replace(DIRECTORY_SEPARATOR,'/',$file),
                    'path'    => str_replace(DIRECTORY_SEPARATOR,'/',$path),
                    'access'  => isset($yamlView['access']) ? $yamlView['access'] : $defaultAccess,
                    'args'    => isset($yamlView['args']) ? $yamlView['args'] : array(),
                    'info'    => isset($yamlView['info']) ? $yamlView['info'] : ($name . ' view'),
                    'assets'  => array()
                );
            }

            $assetExt = array(
                'css'  => '.css',
                'less' => '.less',
                'js'   => '.js'
            );

            # ===  ASSETS =====
            $assets = array();
            foreach ($views as $name => &$view) {
                foreach ($assetExt as $type => $ext) {
                    $method = 'asset_' . $name . '_' . $type;
                    if (isset($all[$ext][$name])) {
                        $file = $all[$ext][$name];
                        $assets[] = $view->assets[$method] = (object)array(
                            'override' => true,
                            'name'     => $name,
                            'relPath'  => str_replace(DIRECTORY_SEPARATOR,'/',$path . DIRECTORY_SEPARATOR . basename($file)),
                            'absPath'  => $file,
                            'baseName' => basename($file),
                            'type'     => $type,
                            'genPath'  => OXYGEN_ASSET_ROOT . DIRECTORY_SEPARATOR 
                                        . $type . $path . DIRECTORY_SEPARATOR
                                        . basename($file),
                            'access'   => $view->access
                        );
                    } else if ($ancestor_access[$name] === 'private') {
                        if ($view->access !== 'private') {
                            $assets[] = $view->assets[$method] = (object)array(
                                'override' => false,
                                'name'    => $name,
                                'type'    => $type,
                                'method'  => $method,
                                'access'   => $view->access
                            );
                        }
                    } else {
                        $view->assets[$method] = (object)array(
                            'override' => false,
                            'name'    => $name,
                            'type'    => $type,
                            'method'  => $method,
                            'baseName' => $name . $ext,
                            'access'   => $view->access
                        );
                    }
                }
            }

            # === SCOPE ===

            $scope = isset($yaml['scope'])
                ? $yaml['scope']
                : false;
            ;

            $class = (object)array(
                'both' => $both,
                'name' => $className,
                'extends' => $ancestor,
                'scope' => $scope,
                'oxyName' => $className . self::OXYGEN_SUFFIX,
                'views' => $views,
                'assets' => $assets
            );

            try {
                $generated = $this->get_oxy($class);
                $f = fopen($genPath,'w+');
                if (flock($f,LOCK_EX)) {
                    ftruncate($f, 0);
                    fwrite($f, $generated);
                    flock($f, LOCK_UN);
                    echo "compiled";
                }
                fclose($f);
            } catch (Exception $e) {
                print $e;
            }
        }

        public function loadClass($class) {
            list($path, $last, $base) = self::parseName($class);
            $common     = $path . DIRECTORY_SEPARATOR . $last;
            $normalPath = OXYGEN_ROOT . $common  . '.class.php';
            $yamlPath   = OXYGEN_ROOT .  $common . '.class.yml';
            $compilePath    = OXYGEN_COMPILE_ROOT . $common . '.oxy.php';
            $normalDir  = OXYGEN_ROOT . $path;
            $compileDir     = OXYGEN_COMPILE_ROOT . $path;
            if (!$base) {
                try {
                    return include_once $normalPath;
                } catch (Oxygen_FileNotFoundException $e) {

                }
            }
            $n = self::modificationTime($normalDir);
            if ($n === 0) return; // CLASS NOT FOUND;
            $y = self::modificationTime($yamlPath);
            $g = self::modificationTime($compileDir);
            $compile = self::modificationTime($compilePath) < max($n,$g,$y,OXYGEN_MODIFIED,OXYGEN_MODIFIED_OXY);
            if ($compile) {
                if ($y === 0) $yaml = array('extends'=>'Oxygen_Controller');
                else $yaml = self::getYaml($yamlPath);
                if ($g === 0) self::getWritableDir(OXYGEN_COMPILE_ROOT, $path);
                assert('is_writable($compileDir)');
                $this->compileItem(
                    $normalDir,
                    $compilePath,
                    $base ? substr($class,0,-strlen(self::OXYGEN_SUFFIX)) : $class,
                    $yaml,
                    $path,
                    !$base
                );
            }
            return include_once $compilePath;
        }

		public function __construct($privateCacheRoot, $compileRoot = false, $assetRoot = false) {
            $startedAt = time();
            set_error_handler('Oxygen::handleError');
            spl_autoload_register(array($this,'loadClass'));
            define('OXYGEN_ROOT', dirname(dirname(__FILE__)));
            define('OXYGEN_BASE', dirname(__FILE__) . DIRECTORY_SEPARATOR);
            assert('is_string($privateCacheRoot)');
            assert('is_dir($privateCacheRoot)');
            assert('is_writable($privateCacheRoot)');
            define('OXYGEN_CACHE_ROOT', $privateCacheRoot);
            if ($compileRoot === false) {
                $compileRoot = OXYGEN_ROOT;
            }
            assert('is_string($compileRoot)');
			assert('is_dir($compileRoot)');
			assert('is_writable($compileRoot)');
            define('OXYGEN_COMPILE_ROOT',$compileRoot);
			if ($assetRoot === false) {
				$assetRoot = $this->getWritableDir($compileRoot, array('assets'));
			} else {
				assert('is_string($assetRoot)');
				assert('is_dir($assetRoot)');
				assert('is_writable($assetRoot)');
			}
            define('OXYGEN_ASSET_ROOT', $assetRoot);
            define('OXYGEN_CSS_ROOT',   $this->getWritableDir(OXYGEN_ASSET_ROOT,  array('css')));
            define('OXYGEN_LESS_ROOT',   $this->getWritableDir(OXYGEN_ASSET_ROOT,  array('less')));
            define('OXYGEN_JS_ROOT',   $this->getWritableDir(OXYGEN_ASSET_ROOT,  array('js')));
            $scope = new Oxygen_Scope();
            $scope->Oxygen = array($this,'itself');
            $scope->o = $this;
            $scope->startedAt = $startedAt;
            $this->scope = $scope;
            $this->scope->__set(self::$__oxygenScope);
		}

		public function compile() {
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
                $scope->assets = new ArrayObject(array(
                    'css'  => array(),
                    'less' => array(),
                    'js'   => array()
                ));
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

            # oxy -----------------------------------------------------------

            public function get_oxy($class) {
                ob_start(); try {$this->put_oxy($class);}
                catch (Exception $_) {}
                if(isset($_)) {ob_end_clean(); throw $_;}
                return ob_get_clean();
            }

            public function put_oxy($class) {
                $result = include OXYGEN_BASE . 'oxy.php';
            }

        # }

	}





?>