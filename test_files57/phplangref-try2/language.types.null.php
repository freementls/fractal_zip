<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: NULL - Manual</title>
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
 <link rel="index" href="language.types.php" />
 <link rel="prev" href="language.types.resource.php" />
 <link rel="next" href="language.types.callable.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/types.null" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/language.types.null.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/language.types.null.php" />
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
 <li class="header up"><a href="language.types.php">Types</a></li>
 <li><a href="language.types.intro.php">Introduction</a></li>
 <li><a href="language.types.boolean.php">Booleans</a></li>
 <li><a href="language.types.integer.php">Integers</a></li>
 <li><a href="language.types.float.php">Floating point numbers</a></li>
 <li><a href="language.types.string.php">Strings</a></li>
 <li><a href="language.types.array.php">Arrays</a></li>
 <li><a href="language.types.object.php">Objects</a></li>
 <li><a href="language.types.resource.php">Resources</a></li>
 <li class="active"><a href="language.types.null.php">NULL</a></li>
 <li><a href="language.types.callable.php">Callbacks</a></li>
 <li><a href="language.pseudo-types.php">Pseudo-types and variables used in this documentation</a></li>
 <li><a href="language.types.type-juggling.php">Type Juggling</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="language.types.callable.php">Callbacks<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.types.resource.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Resources</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.types.null.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/language.types.null.php">Brazilian Portuguese</option>
    <option value="zh/language.types.null.php">Chinese (Simplified)</option>
    <option value="fr/language.types.null.php">French</option>
    <option value="de/language.types.null.php">German</option>
    <option value="ja/language.types.null.php">Japanese</option>
    <option value="pl/language.types.null.php">Polish</option>
    <option value="ro/language.types.null.php">Romanian</option>
    <option value="ru/language.types.null.php">Russian</option>
    <option value="fa/language.types.null.php">Persian</option>
    <option value="es/language.types.null.php">Spanish</option>
    <option value="tr/language.types.null.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="language.types.null" class="sect1">
 <h2 class="title">NULL</h2>
  
 <p class="para">
  The special <strong><code>NULL</code></strong> value represents a variable with no value. <strong><code>NULL</code></strong> is the
  only possible value of type <span class="type"><a href="language.types.null.php" class="type NULL">NULL</a></span>.
 </p>

 <p class="para">
  A variable is considered to be <span class="type"><a href="language.types.null.php" class="type null">null</a></span> if:
 </p>

 <ul class="itemizedlist">
  <li class="listitem">
   <p class="para">
    it has been assigned the constant <strong><code>NULL</code></strong>.
   </p>
  </li>
  <li class="listitem">
   <p class="para">
    it has not been set to any value yet.
   </p>
  </li>
  <li class="listitem">
   <p class="para">
    it has been  <span class="function"><a href="function.unset.php" class="function">unset()</a></span>.
   </p>
  </li>
 </ul>
  
 <div class="sect2" id="language.types.null.syntax">
  <h3 class="title">Syntax</h3>

  <p class="para">
   There is only one value of type <span class="type"><a href="language.types.null.php" class="type null">null</a></span>, and that is the
   case-insensitive constant <strong><code>NULL</code></strong>.
  </p>

  <div class="informalexample">
   <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br />$var&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">NULL</span><span style="color: #007700">;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
   </div>

  </div>

  <p class="para">
   See also the functions  <span class="function"><a href="function.is-null.php" class="function">is_null()</a></span> and
    <span class="function"><a href="function.unset.php" class="function">unset()</a></span>.
  </p>

 </div>
 
 <div class="sect2" id="language.types.null.casting">
  <h3 class="title">Casting to <em>NULL</em></h3>

  <p class="para">
   Casting a variable to <span class="type"><a href="language.types.null.php" class="type null">null</a></span> using <em>(unset) $var</em>
   will <em class="emphasis">not</em> remove the variable or unset its value.
   It will only return a <strong><code>NULL</code></strong> value.
  </p>

 </div>

</div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="language.types.callable.php">Callbacks<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.types.resource.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Resources</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.types.null.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=language.types.null&amp;redirect=http://www.php.net/manual/en/language.types.null.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.types.null&amp;redirect=http://www.php.net/manual/en/language.types.null.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>NULL</strong>
 </div><div id="allnotes">
 <a name="104216"></a>
 <div class="note">
  <strong class='user'>ryan at trezshard dot com</strong>
  <a href="#104216" class="date">01-Jun-2011 08:31</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
