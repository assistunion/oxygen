<?

	class Oxygen_Session extends Oxygen_Object 
		implements ArrayAccess
	{

		public function put($offset,$value) {
			$_SESSION[$offset] = serialize($value);
		}

		public function get($offset,$default){
			if(!isset($_SESSION[$offset])) return $default;
			else return unserialize($_SESSION[$offset]);
		}

		public function offsetGet($offset){
			return $this->get($offset,false);

		}
		public function offsetSet($offset, $value){
			$this->put($offset,$value);
		}

		public function offsetExists($offset){
			return isset($_SESSION[$offset]);
		}

		public function offsetUnset($offset) {
			unset($_SESSION[$offset]);
		}

	}


?>