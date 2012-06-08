<?

	class Oxygen_Controller_Configurator extends Oxygen_Object {

		private $controller = null;
		private $route = '';
		private $factory = null;
        private $args = null;

		public function __construct($controller, $route) {
			$this->controller = $controller;
			$this->route = $route;
		}

		public function getWrapped($model) {
			$args = $this->args;
			$args[0] = $model;
			return $this->factory->getInstance($args,$this->scope);
		}

		public function on($name) {
			$this->controller->setSectionAlias($name, $this->route);
			return $this;
		}

		public function __call($class, $args) {
			$this->factory = $this->scope->resolve($class);
			$model = isset($args[0])?$args[0]:null;
			$args[0] = null; // Erase model info;
			$this->args = $args;
			$this->controller->add(
				array($this, 'getWrapped'),
				$this->route,
				$model
			);
			return $this;
		}
	}


?>