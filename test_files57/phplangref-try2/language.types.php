<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Types - Manual</title>
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
 <link rel="index" href="langref.php" />
 <link rel="prev" href="language.basic-syntax.comments.php" />
 <link rel="next" href="language.types.intro.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/types" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="alternate" href="/manual/en/feeds/language.types.atom" type="application/atom+xml" />
 <link rel="canonical" href="http://php.net/manual/en/language.types.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/language.types.php" />
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
 <li><a href="language.basic-syntax.php">Basic syntax</a></li>
 <li class="active"><a href="language.types.php">Types</a></li>
 <li><a href="language.variables.php">Variables</a></li>
 <li><a href="language.constants.php">Constants</a></li>
 <li><a href="language.expressions.php">Expressions</a></li>
 <li><a href="language.operators.php">Operators</a></li>
 <li><a href="language.control-structures.php">Control Structures</a></li>
 <li><a href="language.functions.php">Functions</a></li>
 <li><a href="language.oop5.php">Classes and Objects</a></li>
 <li><a href="language.namespaces.php">Namespaces</a></li>
 <li><a href="language.exceptions.php">Exceptions</a></li>
 <li><a href="language.references.php">References Explained</a></li>
 <li><a href="reserved.variables.php">Predefined Variables</a></li>
 <li><a href="reserved.exceptions.php">Predefined Exceptions</a></li>
 <li><a href="reserved.interfaces.php">Predefined Interfaces</a></li>
 <li><a href="context.php">Context options and parameters</a></li>
 <li><a href="wrappers.php">Supported Protocols and Wrappers</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="language.types.intro.php">Introduction<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.basic-syntax.comments.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Comments</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.types.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/language.types.php">Brazilian Portuguese</option>
    <option value="zh/language.types.php">Chinese (Simplified)</option>
    <option value="fr/language.types.php">French</option>
    <option value="de/language.types.php">German</option>
    <option value="ja/language.types.php">Japanese</option>
    <option value="pl/language.types.php">Polish</option>
    <option value="ro/language.types.php">Romanian</option>
    <option value="ru/language.types.php">Russian</option>
    <option value="fa/language.types.php">Persian</option>
    <option value="es/language.types.php">Spanish</option>
    <option value="tr/language.types.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="language.types" class="chapter">
 <h1>Types</h1>
<h2>Table of Contents</h2><ul class="chunklist chunklist_chapter"><li><a href="language.types.intro.php">Introduction</a></li><li><a href="language.types.boolean.php">Booleans</a></li><li><a href="language.types.integer.php">Integers</a></li><li><a href="language.types.float.php">Floating point numbers</a></li><li><a href="language.types.string.php">Strings</a></li><li><a href="language.types.array.php">Arrays</a></li><li><a href="language.types.object.php">Objects</a></li><li><a href="language.types.resource.php">Resources</a></li><li><a href="language.types.null.php">NULL</a></li><li><a href="language.types.callable.php">Callbacks</a></li><li><a href="language.pseudo-types.php">Pseudo-types and variables used in this documentation</a></li><li><a href="language.types.type-juggling.php">Type Juggling</a></li></ul>


 
 
 





 


 


 





 


 


 





 


 


 


 


 


 


 


 


 


 


 


 


 
</div>
<br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="language.types.intro.php">Introduction<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.basic-syntax.comments.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Comments</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.types.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=language.types&amp;redirect=http://www.php.net/manual/en/language.types.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.types&amp;redirect=http://www.php.net/manual/en/language.types.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Types</strong>
 </div><div id="allnotes">
 <a name="86617"></a>
 <div class="note">
  <strong class='user'>Jeffrey</strong>
  <a href="#86617" class="date">26-Oct-2008 10:31</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The Object (compound) Type<br />
