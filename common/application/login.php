<form class="logon-form" action="<?=$this->go('login')?>" method="POST">
	<?$auth = $this->scope->auth?>
	<?if($auth->role):?>
		<span class="user"><?=$auth->login?></span> is logged as
		<select class="role">
			<?foreach($auth->roles as $role):?>
				<option><?=$role?></option>
			<?endforeach?>
		</select>
		<input type="submit" name="sign-out" value="Sign-Out"/>
	<?else:?>
		<span class="message">You are not signed in</span>
	<?endif?>
</form>