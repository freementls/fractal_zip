<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Booleans - Manual</title>
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
 <link rel="prev" href="language.types.intro.php" />
 <link rel="next" href="language.types.integer.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/types.boolean" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/language.types.boolean.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/language.types.boolean.php" />
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
 <li class="active"><a href="language.types.boolean.php">Booleans</a></li>
 <li><a href="language.types.integer.php">Integers</a></li>
 <li><a href="language.types.float.php">Floating point numbers</a></li>
 <li><a href="language.types.string.php">Strings</a></li>
 <li><a href="language.types.array.php">Arrays</a></li>
 <li><a href="language.types.object.php">Objects</a></li>
 <li><a href="language.types.resource.php">Resources</a></li>
 <li><a href="language.types.null.php">NULL</a></li>
 <li><a href="language.types.callable.php">Callbacks</a></li>
 <li><a href="language.pseudo-types.php">Pseudo-types and variables used in this documentation</a></li>
 <li><a href="language.types.type-juggling.php">Type Juggling</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="language.types.integer.php">Integers<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.types.intro.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Introduction</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.types.boolean.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/language.types.boolean.php">Brazilian Portuguese</option>
    <option value="zh/language.types.boolean.php">Chinese (Simplified)</option>
    <option value="fr/language.types.boolean.php">French</option>
    <option value="de/language.types.boolean.php">German</option>
    <option value="ja/language.types.boolean.php">Japanese</option>
    <option value="pl/language.types.boolean.php">Polish</option>
    <option value="ro/language.types.boolean.php">Romanian</option>
    <option value="ru/language.types.boolean.php">Russian</option>
    <option value="fa/language.types.boolean.php">Persian</option>
    <option value="es/language.types.boolean.php">Spanish</option>
    <option value="tr/language.types.boolean.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="language.types.boolean" class="sect1">
 <h2 class="title">Booleans</h2>
 
 <p class="simpara">
  This is the simplest type. A <span class="type"><a href="language.types.boolean.php" class="type boolean">boolean</a></span> expresses a truth value. It
  can be either <strong><code>TRUE</code></strong> or <strong><code>FALSE</code></strong>. 
 </p>

 <div class="sect2" id="language.types.boolean.syntax">
  <h3 class="title">Syntax</h3>
  <p class="para">
   To specify a <span class="type"><a href="language.types.boolean.php" class="type boolean">boolean</a></span> literal, use the keywords <strong><code>TRUE</code></strong> or
   <strong><code>FALSE</code></strong>. Both are case-insensitive.
  </p>

  <div class="informalexample">
   <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br />$foo&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">True</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;assign&nbsp;the&nbsp;value&nbsp;TRUE&nbsp;to&nbsp;$foo<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
   </div>

  </div>

  <p class="para">
   Typically, the result of an <a href="language.operators.php" class="link">operator</a>
   which returns a <span class="type"><a href="language.types.boolean.php" class="type boolean">boolean</a></span> value is passed on to a
   <a href="language.control-structures.php" class="link">control structure</a>.
  </p>

  <div class="informalexample">
   <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #FF8000">//&nbsp;==&nbsp;is&nbsp;an&nbsp;operator&nbsp;which&nbsp;tests<br />//&nbsp;equality&nbsp;and&nbsp;returns&nbsp;a&nbsp;boolean<br /></span><span style="color: #007700">if&nbsp;(</span><span style="color: #0000BB">$action&nbsp;</span><span style="color: #007700">==&nbsp;</span><span style="color: #DD0000">"show_version"</span><span style="color: #007700">)&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"The&nbsp;version&nbsp;is&nbsp;1.23"</span><span style="color: #007700">;<br />}<br /><br /></span><span style="color: #FF8000">//&nbsp;this&nbsp;is&nbsp;not&nbsp;necessary...<br /></span><span style="color: #007700">if&nbsp;(</span><span style="color: #0000BB">$show_separators&nbsp;</span><span style="color: #007700">==&nbsp;</span><span style="color: #0000BB">TRUE</span><span style="color: #007700">)&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"&lt;hr&gt;\n"</span><span style="color: #007700">;<br />}<br /><br /></span><span style="color: #FF8000">//&nbsp;...because&nbsp;this&nbsp;can&nbsp;be&nbsp;used&nbsp;with&nbsp;exactly&nbsp;the&nbsp;same&nbsp;meaning:<br /></span><span style="color: #007700">if&nbsp;(</span><span style="color: #0000BB">$show_separators</span><span style="color: #007700">)&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"&lt;hr&gt;\n"</span><span style="color: #007700">;<br />}<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
   </div>

  </div>
 </div>

 <div class="sect2" id="language.types.boolean.casting">
  <h3 class="title">Converting to boolean</h3>

  <p class="simpara">
   To explicitly convert a value to <span class="type"><a href="language.types.boolean.php" class="type boolean">boolean</a></span>, use the
   <em>(bool)</em> or <em>(boolean)</em> casts. However, in
   most cases the cast is unnecessary, since a value will be automatically
   converted if an operator, function or control structure requires a
   <span class="type"><a href="language.types.boolean.php" class="type boolean">boolean</a></span> argument.
  </p>

  <p class="simpara">
   See also <a href="language.types.type-juggling.php" class="link">Type Juggling</a>.
  </p>
   
  <p class="para">
   When converting to <span class="type"><a href="language.types.boolean.php" class="type boolean">boolean</a></span>, the following values are considered
   <strong><code>FALSE</code></strong>:
  </p>
  
  <ul class="itemizedlist">
   <li class="listitem">
    <span class="simpara">
     the <a href="language.types.boolean.php" class="link">boolean</a> <strong><code>FALSE</code></strong> itself
    </span>
   </li>
   <li class="listitem">
    <span class="simpara">
     the <a href="language.types.integer.php" class="link">integer</a> 0 (zero)
    </span>
   </li>
   <li class="listitem">
    <span class="simpara">
     the <a href="language.types.float.php" class="link">float</a> 0.0 (zero)
    </span>
   </li>
   <li class="listitem">
    <span class="simpara">
     the empty <a href="language.types.string.php" class="link">string</a>, and the
     <a href="language.types.string.php" class="link">string</a> &quot;0&quot;
    </span>
   </li>
   <li class="listitem">
    <span class="simpara">
     an <a href="language.types.array.php" class="link">array</a> with zero elements
    </span>
   </li>
   <li class="listitem">
    <span class="simpara">
     an <a href="language.types.object.php" class="link">object</a> with zero member
     variables (PHP 4 only)
    </span>
   </li>
   <li class="listitem">
    <span class="simpara">
     the special type <a href="language.types.null.php" class="link">NULL</a> (including
     unset variables)
    </span>
   </li>
   <li class="listitem">
    <span class="simpara">
     <a href="ref.simplexml.php" class="link">SimpleXML</a> objects created from empty
     tags
    </span>
   </li>
  </ul>
    
  <p class="para">
   Every other value is considered <strong><code>TRUE</code></strong> (including any
   <a href="language.types.resource.php" class="link">resource</a>).
  </p>
  
  <div class="warning"><strong class="warning">Warning</strong>
   <p class="simpara">
    <em>-1</em> is considered <strong><code>TRUE</code></strong>, like any other non-zero
    (whether negative or positive) number!
   </p>
  </div>
  
  <div class="informalexample">
   <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br />var_dump</span><span style="color: #007700">((bool)&nbsp;</span><span style="color: #DD0000">""</span><span style="color: #007700">);&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;bool(false)<br /></span><span style="color: #0000BB">var_dump</span><span style="color: #007700">((bool)&nbsp;</span><span style="color: #0000BB">1</span><span style="color: #007700">);&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;bool(true)<br /></span><span style="color: #0000BB">var_dump</span><span style="color: #007700">((bool)&nbsp;-</span><span style="color: #0000BB">2</span><span style="color: #007700">);&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;bool(true)<br /></span><span style="color: #0000BB">var_dump</span><span style="color: #007700">((bool)&nbsp;</span><span style="color: #DD0000">"foo"</span><span style="color: #007700">);&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;bool(true)<br /></span><span style="color: #0000BB">var_dump</span><span style="color: #007700">((bool)&nbsp;</span><span style="color: #0000BB">2.3e5</span><span style="color: #007700">);&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;bool(true)<br /></span><span style="color: #0000BB">var_dump</span><span style="color: #007700">((bool)&nbsp;array(</span><span style="color: #0000BB">12</span><span style="color: #007700">));&nbsp;</span><span style="color: #FF8000">//&nbsp;bool(true)<br /></span><span style="color: #0000BB">var_dump</span><span style="color: #007700">((bool)&nbsp;array());&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;bool(false)<br /></span><span style="color: #0000BB">var_dump</span><span style="color: #007700">((bool)&nbsp;</span><span style="color: #DD0000">"false"</span><span style="color: #007700">);&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;bool(true)<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
   </div>

  </div>

 </div>
