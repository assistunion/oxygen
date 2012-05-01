<?

	abstract class Oxygen_Cache extends Oxygen_Object implements ArrayAccess {

		public abstract function load($key);
		public abstract function store($key, $value);
		public abstract function exists($key);
		public abstract function remove($key);
		public abstract function storeAll($key, $array);
        public abstract function serialize($key, $value);
        public abstract function deserialize($key);

		public function offsetGet($key){
			return $this->load($key);
		}

		public function offsetSet($key, $value) {
			return $this->store($key, $value);
		}

		public function offsetExists($key) {
			return $this->exists($key);
		}

		public function offsetUnset($key) {
			return $this->remove($key);
		}

	}


?>