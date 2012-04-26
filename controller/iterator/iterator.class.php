<?

	class Oxygen_Controller_Iterator extends Oxygen_Object implements Iterator {

		private $routers = null;
        private $path = '';
		private $internal = null;
		private $key = null;

		public function __construct($routers, $path) {
			$this->routers = $routers;
            $this->path = $path;
		}

		public function valid() {
			if ($this->internal === null) return false;
			if ($this->internal->valid()) return true;
			while ($r = next($this->routers)) {
				$this->internal = $r->getIterator();
				if($this->internal->valid()) return true;
			}
			$this->internal = null;
			return false;
		}

		public function current() {
			$key = $this->key();
			if(!$this->scope->tryOffsetCache($key,$current)) {
	            $current = $this->internal->current();
    	        $current->shiftRoute($this->path, $key,'');
    	        $this->scope->setOffsetCache($key,$current);
			}
			return $current;
		}

		public function next() {
			$this->key = null;
			$this->internal->next();
		}

		public function key() {
			if ($this->key !== null) {
				return $this->key;
			} else {
				return $this->key = $this->internal->key();
			}
		}

		public function rewind(){
			reset($this->routers);
			if($r = current($this->routers)){
				$this->internal = $r->getIterator();
			} else {
				$this->internal = null;
			}
			$this->key = null;
		}
	}

?>