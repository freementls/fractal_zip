<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Superglobals - Manual</title>
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
 <link rel="prev" href="reserved.variables.php" />
 <link rel="next" href="reserved.variables.globals.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/variables.superglobals" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/language.variables.superglobals.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="@w{2Z9CZADW}" />
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
 <li class="active"><a href="language.variables.superglobals.php">Superglobals</a></li>
 <li><a href="reserved.variables.globals.php">$GLOBALS</a></li>
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
  <a href="reserved.variables.globals.php">$GLOBALS<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="reserved.variables.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Predefined Variables</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.variables.superglobals.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/language.variables.superglobals.php">Brazilian Portuguese</option>
    <option value="zh/language.variables.superglobals.php">Chinese (Simplified)</option>
    <option value="fr/language.variables.superglobals.php">French</option>
    <option value="de/language.variables.superglobals.php">German</option>
    <option value="ja/language.variables.superglobals.php">Japanese</option>
    <option value="pl/language.variables.superglobals.php">Polish</option>
    <option value="ro/language.variables.superglobals.php">Romanian</option>
    <option value="ru/language.variables.superglobals.php">Russian</option>
    <option value="fa/language.variables.superglobals.php">Persian</option>
    <option value="es/language.variables.superglobals.php">Spanish</option>
    <option value="tr/language.variables.superglobals.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="language.variables.superglobals" class="refentry">
 <div class="refnamediv">
  <h1 class="refname">Superglobals</h1>
  <p class="refpurpose"><span class="refname">Superglobals</span> &mdash; <span class="dc-title">Superglobals are built-in variables that are always available in all scopes</span></p>

 </div>
 
 <div class="refsect1 description" id="refsect1-language.variables.superglobals-description">
  <h3 class="title">Description</h3>
  <p class="para">
   Several predefined variables in PHP are &quot;superglobals&quot;, which means they
   are available in all scopes throughout a script. There is no need to do
   <strong class="command">global $variable;</strong> to access them within functions
   or methods.
  </p>
  <p class="para">
   These superglobal variables are:
   <ul class="simplelist">
    <li class="member"><var class="varname"><var class="varname"><a href="reserved.variables.globals.php" class="classname">$GLOBALS</a></var></var></li>
    <li class="member"><var class="varname"><var class="varname"><a href="reserved.variables.server.php" class="classname">$_SERVER</a></var></var></li>
    <li class="member"><var class="varname"><var class="varname"><a href="reserved.variables.get.php" class="classname">$_GET</a></var></var></li>
    <li class="member"><var class="varname"><var class="varname"><a href="reserved.variables.post.php" class="classname">$_POST</a></var></var></li>
    <li class="member"><var class="varname"><var class="varname"><a href="reserved.variables.files.php" class="classname">$_FILES</a></var></var></li>
    <li class="member"><var class="varname"><var class="varname"><a href="reserved.variables.cookies.php" class="classname">$_COOKIE</a></var></var></li>
    <li class="member"><var class="varname"><var class="varname"><a href="reserved.variables.session.php" class="classname">$_SESSION</a></var></var></li>
    <li class="member"><var class="varname"><var class="varname"><a href="reserved.variables.request.php" class="classname">$_REQUEST</a></var></var></li>
    <li class="member"><var class="varname"><var class="varname"><a href="reserved.variables.environment.php" class="classname">$_ENV</a></var></var></li>
   </ul>
  </p>
 </div>


 <div class="refsect1 changelog" id="refsect1-language.variables.superglobals-changelog">
  <h3 class="title">Changelog</h3>
  <p class="para">
   <table class="doctable informaltable">
    
     <thead>
      <tr>
       <th>Version</th>
       <th>Description</th>
      </tr>

     </thead>

     <tbody class="tbody">
      <tr>
       <td>4.1.0</td>
       <td>
        Superglobals were introduced to PHP.
       </td>
      </tr>

     </tbody>
    
   </table>

  </p>
 </div>


 <div class="refsect1 notes" id="refsect1-language.variables.superglobals-notes">
  <h3 class="title">Notes</h3>
  <blockquote class="note"><p><strong class="note">Note</strong>: 
   <strong>Variable availability</strong><br />
   <p class="para">
    By default, all of the superglobals are available but there are
    directives that affect this availability. For further information, refer
    to the documentation for
    <a href="ini.core.php#ini.variables-order" class="link">variables_order</a>.
   </p>
  </p></blockquote>
  <blockquote class="note"><p><strong class="note">Note</strong>: 
   <strong>Dealing with register_globals</strong><br />
   <p class="para">
    If the deprecated <a href="ini.core.php#ini.register-globals" class="link">register_globals</a>
    directive is set to <em>on</em> then the variables within will
    also be made available in the global scope of the script. For example,
    <var class="varname"><var class="varname"><a href="reserved.variables.post.php" class="classname">$_POST['foo']</a></var></var> would also exist as <var class="varname"><var class="varname">$foo</var></var>.
   </p>
   <p class="para">
    For related information, see the FAQ titled 
    &quot;<a href="faq.using.php#faq.register-globals" class="link">How does register_globals affect me?</a>&quot;
   </p>
  </p></blockquote>
  <blockquote class="note"><p><strong class="note">Note</strong>: 
   <strong>Variable variables</strong><br />
   <p class="para">
    Superglobals cannot be used as 
    <a href="language.variables.variable.php" class="link">variable variables</a>
    inside functions or class methods.
   </p>
  </p></blockquote>
 </div>


 <div class="refsect1 seealso" id="refsect1-language.variables.superglobals-seealso">
  <h3 class="title">See Also</h3>
  <p class="para">
   <ul class="simplelist">
    <li class="member"><a href="language.variables.scope.php" class="link">variable scope</a></li>
    <li class="member">The <a href="ini.core.php#ini.variables-order" class="link">variables_order</a> directive</li>
    <li class="member"><a href="book.filter.php" class="link">The filter extension</a></li>
   </ul>
  </p>
 </div>


