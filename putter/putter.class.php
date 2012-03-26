<?
    class Oxygen_Putter {
        
        private $owner;

        public function __construct($owner) {
            $this->owner = $owner;
        }

        public function __call($resource,$args) {
            $path = $this->$resource;
            return $this->owner->executeResource($path,$resource,$args);
        }

        function __get($name) {
            return Oxygen_Loader::pathFor(get_class($this->owner),$name);
        }
    }

?>