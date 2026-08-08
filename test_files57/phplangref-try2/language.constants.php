<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Constants - Manual</title>
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
 <link rel="prev" href="language.variables.external.php" />
 <link rel="next" href="language.constants.syntax.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/constants" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="alternate" href="/manual/en/feeds/language.constants.atom" type="application/atom+xml" />
 <link rel="canonical" href="http://php.net/manual/en/language.constants.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/language.constants.php" />
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
 <li><a href="language.types.php">Types</a></li>
 <li><a href="language.variables.php">Variables</a></li>
 <li class="active"><a href="language.constants.php">Constants</a></li>
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
  <a href="language.constants.syntax.php">Syntax<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.variables.external.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Variables From External Sources</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.constants.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/language.constants.php">Brazilian Portuguese</option>
    <option value="zh/language.constants.php">Chinese (Simplified)</option>
    <option value="fr/language.constants.php">French</option>
    <option value="de/language.constants.php">German</option>
    <option value="ja/language.constants.php">Japanese</option>
    <option value="pl/language.constants.php">Polish</option>
    <option value="ro/language.constants.php">Romanian</option>
    <option value="ru/language.constants.php">Russian</option>
    <option value="fa/language.constants.php">Persian</option>
    <option value="es/language.constants.php">Spanish</option>
    <option value="tr/language.constants.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="language.constants" class="chapter">
  <h1>Constants</h1>
