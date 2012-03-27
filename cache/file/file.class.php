<?

	class Oxygen_Cache_File extends Oxygen_Cache {

		const SAFE_FILE = "/^[0-9A-Za-z_].*$/";
		const FILE_TEMPLATE = 'oxygen-{0}.tmp';

		private $temp_path = '';

		public function __complete() {
			$this->temp_path = $this->scope->temp_path;
		}

		public function getPath($key) {
			$hash = md5($key);
			$path = $this->temp_path 
				  . DIRECTORY_SEPARATOR 
				  . Oxygen_Utils_Text::format(self::FILE_TEMPLATE,$hash)
				  ;
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
					$this->throwException('Nonexistent cache key');
				}
			}
			return file_get_contents($path);
		}

		public function storeAll($key,$array) {
			$path = $this->getPath($key);
			$f = fopen($path,'w');
			try {
				foreach ($array as $value) {
					fwrite($f, $value);
				}
			} finally {
				fclose($f);
			}
		}

		public function store($key, $value) {
			$path = $this->getPath($key);
			$f = fopen($path,'w');
			try {
				fwrite($f, $value);
			} finally {
				fclose($f);
			}
		}

		public function exists($offset) {
			return file_exists($this->getPath($offset));
		}

		public function remove($offset) {
			unlink($offset);
		}

	}

?>