<?php
	$lib=strtolower($_SESSION['library'].$_SESSION['itc'].$_SESSION['instance']);
	echo "<input type='hidden' name='openai' id='openai' value='".$lib."'>";
	// dlfnnoacsctest = show did you mean
	if (isset($_POST)) {
		if (isset($_POST['searchfieldselect'])) {
			$_SESSION['searchfieldselect']=$_POST['searchfieldselect'];
		}
		if (isset($_POST['q'])) { $_SESSION['q']=$_POST['q']; }
		if (isset($_POST['page'])) { $_SESSION['page']=$_POST['page'];}
		if (isset($_POST['scope'])) { $_SESSION['scope']=$_POST['scope'];}
		if (isset($_POST['sort'])) { $_SESSION['sort']=$_POST['sort'];}
		if (isset($_POST['selectnewlib'])) {
			if (isset($_SESSION['searchlibrary'])) {
				if ($_POST['selectnewlib'] > "" && $_POST['selectnewlib'] != $_SESSION['searchlibrary']) {
					$_SESSION['searchlibrary'] = "dist";
				}
			}
		}
		if (isset($_POST['scope'])) {
			if ($_POST['scope'] > "") {
				$_SESSION['page']=1;
			}
		}
		if (isset($_SESSION['scope'])) {
			if ($_SESSION['scope'] <= "") {
				$_SESSION['scope']="GENERAL";
			}
		} else {
			$_SESSION['scope']="GENERAL";
		}
		if (isset($_POST['hits'])) { $_SESSION['hits']=$_POST['hits']; }
		$_SESSION['view']="search";
		if (isset($_POST['page'])) { $_SESSION['pages']=$_POST['pages']; } else { $_SESSION['pages']=0; }
		if (isset($_POST['itemgroup'])) {
			$_SESSION['itemgroup']=$_POST['itemgroup'];
		} else {
			$_SESSION['itemgroup']="";
		}
		if (isset($_POST['rememberpage'])) {
			if ($_POST['rememberpage'] > 0) {
				$_SESSION['page']=$_POST['rememberpage'];
			}
		}
	}
