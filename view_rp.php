<?php
	// Initialize
	$lx1=0;
	$lx2=0;
	$arl1=0;
	$arl2=0;
	$arp1=0;
	$arp2=0;
	$rcl1=0;
	$rcl2=0;
	$rcp1=0;
	$rcp2=0;
	$post_rl1=0;
	$post_rl2=12;
	$post_rp1=0;
	$post_rp2=99.9;
	$post_lx1=0;
	$post_lx2=1800;
	if (!isset($_SESSION['rlrecs'])) {
		$_SESSION['rlrecs']=0;
	}
	if ($_SESSION['rlrecs'] <= 0) {
		
	}
	if (!isset($_SESSION['rptype'])) {
		$_SESSION['rptype']="AR";
	}
	if (isset($_POST['q'])) { 
		$_SESSION['q']=$_POST['q'];
	}
	if (isset($_COOKIE['fetchconfig'])) {
		$config = $_COOKIE['fetchconfig'];
	}
	if (isset($_POST['fetchconfig'])) {	
		if ($_POST['fetchconfig']) {
			$_COOKIE['fetchconfig']=$_POST['fetchconfig'];
			$config = $_POST['fetchconfig'];
		} else {
			if (isset($_COOKIE['fetchconfig'])) {
				$config = $_COOKIE['fetchconfig'];
			} 
		}
	}

	if (isset($_POST['rl1'])) { $post_rl1=$_POST['rl1']; }
	if (isset($_POST['rl2'])) { $post_rl2=$_POST['rl2']; }
	if (isset($_POST['rp1'])) { $post_rp1=$_POST['rp1']; }
	if (isset($_POST['rp2'])) { $post_rp2=$_POST['rp2']; }
	$configopts = explode("|",$config ?? "");
	$config_image = $configopts[0];
	
	if (isset($_POST['lx1'])) { $lx1=$_POST['lx1']; } 
	if ($lx1 <= "") { $lx1=0; }
	if (isset($_POST['ls2'])) { $lx2=$_POST['lx2']; }
	if ($lx2 <= "") { $lx2=1800; }

	if (isset($_SESSION['rptype'])) {
		if ($_SESSION['rptype'] <= "") { 
			$_SESSION['rptype']="AR";
		}
	}
	if (isset($_POST['rptype'])) {
		if ($_POST['rptype'] > "") {
			$_SESSION['rptype']=$_POST['rptype'];
		}
	}
	if (isset($_SESSION['rptype'])) {
		if (strtoupper($_SESSION['rptype']) == "LX") {
			$lxclass=" display:block ";
			$rpclass=" display:none ";
		} else {
			$rpclass=" display:block ";
			$lxclass=" display:none ";
		}
	}
	if (isset($_POST['lx1'])) { if ($_POST['lx1'] <=0) { $_POST['lx1']=0; } }
	if (isset($_POST['lx1'])) { if ($_POST['lx1'] > 1800) { $_POST['lx1']=1800; } }
	if (isset($_POST['lx2'])) { if ($_POST['lx2'] <=0) { $_POST['lx2']=0;} }
	if (isset($_POST['lx2'])) { if ($_POST['lx2'] > 1800) { $_POST['lx2']=1800; } }
	if (isset($_POST['lx2']) && isset($_POST['lx1'])) {
		if ($_POST['lx2'] < $_POST['lx1']) {
			$hold = $_POST['lx2'];
			$_POST['lx2'] = $_POST['lx1'];
			$_POST['lx1'] = $hold;
		}
	}
	if (isset($_POST['rl1'])) { 
		if ($_POST['rl1'] <= "" || $_POST['rl1'] > 12) {
			$_POST['rl1'] = 0;
		}
	}
	if (isset($_POST['rl2'])) {
		if ($_POST['rl2'] <= "" || $_POST['rl2'] > 12) {
			$_POST['rl2'] = 12;
		}
	}
	if (isset($_POST['rp1'])) { 
		if ($_POST['rp1'] <= "" || $_POST['rp1'] > 99.9) {
			$_POST['rp1'] = 0;
		}
	}
	if (isset($_POST['rp2'])) {
		if ($_POST['rp2'] <= "" || $_POST['rp2'] > 99.9) {
			$_POST['rp2'] = 99.9;
		}	
	}
	if (isset($_POST['rp1']) && isset($_POST['rp2'])) {
		if ($_POST['rp1'] > $_POST['rp2']) {
			$tmp = $_POST['rp1'];
			$_POST['rp1'] = $_POST['rp2'];
			$_POST['rp2'] = $tmp;
		}
	}
	if (isset($_POST['arlevel1'])) { $arl1=$_POST['arlevel1']; } if ($arl1 <= "") { $arl1=0; }
	if (isset($_POST['arlevel2'])) { $arl2=$_POST['arlevel2']; } if ($arl2 <= "") { $arl2=12; }
	if (isset($_POST['arpoints1'])) { $arp1=$_POST['arpoints1']; } if ($arp1 <= "") { $arp1=0; }
	if (isset($_POST['arpoints2'])) { $arp2=$_POST['arpoints2']; } if ($arp2 <= "") { $arp2=99.9; }
	
	if (isset($_POST['rclevel1'])) { $rcl1=$_POST['rclevel1']; } if ($rcl1 <= "") { $rcl1=0; }
	if (isset($_POST['rclevel2'])) { $rcl2=$_POST['rclevel2']; } if ($rcl2 <= "") { $rcl2=12; }
	if (isset($_POST['rcpoints1'])) { $rcp1=$_POST['rcpoints1']; } if ($rcp1 <= "") { $rcp1=0; }
	if (isset($_POST['rcpoints2'])) { $rcp2=$_POST['rcpoints2']; } if ($rcp2 <= "") { $rcp2=99.9; }
