<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style>
    #headercntr {
        display: none;
    }

    .footer {
        display: none;
    }
</style>
<div style="margin: 168px 0px 0px 343px; font-size:28px;">Please wait... we are redirecting you to payment gateway.</div>


<form id="formsub2" action="http://live.gotapnow.com/webpay.aspx" method="post">
    <input type="hidden" name="CstFName" id="CstFName" value="<?php echo $CstFName; ?>"/>
    <input type="hidden" name="CstMobile" id="CstMobile" value="<?php echo $CstMobile; ?>"/>
    <input type="hidden" name="CstEmail" id="CstEmail" value="<?php echo $CstEmail; ?>"/>
    <input type="hidden" name="ItemQty1" value="<?php echo $ItemQty1; ?>"/> <!-- Always 1 -->
    <input type="hidden" name="ItemName1" value="<?php echo $ItemName1; ?>"/> <!--  Description about the Payment-->
    <input type="hidden" name="ItemPrice1" value="<?php echo $ItemPrice1; ?>"/><!--  Total Payment Amount-->
    <input type="hidden" name="CurrencyCode" value="<?php echo $CurrencyCode; ?>"/> <!--  Currency Code-->
    <input type="hidden" name="OrdID" value="<?php echo $OrdID; ?>"/> <!--  Order Number or Reference Number-->
    <input type="hidden" name="MEID" value="<?php echo $MEID; ?>"/> <!--  Merchant ID, Provided by Tap-->
    <input type="hidden" name="UName" value="<?php echo $UName; ?>"/> <!--  User Name, Provided by Tap-->
    <input type="hidden" name="hash" value="<?php echo $hash; ?>"/>
    <input type="hidden" name="ReturnURL" value="<?php echo $ReturnURL; ?>"/>
    <input type="hidden" name="FailURL" value="<?php echo $FailURL; ?>"/>
    <input type="hidden" name="PostURL" value="<?php echo $PostURL; ?>"/>
</form>

<script>
    document.getElementById('formsub2').submit();
</script>