<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Defining multiple namespaces in the same file - Manual</title>
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
 <link rel="prev" href="language.namespaces.nested.php" />
 <link rel="next" href="language.namespaces.basics.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/namespaces.definitionmultiple" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/language.namespaces.definitionmultiple.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="@w{HBJSZ8WY}" />
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
 <li class="active"><a href="language.namespaces.definitionmultiple.php">Defining multiple namespaces in the same file</a></li>
 <li><a href="language.namespaces.basics.php">Using namespaces: Basics</a></li>
 <li><a href="language.namespaces.dynamic.php">Namespaces and dynamic language features</a></li>
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
  <a href="language.namespaces.basics.php">Using namespaces: Basics<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.namespaces.nested.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Declaring sub-namespaces</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.namespaces.definitionmultiple.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/language.namespaces.definitionmultiple.php">Brazilian Portuguese</option>
    <option value="zh/language.namespaces.definitionmultiple.php">Chinese (Simplified)</option>
    <option value="fr/language.namespaces.definitionmultiple.php">French</option>
    <option value="de/language.namespaces.definitionmultiple.php">German</option>
    <option value="ja/language.namespaces.definitionmultiple.php">Japanese</option>
    <option value="pl/language.namespaces.definitionmultiple.php">Polish</option>
    <option value="ro/language.namespaces.definitionmultiple.php">Romanian</option>
    <option value="ru/language.namespaces.definitionmultiple.php">Russian</option>
    <option value="fa/language.namespaces.definitionmultiple.php">Persian</option>
    <option value="es/language.namespaces.definitionmultiple.php">Spanish</option>
    <option value="tr/language.namespaces.definitionmultiple.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="language.namespaces.definitionmultiple" class="sect1">
  <h2 class="title">Defining multiple namespaces in the same file</h2>
  <p class="verinfo">(PHP 5 &gt;= 5.3.0)</p>
  <p class="para">
   Multiple namespaces may also be declared in the same file.  There are two allowed
   syntaxes.
  </p>
  <p class="para">
   <div class="example" id="example-230">
    <p><strong>Example #1 Declaring multiple namespaces, simple combination syntax</strong></p>
    <div class="example-contents">
     <div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">namespace&nbsp;</span><span style="color: #0000BB">MyProject</span><span style="color: #007700">;<br /><br />const&nbsp;</span><span style="color: #0000BB">CONNECT_OK&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">1</span><span style="color: #007700">;<br />class&nbsp;</span><span style="color: #0000BB">Connection&nbsp;</span><span style="color: #007700">{&nbsp;</span><span style="color: #FF8000">/*&nbsp;...&nbsp;*/&nbsp;</span><span style="color: #007700">}<br />function&nbsp;</span><span style="color: #0000BB">connect</span><span style="color: #007700">()&nbsp;{&nbsp;</span><span style="color: #FF8000">/*&nbsp;...&nbsp;*/&nbsp;&nbsp;</span><span style="color: #007700">}<br /><br />namespace&nbsp;</span><span style="color: #0000BB">AnotherProject</span><span style="color: #007700">;<br /><br />const&nbsp;</span><span style="color: #0000BB">CONNECT_OK&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">1</span><span style="color: #007700">;<br />class&nbsp;</span><span style="color: #0000BB">Connection&nbsp;</span><span style="color: #007700">{&nbsp;</span><span style="color: #FF8000">/*&nbsp;...&nbsp;*/&nbsp;</span><span style="color: #007700">}<br />function&nbsp;</span><span style="color: #0000BB">connect</span><span style="color: #007700">()&nbsp;{&nbsp;</span><span style="color: #FF8000">/*&nbsp;...&nbsp;*/&nbsp;&nbsp;</span><span style="color: #007700">}<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

   </div>
  </p>
  <p class="para">
   This syntax is not recommended for combining namespaces into a single file.
   Instead it is recommended to use the alternate bracketed syntax.
  </p>
  <p class="para">
   <div class="example" id="example-231">
    <p><strong>Example #2 Declaring multiple namespaces, bracketed syntax</strong></p>
    <div class="example-contents">
     <div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">namespace&nbsp;</span><span style="color: #0000BB">MyProject&nbsp;</span><span style="color: #007700">{<br /><br />const&nbsp;</span><span style="color: #0000BB">CONNECT_OK&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">1</span><span style="color: #007700">;<br />class&nbsp;</span><span style="color: #0000BB">Connection&nbsp;</span><span style="color: #007700">{&nbsp;</span><span style="color: #FF8000">/*&nbsp;...&nbsp;*/&nbsp;</span><span style="color: #007700">}<br />function&nbsp;</span><span style="color: #0000BB">connect</span><span style="color: #007700">()&nbsp;{&nbsp;</span><span style="color: #FF8000">/*&nbsp;...&nbsp;*/&nbsp;&nbsp;</span><span style="color: #007700">}<br />}<br /><br />namespace&nbsp;</span><span style="color: #0000BB">AnotherProject&nbsp;</span><span style="color: #007700">{<br /><br />const&nbsp;</span><span style="color: #0000BB">CONNECT_OK&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">1</span><span style="color: #007700">;<br />class&nbsp;</span><span style="color: #0000BB">Connection&nbsp;</span><span style="color: #007700">{&nbsp;</span><span style="color: #FF8000">/*&nbsp;...&nbsp;*/&nbsp;</span><span style="color: #007700">}<br />function&nbsp;</span><span style="color: #0000BB">connect</span><span style="color: #007700">()&nbsp;{&nbsp;</span><span style="color: #FF8000">/*&nbsp;...&nbsp;*/&nbsp;&nbsp;</span><span style="color: #007700">}<br />}<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

   </div>
  </p>
  <p class="para">
   It is strongly discouraged as a coding practice to combine multiple namespaces into
   the same file.  The primary use case is to combine multiple PHP scripts into the same
   file.
  </p>
  <p class="para">
   To combine global non-namespaced code with namespaced code, only bracketed syntax is
   supported.  Global code should be
   encased in a namespace statement with no namespace name as in:
   <div class="example" id="example-232">
    <p><strong>Example #3 Declaring multiple namespaces and unnamespaced code</strong></p>
    <div class="example-contents">
     <div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">namespace&nbsp;</span><span style="color: #0000BB">MyProject&nbsp;</span><span style="color: #007700">{<br /><br />const&nbsp;</span><span style="color: #0000BB">CONNECT_OK&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">1</span><span style="color: #007700">;<br />class&nbsp;</span><span style="color: #0000BB">Connection&nbsp;</span><span style="color: #007700">{&nbsp;</span><span style="color: #FF8000">/*&nbsp;...&nbsp;*/&nbsp;</span><span style="color: #007700">}<br />function&nbsp;</span><span style="color: #0000BB">connect</span><span style="color: #007700">()&nbsp;{&nbsp;</span><span style="color: #FF8000">/*&nbsp;...&nbsp;*/&nbsp;&nbsp;</span><span style="color: #007700">}<br />}<br /><br />namespace&nbsp;{&nbsp;</span><span style="color: #FF8000">//&nbsp;global&nbsp;code<br /></span><span style="color: #0000BB">session_start</span><span style="color: #007700">();<br /></span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">MyProject</span><span style="color: #007700">\</span><span style="color: #0000BB">connect</span><span style="color: #007700">();<br />echo&nbsp;</span><span style="color: #0000BB">MyProject</span><span style="color: #007700">\</span><span style="color: #0000BB">Connection</span><span style="color: #007700">::</span><span style="color: #0000BB">start</span><span style="color: #007700">();<br />}<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

   </div>
  </p>
  <p class="para">
   No PHP code may exist outside of the namespace brackets except for an opening
   declare statement.
   <div class="example" id="example-233">
    <p><strong>Example #4 Declaring multiple namespaces and unnamespaced code</strong></p>
    <div class="example-contents">
     <div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">declare(</span><span style="color: #0000BB">encoding</span><span style="color: #007700">=</span><span style="color: #DD0000">'UTF-8'</span><span style="color: #007700">);<br />namespace&nbsp;</span><span style="color: #0000BB">MyProject&nbsp;</span><span style="color: #007700">{<br /><br />const&nbsp;</span><span style="color: #0000BB">CONNECT_OK&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">1</span><span style="color: #007700">;<br />class&nbsp;</span><span style="color: #0000BB">Connection&nbsp;</span><span style="color: #007700">{&nbsp;</span><span style="color: #FF8000">/*&nbsp;...&nbsp;*/&nbsp;</span><span style="color: #007700">}<br />function&nbsp;</span><span style="color: #0000BB">connect</span><span style="color: #007700">()&nbsp;{&nbsp;</span><span style="color: #FF8000">/*&nbsp;...&nbsp;*/&nbsp;&nbsp;</span><span style="color: #007700">}<br />}<br /><br />namespace&nbsp;{&nbsp;</span><span style="color: #FF8000">//&nbsp;global&nbsp;code<br /></span><span style="color: #0000BB">session_start</span><span style="color: #007700">();<br /></span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">MyProject</span><span style="color: #007700">\</span><span style="color: #0000BB">connect</span><span style="color: #007700">();<br />echo&nbsp;</span><span style="color: #0000BB">MyProject</span><span style="color: #007700">\</span><span style="color: #0000BB">Connection</span><span style="color: #007700">::</span><span style="color: #0000BB">start</span><span style="color: #007700">();<br />}<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

   </div>
  </p>
 </div><br /><br />
