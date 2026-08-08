<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Supported Protocols and Wrappers - Manual</title>
 <style type="text/css" media="all">
  @import url("@w{2XX58MCD}");
  @import url("@w{884KPP5P}");
  
 </style>
 <!--[if IE]><![if gte IE 6]><![endif]-->
  <style type="text/css" media="print">
   @import url("@w{M98RFPWS}");
  </style>
 <!--[if IE]><![endif]><![endif]-->
 <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
 <link rel="shortcut icon" href="@w{NGWYKJ8F}" />
 <link rel="contents" href="index.php" />
 <link rel="index" href="langref.php" />
 <link rel="prev" href="context.params.php" />
 <link rel="next" href="wrappers.file.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/wrappers" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="alternate" href="/manual/en/feeds/wrappers.atom" type="application/atom+xml" />
 <link rel="canonical" href="http://php.net/manual/en/wrappers.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/wrappers.php" />
 <meta http-equiv="Content-language" content="en" />
            <script type="text/javascript" src="@w{ME5H2G8Y}"></script>
            <script type="text/javascript" src="@w{BYSKBGP9}"></script>
<script type="text/javascript">
$(document).ready(function() {
    var toggleImage = function(elem) {
        if ($(elem).hasClass("shown")) {
            $(elem).removeClass("shown").addClass("hidden");
            $("img", elem).attr("src", "/images/notes-add.gif");
        }
        else {
            $(elem).removeClass("hidden").addClass("shown");
            $("img", elem).attr("src", "/images/notes-reject.gif");
        }
    };

    $(".soft-deprecation-notice h1.title").each(function() {
        $(this).prepend("<a class='toggler shown' href='#'><img src='/images/notes-reject.gif' alt='minimize' /></a> ");
    });
    $(".refsect1 h3.title").each(function() {
        url = "@w{BD87E369}" + $(this).parent().parent().attr("id") + "%23" + $(this).parent().attr("id");
        $(this).parent().prepend("<div class='reportbug'><a href='" + url + "'>Report a bug</a></div>");
        $(this).prepend("<a class='toggler shown' href='#'><img src='/images/notes-reject.gif' alt='reject note' /></a> ");
    });
    $("#usernotes .head").each(function() {
        $(this).prepend("<a class='toggler shown' href='#'><img src='/images/notes-reject.gif' alt='reject note' /></a> ");
    });
    $(".soft-deprecation-notice h1.title .toggler").click(function() {
        $(this).parent().siblings().slideToggle("slow");
        toggleImage(this);
        return false;
    });
    $(".refsect1 h3.title .toggler").click(function() {
        $(this).parent().siblings().slideToggle("slow");
        toggleImage(this);
        return false;
    });
    $("#usernotes .head .toggler").click(function() {
        $(this).parent().next().slideToggle("slow");
        toggleImage(this);
        return false;
    });
});
</script>

</head>
<body>

<div id="headnav">
 <a href="/" rel="home"><img src="@w{BJ2SG82M}"
 alt="PHP" width="120" height="67" id="phplogo" /></a>
 <div id="headmenu">
  <a href="/downloads.php">downloads</a> |
  <a href="/docs.php">documentation</a> |
  <a href="/FAQ.php">faq</a> |
  <a href="/support.php">getting help</a> |
  <a href="/mailing-lists.php">mailing lists</a> |
  <a href="/license">licenses</a> |
  <a href="@w{WEGCK3BV}">wiki</a> |
  <a href="@w{JBVFFY7T}">reporting bugs</a> |
  <a href="/sites.php">php.net sites</a> |
  <a href="/conferences/">conferences</a> |
  <a href="/my.php">my php.net</a>
 </div>
</div>

