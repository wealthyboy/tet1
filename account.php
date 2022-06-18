<?php
	session_start();
	ob_start();
	
	require_once('classes/class.db.php');
	require_once('classes/class.ordered_product.php');
	require_once('classes/class.state.php');
	
	$page_title = 'Shipping and Order History';
	require_once('includes/header.php');
	require_once('functions/pagination.php');

	if (!isLoggedIn()) {
		header('Location: index.php');
	} else {
		if (isset($_GET['update-shipping'])) {
			$user = json_decode($_COOKIE['user'], true);
			$user_id = $user['id'];

			$first_name = DB::getInstance()->prep(trim($_GET['fname']));
			$last_name =  DB::getInstance()->prep(trim($_GET['lname']));
			$phone =      DB::getInstance()->prep(trim($_GET['mnumber']));
			$address =    DB::getInstance()->prep(trim($_GET['address']));
			$city =       DB::getInstance()->prep(trim($_GET['city']));
			$land_mark =  DB::getInstance()->prep(trim($_GET['lmark']));
			$state_id =   DB::getInstance()->prep(trim($_GET['state']));

			$query = "UPDATE users SET first_name = '$first_name', last_name = '$last_name', phone = '$phone', address = '$address', city = '$city', landmark = '$land_mark', state_id = '$state_id' WHERE id = $user_id LIMIT 1";

			$update_ship = mysqli_query($GLOBALS['dbc'], $query);

			if ($update_ship) { ?>
				<script>
				location.search = 'update=ok';
				</script>
		<?php }
		}

		$u = new User();
		$user_cookie = json_decode($_COOKIE['user'], true);
		$user_id = $user_cookie['id'];
		
		$user = $u->find_by_id($user_id);
		//print_r($user);
       
		$user_order = DB::getInstance()->run_sql("SELECT * FROM orders WHERE user_id = '$user_id' ORDER BY order_id DESC");
	
		
		
	}
?>

<h3 class="page_header center">Shipping/Order History Details</h3>
<div class="product_wrapp">
	<form id="shipping_form" method="GET" action="<?php htmlspecialchars($_SERVER['PHP_SELF']); ?>">
		<p class="pass"><?php echo (!empty($_GET['update']) && $_GET['update'] == 'ok' ? 'Shipping Details Updated Successfully' : ''); ?>
		</p>
		<p>
			<label for="fname">First Name</label><br />
			<input type="text" name="fname" id="fname" value="<?= $user->first_name; ?>" placeholder="First name" />
		</p>
		<p>
			<label for="lname">Last Name</label><br />
			<input type="text" name="lname" id="lname" value="<?= $user->last_name; ?>" placeholder="Last name" />
		</p>
		<p>
			<label for="mnumber">Mobile Number</label><br />
			<input type="text" name="mnumber" id="mnumber" value="<?= $user->phone; ?>" placeholder="Phone name" />
		</p>
		<p>
			<label for="address">Street Address</label><br />
			<input type="text" name="address" id="address" value="<?= $user->address; ?>" placeholder="Street address" />
		</p>
		<p>
			<label for="city">City</label><br />
			<input type="text" name="city" id="city" value="<?= $user->city; ?>" placeholder="e.g Surulere" />
		</p>
		<p>
			<label for="lmark">Land Mark</label><br />
			<input type="text" name="lmark" id="lmark" value="<?= $user->landmark; ?>" placeholder="e.g Opposite mobile filling station" />
		</p>
		<p>
			<?php
				$user_state = State::getInstance()->find_state($user->state_id);
			?>
			<label for="state">State</label><br />
			<select name="state" id="state" required="required">
			<?php
				$data =DB::getInstance()->run_sql("SELECT * FROM state");
				
	          foreach ($data as $details){
					if ($details->id == $user_state->id) { ?>
						<option value="<?php echo $details->id  ?>" selected="selected"><?php echo $details->name; ?></option>
			<?php	}

					else { ?>
							<option value="<?php echo $details->id; ?>"><?php echo $details->name; ?></option>
			<?php	} 
				} ?>
			</select>
		</p>
		<p>
			<input style="width: 100%;" type="submit" name="update-shipping" value="Update" id="shipping_button" />
		</p>
	</form>	

	<div id="order_history_wrapp">
	<div id="order_history">
	<?php
	$od = new Ordered_Product();
		if (!empty($user_order)) {
		
			foreach ($user_order as $details) { 
				
				$total_price = 0; 

				$ot = strtotime($details->order_time);
				$ot = date('h:i A', $ot);
				$order_dates = explode('/', $details->order_date); 
				$months = [
					'01' => 'January',
					'02' => 'February',
					'03' => 'March',
					'04' => 'April',
					'05' => 'May',
					'06' => 'June',
					'07' => 'July',
					'08' => 'August',
					'09' => 'September',
					'10' => 'October',
					'11' => 'November',
					'12' => 'December'
					];
				
				$order_date_string = $months[$order_dates[1]].' '.$order_dates[0].', '.$order_dates[2].', '.$ot;?>
				<h3><?= $order_date_string; ?></h3>
					<?php $products =$od->run_sql("SELECT *FROM ordered_product WHERE order_id = '{$details->order_id}' "); ?>
				  
			<div>
			<?php 	foreach ($products as $od){?>	
					<ul>
					
				<?php
			          
						echo '<li>'.$od->item_name.' - '.CURRENCY.$od->total.'</li>';
						
				
				?>
					</ul>
					<hr />
					<p>Order Number: <?= $details->order_id ?> </p>
					<p>Payment Method: <?= ($details->payment_method == 'cash' ? 'Pay on Delivery' : 'Debit/Credit Card'); ?> </p>
				<?php }?>
				</div>
				
				
				
	 <?php }
	}?>
	</div> 
	</div> <!-- order_history_wrapp -->	

	<form id="track_order_form" method="GET" action="track_order.php">
		<p class="track_order_msg_output error"></p>
		<p>
			<label>Your tracking number</label>
			<input type="text" name="order-number" id="tracking_number">
		</p>
		<p>
			<label>Your email</label>
			<input id="tracking_email" type="text" name="track-email" value="<?= $user->email; ?>">
		</p>
		<p>
			<button style="width: 100%;" id="track_order_button2">Track</button>
		</p>
	</form>
</div>

<div class="clearfix"></div>
<?php
	require_once('modules/about');
	require_once('includes/footer.php');
	ob_flush();
?>