?>
    <div class='cat-maincontainer' id='cat-maincontainer'>
		<div class='cat-body'>
			<?php
				if (isset($_POST['action']) && isset($_POST['q'])) {
					if ($_POST['action'] == "newvisualsearch" && $_POST['q'] <= "") {
						getvisualsearch(); 
					}
				}
			?>
			<div id="cat-search">
				<form id="searchform" method="POST" action="<?php $PHP_SELF; ?>">
				<input type='hidden' name='view' id='searchview' value='search'>
				<input type='hidden' name='rlflag' id='rlflag' value='<?php echo $_POST['rlflag']; ?>'>
				<input type='hidden' name='vs' id='vs' value='<?php echo $_SESSION['vs']; ?>'>
				<input type='hidden' name='submitted' id='submitted' value='Y'>
				<input type='hidden' name='scope' id='scope' value='<?php echo $_SESSION['scope']; ?>'>
				<input type='hidden' name='hits' id='hits' value='<?php echo $_SESSION['hits']; ?>'>
				<input type='hidden' name='library' id='library' value='<?php echo $_SESSION['library']; ?>'>
				<input type='hidden' name='searchlibrary' id='searchlibrary' value='<?php echo $_SESSION['searchlibrary']; ?>'>
				<input type='hidden' name='page' id='page' value='<?php echo $_SESSION['page']; ?>'>
				<input type='hidden' name='bool' id='bool' value='<?php echo $_POST['bool']; ?>'>
				<input type='hidden' name='ct' id='ct' value='10'>
				<input type='hidden' name='pages' id='pages' value='<?php echo $_SESSION['pages']; ?>'>
				<div class='container'>
					<div class="ccat-search-box animated boundce infinite" id="mainsearchbox">
						<div class="ccat-search-container" style="max-width:600px;margin:auto">
							<div class=" mb-3">
							<div class='input-group' style='overflow:hidden;border-radius:5px;-moz-border-radius:5px;box-shadow: 2px 2px 10px #000'>
								<div class='input-group-prepend' style='overflow:hidden'>
									<button type="button" id="toggle_advanced" class="btn btn-lg btn-secondary red-tooltip align-middle" data-toggle="tooltip" data-placement="left" title='Advanced Options' style='overflow:hidden'><i class='fas fa-cog'></i></button>
								</div>
								<input type="text" style="border:0px;border-radius:0px" id="q" value="<?php echo htmlentities($_POST['q'], ENT_QUOTES); ?>" name="q" placeholder="Type your search here" class="form-control form-control-lg">
								
								<div class='input-group-append' style='overflow:hidden'>
									<button id='button_search' class='btn btn-lg btn-success searchformbutton'><i class='fas fa-search'></i></button>
								</div>
								
							</div>
							<table id='advsearchoptions' class='advsearchoption' style='display:none;'>
							
							<tr><td>
									<select class="form-control form-control-lg btn-secondary" name="itemgroup" id="itemgroup">
										<option value="">Group: Any</option>
										<?php
											if ($_SESSION['groupset'] == "all") {
												foreach ($_SESSION['itemgroups'] as $code => $gd) {
													$id=$gd['code'];
													$dsp=$gd['description'];
													if ($code == $_SESSION['itemgroup']) {
														$selected="selected";
													} else {
														$selected="";
													}
													echo '<option value="'.$code.'" '.$selected.'>Group: '.$dsp.'</option>';
												}
											} else {
												foreach ($_SESSION['fetchgroups'] as $code => $gname) {
													if ($_SESSION['itemgroup'] == $code) { 
														$selected="selected";
													} else {
														$selected="";
													}
													echo "<option value='".$code."' ".$selected." >Group: ".$gname."</option>";
												}
/*
												foreach ($_SESSION['groups'] as $grp) {
													if ($grp == $_SESSION['itemgroup']) {
														$selected="selected";
													} else {
														$selected="";
													}
													echo '<option value="'.$grp.'" '.$selected.'>Group: '.$grp.'</option>';
												}
*/
											}
										?>
									</select>
							</td></tr>
							<tr><td>
									<select class="form-control form-control-lg btn-secondary" name="sort" id="sort">
									<option value=''>Sort: Not Sorted</option>
									<?php
										foreach ($_SESSION['sorts'] as $key => $sort) {
											if ($key == $_SESSION['sort']) {
												$sel="selected";
											} else {
												$sel="";
											}
											echo "<option ".$sel." value='".$key."'>Sort: ".$sort."</option>";
										}
									?>
									</select>
							</td></tr>
							<tr><td>
									<select class="form-control form-control-lg btn-secondary" name="searchfieldselect" id="searchfieldselect">
									<option value='general'>Search:  All Fields</option>
									<?php
										$fields=array("author"=>"Author","title"=>"Title","subject"=>"Subject","series"=>"Series");
										foreach ($fields as $kk => $field) {
											if ($kk == $_SESSION['searchfieldselect']) {
												$selected="selected";
											} else {
												$selected="";
											}
											echo "<option ".$selected." value='".$kk."'>Search: ".$field."</option>";
										}
									?>
									</select>
							</td></tr></table>
						</div>
					</div>
				</div>
			</div>
			</form>
				<?php
					if ($viewitemid > "") {
						echo "<input type='hidden' name='viewitemid' id='viewitemid' value='".$viewitemid."'>";
					}
					if ($_SESSION['library'] == "SABE" && $_COOKIE['fetchdebug'] == "Y") {
						echo "<div style='width:100%;text-align:center;color:#FFF;background-color:#008000;padding:5px'>SLIDER DEBUG</div>";
						include("bookriver.php");
					}
				?>	
				<div id="cat-results">
				<div id='warning' style='padding:10px;border-radius:10px;display:none' class='mb-3'><i class='fas fa-exclamation-triangle'></i>&nbsp;<span id='warningtext'></span></div>
				<div class="col text-center searchsummary" id='searchsummary' style="display:none"></div>
				<?php	if ($_SESSION['lazyload'] != "Y") { ?>
					<div id='nav_top' class='mb-3'></div>
					<div id='results' class='container-fluid'></div>
					<div id='nav_bottom' class='text-center mb-3'></div>
				<?php	} else { ?>		
					<div id="results" class="container-fluid"></div>
					<div class='mb-2 mt-2' style='text-align:center'>
						<a id='loadmorebutton' class='btn btn-success' style='display:none'>Load More</a>
						<div id='allresultsloaded'><a class='btn btn-sm btn-danger'>All Results Loaded: Go Back to the Top</a></div>
				<?php 	} ?>

				<div id="loader" style="color: #FFF;padding:30px; display:none">
				<div class="spinner-border text-primary" role="status">
					<span class="sr-only">Loading...</span>
					<br><br>
				</div>
				</div>
			</div>
			</div>
		</div>
	</div>