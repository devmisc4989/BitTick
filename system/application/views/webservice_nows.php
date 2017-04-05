<?
// set output content type to Javascript
header('Content-type: application/x-javascript');
?>
var _btView = {
	exec : function (attempts) {
		if(typeof _bt === 'object') {
			_bt.setReady('done');	
			_bt.removePreloader();
			_bt.setNoWS();
		}
		else {
			if(attempts > 0) {
				setTimeout(function() {_btView.exec(attempts-1)},30);
			}
		}
	}
};
_btView.exec(5);
// BTO webservice noWs