<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: require_once - Manual</title>
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
 <link rel="index" href="language.control-structures.php" />
 <link rel="prev" href="function.include.php" />
 <link rel="next" href="function.include-once.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/require-once" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/function.require-once.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/function.require-once.php" />
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
 <li class="header up"><a href="language.control-structures.php">Control Structures</a></li>
 <li><a href="control-structures.intro.php">Introduction</a></li>
 <li><a href="control-structures.if.php">if</a></li>
 <li><a href="control-structures.else.php">else</a></li>
 <li><a href="control-structures.elseif.php">elseif/else if</a></li>
 <li><a href="control-structures.alternative-syntax.php">Alternative syntax for control structures</a></li>
 <li><a href="control-structures.while.php">while</a></li>
 <li><a href="control-structures.do.while.php">do-while</a></li>
 <li><a href="control-structures.for.php">for</a></li>
 <li><a href="control-structures.foreach.php">foreach</a></li>
 <li><a href="control-structures.break.php">break</a></li>
 <li><a href="control-structures.continue.php">continue</a></li>
 <li><a href="control-structures.switch.php">switch</a></li>
 <li><a href="control-structures.declare.php">declare</a></li>
 <li><a href="function.return.php">return</a></li>
 <li><a href="function.require.php">require</a></li>
 <li><a href="function.include.php">include</a></li>
 <li class="active"><a href="function.require-once.php">require_<span class="w"> </span>once</a></li>
 <li><a href="function.include-once.php">include_<span class="w"> </span>once</a></li>
 <li><a href="control-structures.goto.php">goto</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="function.include-once.php">include_once<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="function.include.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />include</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/function.require-once.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/function.require-once.php">Brazilian Portuguese</option>
    <option value="zh/function.require-once.php">Chinese (Simplified)</option>
    <option value="fr/function.require-once.php">French</option>
    <option value="de/function.require-once.php">German</option>
    <option value="ja/function.require-once.php">Japanese</option>
    <option value="pl/function.require-once.php">Polish</option>
    <option value="ro/function.require-once.php">Romanian</option>
    <option value="ru/function.require-once.php">Russian</option>
    <option value="fa/function.require-once.php">Persian</option>
    <option value="es/function.require-once.php">Spanish</option>
    <option value="tr/function.require-once.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="function.require-once" class="sect1">
 <h2 class="title">require_once</h2>
 <p class="verinfo">(PHP 4, PHP 5)</p>
 <p class="para">
  The <em>require_once</em> statement is identical to
   <span class="function"><a href="function.require.php" class="function">require</a></span> except PHP will check if the file has
  already been included, and if so, not include (require) it again.
 </p>
 <p class="para">
  See the  <span class="function"><a href="function.include-once.php" class="function">include_once</a></span> documentation for information
  about the <em>_once</em> behaviour, and how it differs from
  its non <em>_once</em> siblings.
 </p>

</div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="function.include-once.php">include_once<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="function.include.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />include</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/function.require-once.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=function.require-once&amp;redirect=http://www.php.net/manual/en/function.require-once.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=function.require-once&amp;redirect=http://www.php.net/manual/en/function.require-once.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>require_once</strong>
 </div><div id="allnotes">
 <a name="104265"></a>
 <div class="note">
  <strong class='user'>bimal at sanjaal dot com</strong>
  <a href="#104265" class="date">04-Jun-2011 11:46</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If your code is running on multiple servers with different environments (locations from where your scripts run) the following idea may be useful to you:<br />