<br />
Like every programming language, PHP offers the usual basic primitive types which can hold only one piece of data at a time (scalar). I am particularly fond of the "object" type (compound) because that allows me to group many basic PHP types together, and I can name it anything I want.<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">Person<br />
</span><span class="keyword">{<br />
&nbsp; </span><span class="default">$firstName</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// a PHP String<br />
&nbsp; </span><span class="default">$middleName</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="comment">// a PHP String<br />
&nbsp; </span><span class="default">$lastName</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="comment">// a PHP String<br />
&nbsp; </span><span class="default">$age</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// a PHP Integer<br />
&nbsp; </span><span class="default">$hasDriversLicense</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// a PHP Boolean <br />
</span><span class="keyword">}<br />
</span><span class="default">?&gt;<br />
</span><br />
Here, I have grouped several basic PHP types together, (3) Strings, (1) Integer, and (1) Boolean... then I named that group "Person". Since I used the proper syntax to do so, this code is pure PHP, which means that if you run this code, you would have an extra PHP "type" available to you in your scripts, like so:<br />
<br />
<span class="default">&lt;?php<br />
$myAge </span><span class="keyword">= </span><span class="default">16</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="comment">// a PHP Integer - always available<br />
</span><span class="default">$yourAge </span><span class="keyword">= </span><span class="default">15.5</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="comment">// a PHP Float&nbsp;&nbsp; - always available<br />
</span><span class="default">$hasHair </span><span class="keyword">= </span><span class="default">true</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="comment">// a PHP Boolean - always available<br />
</span><span class="default">$greeting </span><span class="keyword">= </span><span class="string">"Hello World!"&nbsp; &nbsp; &nbsp; </span><span class="comment">// a PHP String&nbsp; - always available<br />
<br />
</span><span class="default">$person </span><span class="keyword">= new </span><span class="default">Person</span><span class="keyword">();&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// a PHP Person&nbsp; - available NOW!<br />
</span><span class="default">?&gt;<br />
</span><br />
You can make your own object types and have PHP execute it as if it were part of the PHP language itself. See more on classes and objects in this manual at: <a href="@w{6EHQVFYQ}" rel="nofollow" target="_blank">@w{6EHQVFYQ}</a></span>
</code></div>
  </div>
 </div>
 <a name="59427"></a>
 <div class="note">
  <strong class='user'>arjini at gmail dot com</strong>
  <a href="#59427" class="date">06-Dec-2005 12:32</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Note that you can chain type castng:<br />
<br />
var_dump((string)(int)false); //string(1) "0"</span>
</code></div>
  </div>
 </div>
 <a name="51056"></a>
 <div class="note">
  <strong class='user'>shahnaz khan</strong>
  <a href="#51056" class="date">18-Mar-2005 04:40</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
if we use gettype() before initializinf any variable it give NULL<br />
for eg.<br />
<br />
<span class="default">&lt;?php<br />
$foo</span><span class="keyword">;<br />
echo </span><span class="default">gettype</span><span class="keyword">(</span><span class="default">$foo</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span><br />
it will show <br />
<br />
NULL</span>
</code></div>
  </div>
 </div>
 <a name="43671"></a>
 <div class="note">
  <strong class='user'>Trizor of www.freedom-uplink.org</strong>
  <a href="#43671" class="date">29-Jun-2004 06:14</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The differance of float and double dates back to a FORTRAN standard. In FORTRAN Variables aren't as loosly written as in PHP and you had to define variable types(OH NOES!). FLOAT or REAL*4 (For all you VAX people out there) defined the variable as a standard precision floating point, with 4 bytes of memory allocated to it. DOUBLE PRECISION or REAL*8 (Again for the VAX) was identical to FLOAT or REAL*4, but with an 8 byte allocation of memory instead of a 4 byte allocation.<br />
<br />
In fact most modern variable types date back to FORTRAN, except a string was called a CHARACHTER*N and you had to specify the length, or CHARACHTER*(*) for a variable length string. Boolean was LOGICAL, and there weren't yet objects, and there was support for complex numbers(a+bi).<br />
<br />
Of course, most people reading this are web programmers and could care less about the mathematical background of programming.<br />
<br />
NOTE: Object support was added to FORTRAN in the FORTRAN90 spec, and expanded with the FORTRAN94 spec, but by then C was the powerful force on the block, and most people who still use FORTRAN use the FORTRAN77.</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=language.types&amp;redirect=http://www.php.net/manual/en/language.types.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.types&amp;redirect=http://www.php.net/manual/en/language.types.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/language.types.php">show source</a> |
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