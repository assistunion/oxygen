<?

	abstract class Oxygen_Controller extends Oxygen_Collection {

        const PARAM_EXTRACT_REGEXP = '/^_([0-9]+)_([0-9A-Za-z_]+)$/';
        const ARG_EXTRACT_REGEXP = '/([^\/]*)\/?(.*)/';

		const ROUTING_TEMPLATE = '/^(?:{0})?(?P<{1}>.*)$/';
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

		private $configured = false;
		private $children   = array();
		private $routes     = array();
		private $index      = array();
		private $pattern    = '';

        private $rawArgs = '';

		protected $model         = null;
		protected $parent        = null;
        protected $count         = false;

        protected $route = '';
        protected $path = '';

		public function __construct($model = null, $arguments = array()){
			$this->model = $model;
		}

        public static function __class_construct($scope){
            $scope->controller = null; // Global parent controller;
        }

		public function __depend($scope){
			$this->parent = $scope->controller;
            if($this->parent === null) {
                $this->path = $scope->OXYGEN_ROOT_URI;
            }
			$this->scope = $scope->new_Scope();
			$this->scope->controller = $this;
		}

		public function getModel() {
			return $this->model;
		}

        public function go() {
            return $this->path;
        }

		public function routeExists($route) {
            if (preg_match('#^((?:(\.)|(\.\.)|/(.*))(/.*$|$)|$)#',$offset, $match)) {;
                switch($offset){
                case '': return true;
                case '.': return true;
                case '..': return $this->parent !== null;
                default:
                    return $this->parent === null
                        ? $this->routeExists($match[4])
                        : $this->parent['/']->routeExists($match[4])
                    ;
                }
            }
            $this->ensureConfigured();
			preg_match($this->pattern, $route, $match);
			$rest = $match[self::ROUTING_REST];
			return $rest != $route;
		}

		public function offsetExists($route) {
			if(isset($this->children[$route])) return true;
			return $this->routeExists($route);
		}

		public function count() {
            if ($this->count === false) {
                $this->ensureConfigured();
                foreach($this->routes as $router) {
                    $this->count += count($router);
                }
            } else {
                return $this->count;
            }
		}

		public function getIterator() {
			$this->ensureConfigured();
			return $this->new_Oxygen_Controller_Iterator($this->routes, $this->path);
		}

		public function offsetUnset($offset){

		}

        public function __toString() {
            return (string)$this->getModel();
        }

		public function offsetSet($offset, $value) {
			$this->throw_Exception('Please refer to user manual how to configure controllers');
		}

        public function parseArgs(){
        }

        public function shiftRoute($path, $route, $rest){
            preg_match(self::ARG_EXTRACT_REGEXP, $rest, $match);
            $this->rawArgs = $match[1];
            $this->route = $route;
            $this->path = $path . '/' .  $route . $this->rawArgs;
            $this->parseArgs();
            return $match[2];
        }

		public function offsetGet($offset) {
            if (preg_match('#^((?:(\.)|(\.\.)|/(.*))(/.*$|$)|$)#',$offset, $match)) {;
                switch($offset){
                case '': return $this;
                case '.': return $this;
                case '..': return $this->parent === null
                    ? $this->routeMissing('..')
                    : $this->parent
                ;
                default:
                    return $this->parent === null
                        ? $this[$match[4]]
                        : $this->parent['/'][$match[4]]
                    ;
                }
            }
			$this->ensureConfigured();
			preg_match($this->pattern, $offset, $match);
			$rest = $match[self::ROUTING_REST];
			if ($rest === $offset) return $this->routeMissing($offset);
            $router = null;
            $actual = '';
            foreach($this->index as $index => $route) {
                if(isset($match[$index]) && $match[$index] !== '') {
                    $actual = $match[$index];
                    $router = $this->routes[$route];
                    break;
                }
            }
            $this->__assert($router !== null);
            $next = $router[$actual];
            $rest = $next->shiftRoute($this->route, $actual, $rest);
            return ($rest === '')
                ? $next
                : $next[$rest]
            ;
		}

		public function routeMissing($route) {
			$this->__assert(false,
				'Route {0} is missing',
				$route
			);
		}

		private function postConfigure() {
			$regexp = '';
			foreach($this->routes as $route){
				if ($regexp != '') $regexp .= '|';
				$regexp .= '(' . $route->getRegexp() . ')';
			}
			$this->pattern = Oxygen_Utils_Text::format(
				self::ROUTING_TEMPLATE,
				$regexp,
				self::ROUTING_REST
			);
			$this->configured = true;
		}

		public function add($class, $route, $model) {
            $index = count($this->routes) + 1;
            $this->index[$index] = $route;
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