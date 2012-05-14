<?
    abstract class Oxygen_Asset extends Oxygen_Object {

        public $list = array();
        public $added = array();
        public $pipeline = false;
        public $process = false;
        private $ext = '';
        private $hash = false;
        private $compiled = false;

        public $cache = false;
        private $loader = false;
        private $manager = null;

        const URL_REGEX = "|^https?://|";
        const REMOTE_KEY_TEMPLATE = 'cached-url-{0}';

        const LOCAL_RESOURCE = 0;
        const REMOTE_RESOURCE = 1;

        const UNKNOWN_ASSET_TYPE = 'Unknown asset type';

        public function __construct($ext) {
            $this->ext = $ext;
        }

      public function __complete() {
            $this->cache = $this->scope->cache;
            $this->loader = $this->scope->loader;
            $this->manager = $this->scope->assets;
        }


        public function addRemote($url) {
            if(isset($this->list[$url])) return;
            $assets = array();
            $assets[$this->ext] = array(self::REMOTE_RESOURCE, $url);
            $this->list[$url] = (object)array(
                'assets'    => $assets,
                'component' => false,
                'class'     => false,
                'name'      => false
            );
            $this->invalidate();
        }

        // Calculate hash code on basis of
        // paths and their modification times
        public function getHashCode() {
            if($this->hash === false) {
                $toHash = array();
                $list = $this->list;
                ksort($list);
                foreach($list as $key=>$item) {
                    $toHash[] = $key;
                    list($type, $path) = $item->assets[$this->ext];
                    if($type == self::LOCAL_RESOURCE) {
                        $toHash[] = filemtime($path);
                    }
                }
                $str = implode(':', $toHash);
                $this->hash = md5($str);
            }
            return $this->hash;
        }

        protected function getCachedUrlContent($url) {
            $cache = $this->cache;
            $key = Oxygen_Utils_Text::format(self::REMOTE_KEY_TEMPLATE,$url);
            if(!isset($cache[$key])){
                return $cache[$key] = file_get_contents($url);
            } else {
                return $cache[$key];
            }
        }

        protected function processOne($item) {
            list($type, $path) = $item->assets[$this->ext];
            if($type === self::REMOTE_RESOURCE){
                return $this->getCachedUrlContent($path);
            } else {
                return file_get_contents($path);
            }
        }

        protected function process($source) {
            return $source;
        }

        public function compile() {
            if(!$this->compiled) {
                $hash  = $this->getHashCode();
                $cache = $this->cache;
                if(!isset($cache[$hash])) {
                    $source = array();
                    foreach ($this->list as $key => $asset) {
                        $source[] = $this->processOne($asset);
                    }
                    $source = implode("\n",$source);
                    $cache[$hash] = $this->process($source);
                }
                $this->compiled = true;
            }
            return $this->hash;
        }

        public function getCompiledUri() {
            return $this->scope->OXYGEN_ROOT_URI . '/' . $this->compile() . $this->ext;
        }

        protected function invalidate() {
            $this->hash = false;
            $this->compiled = false;
        }

        public function add($call, $key = false) {
            if ($key === false) {
                $key = $this->manager->getKey($call);
            }
            if(isset($this->added[$key])) return;
            $this->added[$key] = true;
            $path = $this->loader->pathFor($call->class, $call->name . $this->ext);
            if($path !== false) {
                $key = $path . '::' . $call->component;
                if (!isset($this->list[$key])){
                    $call->assets[$this->ext] = array(self::LOCAL_RESOURCE, $path);
                    $this->list[$key] = $call;
                    $this->invalidate();
                }
            }
        }
    }
?>