<?

	class Oxygen_Scope {

		private $entries = array();
		private $callables = array();

		protected $scope = null;

		public function __get($name) {
			if (isset($this->entries[$name])) {
				return $this->entries[$name];
			} else if ($this->scope !== null) {
				return $this->entries[$name] = $this->scope->__get($name);
			} else {
				throw new Exception("Undeclared entry $name");
			}
		}

		public function __set($name, $value) {
			if (isset($this->entries[$name])) {
				throw $this->Exception("Duplicate entry $name");
			} else {
				$this->entries[$name] = $value;
			}
		}

		public function __defined($name, &$out) {
			if (isset($this->entries[$name])) {
				$out = $this->entries[$name];
				return true;
			} else if ($this->scope !== null) {
				return $this->scope->__defined($name, $out);
			} else {
				return false;
			}
		}
		public function __resolve($name) {
			if (isset($this->callables[$name])) {
				return $this->callables[$name];
			} else if (isset($this->entries[$name])) {
				$def = $this->entries[$name];
				if (is_string($def)) {
					return $this->callables[$name] = array(true, new Oxygen_Reflector($def));
				} else if (is_callable($def)) {
					return $this->callables[$name] = array(false, $def);
				} else {
					throw $this->Exception("Entry $name is not callable nor instantiable");
				}
			} else if ($this->scope !== null) {
				list($new, $def) = $this->callables[$name] = $this->scope->__resolve($name);
				if ($new) {
					$this->entries[$name] = $def->name;
				} else {
					$this->entries[$name] = $def;
				}
			} else {
				throw $this->Exception("Undeclared entry $name");
			}
		}

		public function __call($name, $args) {
			list($new, $def) = $this->__resolve($name);
			return $new
				? $def->newInstance($args, $this)
				: call_user_func_array($def, $args)
			;
		}
	}

?>