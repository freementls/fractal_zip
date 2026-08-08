<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Namespaces overview - Manual</title>
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
 <link rel="prev" href="language.namespaces.php" />
 <link rel="next" href="language.namespaces.definition.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/namespaces.rationale" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/language.namespaces.rationale.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="@w{ET54EMVM}" />
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
 <li class="active"><a href="language.namespaces.rationale.php">Namespaces overview</a></li>
 <li><a href="language.namespaces.definition.php">Defining namespaces</a></li>
 <li><a href="language.namespaces.nested.php">Declaring sub-namespaces</a></li>
 <li><a href="language.namespaces.definitionmultiple.php">Defining multiple namespaces in the same file</a></li>
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
  <a href="language.namespaces.definition.php">Defining namespaces<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.namespaces.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Namespaces</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.namespaces.rationale.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/language.namespaces.rationale.php">Brazilian Portuguese</option>
    <option value="zh/language.namespaces.rationale.php">Chinese (Simplified)</option>
    <option value="fr/language.namespaces.rationale.php">French</option>
    <option value="de/language.namespaces.rationale.php">German</option>
    <option value="ja/language.namespaces.rationale.php">Japanese</option>
    <option value="pl/language.namespaces.rationale.php">Polish</option>
    <option value="ro/language.namespaces.rationale.php">Romanian</option>
    <option value="ru/language.namespaces.rationale.php">Russian</option>
    <option value="fa/language.namespaces.rationale.php">Persian</option>
    <option value="es/language.namespaces.rationale.php">Spanish</option>
    <option value="tr/language.namespaces.rationale.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="language.namespaces.rationale" class="sect1">
  <h2 class="title">Namespaces overview</h2>
  <p class="verinfo">(PHP 5 &gt;= 5.3.0)</p>
  <p class="simpara">
   What are namespaces? In the broadest definition namespaces are a way of encapsulating
   items.  This can be seen as an abstract concept in many places. For example, in any
   operating system directories serve to group related files, and act as a namespace for
   the files within them. As a concrete example, the file <em>foo.txt</em> can
   exist in both directory <em>/home/greg</em> and in <em>/home/other</em>,
   but two copies of <em>foo.txt</em> cannot co-exist in the same directory. In
   addition, to access the <em>foo.txt</em> file outside of the
   <em>/home/greg</em> directory, we must prepend the directory name to the file
   name using the directory separator to get <em>/home/greg/foo.txt</em>. This
   same principle extends to namespaces in the programming world.
  </p>
  <p class="simpara">
   In the PHP world, namespaces are designed to solve two problems that authors
   of libraries and applications encounter when creating re-usable code elements
   such as classes or functions:
  </p>
  <p class="para">
   <ol type="1">
    <li class="listitem">
     <span class="simpara">
      Name collisions between code you create, and
      internal PHP classes/functions/constants or third-party classes/functions/constants.
     </span>
    </li>
    <li class="listitem">
     <span class="simpara">
      Ability to alias (or shorten) Extra_Long_Names designed to alleviate the first problem,
      improving readability of source code.
     </span>
    </li>
   </ol>
  </p>
  <p class="simpara">
   PHP Namespaces provide a way in which to group related classes, interfaces, 
   functions and constants.  Here is an example of namespace syntax in PHP:
  </p>
  <div class="example" id="example-226">
   <p><strong>Example #1 Namespace syntax example</strong></p>
   <div class="example-contents">
   <div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">namespace&nbsp;</span><span style="color: #0000BB">my</span><span style="color: #007700">\</span><span style="color: #0000BB">name</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;see&nbsp;"Defining&nbsp;Namespaces"&nbsp;section<br /><br /></span><span style="color: #007700">class&nbsp;</span><span style="color: #0000BB">MyClass&nbsp;</span><span style="color: #007700">{}<br />function&nbsp;</span><span style="color: #0000BB">myfunction</span><span style="color: #007700">()&nbsp;{}<br />const&nbsp;</span><span style="color: #0000BB">MYCONST&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">1</span><span style="color: #007700">;<br /><br /></span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">MyClass</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">$c&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;\</span><span style="color: #0000BB">my</span><span style="color: #007700">\</span><span style="color: #0000BB">name</span><span style="color: #007700">\</span><span style="color: #0000BB">MyClass</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;see&nbsp;"Global&nbsp;Space"&nbsp;section<br /><br /></span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">strlen</span><span style="color: #007700">(</span><span style="color: #DD0000">'hi'</span><span style="color: #007700">);&nbsp;</span><span style="color: #FF8000">//&nbsp;see&nbsp;"Using&nbsp;namespaces:&nbsp;fallback&nbsp;to&nbsp;global<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;//&nbsp;function/constant"&nbsp;section<br /><br /></span><span style="color: #0000BB">$d&nbsp;</span><span style="color: #007700">=&nbsp;namespace\</span><span style="color: #0000BB">MYCONST</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;see&nbsp;"namespace&nbsp;operator&nbsp;and&nbsp;__NAMESPACE__<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;//&nbsp;constant"&nbsp;section<br /></span><span style="color: #0000BB">$d&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">__NAMESPACE__&nbsp;</span><span style="color: #007700">.&nbsp;</span><span style="color: #DD0000">'\MYCONST'</span><span style="color: #007700">;<br />echo&nbsp;</span><span style="color: #0000BB">constant</span><span style="color: #007700">(</span><span style="color: #0000BB">$d</span><span style="color: #007700">);&nbsp;</span><span style="color: #FF8000">//&nbsp;see&nbsp;"Namespaces&nbsp;and&nbsp;dynamic&nbsp;language&nbsp;features"&nbsp;section<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
   </div>

  </div>
  <blockquote class="note"><p><strong class="note">Note</strong>: 
   <p class="para">
    Namespace names <em>PHP</em> and <em>php</em>, and compound names starting
    with these names (like <em>PHP\Classes</em>) are reserved for internal language use 
    and should not be used in the userspace code. 
   </p>
  </p></blockquote>
 </div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="language.namespaces.definition.php">Defining namespaces<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.namespaces.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Namespaces</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.namespaces.rationale.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=language.namespaces.rationale&amp;redirect=@w{ET54EMVM}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.namespaces.rationale&amp;redirect=@w{ET54EMVM}">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Namespaces overview</strong>
 </div><div id="allnotes">
 <a name="104892"></a>
 <div class="note">
  <strong class='user'>Anonymous</strong>
  <a href="#104892" class="date">14-Jul-2011 04:14</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
