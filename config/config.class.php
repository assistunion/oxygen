<?

    class Oxygen_Config extends Oxygen_Object {


        public static function __class_construct($scope) {
            $scope->assets->register('css','Oxygen_Asset_CSS');
            $scope->assets->register('less','Oxygen_Asset_LESS');
            $scope->assets->register('js','Oxygen_Asset_JS');
            $scope->cache = $scope->new_Oxygen_Cache_File($scope->temp_dir);
            $serializer = $scope->serializer = $scope->new_Oxygen_Serializer();
            $scope->callable('serialize',array($serializer,'add'));

        }
    }



?>