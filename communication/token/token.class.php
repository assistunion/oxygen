<?

    class Oxygen_Communication_Token extends Exception {

        public $data = null;

        public function __construct($embed, $data, $continuation, $digest) {
            parent::__construct('Communication: '.$continuation);
            $this->data = array(
                'status'       => 'ask',
                'embed'        => $embed,
                'data'         => $data,
                'continuation' => $continuation,
                'digest'       => $digest
            );
        }
        public function getData() {
            return $this->data;
        }
    }


?>