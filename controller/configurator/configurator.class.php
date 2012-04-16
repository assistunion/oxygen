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
			return $this->controller->add($class,$this->route,$model);
		}
	}


?>