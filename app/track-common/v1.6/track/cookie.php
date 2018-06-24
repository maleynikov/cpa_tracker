<?php
	header("Content-Type: text/javascript; charset=utf-8");
?>
function cpatracker_add_lead(profit) {
	var api_key = "ec63b5ea28";
	var cookie = " " + document.cookie;
	var search = "cpa_subid=";
	var setStr = null;
	var offset = 0;
	var end = 0;
	if (cookie.length > 0) {
		offset = cookie.indexOf(search);
		if (offset != -1) {
			offset += search.length;
			end = cookie.indexOf(";", offset)
			if (end == -1) {
				end = cookie.length;
			}
			setStr = unescape(cookie.substring(offset, end));
		}
	}
	if(setStr) {
		var img= document.createElement('img');
	    img.src = '<?php echo _HTML_TRACK_PATH; ?>/p.php?n=custom&ak=' + api_key + '&subid=' + encodeURIComponent(setStr) + '&profit=' + profit;
	}
}

// Simple AJAX
function SendRequest(r_path, r_args, r_handler) {
    var Request = CreateRequest();
    if (!Request) return;
    
    Request.onreadystatechange = function() {
        if (Request.readyState == 4) {
            r_handler(Request);
        }
    }
    
    Request.open('GET', r_path, true);
    Request.setRequestHeader("Content-Type","application/x-www-form-urlencoded; charset=utf-8");
    Request.send(r_args);
} 

function CreateRequest() {
    var Request = false;
    if (window.XMLHttpRequest) {
        Request = new XMLHttpRequest();
    } else if (window.ActiveXObject) {
        try {
             Request = new ActiveXObject("Microsoft.XMLHTTP");
        } catch (CatchException) {
             Request = new ActiveXObject("Msxml2.XMLHTTP");
        }
    }
    if (!Request) {
        console.log("Невозможно создать XMLHttpRequest");
    }
    return Request;
}

function _modufy_links(subid) {
	var domain_name = window.location.hostname.split('.').slice(-2).join('.');

	var exp = new Date();
	var cookie_time=exp.getTime() + (365*10*24*60*60*1000);
	document.cookie = "cpa_subid="+subid+";path=/;domain=."+domain_name+";expires="+cookie_time;

	var host = '<?php echo str_replace(array(':80', ':443', 'www.'), '', end(explode('//', _HTML_TRACK_PATH)))?>';

	if(document.getElementsByTagName("body").length > 0) {
		var node = document.getElementsByTagName("body")[0];
		var els = node.getElementsByTagName("a");
		
		for(var i=0,j=els.length; i<j; i++) {
			href = els[i].href;
			if(href.indexOf(host) != -1 && href.indexOf('_subid=') == -1) {
				divider = href.indexOf('?') == -1 ? '?' : '&';
				els[i].href = els[i].href + divider + '_subid=' + subid;
			}
		}
	} else {
		var els = document.getElementsByTagName("frame");
		
		for(var i=0,j=els.length; i<j; i++) {
			src = els[i].src;
			if(src.indexOf(host) != -1 && src.indexOf('_subid=') == -1) {
				divider = src.indexOf('?') == -1 ? '?' : '&';
				els[i].src = els[i].src + divider + '_subid=' + subid;
			}
		}
	}
}

