<?
	header("content-type: css/text");
	session_start();
?>
.errorbox {
	width: 800px;
	max-width: 80%;
	padding: 20px;
	margin-top: 50px;
	border: 1px solid #FFF;
	box-shadow: 1px 1px 5px #000;
	background-color: #404040;
	color: #FFF;
	margin-left:auto;
	margin-right:auto;
}
.errortitle {
	text-align:center;
	font-weight: bold;
	font-size: 2em;
	line-height: 1.2em;
	padding-bottom: 15px;
	text-shadow: 1px 1px 3px #000;
}
#debugbox {
	width: 80%;
	margin:auto;
	border: 1px solid #FF0000;
	background-color: #400000;
	color: #FFF !important;
	padding: 15px;
	display:none;
}
#debugbox pre {
	color: #FFF;
}
.errordescription {
	text-align: left;
	font-size: 1.2em;
}
.xbookriveritem {
	width: 143px;
	cursor: pointer;
	transition: all .25s ease-in-out;
	box-shadow: 1px 1px 10px #000;
}
.link856 {
	font-weight: 900;
	transition: all .25s ease-in-out;
}
.findmore {
	transition: all .25s ease-in-out;
}
.findmore:hover, .link856:hover {
	background-color: <? echo $_SESSION['color1']; ?>;
	color: #fff;
	cursor:pointer;
}
.bookriveritem {
	cursor: pointer;
	transition: all .25s ease-in-out;
	box-shadow: 1px 1px 10px #000;
	max-width:200px;
	width:auto;
	height:auto;
}
.bookriveritem:hover {
	box-shadow: <? echo $_SESSION['color1']; ?>;
	cursor:pointer;
	transform: scale(1.1);
	box-shadow: 2px 2px 15px #000;
}
.iconcelltitle {
    text-align: center;
    color: #FFF;
    background-color: rgba(0,0,0,0);
    overflow: hidden;
    font-size: 1em;
    font-weight: 900;
    padding: 0px 10px 10px 10px;
}
.iconcell:hover {
	background-color: rgba(0,0,0,.9);
	box-shadow: 0px 0px 30px #fff;
	cursor:pointer;
	border: 1px solid #fff;
}

.iconcell {
	border-radius: 10px;
	-moz-border-radius: 10px;
	overflow:hidden;
	border: 1px solid rgba(255,255,255,.25);
	box-shadow: 0px 0px 10px rgba(255,255,255,0);
	background-color: rgba(0,0,0,.75);
	text-align:center;
	display: inline-block;
	margin: 10px;
	display: block;
	transition: .25s all ease-in-out;
}
.iconcellpadding {
	padding:10px 10px 0px 10px;
}
.iconcell img {
	filter: brightness(100%);
	height:auto;
	width:auto;
	border-radius: 5px;
	-moz-border-radius: 5px;
	transition: all .25s ease-in-out;
	position: relative;
	height: 128px;
	width: 128px;
	padding:10px;
}
.hitrow {
	box-shadow: 5px 5px 20px #000;
	border-radius: 10px;
	-moz-border-radius: 10px;
	background-color: rgba(255,255,255,.9);
	overflow:hidden;
	padding: 15px;
	transition: all .25s ease-in-out;
	border: 3px solid rgba(255,255,255,0);
}
.searchformcontent {
	padding-left: 10px;
	padding-right: 10px;
	padding-top: 20px;
}
.cat-maincontainer {
	width: 100%;
	max-width: 1150px;
	margin:auto;
	text-align:center;
	padding-left: 10px;
	padding-right:10px;
	padding-bottom: 100px;
}
@media only screen and (max-width: 770px) {
	.cat-maincontainer {
		width: 100%;
		max-width: 1150px;
		margin:auto;
		text-align:center;
		padding-left: 0px;
		padding-right:0px;
		padding-bottom: 100px;
	}
	.searchformcontent {
		padding-left: 0px;
		padding-right: 0px;
		padding-top: 10px;
	}
	.cat-mainmenu {
		margin: auto;
		color: #FFF;
		background-color: #000 !important;
		box-shadow: 5px 5px 20px rgba(0,0,0,.8);
		border-radius: 0px !important;
		max-width:1000px !important;
		width:100% !important;
		-moz-border-radius: 0px !important;
	
	}
	.hitrow {
		border:0px;
		background-color: rgba(0,0,0,0);
		border-radius:0px;
		box-shadow:none;
	}
  .iconcell {
		border-radius: 5px;
		-moz-border-radius: 5px;
		overflow:hidden;
		border: 1px solid rgba(255,255,255,0);
		text-align:center;
		display: inline-block;
		margin: 2px;
		display: block;
		transition: .25s all ease-in-out;
		background-color: rgba(0,0,0,0);
	}
	.iconcellpadding {
		padding:5px 5px 0px 5px;
	}
	.iconcell img {
		filter: brightness(100%);
		height:100px;
		width:100px;
		border-radius: 5px;
		-moz-border-radius: 5px;
		transition: all .25s ease-in-out;
		position: relative;
	}
}
.iconcell:hover img {
	filter: brightness(150%);
}
.btn-primary {
	background-color: <? echo $_SESSION['color2']; ?>;
}
#gohome {
	display:block;
	position: absolute;
	right: 20px;
	bottom: 5px;
	border-radius:10px;
	-moz-border-radius:10px;
	color: #aaa;
	z-index:234234234;
	display:none;
	transition: all .25s ease-in-out;
}
#gohome:hover {
	color: #FFF;
	cursor:pointer;
	text-shadow: 0px 0px 15px <? echo $_SESSION['color1']; ?>;
}
html {
	background-size: cover;
	height: 100%;
	overflow:hidden;
	
}
<?
	if ($_SESSION['fetchbgseries'] == "safari") {
		$bgimg = "/images/series/".$_SESSION['fetchbgseries']."/".strtolower($_SESSION['fetchbgseries']."_".sprintf("%02d",date("d"))).".jpg";
	} elseif ($_SESSION['fetchbgseries'] == "time") {
		$bgimg = "/images/series/time/afternoon.jpg";
		if (date("h") < 6 || date("h") >= 23) {
			$bgimg = "/images/series/time/night.jpg";
		} elseif (date("h") < 12) {
			$bgimg = "/images/series/time/morning.jpg";
		} elseif (date("h") < 17) {
			$bgimg = "/images/series/time/afternoon.jpg";
		} 
	} elseif (strpos($_SESSION['fetchbg'],".mp4")) {
		$bgimg="";
	} elseif ($_SESSION['fetchbg'] == "nobg.png") {
		$bgimg = "";
	} elseif ($_SESSION['fetchbg'] == "calendar") {
		$bgimg = "/images/background/light.png";
	} elseif (strlen($_SESSION['fetchbg']) > 3) {
		$bgimg = "/images/background/".$_SESSION['fetchbg'];
	} else {
		$_SESSION['fetchbg']="dark.png";
		$bgimg = "/images/background/".$_SESSION['fetchbg'];
	}
