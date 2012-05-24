<?

    class Oxygen_Asset_JS extends Oxygen_Asset {

        const JQUERY_WRAPPER = 'window.oxygen.$(function($){
            var templateClass="{0}"
              , componentClass="{1}"
              , templateName="{2}"
              , o = window.oxygen
              , _ = window.oxygen._
              , JSON = window.oxygen.JSON
            ;
            $(templateClass).each(function(){
               var $this = $(this);
               if(typeof(this.oxygenized) === "undefined"){
                  {3}
                  this.oxygenized = true;
               }
            });
        })';
        public function __construct() {
            parent::__construct('.js');
        }
        protected function processOne($item) {
        	$source = parent::processOne($item);
            list($type, $path) = $item->assets[$this->ext];
        	if($type === self::REMOTE_RESOURCE){
        		return $source;
        	} else {
                return Oxygen_Utils_Text::format(
                    self::JQUERY_WRAPPER,
                    $item->component !== false ? '.' . $item->component : 'body',
                    $item->class,
                    $item->name,
                    $source
                );
            }
        }
    }

?>