<?
	session_start();
	include("/var/www/html/badwords.php");
	$t1 = microtime(true);
		/*
		// LOG USAGE
		$_SESSION['cur'] = sprintf("%04d%02d",date("Y"),date("m"));
		if ($_SESSION['cur'] != $_SESSION['logged']) {
			if ($_SESSION['isearchprofile'] <= "") {
				$pts = explode("/",$_SERVER['REQUEST_URI']);
				$isearchprofile=substr($pts[1],0,4)."_".$pts[2]."_".$pts[3];
			} else {
				$isearchprofile=$_SESSION['isearchprofile'];
			}
			$_SESSION['logged']=$_SESSION['cur'];
			$rlog=file_get_contents("https://fetch.infohio.org/stats/log.php?isearchprofile=".$isearchprofile."&opac=F");
		}
		*/

	$warning = "";
	
	// IS A NEW SEARCH EXECUTED?
	if ($_POST['q'] > "" || $_POST['rptype'] > "") {
		
		// Reading level search or keyword search
		// fields to be retreived with web services
		
		$query=$_POST['q'];
		$fields = urlencode("*,title,author,callList{itemList{library,currentLocation,itemType{displayName,description}},callNumber,dispCallNumber},catalogDate,bib");
		
		// Determine start page for results
		$page = $_POST['page'];
		if ($page < 1) { 
			$page=1; 
		}
		$start = ($_POST['page']-1)*10+1;

		$_SESSION['page']=$page;
		
		// SANITIZE QUERY
		// Remove stop-words, quotes, and commas

		$query=str_replace(",","",$query);
		$checkquery = strtolower(" ".$query." ");
		if (str_contains($checkquery,"not ")) {
			$query=str_replace("not "," ",$query);
		}
		if (	
			!str_contains($checkquery,'"') && (
			str_contains($checkquery," near ") || 
			str_contains($checkquery," adj ") || 
			str_contains($checkquery," not ") || 
			str_contains($checkquery," or ") || 
			str_contains($checkquery," with ") || 
			str_contains($checkquery," and ") || 
			str_contains($checkquery,","))
		) {
			$query = '"'.$query.'"';
		}
		$srch=$_POST['scope'].":".urlencode($query);

		if ($_POST['rptype'] > "") {
			$lx1 = $_POST['lx1']*1;
			$lx2 = $_POST['lx2']*1;
			$rl1 = $_POST['rl1']*10;
			$rl2 = $_POST['rl2']*10;
			$rp1 = $_POST['rp1']*10;
			$rp2 = $_POST['rp2']*10;
			
			if ($rp1 <= 0 && $rp2 <= 0) {
				$rp1=0; $rp2=99.9;
			}
			if ($rl1 <= 0 && $rl2 <= 0) {
				$rl1=0; $rl2=12;
			}
		
			if ($_POST['rptype'] == "LX") {
				$rlsearch = " lexile >= ".$lx1." and lexile <= ".$lx2." ";
				$searchdetail = "Lexile Search";
			} elseif ($_POST['rptype'] == "AR") {
				
			//	$rlsearch = " ( $rp1 <= arpoints2 and $rp2 >= arpoints ) ";
			//	$rlsearch .= " and ( $rl1 <= arlevel2 and $rl2 >= arlevel ) ";
				
				$rlsearch = " ( arpoints >= $rp1 and arpoints <= $rp2 and arlevel >= $rl1 and arlevel <= $rl2 ) ";
				$searchdetail = "Accelerated Reader Search";
			} elseif ($_POST['rptype'] == "RC") {
			//	$rlsearch = " ( $rp1 <= rcpoints2 and $rp2 >= rcpoints ) ";
			//	$rlsearch .= " and ( $rl1 <= rclevel2 and $rl2 >= rclevel ) ";
				
				$rlsearch = " ( rcpoints >= $rp1 and rcpoints <= $rp2 and rclevel >= $rl1 and rclevel <= $rl2 ) ";
			}
			$itc = $_SESSION['itc'];
			$itc2=$itc;
			if ($itc == "CONNECT") {
				$itc2="NCC";
			}
			if ($itc == "HCC") {
				$itc2="HCCA";
			}
			$rlsearch .= " and (itc like '".substr($itc,0,4)."' or itc like '".substr($itc2,0,4)."') ";
			$rlsearch .= " and instance like '".$_SESSION['instance']."' ";
			if ($_SESSION['library'] == "DIST" || $_SESSION['library'] == "dist") {
				$rlsearch .= " and library like '%%' ";
			} else {
				$rlsearch .= " and library like '%[".$_SESSION['library']."]%' ";
			}
			include("dbconnect.php");
			if ($_POST['q'] > "") {
			//	$rlsearch .= " and ( match(metadata) against(\"".mysqli_escape_string($dbwww,$_POST['q'])."\") ) ";
				$rlsearch .= " and ( title like '%".mysqli_escape_string($dbwww,$_POST['q'])."%' or author like '%".mysqli_escape_string($dbwww,$_POST['q'])."%' or subjects like '%".mysqli_escape_string($dbwww,$_POST['q'])."%' or metadata like '%".mysqli_escape_string($dbwww,$_POST['q'])."%' ) ";

			}
			
			// Determine start record number

			$page = $_POST['page'];
			if ($page < 1) { 
				$page=1; 
			}
			$start = ($page-1)*10+1;
			$lstart = $start-1;
			$last=$start+9;
			// Check to see if the table exists
			
			$tablename=strtolower(substr($_SESSION['itc'],0,4)."_".$_SESSION['instance']);
			$q=" SELECT * FROM information_schema.tables WHERE table_schema = 'opac' AND table_name = '".$tablename."' LIMIT 1";
			$tr=$dbwww->query($q);
			$cr=$tr->num_rows;
			if ($cr < 1) {
				$oldtablename=$tablename;
				$tablename="marc_empty";
			}
			if ($_SESSION['hits'] <= 0) {
				$qx="select count(*) as cnt from ".$tablename." where ".$rlsearch;
				$sr=$dbwww->query($qx);
				$fr=$sr->fetch_assoc();
				$hits=$fr['cnt'];
			} else {
				$hits=$_SESSION['hits'];
			}
			$q="select catkey from ".$tablename." where ".$rlsearch." limit $lstart,10 ";
			$sr=$dbwww->query($q);
			$rlquery = $q;
			while ($f=$sr->fetch_assoc()) {
				$searchurl=$_SESSION['wsurl']."/catalog/bib/key/".$f['catkey']."?includeFields=".$fields;
				$chsearch = curl_init($searchurl);
$xyz=$searchurl;
				curl_setopt($chsearch, CURLOPT_POST, 0);
				curl_setopt($chsearch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($chsearch, CURLOPT_SSL_VERIFYPEER, 0); // On dev server only!
				curl_setopt($chsearch, CURLOPT_SSL_VERIFYHOST, 0); // On dev server only!
				curl_setopt($chsearch, CURLOPT_HTTPHEADER, $_SESSION['headers']);
				$result=curl_exec($chsearch);
				$res[] = json_decode($result,true);				
				$cnt++;
			}
			$r['result'] = $res;
			$r['totalResults']=$hits;
			$r['startRow']=$start;
			$pages=round($hits/10+.5,0,1);
			$summary_search="Reading Level Search";
			$rlflag="r";
		} else {	
			$rlflag="s";
			if ($_POST['rlflag'] == "vs") {
				$rlflag="v";
			}
			if ($_POST['scope'] == "GENERAL" || $_POST['scope'] == "") {
				$summary_search="Search for <span style='color:#FFFF00;font-style:italic'>".$query."</span>";
			} else {
				$scope=strtolower($_POST['scope']);
				if ($scope == "general") {
					$scope="Search for ";
				} else {
					$scope=$scope." = ";
				}
				$summary_search = $scope."<span style='color:#FFFF00;font-style:italic;'>".$query."</span>";
			}
			if ($_SESSION['podlibrary'] > "") {
				$srch.=",library:".strtolower($_SESSION['podlibrary']);
			} else {
				$srch.=",library:".strtolower($_SESSION['searchlibrary']);
			}
			if ($_POST['itemgroup']>"") {
				$srch.=",itemType:".$_POST['itemgroup'];
				$summary_search.=" and <span style='font-style:italic;color:#aaaaFF'>group = ".$_POST['itemgroup']."</span>";
			}
			$_SESSION['lastquery'] = $srch; // store query in session

			if ($start == 1 && $_SESSION['sort'] > "") {
				$searchurl = $_SESSION['wsurl']."/catalog/bib/search/?q=".$srch."&rw=1&ct=1";
				$chsearch = curl_init($searchurl);
				curl_setopt($chsearch, CURLOPT_POST, 0);
				curl_setopt($chsearch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($chsearch, CURLOPT_SSL_VERIFYPEER, 0); // On dev server only!
				curl_setopt($chsearch, CURLOPT_SSL_VERIFYHOST, 0); // On dev server only!
				curl_setopt($chsearch, CURLOPT_HTTPHEADER, $_SESSION['headers']);
				$result=curl_exec($chsearch);
				$rrr=$result;
				$r = json_decode($result,true);
				$hits = $r['totalResults'];
				if ($hits <= 0) { $hits=0; }
				$_SESSION['hits']=$hits;
				$_SESSION['bucketlist']=0;
				if ($hits > 300) {
					$bucketlist=0;
					$_SESSION['searchresults']="";
					$_SESSION['bucketlist']=0;
				} else {
					$_SESSION['bucketlist']=1;
					$searchurl = $_SESSION['wsurl']."/catalog/bib/search/?q=".$srch."&includeFields=".$fields."&rw=1&ct=300";
					$chsearch = curl_init($searchurl);
					curl_setopt($chsearch, CURLOPT_POST, 0);
					curl_setopt($chsearch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($chsearch, CURLOPT_SSL_VERIFYPEER, 0); // On dev server only!
					curl_setopt($chsearch, CURLOPT_SSL_VERIFYHOST, 0); // On dev server only!
					curl_setopt($chsearch, CURLOPT_HTTPHEADER, $_SESSION['headers']);
					$result=curl_exec($chsearch);
					$r = json_decode($result,true);
					$unsorted=$r['result'];
					$sorted=$r['result'];
					// apply sort
					if ($_SESSION['sort'] == "AUTHOR") {
						uasort($sorted, function($a, $b) {
							if ($a['fields']['author'] == $b['fields']['author']) { return 0; }
							return ($a['fields']['author'] < $b['fields']['author']) ? -1 : 1;
						});
					} elseif ($_SESSION['sort'] == "TITLE") {
						uasort($sorted, function($a, $b) {
							if ($a['fields']['title'] == $b['fields']['title']) { return 0; }
							return ($a['fields']['title'] < $b['fields']['title']) ? -1 : 1;
						});
					} elseif ($_SESSION['sort'] == "CATDESC") {
						uasort($sorted, function($a, $b) {
							if ($a['fields']['catalogDate'] == $b['fields']['catalogDate']) { return 0; }
							return ($a['fields']['catalogDate'] < $b['fields']['catalogDate']) ? -1 : 1;
						});
					} elseif ($_SESSION['sort'] == "CATASC") {
						uasort($sorted, function($a, $b) {
							if ($a['fields']['catalogDate'] == $b['fields']['catalogDate']) { return 0; }
							return ($a['fields']['catalogDate'] > $b['fields']['catalogDate']) ? -1 : 1;
						});
					}
					$_SESSION['searchresults']=$r;
					$_SESSION['searchresults']['result']=$sorted;
					foreach ($sorted as $k => $v) {
						$authors.=$v['fields']['author']."<br>";
					}
					foreach ($unsorted as $k => $v) {
						$authors2.=$v['fields']['author']."<br>";
					}
				}
			} 
			if ($_SESSION['bucketlist'] == 1) {
				$hits = $_SESSION['searchresults']['totalResults'];
				$_SESSION['hits'] = $hits;
				$subset = array_slice($_SESSION['searchresults']['result'],$start-1,10);
				$r['result']=$subset;
				$r['bucketlist']="YES";
			} else {
				$searchurl = $_SESSION['wsurl']."/catalog/bib/search/?q=".$srch."&includeFields=".$fields."&rw=".$start."&ct=".$_POST['ct'];
				$chsearch = curl_init($searchurl);
				curl_setopt($chsearch, CURLOPT_POST, 0);
				curl_setopt($chsearch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($chsearch, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($chsearch, CURLOPT_SSL_VERIFYPEER, 0); // On dev server only!
				curl_setopt($chsearch, CURLOPT_HTTPHEADER, $_SESSION['headers']);
				$result=curl_exec($chsearch);
				$rrr=$result;
				$r = json_decode($result,true);
				$hits = $r['totalResults'];
				if ($hits <= 0) { $hits=0; }
				$_SESSION['hits']=$hits;
			}
			
			if ($_SESSION['sort'] > "" && $hits <= 300) {
				//$warning = "*** Sorting under construction ***";
				// re-execute query using 300 results
			} elseif ($_SESSION['sort'] > "" && $hits > 300) {
				$warning = "More than 300 titles found.  Results not sorted";
			}
		}

		$pages = round($hits/10+.5,0,1);
		$cnt=0;		
		$nextpage=$page+1;
		$prevpage=$page-1;
		if ($nextpage > $pages) { $nextpage=$pages;}
		if ($prevpage < 1) { $prevpage=1; }
		if ($pages > 1) {
			$nav = "<div class='btn-group' role='group' style='box-shadow: 3px 3px 10px rgba(0,0,0,.5)'>";
			if ($page > 1) {
			  $nav.= "<button type='button' class='btn btn-black btn-lg gopage' id='newpage_".$prevpage."'><i class='fas fa-angle-double-left'></i></button>";
			} else {
			  $nav.= "<button type='button' class='btn btn-black btn-lg' disabled><i style='color: #404040' class='fas fa-angle-double-left'></i></button>";
			}
			    $nav.= "<div class='btn-group middle-group' role='group'>";
			      $nav.= "<button type='button' class='btn btn-black btn-lg dropdown-toggle' aria-expanded='false' data-bs-toggle='dropdown'>";
			      $nav.= "Viewing Page $page";
			      $nav.= "</button>";
				  $nav.= "<div class='dropdown-menu' style='background-color: #000;color:#FFF'>";
				  for ($x=1;$x<=$pages;$x++) {
					  $nav.="<a class='dropdown-item gopage  btn-black btn-lg' id='page_".$x."' >View Page $x</a>";
				  }
			  
				  $nav.= "</div>";
				$nav.="</div>";
				if ($page >= $pages) {
					$nav.= "<button type='button' class='btn btn-black btn-lg' disabled><i class='fas fa-angle-double-right' style='color: #404040'></i></button>";
				} else {
					$nav.= "<button type='button' class='btn btn-black btn-lg gopage' id='newpage_".$nextpage."'><i class='fas fa-angle-double-right'></i></button>";
				}
			$nav.= "</div>";
			
		} else {
			$nav="";
		}

		$start = $r['startRow'];
		$last = $start+9;
		if ($last > $hits) { 
			$last=$hits; 
		}
		if ($_SESSION['q'] > "" && $_SESSION['hits'] <= 0) {
			$words=$_SESSION['q'];
			$out = spellcheck($words);	
			$badcount = $out['badcount'];
		}
		if ($_SESSION['isearchurl'] > "" && $_SESSION['fetchis'] == "Y" && $_POST['rptype'] <= "") {
			$isearchrseultsurl = $_SESSION['isearchurl']."?qu=".$_SESSION['q']."&ic=true";
			$isearchresults="<br><a id='showisearchresultsbutton' style='cursor:hand;color: #FF0000'>See Results in ISearch</a>";
		}
	
		echo $start."|".$last."|".$hits."|".$summary_search.": ".$hits." titles Fetched <span id='tryisearchbox'>".$isearchresults."</span>|".$nav."|".$pages."|".$warning."|"; 
		$t2=time()-$t1;
		$ql="insert into log (url,response) values ('".$_SESSION['wsurl']."','".$t2."')";
		if ($_COOKIE['fetchdebug'] == "Y") {
			echo "Reading Level: ".$rlquery."<br>";
			echo "Search: ".$searchurl."<br>";
		}
		if ($tablename == "marc_empty") {
			// Report to tech team reading-level issue
			$logfile = strtolower($_SESSION['itc']."_".$_SESSION['instance'].".log");
			$elog = fopen($logfile,"a");
			fputs($elog,"Table does not exist ".$oldtablename."\n");
			fclose($elog);
		}
		$curr=$start;
		$current=0;
		echo "<input type='hidden' id='isearchresultsurl' value='".$isearchrseultsurl."'>";
		if ($r['totalResults'] <= 0) {
			echo "<div style='padding:20px;text-align:center'>";
				echo "<div class='text-center mb-4' style='padding: 15px;margin:auto;max-width: 500px;box-shadow: 2px 2px 10px rgba(0,0,0,.5);border: 1px solid rgba(255,255,255,.5);background-color: rgba(0,0,0,.4); border-radius: 10px;color:#FFF;font-size:20px'>";
				if ($badcount > 0 && $hits <= 0) {
					echo "<div class='text-center'>";
					echo "<div style='line-height:1em;color: rgba(255,255,255,.7)'>Did you mean ";
					echo "<span style='color: #FFFF00;text-decoration:underline;font-style:italic'><a style='color: #FFFF00' class='didyoumean' id='didyoumean'>".$out['didyoumean']."</a></span> ?</div>";
					//echo "<div class='text-center mb-2'><span style='font-size:.8em;font-style:italic;color: rgba(255,255,255,.7)'>Or, modify your search ...</span></div>";
					//echo "<div class='newsearch'>".$out['newsearch']."</div><div class='text-center' style='padding:10px;font-size:.6em;padding:5px;color: #FFFF00'>Select the words you want to use. Then click <a id='runnewsearch' class='btn btn-sm btn-success'><i class='fas fa-search'></i></a>.</div>";		
				} elseif ($hits <= 0) {
					echo "<div class='text-center' style='padding:20px;'>No titles were found matching your request</div>";
				}
			echo "</div>";
		} else {
			foreach ($r['result'] as $k => $v) {
				$libcall=0;
				echo "<input type='hidden' id='key".$curr."' value='".$v['key']."'>";
				$kkk = $v['key'];
				$title = $v['fields']['title'];
				$author = $v['fields']['author'];
				$titlenumber = $v['fields']['titleControlNumber'];
				$callnumber = $v['fields']['callList'][0]['fields']['callNumber'];
				$dcallnumber = $v['fields']['callList'][0]['fields']['dispCallNumber'];
				if (strlen($dcallnumber) >= strlen($callnumber)) {
					$callnumber=$dcallnumber;
				}
				$cnt=0;
				$audience2="";
				$pipe="";
				$keylist="";
				$totalcopies=0;
				$totalavail=0;
				$localavail=0;
				foreach ($v['fields']['callList'] as $ndx => $info) {
					$currentcall = $info['fields']['callNumber'];
					$dcurrentcall = $info['fields']['dispCallNumber'];
					if (strlen($dcurrentcall) > strlen($currentcall)) {
						$currentcall=$dcurrentcall;
					}
					foreach ($info['fields']['itemList'] as $ndx => $iteminfo) {
						$key = $iteminfo['key'];
						$callkey = $iteminfo['fields']['call']['key'];
						$type = $iteminfo['fields']['itemType']['key'];
						$typename = $iteminfo['fields']['itemType']['fields']['description'];
						$lib = $iteminfo['fields']['library']['key'];
						$stat = $iteminfo['fields']['currentLocation']['key'];
						if ($libcall == 0 && ($callnumber <= "" || strtolower($lib) == strtolower($_SESSION['library']))) {
							$callnumber = $currentcall;
							$libcall=1;
						}
						$libname = $_SESSION['libnames'][$lib];

						if (in_array($lib,$_SESSION['libarr']) && $stat == "AVAILABLE") {
							$totalavail++;
							if ($lib == $_SESSION['library']) {
								$localavail++;
							}
						} 
					}				
				}
				if ($totalavail > 0) {
					$availability = "<span style='color:#00AA00'>";
					if ($localavail > 0) {
						if ($localavail == 1) {
							$availability.="<a class='btn btn-sm btn-success' style='color:#FFF'><i class='fas fa-copy'></i> ".$localavail." Copy Available</a>";
						} else {
							$availability.="<a class='btn btn-sm btn-success' style='color:#FFF'><i class='fas fa-copy'></i> ".$localavail." Copies Available</a>";
						}
						if ($totalavail > $localavail) {
							$other = (int)$totalavail - (int)$localavail;
							if ($other == 1) {
								$availability.= " <span style='color:#000000aa;font-size:.7em;'>(".$other." copy available in a different library)</span>";
							} else {
								$availability.= " <span style='color:#000000aa;font-size:.7em;'>(".$other." copies available in a different library)<span>";
							}
						}
					} elseif ($totalavail > 0) {
						$availability.= "<a style='color:#FFF' class='btn btn-sm btn-danger'>No Copies Available</a>";
						if ($totalavail == 1) {
							$availability.= "&nbsp;<span style='color:#000000aa;font-size:.7em;'>(".$totalavail." copy available in a different library)</span>";
						} else {
							$availability.= "&nbsp;<span style='color:#000000aa;font-size:.7em;'>(".$totalavail." copies available in a different library)</span>";
						}
					}
					$availability .= "</span>";
				} else {
					$availability = "<span style='color: #AA0000'><a style='color:#FFF' class='btn btn-sm btn-danger'>No Copies Available</a></span>";
				}
			
				echo "<input type='hidden' id='totalitems".$curr."' value='".$callinfo['count']."'>";
				echo "<input type='hidden' id='itemlist".$curr."' class='itemlist' value='".$callinfo['itemlist']."'>";
				echo "<input type='hidden' id='isbn".$curr."' class='isbn' value='".$isbn."'>";
				$marc = $v['fields']['bib']['fields'];
				unset($mrc);
				$mattype=$holdings['typ'];
				echo "<input type='hidden' id='keylist".$curr."' class='keylist' value=\"".$keylist."\">";
				unset($cnt);
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
				$auth2=str_replace(",","",$mrc['100'][0]['a']);
				$pub = $mrc['260'][0]['a']." ".$mrc['260'][0]['b']." ".$mrc['260'][0]['c'];
				$pubyear = preg_replace("/[^0-9,\s]/","",$marc['260'][0]['c']);
				if ($pubyear <= "") {
					$pubyear = preg_replace("/[^0-9,\s]/","",$mrc['264'][0]['c']);
				}
				if ($pub <= "") {
					$pub = $mrc['264'][0]['a']." ".$mrc['264'][0]['b']." ".$mrc['264'][0]['c'];
				}
				$linklist="";
				$iconlist="";
				unset($subjectlist);
				$subjectlist="";
				$subjectlength=0;
				$comma="";
				$subjectcount=0;
				foreach ($mrc[650] as $sub) {
					if ($subjectlength <= 80) {
						$subjectlist.=$comma."<a class='xaddedsubject' style='color:#000' title=\"".$sub['a']."\">".$sub['a']."</a>"; $comma=", ";
					}
					$subjectlength=$subjectlength+strlen($sub['a']);
				}
				foreach ($mrc[690] as $sub) {
					if ($subjectlength <= 80) {
						$subjectlist.=$comma."<a href='#' class='btn btn-sm btn-success addedsubject' style='text-decoration:underline;color:#000' title=\"".$sub['a']."\">".$sub['a']."</a>"; $comma=", ";
					}
					$subjectlength=$subjectlength+strlen($sub['a']);
				}
				if ($subjectlength > 80) {
					$subjectlist.=" ...";
				}
				foreach ($mrc[856] as $link) {
					$url = $link['u'];
					$text = $link['y'];
					$note = $link['z'];
					if ($note > "") {
						$note=" -- ".$note;
						
					}
					if ($url > "" && $text > "") {
						$linklist.="<a href='".$url."' target='_blank' data-toggle='tooltip' title=\"".$text."\" data-placehemt='top' class='btn btn-sm' style='color: #FFF; background-color: ".$_SESSION['color1']."'><i class='fas fa-link'></i></a>";
					}
				}
				$summary = $mrc['520'][0]['a'];
				if (strlen($summary) > 255) { 
					$summary_brief = substr($summary,0,252)."...";
				} else {
					$summary_brief=$summary;
				}
				$isbn = preg_replace("/[^0-9]/","",$mrc['020'][0]['a']);
				if (strlen($isbn) < 13) {
					$isbn = getisbn($isbn); // returns ISBN13
				}
				$audience="";
				$audience2="";
				$comma="";
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
				foreach ($mrc[521] as $kk => $vv) {
					if (strpos($vv['b'],"ountas") || strpos($vv['b'],"exile")) {
						if (strpos($vv['a'],"ountas")) { 
							$tit="Fountas & Pinnell";
						} else {
							$tit="Lexile";
						}
						$vv['b']=str_replace(".","",$vv['b']);
						//$audience.="<div class='hitrow_audience'><b>".$vv['b'].":</b> ".$vv['a']."</div>";
						$audience.=$comma.$vv['b'].": ".$vv['a'];  $comma="&nbsp;<span style='font-weight:900'>&#124;</span>&nbsp;";
					}
				}
				$pd = trim($mrc['300'][0]['a']." ".$mrc['300'][0]['b']." ".$mrc['300'][0]['c']);
				$ax++;
				$booknook="";	
				$burl="https://booknook.infohio.org/opac/isearch.php?isbn=".$isbn."&opac=fetch&title=".urlencode($title);
				$booknook = file_get_contents($burl);
				unset($info);
				$info['pubyear']=$pubyear;
				$info['hitnumber']=$curr;
				$info['subjectlist']=$subjectlist;
				$info['callnumber']=$callnumber;
				$info['title']=$title;
				$info['author']=$author;
				$info['audience']=$audience;
				$info['audience2']=$audience2;
				$info['pub']=$pub;
				$info['pd']=$pd;
				$info['availability']=$availability;
				$info['totalavail']=$totalavail;
				$info['hits']=$hits;
				$info['briefsummary']=$summary_brief;
				$info['isbn']=$isbn;
				$info['catkey']=$kkk;
				$info['linklist']=$linklist;
				$info['booknook']=$booknook;
				$info['typename']=$typename;
				showresult($info);
				
				$curr++;
			}
		}
	}
	echo "|".$rlflag."|";

	$secs = number_format(microtime(true)-$t1,2);
	fetchlog($_SESSION['wsurl'],$rlsearch."|".$searchurl."|".$query,$secs,$_SESSION['itc'],$_SESSION['instance'],$_SESSION['library']);
	function showresult($info) {
		$bn=explode(",",$info['booknook']);
		$bnx=(int)$bn[0];
		$bnl=$bn[1];
 		$coinstitle = "ctx_ver=Z39.88-2004&rft_val_fmt=info%3Aofi%2Ffmt%3Akev%3Amtx%3Abook&rft.au=".urlencode($info['author'])."&rft.date=".urlencode($info['pubyear'])."&rft.btitle=".urlencode($info['title'])."&rft.isbn=".$info['isbn']."&rft.type=fetch&rft.genre=fetch&rfr_id=info%3Asid%2Ffetch.infohio.org&rft_dat=bibnumber%2F".$info['catkey'];
?>
		<div id="hitlist<?php echo $info['hitnumber']; ?>SIRSI_COINS_2020" style='dispay:none'><span class="Z3988" id="spanhitlist<?php echo $info['hitnumber']; ?>" style="height: 0;" title="<?php echo $coinstitle; ?>"></span> </div>
<?php
		echo "<div class='row mb-4 hitrow opendetails' id='hitrow".$info['hitnumber']."' style='z-index:3;position:relative' data-toggle='offcanvas' data-target='#js-bootstrap-offcanvas'>";
			echo "<input type='hidden' id='callnumber".$info['hitnumber']."' value='".$info['callnumber']."'>";
			echo "<input type='hidden' id='firstisbn".$info['hitnumber']."' value='".$info['isbn']."'>";
			echo "<div style='display:none'><span class='ISBN SDFIELD'><div class='displayElementWrapper ISBN'><div class='displayElementLabel text-h5 ISBN'>ISBN&nbsp;</div><div class='displayElementText text-p highlightMe ISBN'>".$info['isbn']."</div></div></span></div>";
			echo "<div class='col'>";				
				echo "<div class='row' style='position:relative;z-index:90000'>";
					echo "<div class='text-left d-none d-md-block col-150' style='width:150px'>";
						echo "<div class='row'>";
							echo "<div class='text-center' style='padding-left:10px'>";
								echo "<div class='text-center mb-2 d-sm-block d-none' style='font-size:10px;background'>".$info['hitnumber']." of ".$info['hits']."</div>";
								echo "<img src='./images/nocover.png' style='position:relative;box-shadow: 1px 1px 5px rgba(0,0,0,.1);position: relative;width:140px; height:200px; border-radius:5px;-moz-border-radius:5px;background-image:url(./images/nocoverbg.png);background-size:cover;background-cover: #FF0000;background-position:center center' class='catcover' id='image_".$info['isbn']."'>";
							echo "</div>";
						echo "</div>";
					echo "</div>";
					echo "<div class='d-block d-md-none col' style='padding:0px;background-color: #FFF;border-radius:10px;overflow:hidden;'>";
						if ($info['totalavail'] > 1) {
							$bgc="#00aa00";
							$bgt="<div style='line-height:1.1em;color: #fff;font-size:1.5em;text-shadow:1px 1px 3px #000;'>".$info['callnumber']."</div>";
							$bgt.="<div style='font-size:.8em;font-weight:300;line-height:1em;'>1 Copy Available</div>";
						} elseif ($info['totalavail'] > 0) {
							$bgc="#00AA00";
							$bgt="<div style='line-height:1.1em;color: #fff;font-size:1.5em;text-shadow:1px 1px 3px #000;'>".$info['callnumber']."</div>";
							$bgt.="<div style='font-size:.8em;font-weight:300;line-height:1em;'>".$info['totalavail']." Copies Available</div>";
						} else {
							$bgc="#AA0000";
							$bgt="<div style='line-height:1.1em;color: #fff;font-size:1.5em;text-shadow:1px 1px 3px #000;'>".$info['callnumber']."</div>";
							$bgt.="<div style='font-size:.8em;font-weight:300;line-height:1em;'>Not Available</div>";
						}
						echo "<div class='mb-2 text-center' style=';background-color: ".$bgc.";padding:10px;color: #FFF;font-size: 1.3em;font-weight:900;'>".$bgt."</div>";
						echo "<div style='padding:20px;'>";
							echo "<table style='width:100%'><tr><td style='width:25%;vertical-align:top'>";
								echo "<img src='./images/nocover.png' style='max-width:150px;border: 1px solid rgba(0,0,0,.1);border-radius:5px;height:auto;width:auto;background-image:url(./images/nocoverbg.png);background-size:cover;background-cover: #FF0000;background-position:center center' class='catcover' id='image_".$info['isbn']."'>";
							echo "</td><td style='width:75%;line-height:1.2em;padding-left:10px;padding-right:5px;vertical-align:top'>";
								//echo "<div class='mb-2' style='text-align:left;color: #ff0000;font-weight:900;font-size:1.5em'>".$info['callnumber']."</div>";
								echo "<div style='font-size:1.2em;font-weight:900;color:#000' class='mb-2'>".$info['title']."<hr></div>";

								if ($info['author'] > "") { echo "<div style='color:#000;' class='mb-2'><b>Author:</b><span style='font-size:.8em'><br>".$info['author']."</span></div>"; }
								if (strlen(trim($info['pub'])) > 3) { echo "<div style='color:#000;' class='mb-2'><b>Pub. Info:</b><span style='font-size:.8em'><br>".$info['pub']."</span></div>"; }
								if (strlen(trim($info['audience'])) > 2) { echo "<div class='mb-2'><b>Reading Level:</b><br>".$info['audience']."</div>"; }
								if ($bnx > 0) { echo "<div style='color:#000;' class='mb-2 align-items-center'><a href='".$bnl."' target='_blank'><span style='color:#000;text-decoration:none'><b>Book Nook:</b></span>&nbsp;<img src='https://booknook.infohio.org/images/bkicon.png' style='height:24px;width:auto'>&nbsp;<span style='line-height:24px;color:#000'>".$bnx." Video Reviews Available</a></span></div>"; }
								if ($info['linklist'] > "") {
									echo "<div class='mb-3 mt-2'>".$info['linklist']."</div>";
								}								
							echo "</td></tr></table>";
						echo "</div>";
					echo "</div>";						
					echo "<input type='hidden' id='bibkey".$info['hitnumber']."' value='".$info['catkey']."'>";
					echo "<div class='col d-none d-md-block text-start text-left' >";
						echo "<div class='hitrow_callnumber'>".$info['callnumber']."</div>";
						echo "<div class='hitrow_title'>".$info['title']."</div>";
						echo "<div class='hitrow_author' style='margin-bottom:5px'>".$info['author']."</div>";
						if ($info['typename'] > "") {
							echo "<div class='hitrow_pub'><b>Item Group:</b>&nbsp;".$info['typename']."</div>";
						}
						if ($info['pub'] > "" && $info['pd'] > "") {
							echo "<div class='hitrow_pub'><b>Pub Info:</b>&nbsp;".$info['pub'].", ".$info['pd']."</div>";
						} else {
							echo "<div class='hitrow_pub'><b>Pub Info:</b>&nbsp;Unknown</div>";
						}
						if ($info['subjectlist'] > "" && 1 == 1) {
							echo "<div style='z-index:9999999' style='dispay:block;cursor:pointer;position:relative;' class='hitrow_pub'><b>Subject(s):</b>&nbsp;".$info['subjectlist']."</div>";
						}
						echo "<div class='mt-1 d-none d-sm-inline-block hitrow_summary mb-1'>".$info['briefsummary']."</div>";
						if ($info['audience'] > "") {
							echo "<div class='hitrow_pub' style='font-style:italic'>".$info['audience']."</div>";
						} 
						if ($info['linklist'] > "") {
							echo "<div class='hitrow_pub mb-3 mt-2'>".$info['linklist']."</div>";
						}
						echo "<div class='mt-2 d-none d-md-block' id='hitrow_avail".$info['hitnumber']."'>";
							echo $info['availability'];
							if ($bnx > 0) {
								echo "<a href='".$bnl."' target='_blank' style='text-decoration:none;font-weight:900;line-height:1em;font-size:.7em;color:#000;'><img src='https://booknook.infohio.org/images/bkicon.png' style='border-radius:3px;-moz-border-radius:3px;box-shadow: 1px 1px 10px #00000030;margin-left:5px;' title='".$bnx." Book Nook Video Review(s) Available'>&nbsp;".$bnx." VIDEO REVIEW(S)</a>";
							}
						echo "</div>";
					echo "</div>";
				echo "</div>";
			echo "</div>";
		echo "</div>";
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

// usage
	function getHoldings($keys) {

		unset($holdlist);
		unset($cnt);
		
		$copiestot=0;
		$copiesavail=0;

		foreach ($keys as $ndx => $vv) {
			// get call number_format
				$key=$vv;
				$searchurl = $_SESSION['wsurl']."catalog/item/key/".$key."?includeFields=currentLocation,itemCategory1,itemType{displayName,description},library";
				$chhold2 = curl_init($searchurl);
				curl_setopt($chhold2, CURLOPT_POST, 0);
				curl_setopt($chhold2, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($chhold2, CURLOPT_SSL_VERIFYPEER, 0); // On dev server only!
				curl_setopt($chhold2, CURLOPT_SSL_VERIFYHOST, 0); // On dev server only!
				curl_setopt($chhold2, CURLOPT_HTTPHEADER, $_SESSION['headers']);
				$result2=json_decode(curl_exec($chhold2),true);
				$loc = $result2['fields']['currentLocation']['key'];
				$typ = $result2['fields']['itemCategory1']['key'];
				$cls = $result2['fields']['itemType']['key'];
				$lib = $result2['fields']['library']['key'];
				
				$details[$key]['lib']=$lib;
				$details[$key]['type']=$typ;
				$details[$key]['cat']=$cls;
				$details[$key]['status']=$loc;
				
				if ($loc == "AVAILABLE") {
					$copiesavail++;
					$cnt[$library]['avail']++;
				}
				$cnt[$library]['total']++;
				$copiestot++;
		}
		$holdlist['copies']=$copiestot;
		$holdlist['avail']=$copiesavail;
		$holdlist['callnumber']=$callnumber;
		$holdlist['details']=$details;
		$holdlist['typ']=$typ;
		return $holdlist;
	}
	function spellcheck($phrase) {
                $x = explode(" ",$phrase);
                include("/var/www/html/badwords.php");
                $newphrase="";
                foreach ($x as $xx) {
                        if (!in_array($xx,$badwords)) {
                                $newphrase.=$xx." ";
                        }
                }

                $newphrase=trim($newphrase);
                                $cmd = "echo \"".$newphrase."\" | aspell -a --sug-mode=bad-spellers";
                                exec($cmd,$out);
                                $bad=0;
                                foreach ($out as $line) {
                                        if (substr($line,0,1) == "&") {
                                                $bad++;
                                                $pts=explode(":",$line);
                                                $x=explode(" ",$pts[0]);
                                                $badword=$x[1];
                                                $w=trim($pts[1]);
                                                $words = explode(",",$w);
                                                $good = trim($words[0]);
                                                $newphrase = str_replace($badword,$good,$newphrase);
                                        }
                                }
                unset($out);
                $out=array();
                $out['badcount']=$bad;
                $out['didyoumean']=$newphrase;
		return $out;
	}

	function fetchlog($wsurl,$search,$secs,$itc,$inst,$lib) {
		$fdb = new mysqli("localhost","fetchlog","fetchlog","fetchlog");
		$q="insert into log (wsurl,search,response,response2,itc,instance,library) values (?, ?, ?, ?, ?, ?, ?)";
		$r=$fdb->prepare($q);
		$r->bind_param("sssssss",$wsurl,$search,$secs,$secs,$itc,$inst,$lib);
		$r->execute();
		return 1;
	}
?>
