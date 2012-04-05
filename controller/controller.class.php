<?

	class Oxygen_Controller extends Oxygen_Object
		implements ArrayAccess, IteratorAggregate, Countable
	{

		const TYPES_REGEXP = '/^(?:(int)|(str)|\/([^\/]*(?:\\\\\/[^\/]*)*)\/)$/';
		const PARAM_REGEXP = '/{(@?[0-9A-Za-z_]+):([^{}]+)}/';

		const PARAM_GUARD_REPLACE = '#\\1#';
		const PARAM_GUARD_REGEXP  = '/#(@?[0-9A-Za-z_]+)#/e';

		const INT_REGEXP_BARE = '[0-9]+';
		const STR_REGEXP_BARE = '[^/]+';

		const ARG_PREFIX = '@';

		const SINGLE     = 0;
		const COLLECTION = 1;

		const INVALID_CLASS_RETRIEVER       = 'Invalid class retriever';
		const ROUTE_PARAM_REDEFINED         = 'Route param redefined';
		const INVALID_PARAM_TYPE            = 'Invalid route parameter type';
		const CONTROLLER_ALREADY_CONFIGURED = 'Controller is already configured';

		private $visualChild   = null;
		private $logicalChild  = null;
		private $visualParent  = null;
		private $logicalParent = null;
		private $model         = null;
		private $configured    = false;
		private $children      = array();
		private $routes        = array();
		private $routingRegexp = '';

		public function __construct($model){
			$this->model = $model;
		}

		public function routeExists($route){
			$this->ensureConfigured();
		}

		public function routeGet($route){
			$this->ensureConfigured();
			echo $this->routingRegexp;
			preg_match($this->routingRegexp,$route,$m);
			return $m;
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

		public function offsetSet($offset, $childDef) {
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

		private function getClassFor($class,$model){
			if(is_array($class)){
				if(is_callable($class)) return call_user_func($class,$model);
				else $this->throwException(self::INVALID_CLASS_RETRIEVER);
			} elseif(is_string($class)) {
				return $class;
			} elseif(is_callable($class)) {
				// for PHP 5.3+
				return call_user_func($class,$model);
			} else {
				$this->throwException(self::INVALID_CLASS_RETRIEVER);
			}
		}

		private function getRegexpFor ($type){
			if(preg_match(self::TYPES_REGEXP, $type,$match)){
				switch (count($match)) {
					case 2: return self::INT_REGEXP_BARE;
					case 3: return self::STR_REGEXP_BARE;
					case 4: return $match[3];
				}
			} else {
				$this->throwException(self::INVALID_PARAM_TYPE);
			}
		}


		public function compileRoute($route, $class = 'Oxygen_Controller'){
			$args = call_user_func(array($class,'getArgsRegexp'));
			$route = trim($route,'/') . $args;
			$type = self::SINGLE;
			if(0 < preg_match_all(self::PARAM_REGEXP, $route, $match)){
				$names = $match[1];
				$types = $match[2];
				$params = array();
				foreach($names as $i => $name){
					if($name{0} != self::ARG_PREFIX) $type = self::COLLECTION;
					if(isset($params[$name])) {
						$this->throwException(self::ROUTE_PARAM_REDEFINED);
					} else {
						$params[$name] = self::getRegexpFor($types[$i]);
					}
				}
				$compiled = preg_replace(self::PARAM_REGEXP,self::PARAM_GUARD_REPLACE, $route);
				$compiled = preg_quote($compiled,'/');
				$compiled = preg_replace(self::PARAM_GUARD_REGEXP,"'(?P<\\1>'.\$params[\\1].')'", $compiled);
				return array($type,$compiled);
			} else {
				return array($type,preg_quote($route));
			}
		}

		private function postConfigure() {
			$re = '';
			foreach($this->routes as $route){
				print_r($route);
				if ($re != '') $re .= '|';
				$re .= '(' . $route->regexp . ')';
			}
			$re = '/^(?:' . $re . ')(?P<__>.*)$/';
			$this->routingRegexp = $re;
			$this->configured = true;
		}

		public function ensureConfigured() {
			if(!$this->configured){
				$this->configure();
			    $this->postConfigure();
			}
		}

		public static function getArgsRegexp(){
			return '';
		}

		public function add($class, $route, $model, $iterable) {
			if(!$this->configured) {
				$route = trim($route,'/');
				list($type,$regexp) = $this->compileRoute($route, $class);
				$this->routes[] = (object)array(
					'class'    => $class,
					'type'     => $type,
					'regexp'   => $regexp,
					'route'    => $route,
					'model'    => $model,
					'iterable' => $iterable
				);
			} else {
				$this->throwException(self::CONTROLLER_ALREADY_CONFIGURED);
			}
		}

		public function configure() {

		}
	}

?>