<br />
a. Do not give absolute path to include files on your server.<br />
b. Dynamically calculate the full path (absolute path)<br />
<br />
Hints:<br />
Use a combination of dirname(__FILE__) and subsequent calls to itself until you reach to the home of your '/index.php'. Then, attach this variable (that contains the path) to your included files.<br />
<br />
One of my typical example is:<br />
<br />
<span class="default">&lt;?php<br />
define</span><span class="keyword">(</span><span class="string">'__ROOT__'</span><span class="keyword">, </span><span class="default">dirname</span><span class="keyword">(</span><span class="default">dirname</span><span class="keyword">(</span><span class="default">__FILE__</span><span class="keyword">)));<br />
require_once(</span><span class="default">__ROOT__</span><span class="keyword">.</span><span class="string">'/config.php);<br />
?&gt;<br />
<br />
instead of:<br />
&lt;?php require_once('</span><span class="keyword">/var/</span><span class="default">www</span><span class="keyword">/</span><span class="default">public_html</span><span class="keyword">/</span><span class="default">config</span><span class="keyword">.</span><span class="default">php</span><span class="keyword">); </span><span class="default">?&gt;<br />
</span><br />
After this, if you copy paste your codes to another servers, it will still run, without requiring any further re-configurations.<br />
<br />
[EDIT BY danbrown AT php DOT net: Contains a typofix (missing ')') provided by 'JoeB' on 09-JUN-2011.]</span>
</code></div>
  </div>
 </div>
 <a name="103462"></a>
 <div class="note">
  <strong class='user'>spark at limao dot com dot br</strong>
  <a href="#103462" class="date">14-Apr-2011 07:31</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
if you use require_once on a file A pointing to file B, and require_once in the file B pointing to file A, in some configurations you will get stuck<br />
<br />
also wouldn't it be nice to manage that to prevent getting stuck AND use the good old Java import?<br />
<br />
<span class="default">&lt;?php<br />
&nbsp;&nbsp; &nbsp; &nbsp; </span><span class="keyword">function </span><span class="default">import</span><span class="keyword">(</span><span class="default">$path</span><span class="keyword">=</span><span class="string">""</span><span class="keyword">){<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; if(</span><span class="default">$path </span><span class="keyword">== </span><span class="string">""</span><span class="keyword">){ </span><span class="comment">//no parameter returns the file import info tree;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="default">$report </span><span class="keyword">= </span><span class="default">$_SESSION</span><span class="keyword">[</span><span class="string">'imports'</span><span class="keyword">];<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; foreach(</span><span class="default">$report </span><span class="keyword">as &amp;</span><span class="default">$item</span><span class="keyword">) </span><span class="default">$item </span><span class="keyword">= </span><span class="default">array_flip</span><span class="keyword">(</span><span class="default">$item</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; return </span><span class="default">$report</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; }<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="default">$current </span><span class="keyword">= </span><span class="default">str_replace</span><span class="keyword">(</span><span class="string">"\\"</span><span class="keyword">,</span><span class="string">"/"</span><span class="keyword">,</span><span class="default">getcwd</span><span class="keyword">()).</span><span class="string">"/"</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="default">$path </span><span class="keyword">= </span><span class="default">$current</span><span class="keyword">.</span><span class="default">str_replace</span><span class="keyword">(</span><span class="string">"."</span><span class="keyword">,</span><span class="string">"/"</span><span class="keyword">,</span><span class="default">$path</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; if(</span><span class="default">substr</span><span class="keyword">(</span><span class="default">$path</span><span class="keyword">,-</span><span class="default">1</span><span class="keyword">) != </span><span class="string">"*"</span><span class="keyword">) </span><span class="default">$path </span><span class="keyword">.= </span><span class="string">".class.php"</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="default">$imports </span><span class="keyword">= &amp;</span><span class="default">$_SESSION</span><span class="keyword">[</span><span class="string">'imports'</span><span class="keyword">];<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; if(!</span><span class="default">is_array</span><span class="keyword">(</span><span class="default">$imports</span><span class="keyword">)) </span><span class="default">$imports </span><span class="keyword">= array();<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="default">$control </span><span class="keyword">= &amp;</span><span class="default">$imports</span><span class="keyword">[</span><span class="default">$_SERVER</span><span class="keyword">[</span><span class="string">'SCRIPT_FILENAME'</span><span class="keyword">]];<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; if(!</span><span class="default">is_array</span><span class="keyword">(</span><span class="default">$control</span><span class="keyword">)) </span><span class="default">$control </span><span class="keyword">= array();<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; foreach(</span><span class="default">glob</span><span class="keyword">(</span><span class="default">$path</span><span class="keyword">) as </span><span class="default">$file</span><span class="keyword">){<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="default">$file </span><span class="keyword">= </span><span class="default">str_replace</span><span class="keyword">(</span><span class="default">$current</span><span class="keyword">,</span><span class="string">""</span><span class="keyword">,</span><span class="default">$file</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; if(</span><span class="default">is_dir</span><span class="keyword">(</span><span class="default">$file</span><span class="keyword">)) </span><span class="default">import</span><span class="keyword">(</span><span class="default">$file</span><span class="keyword">.</span><span class="string">".*"</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; if(</span><span class="default">substr</span><span class="keyword">(</span><span class="default">$file</span><span class="keyword">,-</span><span class="default">10</span><span class="keyword">) != </span><span class="string">".class.php"</span><span class="keyword">) continue;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; if(</span><span class="default">$control</span><span class="keyword">[</span><span class="default">$file</span><span class="keyword">]) continue;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="default">$control</span><span class="keyword">[</span><span class="default">$file</span><span class="keyword">] = </span><span class="default">count</span><span class="keyword">(</span><span class="default">$control</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; require_once(</span><span class="default">$file</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp; }<br />
</span><span class="default">?&gt;<br />
</span><br />
just remember to start the session and to enable the glob function<br />
<br />
now you can use<br />
<span class="default">&lt;?php<br />
&nbsp;&nbsp;&nbsp; import</span><span class="keyword">(</span><span class="string">"package.ClassName"</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">import</span><span class="keyword">(</span><span class="string">"another.package.*"</span><span class="keyword">); </span><span class="comment">//this will import everything in the folder<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="100266"></a>
 <div class="note">
  <strong class='user'>info at erpplaza dot com</strong>
  <a href="#100266" class="date">05-Oct-2010 12:10</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Include all files from a particular directory<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">foreach (</span><span class="default">glob</span><span class="keyword">(</span><span class="string">"classes/*.php"</span><span class="keyword">) as </span><span class="default">$filename</span><span class="keyword">)<br />
{<br />
&nbsp;&nbsp;&nbsp; include </span><span class="default">$filename</span><span class="keyword">;<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
ERPPlaza</span>
</code></div>
  </div>
 </div>
 <a name="98945"></a>
 <div class="note">
  <strong class='user'>jason semko at gmail dot com</strong>
  <a href="#98945" class="date">16-Jul-2010 01:33</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you are coding on localhost and require_once is not opening files due to 'relative paths' a simple solution is:<br />
<br />
<span class="default">&lt;?php <br />
<br />
&nbsp;&nbsp; &nbsp;&nbsp; </span><span class="keyword">require_once(</span><span class="default">dirname</span><span class="keyword">(</span><span class="default">__FILE__</span><span class="keyword">) . </span><span class="string">"/file.php"</span><span class="keyword">);<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
If you have file.php under the folder 'includes' (or anywhere for that matter), then folder 'public' AND folder 'public/admin' will be able to access all required files despite having different relative paths.</span>
</code></div>
  </div>
 </div>
 <a name="93403"></a>
 <div class="note">
  <strong class='user'>ivan[DOT_NO_SPAM]chepurnyi[AT]gmail</strong>
  <a href="#93403" class="date">08-Sep-2009 06:42</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Also if you have a large MVC framework, it make sense to compile&nbsp; structure "file/path/to/class.php" to something like this "file_path_to_class.php", it will speed up any type of php files includes, becouse php interpreter will not check FS stat data for directories "file", "file/path", "file/path/to", etc.</span>
</code></div>
  </div>
 </div>
 <a name="92566"></a>
 <div class="note">
  <strong class='user'>felix dot nensa at zeec dot biz</strong>
  <a href="#92566" class="date">29-Jul-2009 09:40</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
To include/require a whole folder of classes can be stripped down to a one-liner:<br />
<br />
<span class="default">&lt;?php<br />
array_walk</span><span class="keyword">(</span><span class="default">glob</span><span class="keyword">(</span><span class="string">'./lib/*.class.php'</span><span class="keyword">),</span><span class="default">create_function</span><span class="keyword">(</span><span class="string">'$v,$i'</span><span class="keyword">, </span><span class="string">'return require_once($v);'</span><span class="keyword">));<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="92340"></a>
 <div class="note">
  <strong class='user'>yahel at wanadoo dot fr</strong>
  <a href="#92340" class="date">20-Jul-2009 09:25</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you get "failed to open stream" although you are sure your include_path is correct, check your open_basedir setting.<br />
<br />
I spent 2 hours trying to figure out why although my inclusion path was good, php kept sending me the "failed to open" error.<br />
<br />
It was simply because my included directory was outside the scope of the open_basedir which blocks php from accessing file outside you root directory(usually).<br />
<br />
I think the error you send the "open_basedir restriction in effect" in this case.</span>
</code></div>
  </div>
 </div>
 <a name="90017"></a>
 <div class="note">
  <strong class='user'>Konstantin Rozinov (krozinov[at]gmail)</strong>
  <a href="#90017" class="date">01-Apr-2009 04:58</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
There's been a lot of discussion about the speed differences between using require_once() vs. require().<br />
I was curious myself, so I ran some tests to see what's faster: <br />
&nbsp;- require_once() vs require()<br />
&nbsp;- using relative_path vs absolute_path<br />
<br />
I also included results from strace for the number of stat() system calls.&nbsp; My results and conclusions below.<br />
<br />
METHODOLOGY:<br />
------------<br />
The script (test.php):<br />
<span class="default">&lt;?php<br />
&nbsp;$start_time </span><span class="keyword">= </span><span class="default">microtime</span><span class="keyword">(</span><span class="default">true</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;</span><span class="comment">/* <br />
&nbsp; * Uncomment one at a time and run test below.<br />
&nbsp; * sql_servers.inc only contains define() statements.<br />
&nbsp; */<br />
&nbsp;<br />
&nbsp;//require ('/www/includes/example.com/code/conf/sql_servers.inc');<br />
&nbsp;//require ('../../includes/example.com/code/conf/sql_servers.inc');<br />
&nbsp;//require_once ('/www/includes/example.com/code/conf/sql_servers.inc');<br />
&nbsp;//require_once ('../../includes/example.com/code/conf/sql_servers.inc');<br />
&nbsp;<br />
&nbsp;</span><span class="default">$end_time </span><span class="keyword">= </span><span class="default">microtime</span><span class="keyword">(</span><span class="default">true</span><span class="keyword">);<br />
&nbsp;<br />
&nbsp;</span><span class="default">$handle </span><span class="keyword">= </span><span class="default">fopen</span><span class="keyword">(</span><span class="string">"/tmp/results"</span><span class="keyword">, </span><span class="string">"ab+"</span><span class="keyword">);<br />
&nbsp;</span><span class="default">fwrite</span><span class="keyword">(</span><span class="default">$handle</span><span class="keyword">, (</span><span class="default">$end_time </span><span class="keyword">- </span><span class="default">$start_time</span><span class="keyword">) . </span><span class="string">"\n"</span><span class="keyword">);<br />
&nbsp;</span><span class="default">fclose</span><span class="keyword">(</span><span class="default">$handle</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span><br />
The test:<br />
&nbsp; I ran ab on the test.php script with a different require*() uncommented each time:<br />
&nbsp; ab -n 1000 -c 10 www.example.com/test.php<br />
<br />
RESULTS:<br />
--------<br />
The average time it took to run test.php once:<br />
require('absolute_path'):&nbsp; &nbsp; &nbsp; 0.000830569960420<br />
require('relative_path'):&nbsp; &nbsp; &nbsp; 0.000829198306664<br />
require_once('absolute_path'): 0.000832904849136<br />
require_once('relative_path'): 0.000824960252097<br />
<br />
The average was computed by eliminating the 100 slowest and 100 fastest times, so a total of 800 (1000 - 200) times were used to compute the average time.&nbsp; This was done to eliminate any unusual spikes or dips.<br />
<br />
The question of how many stat() system calls were made can be answered as follows:<br />
- If you run httpd -X and then do an strace -p &lt;pid_of_httpd&gt;, you can view the system calls that take place to process the request.<br />
- The most important thing to note is if you run test.php continuously (as the ab test does above), the stat() calls only happen for the first request:<br />
<br />
&nbsp; first call to test.php (above):<br />
&nbsp; -------------------------------<br />
&nbsp; lstat64 ("/www", {st_mode=S_IFDIR|0755, st_size=...<br />
&nbsp; lstat64 ("/www/includes", {st_mode=S_IFDIR|0755,...<br />
&nbsp; lstat64 ("/www/includes/example.com", {st_mode=S...<br />
&nbsp; lstat64 ("/www/includes/example.com/code", {st_m...<br />
&nbsp; lstat64 ("/www/includes/example.com/code/conf", ...<br />
&nbsp; lstat64 ("/www/includes/example.com/code/conf/sql_servers.inc", {st_mode...<br />
&nbsp; open ("/www/includes/example.com/code/conf/sql_servers.inc", O_RDONLY) = 17<br />
&nbsp; <br />
&nbsp; subsequent calls to test.php:<br />
&nbsp; -----------------------------<br />
&nbsp; open ("/www/includes/example.com/code/conf/sql_servers.inc", O_RDONLY) = 17<br />
<br />
- The lack of stat() system calls in the subsequent calls to test.php only happens when test.php is called continusly.&nbsp; If you wait a certain period of time (about 1 minute or so), the stat() calls will happen again.<br />
- This indicates that either the OS (Ubuntu Linux in my case), or Apache is "caching" or knows the results of the previous stat() calls, so it doesn't bother repeating them.<br />
- When using absolute_path there are fewer stat() system calls.<br />
- When using relative_path there are more stat() system calls because it has to start stat()ing from the current directory back up to / and then to the include/ directory.<br />
<br />
CONCLUSIONS:<br />
------------<br />
- Try to use absolute_path when calling require*().<br />
- The time difference between require_once() vs. require() is so tiny, it's almost always insignificant in terms of performance.&nbsp; The one exception is if you have a very large application that has hundreds of require*() calls.<br />
- When using APC opcode caching, the speed difference between the two is completely irrelevant.<br />
- Use an opcode cache, like APC!<br />
<br />
Konstantin Rozinov<br />
krozinov [at] gmail</span>
</code></div>
  </div>
 </div>
 <a name="81812"></a>
 <div class="note">
  <strong class='user'>cnorthcote at underground dot co dot uk</strong>
  <a href="#81812" class="date">14-Mar-2008 03:20</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Bear in mind that require_once doesn't have a return value (neither does require; since they both halt execution on failure), so this won't work:<br />
<br />
&lt;?<br />
<br />
require_once("path/to/myfile.php") or die("Couldn't load myfile");<br />
<br />
?&gt;<br />
<br />
because you will get a very unhelpful error:<br />
<br />
PHP Fatal error:&nbsp; require_once() : Failed opening required '1' (include_path='.;C:\\php5\\pear') in C:\path\to\code.php on line 1<br />
<br />
This was mentioned on the php-general mailing list in about 2003 but is a gotcha I have seen a few people come across. If you want to check to see if a file was included, use @include() instead.</span>
</code></div>
  </div>
 </div>
 <a name="81023"></a>
 <div class="note">
  <strong class='user'>amcewen at look dot ca</strong>
  <a href="#81023" class="date">11-Feb-2008 07:00</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Perhaps it would be clearer to say that require_once() includes AND evaluates the resulting code once.&nbsp; More specifically, if there is code in the script file other than function declarations, this code will only be executed once via require_once().</span>
</code></div>
  </div>
 </div>
 <a name="77218"></a>
 <div class="note">
  <strong class='user'>Sinured</strong>
  <a href="#77218" class="date">20-Aug-2007 10:33</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Beware: As miqrogroove said below, require_once() is not independent of require() -- but vice versa, require() IS independent of require_once()!<br />
Let's turn miqrogroove's example around:<br />
<br />
echo.php<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">echo </span><span class="string">"42!&lt;br /&gt;\n"</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
test.php<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">require_once </span><span class="string">'echo.php'</span><span class="keyword">;<br />
require </span><span class="string">'echo.php'</span><span class="keyword">;<br />
</span><span class="comment">// 42!<br />
// 42!<br />
</span><span class="default">?&gt;<br />
</span><br />
So: require_once() will NOT include a file previously included by require(), while require WILL include a file previously included by require_once.</span>
</code></div>
  </div>
 </div>
 <a name="75766"></a>
 <div class="note">
  <strong class='user'>manuel schaffner</strong>
  <a href="#75766" class="date">14-Jun-2007 02:02</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The path for nested require_once() is always evaluated relative to the called / first file containing require_once(). To make it more flexible, maintain the include_path (php.ini) or use set_include_path() - then the file will be looked up in all these locations.</span>
</code></div>
  </div>
 </div>
 <a name="73733"></a>
 <div class="note">
  <strong class='user'>jazfresh at hotmail.com</strong>
  <a href="#73733" class="date">07-Mar-2007 11:22</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Check how many files you are including with get_required_files(). If it's a significant number (&gt; 100), it may be worth "compiling" the main PHP file. By "compiling", I mean write a script that reads a PHP file and replaces any "include/require_once" references with either:<br />
- the file that it's requiring<br />
- a blank line if that file has been included before<br />
<br />
This function can be recursive, thus building up a large PHP file with no require_once references at all. The speedup can be dramatic. On one of our pages that included 115 classes, the page was sped up by 60%.</span>
</code></div>
  </div>
 </div>
 <a name="72464"></a>
 <div class="note">
  <strong class='user'>sneskid at hotmail dot com</strong>
  <a href="#72464" class="date">19-Jan-2007 12:00</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
<span class="default">&lt;?php<br />
</span><span class="keyword">function &amp; </span><span class="default">rel</span><span class="keyword">(</span><span class="default">$r</span><span class="keyword">, &amp;</span><span class="default">$f</span><span class="keyword">) {return </span><span class="default">file_exists</span><span class="keyword">( ( </span><span class="default">$f </span><span class="keyword">= ( </span><span class="default">dirname</span><span class="keyword">(</span><span class="default">$r</span><span class="keyword">).</span><span class="string">'/'</span><span class="keyword">.</span><span class="default">$f </span><span class="keyword">) ) );}<br />
function &amp; </span><span class="default">relf</span><span class="keyword">(</span><span class="default">$r</span><span class="keyword">, </span><span class="default">$f</span><span class="keyword">) {return </span><span class="default">rel</span><span class="keyword">(</span><span class="default">$r</span><span class="keyword">,</span><span class="default">$f</span><span class="keyword">) ? </span><span class="default">file_get_contents</span><span class="keyword">(</span><span class="default">$f</span><span class="keyword">) : </span><span class="default">null</span><span class="keyword">;}<br />
function &amp; </span><span class="default">reli</span><span class="keyword">(</span><span class="default">$r</span><span class="keyword">, </span><span class="default">$f</span><span class="keyword">) {return </span><span class="default">rel</span><span class="keyword">(</span><span class="default">$r</span><span class="keyword">,</span><span class="default">$f</span><span class="keyword">) ? include(</span><span class="default">$f</span><span class="keyword">) : </span><span class="default">null</span><span class="keyword">;}<br />
function &amp; </span><span class="default">relr</span><span class="keyword">(</span><span class="default">$r</span><span class="keyword">, </span><span class="default">$f</span><span class="keyword">) {return </span><span class="default">rel</span><span class="keyword">(</span><span class="default">$r</span><span class="keyword">,</span><span class="default">$f</span><span class="keyword">) ? require(</span><span class="default">$f</span><span class="keyword">) : </span><span class="default">null</span><span class="keyword">;}<br />
function &amp; </span><span class="default">relio</span><span class="keyword">(</span><span class="default">$r</span><span class="keyword">, </span><span class="default">$f</span><span class="keyword">) {return </span><span class="default">rel</span><span class="keyword">(</span><span class="default">$r</span><span class="keyword">,</span><span class="default">$f</span><span class="keyword">) ? include_once(</span><span class="default">$f</span><span class="keyword">) : </span><span class="default">null</span><span class="keyword">;}<br />
function &amp; </span><span class="default">relro</span><span class="keyword">(</span><span class="default">$r</span><span class="keyword">, </span><span class="default">$f</span><span class="keyword">) {return </span><span class="default">rel</span><span class="keyword">(</span><span class="default">$r</span><span class="keyword">,</span><span class="default">$f</span><span class="keyword">) ? require_once(</span><span class="default">$f</span><span class="keyword">) : </span><span class="default">null</span><span class="keyword">;}<br />
</span><span class="default">?&gt;<br />
</span><br />
I found it useful to have a function that can load a file relative to the calling script and return null if the file did not exist, without raising errors.<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="comment">/*<br />
Load file contents or return blank if it's not there.<br />
Relative to the file calling the function.<br />
*/<br />
</span><span class="keyword">echo </span><span class="default">relf</span><span class="keyword">(</span><span class="default">__FILE__</span><span class="keyword">, </span><span class="string">'some.file'</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span><br />
It was easy to modify and just as useful for require/include.<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="comment">/*<br />
Require the file once.<br />
It's like suppressing error messages with @ but only when the file does not exist.<br />
Still shows compile errors/warning, unless you use @relro().<br />
Relative to the file calling the function.<br />
*/<br />
</span><span class="default">relro</span><span class="keyword">(</span><span class="default">__FILE__</span><span class="keyword">, </span><span class="string">'stats.php'</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span><br />
If you work with a deep php file structure and a barrage of includes/requires/file-loads this works well.</span>
</code></div>
  </div>
 </div>
 <a name="69339"></a>
 <div class="note">
  <strong class='user'>rejjn at mail dot nu</strong>
  <a href="#69339" class="date">01-Sep-2006 02:28</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The following only applies to case insensitive systems like Windows.<br />
<br />
Even though the documentation sais that "the path is normalized" that doesn't seem to be true in all cases. <br />
<br />
If you are using the magic __autoload() function (or if the framework you're using is using it) and it includes the requested class file with complete path or if you override the include path in mid execution, you may have some very strange behavior. The most subtle problem is that the *_once functions seem to differentiate between c:\.... and C:\....<br />
<br />
So to avoid any strange problems and painfull debugging make sure ALL paths you use within the system have the same case everywhere, and that they correspond with the actual case of the filesystem. That includes include paths set in webserver config/php.ini, auto load config, runtime include path settings or anywhere else.</span>
</code></div>
  </div>
 </div>
 <a name="62838"></a>
 <div class="note">
  <strong class='user'>antoine dot pouch at mcgill dot ca</strong>
  <a href="#62838" class="date">10-Mar-2006 07:13</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
require_once (and include_once for that matters) is slow. <br />
Furthermore, if you plan on using unit tests and mock objects (i.e. including mock classes before the real ones are included in the class you want to test), it will not work as require() loads a file and not a class.<br />
<br />
To bypass that, and gain speed, I use :<br />
<br />
<span class="default">&lt;?php<br />
class_exists</span><span class="keyword">(</span><span class="string">'myClass'</span><span class="keyword">) || require(</span><span class="string">'path/to/myClass.class.php'</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span><br />
I tried to time 100 require_once on the same file and it took the script 0.0026 seconds to run, whereas with my method it took only 0.00054 seconds. 4 times faster ! OK, my method of testing is quite empirical and YMMV but the bonus is the ability to use mock objects in your unit tests.</span>
</code></div>
  </div>
 </div>
 <a name="62308"></a>
 <div class="note">
  <strong class='user'>martijn(dot)lowrider(at)gmail(dot)com</strong>
  <a href="#62308" class="date">24-Feb-2006 11:00</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
How to use Require_Once with error reporting to include a MySQL Connection file:<br />
<br />
-------------------------------------------------------------------<br />
$MySQLConnectFile = './inc/MySQL.Class.php';<br />
<br />
if ( is_dir ( './inc/' ) )<br />
{<br />
&nbsp;&nbsp;&nbsp; $IncIsDir == TRUE;<br />
}<br />
<br />
if ( file_exists ( $MySQLConnectFile ) )<br />
{<br />
&nbsp;&nbsp;&nbsp; $MySQLFileExists == TRUE;<br />
}<br />
<br />
if ( $IncIsDir &amp;&amp; $MySQLFileExists )<br />
{<br />
&nbsp;&nbsp;&nbsp; require_once ( $MySQLConnectFile )<br />
}<br />
else<br />
{<br />
&nbsp;&nbsp;&nbsp; echo '&lt;b&gt;Error:&lt;/b&gt; &lt;i&gt;Could not read the MySQL Connection File. Please try again later.';<br />
&nbsp;&nbsp;&nbsp; exit();<br />
}<br />
<br />
----------------------------------------------------------------------</span>
</code></div>
  </div>
 </div>
 <a name="59387"></a>
 <div class="note">
  <strong class='user'>sdh00b at gmail dot com</strong>
  <a href="#59387" class="date">05-Dec-2005 07:12</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
@georg_gruber at yahoo dot com<br />
<br />
Files for including are first looked in include_path relative to the current working directory and then in include_path relative to the directory of current script. E.g. if your include_path is ., current working directory is /www/, you included include/a.php and there is include "b.php" in that file, b.php is first looked in /www/ and then in /www/include/. If filename begins with ./ or ../, it is looked only in include_path relative to the current working directory.<br />
<br />
Taken from <a href="http://us2.php.net/manual/en/function.include.php" rel="nofollow" target="_blank">http://us2.php.net/manual/en/function.include.php</a></span>
</code></div>
  </div>
 </div>
 <a name="57545"></a>
 <div class="note">
  <strong class='user'>georg_gruber at yahoo dot com</strong>
  <a href="#57545" class="date">06-Oct-2005 03:06</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
A very interesting behaviour of require_once (and probably all include commands):<br />
<br />
consider the following files:<br />
<br />
/index.php -&gt; require_once('/inc/library.php');<br />
/function1.php -&gt; print('/function1.php');<br />
/inc/library.php -&gt; require_once('function1.php');<br />
/inc/function1.php -&gt; print('/inc/function1.php');<br />
<br />
Note that /function1.php and /inc/function1.php are files with the SAME filename in different folders.<br />
<br />
If you "/index.php" is executed it will output<br />
"/function1.php".<br />
<br />
Although /index.php "includes" /inc/library.php the scope of the file is still /index.php therefor /function1.php will be found even it could be asumed the /inc/function1.php is the correct one.<br />
<br />
And it gets more interesting: if you delete /function1.php and execute /index.php PHP checks this and "includes" /inc/function1.php.</span>
</code></div>
  </div>
 </div>
 <a name="52442"></a>
 <div class="note">
  <strong class='user'>miqrogroove</strong>
  <a href="#52442" class="date">01-May-2005 11:10</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
require_once() is NOT independent of require().&nbsp; Therefore, the following code will work as expected:<br />
<br />
echo.php<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">echo </span><span class="string">"Hello"</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
test.php<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">require(</span><span class="string">'echo.php'</span><span class="keyword">);<br />
require_once(</span><span class="string">'echo.php'</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span><br />
test.php outputs: "Hello".<br />
<br />
Enjoy,<br />
-- Miqro</span>
</code></div>
  </div>
 </div>
 <a name="51165"></a>
 <div class="note">
  <strong class='user'>ulderico at maber dot com dot br</strong>
  <a href="#51165" class="date">22-Mar-2005 05:38</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
With both of your functions guys, Pure-PHP and jtaal at eljakim dot nl, you'll not have any variables available GLOBALly if they're supposed to be globals...<br />
<br />
That's why my import handles better those situation. OK, SOME MAY DISPUTE that using include_once and require_once may slow down an application. But what's the use to do IN PHP what the interpreter *should* do better for you. Thusly these workarounds shall, some time in the future, DIE.<br />
<br />
Thus It's better to well design your application to keep some order using few INCLUDES and REQUIRES in it rather than insert MANY AND SEVERAL *_once around.</span>
</code></div>
  </div>
 </div>
 <a name="51044"></a>
 <div class="note">
  <strong class='user'>Pure-PHP</strong>
  <a href="#51044" class="date">17-Mar-2005 02:19</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
require_once can slower your app, if you include to many files.<br />
<br />
You cann use this wrapper class, it is faster than include_once <br />
<br />
<a href="http://www.pure-php.de/node/19" rel="nofollow" target="_blank">http://www.pure-php.de/node/19</a><br />
<br />
require_once("includeWrapper.class.php")<br />
<br />
includeWrapper::require_once("Class1.class.php");<br />
includeWrapper::require_once("Class1.class.php");<br />
includeWrapper::require_once("Class2.class.php")</span>
</code></div>
  </div>
 </div>
 <a name="50803"></a>
 <div class="note">
  <strong class='user'>jtaal at eljakim dot nl</strong>
  <a href="#50803" class="date">10-Mar-2005 05:00</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
When you feel the need for a require_once_wildcard function, here's the solution:<br />
<br />
<span class="default">&lt;?php </span><span class="comment">// /var/www/app/system/include.inc.php<br />
<br />
</span><span class="keyword">function </span><span class="default">require_once_wildcard</span><span class="keyword">(</span><span class="default">$wildcard</span><span class="keyword">, </span><span class="default">$__FILE__</span><span class="keyword">) {<br />
&nbsp; </span><span class="default">preg_match</span><span class="keyword">(</span><span class="string">"/^(.+)\/[^\/]+$/"</span><span class="keyword">, </span><span class="default">$__FILE__</span><span class="keyword">, </span><span class="default">$matches</span><span class="keyword">);<br />
&nbsp; </span><span class="default">$ls </span><span class="keyword">= `</span><span class="default">ls $matches</span><span class="keyword">[</span><span class="default">1</span><span class="keyword">]</span><span class="default">/$wildcard</span><span class="keyword">`;<br />
&nbsp; </span><span class="default">$ls </span><span class="keyword">= </span><span class="default">explode</span><span class="keyword">(</span><span class="string">"\n"</span><span class="keyword">, </span><span class="default">$ls</span><span class="keyword">);<br />
&nbsp; </span><span class="default">array_pop</span><span class="keyword">(</span><span class="default">$ls</span><span class="keyword">); </span><span class="comment">// remove empty line ls always prints<br />
&nbsp; </span><span class="keyword">foreach (</span><span class="default">$ls </span><span class="keyword">as </span><span class="default">$inc</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; require_once(</span><span class="default">$inc</span><span class="keyword">);<br />
&nbsp; }<br />
}<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
The $__FILE__ variable should be filled with the special PHP construct __FILE__:<br />
<span class="default">&lt;?php </span><span class="comment">// /var/www/app/classes.inc.php<br />
<br />
</span><span class="keyword">require_once(</span><span class="string">'system/include.inc.php'</span><span class="keyword">);<br />
</span><span class="default">require_once_wildcard</span><span class="keyword">(</span><span class="string">"classes/*.inc.php"</span><span class="keyword">, </span><span class="default">__FILE__</span><span class="keyword">);<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
The (*.inc.php) files inside the directory classes are automagically included using require_once_wildcard.<br />
<br />
This solution may not be as useful when using PHP5 in combination with classes and the autoload feature.<br />
<br />
--<br />
Jaap Taal</span>
</code></div>
  </div>
 </div>
 <a name="49245"></a>
 <div class="note">
  <strong class='user'>thomas dot revell at uwe dot ac dot uk</strong>
  <a href="#49245" class="date">21-Jan-2005 02:00</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Regarding the case insensitivity problems on Windows, it looks to me as though it is a problem in PHP5 as well (at least in some cases).<br />
<br />
The following gave me problems:<br />
<br />
From file URLSwitcher.php<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">require_once </span><span class="string">'slimError/slimError.php'</span><span class="keyword">;<br />
require_once </span><span class="string">'Navigator_Cache.php'</span><span class="keyword">;<br />
....<br />
</span><span class="default">?&gt;<br />
</span><br />
From file Navigator_Cache.php<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">require_once </span><span class="string">'slimError/slimerror.php'</span><span class="keyword">;<br />
...<br />
</span><span class="default">?&gt;<br />
</span><br />
From file slimerror.php<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">SLIMError </span><span class="keyword">{<br />
...<br />
}<br />
</span><span class="default">?&gt;<br />
</span>The above setup gave me an error : "Cannot redeclare class SLIMError"<br />
<br />
If I change the require_once in URLSwitcher.php to match the one in Navigator_Cache.php, there isn't a problem, but if I do this the other way round, the same problem occurs.</span>
</code></div>
  </div>
 </div>
 <a name="40897"></a>
 <div class="note">
  <a href="#40897" class="date">18-Mar-2004 09:49</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
&gt; Mac OS X systems are also not case-sensitive.<br />
That depends on the filesystem:<br />
- HFS and HFS+ are NOT case sensitive.<br />
- UFS is case sensitive.</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=function.require-once&amp;redirect=http://www.php.net/manual/en/function.require-once.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=function.require-once&amp;redirect=http://www.php.net/manual/en/function.require-once.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/function.require-once.php">show source</a> |
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