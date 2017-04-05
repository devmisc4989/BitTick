;(function (bt) {
    "use strict";
	var fromWindow = false;
	var sendDomain = '*';
	var callBacks = {};
	var lastCallBackId = 0;
	var com = {
		init: function(){
			window.addEventListener('message', onReceiveMessage);
			bt.Log('[COM] Communication framework inited!');
		},
		sendMessage: sendMessage,
		sendCallBackMessage : sendCallBackMessage,
		bt_ping: function(fromUrl){
			if(fromUrl)
				sendDomain = fromUrl;
			sendMessage('bt_pong', {BTEditorUrl: window.BTEditorProxy || '', BTSkipUrl:'', BTUrl: getDomain() });
		}
	}
	
	/* heplers */
	function getDomain(){
		if (!window.location.origin)
		  return window.location.protocol + "//" + window.location.hostname + (window.location.port ? ':' + window.location.port: '');
		else
			return window.location.origin;
	}
	function getNextCallBackId(){
		return 'cb_' + (lastCallBackId++);
	}
	function sendMessage(method){
		if(fromWindow){
			var prms = [];
			for(var i=1; i<arguments.length; i++)
				prms.push(arguments[i]);
			fromWindow.postMessage(JSON.stringify({"method": method, params: prms || [] }), sendDomain);
		}
	}
	function sendCallBackMessage(method, callBack){
		if(fromWindow){
			
			var cbid = getNextCallBackId();
			callBacks[cbid] = callBack;
			
			var prms = [];
			for(var i=2; i<arguments.length; i++)
				prms.push(arguments[i]);
			fromWindow.postMessage(JSON.stringify({"method": method, cbid: cbid, params: prms || [] }), sendDomain);
		}
	}
	function onReceiveMessage(event){
		if (event == null || typeof event != "object") {
			bt.Log("[COM] Missing event");
			return;
		}
		var info = event.data;
		if (info == null) {
			bt.Log("[COM] Missing info");
			return;
		}
		try { info = JSON.parse(info); }
		catch (e) {
			bt.Log("[COM] Parse info failure: " + e.message);
			return;
		}
		if (typeof info != "object") {
			bt.Log("[COM] Invalid info received");
			return;
		}
		if(info.cbid){
			if(typeof callBacks[info.cbid] == 'function'){
				var params = info.params || [];
				callBacks[info.cbid].apply(null, params);
			}
		}
		else if(info.method){
			if(!fromWindow)
				fromWindow = event.source;
			var method = info.method;
			var params = info.params || [];
			if(method.indexOf('.') != -1){
				var objPaths = method.split('.');
				if(objPaths.length > 0){					
					var rootObj = window;
					for(var i=0; i<objPaths.length; i++){
						if(rootObj[objPaths[i]])
							rootObj = rootObj[objPaths[i]]
						else
							break;
					}
					if(typeof rootObj == 'function')
						rootObj.apply(null, params);
				}
			}
			else if(method == 'eval'){
				if(params[0]!='')
					bt.jQuery.globalEval(params[0]);
			}
			else if(bt[method]){
				bt.Log("[COM] ->>>>>>>>>>>>>>>>>> Method "+method+" found in bt object");
				bt[method].apply(bt, params);
			}
			else if(com[method]){
				bt.Log("[COM] ->>>>>>>>>>>>>>>>>> Method "+method+" found in communication object");
				com[method].apply(com, params);
			}
			else if(window[method]){
				bt.Log("[COM] ->>>>>>>>>>>>>>>>>> Method "+method+" is global");
				window[method].apply(null, params);
			}
			else {
				bt.Log("[COM] ->>>>>>>>>>>>>>>>>> Method "+method+" not found!!!!");
			}
		}
	}
	
	
	bt.com = com;	
	bt.com.init();
	
})(BlackTri);
