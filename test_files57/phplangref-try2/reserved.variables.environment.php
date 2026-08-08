<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: $_ENV - Manual</title>
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
 <link rel="prev" href="reserved.variables.session.php" />
 <link rel="next" href="reserved.variables.cookies.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/reserved.variables.environment" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/reserved.variables.environment.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="@w{45CEY672}" />
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
 <li class="active"><a href="reserved.variables.environment.php">$_ENV</a></li>
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
  <a href="reserved.variables.cookies.php">$_COOKIE<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="reserved.variables.session.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />$_SESSION</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/reserved.variables.environment.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/reserved.variables.environment.php">Brazilian Portuguese</option>
    <option value="zh/reserved.variables.environment.php">Chinese (Simplified)</option>
    <option value="fr/reserved.variables.environment.php">French</option>
    <option value="de/reserved.variables.environment.php">German</option>
    <option value="ja/reserved.variables.environment.php">Japanese</option>
    <option value="pl/reserved.variables.environment.php">Polish</option>
    <option value="ro/reserved.variables.environment.php">Romanian</option>
    <option value="ru/reserved.variables.environment.php">Russian</option>
    <option value="fa/reserved.variables.environment.php">Persian</option>
    <option value="es/reserved.variables.environment.php">Spanish</option>
    <option value="tr/reserved.variables.environment.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="reserved.variables.environment" class="refentry">
 <div class="refnamediv">
  <h1 class="refname">$_ENV</h1>
  <h1 class="refname">$HTTP_ENV_VARS [deprecated]</h1>
  <p class="verinfo">(PHP 4 &gt;= 4.1.0, PHP 5)</p><p class="refpurpose"><span class="refname">$_ENV</span> -- <span class="refname">$HTTP_ENV_VARS [deprecated]</span> &mdash; <span class="dc-title">Environment variables</span></p>

 </div>
 
 <div class="refsect1 description" id="refsect1-reserved.variables.environment-description">
  <h3 class="title">Description</h3>
  <p class="para">
   An associative <span class="type"><a href="language.types.array.php" class="type array">array</a></span> of variables passed to the current script
   via the environment method. 
  </p>

  <p class="simpara">
   These variables are imported into PHP&#039;s global namespace from the
   environment under which the PHP parser is running. Many are
   provided by the shell under which PHP is running and different
   systems are likely running different kinds of shells, a
   definitive list is impossible. Please see your shell&#039;s
   documentation for a list of defined environment variables.
  </p>

  <p class="simpara">
   Other environment variables include the CGI variables, placed
   there regardless of whether PHP is running as a server module or
   CGI processor.
  </p>

  <p class="simpara">
   <var class="varname"><var class="varname">$HTTP_ENV_VARS</var></var> contains the same initial
   information, but is not a <a href="language.variables.superglobals.php" class="link">superglobal</a>. 
   (Note that <var class="varname"><var class="varname">$HTTP_ENV_VARS</var></var> and <var class="varname"><var class="varname">$_ENV</var></var>
   are different variables and that PHP handles them as such)
  </p>

 </div>

 

 <div class="refsect1 changelog" id="refsect1-reserved.variables.environment-changelog">
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
        Introduced <var class="varname"><var class="varname">$_ENV</var></var> that deprecated
        <var class="varname"><var class="varname">$HTTP_ENV_VARS</var></var>.
       </td>
      </tr>

     </tbody>
    
   </table>

  </p>
 </div>

 
 <div class="refsect1 examples" id="refsect1-reserved.variables.environment-examples">
  <h3 class="title">Examples</h3>
  <p class="para">
   <div class="example" id="variable.env.basic">
    <p><strong>Example #1 <var class="varname"><var class="varname">$_ENV</var></var> example</strong></p>
    <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">echo&nbsp;</span><span style="color: #DD0000">'My&nbsp;username&nbsp;is&nbsp;'&nbsp;</span><span style="color: #007700">.</span><span style="color: #0000BB">$_ENV</span><span style="color: #007700">[</span><span style="color: #DD0000">"USER"</span><span style="color: #007700">]&nbsp;.&nbsp;</span><span style="color: #DD0000">'!'</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

    <div class="example-contents"><p>
     Assuming &quot;bjori&quot; executes this script
    </p></div>
    <div class="example-contents"><p>The above example will output
something similar to:</p></div>
    <div class="example-contents screen">
