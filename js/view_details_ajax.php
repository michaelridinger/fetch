<?
	session_start();
	$isearchresultsurl="";
	$isearchresulturl="";
	$homepage="";
	$token="";
	$catkey="";
	$isearchurl="";
	$libsearchsource="";
	$fetchcatkey="";
	$cnt=array();
	$library="";
	$itc="";
	$instance="";
	$badcount=0;
	$order="";
			
	if ($_SESSION['wsurl'] <= "") {
		echo "<div style='text-align:center'>";
		echo "<p>Your session has timed out.</p>";
		echo "<p>Redirecting to your library home page ...</p>";
		$homepage = $_COOKIE['libraryhome'];
		if ($homepage <= "") {
			$homepage = "https://www.infohio.org/opac";
		}
		header("location: ".$homepage);
	} 
	//echo "<pre>"; print_r($_SESSION); echo "</pre>";
	// https://otst9beta.ent.sirsi.net/client/en_US/acce_aust_afes/search/detailnonmodal/?d=ent://ACCE_AUST_ILS/0/ACCE_AUST_ILS:79549~ACCE_AUST_ILS~0
	$token = $_SESSION['token'];
	if ($token <= "") {
		$token = getToken();
		$_SESSION['token']=$token;
		$_SESSION['authheaders'] = 	array(
			"Accept: application/json",
			"Content-type: application/json",
			"sd-originating-app-id: catalogsearch",
			"x-sirs-sessionToken: ".$token,
			"x-sirs-clientID: DS_CLIENT"
		);
	}
	$catkey=$_GET['catalogkey'];
	if ($catkey <= "" && $fetchcatkey > "") {
		$catkey=$fetchcatkey;
	}
	if ($_SESSION['searchsource'] > "" && $_SESSION['isearchurl'] > "") {
		$libsearchsource=$_SESSION['searchsource'];
		if ($libsearchsource == "WOCO_NB_ILS") { $libsearchsource = "WOCO_NB_ILS_1"; }
		$holdsurl = str_replace("results/","detailnonmodal/?qu=".urlencode($_SESSION['q'])."&d=ent://".$_SESSION['searchsource']."/0/".$libsearchsource.":".$catkey."~".$_SESSION['searchsource']."~0&infohiohold=Y&ic=true",$_SESSION['isearchurl']);
		$isearchlogout = str_replace("results/","index.template.header.mainmenu_0.logout?ic=true",$_SESSION['isearchurl']);
	} else {
		$holdsurl="";
	}
	echo "<input type='hidden' id='holdsurl".$catkey."' name='holdsurl".$catkey."' value='".$holdsurl."'>";
	echo "<input type='hidden' id='isearchlogout' name='isearchlogout' value='".$isearchlogout."'>";
	$isbn=$_GET['isbn'];

	$library=$_SESSION['library'];
	$itc=$_SESSION['itc'];
	$instance=$_SESSION['instance'];
	$id=$_GET['id'];
	
	// fields to retrieve
	$fields = urlencode("*,callList{itemList{call,itemType{displayName,description},itemCategory5,library,currentLocation},dispCallNumber,callNumber}");
	$searchurl = $_SESSION['wsurl']."/catalog/bib/key/".$catkey."?includeFields=".$fields;
	$chsearch = curl_init($searchurl);
	curl_setopt($chsearch, CURLOPT_POST, 0);
	curl_setopt($chsearch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($chsearch, CURLOPT_SSL_VERIFYPEER, 0); // On dev server only!
	curl_setopt($chsearch, CURLOPT_HTTPHEADER, $_SESSION['headers']);
	$result=curl_exec($chsearch);
	$r = json_decode($result,true);

	//echo "<pre>"; print_r($r); echo "</pre>";

	// Parse MARC data - create mrc array to hold tag data
	$marc = $r['fields']['bib']['fields'];
	unset($mrc);
	$tcnt=0;
	foreach ($marc as $kk => $rec) {
		$tag=$rec['tag'];
		if ($cnt[$tag]<1) { $cnt[$tag]=0; }
		$tcnt=$cnt[$tag];
		foreach ($rec['subfields'] as $sk => $sv) {
			$sub=$sv['code'];
			$val=$sv['data'];
			$mrc[$tag][$tcnt][$sub]=$val;
			$cnt[$tag]++;
		}
	}
	$isbn = preg_replace("/[^0-9]/","",$mrc['020'][0]['a']);
	if (strlen(trim($isbn)) < 13) {
		$isbn = getisbn($isbn);
	}
	$imgisbn=$isbn;
	// Use marc data (mrc array) to populate common values
	$linklist="";
	$lcomma="";
	$linkid=0;
	foreach ($mrc[856] as $link) {
		$url = $link['u'];
		$text = $link['y'];
		$note = $link['z'];
		if ($note > "") {
			if ($text > "") {
				$text.=" - ".$note;
			} else {
				$text = $note;
			}
		}
		if ($url > "" && $text > "") {
			$linkid++;
			$iconlist.="<a href='".$url."' target='_blank' data-toggle='tooltip' title=\"".$text."\" data-placehemt='top' class='btn btn-sm' style='color: #FFF; background-color: ".$_SESSION['color1']."'><i class='fas fa-link'></i></a>";
			$linklist.="<li class='list-group-item link856' id='link856".$linkid."'>";
			$linklist.="<input type='hidden' id='link856url".$linkid."' value='".$url."'>";
			$linklist.=$text."</li>";
			$lcomma=", ";
		}
	}
	$audience="";
	foreach ($mrc[521] as $kk => $vv) {
		if (strpos(strtolower($vv['b']),"exile")) {
			$audience.=$comma.$vv['a']." ".$vv['b']; $comma=", ";
		} else {
			if ($vv['b'] > "") {
				if ($kk == 0) {
					$audience.=$comma."Reading Grade Level - ".$vv['a']." - Source ".$vv['b']; $comma=", ";
				} elseif ($kk == 1) {
					$audience.=$comma."Interest Age Level - ".$vv['a']." - Source ".$vv['b']; $comma=", ";
				}
			}
		}
	}
	if (isset($mrc[595])) {
		foreach ($mrc[595] as $kk => $vv) {
			if (strpos($vv['a'],"cceler") || strpos($vv['a'],"ounts")) {
				$lvl = $vv['l']*1/10;
				$pts = ($vv['p']*1-10000)/10;
				if (strpos($vv['a'],"cceler")) { 
					$tit="Accelerated Reader";
				} else {
					$tit="Reading Counts";
				}
				//$audience.="<div class='hitrow_audience'><b>".$tit.":</b> ".$lvl.", <b>Points:</b> ".sprintf("%4.1f",$pts)."</div>"; 
				$audience.=$comma.$tit.": ".$lvl.", Points: ".sprintf("%4.1f",$pts);  $comma="&nbsp;<span style='font-weight:900'>&#124;</span>&nbsp;";
			} elseif (strpos($vv['b'],"exile") || strpos($vv['b'],"ountas")) {
				$vv['b']=str_replace(".","",$vv['b']);
				//$audience.="<div class='hitrow_audience'><b>".$vv['b'].":</b> ".$vv['a']."</div>";
				$audience.=$comma.$vv['b'].": ".$vv['a']; $comma="&nbsp;<span style='font-weight:900'>&#124;</span>&nbsp;";
			}
		}	
	}
	
	$auth2=str_replace(",","",$mrc['100'][0]['a']);
	$pubinfo = $mrc['260'][0]['a']." ".$mrc['260'][0]['b']." ".$mrc['260'][0]['c'];
	$pubyear = preg_replace("/[^0-9,\s]/","",$marc['260'][0]['c']);
	if ($pubyear <= "") {
		$pubyear = preg_replace("/[^0-9,\s]/","",$mrc['264'][0]['c']);
	}
	if ($pubinfo <= "") {
		$pubinfo = $mrc['264'][0]['a']." ".$mrc['264'][0]['b']." ".$mrc['264'][0]['c'];
	}
	$summary = $mrc['520'][0]['a'];
	if (strlen($summary) > 255) { 
		$summary_brief = substr($summary,0,252)."...";
	} else {
		$summary_brief=$summary;
	}
	$pdesc = $mrc[300][0]['a']." ".$mrc[300][0]['b'].$mrc[300][0]['c'];
        $isbnlist = $mrc['020'];
        $isbnq="";
        $isbnc="";
        foreach ($isbnlist as $ndx => $isbn) {
                $isbnq.=$isbnc.$isbn['a'];
                $isbnc=",";
        }
	$title = $r['fields']['title'];
	$author = $r['fields']['author'];
	$burl="https://booknook.infohio.org/opac/isearch.php?isbn=".$isbnq."&opac=fetch&title=".urlencode($title);
	$booknook = file_get_contents($burl);
	$booknook = explode(",",$booknook);
	if (count($booknook) > 1) {
		$bn_count = $booknook[0];
		$bn_link = $booknook[1];
	}
	$callnumber = $r['fields']['callList'][0]['fields']['callNumber'];
	$dcallnumber = $r['fields']['callList'][0]['fields']['dispCallNumber'];
	if (strlen($dcallnumber) > strlen($callnumber)) {
		$callnumber=$dcallnumber;
	}
	
	// Get Subject List
	
	unset($subjectlist);
	$subjectlist=array();
	foreach ($mrc['650'] as $num => $v) {
		$term=$v['a'];
		if ($v['v'] > "") {
			$subjectdesc=$v['v'];
		} else {
			$subjectdesc=$v['a'];
		}
		
		$subjectlist[]=str_replace(".","",$term."|".$subjectdesc);
	}
	foreach ($mrc['690'] as $num => $v) {
		$term=$v['a'];
		if ($v['v'] > "") {
			$subjectdesc=$v['v'];
		} else {
			$subjectdesc=$v['a'];
		}
		
		$subjectlist[]=str_replace(".","",$term."|".$subjectdesc);
	}
	sort($subjectlist);
	$subjectlists= "<div class='details_pubinfo mb-2' style='line-height: 1.2em'>";
	$subjectlists.= "<form id='subjectform' action='/".strtolower($itc."/".$instance."/".$library)."' method='POST'>";
	$subjectlists.=  "<input type='hidden' name='scope' value='SUBJECT'>";
	$subjectlists.=  "<input type='hidden' name='q' id='subjectq' value=''>";
	$subjectlists.=  "</form>";
	$subjectlists.=  "<b>Subject(s):&nbsp;</b>";
	$comma="";
	foreach ($subjectlist as $subj) {
		$items=explode("|",$subj);
		$terms = preg_replace("/[^a-zA-Z0-9 \s\b]/", "", $items[0]);
		$subjectlists.=  $comma."<a class='addedsubject' style='cursor:pointer;text-decoration:underline' title=\"".$terms."\">".$items[0]." ".$items[1]."</a>"; $comma="<span style='font-size:30px;line-height:10px;'>,</span>&nbsp;&nbsp;";
	}
	$subjectlists.=  "</div>";
	
	// create holdings table
	
	$havail=0;
	$htot=0;
	$holdings="";
	$holdlist="";
	$holdlistx="";
	$hiddencount=0;
	$mytot=0;
	$myavail=0;
	unset($notelist);
	$notelist=array();
	$shownotes=0;
	$fcallnum="";
	$curcallnum="";
	foreach ($r['fields']['callList'] as $ndx => $info) {
		$callnumber = $info['fields']['callNumber'];
		if ($fcallnum <= "") {
			$fcallnum=$callnumber;
		}
		$dcallnumber = $info['fields']['dispCallNumber'];
		if (strlen($dcallnumber) >= strlen($callnumber)) {
			$callnumber=$dcallnumber;
		}
		foreach ($info['fields']['itemList'] as $ndx => $iteminfo) {
			$key = $iteminfo['key'];
			// Get Notes
			$cnoteurl = $_SESSION['wsurl']."/catalog/item/key/".$iteminfo['key']."?includeFields=".urlencode("publicNoteList{*}");
			$cnote = curl_init($cnoteurl);
			curl_setopt($cnote, CURLOPT_POST, 0);
			curl_setopt($cnote, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($cnote, CURLOPT_SSL_VERIFYPEER, 0); // On dev server only!
			curl_setopt($cnote, CURLOPT_HTTPHEADER, $_SESSION['authheaders']);
			$result=json_decode(curl_exec($cnote),true);
			$notes=$result['fields']['publicNoteList'];
                if ($_COOKIE['fetchdebug'] == "Y") {
			echo "<span style='color:#FF0000'>".$cnoteurl."</span>";
			echo "<pre>";
			print_r($notes);
			echo "</pre>";
                }

			//print_r($notes);
			$itemnote = "";
			$itemnotecount=0;
	//		$itemnote = $notes[0]['fields']['data'];
			foreach ($notes as $kk => $vv) {
				$note = $vv['fields']['data'];
				if (!in_array($note,$notelist)) {
					$itemnote.="<div style='padding:3px'><b>Note:&nbsp;</b>".$note."</div>";
					$itemnotecount++;
				}
			}
			// End Get Notes
			$callkey = $iteminfo['fields']['call']['key'];
			$type = $iteminfo['fields']['itemType']['key'];
			$typename = $iteminfo['fields']['itemType']['fields']['description'];
			$lib = $iteminfo['fields']['library']['key'];
			$stat = $iteminfo['fields']['currentLocation']['key'];
			$libname = "[".$lib."] ".$_SESSION['libnames'][$lib];
			if ($lib == $_SESSION['library'] && in_array($lib,$_SESSION['libarr'])) {
				$mytot++;
				if ($stat == "AVAILABLE") {
					if ($curcallnum <= "") {
						$curcallnum=$callnumber;
					}
					$myavail++;
					$havail++;
					$stat = "<span style='font-weight:900;color:#00AA00'>".$stat."</span>";
				} else {
				}
				$cls="inmylib";
				$hcls="inmylib2";
				$htot++;
			} else {
				if ($stat == "AVAILABLE") {
					$havail++;
					$cls="notinmylib availnotinmylib";
					$hcls="notinmylib";
					$stat = "<span style='font-weight:900;color:#00AA00'>".$stat."</span>";
				} else {
					$hcls="notinmylib";
					$cls="notinmylib";
				}
				$hiddencount++;
				$htot++;
			}
			if ($libname <= "") {
				$libname=$lib;
			}
			if ($holdbg == "#f1f4f7") {
				$holdbg = "#FFFFFF";
			} else {
				$holdbg = "#f1f4f7";
			}
			if ($itemnotecount > 0) {
				$shownotes=1;
				$holdlist.= "<tr style='background-color: ".$holdbg.";' class='".$cls." noterow1'><td style='font-size:.8em'>".$libname."</td><td style='font-size:.8em'>".$callnumber."</td><td style='font-size:.8em'>".$typename."</td><td style='font-size:.8em'>".$stat."</td></tr>";
				$holdlist.="<tr style='background-color: ".$holdbg.";margin-top: 0px;padding-top:0px;' class='".$hcls." noterow2'><td colspan='4' style='text-align:left'><div style='background-color: #FFFFAA;color:#000;box-shadow: 1px 1px 5px #00000050;border: 1px dotted #00000050;padding:5px'>".$itemnote."</div></td></tr>";
				$itemnote="";
			} else {
				$holdlist.= "<tr style='background-color: ".$holdbg.";' class='".$cls." '><td style='font-size:.8em'>".$libname."</td><td style='font-size:.8em'>".$callnumber."</td><td style='font-size:.8em'>".$typename."</td><td style='font-size:.8em'>".$stat."</td></tr>";
			}
		}
	}
	$moreavail = $havail - $myavail;
	$holdlistx="";
	$holdings= "<div class='d-none d-lg-block'><table class='table' style='border-radius:5px;-moz-border-radius:5px;overflow:hidden;box-shadow: 1px 1px 5px rgba(0,0,0,.2);'>";
	$holdings.= "<tr style='background-color: #404040;color:#FFF;'><td colspan='3' style='background-color: #404040;color:#FFF;text-align:left'>";
	$holdings.= "<span id='allholdingstitle' style='display:none'>".$havail." of ".$htot." COPIES AVAILABLE</span>";
	$holdings.= "<span id='myholdingstitle'>".$myavail." of ".$mytot." COPIES AVAILABLE</span>";
	$holdings.= "</td><td style='text-align:right'>&nbsp;";
	if ($moreavail > 0) {
		$holdings.="<i id='showallholdingsbutton' class='fas fa-toggle-off showallholdingsbutton' style='color: #808080' title='There are other copies available elsewhere in your school district.  Click to see them.' data-toggle='tooltip' data-original-title='More copies are available at other libraries.' red-tooltip data-placement='top' ></i>";
	}
	$holdings.="<input type='hidden' id='showallholdings' value='N'></td></tr>";
	if ($mytot > 0) {
		$holdings.= "<tr style='font-weight:900'><td>Library</td><td style='white-space:nowrap'>Call<span class='d-lg-inline-block'>&nbsp;Number</span></td><td style='white-space:nowrap'>Group</td><td style='white-space:nowrap'>Status</td>";
		$holdings.="</tr>";
	}
	$holdings.=$holdlist;	
	$holdings.= "</table>";
	if ($moreavail > 0) {
		$holdings.="<div class='showallholdingsbutton' id='sahb' style='text-align:center;padding-top:5px;padding-bottom:10px;'>";
		$holdings.="<a class='moreholdings'>";
		$holdings.="<i class='fas fa-clone'></i>&nbsp;";
		if ($moreavail == 1) {
			$holdings.= "There is one more copy ";
		} else {
			$holdings.= "There are ".$moreavail." more copies ";
		}
		$holdings.="available elsewhere in your school district.";
		$holdings.="</a></div>";
	}
	$holdings.= "</div><div class='d-block d-lg-none'><table class='table'><tr><td colspan='4' style='background-color: #404040;color:#FFF;text-align:center'>".$havail." of ".$htot." COPIES AVAILABLE</td></tr></table></div>";
?>
<div>
	<div id='detailsx' style='width:100%;'>
		<div class="row">
			<div class="col-12 col-lg-8 text-left">
				<div class='details_title mb-1'><? echo $title; ?></div>
				<div class='details_author mb-2'><? echo $author; ?></div>
				<input type='hidden' id='findauthor' value="<? echo preg_replace("/[^a-zA-Z0-9\s]/","",$mrc[100][0]['a']); ?>">
				<div class='details_holdings'>
					<?
						echo "<div class='mt-4 mb-4'>".$holdings."</div>";
						if ($bn_count > 0) {
							echo "<div class='details_pubinfo mb-2'><b>Book Nook:</b>&nbsp;<img src='https://booknook.infohio.org/images/bkicon.png' style='height:20px;line-height:20px;display:inline-block'><span style='line-height:20px'>&nbsp;<a href='".$bn_link."' target='_blank'>".$bn_count." Video Review(s)</a></span></div>";
						}
						if (strlen($pubinfo) > 2) {
							echo "<div class='details_pubinfo mb-2'><b>Pub Info:</b>&nbsp;".$pubinfo."</div>";
						}
						if (strlen($audience) > 2) {
							echo "<div class='details_pubinfo mb-2'><b>Audience:</b>&nbsp;".$audience."</div>";
						}
						echo "<div class='details_pdesc mb-2'><b>Description:</b>&nbsp;".$pdesc."</div>";
						if (strlen($summary) > 5) {
							echo "<div class='details_summary mb-2'><b>Summary:</b> ".$summary."</div>";
						}
						if ($linklist > "") {
							echo "<div class='details_summary mb-2'><b>Links:</b> ".$iconlist."</div>";
						}
						
						if (count($subjectlist) > 0) {
							echo $subjectlists;
						}
						$pl= strtolower("https://fetch.infohio.org/".$_SESSION['itc']."/".$_SESSION['instance']."/".$_SESSION['library']."/".$catkey);	
					?>
				</div>
			</div>
			<div class="col-12 col-lg-4 mb-4">
				<div class="card" style="border-radius: 7px;-moz-border-radius:7px;overflow:hidden;text-align:center;display:block;" >
					<?php
						if (isset($isbn['a']) && $imgisbn <= "") {
							$imgisbn=$isbn['a'];
						}
					?>
					<img src='./images/nocoverbg.png' class='catcoverdefault catcover2' id='image_<? echo $imgisbn; ?>'>
					<ul class="list-group list-group-flush">
						<?
							if ($linklist > "") {
								echo $linklist;
							}
						?>
						<li class="list-group-item findmore findmoreauthor" style='text-align:center' id='findmoreauthor2' data-toggle="tooltip" data-placement="top" title="Find more by <? echo $author; ?>">
							Find More by this author
						</li>
						<? //echo $holdsurl; ?>
						<? if ($holdsurl > "" && $_SESSION['fetchish'] == "Y") { ?>
							<li class="list-group-item findmore" style='text-align:center'><a class='placehold' id='placehold<? echo $catkey; ?>' style='cursor:pointer;color: #202020' >PLACE HOLD<br><span style='font-size:.7em;TEXT-ALIGN:CENTER'>USING ISEARCH</span></a></li>
						<? } ?>
						<? if ($holdsurl > "" && $_SESSION['fetchis'] == "Y") { ?>
							<li class="list-group-item findmore"  style='text-align:center'><a  onClick="myPopup('<? echo str_replace("infohiohold","",$holdsurl); ?>','ISearch',800,600)" style='text-decoration:none;cursor:pointer;color: #202020' >VIEW IN ISEARCH</a></li>
						<? } ?>
					</ul>
				</div>
			</div>
		</div>
		<?php
			$code = strtolower($library.$instance);
			if ($code == "lhslkd") {
?>
<script type="text/javascript" 
src="https://imageserver.ebscohost.com/novelistselect/ns2init.js">
</script>
<div data-novelist-novelistselect='<?php echo $isbn; ?>'></div>
<?php
			}
		?>
		<hr>
		<div class='row' style='text-align:center'>
			<div class='col-12'>
				<?php
				if (strlen($imgisbn) > 5) {
				?>
				<p><a class='btn btn-success' id="togglemarcdetails" style='color: #FFF'>View MARC Details</a>&nbsp;&nbsp;<a href="http://www.syndetics.com/index.aspx?isbn=<? echo $imgisbn; ?>/index.html&client=419-222-7417&type=rw12" target="_blank" class='btn btn-danger'>More Info</a></p>
			<?php } ?>
				<div style='text-align:center;padding:5px;font-size:.7em;'><b>Permalink:</b>&nbsp;<?php echo "<a href='".$pl."'>".$pl."</a>"; ?></div>
</div>
			<div id='marcdetails' style='display:none;'>
					<table class='table table-striped' style='width: 80%;margin:auto;;border-radius: 10px; -moz-border-radius:5px; overflow:hidden;'>
					<tr><td colspan=3 style='text-align:center;background-color: #404040;color: #FFF;'>MARC Details</td></tr>
					<tr><td><b>Tag</b></td><td><b>Subfield</b></td><td><b>Value</b></td></tr>
					<?
						foreach ($mrc as $tag => $data) {
							foreach ($data as $ndx => $field) {
								foreach ($field as $subfield => $val) {
									if ($subfield != $oldsubfield && $tag == $oldtag) {
										$xtag="--";
									} else {
										$oldtag=$tag;
										$oldsubfield=$subfield;
										$xtag="<span style='font-weight:900;color:#000'>".$tag."</span>";
									}
									echo "<tr><td>".$xtag."</td><td class='text-center'><span style='color: #06c;font-weight:600'>".$subfield."</span></td><td>".$val."</td></tr>";
								}
							}
						}
					?>
					</table>
					
			</div>
		</div>
	</div>
</div>
<?php if ($instance == "TEST") { 
$isbn = $isbn['a'];
?>


<div class="chili_review" id="isbn_<?php echo $isbn; ?>"> </div>
<div id="chili_review_<?php echo $isbn; ?>" style="display: none; clear: both;" align="center" width="100%"></div>
<input type="hidden" name="chilifresh_profile" id="chilifresh_profile" value="DLFN" />
<input type="hidden" id="chilifresh_version" name="chilifresh_version" value="popup_v1" />
<input type="hidden" id="chilifresh_type" name="chilifresh_type" value="search" />
<input type="hidden" id="chilifresh_language" name="chilifresh_language" value="English" />
<input type="hidden" id="chilifresh_account" name="chilifresh_account" value="906" />
<script type="text/javascript" src="https://chilifresh.com/pop-up/js/symphony.js"></script>
<!-- ChiliFresh code part 4 end -->

<?php } ?>
<form id="detailsform" method="POST" action="/<? echo strtolower($itc."/".$instance."/".$library); ?>">
<input type='hidden' name='submitted' id='newsubmitted' value='Y'>
<input type='hidden' name='scope' id='newscope' value='<? echo $_SESSION['scope']; ?>'>
<input type='hidden' name='hits' id='newhits' value='<? echo $_SESSION['hits']; ?>'>
<input type='hidden' name='rememberpage' id='newrememberpage' value='<? echo $_SESSION['page']; ?>'>
<input type='hidden' name='ct' id='newct' value='10'>
<input type='hidden' name='vs' id='newvs' value=''>
<input type='hidden' name='pages' id='newpages' value='<? echo $_SESSION['pages']; ?>'>
<input type="hidden" id="newq" value="<? echo $_SESSION['q']; ?>" name="q">
<input type="hidden" name="itemgroup" id="newitemgroup" value="<? echo $_SESSION['itemgroup']; ?>">
<input type="hidden" name="sort" value="<? echo $_SESSION['sort']; ?>">
<input type="hidden" name="library" value="<? echo $_SESSION['library']; ?>">
<input type='hidden' name='view' value='search'>
</form>
<script type="text/javascript">
	function myPopup(myURL, title, myWidth, myHeight) {
		if (myWidth > screen.width) {
			myWidth = screen.width-20;
		}
		if (myHeight > screen.height) {
			myHeight = screen.height-20;
		}
		var left = (screen.width - myWidth) / 2;
		var top = (screen.height - myHeight) / 4;
		var myWindow = window.open(myURL, title, 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width=' + myWidth + ', height=' + myHeight + ', top=' + top + ', left=' + left);
    }
	</script>
<?
	if ($curcallnum <= "") { $curcallnum=$fcallnum; }
	echo "|".$curcallnum;
	function getToken() {
		$json = '{"login":"webserver","password":""}';
		$searchurl = $_SESSION['wsurl']."/user/patron/login";
		$chsearch = curl_init($searchurl);
		curl_setopt($chsearch, CURLOPT_POST, 0);
		curl_setopt($chsearch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($chsearch, CURLOPT_SSL_VERIFYPEER, 0); // On dev server only!
		curl_setopt($chsearch, CURLOPT_HTTPHEADER, $_SESSION['headers']);
		curl_setopt($chsearch, CURLOPT_POSTFIELDS, $json);
		
		$result=curl_exec($chsearch);
		$r = json_decode($result,true);		
		return $r['sessionToken'];
	}
	function getisbn($isbn)
	{
	   $isbn = trim($isbn);
           if(strlen($isbn) == 12) { // if number is UPC just add zero
                  $isbn13 = '0'.$isbn;
                } else {
                  $isbn13 = "978".substr(trim($isbn),0,9);
                  $tot=0;
                  for ($x=1;$x<=12;$x=$x+2) {
                          $tot=(int)$tot+((int)substr($isbn13,$x-1,1));
                          $tot=(int)$tot+((int)substr($isbn13,$x,1)*3);
                  }
                  $x = $tot % 10;
                  if ($x > 0) { $x = 10-$x; }
                  $isbn13.=$x;
           }
	   return ($isbn13);
	}
?>