</div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="language.types.integer.php">Integers<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.types.intro.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Introduction</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.types.boolean.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=language.types.boolean&amp;redirect=http://www.php.net/manual/en/language.types.boolean.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.types.boolean&amp;redirect=http://www.php.net/manual/en/language.types.boolean.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Booleans</strong>
 </div><div id="allnotes">
 <a name="105325"></a>
 <div class="note">
  <strong class='user'>pablo at loop-sistemas dot com dot ar</strong>
  <a href="#105325" class="date">09-Aug-2011 12:22</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
altough it may be obvious to some, special value NaN evaluates to true, as it not in the false list<br />
<br />
the same goes with INF and -INF</span>
</code></div>
  </div>
 </div>
 <a name="103757"></a>
 <div class="note">
  <strong class='user'>frank at interactinet dot com</strong>
  <a href="#103757" class="date">02-May-2011 10:15</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Be careful when assigning a value in the if statement, for example:<br />
<br />
&nbsp;if($var = $arg)<br />
<br />
$var might be assigned "1" instead of the expected value in $arg.<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">public function </span><span class="default">myMethod</span><span class="keyword">()<br />
{<br />
return </span><span class="string">'test'</span><span class="keyword">;<br />
}<br />
<br />
public function </span><span class="default">myOtherMethod</span><span class="keyword">()<br />
{<br />
return </span><span class="default">null</span><span class="keyword">;<br />
}<br />
<br />
if(</span><span class="default">$val </span><span class="keyword">= </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">myMethod</span><span class="keyword">())<br />
{<br />
&nbsp;</span><span class="comment">// $val might be 1 instead of the expected 'test'<br />
</span><span class="keyword">}<br />
<br />
if( (</span><span class="default">$val </span><span class="keyword">= </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">myMethod</span><span class="keyword">()) )<br />
{<br />
</span><span class="comment">// now $val should be 'test'<br />
</span><span class="keyword">}<br />
<br />
</span><span class="comment">// or to check for false<br />
</span><span class="keyword">if( !(</span><span class="default">$val </span><span class="keyword">= </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">myMethod</span><span class="keyword">()) )<br />
{<br />
</span><span class="comment">// this will not run since $val = 'test' and equates to true<br />
</span><span class="keyword">}<br />
<br />
</span><span class="comment">// this is an easy way to assign default value only if a value is not returned:<br />
<br />
</span><span class="keyword">if( !(</span><span class="default">$val </span><span class="keyword">= </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">myOtherMethod</span><span class="keyword">()) )<br />
{<br />
</span><span class="default">$val </span><span class="keyword">= </span><span class="string">'default'<br />
</span><span class="keyword">}<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="101180"></a>
 <div class="note">
  <strong class='user'>oscar at oveas dot com</strong>
  <a href="#101180" class="date">01-Dec-2010 03:17</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Dunno if someone else posted this solution already, but if not, here's a useful and function to convert strings to strict booleans.<br />
Note this one only checks for string and defaults to the PHP (boolean) cast where e.g. -1 returns true, but you easily add some elseifs for other datatypes.<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">toStrictBoolean </span><span class="keyword">(</span><span class="default">$_val</span><span class="keyword">, </span><span class="default">$_trueValues </span><span class="keyword">= array(</span><span class="string">'yes'</span><span class="keyword">, </span><span class="string">'y'</span><span class="keyword">, </span><span class="string">'true'</span><span class="keyword">), </span><span class="default">$_forceLowercase </span><span class="keyword">= </span><span class="default">true</span><span class="keyword">)<br />
{<br />
&nbsp;&nbsp;&nbsp; if (</span><span class="default">is_string</span><span class="keyword">(</span><span class="default">$_val</span><span class="keyword">)) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return (</span><span class="default">in_array</span><span class="keyword">(<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; (</span><span class="default">$_forceLowercase</span><span class="keyword">?</span><span class="default">strtolower</span><span class="keyword">(</span><span class="default">$_val</span><span class="keyword">):</span><span class="default">$_val</span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; , </span><span class="default">$_trueValues</span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; );<br />
&nbsp;&nbsp;&nbsp; } else {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return (boolean) </span><span class="default">$_val</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="100628"></a>
 <div class="note">
  <strong class='user'>ledadu at gmail dot com</strong>
  <a href="#100628" class="date">27-Oct-2010 08:56</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Function to sort array by elements and count of element (before php 5.3) (not use Lambda Functions, and Closures)<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="comment">//-----------------------------<br />
<br />
</span><span class="keyword">function </span><span class="default">arraySortByElements</span><span class="keyword">(</span><span class="default">$array2sort</span><span class="keyword">,</span><span class="default">$sortField</span><span class="keyword">,</span><span class="default">$order</span><span class="keyword">,</span><span class="default">$iscount</span><span class="keyword">=</span><span class="default">false</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$functionString</span><span class="keyword">=</span><span class="string">'<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if ('</span><span class="keyword">.(</span><span class="default">$iscount</span><span class="keyword">?</span><span class="string">'true'</span><span class="keyword">:</span><span class="string">'false'</span><span class="keyword">).</span><span class="string">'){<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; if(count($a["'</span><span class="keyword">.</span><span class="default">$sortField</span><span class="keyword">.</span><span class="string">'"]) &gt; count($b["'</span><span class="keyword">.</span><span class="default">$sortField</span><span class="keyword">.</span><span class="string">'"])) return 1*'</span><span class="keyword">.</span><span class="default">$order</span><span class="keyword">.</span><span class="string">';<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; if(count($a["'</span><span class="keyword">.</span><span class="default">$sortField</span><span class="keyword">.</span><span class="string">'"]) &lt; count($b["'</span><span class="keyword">.</span><span class="default">$sortField</span><span class="keyword">.</span><span class="string">'"])) return -1*'</span><span class="keyword">.</span><span class="default">$order</span><span class="keyword">.</span><span class="string">';<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; }else{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; if($a["'</span><span class="keyword">.</span><span class="default">$sortField</span><span class="keyword">.</span><span class="string">'"] &gt; $b["'</span><span class="keyword">.</span><span class="default">$sortField</span><span class="keyword">.</span><span class="string">'"]) return 1*'</span><span class="keyword">.</span><span class="default">$order</span><span class="keyword">.</span><span class="string">';<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; if($a["'</span><span class="keyword">.</span><span class="default">$sortField</span><span class="keyword">.</span><span class="string">'"] &lt; $b["'</span><span class="keyword">.</span><span class="default">$sortField</span><span class="keyword">.</span><span class="string">'"]) return -1*'</span><span class="keyword">.</span><span class="default">$order</span><span class="keyword">.</span><span class="string">';<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return 0;'</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; </span><span class="default">usort</span><span class="keyword">(</span><span class="default">$array2sort</span><span class="keyword">, </span><span class="default">create_function</span><span class="keyword">(</span><span class="string">'$a,$b'</span><span class="keyword">,</span><span class="default">$functionString</span><span class="keyword">));<br />
&nbsp;&nbsp; &nbsp; return </span><span class="default">$array2sort</span><span class="keyword">;<br />
}<br />
<br />
</span><span class="comment">//-----------------------------<br />
<br />
//init Array for testing :<br />
</span><span class="default">$testArray </span><span class="keyword">= array( <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; array(</span><span class="string">'name' </span><span class="keyword">=&gt; </span><span class="string">'Lenny'</span><span class="keyword">, </span><span class="string">'note' </span><span class="keyword">=&gt; </span><span class="default">5</span><span class="keyword">, </span><span class="string">'listId' </span><span class="keyword">=&gt; array(</span><span class="default">654</span><span class="keyword">,</span><span class="default">987</span><span class="keyword">,</span><span class="default">32165</span><span class="keyword">)), <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; array(</span><span class="string">'name' </span><span class="keyword">=&gt; </span><span class="string">'Olivier'</span><span class="keyword">, </span><span class="string">'note' </span><span class="keyword">=&gt;</span><span class="default">3</span><span class="keyword">, </span><span class="string">'listId' </span><span class="keyword">=&gt; array(</span><span class="default">2</span><span class="keyword">)), <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; array(</span><span class="string">'name' </span><span class="keyword">=&gt; </span><span class="string">'Gregory'</span><span class="keyword">, </span><span class="string">'note' </span><span class="keyword">=&gt; </span><span class="default">1</span><span class="keyword">, </span><span class="string">'listId' </span><span class="keyword">=&gt; array(</span><span class="default">45</span><span class="keyword">,</span><span class="default">58</span><span class="keyword">)), <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; array(</span><span class="string">'name' </span><span class="keyword">=&gt; </span><span class="string">'Clement'</span><span class="keyword">, </span><span class="string">'note' </span><span class="keyword">=&gt; </span><span class="default">2</span><span class="keyword">, </span><span class="string">'listId' </span><span class="keyword">=&gt; array(</span><span class="default">584</span><span class="keyword">,</span><span class="default">587</span><span class="keyword">,</span><span class="default">741</span><span class="keyword">,</span><span class="default">14781</span><span class="keyword">,</span><span class="default">147</span><span class="keyword">))<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; );<br />
<br />
</span><span class="comment">//sorted Arrays :<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$testArrayByNameASC </span><span class="keyword">= </span><span class="default">arraySortByElements</span><span class="keyword">(</span><span class="default">$testArray</span><span class="keyword">,</span><span class="string">'name'</span><span class="keyword">,</span><span class="default">1</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$testArrayByNoteDESC </span><span class="keyword">= </span><span class="default">arraySortByElements</span><span class="keyword">(</span><span class="default">$testArray</span><span class="keyword">,</span><span class="string">'note'</span><span class="keyword">,-</span><span class="default">1</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$testArrayByCountlistIdDESC </span><span class="keyword">= </span><span class="default">arraySortByElements</span><span class="keyword">(</span><span class="default">$testArray</span><span class="keyword">,</span><span class="string">'listId'</span><span class="keyword">,-</span><span class="default">1</span><span class="keyword">,</span><span class="default">true</span><span class="keyword">);<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="98807"></a>
 <div class="note">
  <strong class='user'>mobil dot boty at no dot spam dot gmail dot com</strong>
  <a href="#98807" class="date">08-Jul-2010 02:33</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">test</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; return </span><span class="default">TRUE</span><span class="keyword">;<br />
}<br />
<br />
</span><span class="default">$var </span><span class="keyword">= </span><span class="default">test</span><span class="keyword">();<br />
<br />
</span><span class="comment">// FASE instead of FALSE<br />
<br />
</span><span class="keyword">if (</span><span class="default">$var </span><span class="keyword">== </span><span class="default">FASE</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; echo </span><span class="string">'function returned FALSE'</span><span class="keyword">;<br />
} else {<br />
&nbsp;&nbsp; &nbsp; echo </span><span class="string">'function returned TRUE'</span><span class="keyword">;<br />
}<br />
<br />
</span><span class="comment">// will output : function returned FALSE<br />
</span><span class="default">?&gt;<br />
</span><br />
&nbsp;&nbsp; Just spent 10 mins trying to figure out why a function returned false when it didn't, so check your typing or use === instead of ==</span>
</code></div>
  </div>
 </div>
 <a name="98410"></a>
 <div class="note">
  <strong class='user'>fyrye at torntech dot com</strong>
  <a href="#98410" class="date">14-Jun-2010 04:39</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Since I haven't seen it posted.<br />
Here is a function that you can use if you have a need to force strict boolean values.<br />
Hopefully this will save someone some time from searching for similar.<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">strictBool</span><span class="keyword">(</span><span class="default">$val</span><span class="keyword">=</span><span class="default">false</span><span class="keyword">){<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">is_integer</span><span class="keyword">(</span><span class="default">$val</span><span class="keyword">)?</span><span class="default">false</span><span class="keyword">:</span><span class="default">$val </span><span class="keyword">== </span><span class="default">1</span><span class="keyword">;<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
Simply put, it verifies that the value passed is (bool)true otherwise it's false.<br />
<br />
Examples:<br />
__________________________________<br />
<span class="default">&lt;?php<br />
$myBool </span><span class="keyword">= </span><span class="default">strictBool</span><span class="keyword">(</span><span class="default">true</span><span class="keyword">);<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$myBool</span><span class="keyword">);<br />
</span><span class="comment">//returns (bool)true<br />
<br />
</span><span class="default">$myar </span><span class="keyword">= array(</span><span class="default">0 </span><span class="keyword">=&gt; </span><span class="default">true</span><span class="keyword">);<br />
</span><span class="default">$myBool </span><span class="keyword">= </span><span class="default">strictBool</span><span class="keyword">(</span><span class="default">$myar</span><span class="keyword">[</span><span class="default">0</span><span class="keyword">]);<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$myBool</span><span class="keyword">);<br />
</span><span class="comment">//returns (bool)true<br />
<br />
</span><span class="default">$myBool </span><span class="keyword">= </span><span class="default">strictBool</span><span class="keyword">(</span><span class="string">"hello"</span><span class="keyword">);<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$myBool</span><span class="keyword">);<br />
</span><span class="comment">//returns (bool)false<br />
<br />
</span><span class="default">$myBool </span><span class="keyword">= </span><span class="default">strictBool</span><span class="keyword">(</span><span class="default">false</span><span class="keyword">);<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$myBool</span><span class="keyword">);<br />
</span><span class="comment">//returns (bool)false<br />
<br />
</span><span class="default">$myBool </span><span class="keyword">= </span><span class="default">strictBool</span><span class="keyword">(array(</span><span class="default">0 </span><span class="keyword">=&gt; </span><span class="string">"hello"</span><span class="keyword">));<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$myBool</span><span class="keyword">);<br />
</span><span class="comment">//returns (bool)false<br />
<br />
</span><span class="default">$myBool </span><span class="keyword">= </span><span class="default">strictBool</span><span class="keyword">(</span><span class="default">1</span><span class="keyword">);<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$myBool</span><span class="keyword">);<br />
</span><span class="comment">//returns (bool)false<br />
<br />
</span><span class="default">$myBool </span><span class="keyword">= </span><span class="default">strictBool</span><span class="keyword">();<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$myBool</span><span class="keyword">);<br />
</span><span class="comment">//returns (bool)false<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="96070"></a>
 <div class="note">
  <strong class='user'>mercusmaximus at yahoo dot com</strong>
  <a href="#96070" class="date">06-Feb-2010 01:50</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Note that the comparison: (false == 0) evaluates to true and so will any value you set to false as well (without casting).</span>
</code></div>
  </div>
 </div>
 <a name="90237"></a>
 <div class="note">
  <strong class='user'>Symbol</strong>
  <a href="#90237" class="date">11-Apr-2009 01:35</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Just a side note, doesn't really matters, the reason -1 is true and not false is because boolean type is treated as unsigned, so -1 would be for example, if it's unsigned int32 translate to hex: 0xFFFFFFFF and back to decimal: 4294967295 which is non-zero. there isn't really a "negative boolean". it's a binary thing. :o (since it used to be a bit and then there was only 0 and 1 as an option)</span>
</code></div>
  </div>
 </div>
 <a name="90037"></a>
 <div class="note">
  <strong class='user'>russell dot harper at springboardnetworks dot com</strong>
  <a href="#90037" class="date">02-Apr-2009 06:27</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
PHP is very fussy converting strings to booleans. The only ones it recognizes are '0' or '', everything else evaluates to TRUE, even 'false' and '0.0' are evaluated as true! I suppose this can't be fixed without breaking a lot of existing code.<br />
<br />
Example:<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">print </span><span class="string">'yes'</span><span class="keyword">.</span><span class="string">"\t"</span><span class="keyword">.((bool)</span><span class="string">'yes'</span><span class="keyword">? </span><span class="default">1</span><span class="keyword">: </span><span class="default">0</span><span class="keyword">).</span><span class="string">"\n"</span><span class="keyword">;<br />
print </span><span class="string">'true'</span><span class="keyword">.</span><span class="string">"\t"</span><span class="keyword">.((bool)</span><span class="string">'true'</span><span class="keyword">? </span><span class="default">1</span><span class="keyword">: </span><span class="default">0</span><span class="keyword">).</span><span class="string">"\n"</span><span class="keyword">;<br />
print </span><span class="string">'no'</span><span class="keyword">.</span><span class="string">"\t"</span><span class="keyword">.((bool)</span><span class="string">'no'</span><span class="keyword">? </span><span class="default">1</span><span class="keyword">: </span><span class="default">0</span><span class="keyword">).</span><span class="string">"\n"</span><span class="keyword">;<br />
print </span><span class="string">'false'</span><span class="keyword">.</span><span class="string">"\t"</span><span class="keyword">.((bool)</span><span class="string">'false'</span><span class="keyword">? </span><span class="default">1</span><span class="keyword">: </span><span class="default">0</span><span class="keyword">).</span><span class="string">"\n"</span><span class="keyword">;<br />
print </span><span class="string">'1'</span><span class="keyword">.</span><span class="string">"\t"</span><span class="keyword">.((bool)</span><span class="string">'1'</span><span class="keyword">? </span><span class="default">1</span><span class="keyword">: </span><span class="default">0</span><span class="keyword">).</span><span class="string">"\n"</span><span class="keyword">;<br />
print </span><span class="string">'0'</span><span class="keyword">.</span><span class="string">"\t"</span><span class="keyword">.((bool)</span><span class="string">'0'</span><span class="keyword">? </span><span class="default">1</span><span class="keyword">: </span><span class="default">0</span><span class="keyword">).</span><span class="string">"\n"</span><span class="keyword">;<br />
print </span><span class="string">'0.0'</span><span class="keyword">.</span><span class="string">"\t"</span><span class="keyword">.((bool)</span><span class="string">'0.0'</span><span class="keyword">? </span><span class="default">1</span><span class="keyword">: </span><span class="default">0</span><span class="keyword">).</span><span class="string">"\n"</span><span class="keyword">;<br />
print </span><span class="string">''</span><span class="keyword">.</span><span class="string">"\t"</span><span class="keyword">.((bool)</span><span class="string">''</span><span class="keyword">? </span><span class="default">1</span><span class="keyword">: </span><span class="default">0</span><span class="keyword">).</span><span class="string">"\n"</span><span class="keyword">;<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
Output:<br />
<br />
yes&nbsp; &nbsp;&nbsp; 1<br />
true&nbsp; &nbsp; 1<br />
no&nbsp; &nbsp; &nbsp; 1<br />
false&nbsp;&nbsp; 1<br />
1&nbsp; &nbsp; &nbsp;&nbsp; 1<br />
0&nbsp; &nbsp; &nbsp;&nbsp; 0<br />
0.0&nbsp; &nbsp;&nbsp; 1<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; 0</span>
</code></div>
  </div>
 </div>
 <a name="89194"></a>
 <div class="note">
  <strong class='user'>ashafer01 at gmail dot com</strong>
  <a href="#89194" class="date">25-Feb-2009 07:59</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
A note when working with PostgreSQL - if you select a boolean field from the database, it returns 't' or 'f'. If you directly evaluate a variable storing a boolean from a PostgreSQL database, it will always return true.<br />
<br />
For example...<br />
<br />
<span class="default">&lt;?php<br />
$x </span><span class="keyword">= </span><span class="default">pg_query</span><span class="keyword">(</span><span class="string">"SELECT someBool FROM atable"</span><span class="keyword">);<br />
</span><span class="default">$x </span><span class="keyword">= </span><span class="default">pg_fetch_array</span><span class="keyword">(</span><span class="default">$x</span><span class="keyword">);<br />
</span><span class="default">$x </span><span class="keyword">= </span><span class="default">$x</span><span class="keyword">[</span><span class="string">'someBool'</span><span class="keyword">];<br />
<br />
if (</span><span class="default">$x</span><span class="keyword">) echo </span><span class="string">"true"</span><span class="keyword">;<br />
else echo </span><span class="string">"false"</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
...ALWAYS outputs true</span>
</code></div>
  </div>
 </div>
 <a name="86809"></a>
 <div class="note">
  <strong class='user'>admin at eexit dot fr</strong>
  <a href="#86809" class="date">04-Nov-2008 12:27</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Beware of certain control behavior with boolean and non boolean values :<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="comment">// Consider that the 0 could by any parameters including itself<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">0 </span><span class="keyword">== </span><span class="default">1</span><span class="keyword">); </span><span class="comment">// false<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">0 </span><span class="keyword">== (bool)</span><span class="string">'all'</span><span class="keyword">); </span><span class="comment">// false<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">0 </span><span class="keyword">== </span><span class="string">'all'</span><span class="keyword">); </span><span class="comment">// TRUE, take care<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">0 </span><span class="keyword">=== </span><span class="string">'all'</span><span class="keyword">); </span><span class="comment">// false<br />
<br />
// To avoid this behavior, you need to cast your parameter as string like that :<br />
</span><span class="default">var_dump</span><span class="keyword">((string)</span><span class="default">0 </span><span class="keyword">== </span><span class="string">'all'</span><span class="keyword">); </span><span class="comment">// false<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="86171"></a>
 <div class="note">
  <strong class='user'>wbcarts at juno dot com</strong>
  <a href="#86171" class="date">06-Oct-2008 11:59</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
CODING PRACTICE...<br />
<br />
Much of the confusion about booleans (but not limited to booleans) is the fact that PHP itself automatically makes a type cast or conversion for you, which may NOT be what you want or expect. In most cases, it's better to provide functions that give your program the exact behavior you want.<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">function </span><span class="default">boolNumber</span><span class="keyword">(</span><span class="default">$bValue </span><span class="keyword">= </span><span class="default">false</span><span class="keyword">) {&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="comment">// returns integer<br />
&nbsp; </span><span class="keyword">return (</span><span class="default">$bValue </span><span class="keyword">? </span><span class="default">1 </span><span class="keyword">: </span><span class="default">0</span><span class="keyword">);<br />
}<br />
<br />
function </span><span class="default">boolString</span><span class="keyword">(</span><span class="default">$bValue </span><span class="keyword">= </span><span class="default">false</span><span class="keyword">) {&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="comment">// returns string<br />
&nbsp; </span><span class="keyword">return (</span><span class="default">$bValue </span><span class="keyword">? </span><span class="string">'true' </span><span class="keyword">: </span><span class="string">'false'</span><span class="keyword">);<br />
}<br />
<br />
</span><span class="default">$a </span><span class="keyword">= </span><span class="default">true</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="comment">// boolean value<br />
</span><span class="keyword">echo </span><span class="string">'boolean $a AS string = ' </span><span class="keyword">. </span><span class="default">boolString</span><span class="keyword">(</span><span class="default">$a</span><span class="keyword">) . </span><span class="string">'&lt;br&gt;'</span><span class="keyword">;&nbsp;&nbsp; </span><span class="comment">// boolean as a string<br />
</span><span class="keyword">echo </span><span class="string">'boolean $a AS number = ' </span><span class="keyword">. </span><span class="default">boolNumber</span><span class="keyword">(</span><span class="default">$a</span><span class="keyword">) . </span><span class="string">'&lt;br&gt;'</span><span class="keyword">;&nbsp;&nbsp; </span><span class="comment">// boolean as a number<br />
</span><span class="keyword">echo </span><span class="string">'&lt;br&gt;'</span><span class="keyword">;<br />
<br />
</span><span class="default">$b </span><span class="keyword">= (</span><span class="default">45 </span><span class="keyword">&gt; </span><span class="default">90</span><span class="keyword">);&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// boolean value<br />
</span><span class="keyword">echo </span><span class="string">'boolean $b AS string = ' </span><span class="keyword">. </span><span class="default">boolString</span><span class="keyword">(</span><span class="default">$b</span><span class="keyword">) . </span><span class="string">'&lt;br&gt;'</span><span class="keyword">;&nbsp;&nbsp; </span><span class="comment">// boolean as a string<br />
</span><span class="keyword">echo </span><span class="string">'boolean $b AS number = ' </span><span class="keyword">. </span><span class="default">boolNumber</span><span class="keyword">(</span><span class="default">$b</span><span class="keyword">) . </span><span class="string">'&lt;br&gt;'</span><span class="keyword">;&nbsp;&nbsp; </span><span class="comment">// boolean as a number<br />
</span><span class="keyword">echo </span><span class="string">'&lt;br&gt;'</span><span class="keyword">;<br />
<br />
</span><span class="default">$c </span><span class="keyword">= </span><span class="default">boolNumber</span><span class="keyword">(</span><span class="default">10 </span><span class="keyword">&gt; </span><span class="default">8</span><span class="keyword">) + </span><span class="default">boolNumber</span><span class="keyword">(!(</span><span class="default">5 </span><span class="keyword">&gt; </span><span class="default">10</span><span class="keyword">));&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="comment">// adding booleans<br />
</span><span class="keyword">echo </span><span class="string">'integer $c = ' </span><span class="keyword">. </span><span class="default">$c </span><span class="keyword">.</span><span class="string">'&lt;br&gt;'</span><span class="keyword">;<br />
<br />
</span><span class="default">?&gt;<br />
</span>Results in the following being printed...<br />
<br />
&nbsp;boolean $a AS string = true<br />
&nbsp;boolean $a AS number = 1<br />
<br />
&nbsp;boolean $b AS string = false<br />
&nbsp;boolean $b AS number = 0<br />
<br />
&nbsp;integer $c = 2<br />
<br />
In other words, if we know what we want out of our program, we can create functions to accommodate. Here, we just wanted 'manual control' over numbers and strings, so that PHP doesn't confuse us.</span>
</code></div>
  </div>
 </div>
 <a name="80648"></a>
 <div class="note">
  <strong class='user'>Wackzingo</strong>
  <a href="#80648" class="date">27-Jan-2008 06:39</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
It is correct that TRUE or FALSE should not be used as constants for the numbers 0 and 1. But there may be times when it might be helpful to see the value of the Boolean as a 1 or 0. Here's how to do it.<br />
<br />
<span class="default">&lt;?php<br />
$var1 </span><span class="keyword">= </span><span class="default">TRUE</span><span class="keyword">;<br />
</span><span class="default">$var2 </span><span class="keyword">= </span><span class="default">FALSE</span><span class="keyword">;<br />
<br />
echo </span><span class="default">$var1</span><span class="keyword">; </span><span class="comment">// Will display the number 1<br />
<br />
</span><span class="keyword">echo </span><span class="default">$var2</span><span class="keyword">; </span><span class="comment">//Will display nothing<br />
<br />
/* To get it to display the number 0 for<br />
a false value you have to typecast it: */<br />
<br />
</span><span class="keyword">echo (int)</span><span class="default">$var2</span><span class="keyword">; </span><span class="comment">//This will display the number 0 for false.<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="80433"></a>
 <div class="note">
  <strong class='user'>Steve</strong>
  <a href="#80433" class="date">15-Jan-2008 03:00</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
PHP does not break any rules with the values of true and false.&nbsp; The value false is not a constant for the number 0, it is a boolean value that indicates false.&nbsp; The value true is also not a constant for 1, it is a special boolean value that indicates true.&nbsp; It just happens to cast to integer 1 when you print it or use it in an expression, but it's not the same as a constant for the integer value 1 and you shouldn't use it as one.&nbsp; Notice what it says at the top of the page:<br />
<br />
A boolean expresses a truth value.<br />
<br />
It does not say "a boolean expresses a 0 or 1".<br />
<br />
It's true that symbolic constants are specifically designed to always and only reference their constant value.&nbsp; But booleans are not symbolic constants, they are values.&nbsp; If you're trying to add 2 boolean values you might have other problems in your application.</span>
</code></div>
  </div>
 </div>
 <a name="80254"></a>
 <div class="note">
  <strong class='user'>Anonymous</strong>
  <a href="#80254" class="date">06-Jan-2008 07:05</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Note that the symbolic constants TRUE and FALSE are treated differently.&nbsp; I was told that this is a feature, not a bug.<br />
<br />
echo false ;<br />
echo (false) ;<br />
echo false+false ;<br />
echo (false+false) ;<br />
echo intval(false) ;<br />
echo '"'.false.'"' ;<br />
<br />
echo true ;<br />
echo (true) ;<br />
echo true+true ;<br />
echo (true+true) ;<br />
echo intval(true) ;<br />
echo '"'.true.'"' ;<br />
<br />
should produce<br />
<br />
00000"0"11221"1"<br />
<br />
but instead produces<br />
<br />
000""11221"1"<br />
<br />
In other words, the only way to output the underlying zero or use it in a string is to use 'false+false' or pass it through intval().&nbsp; No such tricks are required to get at the 1 that underlies true.<br />
<br />
The whole idea of symbolic constants is that the underlying value *always* replaces them during translation, and thus anywhere you would otherwise have to use some obscure "magic number" such as 191, you can use a symbolic constant that makes sense, such as TOTAL_NATIONS.&nbsp; <br />
<br />
Exactly what php gets out of breaking this rule was not explained to me.</span>
</code></div>
  </div>
 </div>
 <a name="78099"></a>
 <div class="note">
  <strong class='user'>artktec at gmail dot com</strong>
  <a href="#78099" class="date">27-Sep-2007 09:37</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Note you can also use the '!' to convert a number to a boolean, as if it was an explicit (bool) cast then NOT.<br />
<br />
So you can do something like:<br />
<br />
<span class="default">&lt;?php<br />
$t </span><span class="keyword">= !</span><span class="default">0</span><span class="keyword">; </span><span class="comment">// This will === true;<br />
</span><span class="default">$f </span><span class="keyword">= !</span><span class="default">1</span><span class="keyword">; </span><span class="comment">// This will === false;<br />
</span><span class="default">?&gt;<br />
</span><br />
And non-integers are casted as if to bool, then NOT.<br />
<br />
Example:<br />
<br />
<span class="default">&lt;?php<br />
$a </span><span class="keyword">= !array();&nbsp; &nbsp; &nbsp; </span><span class="comment">// This will === true;<br />
</span><span class="default">$a </span><span class="keyword">= !array(</span><span class="string">'a'</span><span class="keyword">);&nbsp;&nbsp; </span><span class="comment">// This will === false;<br />
</span><span class="default">$s </span><span class="keyword">= !</span><span class="string">""</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// This will === true;<br />
</span><span class="default">$s </span><span class="keyword">= !</span><span class="string">"hello"</span><span class="keyword">;&nbsp; &nbsp; &nbsp; </span><span class="comment">// This will === false;<br />
</span><span class="default">?&gt;<br />
</span><br />
To cast as if using a (bool) you can NOT the NOT with "!!" (double '!'), then you are casting to the correct (bool).<br />
<br />
Example:<br />
<br />
<span class="default">&lt;?php<br />
$a </span><span class="keyword">= !!array();&nbsp;&nbsp; </span><span class="comment">// This will === false; (as expected)<br />
/* <br />
This can be a substitute for count($array) &gt; 0 or !(empty($array)) to check to see if an array is empty or not&nbsp; (you would use: !!$array).<br />
*/<br />
<br />
</span><span class="default">$status </span><span class="keyword">= (!!</span><span class="default">$array </span><span class="keyword">? </span><span class="string">'complete' </span><span class="keyword">: </span><span class="string">'incomplete'</span><span class="keyword">);<br />
<br />
</span><span class="default">$s </span><span class="keyword">= !!</span><span class="string">"testing"</span><span class="keyword">; </span><span class="comment">// This will === true; (as expected)<br />
/* <br />
Note: normal casting rules apply so a !!"0" would evaluate to an === false<br />
*/<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="74827"></a>
 <div class="note">
  <strong class='user'>terminatorul at gmail dot com</strong>
  <a href="#74827" class="date">29-Apr-2007 02:21</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Beware that "0.00" converts to boolean TRUE !<br />
<br />
You may get such a string from your database, if you have columns of type DECIMAL or CURRENCY. In such cases you have to explicitly check if the value is != 0 or to explicitly convert the value to int also, not only to boolean.</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=language.types.boolean&amp;redirect=http://www.php.net/manual/en/language.types.boolean.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.types.boolean&amp;redirect=http://www.php.net/manual/en/language.types.boolean.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/language.types.boolean.php">show source</a> |
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