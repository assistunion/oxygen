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
			$relative = 'silk-icons/icons/' . $value . '.png';
			$path = $this->scope->lib->path($relative);
			$this->__assert(file_exists($path),'Icon not found');
			return Oxygen_Utils_Text::format(
				self::CSS_URL,
				$this->scope->lib->url($relative)
			);
		}

		public function resource($parsed) {
			//die(print_r($parsed,true));
		}

		public function processOne($asset) {
			$source = parent::processOne($asset);
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