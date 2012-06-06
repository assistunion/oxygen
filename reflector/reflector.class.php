<?
	class Oxygen_Reflector {

		public $name = '';
		public $factory = null;
		private $setScope = 'setScopeNone';
		private $setScopeName = 'scope';
		private $reflected = null;
		private $parent = null;
		private $overrides = false;
        private $info = array();

		private static $defaults = array(
			'factory'  => false,
			'complete'  => '__complete',
			'setScope' => true,
		);

		public function __construct($name) {
			$this->name = $name;
			$ref = $this->reflected = new ReflectionClass($name);

			# === Handling scope overrides ===
			$newScope = false;
			// current scope
			try {
				$__currentScope = $ref->getProperty('__oxygenScope')->getValue();
			} catch (ReflectionException $e) {
				$__currentScope = false;
			}
			// parent scope
			try {
				$par = $ref->getParentClass();
				if($par) $par = $par->getParentClass();
				if ($par) {
					$__parentScope = $par->getProperty('__oxygenScope')->getValue();
				} else {
					$__parentScope = false;
				}
			} catch (ReflectionException $e) {
				$__parentScope = false;
			}

			if ($__parentScope || $__currentScope) {
				if(!is_array($__parentScope)) $__parentScope = array();
				if(!is_array($__currentScope)) $__currentScope = array();
				$this->overrides = array_merge($__parentScope, $__currentScope);
			} else {
				$this->overrides = false;
			}

			try {
				$info = $ref->getMethod('__oxygen_info');
				if (!$info->isStatic()) $info = false;
			} catch(ReflectionException $e) {
				$info = false;
			}
			if ($info !== false) {
				$this->info = self::$defaults;
				$params_ = &$this->info;
				$info->invoke(null, $params_);
				$this->factory = $params_['factory'] === false
					? array($this->reflected, 'newInstance')
					: $params['factory']
				;
				$setScope = $params_['setScope'];
				if ($setScope === true) {
					$this->setScope = 'setScopeDefault';
				} else if ($setScope === false) {
					$this->setScope = 'setScopeNone';
				} else if (preg_match('/^(\\$?)([A-Za-z_][A-Za-z0-9_]*)$/', $setScope, $match)) {
					$this->setScope = $match[1] === '$'
						? 'setScopeVar'
						: 'setScopeMethod'
					;
					$this->setScopeName = $match[2];
				} else {
					$this->setScope = 'setScopeThrow';
					$this->setScopeName = $setScope;
				}
			} else {
				$this->factory = array($this->reflected, 'newInstance');
			}

		}

		private function setScopeDefault($obj, $scope) {
			$obj->scope = $scope;
		}

		private function setScopeNone($obj, $scope) {
			// Nothing here;
		}

		private function setScopeVar($obj, $scope) {
			$obj->{$this->setScopeName} = $scope;
		}

		private function setScopeMethod($obj, $scope) {
			$obj->{$this->setScopeName}($scope);
		}

		private function setScopeThrow($obj, $scope) {
			throw $scope->Exception("setScope = '{$this->setScopeName}' is not valid");
		}

		public function newInstance($args, $scope) {
			$result = call_user_func_array($this->factory, $args);
			if($this->overrides !== false) {
				$scope = $scope->Scope();
				$scope->__set($this->overrides);
			}
			$this->{$this->setScope}($result, $scope);
			if(isset($this->info['complete'])) {
				$result->{$this->info['complete']}($scope);
			}
			return $result;
		}

	}


?>