<div class="cdata"><pre>
My username is bjori!
</pre></div>
    </div>
   </div>
  </p>
 </div>

 
 <div class="refsect1 notes" id="refsect1-reserved.variables.environment-notes">
  <h3 class="title">Notes</h3>
  <blockquote class="note"><p><strong class="note">Note</strong>: <p class="para">This is a &#039;superglobal&#039;, or
automatic global, variable. This simply means that it is available in
all scopes throughout a script. There is no need to do
<strong class="command">global $variable;</strong> to access it within functions or methods.
</p></p></blockquote>
 </div>


 <div class="refsect1 seealso" id="refsect1-reserved.variables.environment-seealso">
  <h3 class="title">See Also</h3>
  <p class="para">
   <ul class="simplelist">
    <li class="member"> <span class="function"><a href="function.getenv.php" class="function" rel="rdfs-seeAlso">getenv()</a> - Gets the value of an environment variable</span></li>
    <li class="member"><a href="book.filter.php" class="link">The filter extension</a></li>
   </ul>
  </p>
 </div>


</div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="reserved.variables.cookies.php">$_COOKIE<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="reserved.variables.session.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />$_SESSION</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/reserved.variables.environment.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=reserved.variables.environment&amp;redirect=@w{45CEY672}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=reserved.variables.environment&amp;redirect=@w{45CEY672}">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>$_ENV</strong>
 </div><div id="allnotes">
 <a name="107413"></a>
 <div class="note">
  <strong class='user'>david at davidfavor dot com</strong>
  <a href="#107413" class="date">07-Feb-2012 02:07</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Comments for this page seem to indicate getenv() returns environment variables in all cases.<br />
<br />
For getenv() to work, php.ini variables_order must contain 'E'.</span>
</code></div>
  </div>
 </div>
 <a name="99840"></a>
 <div class="note">
  <strong class='user'>anonymous</strong>
  <a href="#99840" class="date">09-Sep-2010 10:36</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If $_ENV is empty because variables_order does not include it, it will be filled with values fetched by getenv().<br />
<br />
For example, when calling getenv("REMOTE_ADDR"), $_ENV['REMOTE_ADDR'] will be defined as well (if such an environment variable exists).</span>
</code></div>
  </div>
 </div>
 <a name="98113"></a>
 <div class="note">
  <strong class='user'>gabe-php at mudbugmedia dot com</strong>
  <a href="#98113" class="date">26-May-2010 09:11</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If your $_ENV array is mysteriously empty, but you still see the variables when calling getenv() or in your phpinfo(), check your <a href="http://us.php.net/manual/en/ini.core.php#ini.variables-order" rel="nofollow" target="_blank">http://us.php.net/manual/en/ini.core.php#ini.variables-order</a> ini setting to ensure it includes "E" in the string.</span>
</code></div>
  </div>
 </div>
 <a name="97105"></a>
 <div class="note">
  <strong class='user'>php at isnoop dot net</strong>
  <a href="#97105" class="date">01-Apr-2010 09:33</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you wish to define an environment variable in your Apache vhost file, use the directive SetEnv.<br />
<br />
SetEnv varname "variable value"<br />
<br />
It is important to note that this new variable will appear in $_SERVER, not $_ENV.</span>
</code></div>
  </div>
 </div>
 <a name="89725"></a>
 <div class="note">
  <strong class='user'>ewilde aht bsmdevelopment dawt com</strong>
  <a href="#89725" class="date">20-Mar-2009 05:48</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
When running a PHP program under the command line, the $_SERVER["SERVER_NAME"] variable does not contain the hostname. However, the following works for me under Unix/Linux and Windows:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">if (isset(</span><span class="default">$_ENV</span><span class="keyword">[</span><span class="string">"HOSTNAME"</span><span class="keyword">]))<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$MachineName </span><span class="keyword">= </span><span class="default">$_ENV</span><span class="keyword">[</span><span class="string">"HOSTNAME"</span><span class="keyword">];<br />
else if&nbsp; (isset(</span><span class="default">$_ENV</span><span class="keyword">[</span><span class="string">"COMPUTERNAME"</span><span class="keyword">]))<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$MachineName </span><span class="keyword">= </span><span class="default">$_ENV</span><span class="keyword">[</span><span class="string">"COMPUTERNAME"</span><span class="keyword">];<br />
else </span><span class="default">$MachineName </span><span class="keyword">= </span><span class="string">""</span><span class="keyword">;<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=reserved.variables.environment&amp;redirect=@w{45CEY672}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=reserved.variables.environment&amp;redirect=@w{45CEY672}">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/reserved.variables.environment.php">show source</a> |
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