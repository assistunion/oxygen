<?

	abstract class Oxygen_Controller extends Oxygen_Collection {

        const PARAM_EXTRACT_REGEXP = '/^_([0-9]+)_([0-9A-Za-z_]+)$/';

		const ROUTING_TEMPLATE = '/^(?:{0})(?P<{1}>.*)$/';
		const ROUTING_REST = '__';

		const INVALID_CLASS_RETRIEVER       = 'Invalid class retriever';
		const ROUTE_PARAM_REDEFINED         = 'Route param redefined';
		const INVALID_PARAM_TYPE            = 'Invalid route parameter type';
		const CONTROLLER_ALREADY_CONFIGURED = 'Controller is already configured';

		const UNWRAP_METHOD = 'getModel';

		private $visualChild   = null;
		private $logicalChild  = null;
		private $visualParent  = null;
		private $logicalParent = null;
		private $configured    = false;
		private $children      = array();
		private $routes        = array();
		private $index         = array();
		private $routingRegexp = '';

		protected $model         = null;
		protected $parent        = null;

		public function __construct($model = null, $arguments = array()){
			$this->model = $model;
		}

        public static function __class_construct($scope){
            $scope->controller = null; // Parent controller;
        }

		public function __depend($scope){
			$this->parent = $scope->controller;
			$this->scope = $scope->new_Scope();
			$this->scope->controller = $this;
		}

		public function getModel() {
			return $this->model;
		}

		public function routeExists($route){
			$this->ensureConfigured();
			preg_match($this->routing, $route, $match);
			$rest = $match[self::ROUTING_REST];
			return $rest != $route;
		}

		public function evalRoute($route){
			$this->ensureConfigured();
			preg_match($this->routing, $route, $match);
			$rest = $match[self::ROUTING_REST];
			if ($rest == $route) {
				return $this->routeMissing($route);
			} else {
				$params = array();
				$route = null;
				foreach($match as $name => $value) {
					if($value !== '' && preg_match(self::PARAM_EXTRACT_REGEXP, $name, $m)) {
						$route = $this->routes[$m[1]];
						$params[$m[2]] = $value;
					}
				}
				$next = $route->get($params);
			}
		}

		public function offsetExists($route) {
			if(isset($this->children[$route])) return true;
			return $this->routeExists($route);
		}

		public function count() {

		}

		public function getIterator() {
			$this->ensureConfigured();

		}

		public function offsetUnset($offset){

		}

		public function offsetSet($offset, $value) {
			$this->throw_Exception('Please refer to user manual how to configure controllers');
		}

		public function offsetGet($offset) {
			$this->ensureConfigured();
			if(isset($this->routes))
		}

		public function childMissing($route) {
		}

		private function postConfigure() {
			$regexp = '';
			foreach($this->routes as $route){
				if ($regexp != '') $regexp .= '|';
				$regexp .= '(' . $route->regex . ')';
			}
			$this->routing = Oxygen_Utils_Text::format(
				self::ROUTING_TEMPLATE,
				$regexp,
				self::ROUTING_REST
			);
			$this->configured = true;
		}

		public function add($class, $route, $model) {
			return $this->routes[$route] = $this->new_Oxygen_Router(
				$route, $model, $class, self::UNWRAP_METHOD
			);
		}

		public function ensureConfigured() {
			if(!$this->configured){
				$routes = $this->new_Oxygen_Controller_Routes($this);
				$this->configure($routes);
			    $this->postConfigure();
			}
		}

		public abstract function configure($routes);

	}

?>