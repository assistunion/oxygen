<?

    class Oxygen_Utils_Date {
        public static function Convert($formatFrom,$formatTo,$date) {
            if($formatFrom === 'm.d.Y') {
                return date($formatTo,strtotime($date));
            } else {
                throw new Exception("Date fromat $fromatFrom is not implemented yet!");
            }
        }
    }



?>