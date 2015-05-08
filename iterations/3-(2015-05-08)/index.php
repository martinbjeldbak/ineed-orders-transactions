<?php

include 'models/Order.php';

$order = new iNeed\Order("stripe", "554856821c604e280078b62d", 20.0, 0.15, "554c2c91edb4672b00556203", 0.8);

$order->commit();
?>

<!DOCTYPE html>
<html>
	<head>
		<title>Shopping Cart</title>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
		<link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>
		<link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css"/>
		<link rel="stylesheet" type="text/css" href="assets/css/custom.css"/>	
		<script type="text/javascript">
			function displayTransaction(quantity, itemName, vendor, price) {
				var orderCart = document.getElementById(""); //TODO
				var transaction = document.createElement("li");
				transaction.className = "cart-row row";
				var quantityEntry = document.createElement("span");
				quantityEntry.className = "quantity";
				var itemNameEntry = document.createElement("span");
				itemNameEntry.className = "itemName";
				var vendorEntry = document.createElement("span");
				vendorEntry.className = "vendor";
				var priceEntry = document.createElement("span");
				priceEntry.className = "price";
				// popup Edit/delete
				var popBtn = document.createElement("span");
				popBtn.className = "popbtn";
				var arrow = document.createElement("a");
				arrow.className = "arrow";
				popBtn.appendChild(arrow);
				
				quantityEntry.innerHTML = quantity;
				itemNameEntry.innerHTML = itemName;
				vendorEntry.innerHTML = vendor;
				priceEntry.innerHTML = price;
				transaction.appendChild(quantityEntry);
				transaction.appendChild(itemNameEntry);
				transaction.appendChild(vendorEntry);
				transaction.appendChild(popBtn);
				transaction.appendChild(priceEntry);
				//orderCart needs to append transaction
			}
		</script>	
	</head>

	<body>
    <pre>
    </pre>
		
		<nav class="navbar">
			<div class="container">
				<a class="navbar-brand" href="#">iNeed Team 5 (Orders & Transactions)</a>
				<div class="navbar-right">
					<div class="container minicart"></div>
				</div>
			</div>
		</nav>
		
		<div class="container-fluid breadcrumbBox text-center">
			<ol class="breadcrumb">
				<li><a href="#">Review</a></li>
				<li class="active"><a href="#">Order</a></li>
				<li><a href="#">Payment</a></li>
			</ol>
		</div>
		
		<div class="container text-center">

			<div class="col-md-5 col-sm-12">
				<div class="bigcart"></div>
				<h1>Your Product List</h1>
				<p>
                    <!--
                        <li><a href="#" class="itemName">Double Double</a></li>
                        <li><a href="#" class="itemName">Hamburger</a></li>
                        <li><a href="#">Product 2</a></li>
                        <li><a href="#">Product 3</a></li>
                        <li><a href="#">Product 4</a></li>
    This is a free and <b><a href="http://tutorialzine.com/2014/04/responsive-shopping-cart-layout-twitter-bootstrap-3/" title="Read the article!">responsive shopping cart layout, made by Tutorialzine</a></b>. It looks nice on both desktop and mobile browsers. Try it by resizing your window (or opening it on your smartphone and pc).
    -->				</p>
			</div>
			
			<div class="col-md-7 col-sm-12 text-left">
				<ul>
					<li class="cart-row row list-inline columnCaptions">
						<span>QTY</span>
						<span>ITEM</span>
						<span>VENDOR</span>
						<span>Price</span>
					</li>
					<li class="cart-row row">
						<span class="quantity">1</span>
						<span class="itemName">Double Double</span>
						<span class="vendor">In-N-Out</span>
						<span class="popbtn"><a class="arrow"></a></span>
						<span class="price">$3.45</span>
					</li>
					<li class="cart-row row">
						<span class="quantity">2</span>
						<span class="itemName">HamBurger</span>
						<span class="vendor">In-N-Out</span>
						<span class="popbtn"><a class="arrow"></a></span>
						<span class="price">$4.55</span>
					</li>
					<li class="cart-row row totals">
						<span class="itemName">Total:</span>
						<span class="price">$8.00</span>
						<span class="price"><form><input id="coupon" type="text" value="Enter Coupon Code" onfocus="value=''" style="display:none; font-size:12px" /></form></span>
						<span class="order"> <a class="text-center" href="#" onclick="document.getElementById('coupon').style.display='block'">Add Deal</a></span>
						<span class="order"> <a class="text-center" href="payment.html">ORDER</a></span>
					</li>
				</ul>
			</div>

		</div>

		<!-- The popover content -->

		<div id="popover" style="display: none">
			<a href="#"><span class="glyphicon glyphicon-pencil"></span></a>
			<a href="#"><span class="glyphicon glyphicon-remove"></span></a>
		</div>
		
		<!-- JavaScript includes -->

		<script src="http://code.jquery.com/jquery-1.11.0.min.js"></script> 
		<script src="assets/js/bootstrap.min.js"></script>
		<script src="assets/js/customjs.js"></script>

	</body>
</html>
