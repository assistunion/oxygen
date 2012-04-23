<?

	class Oxygen_Controller_Configurator {
		private $controller = null;
		private $route = '';
		public function __construct($route, $controller) {
			$this->controller = $controller;
			$this->route = $route;
		}
		private $factory = null;
		private $args = null;

		public function getWrapped($model) {
			$args = $this->args;
			$args[0] = $model;
			return $this->factory->getInstance($args);
		}

		public function __call($class, $args) {
			$this->factory = $this->controller->scope->resolve($class);
			$model = isset($args[0])?$args[0]:null;
			$args[0] = null; // Erase model info;
			$this->args = $args;
			return $this->controller->add(array($this,'getWrapped'),$this->route,$model);
		}
	}


?>