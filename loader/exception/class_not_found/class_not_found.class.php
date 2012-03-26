<?
    class Oxygen_Loader_Exception_ClassNotFound extends Oxygen_Loader_Exception {
        public function __construct($class) {
            parent::__construct("Class '$class' is not found");
        }
    }

?>

