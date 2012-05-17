<?

    abstract class Oxygen_Factory extends Oxygen_Object {
        private $definition = null;
        public final function __construct($definition) {
            $this->definition = $definition;
        }
        public abstract function getInstance($args = array(), $scope = null);
        public final function getDefinition(){
            return $this->definition;
        }
    }


?>