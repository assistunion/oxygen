<?

	require_once Oxygen_Lib::path('lessphp/lessc.inc.php');

	class Oxygen_Asset_LESS extends Oxygen_Asset {

		private $less = null;

		const VIRTUAL_WRAPPER = '.{0}{{1}}';
		const CSS_URL = 'url({0})';
		
		public function __construct() {
			parent::__construct('.less');
			$this->less = new lessc();
			$this->less->registerFunction('icon',array($this,'icon'));
			$this->less->registerFunction('virtual',array($this,'resource'));
		}

		public function icon($parsed) {
			list($type,$value) = $parsed;
			if($type !== 'keyword') $this->throwException('Invalid icon code'); 
			return Oxygen_Utils_Text::format(
				self::CSS_URL,
				Oxygen_Utils_Icon::get($value)
			);
		}

		public function resource($parsed) {
			die(print_r($parsed,true));
		}

		public function processOne($asset) {
			$source = parent::processOne($asset);
			if($asset->usage->isVirtual){
				return Oxygen_Utils_Text::format(
					self::VIRTUAL_WRAPPER,
					$asset->usage->componentClass,
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