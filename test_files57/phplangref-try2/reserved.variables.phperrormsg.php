<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: $php_errormsg - Manual</title>
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
 <link rel="index" href="reserved.variables.php" />
 <link rel="prev" href="reserved.variables.cookies.php" />
 <link rel="next" href="reserved.variables.httprawpostdata.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/reserved.variables.phperrormsg" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/reserved.variables.phperrormsg.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="@w{CK64ENZW}" />
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
 <li class="header up"><a href="reserved.variables.php">Predefined Variables</a></li>
 <li><a href="language.variables.superglobals.php">Superglobals</a></li>
 <li><a href="reserved.variables.globals.php">$GLOBALS</a></li>
 <li><a href="reserved.variables.server.php">$_SERVER</a></li>
 <li><a href="reserved.variables.get.php">$_GET</a></li>
 <li><a href="reserved.variables.post.php">$_POST</a></li>
 <li><a href="reserved.variables.files.php">$_FILES</a></li>
 <li><a href="reserved.variables.request.php">$_REQUEST</a></li>
 <li><a href="reserved.variables.session.php">$_SESSION</a></li>
 <li><a href="reserved.variables.environment.php">$_ENV</a></li>
 <li><a href="reserved.variables.cookies.php">$_COOKIE</a></li>
 <li class="active"><a href="reserved.variables.phperrormsg.php">$php_errormsg</a></li>
 <li><a href="reserved.variables.httprawpostdata.php">$HTTP_RAW_POST_DATA</a></li>
 <li><a href="reserved.variables.httpresponseheader.php">$http_response_header</a></li>
 <li><a href="reserved.variables.argc.php">$argc</a></li>
 <li><a href="reserved.variables.argv.php">$argv</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="reserved.variables.httprawpostdata.php">$HTTP_RAW_POST_DATA<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="reserved.variables.cookies.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />$_COOKIE</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/reserved.variables.phperrormsg.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/reserved.variables.phperrormsg.php">Brazilian Portuguese</option>
    <option value="zh/reserved.variables.phperrormsg.php">Chinese (Simplified)</option>
    <option value="fr/reserved.variables.phperrormsg.php">French</option>
    <option value="de/reserved.variables.phperrormsg.php">German</option>
    <option value="ja/reserved.variables.phperrormsg.php">Japanese</option>
    <option value="pl/reserved.variables.phperrormsg.php">Polish</option>
    <option value="ro/reserved.variables.phperrormsg.php">Romanian</option>
    <option value="ru/reserved.variables.phperrormsg.php">Russian</option>
    <option value="fa/reserved.variables.phperrormsg.php">Persian</option>
    <option value="es/reserved.variables.phperrormsg.php">Spanish</option>
    <option value="tr/reserved.variables.phperrormsg.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="reserved.variables.phperrormsg" class="refentry">
 <div class="refnamediv">
  <h1 class="refname">$php_errormsg</h1>
  <p class="verinfo">(PHP 4, PHP 5)</p><p class="refpurpose"><span class="refname">$php_errormsg</span> &mdash; <span class="dc-title">The previous error message</span></p>

 </div>
 
 <div class="refsect1 description" id="refsect1-reserved.variables.phperrormsg-description">
  <h3 class="title">Description</h3>
  <p class="para">
   <var class="varname"><var class="varname">$php_errormsg</var></var> is a variable containing the
   text of the last error message generated by PHP. This variable
   will only be available within the scope in which the error
   occurred, and only if the <a href="errorfunc.configuration.php#ini.track-errors" class="link">track_errors</a> configuration
   option is turned on (it defaults to off).
  </p>
  <blockquote class="note"><p><strong class="note">Note</strong>: 
   <span class="simpara">
    This variable is only available when <em>track_errors</em> is
    enabled in <var class="filename">php.ini</var>.
   </span>
  </p></blockquote>
  <div class="warning"><strong class="warning">Warning</strong>
   <p class="simpara">
    If a user defined error handler ( <span class="function"><a href="function.set-error-handler.php" class="function">set_error_handler()</a></span>)
    is set <var class="varname"><var class="varname">$php_errormsg</var></var> is only set if the error handler
    returns <strong><code>FALSE</code></strong>
   </p>
  </div>
 </div>

 
 <div class="refsect1 examples" id="refsect1-reserved.variables.phperrormsg-examples">
  <h3 class="title">Examples</h3>
  <p class="para">
   <div class="example" id="variable.phperrormsg.basic">
    <p><strong>Example #1 <var class="varname"><var class="varname">$php_errormsg</var></var> example</strong></p>
    <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">@</span><span style="color: #0000BB">strpos</span><span style="color: #007700">();<br />echo&nbsp;</span><span style="color: #0000BB">$php_errormsg</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

    <div class="example-contents"><p>The above example will output
something similar to:</p></div>
    <div class="example-contents screen">
<div class="cdata"><pre>
Wrong parameter count for strpos()
</pre></div>
    </div>
   </div>
  </p>
 </div>

 
</div><br /><br />
<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=reserved.variables.phperrormsg&amp;redirect=@w{CK64ENZW}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=reserved.variables.phperrormsg&amp;redirect=@w{CK64ENZW}">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>$php_errormsg</strong>
 </div><div id="allnotes">
 <a name="104728"></a>
 <div class="note">
  <strong class='user'>josh at karmabunny dot com dot au</strong>
  <a href="#104728" class="date">04-Jul-2011 12:41</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The track_errors parameter is PHP_INI_ALL, so you can use code like this:<br />
<br />
<span class="default">&lt;?php<br />
ini_set</span><span class="keyword">(</span><span class="string">'track_errors'</span><span class="keyword">, </span><span class="default">1</span><span class="keyword">);<br />
<br />
</span><span class="default">$result </span><span class="keyword">= @</span><span class="default">do_risky_thing</span><span class="keyword">();<br />
if (! </span><span class="default">$result</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="string">'&lt;p&gt;Error' </span><span class="keyword">. </span><span class="default">htmlspecialchars</span><span class="keyword">(</span><span class="default">$php_errormsg</span><span class="keyword">) . </span><span class="string">'&lt;/p&gt;'</span><span class="keyword">;<br />
}<br />
<br />
</span><span class="default">ini_set</span><span class="keyword">(</span><span class="string">'track_errors'</span><span class="keyword">, </span><span class="default">0</span><span class="keyword">);<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="88300"></a>
 <div class="note">
  <strong class='user'>ryan kulla</strong>
  <a href="#88300" class="date">19-Jan-2009 11:32</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Note: This variable doesn't seem to get populated if you're running Xdebug.</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=reserved.variables.phperrormsg&amp;redirect=@w{CK64ENZW}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=reserved.variables.phperrormsg&amp;redirect=@w{CK64ENZW}">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/reserved.variables.phperrormsg.php">show source</a> |
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