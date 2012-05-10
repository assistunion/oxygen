<?
    class Oxygen_Utils_CSV {

        

        // Remove surrounding quotes and unescape quotes inside text
        public static function unquote($text) {
            if($text === '') return '';
            if($text{0} === '"') {
                $text = str_replace('""','"',$text);
                $text = substr($text,1,strlen($text)-2);
            }
            return trim($text);
        }

        public static function parseLine($raw_data, $delimiter=",") {
            $BOM = pack("CCC",0xef,0xbb,0xbf);
            // Remove byte-order-mark:
            if(substr($raw_data,0,3) == $BOM) $raw_dara = substr($raw_data,3); 
            
            // Parse CSV-row into array:
            preg_match_all("/(\"[^\"]*(\"\"[^\"]*)*\"|[^\"$delimiter\n\r]*),?/",$raw_data,$match);

            $values = $match[1];

            array_pop($values);

            // Unquote all values:
            return array_map(array('self','unquote'),$values);
        }

    }
?>