<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Defining namespaces - Manual</title>
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
 <link rel="prev" href="language.namespaces.rationale.php" />
 <link rel="next" href="language.namespaces.nested.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/namespaces.definition" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/language.namespaces.definition.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="@w{J627WNRP}" />
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
 <li class="active"><a href="language.namespaces.definition.php">Defining namespaces</a></li>
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
  <a href="language.namespaces.nested.php">Declaring sub-namespaces<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.namespaces.rationale.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Namespaces overview</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.namespaces.definition.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/language.namespaces.definition.php">Brazilian Portuguese</option>
    <option value="zh/language.namespaces.definition.php">Chinese (Simplified)</option>
    <option value="fr/language.namespaces.definition.php">French</option>
    <option value="de/language.namespaces.definition.php">German</option>
    <option value="ja/language.namespaces.definition.php">Japanese</option>
    <option value="pl/language.namespaces.definition.php">Polish</option>
    <option value="ro/language.namespaces.definition.php">Romanian</option>
    <option value="ru/language.namespaces.definition.php">Russian</option>
    <option value="fa/language.namespaces.definition.php">Persian</option>
    <option value="es/language.namespaces.definition.php">Spanish</option>
    <option value="tr/language.namespaces.definition.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="language.namespaces.definition" class="sect1">
  <h2 class="title">Defining namespaces</h2>
  <p class="verinfo">(PHP 5 &gt;= 5.3.0)</p>
  <p class="para">
   Although any valid PHP code can be contained within a namespace, only four
   types of code are affected by namespaces: classes, interfaces, functions and constants.
  </p>
  <p class="para">
   Namespaces are declared using the <em>namespace</em>
   keyword.  A file containing a namespace must declare the namespace
   at the top of the file before any other code - with one exception: the
   <a href="control-structures.declare.php" class="xref">declare</a> keyword.
   <div class="example" id="example-227">
    <p><strong>Example #1 Declaring a single namespace</strong></p>
    <div class="example-contents">
     <div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">namespace&nbsp;</span><span style="color: #0000BB">MyProject</span><span style="color: #007700">;<br /><br />const&nbsp;</span><span style="color: #0000BB">CONNECT_OK&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">1</span><span style="color: #007700">;<br />class&nbsp;</span><span style="color: #0000BB">Connection&nbsp;</span><span style="color: #007700">{&nbsp;</span><span style="color: #FF8000">/*&nbsp;...&nbsp;*/&nbsp;</span><span style="color: #007700">}<br />function&nbsp;</span><span style="color: #0000BB">connect</span><span style="color: #007700">()&nbsp;{&nbsp;</span><span style="color: #FF8000">/*&nbsp;...&nbsp;*/&nbsp;&nbsp;</span><span style="color: #007700">}<br /><br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

   </div>
   The only code construct allowed before a namespace declaration is the
   <em>declare</em> statement, for defining encoding of a source file.  In addition,
   no non-PHP code may precede a namespace declaration, including extra whitespace:
   <div class="example" id="example-228">
    <p><strong>Example #2 Declaring a single namespace</strong></p>
    <div class="example-contents">
     <div class="phpcode"><code><span style="color: #000000">
&lt;html&gt;<br /><span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">namespace&nbsp;</span><span style="color: #0000BB">MyProject</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;fatal&nbsp;error&nbsp;-&nbsp;namespace&nbsp;must&nbsp;be&nbsp;the&nbsp;first&nbsp;statement&nbsp;in&nbsp;the&nbsp;script<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

   </div>
  </p>
  <p class="para">
   In addition, unlike any other PHP construct, the same namespace may be defined
   in multiple files, allowing splitting up of a namespace&#039;s contents across the filesystem.
  </p>
 </div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="language.namespaces.nested.php">Declaring sub-namespaces<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.namespaces.rationale.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Namespaces overview</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.namespaces.definition.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=language.namespaces.definition&amp;redirect=@w{J627WNRP}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.namespaces.definition&amp;redirect=@w{J627WNRP}">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Defining namespaces</strong>
 </div><div id="allnotes">
 <a name="109008"></a>
 <div class="note">
  <strong class='user'>parsmizban.com</strong>
  <a href="#109008" class="date">12-Jun-2012 11:02</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
You can use this as a namespace declaration:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">declare(</span><span class="default">encoding</span><span class="keyword">=</span><span class="string">'UTF-8'</span><span class="keyword">);<br />
</span><span class="default">namespace parsmizban</span><span class="keyword">;<br />
<br />
echo </span><span class="string">"parsmizban"</span><span class="keyword">;<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="93882"></a>
 <div class="note">
  <strong class='user'>huskyr at gmail dot com</strong>
  <a href="#93882" class="date">05-Oct-2009 04:20</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
