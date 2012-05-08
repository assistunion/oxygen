<?

    class Oxygen_Asset_Manager extends Oxygen_Object {

        const ASSET_REDEFINED = 'Asset {0} is already defined';
        const ASSET_UNDEFINED = 'Asset {0} is undefined';

        private $assets = array();
        private $added = array();

        public function getIcon($name){
            $relative = 'silk-icons/icons/' . $name . '.png';
            $path = $this->scope->lib->path($relative);
            $this->__assert(file_exists($path),'Icon not found');
            return $this->scope->lib->url($relative);
        }

        public function register($name, $class){
            $this->__assert(!isset($this->assets[$name]), self::ASSET_REDEFINED, $name);
            $this->assets[$name] = $this->new_($class);
        }

        public function handled($path) {
            if (!preg_match('#^/([a-f0-9]{32})\.(js|css|less)$#',$path, $match)) return false;
            $key = $match[1];
            $type = $match[2];
            if(isset($this->scope->cache[$key])){
                $last_modified = 'Tuesday, 1 Mar 2012 00:00:00 GMT';
                $etag = '"'.$key.'"';
                header("Last-Modified: $last_modified");
                header("ETag: $etag");
                $if_modified_since = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ?
                    stripslashes($_SERVER['HTTP_IF_MODIFIED_SINCE']) :
                    false;
                $if_none_match = isset($_SERVER['HTTP_IF_NONE_MATCH']) ?
                    stripslashes($_SERVER['HTTP_IF_NONE_MATCH']) :
                    false;
                if (!$if_modified_since && !$if_none_match
                    || $if_none_match && $if_none_match != $etag
                    || $if_modified_since && $if_modified_since != $last_modified
                ) {
                    switch($type){
                        case 'css':
                        case 'less': header('Content-Type: text/css'); break;
                        case 'js': header('Content-Type: text/javascript'); break;
                    }
                    $this->scope->cache->echoValue($key);
                } else {
                    header('HTTP/1.0 304 Not Modified');
                }
            } else {
                header('HTTP/1.0 503 Please refresh the WebPage');
            };
            return true;
        }

        public function add($call) {
            $key = implode('::', $call);
            if (isset($this->added[$key])) return;
            $this->added[$key] = true;
            foreach ($this->assets as $asset) {
                $asset->add($call, $key);
            }
        }

        public function __get($name){
            $this->__assert(isset($this->assets[$name]), self::ASSET_UNDEFINED, $name);
            return $this->assets[$name];
        }
    }

?>