<?
    class Oxygen_Router extends Oxygen_Collection {

        const TYPES_REGEXP = '/^(?:(int)|(str)|(url)|(any)|\/([^\/]*(?:\\\\\/[^\/]*)*)\/)$/';
        const PARAM_REGEXP = '/{([0-9A-Za-z_]+):([^{}]+)}/';

        const PARAM_GUARD_REPLACE = '#\\1#';
        const PARAM_GUARD_REGEXP  = '/#([0-9A-Za-z_]+)#/e';

        const INT_REGEXP_BARE = '[0-9]+';
        const STR_REGEXP_BARE = '[^\/]+';
        const URL_REGEXP_BARE = '[^\/&]+';
        const ANY_REGEXP_BARE = '.+';

        const SINGLE     = 0;
        const COLLECTION = 1;
        const ARRAY_TYPE = 2; 

        const INVALID_PARAM_TYPE = 'Invalid parameter type {0}';

        private $extract = '';
        private $regexp = '';
        private $type = self::COLLECTION;
        private $pattern = '';
        private $data = null;
        private $params = array();
        private $parseTransform = array();
        private $formatTransform = array();
        private $autoPopulate = false;
        private $autoRoute = '';

        public function setWrap($wrap, $method = false) {
            if($method !== false) {
                $this->wrapperClass = '';
                $this->wrap = array($wrap, $method);
            } else if($wrap === false) {
                $this->wrapperClass = '';
                $this->wrap = array($this,'sameObject');
            } else if(is_string($wrap)) {
                $this->wrapperClass = $wrap;
                $this->wrap = array($this,'staticWrap');
            } else {
                $this->wrapperClass = '';
                $this->wrap = $wrap;
            }
            return $this;
        }

        public function setUnwrap($unwrap, $method = false) {
            if($method !== false) {
                $this->unwrapMethod = '';
                $this->unwrap = array($unwrap, $method);
            } else if($unwrap === false) {
                $this->unwrapMethod = '';
                $this->unwrap = array($this,'sameObject');
            } else if (is_string($unwrap)) {
                $this->unwrapMethod = $unwrap;
                $this->unwrap = array($this,'staticUnwrap');
            } else {
                $this->unwrapMethod = '';
                $this->unwrap = $unwrap;
            }
            return $this;
        }

        public function __construct($pattern, $data, $wrap = false, $unwrap = false) {
            $this->pattern = $pattern;
            $this->data = $data;
            $this->setWrap($wrap);
            $this->setUnwrap($unwrap);
        }

        private function &sameObject(&$obj){
            return $obj;
        }

        private function staticWrap($obj){
            return $this->new_($this->wrapperClass,array($obj));
        }

        private function staticUnwrap($obj){
            return $obj->{$this->unwrapMethod}();
        }

        public function wrap($obj){
            return call_user_func($this->wrap,$obj);
        }

        public function unwrap($obj){
            return call_user_func($this->unwrap,$obj);
        }

        private function url($data, $encode) {
            return $encode
                ? urldecode($data)
                : urldecode($data)
            ;
        }
        private function any($data, $encode) {
            return $data;
        }

        private function getTypeConfig($type){
            $this->__assert(
                preg_match(self::TYPES_REGEXP, $type, $match),
                self::INVALID_PARAM_TYPE,
                $type
            );
            switch (count($match)) {
                case 2: return array(self::INT_REGEXP_BARE, 'any');
                case 3: return array(self::STR_REGEXP_BARE, 'any');
                case 4: return array(self::URL_REGEXP_BARE, 'url');
                case 5: return array(self::URL_REGEXP_BARE, 'any');
                case 6: return $match[3];
            }
        }

        public function formatKey($data){
            $params = array();
            foreach($this->params as $name => $config) {
                list($regexp, $transform) = $config;
                $value = $this->$transform(
                    (($this->type === self::ARRAY_TYPE || $this->autoPopulate)
                        ? $data
                        : $data[$name]
                    ), true
                );
                $this->__assert(
                    preg_match($regexp, $value),
                    'Data not conforms to pattern'
                );
                $params[$name] = $value;
            }
            return preg_replace(self::PARAM_REGEXP . 'e', "\$params['\\1']", $this->pattern);
        }

        public function parseKey($str) {
            $this->__assert(
                $this->matchKey($str, $key),
                'Invalid key format'
            );
            return $key;
        }

        public function getData() {
            return $this->data;
        }

        public function getType() {
            return $this->type;
        }

        public function getRegexp() {
            return $this->regexp;
        }

        public function offsetExists($offset) {
            if (!$this->matchKey($offset, $key)) return false;
            return $this->autoPopulate || isset($this->data[$key]);
        }

        //TODO: Change return mode to reference on php 5.3.4+
        public function offsetGet($offset) {
            if ($this->autoPopulate) {
                $key = $this->parseKey($offset);
                if(!isset($this->data[$offset])) {
                    $this->data[$offset] = $this->wrap($key);
                }               
                return $this->data[$offset];
            } else {
                return $this->wrap(
                    $this->data[
                        $this->parseKey($offset)
                    ]
                );
            }
        }

        public function offsetSet($offset, $value) {
            $this->data[$this->parseKey($offset)] = $this->unwrap($value);
        }

        public function offsetUnset($offset) {
            unset($this->data[$this->parseKey($offset)]);
        }

        public function count(){
            $this->ensureAuto();
            return count($this->data);
        }
        
        private function ensureAuto() {
            if(!$this->autoPopulate) return;
            if(count($this->data)>0) return;
            if(!$this->autoRoute) return;
            $key = $this->parseKey($this->autoRoute);
            $this->data[$this->autoRoute] = $this->wrap($key);
        }

        public function getIterator() {
            $this->ensureAuto();
            return $this->scope->Oxygen_Router_Iterator($this);
        }

        private function extractKey($match) {
            $key = array();
            foreach($this->params as $name => $config){
                $key[$name] = $this->{$config[1]}($match[$name], false);
            }
            return $key;
        }

        public function matchKey($str, &$key) {
            if(preg_match($this->extract, $str, $match)) {
                switch($this->type){
                case self::SINGLE:
                  reset($match);
                  $key = current($match);
                  break;
                case self::COLLECTION:
                  $key = $this->extractKey($match);
                  break;
                case self::ARRAY_TYPE:
                  $key = $this->extractKey($match);
                  reset($key);
                  $key = current($key);
                  break;
                }
                return true;
            } else {
                $key = array();
                return false;
            }
        }

        private function compile() {
            if(0 < preg_match_all(self::PARAM_REGEXP, $this->pattern, $match)){
                $names = $match[1];
                $types = $match[2];
                $params = array();
                foreach($names as $i => $name){
                    if(isset($params[$name])) {
                        $this->throw_Exception(self::ROUTE_PARAM_REDEFINED);
                    } else {
                        $config = self::getTypeConfig($t = $types[$i]);
                        $params[$name] = $config[0];
                        $this->params[$name] = array('/'.$config[0].'/',$config[1]);
                    }
                }
                $compiled = preg_replace(self::PARAM_REGEXP,self::PARAM_GUARD_REPLACE, $this->pattern);
                $compiled = preg_quote($compiled,'/');
                $this->extract = '/' . preg_replace (
                    self::PARAM_GUARD_REGEXP,
                    "'(?P<\\1>'.\$params['\\1'].')'",
                    $compiled
                ) . '/';
                $this->regexp = preg_replace(self::PARAM_GUARD_REGEXP,"\$params['\\1']", $compiled);
                if(is_array($this->data)){
                    $this->__assert(
                        count($names) === 1,
                        'Arrays can be indexed only with scalar parameter'
                    );
                    $this->type = self::ARRAY_TYPE;
                } else if(is_string($this->data) || !$this->data) {
                    $this->autoRoute = $this->data;
                    $this->data = array();
                    $this->autoPopulate = true;
                } else {
                    $this->type = self::COLLECTION;
                }
            } else {
                $this->type = self::SINGLE;
                $this->regexp = preg_quote($this->pattern, '/');
                $this->extract = '/' . $this->regexp . '/';
                $data = array();
                $data[$this->pattern] = $this->data;
                $this->data = $data;
            }
        }


        public function __complete() {
            $this->compile();
        }
    }


?>