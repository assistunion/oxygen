<?

    class Oxygen_Router_Iterator extends Oxygen_Object implements Iterator {

        private $owner = null;
        private $internal = null;
        private $useKeys = false;

        public function __construct($owner) {
            $this->owner = $owner;
            $data = $owner->getData();
            if (is_array($data)) {
                $data = new ArrayObject($data);
                $this->useKeys = true;
            } 
            $this->internal = $data->getIterator();
        }

        public function current(){
            return $this->owner->wrap($this->internal->current());
        }

        public function key(){
            return $this->owner->formatKey($this->useKeys
                ? $this->internal->key()
                : $this->internal->current()
            );
        }

        public function next() {
            $this->internal->next();
        }

        public function valid() {
            return $this->internal->valid();
        }

        public function rewind() {
            $this->internal->rewind();
        }

    }


?>