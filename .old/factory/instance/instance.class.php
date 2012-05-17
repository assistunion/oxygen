<?
    class Oxygen_Factory_Instance extends Oxygen_Factory {
        public function getInstance($args = array(), $scope = null) {
            return $this->getDefinition();
        }
    }


?>