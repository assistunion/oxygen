<?

    class Oxygen_Utils_Text {
        private static $diacritics = array(
            'Ā' => 'A', 'Č' => 'C', 'Ē' => 'E', 'Ģ' => 'G', 'Ī' => 'I',  'Ķ' => 'K',
            'Ļ' => 'L', 'Ņ' => 'N', 'Š' => 'S', 'Ū' => 'U', 'Ž' => 'Z',
            'ā' => 'a', 'č' => 'c', 'ē' => 'e', 'ģ' => 'g', 'ī' => 'i',  'ķ' => 'k',
            'ļ' => 'l', 'ņ' => 'n', 'š' => 's', 'ū' => 'u', 'ž' => 'z',  'ç' => 'e',
            'í' => 'k', 'â' => 'a'
        );
        const REGEXP_DIACRITICS = '/(Ā|Č|Ē|Ģ|Ī|Ķ|Ļ|Ņ|Š|Ū|Ž|ā|č|ē|ģ|ī|ķ|ļ|ņ|š|ū|ž|ç|í|â)/e';

        public static function removeDiacritics($text) {
            return preg_replace(self::REGEXP_DIACRITICS,'self::$diacritics["\\1"]',$text);
        }
        public static function format($format,
            $arg0 = '',
            $arg1 = '',
            $arg2 = '',
            $arg3 = '',
            $arg4 = '',
            $arg5 = '') {
            return preg_replace('/{([0-5])}/e','$arg\\1',$format);
        }
    }

?>