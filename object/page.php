<!DOCTYPE html>
<html>
<?  
    $o = $this->scope->o;
    try {
        $body = $this->put();
        $head = $this->get_head($o->compileAssets());
    } catch(Exception $e) {
        try {
            $body = $o->get_exception($e);
            $head = $this->get_head($o->compileAssets());
        } catch (Exception $critical) {
            $body = 'initial:'  . $e->getMessage() . '<br />' 
                  . 'critical:' . $critical->getMessage() . '<br />';
            $head = '<title>Exception in handling</title>'
        }
    }
?>
<head>
    <?=$head?>
</head>
<body>
    <?=$body?>
</body>
</html>