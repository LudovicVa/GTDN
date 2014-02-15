<?php
include_once("config.php");
?>
<style type="text/css">
<!--
.procut_item {background: #FFFFCC;border-bottom: 1px solid #FFCC99;width: 500px;margin-bottom: 30px;}
.procut_item h4 {margin: 0px;padding: 0px;}
.product_wrapper {width: 510px;margin-right: auto;margin-left: auto;padding: 20px;background: #FFFFCC;border: 1px solid #FFCC99;}
-->
</style>

<h2 align="center">Test Products</h2>
<div class="product_wrapper">
<table class="procut_item" border="0" cellpadding="4">
  <tr>
    <td width="50%"><h4>Product 1</h4> (Product Description)</td>
    <td width="50%"><form method="post" action="process.php">
		<input type="hidden" name="itemname" value="Product One" /> 
		<input type="hidden" name="itemnumber" value="1" /> 
		<input type="hidden" name="itemprice" value="10" />
        Quantity : <select name="itemQty"><option value="1">1</option><option value="2">2</option><option value="3">3</option></select> 
        <input class="dw_button" type="submit" name="submitbutt" value="Buy (10 <?php echo $PayPalCurrencyCode; ?>)" />
    </form></td>
  </tr>
</table>

<table class="procut_item" border="0" cellpadding="4">
  <tr>
    <td width="50%"><h4>Product 2</h4> (Product Description)</td>
    <td width="50%"><form method="post" action="process.php">
		<input type="hidden" name="itemname" value="Product Two" /> 
		<input type="hidden" name="itemnumber" value="2" /> 
		<input type="hidden" name="itemprice" value="20" /> Quantity : <select name="itemQty"><option value="1">1</option><option value="2">2</option><option value="3">3</option></select> 
        <input class="dw_button" type="submit" name="submitbutt" value="Buy (20 <?php echo $PayPalCurrencyCode; ?>)" />
    </form></td>
  </tr>
</table>
</div>
</body>
</html>