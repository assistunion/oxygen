<?

    class Oxygen_SQL extends Oxygen_Object {

        const STDCLASS = 'StdClass';
        const CENSORED = '***CENSORED***';

        private $link = null;

        public $host = '';
        public $user = '';
        public $pass = '';
        public $name = '';

        public function __construct($config){
            $this->host = $config->host;
            $this->user = $config->user;
            $this->pass = $config->pass; //Will be wiped out after mysql_connect
            $this->name = $config->name;
        }

        public function __complete() {
            $this->link = @mysql_connect($this->host,$this->user,$this->pass);
            $this->pass = self::CENSORED; //Wipe out the password
            if(!$this->link) {
                $this->throwException();
            }
            mysql_query('set names utf8',$this->link);
            @mysql_select_db($this->name,$this->link) or $this->throwException();

        }

        public function param($match) {
            $name = $match[1];
            $type = $match[2];
            if(!isset($this->params[$name])) $this->throwException("param {$name} is not supplied");
            $value = $this->params[$name];
            switch($type){
            case 'int': if (preg_match("/^\-?[0-9]+$/",$value)) return $value; else return 0;
            case 'dbl': return doubleval($value);
            case 'str': return "'" . mysql_real_escape_string($value) . "'";
            }
        }

        public function throwException($text = false, $scope = false) {
            if ($scope === false) $scope = $this->scope;
            if($text === false) {
                if(!$this->link) {
                    $scope->throw_Oxygen_SQL_Exception(mysql_error());
                } else {
                    $scope->throw_Oxygen_SQL_Exception(mysql_error($this->link));
                }
            } else {
                throw $scope->throw_Oxygen_SQL_Exception($text);
            }
        }

        private $params = array();

        public function raw($sql) {
            $res = mysql_query($sql, $this->link);
            if(!$res) $this->throwException();
            return $res;
        }

        public function run($sql, $params = array(), $wrapper = self::STDCLASS, $scope = false) {

            if($scope === false) {
                $scope = $this->scope;
            }

            if(!preg_match("/^(valueof|create|drop|replace|get|select|insert|update|delete)/i", $sql, $match)) {
                $this->throwException('Unknown sql-query type');
            }

            $type = strtolower($match[1]);

            $this->params = $params;

            $sql = preg_replace_callback(
                "/{([a-z_A-Z]+):(str|int|dbl)}/ms",
                array($this,"param"),
                $sql
            );

            if($type == 'select') return $scope->SQL_ResultSet($sql, $this, $wrapper);
            if($type == 'valueof') {
                $sql = preg_replace("/^valueof/i","select",$sql);
                $res = $this->raw($sql);
                $row = mysql_fetch_row($res);
                mysql_free_result($res);
                if(!$row) return false;
                return $row[0];
            }

            if($type == 'get') {
                $sql = preg_replace("/^get/i","select",$sql);
                $res = $this->raw($sql);
                $obj = mysql_fetch_object($res);
                mysql_free_result($res);
                if(!$obj) return false;
                if($wrapper !== self::STDCLASS) {
                    $obj = $scope->$wrapper($obj);
                }
                return $obj;
            }

            $this->raw($sql);

            switch($type){
            case "insert": return mysql_insert_id($this->link);
            case "replace": return mysql_insert_id($this->link);
            case "update": return mysql_affected_rows($this->link);
            case "delete": return mysql_affected_rows($this->link);
            default:
                return mysql_affected_rows($this->link);
            }
        }
    }


?>