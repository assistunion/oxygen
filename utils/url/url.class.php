<?

    //TODO: Finish the functionality
    class Oxygen_Utils_URL {
        public static pathFor($url) {
        }
        public static urlFor($url) {
        }
        public static Absolutize($base, $relative = false) {
            if($relative !== false) {
                return url_to_absolute($base, $relative);
            } else if(preg_match('|^(https?://[^/]*)(/)(.*)$|',$base,$m)) {
                return $base;
            } else {
                throw new Oxygen_Exception('Not impelemented yet');
            }
        }
    }

?>