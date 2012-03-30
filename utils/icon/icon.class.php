<?

	class Oxygen_Utils_Icon {
		public static function silk($name) {
			$scope = Oxygen_Scope::root();
			if(!preg_match("/^[a-z_]+$/",$name)) $scope->throwException('Invalid name');
			$relative = 'silk-icons/icons/'.$name.'.png';
			$path = Oxygen_Lib::path($relative);
			if(!file_exists($path)) $scope->throwException('Icon not found');
			return Oxygen_Lib::url($relative);
		}
		public static function get($name){
			return self::silk($name);
		}
	}

?>