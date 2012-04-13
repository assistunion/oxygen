<?

    class Oxygen_Route extends Oxygen_Object {

        const TYPES_REGEXP = '/^(?:(int)|(str)|\/([^\/]*(?:\\\\\/[^\/]*)*)\/)$/';
        const PARAM_REGEXP = '/{([0-9A-Za-z_]+):([^{}]+)}/';


        const PARAM_GUARD_REPLACE = '#\\1#';
        const PARAM_GUARD_REGEXP  = '/#([0-9A-Za-z_]+)#/e';

        const INT_REGEXP_BARE = '[0-9]+';
        const STR_REGEXP_BARE = '[^/]+';

        const SINGLE     = 0;
        const COLLECTION = 1;

        public $class = '';
        public $route = '';
        public $index = '';
        public $model = null;
        public $regex = '';
        public $type  = '';

        public function __construct($index, $class, $route, $model){
            $this->class = $class;
            $this->route = $route;
            $this->index = $index;
            $this->model = $model;
            list($this->type, $this->regex) = $this->compile($index,$route);
        }

        public function compile($index, $route){
            $route = trim($route,'/');
            if(0 < preg_match_all(self::PARAM_REGEXP, $route, $match)){
                $names = $match[1];
                $types = $match[2];
                $params = array();
                foreach($names as $i => $name){
                    if(isset($params[$name])) {
                        $this->throwException(self::ROUTE_PARAM_REDEFINED);
                    } else {
                        $params[$name] = self::getRegexpFor($types[$i]);
                    }
                }
                $compiled = preg_replace(self::PARAM_REGEXP,self::PARAM_GUARD_REPLACE, $route);
                $compiled = preg_quote($compiled,'/');
                $compiled = preg_replace(self::PARAM_GUARD_REGEXP,"'(?P<_{$index}_\\1>'.\$params['\\1'].')'", $compiled);
                return array(self::COLLECTION,$compiled);
            } else {
                return array(self::SINGLE,preg_quote($route,'/'));
            }
        }

        public function get($params){
            return 'x';
        }

        private function getRegexpFor ($type){
            if(preg_match(self::TYPES_REGEXP, $type,$match)){
                switch (count($match)) {
                    case 2: return self::INT_REGEXP_BARE;
                    case 3: return self::STR_REGEXP_BARE;
                    case 4: return $match[3];
                }
            } else {
                $this->throwException(self::INVALID_PARAM_TYPE);
            }
        }

        private function getClassFor($class,$model){
            if(is_array($class)){
                if(is_callable($class)) return call_user_func($class,$model);
                else $this->throwException(self::INVALID_CLASS_RETRIEVER);
            } elseif(is_string($class)) {
                return $class;
            } elseif(is_callable($class)) {
                // for PHP 5.3+
                return call_user_func($class,$model);
            } else {
                $this->throwException(self::INVALID_CLASS_RETRIEVER);
            }
        }




    }


?>