<!DOCTYPE html>
<html>
<?
    $this->getScope()->js->extra('http://code.jquery.com/jquery-1.7.1.min.js');
    try {
        $body = $this->get->view();
        $less = $this->getScope()->less->compile();
    } catch (Oxygen_Exception $ex) {
        $body = $ex->get->view();
    } catch (Exception $ex) {
        $body = $this->scope->Oxygen_Exception_Wrapper($ex)->get->view();
    }
?>
<head>
<?=$this->put->stylesheets()?>
<?=$this->put->html5shim()?>
</head>
<body>
<?=$body?>
<?=$this->put->javascripts()?>
</body>
</html>