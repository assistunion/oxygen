<?

	class Oxygen_Controller_Iterator extends Oxygen_Object implements Iterator {

		private $routers = null;
        private $path = '';
		private $internal = null;

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
            $current = $this->internal->current();
            $current->shiftRoute($this->path, $this->key(),'');
			return $current;
		}

		public function next() {
			$this->internal->next();
		}

		public function key() {
			return $this->internal->key();
		}

		public function rewind(){
			reset($this->routers);
			if($r = current($this->routers)){
				$this->internal = $r->getIterator();
			} else {
				$this->internal = null;
			}
		}
	}

?>