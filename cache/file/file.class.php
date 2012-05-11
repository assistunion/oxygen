<?

	class Oxygen_Cache_File extends Oxygen_Cache {

		const SAFE_FILE = '/^[0-9A-Za-z_].*$/';
		const FILE_TEMPLATE = 'oxygen-{0}.tmp';

		private $temp_path = '';


        public function __construct($temp_path = false) {
            $this->temp_path = $temp_path;
        }

		public function __complete() {
            if($this->temp_path === false) {
                $this->temp_path = $this->scope->temp_path;
            }
		}

        public function age($key) {
            $path = $this->getPath($key);
            return file_exists($path)
                ? time() - filemtime($path)
                : 0x7FFFFFFF
            ;
        }

		public function getPath($key) {
            $hash = md5($key);
			$path = $this->temp_path
				  . DIRECTORY_SEPARATOR
				  . Oxygen_Utils_Text::format(self::FILE_TEMPLATE,$hash)
				  ;
			return $path;
		}

        // Loads given key. In case of nonexistent key
        // throws an exception unless optional second parameter is given
        // (which is returned instead)
        public function load($key) {
            $path = $this->getPath($key);
            if(!file_exists($path)){
                if(func_num_args()==2){
                    return func_get_arg(1);
                } else {
                    $this->throw_Exception('Nonexistent cache key');
                }
            }
            return file_get_contents($path);
        }

        // Deserializes given key. In case of nonexistent key
        // throws an exception unless optional second parameter is given
        // (which is returned instead)
        public function deserialize($key) {
            $path = $this->getPath($key);
            if(!file_exists($path)){
                if(func_num_args()==2){
                    return func_get_arg(1);
                } else {
                    $this->throw_Exception('Nonexistent cache key');
                }
            }
            return unserialize(file_get_contents($path));
        }

        public function serialize($key, $value) {
            $this->store($key,serialize($value));
        }

		public function storeAll($key,$array) {
			$path = $this->getPath($key);
			$f = fopen($path,'w');
			$ex = null;
			try {
				foreach ($array as $value) {
					fwrite($f, $value);
				}
			} catch(Exception $e) {
				$ex = $e;
			}
			fclose($f);
			if($ex !== null) {
				throw $ex;
			}
		}

        public function echoValue($key) {
            readfile($this->getPath($key));
        }

		public function store($key, $value) {
			$path = $this->getPath($key);
			$f = fopen($path,'w');
			$ex = null;
			try {
				fwrite($f, $value);
			} catch(Exception $e) {
				$ex = $e;
			}
			fclose($f);
			if($ex !== null) {
				throw $ex;
			}
		}

		public function exists($offset) {
			return file_exists($this->getPath($offset));
		}

		public function remove($offset) {
			unlink($this->getPath($offset));
		}

	}

?>