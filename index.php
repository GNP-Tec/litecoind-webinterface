<?php
require_once 'jsonRPCClient.php';
require_once 'config.php';
include "phpqrcode/qrlib.php";  

session_start();
if(isset($_GET['logout'])) {
	session_destroy();
}

$litecoind = new jsonRPCClient("http://".$rpc_user.":".$rpc_pass."@".$rpc_host.":".$rpc_port."/");
$info=$litecoind->getinfo();
if(isset($_GET['acc'])) {
	if($_GET['acc']!="Select an Account") {
		$_SESSION['acc']=$_GET['acc'];
	} else {
		unset($_SESSION['acc']);
	}
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<!-- =============================== -->
<!-- Litecoind webinterface          -->
<!-- Copyright by Georg Zachl 2013   -->
<!-- http://www.gnp-tec.net          -->
<!-- =============================== -->

<!-- Designer:                                                  -->
<!-- ==========================================================	-->
<!--	Created by Devit Schizoper                          	-->
<!--	Created HomePages http://LoadFoo.starzonewebhost.com   	-->
<!--	Created Day 01.12.2006                              	-->
<!-- ========================================================== -->

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
   <meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<meta name="author" content="gnp-tec.net" />
	<meta name="description" content="Site description" />
	<meta name="keywords" content="litecoin,coin,crypto,currency" />
	<title>Litecoind-Webinterface</title>
	<link rel="stylesheet" type="text/css" href="css/style.css" media="screen" />
	<link rel="shortcut icon" href="favicon.ico" />
	<script type="text/javascript" src="js/textsizer.js"></script>
	<script type="text/javascript" src="js/rel.js"></script>
	<script type="text/javascript" src="js/jquery.js"></script>
	<!-- <script type="text/javascript" src="js/jquery.tablesorter.pager.js"></script> -->
	<script type="text/javascript"> 
	jQuery(function($){ //on DOM load


	});
	</script>
</head>

<body>
<div id="wrap">
<div id="top">
<h2><a href="index.php" title="Back to main page">Litecoind-Webinterface</a></h2>
<div id="menu">
<ul>
<li><a href="index.php?cmd=list" <?php if(!isset($_GET['cmd']) || ($_GET['cmd']=="" || $_GET['cmd']=="list" || $_GET['cmd']=="trans")) { echo "class=\"current\""; } ?> >home</a></li>
<li><a href="index.php?cmd=send_new" <?php if(($_GET['cmd']=="send_new" || $_GET['cmd']=="send_sess" || $_GET['cmd']=="send_comp")) { echo "class=\"current\""; } ?> >new transactions</a></li>
<li><a href="index.php?cmd=peers" <?php if(($_GET['cmd']=="peers")) { echo "class=\"current\""; } ?> >peers</a></li>
<li><a href="index.php?cmd=about" <?php if(($_GET['cmd']=="about")) { echo "class=\"current\""; } ?> >about</a></li>
<li><a href="javascript:ts('body',1)">[+]</a></li>
<li><a href="javascript:ts('body',-1)">[-]</a></li>
</ul>
</div>
</div>
<div id="content">
</div>
<div id="left">
<h2>
<?php switch($_GET['cmd']) {
	case "send_new":
	case "send_sess":
	case "send_comp":
		echo "New Transaction";
	break;
	
	case "peers":
		echo "Litecoin Peer Listing";
	break;
	case "backup":
		echo "Wallet-Backup";
	break;
	case "priv":
		echo "Private Wallet Key";
	break;

	case "about":
		echo "About Litecoind-Webinterface";	
	break;

	case "list":
	case "":
	default:
		echo "Transaction Listing";
	break;
}
?></h2>
<p>
<?php
	switch($_GET['cmd']) {
		
		case "send_new":
			if(isset($_SESSION['acc'])) {
				echo "<form action=\"index.php\"><table border=0>\n";
				echo "<tr><td>Address: </td><td><input type=\"text\" size=30 name=\"recv\" title=\"Receiver Address\"></td></tr>\n";
				echo "<tr><td>Amount: </td><td><input type=\"text\" size=30 name=\"amou\" title=\"The amount to send\"></td></tr>\n";
				echo "<tr><td>Comment: </td><td><input type=\"text\" size=30 name=\"comm\" title=\"Add a comment for you\"></td></tr>\n";
				echo "<tr><td>Receiver Comment: </td><td><input type=\"text\" size=30 name=\"como\" title=\"Add a comment for the receiver\"></td></tr>\n";
				echo "</table>\n<input type=\"hidden\" name=\"cmd\" value=\"send_sess\">\n";
				echo "<tab to=t1><input type=\"submit\" text=\"Create\">\n";
				echo "</form>\n";
			}
		
		break;

		case "send_sess":
			if(isset($_SESSION['acc'])) {
				$_SESSION['recv']=$_GET['recv'];
				$_SESSION['amou']=$_GET['amou'];
				$_SESSION['comm']=$_GET['comm'];
				$_SESSION['como']=$_GET['como'];
				echo "The following transaction will transfer ".(double)$_SESSION['amou']." from ".$_SESSION['acc']." to ".$_SESSION['recv']."<br>\n";
				echo "Comment: ".$_SESSION['comm']."<br>\n";
				echo "Click <a href=\"index.php?cmd=send_comp\">here</a> to sign the transaction<br>\n";
			}

		break;

		case "send_comp":
			if(isset($_SESSION['recv']) && isset($_SESSION['acc']) && isset($_SESSION['amou']) && 0.0 < (double)$_SESSION['amou'] && $_SESSION['recv']!="") {
				$ret=$litecoind->sendfrom($_SESSION['acc'],$_SESSION['recv'],(double)$_SESSION['amou'],1,$_SESSION['comm'],$_SESSION['como']);
				unset($_SESSION['recv']);
				unset($_SESSION['amou']);
				unset($_SESSION['comm']);
				unset($_SESSION['como']);
				if($ret!="") {
					echo "Transaction completed!<br>\n";
					echo "Transaction ID ".$ret."<br>\n";
				} else {
					echo "There is something faulty...<br>\n";
				}
			} else {
				echo "An Error occured!<br>\n";
			}
		break;

		case "trans":
			if(isset($_GET['txid']) && $_GET['txid']!="") {
				echo "Transaction ".$_GET['txid']."<br>\n";
				$trans_det=$litecoind->gettransaction($_GET['txid']);
				echo "<p>Amount: ".$trans_det['amount'].", Confirmations: ".$trans_det['confirmations'].", Blockhash: ".$trans_det['blockhash'].", Blockindex: ".$trans_det['blockindex'].", Time:".date("Y.m.d H:i:s",$trans_det['time']).", Blocktime: ".date("Y.m.d H:i:s",$trans_det['blocktime']).", Time-received: ".date("Y.m.d H:i:s",$trans_det['timereceived'])."</p>\n";

				echo "<table border=1>\n";
				echo "<tr><th>account</th><th>address</th><th>category</th><th>amount</th></tr>\n";
				foreach($trans_det['details'] as $va) {
					echo "<tr><td>".$va['account']."</td><td>".$va['address']."</td><td>".$va['category']."</td><td>".$va['amount']."</td></tr>\n";
				}
				echo "</table>";
			} else {
				echo "No Transaction selected!<br>\n";
			}			
		break;
	
		case "about":
			echo "<center><h2>Litecoind-Webinterface by GNP-Tec.net</h2>\n";
			echo "<p>This litecoind interface is open-source software released under the GNU GPLv2. It is created by me in my free time, there is absolutley no commercial idea behind. You can use it for free, change it for free, share it for free, but keep the principals of open source and crypto currencies in mind!<br>If you like my software, feel free to support me with LTC or BTC and visit <a href=http://www.gnp-tec.net/>GNP-Tec.net</a></p><br><p>LTC: LiEByBpASdVte3pzyMChVTdMyJSK1nZ4PS<br>BTC: 1Phcr5Bkqcr6tVBcxL3SQ7i7sQimSXqn3e</p></center>";

		break;

		case "peers":
			$peers=$litecoind->getpeerinfo();
			$i=0;
			foreach($peers as $va) {
				echo "<div id=\"tbl\">\n";
				echo "<a onclick=\"javascript:$('div[name=tbl_hid".$i."]').toggle();\">Address: ".$va['addr'].", Connected since: ".date("Y.m.d H:i:s",$va['conntime']).", Requested Blocks: ".$va['blocksrequested'].", Connection ".((bool)$va['inbound']?"inbound":"outbound")."</a>";
				echo "<div id=\"tbl_hid\" name=\"tbl_hid".$i."\">Version: ".$va['version'].", Sub-Version: ".$va['subver'].", Services: ".$va['services'].", Read: ".getHumanReadableSize((int)$va['bytesrecv']).", Last Read: ".date("Y.m.d H:i:s",$va['lastrecv']).", Wrote: ".getHumanReadableSize((int)$va['bytessent']).", Last Write: ".date("Y.m.d H:i:s",$va['lastsend']).", Starting heigth: ".$va['startingheight'].", Ban-score ".$va['banscore']."</div>";
				echo "</div>\n";
				$i++;
			}
		break;

		case "backup":
			$ret=$litecoind->backupwallet(__dir__."/backup.dat");
			print_r($ret);
			echo "<a href=./backup.dat>Download</a>\n";
		break;
	
		case "priv":
			$priv=$litecoind->dumpprivkey($litecoind->getaccountaddress($_SESSION['acc']));
			QRcode::png($priv, 'priv.png');
			echo "<img src=\"./priv.png\"/><br>\n";
			echo "Your private key for ".$_SESSION['acc'].": <br>";
			echo $priv."<br>";
		break;

		case "list":
		default:
			if(isset($_SESSION['acc'])) {
				echo "<table id=\"listing\">\n";
				echo "<thead><tr><th></th><th>Date</th><th>category</th><th>amount</th><th>confirmations</th><th>Address</th>";
				if(isset($_GET['txid_show'])) {
					echo "<th>Transaction ID</th>";
				}
				echo "</tr></thead>\n<tbody>\n";
				$trans=$litecoind->listtransactions($_SESSION['acc'],$transactions_count,$_GET['page']*$transactions_count);
			
				foreach ($trans as $va) {
					echo "\t<tr><td><a href=\"index.php?cmd=trans&txid=".$va['txid']."\">Show</a></td><td>".date("Y.m.d H:i:s",$va['timereceived'])."</td><td>".$va['category']."</td><td>".$va['amount']."</td><td>".$va['confirmations']."</td><td>".$va['address']."</td>";
					if(isset($_GET['txid_show'])) {
						echo "<td>".$va['txid']."</td>";
					}
					echo "</tr></a>\n";
				}

				echo "</tbody></table>\n";
		}
}

?>
</p>
</div>
<div id="right">
	<div id="acc_selector" class="box">
		<h2 style="margin-top:17px">Working Account</h2>
		<ul>
		<?php
		$accounts=$litecoind->listaccounts();
		echo "<form action=\"index.php\" type=\"GET\">\n";
		echo "<select name=\"acc\" onchange='this.form.submit()'>\n";
		echo "<option value=\"Select an Account\">Select an Account</option>\n";
		foreach ($accounts as $in => $va) {
			echo "\t<option value=\"$in\"";
			if(isset($_SESSION['acc']) && $_SESSION['acc']==$in) {
				echo " selected";
			}
			echo ">$in</option>\n";
		}
		echo "</select>\n</form>\n<br>\n";
		if(isset($_SESSION['acc'])){
			echo $litecoind->getaccountaddress($_SESSION['acc'])."<br>\n";
			echo "Balance: ".$accounts[$_SESSION['acc']]."LTC<br>";
		}
		?>
		</ul>
	</div>
	<div style="margin-top:20px" class="box">
		<h2 style="margin-top:17px">Other features</h2>
		<ul>
			<li><a href="./index.php?cmd=backup">Wallet-Backup</a></li>
			<li><a href="./index.php?cmd=priv">Private Key</a></li>
		</ul>
	</div>
	
</div>
<div id="clear"></div></div>
<div id="footer">
<?php
echo "system information: Version ".$info['version'].", Protocol ".$info['protocolversion'].", Wallet-Version ".$info['walletversion'].", ".$info['connections']." Connections<br>";
echo "general litecoin information: ".$info['blocks']." Blocks, Difficulty is ".$info['difficulty']."<br><br>";
?>
<p>Programmed by <a href="http://www.gnp-tec.net" rel="external">Georg Z</a>. Designed by LoadFoO. Valid <a href="http://jigsaw.w3.org/css-validator/check/referer" rel="external">CSS</a> &amp; <a href="http://validator.w3.org/check?uri=referer" rel="external">XHTML</a></p>
</div>
</div>

</body>
</html>
