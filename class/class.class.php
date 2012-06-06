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

        public function js($source) {
            return $source;
        }

        public function less($source) {
            if ($this->less === null) {
                require_once Oxygen::pathFor('oxygen/lib/lessphp/lessc.inc.php');
                $this->less = new lessc;
            }
            return $this->less->parse($source);
        }

        public function css($source) {
            return $this->less($source);
        }

        public function compile($source, $destination, $css, $type, $time) {
            $include = dirname(__FILE__) . DIRECTORY_SEPARATOR . $type . '.php';
            try {
                $d = filemtime($destination);
                $s = filemtime($source);
                $i = filemtime($include);
                $m = OXYGEN_METACLASS_MODIFIED;
                if ($d >= max($s, $i, $time, $m)) return $d;
            } catch (Oxygen_FileNotFoundException $e) {
                Oxygen::getWritableDir(dirname($destination));
            }
            try {
                unset($d); 
                unset($s);
                unset($i);
                ob_start();
                include $include;
                $result = $this->{$type}(ob_get_clean());
            } catch(Exception $e) {
                ob_end_clean();
                $result = '/* ' . $e->getMessage() . ' */';
            }
            file_put_contents($destination, $result);
            return time();
        }
    }

?>