?>
   <div class='cat-maincontainer' id='cat-maincontainer'>
		<div class='cat-body'>
			<div id="cat-results">
				<div id='warning' style='background-color: rgba(0,0,0,.5);padding:10px;border-radius:10px;display:none' class='mb-3'><i class='fas fa-exclamation-triangle'></i>&nbsp;<span id='warningtext'></span></div>
				<div class="col text-center searchsummary" id='searchsummary' style="display:none"></div>
                                <?php   if ($_SESSION['lazyload'] != "Y") { ?>
                                        <div id='nav_top' class='mb-3'></div>
                                        <div id='results' class='container-fluid'></div>
                                        <div id='nav_bottom' class='text-center mb-3'></div>
                                <?php   } else { ?>
                                        <div id="results" class="container-fluid"></div>
                                        <div class='mb-2 mt-2' style='text-align:center'>
                                                <a id='loadmorebutton' class='btn btn-success' style='display:none'>Load More</a>
                                                <div id='allresultsloaded'><a class='btn btn-sm btn-danger'>All Results Loaded: Go Back to the Top</a></div>
                                <?php   } ?>
				<div id="loader" style="color: #FFF;padding:30px; display:none">
				<div class="spinner-border text-primary" role="status">
					<span class="sr-only">Loading...</span>
					<br><br>
				</div>
				</div>
			</div>
			
	<form id='searchform'  action="" method="POST">

				<input type='hidden' name='view' id='searchview' value='rp'>
				<input type='hidden' name='rlflag' id='rlflag' value='<?php echo $_POST['rlflag']; ?>'>
				<input type='hidden' name='vs' id='vs' value='<?php echo $_SESSION['vs']; ?>'>
				<input type='hidden' name='submitted' id='submitted' value='Y'>
				<input type='hidden' name='scope' id='scope' value='<?php echo $_SESSION['scope']; ?>'>
				<input type='hidden' name='hits' id='hits' value='<?php echo $_SESSION['hits']; ?>'>
				<input type='hidden' name='library' id='library' value='<?php echo $_SESSION['library']; ?>'>
				<input type='hidden' name='searchlibrary' id='searchlibrary' value='<?php echo $_SESSION['searchlibrary']; ?>'>
				<input type='hidden' name='page' id='page' value='<?php echo $_SESSION['page']; ?>'>
				<input type='hidden' name='posted' id='posted' value='Y'>
				<input type='hidden' name='bool' id='bool' value='<?php echo $_POST['bool']; ?>'>
				<input type='hidden' name='ct' id='ct' value='10'>
				<input type='hidden' name='pages' id='pages' value='<?php echo $_SESSION['pages']; ?>'>
				
		<div style="max-width: 600px; margin:auto;padding-bottom:20px;">
			<div class='text-center mt-4 mb-4' style='padding-left:20px;padding-right:20px'>
				<select id='rptype' class='form-control form-control-lg w-auto mb-4' style='margin:auto' name='rptype'>
					<?php
						$rtypes=array("LX"=>"Lexile Search","AR"=>"Accelerated Reader","RC"=>"Reading Counts");
						foreach($rtypes as $kk => $vv) {
							if ($kk == $_SESSION['rptype']) {
								$selected="selected";
								
							} else {
								
								$selected="";
							}
							echo "<option ".$selected." value='".$kk."'>".$vv."</option>";
						}
					?>
				</select>
				<input type='hidden' id='rpselected' name='rpselected' value="<?php echo $_SESSION['rptype']; ?>">
				<div style='padding:20px'>
				<?php if (1) { ?>
					<div style="padding-left:20px;padding-right:20px">
						<input type="text" class="form-control form-control-lg mb-4" placeholder="Search Terms" name="q" id="q" value="<?php echo $_POST['q']; ?>">
					</div>
					
					<div class='text-center' style='padding-left:20px;padding-right:20px; <?php echo $lxclass; ?>' id='rp_lx'>
						<div class='row'>
						<div class='col-3'></div>
							
							<div class='col-6'>
								<div class="row" style='padding:10px;box-shadow: 2px 2px 10px #000; background-color: rgba(0,0,0,9); color: #FFF; border-radius:5px;border:1px solid <?php echo $_SESSION['color1']; ?>'>
									<div class="col-12 mb-2 mt-2 align-self-center" style='color: #FFF'>
										<b>Lexile Score:</b>
									</div>
									<div class="col-12 mb-2 align-self-center">
										<input type="text" id="lx1" name="lx1" class="form-control form-control-lg" style='width:80px;text-align:center;display:inline-block;' value="<?php echo $_POST['lx1']; ?>" placeholder="0">
										<div class='d-block d-md-none'>to</div>
										<span class='d-none d-md-inline-block'>&nbsp;to&nbsp;</span>
										<input type="text" id="lx2" name="lx2" class="form-control form-control-lg" style='width:80px;text-align:center;display:inline-block;' value="<?php echo $_POST['lx2']; ?>" placeholder="1800">
									</div>
								</div>
							</div>
						<div class='col-3'></div>
						</div>
					</div>
					<div class='text-center' style='<?php echo $rpclass; ?>' id='rp_ar'>
						<div class='row'>
							<div class='col-6'>
								<div style='padding:5px'>
								<div class="row" style='padding:10px;box-shadow: 2px 2px 10px #000; background-color: rgba(0,0,0,9); color: #FFF; border-radius:5px;border:1px solid <?php echo $_SESSION['color1']; ?>'>
									<div class="col-12 mb-2 mt-2 align-self-center">
										<b>Reading Level:</b>
									</div>
									<div class="col-12 mb-2 align-self-center">
										<input type="text" id="rl1" name="rl1" class="form-control form-control-lg" style='width:80px;text-align:center;display:inline-block;' value="<?php echo $post_rl1; ?>" placeholder="0">
										<div class='d-block d-md-none'>to</div>
										<span class='d-none d-md-inline-block'>&nbsp;to&nbsp;</span>
										<input type="text" id="rl2" name="rl2" class="form-control form-control-lg" style='width:80px;text-align:center;display:inline-block;' value="<?php echo $post_rl2; ?>" placeholder="0">
									</div>
								</div>
								</div>
							</div>
							<div class='col-6'>
								<div style="padding:5px">
								<div class="row" style='padding:10px;box-shadow: 2px 2px 10px #000; background-color: rgba(0,0,0,9); color: #FFF; border-radius:5px;border:1px solid <?php echo $_SESSION['color1']; ?>'>
									<div class="col-12 mb-2 mt-2 align-self-center">
										<b>Reading Points:</b>
									</div>
									<div class="col-12 align-self-center mb-2">
										<input type="text" id="rp1" name="rp1" class="form-control form-control-lg" style='width:80px;text-align:center;display:inline-block;' value="<?php echo $post_rp1; ?>" placeholder="0">
										<div class='d-block d-md-none'>to</div>
										<span class='d-none d-md-inline-block'>&nbsp;to&nbsp;</span>
										<input type="text" id="rp2" name="rp2" class="form-control form-control-lg" style='width:80px;text-align:center;display:inline-block;' value="<?php echo $post_rp2; ?>" placeholder="0">
									</div>
								</div>
								</div>
							</div>
						</div>
					</div>
				<?php 
					} else { 
						echo "<div class='text-left'><h5 style='color: #aa0000'>Reading Level Search Update In Progress:</h3><p>Reading level search updates are in progress.  Reading level searching will be available again soon.</p></div>";
					}
				?>
				</div>
			</div>
			<div class='text-center mt-2'><button class='btn btn-danger btn-lg searchformbutton'>Search</button></div>
			<div class='text-center mt-4' style='font-size: .8em; color: rgba(255,255,255,.5); text-shadow: 1px 1px 2px #000;'>Searching <?php echo $_SESSION['rlrecs']; ?> Reading Level Records Total</div>
		</div>
		
</form>
	</div>
</div>
