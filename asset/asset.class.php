<?
    class Oxygen_Asset extends Oxygen_Object {

        public $path_list = array();
        public $url_list = array();
        public $idx = array();
        public $cache = array();
        public $pipeline = false;
        public $process = false;
        private $ext = '';

        public function __construct($ext) {
            parent::__construct();
            $this->ext = $ext;
        }
        
        public function extra($url) {
            $this->path_list[] = $url;
            $this->url_list[] = $url;
        }

        // Calculate hash code on basis of
        // paths and their modification times
        public function getHashCode() {
            $toHash = $this->path_list; // copy;
            foreach($this->path_list as $path) {
                $toHash []= filemtime($path);
            }
            $str = implode('', $toHash);
            return sha1($str);
        }

        public function add($class,$resource) {
            $key = $class . '::' . $resource;
            if(isset($this->cache[$key])) return;
            $this->cache[$key] = true;
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