PHP Namespaces do not only provide a way in which to group related classes, functions and constants, but also a way in which to group related classes, interfaces, functions and constants.</span>
</code></div>
  </div>
 </div>
 <a name="104196"></a>
 <div class="note">
  <strong class='user'>Dmitry Snytkine</strong>
  <a href="#104196" class="date">31-May-2011 06:54</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Just a note: namespace (even nested or sub-namespace) cannot be just a number, it must start with a letter.<br />
For example, lets say you want to use namespace for versioning of your packages or versioning of your API:<br />
<br />
namespace Mynamespace\1;&nbsp; // Illegal<br />
Instead use this:<br />
namespace Mynamespace\v1; // OK</span>
</code></div>
  </div>
 </div>
 <a name="102662"></a>
 <div class="note">
  <strong class='user'>SteveWa</strong>
  <a href="#102662" class="date">27-Feb-2011 08:45</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Thought this might help other newbies like me...<br />
<br />
Name collisions means: <br />
you create a function named db_connect, and somebody elses code that you use in your file (i.e. an include) has the same function with the same name.<br />
<br />
To get around that problem, you rename your function SteveWa_db_connect&nbsp; which makes your code longer and harder to read.<br />
<br />
Now you can use namespaces to keep your function name separate from anyone else's function name, and you won't have to make extra_long_named functions to get around the name collision problem.<br />
<br />
So a namespace is like a pointer to a file path where you can find the source of the function you are working with</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=language.namespaces.rationale&amp;redirect=@w{ET54EMVM}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.namespaces.rationale&amp;redirect=@w{ET54EMVM}">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/language.namespaces.rationale.php">show source</a> |
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