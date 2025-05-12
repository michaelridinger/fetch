$(document).ready(function(){
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
          return new bootstrap.Tooltip(tooltipTriggerEl)
        })

	$(document).on("click",".addedsubject",function() {
		var term=$(this).attr("title");
		$("#subjectq").val(term);
		$("#subjectform").submit();
	});
	// Run a query if one exists / handle recent post
	// Enable tooltips site wide

        function isScrolledIntoView(elem)
        {
                var docViewTop = $(window).scrollTop();
                var docViewBottom = docViewTop + $(window).height();

                var elemTop = $(elem).offset().top;
                var elemBottom = elemTop + $(elem).height();

                return ((elemBottom <= docViewBottom) && (elemTop >= docViewTop));
        }
        function Utils() {
        }
        Utils.prototype = {
                constructor: Utils,
                isElementInView: function (element, fullyInView) {
                        var pageTop = $(window).scrollTop();
                        var pageBottom = pageTop + $(window).height();
                        var elementTop = $(element).offset().top;
                        var elementBottom = elementTop + $(element).height();

                        if (fullyInView === true) {
                                return ((pageTop < elementTop) && (pageBottom > elementBottom));
                        } else {
                                return ((elementTop <= pageBottom) && (elementBottom >= pageTop));
                        }
                }
        };
        var Utils = new Utils();
        $("body").scroll(function() {
		if ($("#loadmorebutton").length && $("#loadmorebutton").is(":visible")) {
                        var p = parseInt($("#page").val());
                        var p2 = parseInt($("#pages").val());
                        if (p2 > p) {
                                var isElementInView = Utils.isElementInView($('#loadmorebutton'), true);
                                if (isElementInView && $("#loadmorebutton").is(":visible")) {
					$("#loadmorebutton").hide();
                                        loadmoreresults();
                                }
                        }
		}

        });

	window.addEventListener('scroll', function(e) {
		if( $("#opacheader").isOnScreen() ) {
			$("#gohome").hide();
 		} else {
			$("#gohome").show();
		}
	});
	$("#fetchlogout").on("click",function() {
		$("#action").val("logout");
		$("#stform").submit();
	});
	$("#debugboxbutton").on("click",function() {
		$("#debugbox").slideToggle("slow");
	});
	$(document).on("click",".showallholdingsbutton",function() {
		var curholdstat = $("#showallholdings").val();
		if (curholdstat == "N") {
			$("#showallholdings").val("Y");
			$("#showallholdingsbutton").removeClass("fa-toggle-off").addClass("fa-toggle-on").css("color","#50FF50");
			$(".notinmylib").show("1000");
			$("#sahb").hide();
			$("#myholdingstitle").hide();
			$("#allholdingstitle").show();
		} else {
			$("#sahb").show();
			$("#myholdingstitle").show();
			$("#allholdingstitle").hide();
			$("#showallholdings").val("N");
			$("#showallholdingsbutton").removeClass("fa-toggle-on").addClass("fa-toggle-off").css("color","#808080");
			$(".notinmylib").hide("1000");
		}
		
	});
	$("#allresultsloaded").click(function() {
		$("html, body").animate({
		scrollTop: $("body").offset().top}, 1000);
	});
	$("#gohome").click(function() {
		$("html, body").animate({
		scrollTop: $("#opacheader").offset().top}, 1000);
	});
	$("#vsimage").on("mouseover",function() {
		$(this).attr("src","images/visualsearch2.jpg");
	});
	$("#toggle_advanced").on("click",function() {
		$(".advsearchoption").slideToggle("slow");
	});
	$("#rptype").on("change",function() {
		var cur = $("#rpselected").val();
		if ($(this).val() == "LX") {
			$("#rp_ar").slideUp("slow",function() { 
				$("#rp_ar").hide();
				$("#rp_lx").slideDown("slow");
			});
		} else {
			$("#rp_lx").slideUp("slow",function() {
				$("#rp_ar").slideUp("slow",function() {
					$("#rp_ar").slideDown("slow");
				});
			});
		}
	});
	$("#toggle_advanced").on("click",function() {
		$(".advancedoption").slideToggle("slow");
	});
	$("#newsearch").on("click",function() {
		$("#q").val("");
		$("#page").val("");
		$("#pages").val("");
		$("#scope").val("");
		$("#searchform").submit();
	});
	// Submit form using a button
	$("#searchfieldselect").on("change",function() {
		$("#scope").val($(this).val());
	});
	$("#button_search").on("click",function() {
		$("#searchform").submit();
	});
	
	$(".scope").on("click",function() {
		var scope = $(this).attr("id").replace("button_","");
		$("#scope").val(scope);
		$("#searchform").submit();
	});
	$(".menu_rp").on("click",function() {
		$("#view").val("rp");
		$("#action").val("newrpsearch");
		$("#stform").submit();
	});
	$(document).on("click",".hitrowXXX",function() {
		var id = $(this).attr("id").replace("hitrow","");
		$("#itemform"+id).submit();
	});
	$("#library").on("change",function() {
		$("#searchform").submit();
	});
	$(document).on("click",".opendetails",function() {
		var sel = getSelection().toString();
		if(!sel){
			var id = $(this).attr("id").replace("hitrow","");
			var numb = parseInt(id)-1;
			if (numb < 0) { numb="browser"; } else { numb = "hitrow"+numb;}
			$("#lastviewed").val(numb);
			var key = $("#bibkey"+id).val();
			$("#detailsModalBody").html("<div style='text-align:center;padding:30px;'><div class='spinner-border text-primary' role='status'><span class='sr-only'>Loading...</span></div></div>");
			$("#detailsModalTitle").html($("#callnumber"+id).val());
			$("#detailsModalFooter").html("");
			$("#detailsModal").modal('show');
			$.get("/view_details_ajax.php?catalogkey="+key+"&id="+id,function(data) {
				var dtl=data.split("|");
				$("#detailsModalBody").html(dtl[0]);
				getcovers();
			});
		}
	});
	$(document).on("click",".closeDetails",function() {
		$("#browser").show();
		$("#itemDetailsBox").hide();
		$("html, body").animate({
			scrollTop: $("#"+$("#lastviewed").val()).offset().top}, 300);		
	});
	var holdcount=0;
	$(document).on("click",".placehold",function() {
		holdcount++;
		var id = $(this).attr("id").replace("placehold","");
			var url = $("#holdsurl"+id).val();
			var sw = screen.width;
			var lft = sw/2-300;
			window.open(url+"&holdcount="+holdcount,"PlaceHold"+holdcount,"toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=400,height=600,top=100,left=100");
	});	
	$(document).on("click","#showisearchresultsbutton",function() {
		var url = $("#isearchresultsurl").val();
		var sw = screen.width;
		var lft = sw/2-300;
		window.open(url,"ISearch Results","toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,width=400,height=600,top=100,left="+lft);
	});	
	$(document).on("click","#togglemarcdetails",function() {
		$("#marcdetails").slideToggle("slow");
	});
	$(document).on("click",".closethismodal",function() {
		$("#detailWindow").modal("hide");
	});
	$(document).on("click",".marcdetailsbutton",function() {
		var id = $(this).attr("id").replace("marcdetails","");
		$("#marcdetaillist"+id).slideToggle("slow");
	});
	$(document).on("click","#accmodebutton",function() {
		$("#accmodeform").submit();
	});
        $(document).on("click","#loadmorebutton",function() {
		var page = $("#page").val();
		var pages = $("#pages").val();
		if (page >= pages) {
			alert("All results have been loaded");
		} else {
			var page = parseInt(page)+1;
			$("#page").val(page);
		}
                var frm = $("#searchform").serialize();
                $("#loader").show();
                $.post(
                   './getresults.php',
                        frm,
                        function(data){
                                $("#results").show();
                                var results = data.split("|");
                                var start = results[0]*1;
                                var end = parseInt(results[1]*1-1);
                                var tot = results[2]*1;
                                var summary = results[3];
                                var nav = results[4];
                                var warning = results[6];
                                var recs = results[7];
                                var pages = results[5];
                                $("#searchsummary").html(summary);
                                $("#searchsummary2").html(summary);
                                if (nav > "") {
                                        $("#nav_top").html(nav);
                                        $("#nav_bottom").html(nav);
                                }
                                $("#pages2").html("Page "+$("#page").val()+" of "+pages);
                                $("#pages3").html("Page "+$("#page").val()+" of "+pages);
                          $("#recordcount").html("<div class='uk-text-center' style='font-size:.7em'><span style='color: #eee'>"+tot+" Found:&nbsp;&nbsp;</span>"+summary+"</div>");
                          $("#results").append(recs);
                          $("#loader").hide();
                          getcovers();
                        }

                );
        });

	$(document).on("change",".pageselector",function() {
		var page = $(this).val();
		$("#page").val(page);

		var frm = $("#searchform").serialize();
		$("#loader").show();
		$.post(
		   './getresults.php',
			frm,
			function(data){
				  $("#results").show();
				  var results = data.split("|");
				  var start = results[0]*1;
				  var end = parseInt(results[1]*1-1);
				  var tot = results[2]*1;
				  var summary = results[3];
				  var nav = results[4];
				  var warning = results[6];
				  var recs = results[7];
				  var pages = results[5];
				  $("#searchsummary").html(summary);
				  $("#searchsummary2").html(summary);
				  if (nav > "") {
					  $("#nav_top").html(nav);
					  $("#nav_bottom").html(nav);
				  }
				$("#pages2").html("Page "+$("#page").val()+" of "+pages);
				$("#pages3").html("Page "+$("#page").val()+" of "+pages);
			  $("#recordcount").html("<div class='uk-text-center' style='font-size:.7em'><span style='color: #eee'>"+tot+" Found:&nbsp;&nbsp;</span>"+summary+"</div>");
			  $("#results").html(recs).css("opacity","1");
			  $("#loader").hide();
			  getcovers();
			}
				
		);
	});

	$(document).on("click",".gopage",function() {
		$("#catresultsgrid").css("opacity",".2");
		if (1) {
			var id = $(this).attr("id");
			var curpage = parseInt($("#page").val());
			var pages = parseInt($("#pages").val());
			var page = id.replace("newpage_","").replace("page_","");			 
			$("#page").val(page);
			$("#nav_bottom").hide();
			var frm = $("#searchform").serialize();
			$("#loader").show();
			$("#results").html("");
			$.post(
			   './getresults.php',
				frm,
				function(data){
					var results = data.split("|");
					var start = results[0]*1;
					var end = parseInt(results[1]*1-1);
					var tot = results[2]*1;
					var summary = results[3];
					var nav = results[4];
					var pages = results[5];
					var warning = results[6];
					var recs = results[7];
					$("#searchsummary").html(summary);
					$("#searchsummary2").html(summary);
					$("#searchsummary").show();
					$("#searchsummary2").show();
					if (nav > "") {
						$("#nav_top").html(nav);
						$("#nav_bottom").html(nav);
					}
					$("#pages2").html("Page "+$("#page").val()+" of "+pages);
					$("#pages3").html("Page "+$("#page").val()+" of "+pages);
					$("#recordcount").html("<div class='uk-text-center' style='font-size:.7em'><span style='color: #eee'>"+tot+" Found:&nbsp;&nbsp;</span>"+summary+"</div>");
				 
					$("#results").html(recs).css("opacity","1");
					$("#loader").hide();
					$("#mainsearchbox").show();
					$("#nav_bottom").show();
					getcovers();
				}
			);
		}
	});
	$("#todayspic").mouseover(function() {
		$("#opaccontent").fadeOut(1000);
	});
	$("#todayspic").mouseout(function() {
		$("#opaccontent").fadeIn(1000);
	});
	$(document).on("click",".menu_vs",function() {
		$("#q").val("");
		$("#action").val("newvisualsearch");
		$("#stform").submit();
	});
	$(".menu_newsearch").on("click",function() {
		$("#action").val("newsearch");
		$("#stform").submit();
	});
	$(document).on("click","#newsearch2",function() {
		$("#action").val("newsearch");
		$("#stform").submit();
	});
	if ($("#vs").val() == "Y") {
		$(".cat_home").css("display","inline-block");
	}
	$(".iconcell").on("click",function() {
		if ($(this).attr("id") == "visual_search_home") {
			var prs = $(this).attr("id").split("_info_");
			var p = prs[0];
			$(".iconcell").hide();
			var b = $("#vsbackcat").val();
			$(".cat_"+b).css("display","inline-block").show();
			if (b != "home") {
				$("#visual_search_home").show();
				if (p == "visual_search_home") { 
					p = "home";
				}
				$("#vsbackcat").val(p);
			} else {
				$(".cat_home").show();
			}
		} else {
			var prs = $(this).attr("id").split("_info_");
			if ($(this).hasClass("cat_home")) {
				var goback="home";
			} else {
				var goback = prs[0];
			}
			$("#vsbackcat").val(goback);
			var id = prs[0];
			var qid = prs[2];
			var subcat = prs[1];
			var query = $("#query_"+qid).val();
			if (query > "") {
				$("#q").val(query);
				$("#searchtype").val("GENERAL");
				$("#xsearchtype").val("GENERAL");
				$("#vs").val("N");
				$("#bool").val($("#bool_"+qid).val());
				$("#searchform").submit();
			} else {
				$(".iconcell").hide();
				$("#visual_search_home").css("display","inline-block").show();
				$(".cat_"+subcat).css("display","inline-block").show();
			}
		}
	});
	$(document).on("change",".badword",function() {
		var id = $(this).attr("id").replace("badword_","");
		var q = $("#oq").val().replace(id,$(this).val());
		$("#q").val(q);
	});
	$(document).on("click","#runnewsearch",function() { $("#searchform").submit();});
	$(document).on("click","#didyoumean_openai",function() {
		var q = $("#didyoumean_openai").html();
		$("#q").val(q);
		$("#searchform").submit();
	});
	$(document).on("click","#didyoumean",function() {
		var q = $("#didyoumean").html();
		$("#q").val(q);
		$("#searchform").submit();
	});
	$("#button_search").on("click",function() {
		var school = $("#fetchlibname").html();
		gtag('event', "Anchor Click", {
			'event_category': 'Fetch',
			'event_label': $(this).attr("href"),
			'element_id': $(this).attr("id"),
			'send_to': 'G-783FWF9E22',
			'searchtype': "",
			'profile': $("#ga_isearchprofile").val(),
			'library': $("#ga_lib").val(),
			'itc': $("#ga_itc").val(),
			'instance': $("#ga_inst").val(),
			'searchterm': $("#q").val(),
			'source': 'fetch',
			'distname': school
		});
	});
	if ($("#searchform").length) {
		if ($("#startnewsearch").val() != "Y" && ($("#q").val() > "" || ($("#posted").val() == "Y" && $("#rptype").val() > ""))) {	
			$("#warning").hide();
			$("#warningtext").html("");
			$("#loader").show();
			$(".menu_tryagain").show();
			$("#mainsearchbox").hide();
			$("#nav_bottom").hide();
			$("#results").html("");
			var frm = $("#searchform").serialize();
			$.post(
			   './getresults.php',
				frm,
				function(data){
				  $("#loader").hide();
				  $("#results").show();
				  var results = data.split("|");
				  var start = results[0]*1;
				  var end = parseInt(results[1]*1-1);
				  var tot = results[2]*1;
				  $("#hits").val(tot);
				  if (tot <= 0) {
			if ($("#openai").val() == "dlfnnoacsctest") {
				$.post("didyoumean.php", {
					search: $("#q").val(),
					oldsearch: oldsearch
				}, function(data,status) {
					if (1 == 1) {
						if (data == "ERR") {
							$("#didyoumean_openai_container").append("<div>Old Search</div>");
						} else {
							$("#didyoumean_openai_container").html(data);
							$("#didyoumean_openai_container").slideDown("slow");
						}
					}
				});
				var oldsearch=$("#q").val();
			}
				  }
				  var summary = results[3];
				  $("#searchsummary").html(summary);
				  $("#searchsummary2").html(summary);
				  $("#searchsummary").show();
				  $("#searchsummary2").show();
				  var nav = results[4];
				  var pages = results[5];
				  var warning = results[6];
				  var recs = results[7];
				  var rl = results[8];
				  $("#nav_top").html(nav);
				  $("#nav_bottom").html(nav);
				  $("#pages").val(pages);
				  $("#nav_bottom").show();
				  $("#results").html(recs);
				  $("#searchsummary").show();
				  $("#searchsummary2").show();
				  $("#mainsearchbox").show();
				  if (warning > "") {
					  $("#warningtext").html(warning);
					  $("#warning").show();
				  }
				  
				  getcovers();
				 
					if (rl == "v") {
					  var searchtype="Visual Search";
					  var searchtype2="visual_search";
					} else {
					  if (rl == "r") {
					  var searchtype="Reading Level Search";
					  var searchtype2="readinglevel_search";
					  } else {
						  searchtype="Keyword Search";
						  var searchtype2="keyword_search";
					  }
					}
					var ip = $("#ga_isearchprofile").val();
					if (ip <= "") {
						var ip = $("#ga_isearchprofile").val();
					}
					var school = $("#fetchlibname").html();
					
					gtag('event', "Search", {
						'event_category': 'Fetch',
						'event_label': searchtype,
						'send_to': 'G-783FWF9E22',
						'searchtype': searchtype2,
						'profile': $("#ga_isearchprofile").val(),
						'source': 'fetch',
						'library': $("#ga_lib").val(),
						'itc': $("#ga_itc").val(),
						'instance': $("#ga_inst").val(),
						'seachterm': $("#q").val(),
						'searchterm': $("#q").val(),
						'distname': school
					});
				}
			);
		}  else {
			$("#tryagainbutton").hide();
		}
	}

	
	$("#rp").on("click",function() {
		var ip = $("#ga_isearchprofile").val();
		if (ip <= "") {
			var ip = $("#ga_isearchprofile").val();
		}
		var school = $("#fetchlibname").html();
		gtag('event', "Fetch Search", {
			'event_category': "New Search",
			'event_label': "New Reading Level Search",
			'send_to': 'G-783FWF9E22',
			'search_type': "rl_search",
			'isearch_profile': $("#ga_isearchprofile").val(),
			'fetch_library': $("#ga_lib").val(),
			'fetch_itc': $("#ga_itc").val(),
			'fetch_instance': $("#ga_inst").val(),
			'seach_term': $("#q").val(),
			'fetch_distname': school
		});
	});
	$("#newsearch").on("click",function() {
		var ip = $("#ga_isearchprofile").val();
		if (ip <= "") {
			var ip = $("#ga_isearchprofile").val();
		}
		var school = $("#fetchlibname").html();
		gtag('event', "Fetch Search", {
			'event_category': "New Search",
			'event_label': "New Keyword Search",
			'send_to': 'G-783FWF9E22',
			'search_type': "kw_search",
			'isearch_profile': $("#ga_isearchprofile").val(),
			'fetch_library': $("#ga_lib").val(),
			'fetch_itc': $("#ga_itc").val(),
			'fetch_instance': $("#ga_inst").val(),
			'seach_term': $("#q").val(),
			'fetch_distname': school
		});
	});
	
	function checkisearch() {
		var url = "/isearch.php?"+$("#isearchconnector").val()+"|"+$("#q").val();
		if ($("#q").val() > "") {
			$.get(url,function(data) {
				alert(data);
			});
		}
	}
	
	function getholdings() {
		$(".keylist").each(function() {
			var id = $(this).attr("id").replace("keylist","");
			var keys = $(this).val();
			$.post(
			   './getholdings.php',
				{ keylist: keys },
				function(data){
					var res=data.split("|");
					var out = res[0];
				  $("#hitrow_avail"+id).html(out);
				  $("#hold"+id).val(res[2]);
				}
			);			
		});
	}
	if ($("#details").length) {
		getcovers();
	}
	$(document).on("click",".link856",function() {
		var id = $(this).attr("id").replace("link856","");
		var url = $("#link856url"+id).val();
		var win = window.open(url,"_blank");
		if (win) {
			win.focus();
		} else {
			alert("Please enable popups for Fetch");
		}
	});
	$(document).on("click",".findmoreauthor",function() {
		$("#newq").val($("#findauthor").val());
		$("#newscope").val("AUTHOR");
		$("#detailsform").submit();	
	});
	function getcovers() {
		var page = parseInt($("#page").val())*1;
		var pages = parseInt($("#pages").val())*1;
		if (page < pages) {
			$("#loadmorebutton").show();
			$("#allresultsloaded").hide();
		} else {
			$("#loadmorebutton").hide();
			$("#allresultsloaded").show();
		}
		$(".catcover").each(function() {
			var isbn = $(this).attr("id").replace("image_","");
			$(this).attr("src","https://www.syndetics.com/index.aspx?isbn="+isbn+"/MC.GIF&client=419-222-7417&type=unbound&upc=&oclc=&issn=cover&");
		});
		$(".catcover2").each(function() {
			var isbn = $(this).attr("id").replace("image_","");
			$(this).attr("src","https://www.syndetics.com/index.aspx?isbn="+isbn+"/LC.GIF&client=419-222-7417&type=unbound&upc=&oclc=&issn=cover&");
		});
	}
        function loadnextpage() {
		var page=$("#page").val();
		if (page < "1") {
			$("#page").val("1");
			var page=1;
		} else {
			var page=parseInt($("#page").val())+1;
			$("#page").val(page);
		}

                var frm = $("#searchform").serialize();
                $("#loader").show();
                $.post(
                   './getresults.php',
                        frm,
                        function(data){
                                  $("#results").show();
                                  var results = data.split("|");
                                  var start = results[0]*1;
                                  var end = parseInt(results[1]*1-1);
                                  var tot = results[2]*1;
                                  var summary = results[3];
                                  var nav = results[4];
                                  var warning = results[6];
                                  var recs = results[7];
                                  var pages = results[5];
                                  $("#searchsummary").html(summary);
                                  $("#searchsummary2").html(summary);
                                  if (nav > "") {
                                          $("#nav_top").html(nav);
                                          $("#nav_bottom").html(nav);
                                  }
                                $("#pages2").html("Page "+$("#page").val()+" of "+pages);
                                $("#pages3").html("Page "+$("#page").val()+" of "+pages);
                          $("#recordcount").html("<div class='uk-text-center' style='font-size:.7em'><span style='color: #eee'>"+tot+" Found:&nbsp;&nbsp;</span>"+summary+"</div>");
                          $("#results").html(recs).css("opacity","1");
                          $("#loader").hide();
                          getcovers();
                        }

                );
        }
        function loadmoreresults() {
                var page = parseInt($("#page").val());
                var pages = parseInt($("#pages").val());
		if (pages < 1) { 
			$("#pages").val($("#page").val());
			var pages = page;
		}
                if (page < pages) {
                        var page = parseInt(page)+1;
                        $("#page").val(page);
                }
                var frm = $("#searchform").serialize();
                $("#loader").show();
                $.post(
                   './getresults.php',
                        frm,
                        function(data){
                                $("#results").show();
                                var results = data.split("|");
                                var start = results[0]*1;
                                var end = parseInt(results[1]*1-1);
                                var tot = results[2]*1;
                                var summary = results[3];
                                var nav = results[4];
                                var warning = results[6];
                                var recs = results[7];
                                var pages = results[5];
                                $("#searchsummary").html(summary);
                                $("#searchsummary2").html(summary);
                                if (nav > "") {
                                        $("#nav_top").html(nav);
                                        $("#nav_bottom").html(nav);
                                }
                                $("#pages2").html("Page "+$("#page").val()+" of "+pages);
                                $("#pages3").html("Page "+$("#page").val()+" of "+pages);
                          $("#recordcount").html("<div class='uk-text-center' style='font-size:.7em'><span style='color: #eee'>"+tot+" Found:&nbsp;&nbsp;</span>"+summary+"</div>");
                          $("#results").append(recs);
                          $("#loader").hide();
                          getcovers();
                        }

                );
        }
  	window.dataLayer = window.dataLayer || [];
  	function gtag(){
		dataLayer.push(arguments);
	}
});
