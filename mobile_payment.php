<?php
//start session
session_start();

//get unique reference ID from checkout page using URL
$ref = $_GET['ref'] ?? 'Unknown';

//process payment when form submitted
if (isset($_POST['pay'])) {
    
    //check for paid references in array , if no then create it
    if (!isset($_SESSION['paid_refs'])) { $_SESSION['paid_refs'] = []; }
    
    //store reference in array and mark as paid
    $_SESSION['paid_refs'][] = $ref;
    $done = true;
}
?>
<body style="text-align:center; font-family:sans-serif; padding:50px; background:#f4f4f4;">
    <div style="background:white; padding:30px; border-radius:10px; display:inline-block; box-shadow:0 2px 10px rgba(0,0,0,0.1);">
        
<!--show payment success after pay now form is submitted-->
    <?php if(isset($done)): ?>
            <h2 style="color:green">Payment Success!</h2>
            <p>The checkout page will redirect automatically.</p>
    <!--close pop up window-->
            <button onclick="window.close()" style="padding:10px; background:#555; color:white; border:none; border-radius:5px; cursor:pointer;">Close This Window</button>
        <?php else: ?>
        <!--payment form-->
            <h2>TNG E-Wallet Pay</h2>
            <p>Ref ID: <?= $ref ?></p>
        <!--trigger the pay now logic-->
            <form method="POST"><button type="submit" name="pay" style="padding:15px 30px; background:#2563eb; color:white; border:none; border-radius:5px; cursor:pointer; font-size:1.1em;">Pay Now</button></form>
        <?php endif; ?>
    </div>
</body>