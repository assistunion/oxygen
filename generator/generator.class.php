<?

    class Oxygen_Generator extends Oxygen_Object {
        private $models = null;
        public function __complete() {
            $this->models = new ArrayObject();
            $this->scope->models = $this->models;
        }

        private function collectPaths($start, &$paths = array()) {
            foreach(glob($start . '*.yml') as $file) {
                $paths[] = $file;
            }
            foreach(glob($start . '*') as $dir){
                if(is_dir($dir)) {
                     $this->collectPaths($dir . DIRECTORY_SEPARATOR, $paths);
                }
            }
            return $paths;
        }

        public function generate() {
            $paths = $this->collectPaths(Oxygen_Loader::CLASS_PATH . DIRECTORY_SEPARATOR);
            $tpltime = filemtime(Oxygen_Loader::pathFor('Oxygen_Meta','model_base'));
            foreach($paths as $path) {
                try {
                    $class = Oxygen_Loader::classFor(dirname($path));
                    $yaml  = Utils_YAML::load($path);
                    $time  = filemtime($path);
                    $meta  = $this->scope->Oxygen_Meta($class,$yaml,max($time,$tpltime));
                } catch (Oxygen_Exception $e) {
                    throw $e;
                } catch (Exception $e) {
                    $this->throwException('YAML Error',0,$e);
                }
            }
            foreach($this->models as $model) {
                $model_path = Oxygen_Loader::pathFor(
                    $model->getModelName(),false,false,false
                );
                $model_base_path = Oxygen_Loader::pathFor(
                    $model->getModelBaseName(),false,false,false
                );
                if(!file_exists($model_base_path) 
                  || filemtime($model_base_path) < $model->time
                ) {
                    $f = fopen($model_base_path,'w');
                    fwrite($f,$model->get->model_base());
                    echo "Generated $model_base_path\n";
                    fclose($f);
                }

                if(!file_exists($model_path)) {
                    $f = fopen($model_path,'w');
                    fwrite($f,$model->get->model());
                    echo "Generated $model_path\n";
                    fclose($f);
                }
            }
        }
    }


?>