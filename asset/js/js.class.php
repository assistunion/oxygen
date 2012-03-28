<?

    class Oxygen_Asset_JS extends Oxygen_Asset {
    	const JQUERY_WRAPPER = 'jQuery(function($){{0}});';
        public function __construct() {
            parent::__construct('.js');
        }
        protected function processOne($path) {
        	$source = file_get_contents($path);
        	if($this->isUrl($path)){
        		return $source;
        	} else {
        		return Oxygen_Utils_Text::format(self::JQUERY_WRAPPER,$source);
			}        		
        }
    }

?>