<h2>Table of Contents</h2><ul class="chunklist chunklist_chapter"><li><a href="language.constants.syntax.php">Syntax</a></li><li><a href="language.constants.predefined.php">Magic constants</a></li></ul>


  <p class="simpara">
   A constant is an identifier (name) for a simple value. As the name
   suggests, that value cannot change during the execution of the
   script (except for <a href="language.constants.predefined.php" class="link">
   magic constants</a>, which aren&#039;t actually constants).
   A constant is case-sensitive by default. By convention, constant 
   identifiers are always uppercase.
  </p>
  <p class="para">
   The name of a constant follows the same rules as any label in PHP. A 
   valid constant name starts with a letter or underscore, followed
   by any number of letters, numbers, or underscores. As a regular
   expression, it would be expressed thusly:
   <em>[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*</em>
  </p>
  <div class="tip"><strong class="tip">Tip</strong><p class="simpara">See also the
<a href="userlandnaming.php" class="xref">Userland Naming Guide</a>.</p></div>
  <p class="para">
   <div class="example" id="example-110">
    <p><strong>Example #1 Valid and invalid constant names</strong></p>
    <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /><br /></span><span style="color: #FF8000">//&nbsp;Valid&nbsp;constant&nbsp;names<br /></span><span style="color: #0000BB">define</span><span style="color: #007700">(</span><span style="color: #DD0000">"FOO"</span><span style="color: #007700">,&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #DD0000">"something"</span><span style="color: #007700">);<br /></span><span style="color: #0000BB">define</span><span style="color: #007700">(</span><span style="color: #DD0000">"FOO2"</span><span style="color: #007700">,&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #DD0000">"something&nbsp;else"</span><span style="color: #007700">);<br /></span><span style="color: #0000BB">define</span><span style="color: #007700">(</span><span style="color: #DD0000">"FOO_BAR"</span><span style="color: #007700">,&nbsp;</span><span style="color: #DD0000">"something&nbsp;more"</span><span style="color: #007700">);<br /><br /></span><span style="color: #FF8000">//&nbsp;Invalid&nbsp;constant&nbsp;names<br /></span><span style="color: #0000BB">define</span><span style="color: #007700">(</span><span style="color: #DD0000">"2FOO"</span><span style="color: #007700">,&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #DD0000">"something"</span><span style="color: #007700">);<br /><br /></span><span style="color: #FF8000">//&nbsp;This&nbsp;is&nbsp;valid,&nbsp;but&nbsp;should&nbsp;be&nbsp;avoided:<br />//&nbsp;PHP&nbsp;may&nbsp;one&nbsp;day&nbsp;provide&nbsp;a&nbsp;magical&nbsp;constant<br />//&nbsp;that&nbsp;will&nbsp;break&nbsp;your&nbsp;script<br /></span><span style="color: #0000BB">define</span><span style="color: #007700">(</span><span style="color: #DD0000">"__FOO__"</span><span style="color: #007700">,&nbsp;</span><span style="color: #DD0000">"something"</span><span style="color: #007700">);&nbsp;<br /><br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

   </div>
  </p>
  <blockquote class="note"><p><strong class="note">Note</strong>: 
   <span class="simpara">
    For our purposes here, a letter is a-z, A-Z, and the ASCII
    characters from 127 through 255 (0x7f-0xff).
   </span>
  </p></blockquote>

  <p class="simpara">
   Like <a href="language.variables.predefined.php" class="link">superglobals</a>, the scope of a constant is global.  You 
   can access constants anywhere in your script without regard to scope.  
   For more information on scope, read the manual section on
   <a href="language.variables.scope.php" class="link">variable scope</a>.
  </p>

  
  
  
 </div>
<br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="language.constants.syntax.php">Syntax<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.variables.external.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Variables From External Sources</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.constants.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=language.constants&amp;redirect=http://www.php.net/manual/en/language.constants.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.constants&amp;redirect=http://www.php.net/manual/en/language.constants.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Constants</strong>
 </div><div id="allnotes">
 <a name="108717"></a>
 <div class="note">
  <strong class='user'>wbcarts at juno dot com</strong>
  <a href="#108717" class="date">20-May-2012 03:04</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
CONSTANTS and PHP Class Definitions<br />
<br />
Using "define('MY_VAR', 'default value')" INSIDE a class definition does not work. You have to use the PHP keyword 'const' and initialize it with a scalar value -- boolean, int, float, or string (no array or other object types) -- right away.<br />
<br />
<span class="default">&lt;?php<br />
<br />
define</span><span class="keyword">(</span><span class="string">'MIN_VALUE'</span><span class="keyword">, </span><span class="string">'0.0'</span><span class="keyword">);&nbsp;&nbsp; </span><span class="comment">// RIGHT - Works OUTSIDE of a class definition.<br />
</span><span class="default">define</span><span class="keyword">(</span><span class="string">'MAX_VALUE'</span><span class="keyword">, </span><span class="string">'1.0'</span><span class="keyword">);&nbsp;&nbsp; </span><span class="comment">// RIGHT - Works OUTSIDE of a class definition.<br />
<br />
//const MIN_VALUE = 0.0;&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; WRONG - Works INSIDE of a class definition.<br />
//const MAX_VALUE = 1.0;&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; WRONG - Works INSIDE of a class definition.<br />
<br />
</span><span class="keyword">class </span><span class="default">Constants<br />
</span><span class="keyword">{<br />
&nbsp; </span><span class="comment">//define('MIN_VALUE', '0.0');&nbsp; WRONG - Works OUTSIDE of a class definition.<br />
&nbsp; //define('MAX_VALUE', '1.0');&nbsp; WRONG - Works OUTSIDE of a class definition.<br />
<br />
&nbsp; </span><span class="keyword">const </span><span class="default">MIN_VALUE </span><span class="keyword">= </span><span class="default">0.0</span><span class="keyword">;&nbsp; &nbsp; &nbsp; </span><span class="comment">// RIGHT - Works INSIDE of a class definition.<br />
&nbsp; </span><span class="keyword">const </span><span class="default">MAX_VALUE </span><span class="keyword">= </span><span class="default">1.0</span><span class="keyword">;&nbsp; &nbsp; &nbsp; </span><span class="comment">// RIGHT - Works INSIDE of a class definition.<br />
<br />
&nbsp; </span><span class="keyword">public static function </span><span class="default">getMinValue</span><span class="keyword">()<br />
&nbsp; {<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">self</span><span class="keyword">::</span><span class="default">MIN_VALUE</span><span class="keyword">;<br />
&nbsp; }<br />
<br />
&nbsp; public static function </span><span class="default">getMaxValue</span><span class="keyword">()<br />
&nbsp; {<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">self</span><span class="keyword">::</span><span class="default">MAX_VALUE</span><span class="keyword">;<br />
&nbsp; }<br />
}<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
#Example 1:<br />
You can access these constants DIRECTLY like so:<br />
&nbsp;* type the class name exactly.<br />
&nbsp;* type two (2) colons.<br />
&nbsp;* type the const name exactly.<br />
<br />
#Example 2:<br />
Because our class definition provides two (2) static functions, you can also access them like so:<br />
&nbsp;* type the class name exactly.<br />
&nbsp;* type two (2) colons.<br />
&nbsp;* type the function name exactly (with the parentheses).<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="comment">#Example 1:<br />
</span><span class="default">$min </span><span class="keyword">= </span><span class="default">Constants</span><span class="keyword">::</span><span class="default">MIN_VALUE</span><span class="keyword">;<br />
</span><span class="default">$max </span><span class="keyword">= </span><span class="default">Constants</span><span class="keyword">::</span><span class="default">MAX_VALUE</span><span class="keyword">;<br />
<br />
</span><span class="comment">#Example 2:<br />
</span><span class="default">$min </span><span class="keyword">= </span><span class="default">Constants</span><span class="keyword">::</span><span class="default">getMinValue</span><span class="keyword">();<br />
</span><span class="default">$max </span><span class="keyword">= </span><span class="default">Constants</span><span class="keyword">::</span><span class="default">getMaxValue</span><span class="keyword">();<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
Once class constants are declared AND initialized, they cannot be set to different values -- that is why there are no setMinValue() and setMaxValue() functions in the class definition -- which means they are READ-ONLY and STATIC (shared by all instances of the class).</span>
</code></div>
  </div>
 </div>
 <a name="105559"></a>
 <div class="note">
  <strong class='user'>Anonymous</strong>
  <a href="#105559" class="date">27-Aug-2011 07:35</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
PHP will allow characters other than those shown for both variable names and constants, and therefore probably functin names, too.&nbsp; And I'm pretty sure array indexes also.<br />
<br />
Many others are allowed, while some are not.&nbsp; It seems like a craps-shoot at first, but there is a 'bit' of reason...<br />
<br />
you can do this:<br />
<span class="default">&lt;?php<br />
$x´</span><span class="keyword">; </span><span class="comment">// using the "acute (reverse) accent mark"&nbsp; (#182)<br />
</span><span class="default">?&gt;<br />
</span>but not:<br />
<span class="default">&lt;?php<br />
$x′</span><span class="keyword">;&nbsp; </span><span class="comment">// using the "prime mark"&nbsp; (#8242)<br />
</span><span class="default">?&gt;<br />
</span><br />
but you can do:<br />
<span class="default">&lt;?php<br />
$x‡†±√2×π&nbsp; </span><span class="comment">// using double dagger, dagger, plus-minus, square-root, the number 2, the "times symbol" and the greek letter pi (lowercase).<br />
</span><span class="default">?&gt;<br />
</span>but not:<br />
<span class="default">&lt;?php<br />
$x♂♀◊∆</span><span class="keyword">;&nbsp; </span><span class="comment">// using male, female, lozenge, mathematical increment symbol.<br />
</span><span class="default">?&gt;<br />
</span><br />
You can do this:<br />
<span class="default">&lt;?php<br />
define</span><span class="keyword">(</span><span class="string">'≈PI'</span><span class="keyword">,&nbsp;&nbsp; </span><span class="default">M_PI</span><span class="keyword">);&nbsp; &nbsp; &nbsp; &nbsp; </span><span class="comment">// 180 degrees = π radians ≈ 3.141592654 radians<br />
</span><span class="default">define</span><span class="keyword">(</span><span class="string">'≈180°'</span><span class="keyword">, </span><span class="default">M_PI</span><span class="keyword">);<br />
</span><span class="default">define</span><span class="keyword">(</span><span class="string">'≈PI÷2'</span><span class="keyword">, </span><span class="default">M_PI_2</span><span class="keyword">);&nbsp; &nbsp; &nbsp; </span><span class="comment">// 90 degrees ≈ 1.570796327 radians<br />
</span><span class="default">define</span><span class="keyword">(</span><span class="string">'≈90°'</span><span class="keyword">,&nbsp; </span><span class="default">M_PI_2</span><span class="keyword">);<br />
</span><span class="default">define</span><span class="keyword">(</span><span class="string">'≈PI÷4'</span><span class="keyword">, </span><span class="default">M_PI_4</span><span class="keyword">);&nbsp; &nbsp; &nbsp; </span><span class="comment">// 45 degrees ≈ 0.785398163 radians<br />
</span><span class="default">define</span><span class="keyword">(</span><span class="string">'≈45°'</span><span class="keyword">,&nbsp; </span><span class="default">M_PI_4</span><span class="keyword">);<br />
</span><span class="default">define</span><span class="keyword">(</span><span class="string">'≈PI×3÷2'</span><span class="keyword">, </span><span class="default">≈PI</span><span class="keyword">+</span><span class="default">≈PI÷2</span><span class="keyword">); </span><span class="comment">// 270 degrees ≈ 4.71238898 radians<br />
</span><span class="default">define</span><span class="keyword">(</span><span class="string">'≈270°'</span><span class="keyword">, </span><span class="default">≈PI×3÷2</span><span class="keyword">);<br />
</span><span class="default">define</span><span class="keyword">(</span><span class="string">'≈PI×2'</span><span class="keyword">, </span><span class="default">≈PI</span><span class="keyword">*</span><span class="default">2</span><span class="keyword">);&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// 360 degrees = 2π radians ≈ 6.283185307 radians<br />
</span><span class="default">define</span><span class="keyword">(</span><span class="string">'≈360°'</span><span class="keyword">, </span><span class="default">≈PI×2</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span><br />
So essentially, you must check each character for acceptance by PHP if you want to use them, but they can really add a semantical value in some cases, thus making your code easier to read and understand..</span>
</code></div>
  </div>
 </div>
 <a name="101796"></a>
 <div class="note">
  <strong class='user'>meint at meint dot net</strong>
  <a href="#101796" class="date">11-Jan-2011 12:08</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
A nice way to set and verify a constant is not already set:<br />
<br />
defined('CONSTANT') or define('CONSTANT', 'value');<br />
<br />
If the constant is defined the expression resolves to false, if the constant isn't set it will be defined.</span>
</code></div>
  </div>
 </div>
 <a name="79431"></a>
 <div class="note">
  <strong class='user'>ben at bendodson dot com</strong>
  <a href="#79431" class="date">27-Nov-2007 03:14</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I recently found I needed a way of retrieving the value of a constant dynamically - e.g. trying to find the value of FOO_BAR by passing 'FOO_' . $someVariableWithValueBAR.&nbsp; I came up with the following solution:<br />
<br />
<span class="default">&lt;?php<br />
<br />
define</span><span class="keyword">(</span><span class="string">'FOO_BAR'</span><span class="keyword">,</span><span class="string">'It works!'</span><span class="keyword">);<br />
</span><span class="default">define</span><span class="keyword">(</span><span class="string">'FOO_FOO_BAR'</span><span class="keyword">,</span><span class="string">'It works again!'</span><span class="keyword">);<br />
<br />
</span><span class="comment">// prints 'It works!'<br />
</span><span class="default">$changing_variable </span><span class="keyword">= </span><span class="string">'bar'</span><span class="keyword">;<br />
echo </span><span class="default">constant</span><span class="keyword">(</span><span class="string">'FOO_' </span><span class="keyword">. </span><span class="default">strtoupper</span><span class="keyword">(</span><span class="default">$changing_variable</span><span class="keyword">));<br />
<br />
</span><span class="comment">// prints 'It works again!'<br />
</span><span class="default">$changing_variable </span><span class="keyword">= </span><span class="string">'foo_bar'</span><span class="keyword">;<br />
echo </span><span class="default">constant</span><span class="keyword">(</span><span class="string">'FOO_' </span><span class="keyword">. </span><span class="default">strtoupper</span><span class="keyword">(</span><span class="default">$changing_variable</span><span class="keyword">));<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
Note the use of strtoupper() as constants should be defined in uppercase for good practice - feel free to remove if you have constants defined in lowercase or you can set $changing_variable as uppercase.<br />
<br />
Might be of some use to someone!</span>
</code></div>
  </div>
 </div>
 <a name="76304"></a>
 <div class="note">
  <strong class='user'>tudor at tudorholton dot com</strong>
  <a href="#76304" class="date">09-Jul-2007 05:15</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Note that constant name must always be quoted when defined.<br />
<br />
e.g.<br />
define('MY_CONST','blah') - correct<br />
define(MY_CONST,'blah') - incorrect<br />
<br />
The following error message also indicates this fact:<br />
Notice:&nbsp; Use of undefined constant MY_CONST - assumed 'MY_CONST' in included_script.php on line 5<br />
<br />
Note the error message gives you some incorrect information.&nbsp;&nbsp; 'MY_CONST' (with quotes) doesn't actually exist anywhere in your code.&nbsp; The error _is_ that you didn't quote the constant when you defined it in the 'assumed' file.</span>
</code></div>
  </div>
 </div>
 <a name="74836"></a>
 <div class="note">
  <strong class='user'>Andreas R.</strong>
  <a href="#74836" class="date">30-Apr-2007 07:19</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you are looking for predefined constants like<br />
* PHP_OS (to show the operating system, PHP was compiled for; php_uname('s') might be more suitable),<br />
* DIRECTORY_SEPARATOR ("\\" on Win, '/' Linux,...)<br />
* PATH_SEPARATOR (';' on Win, ':' on Linux,...)<br />
they are buried in 'Predefined Constants' under 'List of Reserved Words' in the appendix:<br />
<a href="http://www.php.net/manual/en/reserved.constants.php" rel="nofollow" target="_blank">http://www.php.net/manual/en/reserved.constants.php</a><br />
while the latter two are also mentioned in 'Directory Functions'<br />
<a href="http://www.php.net/manual/en/ref.dir.php" rel="nofollow" target="_blank">http://www.php.net/manual/en/ref.dir.php</a></span>
</code></div>
  </div>
 </div>
 <a name="73339"></a>
 <div class="note">
  <strong class='user'>pdenny at magmic dot com</strong>
  <a href="#73339" class="date">18-Feb-2007 09:46</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Note that constants can also be used as default argument values<br />
so the following code:<br />
<br />
<span class="default">&lt;?php<br />
&nbsp; define</span><span class="keyword">(</span><span class="string">'TEST_CONSTANT'</span><span class="keyword">,</span><span class="string">'Works!'</span><span class="keyword">);<br />
&nbsp; function </span><span class="default">testThis</span><span class="keyword">(</span><span class="default">$var</span><span class="keyword">=</span><span class="default">TEST_CONSTANT</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp;&nbsp; echo </span><span class="string">"Passing constants as default values $var"</span><span class="keyword">;<br />
&nbsp; }<br />
&nbsp; </span><span class="default">testThis</span><span class="keyword">();<br />
</span><span class="default">?&gt;<br />
</span><br />
will produce :<br />
<br />
Passing constants as default values Works!<br />
<br />
(I tried this in both PHP 4 and 5)</span>
</code></div>
  </div>
 </div>
 <a name="69408"></a>
 <div class="note">
  <strong class='user'>dexen at google dot me dot up</strong>
  <a href="#69408" class="date">05-Sep-2006 04:02</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
1) Constants are invaluable when you want to be sure that *nobody*&nbsp; changes your important piece of data through lifetime of script -- especially when you're developing in team -- as this can cause strange, hard to track bugs. <br />
<br />
2) Using constants is prefered over ``magic values'', as it leads to self-documenting code. Also saves you from scanning and tweaking tens of files should the value ever change.<br />
Consider example: <span class="default">&lt;?php <br />
</span><span class="keyword">if ( </span><span class="default">$headers</span><span class="keyword">[</span><span class="string">'code'</span><span class="keyword">] = </span><span class="default">505 </span><span class="keyword">) { </span><span class="comment">//wth is 505? What do following code do? </span><span class="default">?&gt;<br />
</span>versus: <span class="default">&lt;?php <br />
</span><span class="keyword">if ( </span><span class="default">$headers</span><span class="keyword">[</span><span class="string">'code'</span><span class="keyword">] = </span><span class="default">HTTP_VERSION_NOT_SUPPORTED </span><span class="keyword">) {<br />
&nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">useHttp </span><span class="keyword">= </span><span class="string">'1.0'</span><span class="keyword">; </span><span class="default">?&gt;<br />
</span><br />
In response to ``kencomer'':<br />
3) Why not to use <span class="default">&lt;?php<br />
define</span><span class="keyword">( </span><span class="string">'DEBUG'</span><span class="keyword">, </span><span class="default">FALSE </span><span class="keyword">);<br />
</span><span class="default">define</span><span class="keyword">( </span><span class="string">'DEBUG'</span><span class="keyword">, </span><span class="default">TRUE </span><span class="keyword">); </span><span class="default">?&gt;<br />
</span>and comment one of them out as needed when developing/deploying?<br />
That'd save a lot of ugly ``if ( defined( 'DEBUG' ) &amp;&amp; DEBUG ) {}''.<br />
<br />
4) For debugging toggled on/off you pretty often want to use assert() anyway. You're free to turn it on/off at any moment (thou you better do it only once ;) ). assert() gives some nice details upon failed assertion, like file/line/function and context (that's invaluable!)</span>
</code></div>
  </div>
 </div>
 <a name="62253"></a>
 <div class="note">
  <strong class='user'>martin at larsen dot dk</strong>
  <a href="#62253" class="date">23-Feb-2006 02:24</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I find variables much more flexible than constants because variables can be used inside quotes and heredocs etc. Especially for language systems, this is nice.<br />