</div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="reserved.variables.globals.php">$GLOBALS<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="reserved.variables.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Predefined Variables</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.variables.superglobals.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=language.variables.superglobals&amp;redirect=@w{2Z9CZADW}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.variables.superglobals&amp;redirect=@w{2Z9CZADW}">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Superglobals</strong>
 </div><div id="allnotes">
 <a name="109418"></a>
 <div class="note">
  <strong class='user'>serpent at paradise dot net dot nz</strong>
  <a href="#109418" class="date">16-Jul-2012 01:27</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
You can go the other way as well i.e. <br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">test</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$GLOBALS</span><span class="keyword">[</span><span class="string">'foo'</span><span class="keyword">] = </span><span class="string">"Example content"</span><span class="keyword">;<br />
}<br />
<br />
</span><span class="default">test</span><span class="keyword">();<br />
echo </span><span class="string">"&lt;p&gt;$foo&lt;/p&gt;"</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
This doesn't appear to be affected by register_globals, I have it switched off.</span>
</code></div>
  </div>
 </div>
 <a name="86276"></a>
 <div class="note">
  <strong class='user'>lskatz at gmail dot com</strong>
  <a href="#86276" class="date">10-Oct-2008 08:51</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Tibor:<br />
It's not a good idea to use $_ENV unless you are specifying an environmental variable.&nbsp; This is probably a better example that I found on another page in php.net<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">test</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$foo </span><span class="keyword">= </span><span class="string">"local variable"</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="string">'$foo in global scope: ' </span><span class="keyword">. </span><span class="default">$GLOBALS</span><span class="keyword">[</span><span class="string">"foo"</span><span class="keyword">] . </span><span class="string">"\n"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="string">'$foo in current scope: ' </span><span class="keyword">. </span><span class="default">$foo </span><span class="keyword">. </span><span class="string">"\n"</span><span class="keyword">;<br />
}<br />
<br />
</span><span class="default">$foo </span><span class="keyword">= </span><span class="string">"Example content"</span><span class="keyword">;<br />
</span><span class="default">test</span><span class="keyword">();<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="86149"></a>
 <div class="note">
  <strong class='user'>Tibor &gt; rocketmachine.com</strong>
  <a href="#86149" class="date">05-Oct-2008 10:00</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
You can use superglobals to make your variables available everywhere without declaring them global.<br />
<br />
<span class="default">&lt;?php<br />
<br />
$_ENV</span><span class="keyword">[</span><span class="string">'mystring'</span><span class="keyword">] = </span><span class="string">'Hello World'</span><span class="keyword">;<br />
</span><span class="default">$_ENV</span><span class="keyword">[</span><span class="string">'myarray'</span><span class="keyword">] = array(</span><span class="string">'Alpha'</span><span class="keyword">, </span><span class="string">'Bravo'</span><span class="keyword">, </span><span class="string">'Charlie'</span><span class="keyword">);<br />
<br />
function </span><span class="default">test</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; print </span><span class="default">$_ENV</span><span class="keyword">[</span><span class="string">'mystring'</span><span class="keyword">];<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">print_r</span><span class="keyword">(</span><span class="default">$_ENV</span><span class="keyword">[</span><span class="string">'myarray'</span><span class="keyword">]);<br />
}<br />
<br />
</span><span class="default">test</span><span class="keyword">();<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=language.variables.superglobals&amp;redirect=@w{2Z9CZADW}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.variables.superglobals&amp;redirect=@w{2Z9CZADW}">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/language.variables.superglobals.php">show source</a> |
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