<?

    define('OXYGEN_METACLASS_MODIFIED', filemtime(__FILE__));

    class Oxygen_Class {

        private $ref = null;
        private $name = '';
        private $less = null;
        public function __construct($name) {
            $this->name = $name;
            $this->ref = new ReflectionClass($name);
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

        public function __getPublicInstanceMethod($name) {
            $m = $this->ref->getMethod($name);
            if ($m->isStatic()) throw new ReflectionException("$name is static");
            if (!$m->isPublic()) throw new ReflectionException("$name is not public");
            return $m;
        }

        public function compile($asset) {

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
                $m = OXYGEN_METACLASS_MODIFIED;
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
    }

?>