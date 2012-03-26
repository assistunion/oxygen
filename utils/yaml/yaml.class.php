<?
    require_once Oxygen_Lib::path('yaml-php/lib/sfYaml.php');

    public Oxygen_Utils_YAML {
        public static function load($input) {
            return sfYaml::load($input);
        }
    }


?>