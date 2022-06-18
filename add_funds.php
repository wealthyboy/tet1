<?php ob_start();

   
    require_once $_SERVER["DOCUMENT_ROOT"].'/init/init.php';
    require_once('functions/login.php');

    if ( !isLoggedIn() ){
        header("Location: 404");
        exit;
    }
      
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Autofactorng || Add funds Up</title>
	<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="css/style.css">
	<script type="text/javascript" src="js/jquery.js"></script>
</head>


<body>
<?php
  $display_form = true;
  $request = \Illuminate\Http\Request::capture();
  require $_SERVER["DOCUMENT_ROOT"].'/modules/phpmailer/PHPMailerAutoload.php';
  
    $user = '';
    $user = json_decode($_COOKIE['user']);
    $user = User::find($user->id);

if ($request->isMethod('post')) { 
    
    $wallet = Wallet::where('user_id', $user->id)->first();
    $mail = new PHPMailer;
	$mail->isSMTP();
	$mail->Host = 'smtp.zoho.com';
	$mail->Port = 465;
	$mail->SMTPAuth = true;
	$mail->Username = 'wallet@autofactorng.com';
	$mail->Password = 'wallet_0808';
	$mail->SMTPSecure = 'ssl';
	$mail->From = 'wallet@autofactorng.com';
	$mail->FromName = 'Autofactorng Team';
	$mail->addAddress('info@autofactorng.com', 'Autofactor');
	$mail->addAddress('jacob.atam@gmail.com', 'Autofactor');

	$mail->WordWrap = 50;
	$mail->isHTML(true);

	$mail->Subject = 'Wallet Funded';

	$bodyc = "<h1 style= 'text-align: center ; color: #D43A16;'> Wallet Fund</h1>";
    $bodyc .= "<p style= ''>Name: $user->first_name . </p>";
    $bodyc .= "<p >Amount:  $request->amount</p>";
    $bodyc .= "<p style= ''>Email: $user->email </p>";


    $bodyc.= "";
	$mail->Body = "$bodyc";

	$mail->send();
	
    if (null !== $wallet ){
        $amount  = $request->amount +  $wallet->amount;
        $wallet->amount  = $amount > 0 ? $amount : 0;
        $wallet->save();
        echo 1;
        
    } else {
        $wallet = new Wallet;
        $wallet->amount =  $request->amount;
        $wallet->user_id =  $user->id;
        $wallet->save();
        echo 1;
    }
}
?>
<div id="signup_wrapp">
<div id="signup_header">
<a href="https://autofactorng.com" alt="LINK: HOME" title="<< Go back to homepage"><img src="/images/afng_logo.png"></a>
</div>
<h3 style="color: #777;">Wallet</h3>
<p>Please enter amount below</p>
<hr />
<h4 id="reg_response"><?= $reg_response = ''; ?></h4>
<?php
	if ($display_form) { ?>
		<form method="POST" id="funds-form" action="">
			 <input type="hidden" name="token" value="<?php echo  $_SESSION['token'] ?>" />
			  <input type="hidden" name="secret" value="6LfNv0cUAAAAAPsOGLWHxAuzbPSZTVcvp1t7u6Se" />
	
	
		<p class="amount">
			<label for="pword1">Amount</label><br />
			<input type="text" name="amount" id="amount" placeholder="Enter Amount" required="required" />
		</p>
		
		<p class="hide payment-round-up">
			Please wait while we round up your payment. Don't leave this page!!
		</p>
		
	
      <br/>
         
		<p>
			<input type="submit" id="fund-button" name="signup" value="Submit" />
		</p>
	</form>
<?php } ?>
</div>
<script src="https://js.paystack.co/v1/inline.js"></script>


<script type="text/javascript">
    $('#funds-form').on('submit',function(e) {
        e.preventDefault();
        var amount = $('#amount').val();
            $("#fund-button").val("Please Wait");
            $('.amount').addClass('hide')
            $('.payment-round-up').removeClass('hide')
            

        var handler = PaystackPop.setup({
            key: 'pk_live_f781064afdc5336a6210015e9ff17014d28a4f8b',
            amount: amount * 100, /* amount in kobo */
            email: '<?php  echo  $user->email ?>',
            ref:  Math.random(),// returns a random number ,
            callback: function(response) {
            	$.post('/add_funds.php', 'amount='+amount, function(resp) {
            		window.location.href = "/index";
            	});
            },
            onClose: function(){
             alert('We could not funds to  your wallet. Sorry');
             $("#fund-button").val("Submit");
             $('.amount').removeClass('hide')
             $('.payment-round-up').addClass('hide')

            }
          });
            handler.openIframe();
            return;
        })
     
     
</script>

</body>
</html>