<?

    class Oxygen_Downloader extends Oxygen_Object {

        const USER_AGENT = 'Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.9.1.1) Gecko/20090715 Firefox/3.5.1';
        const TIMEOUT = 60;

        public $ch = null;
        public static $options = array(
            CURLOPT_USERAGENT => self::USER_AGENT,
            CURLOPT_FAILONERROR => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT => self::TIMEOUT,
            CURLOPT_HTTPGET => 1,
            CURLOPT_ENCODING => 'gzip'
        );


        public function __construct() {
            $ch = $this->ch =  curl_init();
            foreach(self::$options as $name => $value) {
                curl_setopt($ch, $name, $value);
            }
        }

        public function get($url,$params = array()) {
            if (count($params)>0) {
                if (preg_match("/\?/",$url)) {
                    $url .= '&';
                } else {
                    $url .= '?'; 

                }
                $url .= http_build_query($params);
            }
            curl_setopt($this->ch, CURLOPT_URL, $url);
            curl_setopt($this->ch, CURLOPT_RETURNTRANSFER,1);
            $result = curl_exec($this->ch);
            $this->__assert(
                $result !== false,
                'DOWNLOAD FAILED'
            );
            return $result;
        }

        public function getJSON($url, $params = array()) {
            $result = trim($this->get($url, $params));
            $result = json_decode($result);
            $this->__assert(
                $result !== false,
                'INVALID JSON'
            );
            return $result;
        }
    }



?>