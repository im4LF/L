var scripts = document.getElementsByTagName('script');
var src;
for (var i = 0; i < scripts.length; i++) {
	if (!/L\.js/.test(scripts[i].src)) continue;
	
	src = scripts[i].src;
	break;
}

var params = {
	opened: true
};
var params_re = /([\w\-]+)\=([\w\-]+)/gi;
match = '';
while (null != (match = params_re.exec(src))) {
	params[match[1]] = eval(match[2]);
}

document.write('<div style="position:fixed;z-index:1000;top:0;right:0; background:#efefef;padding:2px 4px;">\
		<iframe id="logger-output" style="width:400px;height:300px;'+(params.opened ? '' : 'display:none;')+'" src="/logger/reader.php?url='+encodeURIComponent(location.href)+'&rand='+Math.random()+'"></iframe>\
		<div style="text-align:right;font:11px Arial;"><a href="" id="logger-toggler"><b>L</b> '+(params.opened ? 'hide' : 'show')+'</a></div>\
	</div>');

var logger_toggler = document.getElementById('logger-toggler');

logger_toggler.onclick = function() {
	if ('none' == document.getElementById('logger-output').style.display) {
		document.getElementById('logger-output').style.display = 'block';
		this.innerHTML = '<b>L</b> hide';
	}
	else {
		document.getElementById('logger-output').style.display = 'none';
		this.innerHTML = '<b>L</b> show';
	}
		
	return false;
};
