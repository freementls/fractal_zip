<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Namespaces and dynamic language features - Manual</title>
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
 <link rel="index" href="language.namespaces.php" />
 <link rel="prev" href="language.namespaces.basics.php" />
 <link rel="next" href="language.namespaces.nsconstants.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/namespaces.dynamic" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/language.namespaces.dynamic.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="@w{F7HC2HQF}" />
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
 <li class="header up"><a href="language.namespaces.php">Namespaces</a></li>
 <li><a href="language.namespaces.rationale.php">Namespaces overview</a></li>
 <li><a href="language.namespaces.definition.php">Defining namespaces</a></li>
 <li><a href="language.namespaces.nested.php">Declaring sub-namespaces</a></li>
 <li><a href="language.namespaces.definitionmultiple.php">Defining multiple namespaces in the same file</a></li>
 <li><a href="language.namespaces.basics.php">Using namespaces: Basics</a></li>
 <li class="active"><a href="language.namespaces.dynamic.php">Namespaces and dynamic language features</a></li>
 <li><a href="language.namespaces.nsconstants.php">namespace keyword and __NAMESPACE__ constant</a></li>
 <li><a href="language.namespaces.importing.php">Using namespaces: Aliasing/Importing</a></li>
 <li><a href="language.namespaces.global.php">Global space</a></li>
 <li><a href="language.namespaces.fallback.php">Using namespaces: fallback to global function/constant</a></li>
 <li><a href="language.namespaces.rules.php">Name resolution rules</a></li>
 <li><a href="language.namespaces.faq.php">FAQ: things you need to know about namespaces</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="language.namespaces.nsconstants.php">namespace keyword and __NAMESPACE__ constant<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.namespaces.basics.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Using namespaces: Basics</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.namespaces.dynamic.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/language.namespaces.dynamic.php">Brazilian Portuguese</option>
    <option value="zh/language.namespaces.dynamic.php">Chinese (Simplified)</option>
    <option value="fr/language.namespaces.dynamic.php">French</option>
    <option value="de/language.namespaces.dynamic.php">German</option>
    <option value="ja/language.namespaces.dynamic.php">Japanese</option>
    <option value="pl/language.namespaces.dynamic.php">Polish</option>
    <option value="ro/language.namespaces.dynamic.php">Romanian</option>
    <option value="ru/language.namespaces.dynamic.php">Russian</option>
    <option value="fa/language.namespaces.dynamic.php">Persian</option>
    <option value="es/language.namespaces.dynamic.php">Spanish</option>
    <option value="tr/language.namespaces.dynamic.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="language.namespaces.dynamic" class="sect1">
  <h2 class="title">Namespaces and dynamic language features</h2>
  <p class="verinfo">(PHP 5 &gt;= 5.3.0)</p>
  <p class="para">
   PHP&#039;s implementation of namespaces is influenced by its dynamic nature as a programming
   language.  Thus, to convert code like the following example into namespaced code:
   <div class="example" id="example-235">
    <p><strong>Example #1 Dynamically accessing elements</strong></p>
    <div class="example-contents"><p>example1.php:</p></div>
    <div class="example-contents">
     <div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">class&nbsp;</span><span style="color: #0000BB">classname<br /></span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;function&nbsp;</span><span style="color: #0000BB">__construct</span><span style="color: #007700">()<br />&nbsp;&nbsp;&nbsp;&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #0000BB">__METHOD__</span><span style="color: #007700">,</span><span style="color: #DD0000">"\n"</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br />function&nbsp;</span><span style="color: #0000BB">funcname</span><span style="color: #007700">()<br />{<br />&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #0000BB">__FUNCTION__</span><span style="color: #007700">,</span><span style="color: #DD0000">"\n"</span><span style="color: #007700">;<br />}<br />const&nbsp;</span><span style="color: #0000BB">constname&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">"global"</span><span style="color: #007700">;<br /><br /></span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'classname'</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">$obj&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">$a</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;prints&nbsp;classname::__construct<br /></span><span style="color: #0000BB">$b&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'funcname'</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">$b</span><span style="color: #007700">();&nbsp;</span><span style="color: #FF8000">//&nbsp;prints&nbsp;funcname<br /></span><span style="color: #007700">echo&nbsp;</span><span style="color: #0000BB">constant</span><span style="color: #007700">(</span><span style="color: #DD0000">'constname'</span><span style="color: #007700">),&nbsp;</span><span style="color: #DD0000">"\n"</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;prints&nbsp;global<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

   </div>
   One must use the fully qualified name (class name with namespace prefix).
   Note that because there is no difference between a qualified and a fully qualified Name
   inside a dynamic class name, function name, or constant name, the leading backslash is
   not necessary.
   <div class="example" id="example-236">
    <p><strong>Example #2 Dynamically accessing namespaced elements</strong></p>
    <div class="example-contents">
     <div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">namespace&nbsp;</span><span style="color: #0000BB">namespacename</span><span style="color: #007700">;<br />class&nbsp;</span><span style="color: #0000BB">classname<br /></span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;function&nbsp;</span><span style="color: #0000BB">__construct</span><span style="color: #007700">()<br />&nbsp;&nbsp;&nbsp;&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #0000BB">__METHOD__</span><span style="color: #007700">,</span><span style="color: #DD0000">"\n"</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br />function&nbsp;</span><span style="color: #0000BB">funcname</span><span style="color: #007700">()<br />{<br />&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #0000BB">__FUNCTION__</span><span style="color: #007700">,</span><span style="color: #DD0000">"\n"</span><span style="color: #007700">;<br />}<br />const&nbsp;</span><span style="color: #0000BB">constname&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">"namespaced"</span><span style="color: #007700">;<br /><br />include&nbsp;</span><span style="color: #DD0000">'example1.php'</span><span style="color: #007700">;<br /><br /></span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'classname'</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">$obj&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">$a</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;prints&nbsp;classname::__construct<br /></span><span style="color: #0000BB">$b&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'funcname'</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">$b</span><span style="color: #007700">();&nbsp;</span><span style="color: #FF8000">//&nbsp;prints&nbsp;funcname<br /></span><span style="color: #007700">echo&nbsp;</span><span style="color: #0000BB">constant</span><span style="color: #007700">(</span><span style="color: #DD0000">'constname'</span><span style="color: #007700">),&nbsp;</span><span style="color: #DD0000">"\n"</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;prints&nbsp;global<br /><br />/*&nbsp;note&nbsp;that&nbsp;if&nbsp;using&nbsp;double&nbsp;quotes,&nbsp;"\\namespacename\\classname"&nbsp;must&nbsp;be&nbsp;used&nbsp;*/<br /></span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'\namespacename\classname'</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">$obj&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">$a</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;prints&nbsp;namespacename\classname::__construct<br /></span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'namespacename\classname'</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">$obj&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">$a</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;also&nbsp;prints&nbsp;namespacename\classname::__construct<br /></span><span style="color: #0000BB">$b&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'namespacename\funcname'</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">$b</span><span style="color: #007700">();&nbsp;</span><span style="color: #FF8000">//&nbsp;prints&nbsp;namespacename\funcname<br /></span><span style="color: #0000BB">$b&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'\namespacename\funcname'</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">$b</span><span style="color: #007700">();&nbsp;</span><span style="color: #FF8000">//&nbsp;also&nbsp;prints&nbsp;namespacename\funcname<br /></span><span style="color: #007700">echo&nbsp;</span><span style="color: #0000BB">constant</span><span style="color: #007700">(</span><span style="color: #DD0000">'\namespacename\constname'</span><span style="color: #007700">),&nbsp;</span><span style="color: #DD0000">"\n"</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;prints&nbsp;namespaced<br /></span><span style="color: #007700">echo&nbsp;</span><span style="color: #0000BB">constant</span><span style="color: #007700">(</span><span style="color: #DD0000">'namespacename\constname'</span><span style="color: #007700">),&nbsp;</span><span style="color: #DD0000">"\n"</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;also&nbsp;prints&nbsp;namespaced<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

   </div>
  </p>
  <p class="para">
   Be sure to read the <a href="language.namespaces.faq.php#language.namespaces.faq.quote" class="link">note about
   escaping namespace names in strings</a>.
  </p>
 </div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="language.namespaces.nsconstants.php">namespace keyword and __NAMESPACE__ constant<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.namespaces.basics.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Using namespaces: Basics</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.namespaces.dynamic.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=language.namespaces.dynamic&amp;redirect=@w{F7HC2HQF}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.namespaces.dynamic&amp;redirect=@w{F7HC2HQF}">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Namespaces and dynamic language features</strong>
 </div><div id="allnotes">
 <a name="104762"></a>
 <div class="note">
  <strong class='user'>Alexander Kirk</strong>
  <a href="#104762" class="date">06-Jul-2011 12:57</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
