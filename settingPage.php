<?php 
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__))
	die('Direct Access not permitted...');


	
	function getPage($url, $param = '')
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url); // Define target site
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); // Return page in string
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE); // Follow redirects
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		if(!empty($param['postData']))
		{
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $param['postData']);
		}
		$page = curl_exec($ch);
		
		return $page;
		
	}
	
	
	
	$activation = 0;
	$accKey = @get_option("prepostseo_acckey");
	
	if(!empty($_POST['submit']) and !empty($_POST['account_api']))
	{
		$accKey = $_POST['account_api'];
		@update_option('prepostseo_acckey', $_POST['account_api']);
	}
	
	if(!empty($_POST['submit']) and !empty($_POST['account_api']) and !empty($_POST['update_api']))
	{
		$accKey = $_POST['account_api'];
		@update_option('prepostseo_acckey', $_POST['account_api']);
	}
	
	$postData = array(
		"accKey" => @$accKey,
		"site" => get_site_url()
	);
	$param['postData'] = http_build_query($postData);
	
	$data = getPage(PPS_ACTION_SITE.'frontend/getAccountSettings/', $param);
	
	$user = json_decode($data);
	
	if(!empty($_POST['account_api']) and !empty($_POST['update_api']))
	{
		
		if(empty($user->limit)){
			
			add_action( 'admin_notices', 'pps_invalid_key_error' );
		}
	}
	
	
	if(!empty($user->limit) and !empty($accKey))
	{
		$activation = 1;
		$premium = ($user->premium == 1)? '<strong class="green">Premium User</strong>' : '<strong class="red">FREE</strong>';
		$limitUsed = ($user->used/$user->limit)*100;
		$limitUsed = round($limitUsed, 2);
		
		if($limitUsed > 100)
		{
			$limitUsed = 100;
		}
		if($user->used > $user->limit)
		{
			$usedQ = $user->limit;
		}
		
	} else {
		if(!empty($_POST['account_api']))
		{
			$errorMsg =  "API key you entered is not valid; <br>";
		}
		elseif(!empty($accKey)){
			$errorMsg =  "Error in validating API Key";
		}
	}
	

?>

<?php if($activation == 1 and  empty($_GET['editapi'])): ?>



<table class="pps-setting-table" >
	<tr>
    	<td colspan="2" style="background:#EBEBEB; color:#000; font-size:16px; text-align:center;">
        	<span style="line-height:30px; text-shadow:1px 1px 1px #fff; margin-right:-70px;">-- Account Details --</span> 
            <a href="<?php echo PPS_ACTION_SITE; ?>account" target="_blank" style="float:right;" class="button-secondary button-small">view account</a>
        </td>
    </tr>
    <tr>
    	<td width="30%">Name</td>
        <td><?php echo @$user->name; ?></td>
    </tr>
    <tr>
    	<td>Email Address</td>
        <td><?php echo @$user->email; ?></td>
    </tr>
    <tr>
    	<td>API Key</td>
        <td>
        <?php echo @$accKey; ?>
        <a href="<?php echo admin_url(); ?>admin.php?page=prepost-seo&editapi=1" style="float:right;">Edit/Change API key</a>
        </td>
    </tr>
    <tr>
    	<td>Queries Limit</td>
        <td><strong><?php echo @$user->limit; ?></strong> 
        <a href="<?php echo PPS_ACTION_SITE.'plans?accKey='.$accKey; ?>" target="_blank" style="float:right;">+ Add more queries</a></td>
    </tr>
    <tr>
    	<td>Queries Used</td>
        <td><strong><?php echo $user->used; ?></strong> <span style="margin-left:20px;color:#9C9B9B;">(<strong><?php echo $limitUsed; ?>%</strong> queries used)</span></td>
    </tr>
    <tr>
    	<td>Membership Type</td>
        <td><?php echo $premium; ?></td>
    </tr>
    <tr>
    	<td>Want More Queries?</td>
        <td align="center"><a href="<?php echo PPS_ACTION_SITE.'plans?accKey='.$accKey; ?>" target="_blank" class="button button-primary button-large">Add more queries to your Account</a></td>
    </tr>
</table>


<?php elseif(!empty($_GET['editapi'])): ?>

<form method="post" action="">
<table  class="pps-setting-table">
	<tr>
    	<td colspan="2" style="background:#EBEBEB; color:#000; font-size:16px; text-align:center;">
        	<span style="line-height:30px; text-shadow:1px 1px 1px #fff; margin-right:-70px;">- Edit Account Details -</span> 
            <a href="<?php echo PPS_ACTION_SITE; ?>account" target="_blank" style="float:right;" class="button-secondary button-small">view account</a>
        </td>
    </tr>
    
	<?php if(!empty($_POST['update_api'])): ?>
    <tr>
        <td colspan="2" style="text-align:center; color:#000;">
        Changes Saved successfully <br>
        <a href="<?php admin_url(); ?>admin.php?page=prepost-seo"> &laquo;Back to Setting Page</a>
        </td>
        
    </tr>	
    <?php else: ?>
	
    <tr>
        <td width="30%">Account API key</td>
        <td>
            <input type="hidden" name="update_api" value="1" /> 
            <input type="text" name="account_api" value="<?php echo @$accKey; ?>" style="width:90%; border:1px solid #156780; padding:10px;" />
        </td>
    </tr>
    <tr>
        <td><a href="<?php admin_url(); ?>admin.php?page=prepost-seo"> &laquo;Back to Setting Page</a></td>
        <td align="center">
            <input type="submit" name="submit" value="Save Changes" class="button-primary" />
        </td>
    </tr>
    <?php endif; ?>	
        
    

</table>
</form>


<?php else: ?>



<form method="post" action="">
<table  class="pps-setting-table">
	<tr>
    	<td colspan="2" style="background:#EBEBEB; color:#000; font-size:16px; text-align:center;">
        	<span style="line-height:30px; text-shadow:1px 1px 1px #fff; margin-right:-70px;">- Plugin Setting -</span> 
            <a href="<?php echo PPS_ACTION_SITE; ?>login?reffer=wordpress" target="_blank" style="float:right;" class="button-secondary button-small">Create account</a>
        </td>
    </tr>
    
    <?php if(empty($accKey)): ?>
    <tr>
        <td colspan="2" style="color:#000;">
            To activate this plugin please <a href="<?php echo PPS_ACTION_SITE; ?>login?reffer=wordpress" target="_blank">create an account</a> at prepostseo.com<br>
            Then Get API key from your <a href="<?php echo PPS_ACTION_SITE; ?>account" target="_blank">account page</a>
            and paste that API key in the input box below and click on "Save Changes" Button
            <br>
            <a href="<?php echo PPS_ACTION_SITE; ?>login?reffer=wordpress" target="_blank">click here to get API KEY</a>
        </td>
    </tr>
    <?php else: ?>
    <tr>
        <td colspan="2" style="color:#000;">
            Invalid API key is used to integrate Plugin, Please check your <a href="<?php echo PPS_ACTION_SITE; ?>account" target="_blank">prepost account</a> page and make sure 
            API key that you entered is correct.
            
        </td>
    </tr>
    <?php endif; ?>
    
    <tr>
        <td width="30%">Account API key</td>
        <td>
                <input type="text" name="account_api" value="<?php echo @$accKey; ?>" style="width:90%; border:1px solid #156780; padding:10px;" />
        </td>
    </tr>
	<tr>
        <td colspan="2" align="center">
            <input type="submit" name="submit" value="Save Changes" class="button-primary" />
        </td>
    </tr>
	
        
    

</table>
</form>


<?php endif;