?>
body {
	height:100%;
	overflow:scroll;
	-webkit-overflow-scrolling:touch;
	<? if ($_SESSION['acc'] == "Y") { ?>
		background-color: #000000;
	<? } else { ?>
		background-color: <? echo $_SESSION['color1']; ?>;
	<? } ?>
	<? if ($bgimg > "") { ?>
		background-image: url("<? echo $bgimg; ?>");
		background-size: cover;
		background-position: center center;
		background-attachment: fixed;
	<? } ?>
}
#tryisearchbox {
	font-size:.7em;
	color: #FF0000;
}
.iconcell2title {
	text-align:center;
	color:#fff;
	background-color: <? echo $_SESSION['color1']; ?>;
	overflow: hidden;
	font-size:1.2em;
	font-weight: 900;
	padding: 10px;
	border-color: #FFF;
	border-width: 1px 0px 0px 0px;
	border-style:solid;
}
.catalogheader {
	background-color: <? echo $_SESSION['color1']; ?>;
	background-repeat: repeat;
	padding: 10px;
	font-size: 1.25em;
	color: #FFF;
	text-transform: uppercase;
	padding:15px;
	margin: 0px;
	line-height: 1.5em;
	border-style: solid;
	border-color: rgba(255,255,255,.25);
	border-width: 0px 0px 1px 0px;
	box-shadow: 0px 5px 20px rgba(0,0,0,.8);
}
.cat-mainmenu2 {
	width:100%;
	color: #FFF;
	margin-auto;
	overflow:hidden;
	background-color: #000;
	box-shadow: 0px 5px 10px rgba(0,0,0,1);
	border-style: solid;
	border-color: <? echo $_SESSION['color1']; ?>;
	border-width: 0px 0px 1px 0px;
}
.cat-mainmenu {
	margin: auto;
	color: #FFF;
	margin-auto;
	overflow:hidden;
		background-size:cover;
	background-color: rgba(0,0,0,.9);
	box-shadow: 5px 5px 20px rgba(0,0,0,.8);
	border-radius: 0px 0px 10px 10px;
	max-width: 540px;
	-moz-border-radius:  0px 0px 10px 10px;
	border-color: <? echo $_SESSION['color1']; ?>;
	border-top-width:0px;
	border-bottom-width:1px;
	border-right-width:0px;
	border-left-width:0px;
	border-style:solid;	
}
.cat-mainmenu .btn {
	box-shadow: 2px 2px 15px rgba(0,0,0,.5);
}
.cat-mainmenu-small {
	width: 100%;
	color: #FFF;
	background-color: <? echo $_SESSION['color1']; ?>;
	box-shadow: 5px 5px 20px rgba(0,0,0,.8);
	border-style: solid;
	border-color: rgba(255,255,255,.5);
	border-width: 0px 0px 1px 0px;
	border-radius: 0px;
}
body::-webkit-scrollbar {
  -webkit-appearance: none;
  width: 15px;
}
body::-webkit-scrollbar-track {
	background-color: <? echo $_SESSION['color1']; ?>;
}
body::-webkit-scrollbar-thumb {
  border-radius: 10px;
  width: 12px;
  background-color: rgba(255,255,255,.5);
  box-shadow: 1px 1px 3px #000;
}
body {
	scrollbar-color: rgba(255,255,255,.75) <? echo $_SESSION['color1']; ?> ;
	scrollbar-width: 15px;
	overflow-x:hidden;
}
.table-striped tbody tr:nth-of-type(even) {
	background-color: rgba(0,0,0,.02);
}
.moreholdings {
	border: 1px solid #00000020;
	border-radius: 3px;
	-moz-border-radius: 3px;
	background-color: <? echo $_SESSION['color1']; ?>;
	color: #FFF !important;
	font-size: .7em;
	transition: all .25s ease-in-out;
	padding: 10px;
	margin-bottom: 15px;
}
.moreholdings i {
	font-size: 1.5em;
	color: #FFF;
	text-shadow: 1px 1px 3px #000;
	margin-right: 5px;
}
.moreholdings a {
	color: #FFF !important;
	text-decoration: none;
}
.moreholdings:hover {
	cursor:pointer;
	background-color: #404040;
}
