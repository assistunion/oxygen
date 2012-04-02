<?

	class Oxygen_Controller_Configurator {
		private $controller = null;
		private $route = '';
		public function __construct($route, $controller) {
			$this->controller = $controller;
			$this->route = $route;
		}
		public function __call($class, $args) {
			$model = isset($args[0])?$args[0]:null;
			$iterable = isset($args[1])?$args[1]:null;
			$this->controller->add($class,$route,$model,$iterable);
		}
	}


?>