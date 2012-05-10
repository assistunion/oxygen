<?

	class Oxygen_Controller extends Oxygen_Object
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
		private $index      = array();
		private $pattern    = '';

        private $rawArgs = '';
        protected $args = array();

        public $routes = array();
        public $model  = null;
        public $parent = null;

        public $isCurrent = false;
        public $isActive = false;
        public $child = false;

        protected $count  = false;

        protected $route = '';
        protected $path = '';

        private static $implementations = array(
            'Router'           => 'Oxygen_Router',
            'Routes'           => 'Oxygen_Controller_Routes',
            'Configurator'     => 'Oxygen_Controller_Configurator',
            'Controller'       => 'Oxygen_Controller',
            'Dummy'            => 'Oxygen_Controller_Dummy',
            'ChildrenIterator' => 'Oxygen_Controller_Iterator'
        );

		public function __construct($model = null){
			$this->model = $model;
		}

        public function getIconSource() {
            return $this->scope->assets->getIcon($this->getIcon());
        }

        public function getIcon() {
            return 'bullet_green';
        }

        public static function __class_construct($scope) {
            $scope->registerAll(self::$implementations);
        }

		public function getModel() {
			return $this->model;
		}

        public function deactivate() {
            $this->isActive = false;
            $this->isCurrent = false;
            if ($this->parent) $this->parent->deactivate();
        }

        public function activate() {
            $this->isActive = true;
            $this->isCurrent = false;
            if ($this->parent) {
                $this->parent->child = $this;
                return $this->parent->activate();
            } else {
                return $this;
            }
        }

        public function getPathToCurrent() {
            $result = array();
            $x = $this;
            while($x) {
                $result[] = $x;
                $x = $x->child;
            }
            return $result;
        }

        public function getPathToRoot() {
            $result = array();
            $x = $this;
            while($x) {
                $result[] = $x;
                $x = $x->parent;
            }
            return $result;
        }

        public function handleGet() {
            $first = $this->makeCurrent();
            return htmlResponse(array($first,'put_page_view'));
        }

        public function post() {
            $location = $this->go();
            return redirectResponse($location);
        }

        public function handleRPC($method, $args) {
            $this->__assert(false,
                'Remote method {0} is not allowed',
                $method
            );
        }

        public function handlePost() {
            $SERVER = $this->scope->SERVER;
            if(isset($SERVER['HTTP_X_OXYGEN_RPC'])) {
                $method = $SERVER['HTTP_X_OXYGEN_JSON_RPC'];
                $args = json_decode(file_get_contents('php://input'));
                return $this->handleRPC($method,$args);
            } else {
                return $this->post();
            }
        }

        public function handleRequest() {
            $method = $this->scope->SERVER['REQUEST_METHOD'];
            switch($method){
            case 'GET': return $this->handleGet();
            case 'POST': return $this->handlePost();
            default:
                $this->__assert(
                    false,
                    'Unknown method {0}',
                     $method
                );
            }
        }

        public function getCurrent() {
            if($this->isCurrent) {
                $this->__assert($this->isActive, 'Must be active');
                 return $this;
            } if ($this->isActive) {
                $this->__assert($this->child, 'Must have child');
                return $this->child->getCurrent();
            } else {
                if($this->parent) {
                    $this->__assert($this->parent->child !== $this, 'Cyclic activity');
                    return $this->parent->getCurrent();
                } else {
                    $this->isActive = true;
                    $this->isCurrent = true;
                    return $this;
                }
            }
            
            if ($this->isCurrent)
            $this->__assert($this->child, 'Child must be selected');
            return $this->child->getCurrent();
        }


        public function makeCurrent() {
            $this->getCurrent()->deactivate();
            $root = $this->activate();
            $this->isCurrent = true;
            return $root;
        }

        public function go($path = true, $args = array(), $merge = true) {
            if(is_bool($path)) {
                $p = $this->path === '' ? '/' : $this->path;
                return $path
                    ? $p . $this->rawArgs
                    : $p
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
                    return $this->scope->OXYGEN_ROOT_URI . $path . $args;
                } else {
                    return $this->path . $this->rawArgs . '/' . $path . $args;
                }
            }
        }

        public function __routed() {
        }

		public function routeExists($route) {
            if (preg_match('#^((?:(\.)|(\.\.)|/(.*))(/.*$|$)|$)#', $route, $match)) {;
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
                return $this->count;
            } else {
                return $this->count;
            }
		}

		public function getIterator() {
			$this->ensureConfigured();
			return $this->scope->ChildrenIterator($this);
		}

		public function offsetUnset($offset){

		}

        public function __toString() {
            return $this->route;
        }

		public function offsetSet($offset, $value) {
			$this->throw_Exception('Please refer to user manual how to configure controllers');
		}

        public function parseArgs(){
            $array = array();
            parse_str($this->rawArgs, $array);
            $this->args = $array;
        }

        public function setPath($parent, $route = '', $rest = ''){
            preg_match(self::ARG_EXTRACT_REGEXP, $rest, $match);
            $this->parent = $parent;
            $this->rawArgs = $match[1];
            $this->route = $route;
            if (is_string($parent)) {
                $path = $parent;
                $this->parent = null;
            } else {
                $path = $parent->path;
                $this->parent = $parent;
            }
            if($route !== '') $this->path = $path . '/' . $route;
            else $this->path = $path;
            $this->parseArgs();
            $this->__routed();
            return $match[2];
        }

        public function isRoot() {
            return $this->parent === null;
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
            if(isset($this->children[$actual])) {
                $next = $this->children[$actual];
            } else {
                $next = $router[$actual];
                $this->children[$actual] = $next;
            }
            $rest = $next->setPath($this, $actual, $rest);
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
			return $this->routes[$route] = $this->scope->Router(
				$route, $model, $class, self::UNWRAP_METHOD
			);
		}

		public function ensureConfigured() {
			if(!$this->configured){
				$routes = $this->scope->Routes($this);
				$this->configure($routes);
			    $this->postConfigure();
			}
		}

        public function urlFor($resource) {
            return $this->scope->loader->urlFor(get_class($this),$resource);
        }

        public function pathFor($resource) {
            return $this->scope->loader->pathFor(get_class($this),$resource);
        }

		public function configure($routes) {
        }


	}

?>