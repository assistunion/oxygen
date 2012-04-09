<?

	class Oxygen_Controller extends Oxygen_Object
		implements ArrayAccess, IteratorAggregate, Countable
	{

        const PARAM_EXTRACT_REGEXP = '/^_([0-9]+)_([0-9A-Za-z_]+)$/';

		const ROUTING_TEMPLATE = '/^(?:{0})(?P<{1}>.*)$/';
		const ROUTING_REST = '__';

		const INVALID_CLASS_RETRIEVER       = 'Invalid class retriever';
		const ROUTE_PARAM_REDEFINED         = 'Route param redefined';
		const INVALID_PARAM_TYPE            = 'Invalid route parameter type';
		const CONTROLLER_ALREADY_CONFIGURED = 'Controller is already configured';

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
			parent::__construct();
			$this->model = $model;
			$this->parent = $parent;
		}

		public function __depend($scope, $arg){
			$this->scope = $scope->newScope();
			$this->scope->controller = $this;
		}


		public function routeExists($route){
			$this->ensureConfigured();
			preg_match($this->routing, $route, $match);
			$rest = $match[self::REST_NAME];
			return $rest != $route;
		}

		public function evalRoute($route){
			$this->ensureConfigured();
			preg_match($this->routing, $route, $match);
			$rest = $match[self::REST_NAME];
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
			$this->throwException('Please refer to user manual how to configure controllers');
		}

		public function offsetGet($offset) {
			if (!$this->configured) {
				return $this->scope->Oxygen_Controller_Configurator($offset,$this);
			} else {
			}
		}

		public function childMissing($route) {
		}

		private function postConfigure() {
			$regexp = '';
			foreach($this->routes as $route){
				if ($regexp != '') $regexp .= '|';
				$regexp .= '(' . $route->regexp . ')';
			}
			$this->routing = Oxygen_Utils_Text::format(
				self::ROUTING_REGEXP_TEMPLATE,
				$regexp,
				self::REST_NAME
			);
			$this->configured = true;
		}

		public function ensureConfigured() {
			if(!$this->configured){
				$this->configure();
			    $this->postConfigure();
			}
		}

		public function add($class, $route, $model) {
			if(!$this->configured) {
				$index = count($this->roures);
				$this->routes[] = $this->scope->Oxygen_Route(
					$index,	$class,	$route,	$model
				);
				$this->index[$route] = $index;
			} else {
				$this->throwException(self::CONTROLLER_ALREADY_CONFIGURED);
			}
		}

		public function configure() {

		}
	}

?>