<div id="headsearch">
 <form method="post" action="/search.php" id="topsearch">
  <p>
   <span title="Keyboard shortcut: Alt+S (Win), Ctrl+S (Apple)">
    <span class="shortkey">s</span>earch for
   </span>
   <input type="text" name="pattern" value="" size="30" accesskey="s" />
   <span>in the</span>
   <select name="show">
    <option value="all"      >all php.net sites</option>
    <option value="local"    >this mirror only</option>
    <option value="quickref" selected="selected">function list</option>
    <option value="manual"   >online documentation</option>
    <option value="bugdb"    >bug database</option>
    <option value="news_archive">Site News Archive</option>
    <option value="changelogs">All Changelogs</option>
    <option value="pear"     >just pear.php.net</option>
    <option value="pecl"     >just pecl.php.net</option>
    <option value="talks"    >just talks.php.net</option>
    <option value="maillist" >general mailing list</option>
    <option value="devlist"  >developer mailing list</option>
    <option value="phpdoc"   >documentation mailing list</option>
   </select>
   <input type="image"
          src="@w{XXWWP636}"
          class="submit" alt="search" />
   <input type="hidden" name="lang" value="en" />
  </p>
 </form>
</div>

<div id="layout_2">
 <div id="leftbar">
<!--UdmComment-->
<ul class="toc">
 <li class="header home"><a href="index.php">PHP Manual</a></li>
 <li class="header up"><a href="langref.php">Language Reference</a></li>
 <li><a href="language.basic-syntax.php">Basic syntax</a></li>
 <li><a href="language.types.php">Types</a></li>
 <li><a href="language.variables.php">Variables</a></li>
 <li><a href="language.constants.php">Constants</a></li>
 <li><a href="language.expressions.php">Expressions</a></li>
 <li><a href="language.operators.php">Operators</a></li>
 <li><a href="language.control-structures.php">Control Structures</a></li>
 <li><a href="language.functions.php">Functions</a></li>
 <li><a href="language.oop5.php">Classes and Objects</a></li>
 <li><a href="language.namespaces.php">Namespaces</a></li>
 <li><a href="language.exceptions.php">Exceptions</a></li>
 <li><a href="language.references.php">References Explained</a></li>
 <li><a href="reserved.variables.php">Predefined Variables</a></li>
 <li><a href="reserved.exceptions.php">Predefined Exceptions</a></li>
 <li><a href="reserved.interfaces.php">Predefined Interfaces</a></li>
 <li><a href="context.php">Context options and parameters</a></li>
 <li class="active"><a href="wrappers.php">Supported Protocols and Wrappers</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="wrappers.file.php">file://<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="context.params.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Context parameters</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/wrappers.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/wrappers.php">Brazilian Portuguese</option>
    <option value="zh/wrappers.php">Chinese (Simplified)</option>
    <option value="fr/wrappers.php">French</option>
    <option value="de/wrappers.php">German</option>
    <option value="ja/wrappers.php">Japanese</option>
    <option value="pl/wrappers.php">Polish</option>
    <option value="ro/wrappers.php">Romanian</option>
    <option value="ru/wrappers.php">Russian</option>
    <option value="fa/wrappers.php">Persian</option>
    <option value="es/wrappers.php">Spanish</option>
    <option value="tr/wrappers.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="wrappers" class="reference">
 <h1 class="title">Supported Protocols and Wrappers</h1>
 <div class="partintro">
  <p class="para">
   PHP comes with many built-in wrappers for various URL-style protocols
   for use with the filesystem functions such as  <span class="function"><a href="function.fopen.php" class="function">fopen()</a></span>,
    <span class="function"><a href="function.copy.php" class="function">copy()</a></span>,  <span class="function"><a href="function.file-exists.php" class="function">file_exists()</a></span> and
    <span class="function"><a href="function.filesize.php" class="function">filesize()</a></span>.
   In addition to these wrappers, it is possible to register custom wrappers
   using the  <span class="function"><a href="function.stream-wrapper-register.php" class="function">stream_wrapper_register()</a></span> function.
  </p>
  <blockquote class="note"><p><strong class="note">Note</strong>: 
   <span class="simpara">
    The URL syntax used to describe a wrapper only supports the
    <em>scheme://...</em> syntax. The <em>scheme:/</em>
    and <em>scheme:</em> syntaxes are not supported.
   </span>
  </p></blockquote>
 </div>
 
 







 







 







 







 







 







 







 







 







 







 







 







 