"A file containing a namespace must declare the namespace at the top of the file before any other code"<br />
<br />
It might be obvious, but this means that you *can* include comments and white spaces before the namespace keyword.<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="comment">// Lots <br />
// of<br />
// interesting<br />
// comments and white space<br />
<br />
</span><span class="default">namespace Foo</span><span class="keyword">;<br />
class </span><span class="default">Bar </span><span class="keyword">{<br />
}<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="92212"></a>
 <div class="note">
  <strong class='user'>jeremeamia at gmail dot com</strong>
  <a href="#92212" class="date">14-Jul-2009 08:43</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
You should not try to create namespaces that use PHP keywords. These will cause parse errors. <br />
<br />
Examples:<br />
<br />
<span class="default">&lt;?php<br />
namespace Project</span><span class="keyword">/</span><span class="default">Classes</span><span class="keyword">/Function; </span><span class="comment">// Causes parse errors<br />
</span><span class="default">namespace Project</span><span class="keyword">/Abstract/</span><span class="default">Factory</span><span class="keyword">; </span><span class="comment">// Causes parse errors<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="90283"></a>
 <div class="note">
  <strong class='user'>danbettles at yahoo dot co dot uk</strong>
  <a href="#90283" class="date">14-Apr-2009 12:02</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Regarding constants defined with define() inside namespaces...<br />
<br />
define() will define constants exactly as specified.&nbsp; So, if you want to define a constant in a namespace, you will need to specify the namespace in your call to define(), even if you're calling define() from within a namespace.&nbsp; The following examples will make it clear.<br />
<br />
The following code will define the constant "MESSAGE" in the global namespace (i.e. "\MESSAGE").<br />
<br />
<span class="default">&lt;?php<br />
namespace test</span><span class="keyword">;<br />
</span><span class="default">define</span><span class="keyword">(</span><span class="string">'MESSAGE'</span><span class="keyword">, </span><span class="string">'Hello world!'</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span><br />
The following code will define two constants in the "test" namespace.<br />
<br />
<span class="default">&lt;?php<br />
namespace test</span><span class="keyword">;<br />
</span><span class="default">define</span><span class="keyword">(</span><span class="string">'test\HELLO'</span><span class="keyword">, </span><span class="string">'Hello world!'</span><span class="keyword">);<br />
</span><span class="default">define</span><span class="keyword">(</span><span class="default">__NAMESPACE__ </span><span class="keyword">. </span><span class="string">'\GOODBYE'</span><span class="keyword">, </span><span class="string">'Goodbye cruel world!'</span><span class="keyword">);<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="85589"></a>
 <div class="note">
  <strong class='user'>David Drakard</strong>
  <a href="#85589" class="date">07-Sep-2008 05:56</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I agree with SR, the new namespaces feature has solved a number of problems for me which would have required horrible coding to solve otherwise.<br />
<br />
An example use:<br />
Say you are making a small script, and write a class to connect to a database, calling it 'connection'. If you find your script useful and gradually expand it into a large application, you may want to rename the class. Without namespaces, you have to change the name and every reference to it (say in inheriting objects), possibly creating a load of bugs. With namespaces you can drop the related classes into a namespace with one line of code, and less chance of errors.<br />
<br />
This is by no means one of the biggest problems namespaces solve; I would suggest reading about their advantages before citicising them. They provide an elegant solutions to several problems involved in creating complex systems.</span>
</code></div>
  </div>
 </div>
 <a name="83194"></a>
 <div class="note">
  <strong class='user'>Baptiste</strong>
  <a href="#83194" class="date">14-May-2008 12:47</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
There is nothing wrong with PHP namespaces, except that those 2 instructions give a false impression of package management.<br />
... while they just correspond to the "with()" instruction of Javascript.<br />
<br />
By contrast, a package is a namespace for its members, but it offers more (like deployment facilities), and a compiler knows exactly what classes are in a package, and where to find them.</span>
</code></div>
  </div>
 </div>
 <a name="82222"></a>
 <div class="note">
  <strong class='user'>Anonymous</strong>
  <a href="#82222" class="date">01-Apr-2008 11:11</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
@ RS: Also, you can specify how your __autoload() function looks for the files. That way another users namespace classes cannot overwrite yours unless they replace your file specifically.</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=language.namespaces.definition&amp;redirect=@w{J627WNRP}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.namespaces.definition&amp;redirect=@w{J627WNRP}">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/language.namespaces.definition.php">show source</a> |
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