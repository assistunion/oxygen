<?

	class Oxygen_Controller_Iterator extends Oxygen_Object implements Iterator {

		private $controller = null;
		private $routes = null;
		private $internal = null;
		private $key = null;

		public function __construct($controller) {
			$this->controller = $controller;
			$this->routes = $controller->routes;
		}

		public function valid() {
			if ($this->internal === null) return false;
			if ($this->internal->valid()) return true;
			while ($r = next($this->routes)) {
				$this->internal = $r->getIterator();
                $this->internal->rewind();
				if($this->internal->valid()) return true;
			}
			$this->internal = null;
			return false;
		}

		public function current() {
			$key = $this->key();
			if(!$this->controller->tryOffsetCache($key,$current)) {
	            $current = $this->internal->current();
    	        $current->setPath($this->controller, $key,'');
    	        $this->controller->setOffsetCache($key,$current);
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
			reset($this->routes);
			if($r = current($this->routes)){
				$this->internal = $r->getIterator();
                $this->internal->rewind();
			} else {
				$this->internal = null;
			}
			$this->key = null;
		}
	}

?>