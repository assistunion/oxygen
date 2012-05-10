<?

    class Oxygen_Asset_JS extends Oxygen_Asset {

        const JQUERY_WRAPPER = 'jQuery(function($){
            var templateClass="{0}"
              , componentClass="{1}"
              , templateName="{2}";
            $(templateClass).each(function(){
               var $this = $(this);
               {3}
            });
        })';
        public function __construct() {
            parent::__construct('.js');
        }
        protected function processOne($asset) {
        	$source = parent::processOne($asset);
        	if($asset->type === self::REMOTE_RESOURCE){
        		return $source;
        	} else {
                return Oxygen_Utils_Text::format(
                    self::JQUERY_WRAPPER,
                    $asset->component !== false ? '.' . $asset->component : 'body',
                    $asset->class,
                    $asset->name,
                    $source
                );
            }
        }
    }

?>