<!DOCTYPE html>
<html>
<?
    $o = $this->scope->o;
    try {
        $body = $this->get();
        $head = $this->get_head(Oxygen_Class::__compileAssets($o->assets));
    } catch(Exception $e) {
        try {
            $body = $o->get_exception($e);
            $head = $this->get_head(Oxygen_Class::__compileAssets($o->assets));
        } catch (Exception $critical) {
            $body = 'initial:'  . $e->getMessage() . '<br />'
                  . 'critical:' . $critical->getMessage() . '<br />';
            $head = '<title>Exception in handling</title>';
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