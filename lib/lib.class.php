<?

    class Oxygen_Lib {
        public static function path($path) {
            return dirname(__file__) . DIRECTORY_SEPARATOR . $path;
        }

        public static function url($path) {
        	$document_root = str_replace('/',DIRECTORY_SEPARATOR, $_SERVER['DOCUMENT_ROOT']);
        	$full_path = dirname(__file__) . DIRECTORY_SEPARATOR . $path;
        	$url = str_replace(DIRECTORY_SEPARATOR, '/', str_replace($document_root, '', $full_path));
        	return $url;
        }
    }


?>