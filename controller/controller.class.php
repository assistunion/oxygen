<?

	class Oxygen_Controller extends Oxygen_Scope
        implements Countable, ArrayAccess, IteratorAggregate
    {

        const PARAM_EXTRACT_REGEXP = '/^_([0-9]+)_([0-9A-Za-z_]+)$/';
        const ARG_EXTRACT_REGEXP = '/([^\/]*)\/?(.*)/';

		const ROUTING_TEMPLATE = '/^(?:{0})?(?P<{1}>.*)$/';
		const ROUTING_REST = '__';

		const INVALID_CLASS_RETRIEVER       = 'Invalid class retriever';
		const ROUTE_PARAM_REDEFINED         = 'Route param redefined';
		const INVALID_PARAM_TYPE            = 'Invalid route parameter type';
		const CONTROLLER_ALREADY_CONFIGURED = 'Controller is already configured';

		const UNWRAP_METHOD = 'getModel';

		private $configured = false;
		private $children   = array();
		private $routes     = array();
		private $index      = array();
		private $pattern    = '';

        private $rawArgs = '';
        protected $args = array();

		protected $model         = null;
        protected $count         = false;

        protected $route = '';
        protected $path = '';

		public function __construct($model = null){
			$this->model = $model;
		}

		public function __depend($scope){
            parent::__depend($scope);
            if($this->isRoot()) {
                $this->path = $scope->OXYGEN_ROOT_URI;
            }
		}

		public function getModel() {
			return $this->model;
		}

        public function go($path = true, $args = array(), $merge = true) {
            if(is_bool($path)) {
                return $path 
                    ? $this->path . $this->rawArgs
                    : $this->path
                ;
            } else if(is_array($path)) {
                $merge = $args;
                if(is_array($merge)) {
                    $merge = true;
                }
                $args = $path;
                $path = '';
            }
            if ($path === '') {
                $args = $merge 
                    ? array_merge($this->args, $args)
                    : $args
                ;
                return $this->path . ((count($args) > 0)
                    ? '&' . http_build_query($args)
                    : ''
                );
            } else {
                $args = ((count($args) > 0)
                    ? '&' . http_build_query($args)
                    : ''
                );
                if ($path{0} === '/') {
                    return $this->OXYGEN_ROOT_URI . $path . $args;
                } else {
                    return $this->path . $this->rawArgs . '/' . $path . $args;
                }
            }
        }

		public function routeExists($route) {
            if (preg_match('#^((?:(\.)|(\.\.)|/(.*))(/.*$|$)|$)#',$route, $match)) {;
                switch($route){
                case '': return true;
                case '.': return true;
                case '..': return !$this->isRoot();
                default:
                    return $this->isRoot()
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
			return $this->new_Oxygen_Controller_Iterator($this->routes, $this->go());
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
            $array = array();
            parse_str($this->rawArgs, $array);
            $this->args = $array;
        }

        public function shiftRoute($path, $route, $rest){
            preg_match(self::ARG_EXTRACT_REGEXP, $rest, $match);
            $this->rawArgs = $match[1];
            $this->route = $route;
            $this->path = $path . '/' .  $route;
            $this->parseArgs();
            return $match[2];
        }
        
        public function isRoot() {
            return !($this->parent instanceof Oxygen_Controller);
        }


        public function offsetGet($offset) {
            if (isset($this->children[$offset])) {
                return $this->children[$offset];
            } else {
                return $this->children[$offset] = $this->evalOffset($offset);
            }
        }

        public function setOffsetCache($offset, $value) {
            return $this->children[$offset] = $value;
        }

        public function tryOffsetCache($offset, &$result) {
            if (isset($this->children[$offset])) {
                $result = $this->children[$offset];
                return true;
            } else {
                $result = false;
                return false;
            }
        }

        private function evalOffset($offset) {
            if (preg_match('#^((?:(\.)|(\.\.)|/(.*))(/.*$|$)|$)#',$offset, $match)) {;
                switch($offset){
                case '': return $this;
                case '.': return $this;
                case '..': return $this->isRoot()
                    ? $this->routeMissing('..')
                    : $this->parent
                ;
                default:
                    return $this->isRoot()
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
            $rest = $next->shiftRoute($this->path, $actual, $rest);
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
				$routes = $this->new_Controller_Routes();
				$this->configure($routes);
			    $this->postConfigure();
			}
		}

		public function configure($routes) {
        }

        public static function __class_construct($scope) {
            $scope->register('Controller_Routes','Oxygen_Controller_Routes');
            $scope->register('Controller_Configurator','Oxygen_Controller_Configurator');
            $scope->register('Controller','Oxygen_Controller');
        }

	}

?>