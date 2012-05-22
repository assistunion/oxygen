<?

    class Oxygen_Common_FileUpload_Format extends Oxygen_Controller {
        protected $args = null;
        public $id;
        public $title;
        
        public function __construct($args, $title = false, $id = false) {
            $this->$args = $args;
            $this->id = $id;
            $this->title = $title;
        }
        public function __toString() {
            return $this->title;
        }
    }

?>    