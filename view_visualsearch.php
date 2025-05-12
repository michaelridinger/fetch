<?php
	// INITIALIZE
	$cat2="";
	$cat4="";
?>
<form id="searchform" method="POST" action="">
    <div class='cat-maincontainer' id='cat-maincontainer'>
		<div class='cat-body'>
			<div id="cat-search">
				<input type='hidden' name='vs' id='vs' value='<?php echo $_SESSION['vs']; ?>'>
				<input type='hidden' name='rlflag' id='rlflag' value='vs'>
				<input type='hidden' name='view' id='searchview' value='search'>
				<input type='hidden' name='submitted' id='submitted' value='Y'>
				<input type='hidden' name='hits' id='hits' value='<?php echo $_SESSION['hits']; ?>'>
				<input type='hidden' name='library' id='library' value='<?php echo $_SESSION['library']; ?>'>
				<input type='hidden' name='searchlibrary' id='searchlibrary' value='<?php echo $_SESSION['searchlibrary']; ?>'>
				<input type='hidden' name='page' id='page' value='<?php echo $_SESSION['page']; ?>'>
				<input type='hidden' name='ct' id='ct' value='10'>
				<input type='hidden' name='pages' id='pages' value='<?php echo $_SESSION['pages']; ?>'>
				<input type='hidden' name='bool' id='bool' value=''>
	
 				<a id='tryagain'></a>
				<div class='container'>
					<?php include("searchbox.php"); ?>
				</div>
			</div>
				<div id="cat-results">
				<div id='warning' style='padding:10px;border-radius:10px;display:none' class='mb-3'><i class='fas fa-exclamation-triangle'></i>&nbsp;<span id='warningtext'></span></div>
                                <?php   if ($_SESSION['lazyload'] != "Y") { ?>
                                        <div id='nav_top'></div>
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
				<?php
				$json=file_get_contents("opacimages.json");
				$icons=json_decode($json,true);
				$cnt=0;
				echo "<div id='visualsearch' style='text-align:center;display:block;'>";

				echo "<div class='iconcell' title=\"Home\" id='visual_search_home' class='iconcell x_info_home visual_search_home' style='display:none;background-color:rgba(55,200,12,.87)'>";
				  echo "<input type='hidden' id='vsbackcat' value='home'>";
				  echo "<div class='iconcellpadding'>";
					echo "<img src='/images/homebutton.png' style='padding:10px'>";
				  echo "</div>";
				  echo "<div class='iconcelltitle' id='iconcelltitle".$cnt."'>";
					echo "<< Back/Home";
				  echo "</div>";
				echo "</div>";
				
				foreach ($icons as $kk => $vv) {
					$cat = $kk;
					foreach ($vv as $subcat => $val) {
						$cnt++;
						if ($val['query'] > "") {
							$query=$val['query'];
							$cat2="cat_".substr($subcat,0,1);
						} else {
							$query="";
						}
						if ($cat == "home") {
							$vis="inline-block";
						} else {
							$vis="none";
						}
						echo "<div title=\"".$val['name']."\" id='".$cat."_info_".$subcat."_info_".$cnt."' class='".$cat2." iconcell cat_".$cat." subcat_".$subcat."' style='display:".$vis.";'>";
						  echo "<input type='hidden' id='query_".$cnt."' value=\"".$query."\">";
						  if (isset($val['bool'])) {
							if ($val['bool'] >  "") {
								echo "<input type='hidden' id='bool_".$cnt."' value='".$val['bool']."'>";
							  }
						}
						echo "<div class='iconcellpadding'>";
							if (isset($val['image'])) { echo "<img src='/icons/png/".$val['image'].".png'>"; }
							
						  echo "</div>";
						  echo "<div class='iconcelltitle' id='iconcelltitle".$cnt."'>";
							if (isset($val['name'])) { echo $val['name']; }
						  echo "</div>";
						echo "</div>";
					}
				}
				echo "</div>";
			?>
			</div>
			</div>
		</div>
	</div>
		<input type='hidden' name='vs' id='vs' value='<?php echo $_SESSION['vs']; ?>'>
</form>
<hr>
<hr>
<hr>