<br />
As stated in one of the previous notes, there is no speed penalty by using variables. However, one issue is that you risc name collision with existing variables. When implementing a language system I simply found that adding a prefix to all the variables was the way to go, for example:<br />
<br />
$LNG_myvar1 = "my value";<br />
<br />
That is easier and performs faster than using arrays like<br />
<br />
$LNG['myvar'] = "my value";<br />
<br />
As a final note, implementing a new superglobal in PHP would make using constants much more beneficial. Then it could be used in qoutes like this:<br />
<br />
"The constant myconst has the value $CONSTANTS[myconst] !"</span>
</code></div>
  </div>
 </div>
 <a name="59936"></a>
 <div class="note">
  <strong class='user'>anj at aps dot anl dot gov</strong>
  <a href="#59936" class="date">20-Dec-2005 08:42</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
It is possible to define constants that have the same name as a built-in PHP keyword, although subsequent attempts to actually use these constants will cause a parse error. For example in PHP 5.1.1, this code<br />
<br />
&nbsp;&nbsp;&nbsp; <span class="default">&lt;?php<br />
&nbsp;&nbsp;&nbsp; define</span><span class="keyword">(</span><span class="string">"PUBLIC"</span><span class="keyword">, </span><span class="string">"Hello, world!"</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; echo PUBLIC;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">?&gt;<br />
</span><br />
gives the error<br />
<br />
&nbsp;&nbsp;&nbsp; Parse error: syntax error, unexpected T_PUBLIC in test.php on line 3<br />
<br />
This is a problem to be aware of when converting PHP4 applications to PHP5, since that release introduced several new keywords that used to be legal names for constants.</span>
</code></div>
  </div>
 </div>
 <a name="56756"></a>
 <div class="note">
  <strong class='user'>kencomer at NOSPAM dot kencomer dot com</strong>
  <a href="#56756" class="date">14-Sep-2005 05:38</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Being a belt and suspenders person, when I use a constant to do flow control (i.e., using constants to determine which version of a section of the program should be used), I always use something like:<br />
<br />
if ( defined('DEBUG') &amp;&amp; TRUE===DEBUG )<br />
<br />
If you accidentally use DEBUG somewhere before it is defined, PHP will create a new constant called DEBUG with the value 'DEBUG'. Adding the second comparison will prevent the expression from being TRUE when you did not intentionally create the constant. For the constant DEBUG, this would rarely be a problem, but if you had (e.g.) a constant used to determine whether a function was created using case-sensitive comparisons, an accidental creation of the constant IGNORE_CASE having the value 'IGNORE_CASE' could drive you up the wall trying to find out what went wrong, particularly if you had warnings turned off.<br />
<br />
In almost all code I write, I put this function definition in my configuration section:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">if (!</span><span class="default">function_exists</span><span class="keyword">(</span><span class="string">"debug_print"</span><span class="keyword">)) {<br />
&nbsp; if ( </span><span class="default">defined</span><span class="keyword">(</span><span class="string">'DEBUG'</span><span class="keyword">) &amp;&amp; </span><span class="default">TRUE</span><span class="keyword">===</span><span class="default">DEBUG </span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">debug_print</span><span class="keyword">(</span><span class="default">$string</span><span class="keyword">,</span><span class="default">$flag</span><span class="keyword">=</span><span class="default">NULL</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp;&nbsp; </span><span class="comment">/* if second argument is absent or TRUE, print */<br />
&nbsp;&nbsp; &nbsp;&nbsp; </span><span class="keyword">if ( !(</span><span class="default">FALSE</span><span class="keyword">===</span><span class="default">$flag</span><span class="keyword">) )<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; print </span><span class="string">'DEBUG: '</span><span class="keyword">.</span><span class="default">$string </span><span class="keyword">. </span><span class="string">"\n"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp; } else {<br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">debug_print</span><span class="keyword">(</span><span class="default">$string</span><span class="keyword">,</span><span class="default">$flag</span><span class="keyword">=</span><span class="default">NULL</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp; }<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
Then, in my code, I'll sprinkle liberal doses of debug code like :<br />
<br />
<span class="default">&lt;?php<br />
define</span><span class="keyword">(</span><span class="string">"DEBUG_TRACK_EXAMPLE_CREATION"</span><span class="keyword">,</span><span class="default">FALSE</span><span class="keyword">);<br />
class </span><span class="default">Example </span><span class="keyword">extends </span><span class="default">Something </span><span class="keyword">{<br />
&nbsp; </span><span class="default">__construct</span><span class="keyword">(</span><span class="default">$whatever</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">debug_print</span><span class="keyword">( </span><span class="string">"new instance of Example created with '$whatever'\n"</span><span class="keyword">,</span><span class="default">DEBUG_TRACK_EXAMPLE_CREATION</span><span class="keyword">);<br />
&nbsp; }<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
and :<br />
<br />
<span class="default">&lt;?php<br />
debug_print</span><span class="keyword">(</span><span class="string">"finished init.\n"</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span><br />
In the first case, I would not want to see that message every time I went into DEBUG mode, so I made it a special case. The second case is always printed in DEBUG mode. If I decide to turn everything on, special cases and all, all I have to do is comment out the "if" line in debug_print() and presto magicko! It costs a little and gains a lot.<br />
<br />
As another belt-and-suspenders aside, notice that, unlike most people, I put the language constant (e.g.,TRUE, "string", etc.) on the left side of the comparison. By doing that, you can never accidentally do something like <br />
&nbsp; if ( $hard_to_find_error="here" )<br />
<br />
because you always write it as <br />
&nbsp; if ( "here"==$no_error )<br />
<br />
or, if you got it wrong,<br />
&nbsp; if ( "here"=$easy_to_find_parse_error )</span>
</code></div>
  </div>
 </div>
 <a name="55102"></a>
 <div class="note">
  <strong class='user'>Angelina Bell</strong>
  <a href="#55102" class="date">25-Jul-2005 12:39</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
It is so easy to create a constant that the php novice might do so accidently while attempting to call a function with no arguments.&nbsp; For example:<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">LogoutUser</span><span class="keyword">(){<br />
</span><span class="comment">// destroy the session, the cookie, and the session ID<br />
&nbsp; </span><span class="default">blah blah blah</span><span class="keyword">;<br />
&nbsp; return </span><span class="default">true</span><span class="keyword">;<br />
}<br />
function </span><span class="default">SessionCheck</span><span class="keyword">(){<br />
&nbsp; </span><span class="default">blah blah blah</span><span class="keyword">;<br />
</span><span class="comment">// check for session timeout<br />
</span><span class="keyword">...<br />
&nbsp;&nbsp;&nbsp; if (</span><span class="default">$timeout</span><span class="keyword">) </span><span class="default">LogoutUser</span><span class="keyword">;&nbsp; </span><span class="comment">// should be LogoutUser();<br />
</span><span class="keyword">}<br />
</span><span class="default">?&gt;<br />
</span><br />
OOPS!&nbsp; I don't notice my typo, the SessionCheck function<br />
doesn't work, and it takes me all afternoon to figure out why not!<br />
<br />
<span class="default">&lt;?php<br />
LogoutUser</span><span class="keyword">;<br />
print </span><span class="string">"new constant LogoutUser is " </span><span class="keyword">. </span><span class="default">LogoutUser</span><span class="keyword">;<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="52133"></a>
 <div class="note">
  <strong class='user'>hafenator2000 at yahoo dot com</strong>
  <a href="#52133" class="date">21-Apr-2005 02:09</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
PHP Modules also define constants.&nbsp; Make sure to avoid constant name collisions.&nbsp; There are two ways to do this that I can think of.<br />
First: in your code make sure that the constant name is not already used.&nbsp; ex. <span class="default">&lt;?php </span><span class="keyword">if (! </span><span class="default">defined</span><span class="keyword">(</span><span class="string">"CONSTANT_NAME"</span><span class="keyword">)) { </span><span class="default">Define</span><span class="keyword">(</span><span class="string">"CONSTANT_NAME"</span><span class="keyword">,</span><span class="string">"Some Value"</span><span class="keyword">); } </span><span class="default">?&gt;</span>&nbsp; This can get messy when you start thinking about collision handling, and the implications of this.<br />
Second: Use some off prepend to all your constant names without exception&nbsp; ex. <span class="default">&lt;?php Define</span><span class="keyword">(</span><span class="string">"SITE_CONSTANT_NAME"</span><span class="keyword">,</span><span class="string">"Some Value"</span><span class="keyword">); </span><span class="default">?&gt;<br />
</span><br />
Perhaps the developers or documentation maintainers could recommend a good prepend and ask module writers to avoid that prepend in modules.</span>
</code></div>
  </div>
 </div>
 <a name="52008"></a>
 <div class="note">
  <strong class='user'>storm</strong>
  <a href="#52008" class="date">18-Apr-2005 09:54</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
An undefined constant evaluates as true when not used correctly. Say for example you had something like this:<br />
<br />
settings.php<br />
<span class="default">&lt;?php<br />
</span><span class="comment">// Debug mode<br />
</span><span class="default">define</span><span class="keyword">(</span><span class="string">'DEBUG'</span><span class="keyword">,</span><span class="default">false</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span><br />
test.php<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">include(</span><span class="string">'settings.php'</span><span class="keyword">);<br />
<br />
if (</span><span class="default">DEBUG</span><span class="keyword">) {<br />
&nbsp;&nbsp; </span><span class="comment">// echo some sensitive data.<br />
</span><span class="keyword">}<br />
</span><span class="default">?&gt;<br />
</span><br />
If for some reason settings.php doesn't get included and the DEBUG constant is not set, PHP will STILL print the sensitive data. The solution is to evaluate it. Like so:<br />
<br />
settings.php<br />
<span class="default">&lt;?php<br />
</span><span class="comment">// Debug mode<br />
</span><span class="default">define</span><span class="keyword">(</span><span class="string">'DEBUG'</span><span class="keyword">,</span><span class="default">0</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span><br />
test.php<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">include(</span><span class="string">'settings.php'</span><span class="keyword">);<br />
<br />
if (</span><span class="default">DEBUG </span><span class="keyword">== </span><span class="default">1</span><span class="keyword">) {<br />
&nbsp;&nbsp; </span><span class="comment">// echo some sensitive data.<br />
</span><span class="keyword">}<br />
</span><span class="default">?&gt;<br />
</span><br />
Now it works correctly.</span>
</code></div>
  </div>
 </div>
 <a name="36875"></a>
 <div class="note">
  <strong class='user'>kumar at farmdev</strong>
  <a href="#36875" class="date">25-Oct-2003 05:59</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
before embarking on creating a language system I wanted to see if there was any speed advantage to defining language strings as constants vs. variables or array items.&nbsp; It is more logical to define language strings as constants but you have more flexibility using variables or arrays in your code (i.e. they can be accessed directly, concatenated, used in quotes, used in heredocs whereas constants can only be accessed directly or concatenated).<br />
<br />
Results of the test:<br />
declaring as $Variable is fastest<br />
declaring with define() is second fastest<br />
declaring as $Array['Item'] is slowest<br />
<br />
=======================================<br />
the test was done using PHP 4.3.2, Apache 1.3.27, and the ab (apache bench) tool.<br />
100 requests (1 concurrent) were sent to one php file that includes 15 php files each containing 100 unique declarations of a language string.<br />
<br />
Example of each declaration ("Variable" numbered 1 - 1500):<br />
<span class="default">&lt;?php<br />
$GLOBALS</span><span class="keyword">[</span><span class="string">'Variable1'</span><span class="keyword">] = </span><span class="string">"A whole lot of text for this variable as if it were a language string containing a whole lot of text"</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
&lt;?php<br />
define</span><span class="keyword">(</span><span class="string">'Variable1' </span><span class="keyword">, </span><span class="string">"A whole lot of text for this variable as if it were a language string containing a whole lot of text"</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
&lt;?php<br />
$GLOBALS</span><span class="keyword">[</span><span class="string">'CP_Lang'</span><span class="keyword">][</span><span class="string">'Variable1'</span><span class="keyword">] = </span><span class="string">"A whole lot of text for this variable as if it were a language string containing a whole lot of text"</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
Here are the exact averages of each ab run of 100 requests (averages based on 6 runs):<br />
variable (24.956 secs)<br />
constant (25.426 secs)<br />
array (28.141)<br />
<br />
(not huge differences but good to know that using variables won't take a huge performance hit)</span>
</code></div>
  </div>
 </div>
 <a name="35064"></a>
 <div class="note">
  <strong class='user'>ewspencer at industrex dot com</strong>
  <a href="#35064" class="date">18-Aug-2003 06:30</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I find using the concatenation operator helps disambiguate value assignments with constants. For example, setting constants in a global configuration file:<br />
<br />
<span class="default">&lt;?php<br />
define</span><span class="keyword">(</span><span class="string">'LOCATOR'</span><span class="keyword">,&nbsp;&nbsp; </span><span class="string">"/locator"</span><span class="keyword">);<br />
</span><span class="default">define</span><span class="keyword">(</span><span class="string">'CLASSES'</span><span class="keyword">,&nbsp;&nbsp; </span><span class="default">LOCATOR</span><span class="keyword">.</span><span class="string">"/code/classes"</span><span class="keyword">);<br />
</span><span class="default">define</span><span class="keyword">(</span><span class="string">'FUNCTIONS'</span><span class="keyword">, </span><span class="default">LOCATOR</span><span class="keyword">.</span><span class="string">"/code/functions"</span><span class="keyword">);<br />
</span><span class="default">define</span><span class="keyword">(</span><span class="string">'USERDIR'</span><span class="keyword">,&nbsp;&nbsp; </span><span class="default">LOCATOR</span><span class="keyword">.</span><span class="string">"/user"</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span><br />
Later, I can use the same convention when invoking a constant's value for static constructs such as require() calls:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">require_once(</span><span class="default">FUNCTIONS</span><span class="keyword">.</span><span class="string">"/database.fnc"</span><span class="keyword">);<br />
require_once(</span><span class="default">FUNCTIONS</span><span class="keyword">.</span><span class="string">"/randchar.fnc"</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span><br />
as well as dynamic constructs, typical of value assignment to variables:<br />
<br />
<span class="default">&lt;?php<br />
$userid&nbsp; </span><span class="keyword">= </span><span class="default">randchar</span><span class="keyword">(</span><span class="default">8</span><span class="keyword">,</span><span class="string">'anc'</span><span class="keyword">,</span><span class="string">'u'</span><span class="keyword">);<br />
</span><span class="default">$usermap </span><span class="keyword">= </span><span class="default">USERDIR</span><span class="keyword">.</span><span class="string">"/"</span><span class="keyword">.</span><span class="default">$userid</span><span class="keyword">.</span><span class="string">".png"</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
The above convention works for me, and helps produce self-documenting code.<br />
<br />
-- Erich</span>
</code></div>
  </div>
 </div>
 <a name="19363"></a>
 <div class="note">
  <strong class='user'>katana at katana-inc dot com</strong>
  <a href="#19363" class="date">25-Feb-2002 11:53</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Warning, constants used within the heredoc syntax (<a href="@w{GRSENCRS}" rel="nofollow" target="_blank">@w{GRSENCRS}</a>) are not interpreted!<br />
<br />
Editor's Note: This is true. PHP has no way of recognizing the constant from any other string of characters within the heredoc block.</span>
</code></div>
  </div>
 </div>
 <a name="7489"></a>
 <div class="note">
  <strong class='user'>tom dot harris at home dot com</strong>
  <a href="#7489" class="date">04-Aug-2000 05:44</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
To get a full path (the equivalent of something like "__PATH__") use <br />
dirname($SCRIPT_FILENAME)<br />
to get the directory name of the called script and<br />
dirname(__FILE__)<br />
to get the directory name of the include file.</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=language.constants&amp;redirect=http://www.php.net/manual/en/language.constants.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.constants&amp;redirect=http://www.php.net/manual/en/language.constants.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/language.constants.php">show source</a> |
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