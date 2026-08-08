<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: $GLOBALS - Manual</title>
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
 <link rel="prev" href="language.variables.superglobals.php" />
 <link rel="next" href="reserved.variables.server.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/reserved.variables.globals" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/reserved.variables.globals.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="@w{545FVG9F}" />
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
 <li class="active"><a href="reserved.variables.globals.php">$GLOBALS</a></li>
 <li><a href="reserved.variables.server.php">$_SERVER</a></li>
 <li><a href="reserved.variables.get.php">$_GET</a></li>
 <li><a href="reserved.variables.post.php">$_POST</a></li>
 <li><a href="reserved.variables.files.php">$_FILES</a></li>
 <li><a href="reserved.variables.request.php">$_REQUEST</a></li>
 <li><a href="reserved.variables.session.php">$_SESSION</a></li>
 <li><a href="reserved.variables.environment.php">$_ENV</a></li>
 <li><a href="reserved.variables.cookies.php">$_COOKIE</a></li>
 <li><a href="reserved.variables.phperrormsg.php">$php_errormsg</a></li>
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
  <a href="reserved.variables.server.php">$_SERVER<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.variables.superglobals.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Superglobals</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/reserved.variables.globals.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/reserved.variables.globals.php">Brazilian Portuguese</option>
    <option value="zh/reserved.variables.globals.php">Chinese (Simplified)</option>
    <option value="fr/reserved.variables.globals.php">French</option>
    <option value="de/reserved.variables.globals.php">German</option>
    <option value="ja/reserved.variables.globals.php">Japanese</option>
    <option value="pl/reserved.variables.globals.php">Polish</option>
    <option value="ro/reserved.variables.globals.php">Romanian</option>
    <option value="ru/reserved.variables.globals.php">Russian</option>
    <option value="fa/reserved.variables.globals.php">Persian</option>
    <option value="es/reserved.variables.globals.php">Spanish</option>
    <option value="tr/reserved.variables.globals.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="reserved.variables.globals" class="refentry">
 <div class="refnamediv">
  <h1 class="refname">$GLOBALS</h1>
  <p class="verinfo">(PHP 4, PHP 5)</p><p class="refpurpose"><span class="refname">$GLOBALS</span> &mdash; <span class="dc-title">References all variables available in global scope</span></p>

 </div>
 
 <div class="refsect1 description" id="refsect1-reserved.variables.globals-description">
  <h3 class="title">Description</h3>
  <p class="para">
   An associative <span class="type"><a href="language.types.array.php" class="type array">array</a></span> containing references to all variables which
   are currently defined in the global scope of the script. The
   variable names are the keys of the array.
  </p>
 </div>

 
 <div class="refsect1 examples" id="refsect1-reserved.variables.globals-examples">
  <h3 class="title">Examples</h3>
  <p class="para">
   <div class="example" id="variable.globals.basic">
    <p><strong>Example #1 <var class="varname"><var class="varname">$GLOBALS</var></var> example</strong></p>
    <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">function&nbsp;</span><span style="color: #0000BB">test</span><span style="color: #007700">()&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$foo&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">"local&nbsp;variable"</span><span style="color: #007700">;<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">'$foo&nbsp;in&nbsp;global&nbsp;scope:&nbsp;'&nbsp;</span><span style="color: #007700">.&nbsp;</span><span style="color: #0000BB">$GLOBALS</span><span style="color: #007700">[</span><span style="color: #DD0000">"foo"</span><span style="color: #007700">]&nbsp;.&nbsp;</span><span style="color: #DD0000">"\n"</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">'$foo&nbsp;in&nbsp;current&nbsp;scope:&nbsp;'&nbsp;</span><span style="color: #007700">.&nbsp;</span><span style="color: #0000BB">$foo&nbsp;</span><span style="color: #007700">.&nbsp;</span><span style="color: #DD0000">"\n"</span><span style="color: #007700">;<br />}<br /><br /></span><span style="color: #0000BB">$foo&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">"Example&nbsp;content"</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">test</span><span style="color: #007700">();<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

    <div class="example-contents"><p>The above example will output
something similar to:</p></div>
    <div class="example-contents screen">
<div class="cdata"><pre>
$foo in global scope: Example content
$foo in current scope: local variable
</pre></div>
    </div>
   </div>
  </p>
 </div>

 
 <div class="refsect1 notes" id="refsect1-reserved.variables.globals-notes">
  <h3 class="title">Notes</h3>
  <blockquote class="note"><p><strong class="note">Note</strong>: <p class="para">This is a &#039;superglobal&#039;, or