When extending a class from another namespace that should instantiate a class from within the current namespace, you need to pass on the namespace.<br />
<br />
<span class="default">&lt;?php </span><span class="comment">// File1.php<br />
</span><span class="default">namespace foo</span><span class="keyword">;<br />
class </span><span class="default">A </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">factory</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return new </span><span class="default">C</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
class </span><span class="default">C </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">tell</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">"foo"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
<span class="default">&lt;?php </span><span class="comment">// File2.php<br />
</span><span class="default">namespace bar</span><span class="keyword">;<br />
class </span><span class="default">B </span><span class="keyword">extends </span><span class="default">fooA </span><span class="keyword">{}<br />
class </span><span class="default">C </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">tell</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">"bar"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
<span class="default">&lt;?php<br />
</span><span class="keyword">include </span><span class="string">"File1.php"</span><span class="keyword">;<br />
include </span><span class="string">"File2.php"</span><span class="keyword">;<br />
</span><span class="default">$b </span><span class="keyword">= new </span><span class="default">barB</span><span class="keyword">;<br />
</span><span class="default">$c </span><span class="keyword">= </span><span class="default">$b</span><span class="keyword">-&gt;</span><span class="default">factory</span><span class="keyword">();<br />
</span><span class="default">$c</span><span class="keyword">-&gt;</span><span class="default">tell</span><span class="keyword">(); </span><span class="comment">// "foo" but you want "bar"<br />
</span><span class="default">?&gt;<br />
</span><br />
You need to do it like this:<br />
<br />
When extending a class from another namespace that should instantiate a class from within the current namespace, you need to pass on the namespace.<br />
<br />
<span class="default">&lt;?php </span><span class="comment">// File1.php<br />
</span><span class="default">namespace foo</span><span class="keyword">;<br />
class </span><span class="default">A </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; protected </span><span class="default">$namespace </span><span class="keyword">= </span><span class="default">__NAMESPACE__</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">factory</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$c </span><span class="keyword">= </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">namespace </span><span class="keyword">. </span><span class="string">'\C'</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return new </span><span class="default">$c</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
class </span><span class="default">C </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">tell</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">"foo"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
<span class="default">&lt;?php </span><span class="comment">// File2.php<br />
</span><span class="default">namespace bar</span><span class="keyword">;<br />
class </span><span class="default">B </span><span class="keyword">extends </span><span class="default">fooA </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; protected </span><span class="default">$namespace </span><span class="keyword">= </span><span class="default">__NAMESPACE__</span><span class="keyword">;<br />
}<br />
class </span><span class="default">C </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">tell</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">"bar"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
<span class="default">&lt;?php<br />
</span><span class="keyword">include </span><span class="string">"File1.php"</span><span class="keyword">;<br />
include </span><span class="string">"File2.php"</span><span class="keyword">;<br />
</span><span class="default">$b </span><span class="keyword">= new </span><span class="default">barB</span><span class="keyword">;<br />
</span><span class="default">$c </span><span class="keyword">= </span><span class="default">$b</span><span class="keyword">-&gt;</span><span class="default">factory</span><span class="keyword">();<br />
</span><span class="default">$c</span><span class="keyword">-&gt;</span><span class="default">tell</span><span class="keyword">(); </span><span class="comment">// "bar"<br />
</span><span class="default">?&gt;<br />
</span><br />
(it seems that the namespace-backslashes are stripped from the source code in the preview, maybe it works in the main view. If not: fooA was written as \foo\A and barB as bar\B)</span>
</code></div>
  </div>
 </div>
 <a name="92764"></a>
 <div class="note">
  <strong class='user'>scott at intothewild dot ca</strong>
  <a href="#92764" class="date">07-Aug-2009 03:33</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