<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=language.namespaces.definitionmultiple&amp;redirect=@w{HBJSZ8WY}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.namespaces.definitionmultiple&amp;redirect=@w{HBJSZ8WY}">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Defining multiple namespaces in the same file</strong>
 </div><div id="allnotes">
 <a name="106387"></a>
 <div class="note">
  <strong class='user'>kothnok at gmail dot com</strong>
  <a href="#106387" class="date">03-Nov-2011 10:59</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
"use" statements are required to be placed after the "namespace my\space" but before the "{".<br />
e.g. <br />
<br />
<span class="default">&lt;?php<br />
namespace foobar</span><span class="keyword">;<br />
use </span><span class="default">myspaceMyClass</span><span class="keyword">;<br />
{<br />
<br />
&nbsp;</span><span class="comment">// place code here<br />
<br />
</span><span class="keyword">} </span><span class="comment">// end of namespace foo\bar<br />
<br />
</span><span class="default">namespace anotherbar</span><span class="keyword">;<br />
use </span><span class="default">myspaceMyClass</span><span class="keyword">;<br />
use </span><span class="default">myspaceAnotherClass</span><span class="keyword">;<br />
{<br />
<br />
&nbsp;</span><span class="comment">// place code here<br />
<br />
</span><span class="keyword">} </span><span class="comment">// end of namespace another\bar<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="96878"></a>
 <div class="note">
  <strong class='user'>anders at ingemann dot de</strong>
  <a href="#96878" class="date">20-Mar-2010 11:16</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Apparently you will have to define namespaces using curly brackets enclosing theclasses, if you want doxygen to pick them up.</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=language.namespaces.definitionmultiple&amp;redirect=@w{HBJSZ8WY}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.namespaces.definitionmultiple&amp;redirect=@w{HBJSZ8WY}">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/language.namespaces.definitionmultiple.php">show source</a> |
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