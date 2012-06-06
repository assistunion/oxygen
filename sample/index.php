<?

    if(!class_exists('Oxygen')) {
        require('oxygen/oxygen.class.php');
        $o = new Oxygen('C:\\tmp\\oxygen-cache-v1.0');
        $o->run(basename(__FILE__));
    }

    $o->loadClass('Oxygen_');
    $r = $scope->Oxygen_Router('a{x:int}b',array('A','B','C'));

    foreach($r as $x => $y) {
        echo "$x";
    }

    $o->compileAssets();
    

?>