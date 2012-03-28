<?

	require_once Oxygen_Lib::path('lessphp/lessc.inc.php');

	class Oxygen_Asset_LESS extends Oxygen_Asset {
		private $less = null;
		public function __construct() {
			parent::__construct('.less');
			$this->less = new lessc();
		}
		protected function process($source) {
			return $this->less->parse($source);
		}
	}


?>