as noted by guilhermeblanco at php dot net, <br />
<br />
<span class="default">&lt;?php<br />
<br />
&nbsp; </span><span class="comment">// fact.php<br />
<br />
&nbsp; </span><span class="default">namespace foo</span><span class="keyword">;<br />
<br />
&nbsp; class </span><span class="default">fact </span><span class="keyword">{<br />
<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">create</span><span class="keyword">(</span><span class="default">$class</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp;&nbsp; return new </span><span class="default">$class</span><span class="keyword">();<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp; }<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
<span class="default">&lt;?php <br />
<br />
&nbsp; </span><span class="comment">// bar.php<br />
<br />
&nbsp; </span><span class="default">namespace foo</span><span class="keyword">;<br />
<br />
&nbsp; class </span><span class="default">bar </span><span class="keyword">{<br />
&nbsp; ... <br />
&nbsp; }<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
<span class="default">&lt;?php<br />
<br />
&nbsp; </span><span class="comment">// index.php<br />
&nbsp;<br />
&nbsp; </span><span class="default">namespace foo</span><span class="keyword">;<br />
<br />
&nbsp; include(</span><span class="string">'fact.php'</span><span class="keyword">);<br />
&nbsp; <br />
&nbsp; </span><span class="default">$foofact </span><span class="keyword">= new </span><span class="default">fact</span><span class="keyword">();<br />
&nbsp; </span><span class="default">$bar </span><span class="keyword">= </span><span class="default">$foofact</span><span class="keyword">-&gt;</span><span class="default">create</span><span class="keyword">(</span><span class="string">'bar'</span><span class="keyword">); </span><span class="comment">// attempts to create \bar<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; // even though foofact and<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; // bar reside in \foo<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="91552"></a>
 <div class="note">
  <strong class='user'>guilhermeblanco at php dot net</strong>
  <a href="#91552" class="date">16-Jun-2009 12:04</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Please be aware of FQCN (Full Qualified Class Name) point.<br />
Many people will have troubles with this:<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="comment">// File1.php<br />
</span><span class="default">namespace foo</span><span class="keyword">;<br />
<br />
class </span><span class="default">Bar </span><span class="keyword">{ ... }<br />
<br />
function </span><span class="default">factory</span><span class="keyword">(</span><span class="default">$class</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; return new </span><span class="default">$class</span><span class="keyword">;<br />
}<br />
<br />
</span><span class="comment">// File2.php<br />
</span><span class="default">$bar </span><span class="keyword">= </span><span class="default">foofactory</span><span class="keyword">(</span><span class="string">'Bar'</span><span class="keyword">); </span><span class="comment">// Will try to instantiate \Bar, not \foo\Bar<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
To fix that, and also incorporate a 2 step namespace resolution, you can check for \ as first char of $class, and if not present, build manually the FQCN:<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="comment">// File1.php<br />
</span><span class="default">namespace foo</span><span class="keyword">;<br />
<br />
function </span><span class="default">factory</span><span class="keyword">(</span><span class="default">$class</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; if (</span><span class="default">$class</span><span class="keyword">[</span><span class="default">0</span><span class="keyword">] != </span><span class="string">'\\'</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">'-&gt;'</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; </span><span class="default">$class </span><span class="keyword">= </span><span class="string">'\\' </span><span class="keyword">. </span><span class="default">__NAMESPACE__ </span><span class="keyword">. </span><span class="string">'\\' </span><span class="keyword">. </span><span class="default">$class</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; return new </span><span class="default">$class</span><span class="keyword">();<br />
}<br />
<br />
</span><span class="comment">// File2.php<br />
</span><span class="default">$bar </span><span class="keyword">= </span><span class="default">foofactory</span><span class="keyword">(</span><span class="string">'Bar'</span><span class="keyword">); </span><span class="comment">// Will correctly instantiate \foo\Bar<br />
<br />
</span><span class="default">$bar2 </span><span class="keyword">= </span><span class="default">foofactory</span><span class="keyword">(</span><span class="string">'\anotherfoo\Bar'</span><span class="keyword">); </span><span class="comment">// Wil correctly instantiate \anotherfoo\Bar<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=language.namespaces.dynamic&amp;redirect=@w{F7HC2HQF}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.namespaces.dynamic&amp;redirect=@w{F7HC2HQF}">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/language.namespaces.dynamic.php">show source</a> |
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