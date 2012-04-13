<?

    class Oxygen_Asset_JS extends Oxygen_Asset {
        
        const JQUERY_WRAPPER = 'jQuery(function($){var componentClass="body";{0}});';
        const JQUERY_VIRTUAL_WRAPPER = 'jQuery(function($){var componentClass=".{1}";{0}});';

        public function __construct() {
            parent::__construct('.js');
        }
        protected function processOne($asset) {
        	$source = parent::processOne($asset);
        	if($asset->type === self::REMOTE_RESOURCE){
        		return $source;
        	} else {
                return Oxygen_Utils_Text::format(
                    $asset->component !== false
                    ? self::JQUERY_VIRTUAL_WRAPPER
                    : self::JQUERY_WRAPPER,
                    $source,
                    $asset->component
                );
            }        		
        }
    }

?>