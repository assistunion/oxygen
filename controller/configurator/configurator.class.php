<?

	class Oxygen_Controller_Configurator extends Oxygen_Object {

		private $route = '';
		private $factory = null;
        private $args = null;

		public function __construct($route) {
			$this->route = $route;
		}

		public function getWrapped($model) {
			$args = $this->args;
			$args[0] = $model;
			return $this->factory->getInstance($args,$this->scope);
		}

		public function __call($class, $args) {
			$this->factory = $this->scope->resolve($class);
			$model = isset($args[0])?$args[0]:null;
			$args[0] = null; // Erase model info;
			$this->args = $args;
			return $this->scope->add(
				array($this, 'getWrapped'),
				$this->route,
				$model
			);
		}
	}


?>