automatic global, variable. This simply means that it is available in
all scopes throughout a script. There is no need to do
<strong class="command">global $variable;</strong> to access it within functions or methods.
</p></p></blockquote>
  <blockquote class="note"><p><strong class="note">Note</strong>: 
   <strong>Variable availability</strong><br />
   <p class="para">
    Unlike all of the other <a href="language.variables.superglobals.php" class="link">superglobals</a>,
    <var class="varname"><var class="varname">$GLOBALS</var></var> has essentially always been available in PHP.
   </p>
  </p></blockquote>
 </div>

 
</div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="reserved.variables.server.php">$_SERVER<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.variables.superglobals.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Superglobals</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/reserved.variables.globals.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=reserved.variables.globals&amp;redirect=@w{545FVG9F}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=reserved.variables.globals&amp;redirect=@w{545FVG9F}">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>$GLOBALS</strong>
 </div><div id="allnotes">
 <a name="108640"></a>
 <div class="note">
  <strong class='user'>Gratcy</strong>
  <a href="#108640" class="date">13-May-2012 07:03</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
this is technique that i always did for configuration file..<br />
<br />
<span class="default">&lt;?php<br />
$conf</span><span class="keyword">[</span><span class="string">'conf'</span><span class="keyword">][</span><span class="string">'foo'</span><span class="keyword">] = </span><span class="string">'this is foo'</span><span class="keyword">;<br />
</span><span class="default">$conf</span><span class="keyword">[</span><span class="string">'conf'</span><span class="keyword">][</span><span class="string">'bar'</span><span class="keyword">] = </span><span class="string">'this is bar'</span><span class="keyword">;<br />
<br />
function </span><span class="default">foobar</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; global </span><span class="default">$conf</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$conf</span><span class="keyword">);<br />
}<br />
<br />
</span><span class="default">foobar</span><span class="keyword">();<br />
<br />
</span><span class="comment">/*<br />
result is..<br />
<br />
array<br />
&nbsp; 'conf' =&gt; <br />
&nbsp;&nbsp;&nbsp; array<br />
&nbsp;&nbsp; &nbsp;&nbsp; 'foo' =&gt; string 'this is foo' (length=11)<br />
&nbsp;&nbsp; &nbsp;&nbsp; 'bar' =&gt; string 'this is bar' (length=11)<br />
<br />
*/<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="104666"></a>
 <div class="note">
  <strong class='user'>therandshow at gmail dot com</strong>
  <a href="#104666" class="date">29-Jun-2011 06:32</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
As of PHP 5.4 $GLOBALS is now initialized just-in-time. This means there now is an advantage to not use the $GLOBALS variable as you can avoid the overhead of initializing it. How much of an advantage that is I'm not sure, but I've never liked $GLOBALS much anyways.</span>
</code></div>
  </div>
 </div>
 <a name="92545"></a>
 <div class="note">
  <strong class='user'>williams at 3cisd dot com</strong>
  <a href="#92545" class="date">28-Jul-2009 03:53</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Better yet, use print_r.&nbsp; While var_dump does detect the recursion that var_export fails on, it seems to recurse one level first for my setup.&nbsp; So var_dump ends up printing all globals twice, but print_r prints them only once since it detects the recursion right away.&nbsp; Serialize seems to not detect the recursion at all either, similar to var_export.</span>
</code></div>
  </div>
 </div>
 <a name="85092"></a>
 <div class="note">
  <strong class='user'>David</strong>
  <a href="#85092" class="date">14-Aug-2008 05:47</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Though you can use var_dump to output the value of $GLOBALS.</span>
</code></div>
  </div>
 </div>
 <a name="85048"></a>
 <div class="note">
  <strong class='user'>ravenswd at yahoo dot com</strong>
  <a href="#85048" class="date">12-Aug-2008 01:02</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Keep in mind that $GLOBALS is, itself, a global variable. So code like this won't work:<br />
<br />
<span class="default">&lt;?php<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">print </span><span class="string">'$GLOBALS = ' </span><span class="keyword">. </span><span class="default">var_export</span><span class="keyword">(</span><span class="default">$GLOBALS</span><span class="keyword">, </span><span class="default">true</span><span class="keyword">) . </span><span class="string">"\n"</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
This results in the error message: "Nesting level too deep - recursive dependency?"</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=reserved.variables.globals&amp;redirect=@w{545FVG9F}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=reserved.variables.globals&amp;redirect=@w{545FVG9F}">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/reserved.variables.globals.php">show source</a> |
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