<form method="POST" class="login-form">
	<div class="warning"><?=$this->scope->auth->message?></div>
	<?if($this->scope->auth->role):?>
	<?$this->put_roles()?>
	<input type="submit" name="sign-out" value="Sign out"/>
	<?else:?>
	<?if($this->scope->auth->login):?>
		<label><span>Login:</span><input name="login" value="<?=htmlspecialchars($this->scope->auth->login)?>"/></label>
		<label><span>Passord:</span><input class="logon-focus" name="password" type="password"/></label>
	<?else:?>
		<label><span>Login:</span><input class="logon-focus" name="login"/></label>
		<label><span>Passord:</span><input name="password" type="password"/></label>
	<?endif?>
	<input type="submit" name="authenticate" value="Sign in"/>
	<?endif?>
</form>