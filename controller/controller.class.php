<?

	class Oxygen_Controller extends Oxygen_Object
		implements ArrayAccess, IteratorAggregate, Countable
	{

		const TYPES_REGEXP = '/^(?:(int)|(str)|\/([^\/]*(?:\\\\\/[^\/]*)*)\/)$/';
		const PARAM_REGEXP = '/{([0-9A-Za-z_]+):([^{}]+)}/';

		const PARAM_GUARD_REPLACE = '#\\1#';
		const PARAM_GUARD_REGEXP  = '/#([0-9A-Za-z_]+)#/e';

		const INT_REGEXP_BARE = '[0-9]+';
		const STR_REGEXP_BARE = '[^/]+';


		const INVALID_CLASS_RETRIEVER = 'Invalid class retriever';
		const ROUTE_PARAM_REDEFINED   = 'Route param redefined';
		const INVALID_PARAM_TYPE      = 'Invalid route parameter type';

		private $visualChild   = null;
		private $logicalChild  = null;
		private $visualParent  = null;
		private $logicalParent = null;
		private $model         = null;
		private $configured    = false;

		public function __construct($model){
			$this->model = $model;
		}

		public function offsetExists($route) {
			if(isset($this->children[$route])) return true;
			if(isset($this->childDefs[$route])) return true;
		}

		public function count() {

		}

		public function getIterator() {

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


		public function compileRoute($route){
			$route = trim($route,'/');
			$specificy = substr_count('/', $route) + 1;
			if(0 < preg_match_all(self::PARAM_REGEXP, $route, $match)){
				$names = $match[1];
				$types = $match[2];
				$params = array();
				foreach($names as $i => $name){
					if(isset($params[$name])) {
						$this->throwException(self::ROUTE_PARAM_REDEFINED);
					} else {
						$params[$name] = self::getRegexpFor($types[$i]);
					}
				}
				$cpl = preg_replace(self::PARAM_REGEXP,self::PARAM_GUARD_REPLACE, $route);
				$cpl = preg_quote($cpl,'/');
				$cpl = preg_replace(self::PARAM_GUARD_REGEXP,"'(?P<\\1>'.\$params[\\1].')'", $cpl);
				return $cpl;
			} else {
			}
			return $match;
		}

		private function postConfigure() {

		}

		public function ensureConfigured() {
			if(!$this->configured){
				$this->configure();
			    $this->postConfigure();
			}
		}

		public function add($class, $route, $model, $iterable) {
			if(!$this->configured) {

			} else {

			}
		}

		public function configure() {

		}
	}

?>