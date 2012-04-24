<?

    class Oxygen_Factory_Callable extends Oxygen_Factory {
        public function getInstance($args = array(), $scope = null) {
            if ($scope === null) $scope = $this->scope;
            $callable = $this->getDefinition();
            array_unshift($args, $scope);
            return call_user_func_array($callable, $args);
        }
    }



?>