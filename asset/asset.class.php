<?
    abstract class Oxygen_Asset extends Oxygen_Object {

        public $path_list = array();
        public $url_list = array();
        public $idx = array();
        public $cache = array();
        public $pipeline = false;
        public $process = false;
        private $ext = '';
        private $hash = false;
        private $compiled = false;

        const URL_REGEX = "|^https?://|";

        public function __construct($ext) {
            parent::__construct();
            $this->ext = $ext;
        }
        
        public function extra($url) {
            $this->path_list[] = $url;
            $this->url_list[] = $url;
            $this->invalidate();
        }

        protected function isUrl($path){
            return preg_match(self::URL_REGEX, $path);
        }

        // Calculate hash code on basis of
        // paths and their modification times
        public function getHashCode() {
            if($this->hash === false) {
                $toHash = $this->path_list; // copy;
                foreach($this->path_list as $path) {
                    if(!self::isUrl($path)){
                        $toHash []= filemtime($path);
                    }
                }
                $str = implode('', $toHash);
                $this->hash = sha1($str);
            }
            return $this->hash;
        }

        protected function processOne($path) {
            return file_get_contents($path);
        }

        protected function process($source) {
            return $source;
        }

        public function compile() {
            if(!$this->compiled) {
                $hash  = $this->getHashCode();
                $cache = $this->scope->cache;
                if(!isset($cache[$hash])) {
                    $source = array();
                    foreach ($this->path_list as $path) {
                        $source[] = $this->processOne($path);
                    }
                    $source = implode("\n",$source);
                    $cache[$hash] = $this->process($source);
                }
                $this->compiled = true;
            }
            return $hash;
        }

        protected function invalidate() {
            $this->hash = false;
            $this->compiled = false;
        }


        public function add($class,$resource) {
            $key = $class . '::' . $resource;
            if(isset($this->cache[$key])) return;
            $this->cache[$key] = true;
            $this->invalidate();
            $p = Oxygen_Loader::pathFor($class,$resource,$this->ext);
            if($p !== false) {
                if(!isset($this->idx[$p])){
                    $this->idx[$p] = true;
                    $this->path_list[]= $p;
                    $this->url_list[]= Config::toUrl($p);
                }
            }
        }
    }
?>