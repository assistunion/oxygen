<?

	class Oxygen_Asset_LESS extends Oxygen_Asset {

		public static function __class_construct($scope) {
			$scope->lib->load('lessphp/lessc.inc.php');
		}

		private $less = null;

		const VIRTUAL_WRAPPER = '.{0}{{1}}';
		const CSS_URL = 'url({0})';
		
		public function __construct() {
			parent::__construct('.less');
			$this->less = new lessc();
			$this->less->registerFunction('icon', array($this, 'icon'));
			$this->less->registerFunction('virtual', array($this, 'resource'));
		}

		public function icon($parsed) {
			list($type, $value) = $parsed;
			$this->__assert($type === 'keyword', 'Invalid icon code');
			$this->__assert(preg_match("/^[a-z_]+$/", $value), 'Invalid icon code');
            return Oxygen_Utils_Text::format(
                self::CSS_URL,
                $this->scope->assets->getIcon($value)
            );
		}

		public function resource($parsed) {
			$resource = trim($parsed[2][0][1],'\'');
			$class = trim($parsed[2][1][1],'\'');
			return 'url('.$this->scope->loader->urlFor($class, $resource).')';
		}

		public function processOne($asset) {
			$source = parent::processOne($asset);
			$source = preg_replace("/resource\((.*)\)/", "virtual(\\1,'{$asset->class}')", $source);
			if($asset->component !== false){
				return Oxygen_Utils_Text::format(
					self::VIRTUAL_WRAPPER,
					$asset->component,
					$source
				);
			} else {
				return $source;
			}
		}

		protected function process($source) {
			return $this->less->parse($source);
		}
	}


?>