<?

	require_once Oxygen_Lib::path('lessphp/lessc.inc.php');

	class Oxygen_Asset_LESS extends Oxygen_Asset {

		private $less = null;

		const VIRTUAL_WRAPPER = '.{0}{{1}}';
		
		public function __construct() {
			parent::__construct('.less');
			$this->less = new lessc();
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