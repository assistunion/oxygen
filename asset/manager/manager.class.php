<?

    class Oxygen_Asset_Manager extends Oxygen_Object {

        const ASSET_REDEFINED = 'Asset {0} is already defined';
        const ASSET_UNDEFINED = 'Asset {0} is undefined';

        private $assets = array();
        private $added = array();

        public function register($name, $class){
            $this->__assert(!isset($this->assets[$name]), self::ASSET_REDEFINED, $name);
            $this->assets[$name] = $this->new_($class);
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