<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: namespace keyword and __NAMESPACE__ constant - Manual</title>
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
 <link rel="prev" href="language.namespaces.dynamic.php" />
 <link rel="next" href="language.namespaces.importing.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/namespaces.nsconstants" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/language.namespaces.nsconstants.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="@w{A9K9KDHR}" />
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
 <li><a href="language.namespaces.dynamic.php">Namespaces and dynamic language features</a></li>
 <li class="active"><a href="language.namespaces.nsconstants.php">namespace keyword and __NAMESPACE__ constant</a></li>
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
  <a href="language.namespaces.importing.php">Using namespaces: Aliasing/Importing<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.namespaces.dynamic.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Namespaces and dynamic language features</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.namespaces.nsconstants.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/language.namespaces.nsconstants.php">Brazilian Portuguese</option>
    <option value="zh/language.namespaces.nsconstants.php">Chinese (Simplified)</option>
    <option value="fr/language.namespaces.nsconstants.php">French</option>
    <option value="de/language.namespaces.nsconstants.php">German</option>
    <option value="ja/language.namespaces.nsconstants.php">Japanese</option>
    <option value="pl/language.namespaces.nsconstants.php">Polish</option>
    <option value="ro/language.namespaces.nsconstants.php">Romanian</option>
    <option value="ru/language.namespaces.nsconstants.php">Russian</option>
    <option value="fa/language.namespaces.nsconstants.php">Persian</option>
    <option value="es/language.namespaces.nsconstants.php">Spanish</option>
    <option value="tr/language.namespaces.nsconstants.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="language.namespaces.nsconstants" class="sect1">
  <h2 class="title">namespace keyword and __NAMESPACE__ constant</h2>
  <p class="verinfo">(PHP 5 &gt;= 5.3.0)</p>
  <p class="para">
   PHP supports two ways of abstractly accessing elements within the current namespace,
   the <strong><code>__NAMESPACE__</code></strong> magic constant, and the <em>namespace</em>
   keyword.
  </p>
  <p class="para">
   The value of <strong><code>__NAMESPACE__</code></strong> is a string that contains the current
   namespace name.  In global, un-namespaced code, it contains an empty string.
   <div class="example" id="example-237">
    <p><strong>Example #1 __NAMESPACE__ example, namespaced code</strong></p>
    <div class="example-contents">
     <div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">namespace&nbsp;</span><span style="color: #0000BB">MyProject</span><span style="color: #007700">;<br /><br />echo&nbsp;</span><span style="color: #DD0000">'"'</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">__NAMESPACE__</span><span style="color: #007700">,&nbsp;</span><span style="color: #DD0000">'"'</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;outputs&nbsp;"MyProject"<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

   </div>
   <div class="example" id="example-238">
    <p><strong>Example #2 __NAMESPACE__ example, global code</strong></p>
    <div class="example-contents">
     <div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /><br /></span><span style="color: #007700">echo&nbsp;</span><span style="color: #DD0000">'"'</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">__NAMESPACE__</span><span style="color: #007700">,&nbsp;</span><span style="color: #DD0000">'"'</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;outputs&nbsp;""<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

   </div>
   The <strong><code>__NAMESPACE__</code></strong> constant is useful for dynamically constructing
   names, for instance:
   <div class="example" id="example-239">
    <p><strong>Example #3 using __NAMESPACE__ for dynamic name construction</strong></p>
    <div class="example-contents">
     <div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">namespace&nbsp;</span><span style="color: #0000BB">MyProject</span><span style="color: #007700">;<br /><br />function&nbsp;</span><span style="color: #0000BB">get</span><span style="color: #007700">(</span><span style="color: #0000BB">$classname</span><span style="color: #007700">)<br />{<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">__NAMESPACE__&nbsp;</span><span style="color: #007700">.&nbsp;</span><span style="color: #DD0000">'\\'&nbsp;</span><span style="color: #007700">.&nbsp;</span><span style="color: #0000BB">$classname</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;return&nbsp;new&nbsp;</span><span style="color: #0000BB">$a</span><span style="color: #007700">;<br />}<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

   </div>
  </p>
  <p class="para">
   The <em>namespace</em> keyword can be used to explicitly request
   an element from the current namespace or a sub-namespace.  It is the namespace
   equivalent of the <em>self</em> operator for classes.
   <div class="example" id="example-240">
    <p><strong>Example #4 the namespace operator, inside a namespace</strong></p>
    <div class="example-contents">
     <div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">namespace&nbsp;</span><span style="color: #0000BB">MyProject</span><span style="color: #007700">;<br /><br />use&nbsp;</span><span style="color: #0000BB">blah</span><span style="color: #007700">\</span><span style="color: #0000BB">blah&nbsp;</span><span style="color: #007700">as&nbsp;</span><span style="color: #0000BB">mine</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;see&nbsp;"Using&nbsp;namespaces:&nbsp;importing/aliasing"<br /><br /></span><span style="color: #0000BB">blah</span><span style="color: #007700">\</span><span style="color: #0000BB">mine</span><span style="color: #007700">();&nbsp;</span><span style="color: #FF8000">//&nbsp;calls&nbsp;function&nbsp;MyProject\blah\mine()<br /></span><span style="color: #007700">namespace\</span><span style="color: #0000BB">blah</span><span style="color: #007700">\</span><span style="color: #0000BB">mine</span><span style="color: #007700">();&nbsp;</span><span style="color: #FF8000">//&nbsp;calls&nbsp;function&nbsp;MyProject\blah\mine()<br /><br /></span><span style="color: #007700">namespace\</span><span style="color: #0000BB">func</span><span style="color: #007700">();&nbsp;</span><span style="color: #FF8000">//&nbsp;calls&nbsp;function&nbsp;MyProject\func()<br /></span><span style="color: #007700">namespace\</span><span style="color: #0000BB">sub</span><span style="color: #007700">\</span><span style="color: #0000BB">func</span><span style="color: #007700">();&nbsp;</span><span style="color: #FF8000">//&nbsp;calls&nbsp;function&nbsp;MyProject\sub\func()<br /></span><span style="color: #007700">namespace\</span><span style="color: #0000BB">cname</span><span style="color: #007700">::</span><span style="color: #0000BB">method</span><span style="color: #007700">();&nbsp;</span><span style="color: #FF8000">//&nbsp;calls&nbsp;static&nbsp;method&nbsp;"method"&nbsp;of&nbsp;class&nbsp;MyProject\cname<br /></span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;namespace\</span><span style="color: #0000BB">sub</span><span style="color: #007700">\</span><span style="color: #0000BB">cname</span><span style="color: #007700">();&nbsp;</span><span style="color: #FF8000">//&nbsp;instantiates&nbsp;object&nbsp;of&nbsp;class&nbsp;MyProject\sub\cname<br /></span><span style="color: #0000BB">$b&nbsp;</span><span style="color: #007700">=&nbsp;namespace\</span><span style="color: #0000BB">CONSTANT</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;assigns&nbsp;value&nbsp;of&nbsp;constant&nbsp;MyProject\CONSTANT&nbsp;to&nbsp;$b<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

   </div>
   <div class="example" id="example-241">
    <p><strong>Example #5 the namespace operator, in global code</strong></p>
    <div class="example-contents">
     <div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /><br /></span><span style="color: #007700">namespace\</span><span style="color: #0000BB">func</span><span style="color: #007700">();&nbsp;</span><span style="color: #FF8000">//&nbsp;calls&nbsp;function&nbsp;func()<br /></span><span style="color: #007700">namespace\</span><span style="color: #0000BB">sub</span><span style="color: #007700">\</span><span style="color: #0000BB">func</span><span style="color: #007700">();&nbsp;</span><span style="color: #FF8000">//&nbsp;calls&nbsp;function&nbsp;sub\func()<br /></span><span style="color: #007700">namespace\</span><span style="color: #0000BB">cname</span><span style="color: #007700">::</span><span style="color: #0000BB">method</span><span style="color: #007700">();&nbsp;</span><span style="color: #FF8000">//&nbsp;calls&nbsp;static&nbsp;method&nbsp;"method"&nbsp;of&nbsp;class&nbsp;cname<br /></span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;namespace\</span><span style="color: #0000BB">sub</span><span style="color: #007700">\</span><span style="color: #0000BB">cname</span><span style="color: #007700">();&nbsp;</span><span style="color: #FF8000">//&nbsp;instantiates&nbsp;object&nbsp;of&nbsp;class&nbsp;sub\cname<br /></span><span style="color: #0000BB">$b&nbsp;</span><span style="color: #007700">=&nbsp;namespace\</span><span style="color: #0000BB">CONSTANT</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;assigns&nbsp;value&nbsp;of&nbsp;constant&nbsp;CONSTANT&nbsp;to&nbsp;$b<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

   </div>
  </p>
 </div><br /><br />
