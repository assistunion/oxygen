<?
    class Oxygen {
        public static function Generate() {
            $scope = Oxygen_Scope::root();
            $generator = $scope->Oxygen_Generator();
            $generator->generate();
        }
    }


?>