<h2>Table of Contents</h2><ul class="chunklist chunklist_reference"><li><a href="wrappers.file.php">file://</a> — Accessing local filesystem</li><li><a href="wrappers.http.php">http://</a> — Accessing HTTP(s) URLs</li><li><a href="wrappers.ftp.php">ftp://</a> — Accessing FTP(s) URLs</li><li><a href="wrappers.php.php">php://</a> — Accessing various I/O streams</li><li><a href="wrappers.compression.php">zlib://</a> — Compression Streams</li><li><a href="wrappers.data.php">data://</a> — Data (RFC 2397)</li><li><a href="wrappers.glob.php">glob://</a> — Find pathnames matching pattern</li><li><a href="wrappers.phar.php">phar://</a> — PHP Archive</li><li><a href="wrappers.ssh2.php">ssh2://</a> — Secure Shell 2</li><li><a href="wrappers.rar.php">rar://</a> — RAR</li><li><a href="wrappers.audio.php">ogg://</a> — Audio streams</li><li><a href="wrappers.expect.php">expect://</a> — Process Interaction Streams</li></ul>
</div>
<br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="wrappers.file.php">file://<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="context.params.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Context parameters</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/wrappers.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=wrappers&amp;redirect=http://www.php.net/manual/en/wrappers.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=wrappers&amp;redirect=http://www.php.net/manual/en/wrappers.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Supported Protocols and Wrappers</strong>
 </div><div id="allnotes">
 <a name="108918"></a>
 <div class="note">
  <strong class='user'>aaron dot mason+php at thats-too-much dot info</strong>
  <a href="#108918" class="date">05-Jun-2012 01:46</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Be aware of code injection, folks - like anything else you take from the user, SANITISE IT FIRST.&nbsp; This cannot be stressed enough - if I had a dollar for each time I saw code where form input was taken and directly used (by myself as well, I've been stupid too) I'd probably own PHP.&nbsp; While using data from a form in a URL wrapper is asking for trouble, you can greatly minimise the trouble by making sure your inputs are sane and not likely to provide an opening for the LulzSec of the world to cause havoc.</span>
</code></div>
  </div>
 </div>
 <a name="108703"></a>
 <div class="note">
  <strong class='user'>Toby</strong>
  <a href="#108703" class="date">18-May-2012 10:12</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Dangerous stuff. Had php injection attacks like:<br />
<br />
?-dallow_url_include%253don+-dauto_prepend_file%253dphp://input<br />
<br />
due to this</span>
</code></div>
  </div>
 </div>
 <a name="108529"></a>
 <div class="note">
  <strong class='user'>Anonymous</strong>
  <a href="#108529" class="date">04-May-2012 07:00</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
For php://filter the /resource=foo part must come last. And foo needs no escaping at all.<br />
php://filter/resource=foo/read=somefilter would try to open a file 'foo/read=somefilter' while php://filter/read=somefilter/resource=foo will open file 'foo' with the somefilter filter applied.</span>
</code></div>
  </div>
 </div>
 <a name="107973"></a>
 <div class="note">
  <strong class='user'>Rakesh Verma [rakeshnsony at gmail dot com]</strong>
  <a href="#107973" class="date">19-Mar-2012 10:18</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
/**********************************/<br />
Example JSON Request:<br />
{<br />
&nbsp;&nbsp;&nbsp; "username" : "rakeshnsony",<br />
&nbsp;&nbsp;&nbsp; "password" : "abcdefg"<br />
}<br />
/**********************************/<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="comment">//To access json format data<br />
</span><span class="default">$requestBody </span><span class="keyword">= </span><span class="default">file_get_contents</span><span class="keyword">(</span><span class="string">'php://input'</span><span class="keyword">);<br />
</span><span class="default">$requestBody </span><span class="keyword">= </span><span class="default">json_decode</span><span class="keyword">(</span><span class="default">$requestBody</span><span class="keyword">);<br />
<br />
echo </span><span class="string">"username is: "</span><span class="keyword">.</span><span class="default">$requestBody</span><span class="keyword">-&gt;</span><span class="default">username</span><span class="keyword">;<br />
<br />
echo </span><span class="string">"&lt;br /&gt;&lt;br /&gt;"</span><span class="keyword">;<br />
<br />
echo </span><span class="string">"password is: "</span><span class="keyword">.</span><span class="default">$requestBody</span><span class="keyword">-&gt;</span><span class="default">password</span><span class="keyword">;<br />
</span>
</span>
</code></div>
  </div>
 </div>
 <a name="105029"></a>
 <div class="note">
  <strong class='user'>leonid at shagabutdinov dot com</strong>
  <a href="#105029" class="date">22-Jul-2011 09:45</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
For https for windows enable this extension:<br />
<br />
extension=php_openssl.dll</span>
</code></div>
  </div>
 </div>
 <a name="104122"></a>
 <div class="note">
  <strong class='user'>kwedeth at gmail dot com</strong>
  <a href="#104122" class="date">24-May-2011 06:04</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
When daisy-chaining wrappers, I've found that the stream context only applies to the outside wrapper. For example, the following code will not work:<br />
<br />
<span class="default">&lt;?php<br />
<br />
$options </span><span class="keyword">= array(</span><span class="string">'http'</span><span class="keyword">=&gt;array(</span><span class="string">'header'</span><span class="keyword">=&gt;</span><span class="string">"Accept-Encoding: gzip\r\n"</span><span class="keyword">));<br />
</span><span class="default">$context </span><span class="keyword">= </span><span class="default">stream_context_create</span><span class="keyword">(</span><span class="default">$options</span><span class="keyword">);<br />
<br />
</span><span class="default">$html </span><span class="keyword">= </span><span class="default">file_get_contents</span><span class="keyword">(</span><span class="string">'compress.zlib://<a href="http://example.com/resource.gz" rel="nofollow" target="_blank">http://example.com/resource.gz</a>'</span><span class="keyword">, </span><span class="default">0</span><span class="keyword">, </span><span class="default">$context</span><span class="keyword">);<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
The context in this case is useless for the compress.zlib:// wrapper but it does not get applied to <a href="http://" rel="nofollow" target="_blank">http://</a> and the header will not be sent.</span>
</code></div>
  </div>
 </div>
 <a name="102269"></a>
 <div class="note">
  <strong class='user'>sebastian dot krebs at kingcrunch dot de</strong>
  <a href="#102269" class="date">04-Feb-2011 04:49</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The stream php://temp/maxmemory:$limit stores the data in memory unless the limit is reached. Then it will write the whole content the a temporary file and frees the memory. I didnt found a way to get at least some of the data back to memory.</span>
</code></div>
  </div>
 </div>
 <a name="83220"></a>
 <div class="note">
  <strong class='user'>gjaman at gmail dot com</strong>
  <a href="#83220" class="date">15-May-2008 02:15</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
You can decompress (gzip) a input stream by combining wrappers:<br />
<br />
eg:&nbsp; $x = file_get_contents("compress.zlib://php://input"); <br />
<br />
I used this method to decompress a gzip stream that was pushed to my webserver</span>
</code></div>
  </div>
 </div>
 <a name="77169"></a>
 <div class="note">
  <strong class='user'>jerry at gii dot co dot jp</strong>
  <a href="#77169" class="date">17-Aug-2007 10:11</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Not only are STDIN, STDOUT, and STDERR only allowed for CLI programs, but they are not allowed for programs that are read from STDIN. That can confuse you if you try to type in a simple test program.</span>
</code></div>
  </div>
 </div>
 <a name="75751"></a>
 <div class="note">
  <strong class='user'>sander at medicore dot nl</strong>
  <a href="#75751" class="date">14-Jun-2007 04:25</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
to create a raw tcp listener system i use the following:<br />
<br />
xinetd daemon with config like:<br />
service test<br />
{<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; disable&nbsp; &nbsp; &nbsp; = no<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; type&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; = UNLISTED<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; socket_type&nbsp; = stream<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; protocol&nbsp; &nbsp;&nbsp; = tcp<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; bind&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; = 127.0.0.1<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; port&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; = 12345<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; wait&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; = no<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; user&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; = apache<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; group&nbsp; &nbsp; &nbsp; &nbsp; = apache<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; instances&nbsp; &nbsp; = 10<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; server&nbsp; &nbsp; &nbsp;&nbsp; = /usr/local/bin/php<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; server_args&nbsp; = -n [your php file here]<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; only_from&nbsp; &nbsp; = 127.0.0.1 #gotta love the security#<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; log_type&nbsp; &nbsp;&nbsp; = FILE /var/log/phperrors.log<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; log_on_success += DURATION<br />
}<br />
<br />
now use fgets(STDIN) to read the input. Creates connections pretty quick, works like a charm.Writing can be done using the STDOUT, or just echo. Be aware that you're completely bypassing the webserver and thus certain variables will not be available.</span>
</code></div>
  </div>
 </div>
 <a name="70739"></a>
 <div class="note">
  <strong class='user'>ben dot johansen at gmail dot com</strong>
  <a href="#70739" class="date">25-Oct-2006 02:57</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
followup:<br />
<br />
I found that if I added this line to the AJAX call, the values would show up in the $_POST<br />
<br />
xhttp.setRequestHeader('Content-Type',<br />
'application/x-www-form-urlencoded');</span>
</code></div>
  </div>
 </div>
 <a name="69277"></a>
 <div class="note">
  <strong class='user'>ben dot johansen at gmail dot com</strong>
  <a href="#69277" class="date">29-Aug-2006 11:02</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Example of how to use the php://input to get raw post data<br />
<br />
//read the raw data in<br />
$roughHTTPPOST = file_get_contents("php://input"); <br />
//parse it into vars<br />
parse_str($roughHTTPPOST);<br />
<br />
if you do readfile("php://input") you will get the length of the post data</span>
</code></div>
  </div>
 </div>
 <a name="69255"></a>
 <div class="note">
  <strong class='user'>ben dot johansen at gmail dot com</strong>
  <a href="#69255" class="date">29-Aug-2006 12:33</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
In trying to do AJAX with PHP and Javascript, I came upon an issue where the POST argument from the following javascript could not be read in via PHP 5 using the $_REQUEST or $_POST. I finally figured out how to read in the raw data using the php://input directive.<br />
&nbsp;&nbsp;&nbsp; <br />
Javascript code:<br />
=============<br />
&nbsp;&nbsp; &nbsp;&nbsp; //create request instance&nbsp; &nbsp; &nbsp; <br />
&nbsp;&nbsp; &nbsp;&nbsp; xhttp = new XMLHttpRequest();<br />
&nbsp;&nbsp; &nbsp;&nbsp; // set the event handler<br />
&nbsp;&nbsp; &nbsp;&nbsp; xhttp.onreadystatechange = serviceReturn;<br />
&nbsp;&nbsp; &nbsp;&nbsp; // prep the call, http method=POST, true=asynchronous call<br />
&nbsp;&nbsp; &nbsp;&nbsp; var Args = 'number='+NbrValue;<br />
&nbsp;&nbsp; &nbsp;&nbsp; xhttp.open("POST", "<a href="http://" rel="nofollow" target="_blank">http://</a><span class="default">&lt;?php </span><span class="keyword">echo </span><span class="default">$_SERVER</span><span class="keyword">[</span><span class="string">'SERVER_NAME'</span><span class="keyword">] </span><span class="default">?&gt;</span>/webservices/ws_service.php", true);<br />
&nbsp;&nbsp; &nbsp;&nbsp; // send the call with args<br />
&nbsp;&nbsp; &nbsp;&nbsp; xhttp.send(Args);<br />
<br />
PHP Code:<br />
&nbsp;&nbsp;&nbsp; //read the raw data in<br />
&nbsp;&nbsp;&nbsp; $roughHTTPPOST = file_get_contents("php://input"); <br />
&nbsp;&nbsp;&nbsp; //parse it into vars<br />
&nbsp;&nbsp;&nbsp; parse_str($roughHTTPPOST);</span>
</code></div>
  </div>
 </div>
 <a name="67950"></a>
 <div class="note">
  <strong class='user'>heitorsiller at uol dot com dot br</strong>
  <a href="#67950" class="date">07-Jul-2006 07:55</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
For reading a XML stream, this will work just fine:<br />
<span class="default">&lt;?php<br />
<br />
$arq </span><span class="keyword">= </span><span class="default">file_get_contents</span><span class="keyword">(</span><span class="string">'php://input'</span><span class="keyword">);<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
Then you can parse the XML like this:<br />
<br />
<span class="default">&lt;?php<br />
<br />
$xml </span><span class="keyword">= </span><span class="default">xml_parser_create</span><span class="keyword">();<br />
<br />
</span><span class="default">xml_parse_into_struct</span><span class="keyword">(</span><span class="default">$xml</span><span class="keyword">, </span><span class="default">$arq</span><span class="keyword">, </span><span class="default">$vs</span><span class="keyword">);<br />
<br />
</span><span class="default">xml_parser_free</span><span class="keyword">(</span><span class="default">$xml</span><span class="keyword">);<br />
<br />
</span><span class="default">$data </span><span class="keyword">= </span><span class="string">""</span><span class="keyword">;<br />
<br />
foreach(</span><span class="default">$vs </span><span class="keyword">as </span><span class="default">$v</span><span class="keyword">){<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if(</span><span class="default">$v</span><span class="keyword">[</span><span class="string">'level'</span><span class="keyword">] == </span><span class="default">3 </span><span class="keyword">&amp;&amp; </span><span class="default">$v</span><span class="keyword">[</span><span class="string">'type'</span><span class="keyword">] == </span><span class="string">'complete'</span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$data </span><span class="keyword">.= </span><span class="string">"\n"</span><span class="keyword">.</span><span class="default">$v</span><span class="keyword">[</span><span class="string">'tag'</span><span class="keyword">].</span><span class="string">" -&gt; "</span><span class="keyword">.</span><span class="default">$v</span><span class="keyword">[</span><span class="string">'value'</span><span class="keyword">];<br />
}<br />
<br />
echo </span><span class="default">$data</span><span class="keyword">;<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
PS.: This is particularly useful for receiving mobile originated (MO) SMS messages from cellular phone companies.</span>
</code></div>
  </div>
 </div>
 <a name="64343"></a>
 <div class="note">
  <strong class='user'>opedroso at NOSPAMswoptimizer dot com</strong>
  <a href="#64343" class="date">12-Apr-2006 11:07</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
php://input allows you to read raw POST data. It is a less memory intensive alternative to $HTTP_RAW_POST_DATA and does not need any special php.ini directives. <br />
<br />
Example use:<br />
<br />
$httprawpostdata = file_get_contents("php://input");<br />
<br />
When reading a base64 encoded stream using php://input, be aware that you do not need to decode it, it will automatically be done for you.</span>
</code></div>
  </div>
 </div>
 <a name="59151"></a>
 <div class="note">
  <strong class='user'>nyvsld at gmail dot com</strong>
  <a href="#59151" class="date">27-Nov-2005 10:28</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
php://stdin supports fseek() and fstat() function call, <br />
while php://input doesn't.</span>
</code></div>
  </div>
 </div>
 <a name="57142"></a>
 <div class="note">
  <strong class='user'>drewish at katherinehouse dot com</strong>
  <a href="#57142" class="date">24-Sep-2005 11:50</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Be aware that contrary to the way this makes it sound, under Apache, php://output and php://stdout don't point to the same place.<br />
<br />
<span class="default">&lt;?php<br />
$fo </span><span class="keyword">= </span><span class="default">fopen</span><span class="keyword">(</span><span class="string">'php://output'</span><span class="keyword">, </span><span class="string">'w'</span><span class="keyword">);<br />
</span><span class="default">$fs </span><span class="keyword">= </span><span class="default">fopen</span><span class="keyword">(</span><span class="string">'php://stdout'</span><span class="keyword">, </span><span class="string">'w'</span><span class="keyword">);<br />
<br />
</span><span class="default">fputs</span><span class="keyword">(</span><span class="default">$fo</span><span class="keyword">, </span><span class="string">"You can see this with the CLI and Apache.\n"</span><span class="keyword">);<br />
</span><span class="default">fputs</span><span class="keyword">(</span><span class="default">$fs</span><span class="keyword">, </span><span class="string">"This only shows up on the CLI...\n"</span><span class="keyword">);<br />
<br />
</span><span class="default">fclose</span><span class="keyword">(</span><span class="default">$fo</span><span class="keyword">);<br />
</span><span class="default">fclose</span><span class="keyword">(</span><span class="default">$fs</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span><br />
Using the CLI you'll see:<br />
&nbsp; You can see this with the CLI and Apache.<br />
&nbsp; This only shows up on the CLI...<br />
<br />
Using the Apache SAPI you'll see:<br />
&nbsp; You can see this with the CLI and Apache.</span>
</code></div>
  </div>
 </div>
 <a name="52272"></a>
 <div class="note">
  <strong class='user'>chris at free-source dot com</strong>
  <a href="#52272" class="date">26-Apr-2005 12:52</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you're looking for a unix based smb wrapper there isn't one built in,&nbsp; but I've had luck with <a href="http://www.zevils.com/cgi-bin/viewcvs.cgi/libsmbclient-php/" rel="nofollow" target="_blank">http://www.zevils.com/cgi-bin/viewcvs.cgi/libsmbclient-php/</a> (tarball link at the end).</span>
</code></div>
  </div>
 </div>
 <a name="45947"></a>
 <div class="note">
  <strong class='user'>nargy at yahoo dot com</strong>
  <a href="#45947" class="date">24-Sep-2004 03:16</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
When opening php://output in append mode you get an error, the way to do it:<br />
$fp=fopen("php://output","w");<br />
fwrite($fp,"Hello, world !&lt;BR&gt;\n");<br />
fclose($fp);</span>
</code></div>
  </div>
 </div>
 <a name="42722"></a>
 <div class="note">
  <strong class='user'>aidan at php dot net</strong>
  <a href="#42722" class="date">27-May-2004 03:34</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The contants:<br />
<br />
* STDIN<br />
* STDOUT<br />
* STDERR<br />
<br />
Were introduced in PHP 4.3.0 and are synomous with the fopen('php://stdx') result resource.</span>
</code></div>
  </div>
 </div>
 <a name="37842"></a>
 <div class="note">
  <strong class='user'>lupti at yahoo dot com</strong>
  <a href="#37842" class="date">29-Nov-2003 02:04</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I find using file_get_contents with php://input is very handy and efficient. Here is the code:<br />
<br />
$request = "";<br />
$request = file_get_contents("php://input");<br />
<br />
I don't need to declare the URL filr string as "r". It automatically handles open the file with read.<br />
<br />
I can then use this $request string to your XMLparser as data.</span>
</code></div>
  </div>
 </div>
 <a name="35009"></a>
 <div class="note">
  <strong class='user'>sam at bigwig dot net</strong>
  <a href="#35009" class="date">15-Aug-2003 08:02</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
[ Editor's Note: There is a way to know.&nbsp; All response headers (from both the final responding server and intermediate redirecters) can be found in $http_response_header or stream_get_meta_data() as described above. ]<br />
<br />
If you open an HTTP url and the server issues a Location style redirect, the redirected contents will be read but you can't find out that this has happened.<br />
<br />
So if you then parse the returned html and try and rationalise relative URLs you could get it wrong.</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=wrappers&amp;redirect=http://www.php.net/manual/en/wrappers.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=wrappers&amp;redirect=http://www.php.net/manual/en/wrappers.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/wrappers.php">show source</a> |
 <a href="/credits.php">credits</a> |
 <a href="/stats/">stats</a> |
 <a href="/sitemap.php">sitemap</a> |
 <a href="/contact.php">contact</a> |
 <a href="/contact.php#ads">advertising</a> |
 <a href="/mirrors.php">mirror sites</a>
</div>

<div id="pagefooter">
 <div id="copyright">
  <a href="/copyright.php">Copyright &copy; 2001-2012 The PHP Group</a><br />
  All rights reserved.
 </div>

 <div id="thismirror">
  <a href="/mirror.php">This mirror</a> generously provided by:
  <a href="@w{TDAY9QJ9}">Yahoo! Inc.</a><br />
  Last updated: Tue Jul 31 20:41:05 2012 UTC
 </div>
</div>
<!--[if IE 6]>
<script type="text/javascript">
    /*Load jQuery if not already loaded*/ if(typeof jQuery == 'undefined'){ document.write("<script type=\"text/javascript\"   src=\"@w{8JFFCNVW}"></"+"script>"); var __noconflict = true; }
    var IE6UPDATE_OPTIONS = {
        icons_path: "/ie6update/images/"
    }
</script>
<script type="text/javascript" src="/ie6update/ie6update.js"></script>
<![endif]-->
</body>
</html>