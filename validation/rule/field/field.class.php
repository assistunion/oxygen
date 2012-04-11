<?

    abstract class Oxygen_Validation_Rule_Filed extends Oxygen_Validation_Rule {
        public $filed = null;
        public function __construct($filed) {
            $this->field = $filed;
        }
    }


?>