function modufy_links() {
	var subid = '';
	var vars = [], hash, vars2 = [];
	var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
	var parents = <?php 
		if(array_key_exists('cpa_parents', $_COOKIE)) {
			$parents = json_decode($_COOKIE['cpa_parents'], true);
		} else {
			$parents = array();
		}
		echo json_encode($parents);
	?>;
	
	for(var i = 0; i < hashes.length; i++) {
		vars2.push(hashes[i]);
	    hash = hashes[i].split('=');
	    vars.push(hash[0]);
	    vars[hash[0]] = hash[1];
	}
	
	// try get SubID from URL
	if(vars['subid']) {
		subid = vars['subid'];
		
	// try get SubID from tracker cookie
	} else if(parents != null && parents[window.location.host]) {
		subid = parents[window.location.host];
		
	// try get SubID from our cookie
	} else {
		
		var cookie = " " + document.cookie;
		var search = "cpa_subid=";
		var setStr = null;
		var offset = 0;
		var end = 0;
		if (cookie.length > 0) {
			offset = cookie.indexOf(search);
			if (offset != -1) {
				offset += search.length;
				end = cookie.indexOf(";", offset)
				if (end == -1) {
					end = cookie.length;
				}
				subid = unescape(cookie.substring(offset, end));
			}
		}
	}
	
	if(vars['rule_name'] !== undefined && subid == '') {
		//vars2.push('redirect_link=' + escape(window.location.href));
		vars2.push('referrer=' + escape(document.referrer));
		
		params = vars2.join('&');
		SendRequest('<?php echo _HTML_TRACK_PATH; ?>/track_direct.php?' + params, '', function(data) {
			if(data.status = 200 && data.response != '') {
				_modufy_links(data.response);
				return;
			}
		});
	}
	
	if(subid != '') {
		_modufy_links(subid)
	}
}

(function(){
    var DomReady = window.DomReady = {};
    var userAgent = navigator.userAgent.toLowerCase();

    var browser = {
    	version: (userAgent.match( /.+(?:rv|it|ra|ie)[\/: ]([\d.]+)/ ) || [])[1],
    	safari: /webkit/.test(userAgent),
    	opera: /opera/.test(userAgent),
    	msie: (/msie/.test(userAgent)) && (!/opera/.test( userAgent )),
    	mozilla: (/mozilla/.test(userAgent)) && (!/(compatible|webkit)/.test(userAgent))
    };    

	var readyBound = false;	
	var isReady = false;
	var readyList = [];

	function domReady() {
		if(!isReady) {
			isReady = true;
	        if(readyList) {
	            for(var fn = 0; fn < readyList.length; fn++) {
	                readyList[fn].call(window, []);
	            }
	            readyList = [];
	        }
		}
	};
	
	function addLoadEvent(func) {
	  var oldonload = window.onload;
	  if (typeof window.onload != 'function') {
	    window.onload = func;
	  } else {
	    window.onload = function() {
	      if (oldonload) {
	        oldonload();
	      }
	      func();
	    }
	  }
	};

	function bindReady() {
		if(readyBound) {
		    return;
	    }
		readyBound = true;

		if (document.addEventListener && !browser.opera) {
			document.addEventListener("DOMContentLoaded", domReady, false);
		}

		if (browser.msie && window == top) (function(){
			if (isReady) return;
			try {
				document.documentElement.doScroll("left");
			} catch(error) {
				setTimeout(arguments.callee, 0);
				return;
			}
		    domReady();
		})();

		if(browser.opera) {
			document.addEventListener( "DOMContentLoaded", function () {
				if (isReady) return;
				for (var i = 0; i < document.styleSheets.length; i++)
					if (document.styleSheets[i].disabled) {
						setTimeout( arguments.callee, 0 );
						return;
					}
	            domReady();
			}, false);
		}

		if(browser.safari) {
		    var numStyles;
			(function(){
				if (isReady) return;
				if (document.readyState != "loaded" && document.readyState != "complete") {
					setTimeout( arguments.callee, 0 );
					return;
				}
				if (numStyles === undefined) {
	                var links = document.getElementsByTagName("link");
	                for (var i=0; i < links.length; i++) {
	                	if(links[i].getAttribute('rel') == 'stylesheet') {
	                	    numStyles++;
	                	}
	                }
	                var styles = document.getElementsByTagName("style");
	                numStyles += styles.length;
				}
				if (document.styleSheets.length != numStyles) {
					setTimeout( arguments.callee, 0 );
					return;
				}
			
				domReady();
			})();
		}

	    addLoadEvent(domReady);
	};

	DomReady.ready = function(fn, args) {
		bindReady();
		if (isReady) {
			fn.call(window, []);
	    } else {
	        readyList.push( function() { return fn.call(window, []); } );
	    }
	};
	bindReady();	
})();

DomReady.ready(function() {
	modufy_links();
});