<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=language.namespaces.nsconstants&amp;redirect=@w{A9K9KDHR}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.namespaces.nsconstants&amp;redirect=@w{A9K9KDHR}">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>namespace keyword and __NAMESPACE__ constant</strong>
 </div><div id="allnotes">
 <a name="96010"></a>
 <div class="note">
  <strong class='user'>a dot schaffhirt at sedna-soft dot de</strong>
  <a href="#96010" class="date">02-Feb-2010 06:24</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Just in case you wonder what the practical use of the namespace keyword is...<br />
<br />
It can explicitly refer to classes from the current namespace regardless of possibly "use"d classes with the same name from other namespaces. However, this does not apply for functions.<br />
<br />
Example:<br />
<br />
<span class="default">&lt;?php<br />
namespace foo</span><span class="keyword">;<br />
class </span><span class="default">Xyz </span><span class="keyword">{}<br />
function </span><span class="default">abc </span><span class="keyword">() {}<br />
</span><span class="default">?&gt;<br />
</span><br />
<span class="default">&lt;?php<br />
namespace bar</span><span class="keyword">;<br />
class </span><span class="default">Xyz </span><span class="keyword">{}<br />
function </span><span class="default">abc </span><span class="keyword">() {}<br />
</span><span class="default">?&gt;<br />
</span><br />
<span class="default">&lt;?php<br />
namespace bar</span><span class="keyword">;<br />
use </span><span class="default">foo</span><span class="keyword">|</span><span class="default">Xyz</span><span class="keyword">;<br />
use </span><span class="default">foo</span><span class="keyword">|</span><span class="default">abc</span><span class="keyword">;<br />
new </span><span class="default">Xyz</span><span class="keyword">(); </span><span class="comment">// instantiates \foo\Xyz<br />
</span><span class="keyword">new </span><span class="default">namespace</span><span class="keyword">|</span><span class="default">Xyz</span><span class="keyword">(); </span><span class="comment">// instantiates \bar\Xyz<br />
</span><span class="default">abc</span><span class="keyword">(); </span><span class="comment">// invokes \bar\abc regardless of the second use statement<br />
</span><span class="keyword">|</span><span class="default">foo</span><span class="keyword">|</span><span class="default">abc</span><span class="keyword">(); </span><span class="comment">// it has to be invoked using the fully qualified name<br />
</span><span class="default">?&gt;<br />
</span><br />
(Sorry, I had to use "|" instead of "\", as it was always discarded in the preview, except within a comment.)<br />
<br />
Hope, this can save someone from some trouble.<br />
<br />
Best regards.</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=language.namespaces.nsconstants&amp;redirect=@w{A9K9KDHR}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.namespaces.nsconstants&amp;redirect=@w{A9K9KDHR}">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/language.namespaces.nsconstants.php">show source</a> |
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