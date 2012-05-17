<?
    class Oxygen_Loader_Exception_ResourceNotFound extends Oxygen_Loader_Exception {
        public function __construct($class,$resource) {
            parent::__construct("Resource '$resource' for class '$class' is not found");
        }
    }
?>
