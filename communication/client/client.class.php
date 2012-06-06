<?
    class Oxygen_Communication_Client extends Oxygen_Object {

        public $continuation = '';
        public $owner = null;
        public $session = null;
        public $callDigest = '';
        public $callResult = null;
        public $callError = null;

        public function __construct($owner, $continuation, $callDigest = '', $callResult = null, $callError = null) {
            if ($continuation === 'new') {
                $continuation = self::newGuid();
            }
            $this->continuation = $continuation;
            $this->owner = $owner;
            $this->callDigest = $callDigest;
            $this->callResult = $callResult;
            $this->callError = $callError;
        }

        public function getName() {
            return 'oxygen-call-' . $this->continuation;
        }

        public function getCallName($digest) {
            return $this->getName() . '-' . $digest;
        }

        public function __complete() {
            if (!$this->callError) {
                $this->session = $this->scope->SESSION;
                $this->session[$this->getCallName($this->callDigest)] = $this->callResult;
            }
        }

        public function getCallDigest() {
            $trace = debug_backtrace();
            $chunks = array();
            foreach ($trace as $item) {
                $file = isset($item['file'])
                    ? $item['file']
                    : 'no-file'
                ;
                $line = isset($item['line'])
                    ? $item['line']
                    : '0'
                ;
                $chunks[]=$file . '/' . $line;
            }
            return sha1(implode(':',$chunks));
        }

        public function ask($template, $data) {
            $digest = $this->getCallDigest();
            if($this->callDigest === $digest) {
                if ($this->callError !== null) {
                    throw new Oxygen_Communication_ClientException($this->callError);
                } else {
                    return $this->callResult;
                }
            }
            $name = $this->getCallName($digest);
            if(isset($this->session[$name])) {
                return $this->session[$name];
            } else {
                throw new Oxygen_Communication_Token(
                    $this->owner->embed_($template, $data),
                    $data,
                    $this->continuation,
                    $digest
                );
            }
        }

        public function end() {
            $name = preg_quote($this->getName());
            $pattern = "/^$name/";
            $this->session->removeRegexp($pattern);
            return array(
                'status'       => 'closed',
                'continuation' => $this->continuation
            );
        }
    }


?>