<div class="wrap">
	<h2>Google Calender Options</h2>
	
	<?php
		if($_POST['gc_saved'] == 'Y'){
			echo "<div class='updated'><p>saved</p></div>";
		}
	?>
	
	<form action="" method="post">
		<input type="hidden" name="gc_saved" value="Y" />
		<table class="form-table">
			<tr>
				<td>Application Name</td>
				<td cospan="2"><input size="60" type="text" name="gc_app_name" value="<?php echo $gc['app_name']; ?>" /></td>
			</tr>
			<tr>
				<td>Client Id</td>
				<td cospan="2"><input size="60" type="text" name="gc_client_id" value="<?php echo $gc['client_id']; ?>" /></td>
			</tr>
			<tr>
				<td>Client Secret </td>
				<td cospan="2"><input size="60" type="text" name="gc_client_secret" value="<?php echo $gc['client_secret']; ?>" /></td>
			</tr>
			<tr>
				<td>Developer/Api Key</td>
				<td cospan="2"><input size="60" type="text" name="gc_api_key" value="<?php echo $gc['api_key']; ?>" /></td>
			</tr>
			
			<tr>
				<td>Redirect Urls</td>
				<td><?php echo get_option('siteurl'); ?> </td>
			</tr>
			
			<tr>
				<td><input type="submit" value="save" class="button-primary"  /></td>
			</tr>
		</table>
	</form>
</div>
