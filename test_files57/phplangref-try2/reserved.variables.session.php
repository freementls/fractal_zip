<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: $_SESSION - Manual</title>
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
 <link rel="prev" href="reserved.variables.request.php" />
 <link rel="next" href="reserved.variables.environment.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/reserved.variables.session" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/reserved.variables.session.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="@w{7GCWEJ3T}" />
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
 <li class="active"><a href="reserved.variables.session.php">$_SESSION</a></li>
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
  <a href="reserved.variables.environment.php">$_ENV<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="reserved.variables.request.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />$_REQUEST</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/reserved.variables.session.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/reserved.variables.session.php">Brazilian Portuguese</option>
    <option value="zh/reserved.variables.session.php">Chinese (Simplified)</option>
    <option value="fr/reserved.variables.session.php">French</option>
    <option value="de/reserved.variables.session.php">German</option>
    <option value="ja/reserved.variables.session.php">Japanese</option>
    <option value="pl/reserved.variables.session.php">Polish</option>
    <option value="ro/reserved.variables.session.php">Romanian</option>
    <option value="ru/reserved.variables.session.php">Russian</option>
    <option value="fa/reserved.variables.session.php">Persian</option>
    <option value="es/reserved.variables.session.php">Spanish</option>
    <option value="tr/reserved.variables.session.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="reserved.variables.session" class="refentry">
 <div class="refnamediv">
  <h1 class="refname">$_SESSION</h1>
  <h1 class="refname">$HTTP_SESSION_VARS [deprecated]</h1>
  <p class="verinfo">(PHP 4 &gt;= 4.1.0, PHP 5)</p><p class="refpurpose"><span class="refname">$_SESSION</span> -- <span class="refname">$HTTP_SESSION_VARS [deprecated]</span> &mdash; <span class="dc-title">Session variables</span></p>

 </div>
 
 <div class="refsect1 description" id="refsect1-reserved.variables.session-description">
  <h3 class="title">Description</h3>
  <p class="para">
   An associative array containing session variables available to
   the current script. See the <a href="ref.session.php" class="link">Session
   functions</a> documentation for more information on how this
   is used.
  </p>

  <p class="simpara">
   <var class="varname"><var class="varname">$HTTP_SESSION_VARS</var></var> contains the same initial
   information, but is not a <a href="language.variables.superglobals.php" class="link">superglobal</a>.
   (Note that <var class="varname"><var class="varname">$HTTP_SESSION_VARS</var></var> and <var class="varname"><var class="varname">$_SESSION</var></var>
   are different variables and that PHP handles them as such)
  </p>
 </div>

 
 <div class="refsect1 changelog" id="refsect1-reserved.variables.session-changelog">
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
        Introduced <var class="varname"><var class="varname">$_SESSION</var></var> that deprecated
        <var class="varname"><var class="varname">$HTTP_SESSION_VARS</var></var>.
       </td>
      </tr>

     </tbody>
    
   </table>

  </p>
 </div>

 
 <div class="refsect1 notes" id="refsect1-reserved.variables.session-notes">
  <h3 class="title">Notes</h3>
  <blockquote class="note"><p><strong class="note">Note</strong>: <p class="para">This is a &#039;superglobal&#039;, or
automatic global, variable. This simply means that it is available in
all scopes throughout a script. There is no need to do
<strong class="command">global $variable;</strong> to access it within functions or methods.
</p></p></blockquote>
 </div>

 
 <div class="refsect1 seealso" id="refsect1-reserved.variables.session-seealso">
  <h3 class="title">See Also</h3>
  <p class="para">
   <ul class="simplelist">
    <li class="member"> <span class="function"><a href="function.session-start.php" class="function" rel="rdfs-seeAlso">session_start()</a> - Start new or resume existing session</span></li>
   </ul>
  </p>
 </div>


</div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="reserved.variables.environment.php">$_ENV<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="reserved.variables.request.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />$_REQUEST</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/reserved.variables.session.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=reserved.variables.session&amp;redirect=@w{7GCWEJ3T}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=reserved.variables.session&amp;redirect=@w{7GCWEJ3T}">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>$_SESSION</strong>
 </div><div id="allnotes">
 <a name="102295"></a>
 <div class="note">
  <strong class='user'>pike-php at kw dot nl</strong>
  <a href="#102295" class="date">07-Feb-2011 06:00</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
When accidently assigning a unset variable to $_SESSION, like <br />
<br />
&nbsp;&nbsp; $_SESSION['foo'] = $bar <br />
<br />
while $bar was not defined, I got the following error message:<br />
<br />
"Warning: Unknown(): Your script possibly relies on a session side-effect which existed until PHP 4.2.3. Please be advised that the session extension does not consider global variables as a source of data, unless register_globals is enabled. "<br />
<br />
The errormessage was quite unrelated and got me off-track. The real error was, $bar was not defined.</span>
</code></div>
  </div>
 </div>
 <a name="94676"></a>
 <div class="note">
  <strong class='user'>Dave</strong>
  <a href="#94676" class="date">17-Nov-2009 02:05</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you deploy php code and cannot control whether register_globals is off, place this snippet in your code to prevent session injections:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">if (isset(</span><span class="default">$_REQUEST</span><span class="keyword">[</span><span class="string">'_SESSION'</span><span class="keyword">])) die(</span><span class="string">"Get lost Muppet!"</span><span class="keyword">);<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="92011"></a>
 <div class="note">
  <strong class='user'>charlese at cvs dot com dot au</strong>
  <a href="#92011" class="date">04-Jul-2009 06:47</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I was having troubles with session variables working in some environments and being seriously flaky in others. I was using $_SESSION as an array. It works properly when I used $_SESSION as pointers to arrays. As an example the following code works in some environments and not others.<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="comment">//Trouble if I treate $form_convert and $_SESSION['form_convert'] as unrelated items<br />
