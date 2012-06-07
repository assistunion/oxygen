<?

    class Oxygen_OpenGraph extends Oxygen_Object {

        public $title = '';
        public $description = '';
        public $image = '';
        public $keywords = '';

        public function __construct($title = '', $description = '', $image = '', $keywords = '') {
            $this->title = $title;
            $this->description = $description;
            $this->image = $image;
            $this->keywords = $keywords;
        }

    }

?>