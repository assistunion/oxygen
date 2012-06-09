<?

    if(!class_exists('Oxygen')) {
        require('oxygen/oxygen.class.php');
        $o = new Oxygen('C:\\tmp\\oxygen-cache-v1.0');
        $o->run(basename(__FILE__));
    }
    
    $c = $scope->Sample();

    $c->put_page();
    

    

?>