</span><span class="default">$form_convert</span><span class="keyword">=array();<br />
if (isset(</span><span class="default">$_SESSION</span><span class="keyword">[</span><span class="string">'form_convert'</span><span class="keyword">])){<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$form_convert</span><span class="keyword">=</span><span class="default">$_SESSION</span><span class="keyword">[</span><span class="string">'form_convert'</span><span class="keyword">];<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
</span><span class="default">?&gt;<br />
</span>The following works well. <br />
<span class="default">&lt;?php<br />
</span><span class="keyword">if (isset(</span><span class="default">$_SESSION</span><span class="keyword">[</span><span class="string">'form_convert'</span><span class="keyword">])){<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$form_convert </span><span class="keyword">= </span><span class="default">$_SESSION</span><span class="keyword">[</span><span class="string">'form_convert'</span><span class="keyword">];<br />
}else{<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$form_convert </span><span class="keyword">= array();<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$_SESSION</span><span class="keyword">[</span><span class="string">'form_convert'</span><span class="keyword">]=</span><span class="default">$form_convert</span><span class="keyword">;<br />
}<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="85448"></a>
 <div class="note">
  <strong class='user'>bohwaz</strong>
  <a href="#85448" class="date">31-Aug-2008 02:43</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Please note that if you have register_globals to On, global variables associated to $_SESSION variables are references, so this may lead to some weird situations.<br />
<br />
<span class="default">&lt;?php<br />
<br />
session_start</span><span class="keyword">();<br />
<br />
</span><span class="default">$_SESSION</span><span class="keyword">[</span><span class="string">'test'</span><span class="keyword">] = </span><span class="default">42</span><span class="keyword">;<br />
</span><span class="default">$test </span><span class="keyword">= </span><span class="default">43</span><span class="keyword">;<br />
echo </span><span class="default">$_SESSION</span><span class="keyword">[</span><span class="string">'test'</span><span class="keyword">];<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
Load the page, OK it displays 42, reload the page... it displays 43.<br />
<br />
The solution is to do this after each time you do a session_start() :<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">if (</span><span class="default">ini_get</span><span class="keyword">(</span><span class="string">'register_globals'</span><span class="keyword">))<br />
{<br />
&nbsp;&nbsp;&nbsp; foreach (</span><span class="default">$_SESSION </span><span class="keyword">as </span><span class="default">$key</span><span class="keyword">=&gt;</span><span class="default">$value</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if (isset(</span><span class="default">$GLOBALS</span><span class="keyword">[</span><span class="default">$key</span><span class="keyword">]))<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; unset(</span><span class="default">$GLOBALS</span><span class="keyword">[</span><span class="default">$key</span><span class="keyword">]);<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="85147"></a>
 <div class="note">
  <strong class='user'>Steve Clay</strong>
  <a href="#85147" class="date">17-Aug-2008 06:28</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Unlike a real PHP array, $_SESSION keys at the root level must be valid variable names.<br />
<br />
<span class="default">&lt;?php <br />
$_SESSION</span><span class="keyword">[</span><span class="default">1</span><span class="keyword">][</span><span class="default">1</span><span class="keyword">] = </span><span class="string">'cake'</span><span class="keyword">; </span><span class="comment">// fails<br />
<br />
</span><span class="default">$_SESSION</span><span class="keyword">[</span><span class="string">'v1'</span><span class="keyword">][</span><span class="default">1</span><span class="keyword">] = </span><span class="string">'cake'</span><span class="keyword">; </span><span class="comment">// works<br />
</span><span class="default">?&gt;<br />
</span><br />
I imagine this is an internal limitation having to do with the legacy function session_register(), where the registered global var must similarly have a valid name.</span>
</code></div>
  </div>
 </div>
 <a name="84852"></a>
 <div class="note">
  <strong class='user'>jherry at netcourrier dot com</strong>
  <a href="#84852" class="date">01-Aug-2008 04:16</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
You may have trouble if you use '|' in the key:<br />
<br />
$_SESSION["foo|bar"] = "fuzzy";<br />
<br />
This does not work for me. I think it's because the serialisation of session object is using this char so the server reset your session when it cannot read it.<br />
<br />
To make it work I replaced '|' by '_'.</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=reserved.variables.session&amp;redirect=@w{7GCWEJ3T}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=reserved.variables.session&amp;redirect=@w{7GCWEJ3T}">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/reserved.variables.session.php">show source</a> |
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