This simple shorthand seems to work for setting new variables to NULL:<br />
<br />
<span class="default">&lt;?php<br />
$Var</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
The above code will set $Var to NULL<br />
<br />
UPDATE: After further testing it appears the code only works in the global scope and does not work inside functions.<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">Example</span><span class="keyword">(){<br />
&nbsp; </span><span class="default">$Var</span><span class="keyword">;<br />
&nbsp; </span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$Var</span><span class="keyword">);<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
Would not work as expected.</span>
</code></div>
  </div>
 </div>
 <a name="103620"></a>
 <div class="note">
  <strong class='user'>quickpick</strong>
  <a href="#103620" class="date">22-Apr-2011 03:36</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Note: empty array is converted to null by non-strict equal '==' comparison. Use is_null() or '===' if there is possible of getting empty array.<br />
<br />
$a = array();<br />
<br />
$a == null&nbsp; &lt;== return true<br />
$a === null &lt; == return false<br />
is_null($a) &lt;== return false</span>
</code></div>
  </div>
 </div>
 <a name="77937"></a>
 <div class="note">
  <strong class='user'>james</strong>
  <a href="#77937" class="date">20-Sep-2007 08:25</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
A little speed test:<br />
<br />
<span class="default">&lt;?php<br />
$v </span><span class="keyword">= </span><span class="default">NULL</span><span class="keyword">;<br />
<br />
</span><span class="default">$s </span><span class="keyword">= </span><span class="default">microtime</span><span class="keyword">(</span><span class="default">TRUE</span><span class="keyword">);<br />
for(</span><span class="default">$i</span><span class="keyword">=</span><span class="default">0</span><span class="keyword">; </span><span class="default">$i</span><span class="keyword">&lt;</span><span class="default">1000</span><span class="keyword">; </span><span class="default">$i</span><span class="keyword">++) {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">is_null</span><span class="keyword">(</span><span class="default">$v</span><span class="keyword">);<br />
}<br />
print </span><span class="default">microtime</span><span class="keyword">(</span><span class="default">TRUE</span><span class="keyword">)-</span><span class="default">$s</span><span class="keyword">;<br />
print </span><span class="string">"&lt;br&gt;"</span><span class="keyword">;<br />
<br />
</span><span class="default">$s </span><span class="keyword">= </span><span class="default">microtime</span><span class="keyword">(</span><span class="default">TRUE</span><span class="keyword">);<br />
for(</span><span class="default">$i</span><span class="keyword">=</span><span class="default">0</span><span class="keyword">; </span><span class="default">$i</span><span class="keyword">&lt;</span><span class="default">1000</span><span class="keyword">; </span><span class="default">$i</span><span class="keyword">++) {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$v</span><span class="keyword">===</span><span class="default">NULL</span><span class="keyword">;<br />
}<br />
print </span><span class="default">microtime</span><span class="keyword">(</span><span class="default">TRUE</span><span class="keyword">)-</span><span class="default">$s</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
Results:<br />
<br />
0.017982006072998<br />
0.0005950927734375<br />
<br />
Using "===" is 30x quicker than is_null().</span>
</code></div>
  </div>
 </div>
 <a name="76290"></a>
 <div class="note">
  <strong class='user'>nl-x at bita dot nl</strong>
  <a href="#76290" class="date">09-Jul-2007 10:33</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Watch out. You can define a new constant with the name NULL with define("NULL","FOO");. But you must use the function constant("NULL"); to get it's value. NULL without the function call to the constant() function will still retrieve the special type NULL value.<br />
Within a class there is no problem, as const NULL="Foo"; will be accessible as myClass::NULL.</span>
</code></div>
  </div>
 </div>
 <a name="66688"></a>
 <div class="note">
  <strong class='user'>cdcchen at hotmail dot com</strong>
  <a href="#66688" class="date">25-May-2006 08:17</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
empty() is_null() !isset()<br />
<br />
$var = "";<br />
<br />
empty($var) is true.<br />
is_null($var) is false.<br />
!isset($var) is false.</span>
</code></div>
  </div>
 </div>
 <a name="60417"></a>
 <div class="note">
  <a href="#60417" class="date">06-Jan-2006 01:51</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
