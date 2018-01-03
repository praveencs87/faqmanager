<?php
function display_faq_manager_func(){
	global $wpdb;
	$ad_table = MYFAQ_TABLE;
	$authorise=authorizemyplugin();
	
	if($authorise!="Authorised!") {
if(isset($_POST['licence'])) {
     update_option(bml_access, $_POST['bml_access']);
      update_option(bml_transid, $_POST['bml_transid']);
}
echo '<h3>Plugin Settings</h3>';
echo '<form action="" method="post" style="background-color:#DFDFDF; padding:20px; border-radius:5px; width:560px;">';
echo '<table cellspacing="10px" cellpadding="10px" style="background-color:#DFDFDF; padding:20px; border-radius:5px; width:560px;">';
 $access=get_option('bml_access');
 $trans_id=get_option('bml_transid');
if($access=='' && $trans_id=='') {
    
    echo '<tr>';
        echo '<td style="width:160px;">Access key:</td>';
	echo '<td><input  style="width:330px" type="text" name="bml_access"  /></td>';
	echo '</tr>';
        echo '<tr valign="top">';
	echo '<td scope="row">Transaction ID:</td>';
	echo '<td><input  style="width:330px" type="text" name="bml_transid"  /></tr>';
         echo '<tr valign="top">';
	echo '<td scope="row"></td>';
	echo '<td><input type="submit"  class="button" name="licence" value="Submit" /><td></tr>';
        
                
        } else {
            
	
	$authorise=authorizemyplugin();
        if ($authorise == "Authorised!") {
    echo '<tr>';
        echo '<td width="160" scope="row">Access key:</td>';
	echo '<td><input type="text" style="width:330px" name="bml_access" value="'.get_option('bml_access').'" /></td>';
	echo '</tr>';
	echo '<tr valign="top">';
	echo '<td>Transaction ID:</td>';
	echo '<td><input style="width:330px" type="text" name="bml_transid" value="'.get_option('bml_transid').'" />';

	
	
	
		echo '<br /><b>Authorised!</b></td>';
	}
	else {
	
	echo '<tr><td colspan="2"><b>'.$authorise.'</b></td></tr>';
            echo '<tr>';
			
        echo '<td style="width:160px;">Access key:</td>';
	echo '<td><input style="width:330px" type="text" name="bml_access"  /></td>';
	echo '</tr>';
        echo '<tr valign="top">';
	echo '<td scope="row">Transaction ID:</td>';
	echo '<td><input style="width:330px" type="text" name="bml_transid"  /></tr>';
         echo '<tr valign="top">';
	echo '<td scope="row"></td>';
	echo '<td><input type="submit" class="button" name="licence" value="Submit" /><td></tr>';
            
		
                
	}
	echo '</tr>';
	
	echo '</table>';
        echo '</form>';
        echo '<br>';

}
}
  if ($authorise == "Authorised!") { 
	if ( isset($_GET['act']) && $_GET['act'] == 'delete' && isset($_GET['id']) && is_numeric($_GET['id']) ){
		$sql = "DELETE FROM {$ad_table} WHERE id = {$_GET['id']}";
		$wpdb->query($sql);
		$_GET['m'] = 'd';
	}
	if ( isset($_GET['m']) ) {
		switch ($_GET['m']){
			case 'a': {
				echo '<div id="message" class="updated fade"><p><strong>FAQ saved successfully.</strong></p></div>';
				break;
			}
			case 'e': {
				echo '<div id="message" class="updated fade"><p><strong>FAQ updated successfully.</strong></p></div>';
				break;
			}
			case 'd': {
				echo '<div id="message" class="updated fade"><p><strong>FAQ deleted successfully.</strong></p></div>';
				break;
			}

		}
	}
	?>
	<div class='wrap'>
		<div class="icon32" id="icon-link-manager"></div>
		<h2>Manage FAQs
			<a href='admin.php?page=myfaq-manage&act=new' class='button-primary'>Add New</a>
		</h2>
		<table class="widefat">
			<thead>
				<tr>
					<th>Question</th>
					<th>Answer</th>
					<th>&nbsp;</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th>Question</th>
					<th>Answer</th>
					<th>&nbsp;</th>
				</tr>
			</tfoot>
			<tbody>
				<?php
				$sql = "SELECT * FROM {$ad_table} ORDER BY id DESC";
				$table = $wpdb->get_results($sql);
				
				if ( $table ) {
					foreach( $table as $row ) {
						$edit_link = "admin.php?page=myfaq-manage&act=edit&id={$row->id}";
						$delete_link = "admin.php?page=myfaq-manager&act=delete&id={$row->id}";
						echo trim("
						<tr>
							<td>".stripslashes($row->question)."</td>
							<td>".stripslashes($row->answer)."</td>
							<td style='width:110px'>
								<a href='{$edit_link}' class='button-primary'>Edit</a>
								<a href='{$delete_link}' onclick='return confirm(\"Are you sure to delete?\")' class='button-secondary'>Delete</a>
							</td>
						</tr>
						");
					}
				} else {
					echo "<tr><td colspan='3'>No FAQ found</td></tr>";
				}
				?>
			</tbody>
		</table>
	</div>
	<?php
}
}
add_action("display_faq_manager", "display_faq_manager_func");

function faq_manage_func(){
	extract($_REQUEST);
	if ( !isset($act) || $act == '' ){
		_redirect("admin.php?page=myfaq-manager");
	}
	global $wpdb;
	switch ($act){
		case "new": {
			?>
			<div class='wrap myfaq-manager'>
				<div class="icon32" id="icon-link-manager"></div>
				<h2>Add New FAQ
					<a href='admin.php?page=myfaq-manager' class='button-primary'>FAQ Manager</a>
				</h2>
				<form action="admin.php?page=myfaq-process" method="post">
					<input type='hidden' name='act' value='new' />
					<p>
						<label for='question'>Question:</label>
						<textarea name='question' id='question' class='txtarea'></textarea>
					</p>
					<p>
						<label for='answer'>Answer:</label>
						<textarea name='answer' id='answer' class='txtarea'></textarea>
					</p>
					<p>
						<input type='submit' name='btnAddNew' id='btnAddNew' value="Save" class="button-secondary" />
					</p>
					<script type="text/javascript">
						jQuery(document).ready(function($){
							$("#btnAddNew").on("click", function(){
								if ( $("#question").val() == '' ){
									alert("Provide valid FAQ Question"); return false;
								} else if ( $("#answer").val() == '' ){
									alert("Provide valid FAQ Answer"); return false;
								} else {
									return true;
								}
							});
						});
					</script>
				</form>
			</div>
			<?php
			break;
		}
		case "edit": {
			
			if ( !isset($id) || empty($id) ){
				_redirect("admin.php?page=myfaq-manager");
			}
			
			$sql = "SELECT * FROM " . MYFAQ_TABLE . " WHERE id = {$id}";
			
			$row = $wpdb->get_row($sql);
			
			if (!$row) {
				_redirect("admin.php?page=myfaq-manager");
			}
			
			?>
			<div class='wrap'>
				<div class="icon32" id="icon-link-manager"></div>
				<h2>Edit FAQ
					<a href='admin.php?page=myfaq-manager' class='button-primary'>FAQ Manager</a>
				</h2>
				<form action="admin.php?page=myfaq-process" method="post">
					<input type='hidden' name='act' value='edit' />
					<input type='hidden' name='id' value='<?php echo $id; ?>' />
					<p>
						<label for='question'>Question:</label>
						<textarea name='question' id='question' class='txtarea'><?php echo stripslashes($row->question); ?></textarea>
					</p>
					<p>
						<label for='answer'>Answer:</label>
						<textarea name='answer' id='answer' class='txtarea'><?php echo stripslashes($row->answer); ?></textarea>
					</p>
					<p>
						<input type='submit' name='btnAddNew' id='btnAddNew' value="Update" class="button-secondary" />
					</p>
					<script type="text/javascript">
						jQuery(document).ready(function($){
							$("#btnAddNew").on("click", function(){
								if ( $("#question").val() == '' ){
									alert("Provide valid FAQ Question"); return false;
								} else if ( $("#answer").val() == '' ){
									alert("Provide valid FAQ Answer"); return false;
								} else {
									return true;
								}
							});
						});
					</script>
				</form>
			</div>
			<?php
			break;
		}
	}
	
}
add_action("faq_manage", "faq_manage_func");

function faq_process_func(){
	extract($_REQUEST);
	if ( !isset($act) || $act == '' ){
		_redirect("admin.php?page=myfaq-manager");
	}
	global $wpdb;
	switch($act){
		case 'new': {
			if ( empty($question) ) {
				$err['FAQ Question'] = "FAQ Question Missing";
			}
			if ( empty($answer) ) {
				$err['FAQ Answer'] = "FAQ Answer Missing";
			}
			
			if ( $err ){
				echo "<h1>Following error occured:</h1>";
				foreach($err as $key => $error ){
					echo "<strong>{$key}</strong> : {$error}<br/>";
				}
				die("<a href='admin.php?page=myfaq-manage&act=new' class='button-primary'>Try Again</a>");
			} else {
				// insert new add here 
				$wpdb->insert( 
					MYFAQ_TABLE, 
					array( 
						'question' => $question,
						'answer' => $answer 
					) 
				);
				_redirect("admin.php?page=myfaq-manager&m=a");
			}
			break;
		}
		case 'edit': {
			if ( empty($question) ) {
				$err['FAQ Question'] = "FAQ Question Missing";
			}
			if ( empty($answer) ) {
				$err['FAQ Answer'] = "FAQ Answer Missing";
			}
			
			if ( $err ){
				echo "<h1>Following error occured:</h1>";
				foreach($err as $key => $error ){
					echo "<strong>{$key}</strong> : {$error}<br/>";
				}
				die("<a href='admin.php?page=myfaq-manage&act=new' class='button-primary'>Try Again</a>");
			} else {
				$wpdb->update( 
					MYFAQ_TABLE, 
					array( 
						'question' => $question,
						'answer' => $answer 
					),
					array(
						'id' => $id
					)
				);
			_redirect("admin.php?page=myfaq-manager&m=e");
			}
			break;
		}
	}
}
add_action("faq_process", "faq_process_func");
/*** helping function ***/
if ( !function_exists("_redirect") ) {
	function _redirect( $url ) {
		if ( !headers_sent() )
			wp_redirect( $url );
		else
			echo "<meta http-equiv='Refresh' content='0; URL={$url}' />";
		exit();
	}
}