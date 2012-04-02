<?

	class Oxygen_Controller extends Oxygen_Object 
		implements ArrayAccess, IteratorAggregate, Countable
	{

		const INVALID_CLASS_RETRIEVER = 'Invalid class retriever';

		private $visualChild = null;
		private $logicalChild = null;
		private $visualParent = null;
		private $logicalParent = null;
		private $model = null;
		private $configured = false;
		private $children = array();
		private $childDefs = array();
		private $colDefs = array();

		public function __construct($model){
			$this->model = $model;
		}

		public function offsetExists($route) {
			if(isset($this->children[$route])) return true;
			if(isset($this->childDefs[$route])) return true;
			if()
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

		private getClassFor($class,$model){
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

		public function add($class, $route, $model, $iterable) {
			$this['x-{id:int}/{a:slug}']->Partner($model);
		}

		public function configure() {
			
		}
	}

?>