// Difference between "unset($a);" and "$a = NULL;" :<br />
<span class="default">&lt;?php<br />
</span><span class="comment">// unset($a)<br />
</span><span class="default">$a </span><span class="keyword">= </span><span class="default">5</span><span class="keyword">;<br />
</span><span class="default">$b </span><span class="keyword">= &amp; </span><span class="default">$a</span><span class="keyword">;<br />
unset(</span><span class="default">$a</span><span class="keyword">);<br />
print </span><span class="string">"b $b "</span><span class="keyword">; </span><span class="comment">// b 5 <br />
<br />
// $a = NULL; (better I think)<br />
</span><span class="default">$a </span><span class="keyword">= </span><span class="default">5</span><span class="keyword">;<br />
</span><span class="default">$b </span><span class="keyword">= &amp; </span><span class="default">$a</span><span class="keyword">;<br />
</span><span class="default">$a </span><span class="keyword">= </span><span class="default">NULL</span><span class="keyword">;<br />
print </span><span class="string">"b $b "</span><span class="keyword">; </span><span class="comment">// b <br />
</span><span class="keyword">print(! isset(</span><span class="default">$b</span><span class="keyword">)); </span><span class="comment">// 1 <br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="55128"></a>
 <div class="note">
  <strong class='user'>poutri_j at epitech dot net</strong>
  <a href="#55128" class="date">26-Jul-2005 04:56</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
if you declare something like this :<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">toto<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public </span><span class="default">$a </span><span class="keyword">= array();<br />
<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">load</span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if (</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">a </span><span class="keyword">== </span><span class="default">null</span><span class="keyword">) </span><span class="comment">// ==&gt; the result is true<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$a </span><span class="keyword">= </span><span class="default">other_func</span><span class="keyword">();<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
be carefull, that's strange but an empty array is considered as a null variable</span>
</code></div>
  </div>
 </div>
 <a name="46644"></a>
 <div class="note">
  <strong class='user'>rizwan_nawaz786 at hotmail dot com</strong>
  <a href="#46644" class="date">18-Oct-2004 09:22</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Hi<br />
&nbsp;Rizwan Here<br />
&nbsp;&nbsp; <br />
&nbsp;&nbsp; Null is the Constant in PHP. it is use to assign a empty value to the variable like<br />
<br />
&nbsp; $a=NULL;<br />
<br />
&nbsp; At this time $a has is NULL or $a has no value;<br />
<br />
&nbsp; When we declaire a veriable in other languages than that veriable has some value depending on the value of memory location at which it is pointed but in php when we declaire a veriable than php assign a NULL to a veriable.</span>
</code></div>
  </div>
 </div>
 <a name="16766"></a>
 <div class="note">
  <strong class='user'>dward at maidencreek dot com</strong>
  <a href="#16766" class="date">12-Nov-2001 03:52</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Nulls are almost the same as unset variables and it is hard to tell the difference without creating errors from the interpreter:<br />
<br />
<span class="default">&lt;?php<br />
$var </span><span class="keyword">= </span><span class="default">NULL</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
isset($var) is FALSE<br />
empty($var) is TRUE<br />
is_null($var) is TRUE<br />
<br />
isset($novar) is FALSE<br />
empty($novar) is TRUE<br />
is_null($novar) gives an Undefined variable error<br />
<br />
$var IS in the symbol table (from get_defined_vars())<br />
$var CAN be used as an argument or an expression.<br />
<br />
So, in most cases I found that we needed to use !isset($var) intead of is_null($var) and then set $var = NULL if the variable needs to be used later to guarantee that $var is a valid variable with a NULL value instead of being undefined.</span>
</code></div>
  </div>
 </div>
 <a name="15987"></a>
 <div class="note">
  <strong class='user'>tbdavis at greyshirt dot net</strong>
  <a href="#15987" class="date">11-Oct-2001 04:36</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Unlike the relational model, NULL in PHP has the following properties:<br />
NULL == NULL is true,<br />
NULL == FALSE is true.<br />
And in line with the relational model, NULL == TRUE fails.</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=language.types.null&amp;redirect=http://www.php.net/manual/en/language.types.null.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.types.null&amp;redirect=http://www.php.net/manual/en/language.types.null.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/language.types.null.php">show source</a> |
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