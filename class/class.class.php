<?

    define('OXYGEN_MODIFIED',filemtime(__FILE__));
    define('OXYGEN_MODIFIED_OXY',filemtime(dirname(__FILE__).DIRECTORY_SEPARATOR.'oxy.php'));

    include dirname(__FILE__).DIRECTORY_SEPARATOR.'class.oxy.php';
    class Oxygen_Class extends Oxygen_Class_ {

        const OXYGEN_SUFFIX = '_';

        private $ref = null;
        private $name = '';

        private function __construct($name) {
            $this->name = $name;
            if (class_exists($name, false)) {
                $this->ref = new ReflectionClass($name);
            }
        }

        public function __get($name) {
            return $this->ref->getStaticPropertyValue($name);
        }

        public function __call($name, $args) {
            return call_user_func_array(array($this->name, $name), $args);
        }

        public function __toString() {
            return $this->name;
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

        private static function getYaml($path) {
            Oxygen::once('yaml-php/lib/sfYaml.php');
            return sfYaml::load($path);
        }

        private static function getMetaClassFor($name) {
            if(method_exists($name,'__getMetaClass')) {
                return call_user_func(array($name,'__getMetaClass'));
            } else {
                return 'Oxygen_Class';
            }
        }

        private function __compileClass(
            $directory,
            $destination,
            $className,
            $yaml,
            $path,
            $both
        ) {
            $files = glob($directory . DIRECTORY_SEPARATOR . '*');
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
                $file = '/' . $path . '/' . $file;
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
                $defaultTarget = 'html';
                if ($ref) try {
                    $defaultTarget = $ref->getStaticPropertyValue('__' . $name . '_target');
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
                    'access'  => isset($yamlView['access']) ? $yamlView['access'] : $defaultAccess,
                    'args'    => isset($yamlView['args']) ? $yamlView['args'] : array(),
                    'info'    => isset($yamlView['info']) ? $yamlView['info'] : ($name . ' view'),
                    'target'  => isset($yamlView['target']) ? $yamlView['target'] : $defaultTarget,
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
                if($view->target !== 'html') continue;
                foreach ($assetExt as $type => $ext) {
                    $method = 'asset_' . $name . '_' . $type;
                    $file = isset($all[$ext][$name])
                        ? $all[$ext][$name]
                        : false
                    ;
                    $asset = (object)array(
                            'name'   => $name,
                            'file'   => $file,
                            'ext'    => $ext,
                            'type'   => $type,
                            'method' => $method,
                            'access' => $view->access
                    );
                    if ($file !== false) {
                        // asset is defined in current class
                        // display it both in defs and usages
                        $assets[] = $view->assets[$method] = $asset;
                    } else if ($ancestor_access[$name] === 'private') {
                        // asset is not inherited and file is not present;
                        if ($view->access !== 'private') {
                            //asset should be introduced since view is inheritable
                            //we should create an empty entries in defs
                            //and also generate usage;
                            $assets[] = $view->assets[$method] = $asset;
                        }
                    } else {
                        //asset is simply inherited, so we'll
                        //generate only it's usage
                        $view->assets[$method] = $asset;
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
                'meta' => $yaml['meta'],
                'extends' => $ancestor,
                'scope' => $scope,
                'oxyName' => $className . self::OXYGEN_SUFFIX,
                'views' => $views,
                'assets' => $assets,
                'path' => $path
            );

            try {
                file_put_contents($destination, $this->get_oxy($class));
            } catch (Exception $e) {
                print $e;
            }
            include_once $destination;
            if (class_exists($className, false)) {
                $this->ref = new ReflectionClass($className);
                return $this;
            } else {
                return null;
            }

        }

        private static function modificationTime($path) {
            try {
                return filemtime($path);
            } catch (Oxygen_FileNotFoundException $e) {
                return 0;
            }
        }

        private static $classes = array();

        public static function make($name) {
            if(isset(self::$classes[$name])) {
                return self::$classes[$name];
            }
            list($path, $last, $base) = self::parseName($name);
            $common      = $path . DIRECTORY_SEPARATOR . $last;
            $normalPath  = OXYGEN_ROOT . $common  . '.class.php';
            if (!$base) {
                try {
                    include_once $normalPath;
                    if (!class_exists($name, false)) return null;
                    $meta = self::getMetaClassFor($name);
                    return self::$classes[$name] = new $meta($name);
                } catch (Oxygen_FileNotFoundException $e) {

                }
            }
            $directory   = OXYGEN_ROOT . $path;
            $n = self::modificationTime($directory);
            if ($n === 0) return null; // CLASS NOT FOUND;

            $yamlPath    = OXYGEN_ROOT . $common . '.class.yml';
            $compilePath = OXYGEN_COMPILE_ROOT . $common . '.oxy.php';
            $compileDir  = OXYGEN_COMPILE_ROOT . $path;

            $y = self::modificationTime($yamlPath);
            $g = self::modificationTime($compileDir);
            $compile = self::modificationTime($compilePath) < max($n,$g,$y, OXYGEN_MODIFIED, OXYGEN_MODIFIED_OXY);

            if ($compile) {
                $yaml = ($y === 0)
                    ? array('extends'=>'Oxygen_Controller')
                    : self::getYaml($yamlPath)
                ;
                $ancestor = $yaml['extends'];
                if (!isset($yaml['meta'])) {
                    $yaml['meta'] = $ancestor
                        ? self::getMetaClassFor($ancestor)
                        : 'Oxygen_Class'
                    ;
                }
                $meta = $yaml['meta'];
                if ($g === 0) self::getWritableDir(OXYGEN_COMPILE_ROOT, $path);
                assert('is_writable($compileDir)');
                $result = new $meta($name);
                return self::$classes[$name] = $result->__compileClass(
                    $directory,
                    $compilePath,
                    $base ? substr($name,0,-strlen(self::OXYGEN_SUFFIX)) : $name,
                    $yaml,
                    str_replace(DIRECTORY_SEPARATOR, '/', $path),
                    !$base
                );
            }
            include_once $compilePath;
            if (!class_exists($name, false)) return null;
            $meta = self::getMetaClassFor($name);
            return self::$classes[$name] = new $meta($name);
        }

        public function __getPublicInstanceMethod($name) {
            $m = $this->ref->getMethod($name);
            if ($m->isStatic()) throw new ReflectionException("$name is static");
            if (!$m->isPublic()) throw new ReflectionException("$name is not public");
            return $m;
        }

        public function __composeAsset($asset) {

            $type        = $asset['type'];
            $name        = $asset['name'];
            $ext         = $asset['ext'];

            $include     = dirname(__FILE__)
                         . DIRECTORY_SEPARATOR
                         . $type
                         . '.php'
            ;
            $destination = OXYGEN_ASSET_ROOT
                         . DIRECTORY_SEPARATOR
                         . $type
                         . $this->__oxygen_path
                         . DIRECTORY_SEPARATOR
                         . $name
                         . $ext
            ;
            $source      = OXYGEN_ROOT
                         . DIRECTORY_SEPARATOR
                         . $asset['path']
                         . DIRECTORY_SEPARATOR
                         . $name
                         . $ext
            ;
            try {
                $d = filemtime($destination);
                $s = filemtime($source);
                $i = filemtime($include);
                $t = $this->__lastMetaModified;
                $m = OXYGEN_MODIFIED;
                if ($d >= max($s, $i, $t, $m)) return array($d, $destination);
            } catch (Oxygen_FileNotFoundException $e) {
                Oxygen::getWritableDir(dirname($destination));
            }
            $css = 'css-' . $this . '-' . $name;
            try {
                unset($m);
                unset($t);
                unset($d);
                unset($s);
                unset($i);
                ob_start();
                include $include;
                $result = ob_get_clean();
            } catch(Exception $e) {
                ob_end_clean();
                $result = '/* ' . $e->getMessage() . ' */';
            }
            file_put_contents($destination, $result);
            return array(time(), $destination);
        }

        private static function __compileAssetBundle($type, $source, $bundlePath) {
            switch ($type) {
                case 'css':
                case 'less':
                    Oxygen::once('lessphp/lessc.inc.php');
                    $less = new lessc();
                    try {
                    file_put_contents($bundlePath, $less->parse($source));
                    } catch (Exception $e) {
                        file_put_contents($bundlePath, '/* ' . $e->getMessage() . ' */');
                    }
                    break;
                case 'js':
                default:
                    file_put_contents($bundlePath, $source);
                    break;
            }

        }

        public static function __compileAssets($assetList) {
            $result = array();
            foreach ($assetList as $type => $assets) {
                $fileNames = array();
                $last = 1;
                foreach($assets as $asset) {
                    list($time, $fileName) = $asset['class']->__composeAsset($asset);
                    $last = max($last, $time);
                    $fileNames[] = $fileName;
                }
                $bundle = md5(implode(':',$fileNames));
                $bundlePath = OXYGEN_ASSET_ROOT
                    . DIRECTORY_SEPARATOR . $type
                    . DIRECTORY_SEPARATOR . $bundle
                    . '.' . $type;
                $m = self::modificationTime($bundlePath);
                if ($last > $m) {
                    ob_start();
                    foreach($fileNames as $fileName) {
                        $s = substr($fileName, strlen(OXYGEN_ASSET_ROOT)+1);
                        echo '/* ' . $s .  " */\n";
                        readfile($fileName);
                        echo "\n";
                    }
                    self::__compileAssetBundle($type, ob_get_clean(), $bundlePath);
                }
                $result[$type] = array(
                    'path' => $bundlePath,
                    'url' => OXYGEN_ROOT_URL . '/' . $bundle . '.' . $type
                );
            }
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
                $result = include OXYGEN_ROOT . '/oxygen/class/oxy.php';
            }

    }

?>