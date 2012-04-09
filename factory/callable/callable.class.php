<?

    class Oxygen_Factory_Callable extends Oxygen_Factory {
        public function getInstance($args = array(), $scope = null) {
            if ($scope = null) $scope = $this->getScope();
            $callable = $this->getDefinition();
            switch(count($args)){
            case 0: $result = $callable($scope); break;
            case 1: $result = $callable($scope,$args[0]); break;
            case 2: $result = $callable($scope,$args[0], $args[1]); break;
            case 3: $result = $callable($scope,$args[0], $args[1], $args[2]); break;
            default:
                array_unshift($args, $scope);
                $result = call_user_func_array($callable, $args);
            }
        }
    }



?>