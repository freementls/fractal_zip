<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Integers - Manual</title>
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
 <link rel="prev" href="language.types.boolean.php" />
 <link rel="next" href="language.types.float.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/types.integer" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/language.types.integer.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/language.types.integer.php" />
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
 <li class="active"><a href="language.types.integer.php">Integers</a></li>
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
  <a href="language.types.float.php">Floating point numbers<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.types.boolean.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Booleans</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.types.integer.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/language.types.integer.php">Brazilian Portuguese</option>
    <option value="zh/language.types.integer.php">Chinese (Simplified)</option>
    <option value="fr/language.types.integer.php">French</option>
    <option value="de/language.types.integer.php">German</option>
    <option value="ja/language.types.integer.php">Japanese</option>
    <option value="pl/language.types.integer.php">Polish</option>
    <option value="ro/language.types.integer.php">Romanian</option>
    <option value="ru/language.types.integer.php">Russian</option>
    <option value="fa/language.types.integer.php">Persian</option>
    <option value="es/language.types.integer.php">Spanish</option>
    <option value="tr/language.types.integer.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="language.types.integer" class="sect1">
 <h2 class="title">Integers</h2>
 
 <p class="simpara">
  An <span class="type"><a href="language.types.integer.php" class="type integer">integer</a></span> is a number of the set
  ℤ = {..., -2, -1, 0, 1, 2, ...}.
 </p>
  
 <p class="para">
  See also:
 </p>

 <ul class="itemizedlist">
  <li class="listitem">
   <span class="simpara">
    <a href="ref.gmp.php" class="link">Arbitrary length integer / GMP</a>
   </span>
  </li>
  <li class="listitem">
   <span class="simpara">
    <a href="language.types.float.php" class="link">Floating point numbers</a>
   </span>
  </li>
  <li class="listitem">
   <span class="simpara">
    <a href="ref.bc.php" class="link">Arbitrary precision / BCMath</a>
   </span>
  </li>
 </ul>

 <div class="sect2" id="language.types.integer.syntax">
  <h3 class="title">Syntax</h3>

  <p class="simpara">
   <span class="type"><a href="language.types.integer.php" class="type Integer">Integer</a></span>s can be specified in decimal (base 10), hexadecimal
   (base 16), octal (base 8) or binary (base 2) notation, optionally preceded by a sign
   (- or +).
  </p>

  <p class="para">
   Binary <span class="type"><a href="language.types.integer.php" class="type integer">integer</a></span> literals are available since PHP 5.4.0. 
  </p>

  <p class="para">
   To use octal notation, precede the number with a <em>0</em> (zero).
   To use hexadecimal notation precede the number with <em>0x</em>.
   To use binary notation precede the number with <em>0b</em>.
  </p>

  <div class="example" id="example-67">
   <p><strong>Example #1 Integer literals</strong></p>
   <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br />$a&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">1234</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;decimal&nbsp;number<br /></span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">=&nbsp;-</span><span style="color: #0000BB">123</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;a&nbsp;negative&nbsp;number<br /></span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">0123</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;octal&nbsp;number&nbsp;(equivalent&nbsp;to&nbsp;83&nbsp;decimal)<br /></span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">0x1A</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;hexadecimal&nbsp;number&nbsp;(equivalent&nbsp;to&nbsp;26&nbsp;decimal)<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
   </div>

  </div>

  <p class="para">
   Formally, the structure for <span class="type"><a href="language.types.integer.php" class="type integer">integer</a></span> literals is:
  </p>
 
  <div class="informalexample">
   <div class="example-contents">
<div class="cdata"><pre>
decimal     : [1-9][0-9]*
            | 0

hexadecimal : 0[xX][0-9a-fA-F]+

octal       : 0[0-7]+

binary      : 0b[01]+

integer     : [+-]?decimal
            | [+-]?hexadecimal
            | [+-]?octal
            | [+-]?binary
</pre></div>
   </div>

  </div>

  <p class="para">
   The size of an <span class="type"><a href="language.types.integer.php" class="type integer">integer</a></span> is platform-dependent, although a maximum
   value of about two billion is the usual value (that&#039;s 32 bits signed).
   64-bit platforms usually have a maximum value of about 9E18. PHP
   does not support unsigned <span class="type"><a href="language.types.integer.php" class="type integer">integer</a></span>s. <span class="type"><a href="language.types.integer.php" class="type Integer">Integer</a></span> size
   can be determined using the constant <strong><code>PHP_INT_SIZE</code></strong>, and
   maximum value using the constant <strong><code>PHP_INT_MAX</code></strong> since
   PHP 4.4.0 and PHP 5.0.5.
  </p>

  <div class="warning"><strong class="warning">Warning</strong>
   <p class="para">
    If an invalid digit is given in an octal <span class="type"><a href="language.types.integer.php" class="type integer">integer</a></span> (i.e. 8 or 9),
    the rest of the number is ignored.
   </p>

   <div class="example" id="example-68">
    <p><strong>Example #2 Octal weirdness</strong></p>
    <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br />var_dump</span><span style="color: #007700">(</span><span style="color: #0000BB">01090</span><span style="color: #007700">);&nbsp;</span><span style="color: #FF8000">//&nbsp;010&nbsp;octal&nbsp;=&nbsp;8&nbsp;decimal<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

   </div>
  </div>
 </div>

 <div class="sect2" id="language.types.integer.overflow">
  <h3 class="title">Integer overflow</h3>

  <p class="para">
   If PHP encounters a number beyond the bounds of the <span class="type"><a href="language.types.integer.php" class="type integer">integer</a></span>
   type, it will be interpreted as a <span class="type"><a href="language.types.float.php" class="type float">float</a></span> instead. Also, an
   operation which results in a number beyond the bounds of the
   <span class="type"><a href="language.types.integer.php" class="type integer">integer</a></span> type will return a <span class="type"><a href="language.types.float.php" class="type float">float</a></span> instead.
  </p>

  <div class="example" id="example-69">
   <p><strong>Example #3 Integer overflow on a 32-bit system</strong></p>
   <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br />$large_number&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">2147483647</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">var_dump</span><span style="color: #007700">(</span><span style="color: #0000BB">$large_number</span><span style="color: #007700">);&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;int(2147483647)<br /><br /></span><span style="color: #0000BB">$large_number&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">2147483648</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">var_dump</span><span style="color: #007700">(</span><span style="color: #0000BB">$large_number</span><span style="color: #007700">);&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;float(2147483648)<br /><br /></span><span style="color: #0000BB">$million&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">1000000</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">$large_number&nbsp;</span><span style="color: #007700">=&nbsp;&nbsp;</span><span style="color: #0000BB">50000&nbsp;</span><span style="color: #007700">*&nbsp;</span><span style="color: #0000BB">$million</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">var_dump</span><span style="color: #007700">(</span><span style="color: #0000BB">$large_number</span><span style="color: #007700">);&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;float(50000000000)<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
   </div>

  </div>

  <div class="example" id="example-70">
   <p><strong>Example #4 Integer overflow on a 64-bit system</strong></p>
   <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br />$large_number&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">9223372036854775807</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">var_dump</span><span style="color: #007700">(</span><span style="color: #0000BB">$large_number</span><span style="color: #007700">);&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;int(9223372036854775807)<br /><br /></span><span style="color: #0000BB">$large_number&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">9223372036854775808</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">var_dump</span><span style="color: #007700">(</span><span style="color: #0000BB">$large_number</span><span style="color: #007700">);&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;float(9.2233720368548E+18)<br /><br /></span><span style="color: #0000BB">$million&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">1000000</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">$large_number&nbsp;</span><span style="color: #007700">=&nbsp;&nbsp;</span><span style="color: #0000BB">50000000000000&nbsp;</span><span style="color: #007700">*&nbsp;</span><span style="color: #0000BB">$million</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">var_dump</span><span style="color: #007700">(</span><span style="color: #0000BB">$large_number</span><span style="color: #007700">);&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;float(5.0E+19)<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
   </div>

  </div>
  
  <p class="para">
   There is no <span class="type"><a href="language.types.integer.php" class="type integer">integer</a></span> division operator in PHP.
   <em>1/2</em> yields the <span class="type"><a href="language.types.float.php" class="type float">float</a></span> <em>0.5</em>.
   The value can be casted to an <span class="type"><a href="language.types.integer.php" class="type integer">integer</a></span> to round it downwards, or
   the  <span class="function"><a href="function.round.php" class="function">round()</a></span> function provides finer control over rounding.
  </p>

  <div class="informalexample">
   <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br />var_dump</span><span style="color: #007700">(</span><span style="color: #0000BB">25</span><span style="color: #007700">/</span><span style="color: #0000BB">7</span><span style="color: #007700">);&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;float(3.5714285714286)&nbsp;<br /></span><span style="color: #0000BB">var_dump</span><span style="color: #007700">((int)&nbsp;(</span><span style="color: #0000BB">25</span><span style="color: #007700">/</span><span style="color: #0000BB">7</span><span style="color: #007700">));&nbsp;</span><span style="color: #FF8000">//&nbsp;int(3)<br /></span><span style="color: #0000BB">var_dump</span><span style="color: #007700">(</span><span style="color: #0000BB">round</span><span style="color: #007700">(</span><span style="color: #0000BB">25</span><span style="color: #007700">/</span><span style="color: #0000BB">7</span><span style="color: #007700">));&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;float(4)&nbsp;<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
   </div>

  </div>
 </div>

 <div class="sect2" id="language.types.integer.casting">
  <h3 class="title">Converting to integer</h3>

  <p class="simpara">
   To explicitly convert a value to <span class="type"><a href="language.types.integer.php" class="type integer">integer</a></span>, use either the
   <em>(int)</em> or <em>(integer)</em> casts. However, in
   most cases the cast is not needed, since a value will be automatically
   converted if an operator, function or control structure requires an
   <span class="type"><a href="language.types.integer.php" class="type integer">integer</a></span> argument. A value can also be converted to
   <span class="type"><a href="language.types.integer.php" class="type integer">integer</a></span> with the  <span class="function"><a href="function.intval.php" class="function">intval()</a></span> function.
  </p>

  <p class="simpara">
   See also: <a href="language.types.type-juggling.php" class="link">type-juggling</a>.
  </p>
   
  <div class="sect3" id="language.types.integer.casting.from-boolean">
   <h4 class="title">From <a href="language.types.boolean.php" class="link">booleans</a></h4>

   <p class="simpara">
    <strong><code>FALSE</code></strong> will yield <em>0</em> (zero), and <strong><code>TRUE</code></strong> will yield
    <em>1</em> (one).
   </p>
  </div>

  <div class="sect3" id="language.types.integer.casting.from-float">
   <h4 class="title">
    From <a href="language.types.float.php" class="link">floating point numbers</a>
   </h4> 

   <p class="simpara">
    When converting from <span class="type"><a href="language.types.float.php" class="type float">float</a></span> to <span class="type"><a href="language.types.integer.php" class="type integer">integer</a></span>, the number
    will be rounded <em class="emphasis">towards zero</em>.
   </p>
    
   <p class="para">
    If the float is beyond the boundaries of <span class="type"><a href="language.types.integer.php" class="type integer">integer</a></span> (usually
    <em>+/- 2.15e+9 = 2^31</em> on 32-bit platforms and
    <em>+/- 9.22e+18 = 2^63</em> on 64-bit platforms), the result is
    undefined, since the <span class="type"><a href="language.types.float.php" class="type float">float</a></span> doesn&#039;t have enough precision to
    give an exact <span class="type"><a href="language.types.integer.php" class="type integer">integer</a></span> result. No warning, not even a notice
    will be issued when this happens!
   </p>
    
   <div class="warning"><strong class="warning">Warning</strong>
    <p class="para">
     Never cast an unknown fraction to <span class="type"><a href="language.types.integer.php" class="type integer">integer</a></span>, as this can
     sometimes lead to unexpected results.
    </p>

    <div class="informalexample">
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">echo&nbsp;(int)&nbsp;(&nbsp;(</span><span style="color: #0000BB">0.1</span><span style="color: #007700">+</span><span style="color: #0000BB">0.7</span><span style="color: #007700">)&nbsp;*&nbsp;</span><span style="color: #0000BB">10&nbsp;</span><span style="color: #007700">);&nbsp;</span><span style="color: #FF8000">//&nbsp;echoes&nbsp;7!<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

    </div>
    
    <p class="para">
     See also the <a href="language.types.float.php#warn.float-precision" class="link">warning about float
     precision</a>.
    </p>
   </div>
  </div>
   
  <div class="sect3" id="language.types.integer.casting.from-string">
   <h4 class="title">From strings</h4>

   <p class="simpara">
    See <a href="language.types.string.php#language.types.string.conversion" class="link">String conversion to
    numbers</a>
   </p>
  </div>
   
  <div class="sect3" id="language.types.integer.casting.from-other">
   <h4 class="title">From other types</h4>

   <div class="caution"><strong class="caution">Caution</strong>
    <p class="simpara">
     The behaviour of converting to <span class="type"><a href="language.types.integer.php" class="type integer">integer</a></span> is undefined for other
     types. Do <em class="emphasis">not</em> rely on any observed behaviour, as it
     can change without notice.
    </p>
   </div>
  </div>

 </div>
</div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="language.types.float.php">Floating point numbers<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.types.boolean.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Booleans</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.types.integer.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=language.types.integer&amp;redirect=http://www.php.net/manual/en/language.types.integer.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.types.integer&amp;redirect=http://www.php.net/manual/en/language.types.integer.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Integers</strong>
 </div><div id="allnotes">
 <a name="103506"></a>
 <div class="note">
  <strong class='user'>Richard</strong>
  <a href="#103506" class="date">16-Apr-2011 09:53</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Integer arithmetic in PHP is more accurate than one might think. On a 32-bit system, the largest value that can be held in an INT is&nbsp; 2147483647.<br />
However, a FLOAT can accurately hold integer values up to 10000000000000.<br />
(this is because the significand precision of a double is 53-bits).</span>
</code></div>
  </div>
 </div>
 <a name="103442"></a>
 <div class="note">
  <strong class='user'>php at keith tyler dot com</strong>
  <a href="#103442" class="date">13-Apr-2011 11:08</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you need to convert a numeric string (or more to the point, an object that represents a numeric value) that is greater then PHP_INT_MAX, and you don't have GMP or BCMath installed, you can cast to float.<br />
<br />
For example, when using SimpleXMLElement, you sometimes have to cast the extracted values, such as xml attributes, because they are returned as SimpleXMLElements and not their values' native types. While print() has no trouble with converting them, other functions, such as max(), might not.<br />
<br />
But if you cast such a value with (int), and it is over PHP_INT_MAX, you will just get PHP_INT_MAX (and vice versa for negative numbers). <br />
<br />
The Q&amp;D no-muss solution is to cast to (float) instead.</span>
</code></div>
  </div>
 </div>
 <a name="102925"></a>
 <div class="note">
  <strong class='user'>pere dot cil at wanadoo dot fr</strong>
  <a href="#102925" class="date">15-Mar-2011 01:59</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Please also note that the maximum stored in the integer depends on the platform / compilation; on windows xp 32 bits, the following value:<br />
<br />
0x5468792130ABCDEF<br />
<br />
echoes to:<br />
<br />
6.0822444802213E+18 (cast to float)<br />
<br />
On a fully 64 bits system, it echoes to:<br />
<br />
6082244480221302255</span>
</code></div>
  </div>
 </div>
 <a name="100365"></a>
 <div class="note">
  <strong class='user'>iletras at yahoo dot com</strong>
  <a href="#100365" class="date">11-Oct-2010 08:19</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
//This is a (simpler ?) function to return number of digits of an integer.<br />
<br />
//function declaration<br />
function count_digit($number) {<br />
&nbsp; return&nbsp; strlen((string) $number);<br />
}<br />
<br />
//function call<br />
$num = 12312;<br />
$number_of_digits = count_digit($num); //this is call :)<br />
echo $number_of_digits; <br />
//prints 5</span>
</code></div>
  </div>
 </div>
 <a name="88075"></a>
 <div class="note">
  <strong class='user'>sean dot gilbertson at gmail dot com</strong>
  <a href="#88075" class="date">08-Jan-2009 01:57</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
You can make a signed, negative integer an unsigned integer (in string form) by doing the following:<br />
<br />
<span class="default">&lt;?php<br />
$unsigned </span><span class="keyword">= </span><span class="default">sprintf</span><span class="keyword">(</span><span class="string">'%u'</span><span class="keyword">, -</span><span class="default">5</span><span class="keyword">);<br />
<br />
echo </span><span class="default">$unsigned</span><span class="keyword">; </span><span class="comment">// prints 4294967291<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="86704"></a>
 <div class="note">
  <strong class='user'>Giovanni</strong>
  <a href="#86704" class="date">30-Oct-2008 10:22</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
why not just using logarithms?<br />
<br />
$num_of_digits = (int)(log($num,$base) +1);<br />
<br />
I think this should be more performant (as it relies on the math coprocessor) and it could be extended to number of digits representing the number in any base, not just decimal</span>
</code></div>
  </div>
 </div>
 <a name="86588"></a>
 <div class="note">
  <strong class='user'>Hamza Burak Ylmaz</strong>
  <a href="#86588" class="date">24-Oct-2008 02:36</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
<span class="default">&lt;?php<br />
</span><span class="comment">//This is a simple function to return number of digits of an integer.<br />
<br />
//function declaration<br />
</span><span class="keyword">function </span><span class="default">count_digit</span><span class="keyword">(</span><span class="default">$number</span><span class="keyword">)<br />
{<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$digit </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; do<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$number </span><span class="keyword">/= </span><span class="default">10</span><span class="keyword">;&nbsp; &nbsp; &nbsp; </span><span class="comment">//$number = $number / 10;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$number </span><span class="keyword">= </span><span class="default">intval</span><span class="keyword">(</span><span class="default">$number</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$digit</span><span class="keyword">++;&nbsp; &nbsp; <br />
&nbsp;&nbsp;&nbsp; }while(</span><span class="default">$number</span><span class="keyword">!=</span><span class="default">0</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">$digit</span><span class="keyword">;<br />
}<br />
<br />
</span><span class="comment">//function call<br />
</span><span class="default">$num </span><span class="keyword">= </span><span class="default">12312</span><span class="keyword">;<br />
</span><span class="default">$number_of_digits </span><span class="keyword">= </span><span class="default">count_digit</span><span class="keyword">(</span><span class="default">$num</span><span class="keyword">); </span><span class="comment">//this is call :)<br />
</span><span class="keyword">echo </span><span class="default">$number_of_digits</span><span class="keyword">;<br />
</span><span class="comment">//prints 5<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="86180"></a>
 <div class="note">
  <strong class='user'>wbcarts at juno dot com</strong>
  <a href="#86180" class="date">06-Oct-2008 06:25</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
PHP offers a slew of built-in functions and automatic type-casting routines which can get pretty complicated. But most of the time, you still have to take matters into your own hands and allow PHP to do its thing. In that case, and something that has NOT been mentioned, is how to construct your code. To keep things simple, I divide all my scripts in half. The top half gives my scripts the "capability" they need, and the lower half is the actual code to be "run" or "executed".<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="comment">/*<br />
&nbsp;* build the program's capability - define variables and functions...<br />
&nbsp;*/<br />
</span><span class="default">$item_label </span><span class="keyword">= </span><span class="string">''</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp; </span><span class="comment">// type string<br />
</span><span class="default">$item_price </span><span class="keyword">= </span><span class="default">0.0</span><span class="keyword">;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// type float<br />
</span><span class="default">$item_qty </span><span class="keyword">= </span><span class="default">1</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// type integer<br />
</span><span class="default">$item_total </span><span class="keyword">= </span><span class="default">0.0</span><span class="keyword">;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// type float - to set use calculate()<br />
<br />
</span><span class="keyword">function </span><span class="default">calculate</span><span class="keyword">(){<br />
&nbsp; global </span><span class="default">$item_price</span><span class="keyword">, </span><span class="default">$item_qty</span><span class="keyword">, </span><span class="default">$item_total</span><span class="keyword">;<br />
&nbsp; </span><span class="default">$item_price </span><span class="keyword">= </span><span class="default">number_format</span><span class="keyword">(</span><span class="default">$item_price</span><span class="keyword">, </span><span class="default">2</span><span class="keyword">);<br />
&nbsp; </span><span class="default">$item_total </span><span class="keyword">= </span><span class="default">number_format</span><span class="keyword">((</span><span class="default">$item_price </span><span class="keyword">* </span><span class="default">$item_qty</span><span class="keyword">), </span><span class="default">2</span><span class="keyword">);<br />
}<br />
<br />
function </span><span class="default">itemToString</span><span class="keyword">() {<br />
&nbsp; global </span><span class="default">$item_label</span><span class="keyword">, </span><span class="default">$item_price</span><span class="keyword">, </span><span class="default">$item_qty</span><span class="keyword">, </span><span class="default">$item_total</span><span class="keyword">;<br />
&nbsp; return </span><span class="string">"$item_label [price=\$$item_price, qty=$item_qty, total=\$$item_total]"</span><span class="keyword">;<br />
}<br />
<br />
</span><span class="comment">/*<br />
&nbsp;* run the program - set data, call methods...<br />
&nbsp;*/<br />
</span><span class="default">$item_label </span><span class="keyword">= </span><span class="string">"Coffee"</span><span class="keyword">;<br />
</span><span class="default">$item_price </span><span class="keyword">= </span><span class="default">3.89</span><span class="keyword">;<br />
</span><span class="default">$item_qty </span><span class="keyword">= </span><span class="default">2</span><span class="keyword">;<br />
</span><span class="default">calculate</span><span class="keyword">();&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// set $item_total<br />
</span><span class="keyword">echo </span><span class="default">itemToString</span><span class="keyword">();&nbsp;&nbsp; </span><span class="comment">// -&gt; Coffee [price=$3.89, qty=2, total=$7.78]<br />
<br />
</span><span class="default">$item_label </span><span class="keyword">= </span><span class="string">"Chicken"</span><span class="keyword">;<br />
</span><span class="default">$item_price </span><span class="keyword">= </span><span class="default">.80</span><span class="keyword">;&nbsp; &nbsp;&nbsp; </span><span class="comment">// per lb.<br />
</span><span class="default">$item_qty </span><span class="keyword">= </span><span class="default">3.5</span><span class="keyword">;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// lbs.<br />
</span><span class="default">calculate</span><span class="keyword">();&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// set $item_total<br />
</span><span class="keyword">echo </span><span class="default">itemToString</span><span class="keyword">();&nbsp;&nbsp; </span><span class="comment">// -&gt; Chicken [price=$0.80, qty=3.5, total=$2.80]<br />
</span><span class="default">?&gt;<br />
</span>Note: All type-casting is done by PHP's built-in number_format() method. This allows our program to enter any number (float or int) on item price or quantity in the runtime part of our script. Also, if we explicitly cast values to integer in the capability part of our script, then we start getting results that may not be desirable for this program. For example, if in the calculate method we cast item_qty to integer, then we can no longer sell chicken by the pound!</span>
</code></div>
  </div>
 </div>
 <a name="85173"></a>
 <div class="note">
  <strong class='user'>dbmuller at gmail dot com</strong>
  <a href="#85173" class="date">18-Aug-2008 04:43</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
be careful relying on PHP's data type handling.&nbsp; I have a class that handles database calls and in there a function to handle types and formatting them for insertion.<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">case </span><span class="default">constants</span><span class="keyword">::</span><span class="default">int</span><span class="keyword">:<br />
&nbsp; </span><span class="default">$returnString </span><span class="keyword">= (</span><span class="default">$valueString </span><span class="keyword">&gt;= </span><span class="default">0</span><span class="keyword">) ? </span><span class="default">$valueString </span><span class="keyword">: </span><span class="string">"null"</span><span class="keyword">;<br />
&nbsp; break;<br />
</span><span class="default">?&gt;<br />
</span><br />
Will evaluate to false if a user enters "0" since PHP thinks that the 0 is a Boolean.&nbsp; The following code fixes it:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">case </span><span class="default">constants</span><span class="keyword">::</span><span class="default">int</span><span class="keyword">:<br />
&nbsp; </span><span class="default">$returnString </span><span class="keyword">= ((int)</span><span class="default">$valueString </span><span class="keyword">&gt;= </span><span class="default">0</span><span class="keyword">) ? (int)</span><span class="default">$valueString </span><span class="keyword">: </span><span class="string">"null"</span><span class="keyword">;<br />
&nbsp;break;<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="84889"></a>
 <div class="note">
  <strong class='user'>rustamabd at gmail dot com</strong>
  <a href="#84889" class="date">04-Aug-2008 04:45</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Be very careful with code that relies on integer overflow. Negative overflow is handled differently on different platforms. For example, this code:<br />
<span class="default">&lt;?php<br />
&nbsp; </span><span class="keyword">echo (int)-</span><span class="default">3000000000</span><span class="keyword">; </span><span class="comment">// a 32bit negative overflow<br />
</span><span class="default">?&gt;<br />
</span>... outputs 1294967296 on Windows, and -2147483648 on FreeBSD.<br />
(Tested with php 5.2.6, freebsd 7.0)</span>
</code></div>
  </div>
 </div>
 <a name="83790"></a>
 <div class="note">
  <strong class='user'>eric</strong>
  <a href="#83790" class="date">11-Jun-2008 01:50</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
In response to the comment by me at troyswanson dot net:<br />
<br />
-2147483648 falls into the range of 32 bit signed integers yet php treats it as a float.&nbsp; However, -2147483647-1 is treated as an integer.<br />
<br />
The following code demonstrates:<br />
<span class="default">&lt;?php<br />
&nbsp;&nbsp;&nbsp; var_dump</span><span class="keyword">(-</span><span class="default">2147483648</span><span class="keyword">); </span><span class="comment">//float(-2147483648)<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">var_dump</span><span class="keyword">(-</span><span class="default">2147483647 </span><span class="keyword">- </span><span class="default">1</span><span class="keyword">); </span><span class="comment">//int(-2147483648)<br />
</span><span class="default">?&gt;<br />
</span><br />
This is probably very similar to the MS C bug which also treats -2147483648 as an UNSIGNED because it thinks it's out of the range of a signed int.<br />
<br />
The problem is that the parser does not view "-x" as a single token, but rather as two, "-" and "x".&nbsp; Since "x" is out of the range of an INT, it is promoted to float, even though in this unique case, "-x" is in the range of an int.<br />
<br />
The best cure is probably to replace "-2147483648" with "0x80000000", as that is the hexadecimal equivalent of the same number.<br />
<br />
Hope that helps explain what's going on<br />
<br />
Peace<br />
<br />
&nbsp;- Eric / fez</span>
</code></div>
  </div>
 </div>
 <a name="83768"></a>
 <div class="note">
  <strong class='user'>winterheat</strong>
  <a href="#83768" class="date">10-Jun-2008 03:25</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
PHP_INT_SIZE seems to be 8 when it is 64 bit integers... so 8 means the number of bytes, or number of 8-bits.</span>
</code></div>
  </div>
 </div>
 <a name="80923"></a>
 <div class="note">
  <strong class='user'>Elliott Brueggeman</strong>
  <a href="#80923" class="date">06-Feb-2008 07:52</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Don't forget about the integer minimum value. From my experimentation, the lowest valid integer is (PHP_INT_MAX * -1)-1. All values smaller than this fail the is_int() test, even though the may appear to act normally during mathematic operations. More info on this: <a href="http://www.ebrueggeman.com/blog/php/integers-and-floating-numbers/" rel="nofollow" target="_blank">http://www.ebrueggeman.com/blog/php/integers-and-floating-numbers/</a></span>
</code></div>
  </div>
 </div>
 <a name="80303"></a>
 <div class="note">
  <strong class='user'>bart at NOvankuikSPAM dot nl</strong>
  <a href="#80303" class="date">09-Jan-2008 01:06</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
When handling very large numbers in PHP, you'll notice they get cut off at hexadecimal 7FFFF FFFF. Sometimes, you don't need to use these numbers in an actual calculation in PHP (i.e. just editing and displaying), and just need to save them in a database.<br />
<br />
In that case, you can let MySQL handle the conversion from and to hexadecimal notation. In the example below, engineers need to save hexadecimal addresses up to FFFF FFFF. To update such a value in MySQL, use the following query, where 'addr' is a column with type unsigned integer(10).<br />
<br />
<span class="default">&lt;?php<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; $query </span><span class="keyword">= </span><span class="string">"<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; UPDATE hardware_register<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; SET&nbsp; &nbsp; name = ?,<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; type = ?,<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; addr = conv(?, 16, 10)<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; WHERE&nbsp; id = ?<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; "</span><span class="keyword">;<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
And selecting:<br />
<span class="default">&lt;?php<br />
&nbsp;&nbsp;&nbsp; $query </span><span class="keyword">= </span><span class="string">"<br />
&nbsp;&nbsp;&nbsp; SELECT name, type, conv(addr, 10, 16)<br />
&nbsp;&nbsp;&nbsp; FROM&nbsp;&nbsp; hardware_register<br />
&nbsp;&nbsp;&nbsp; WHERE&nbsp; id = ?<br />
&nbsp;&nbsp;&nbsp; "</span><span class="keyword">;<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
Note that you'll have to treat the resulting addr column as a string everywhere in PHP. You can't do conversions like:<br />
<br />
<span class="default">&lt;?php<br />
&nbsp;&nbsp;&nbsp; $addr_decimal </span><span class="keyword">= </span><span class="default">sprintf</span><span class="keyword">(</span><span class="string">"%X"</span><span class="keyword">, </span><span class="default">$addr_column</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span><br />
because that'll result in $addr_decimal having the cut-off, maximum int value.</span>
</code></div>
  </div>
 </div>
 <a name="79490"></a>
 <div class="note">
  <strong class='user'>autotelic at NOOOOSPAM dot hotmail dot com</strong>
  <a href="#79490" class="date">29-Nov-2007 10:36</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
A note about converting IP addresses for storage in database.&nbsp; For MySQL, this is unnecessary as it has built in support via the INET functions.&nbsp; Also, there is no need to use BIGINT.&nbsp; UNSIGNED INT is, at 4 bytes, the perfect size for holding an IP (column must be defined as UNSIGNED).&nbsp; This can basically halve the storage size, as BIGINT is an 8 byte data type.<br />
<br />
INET_ATON() converts a dotted IP string to INT:<br />
INSERT table(ip) VALUES(INET_ATON('127.0.0.1'));<br />
<br />
INET_NTOA() converts an INT to dotted IP string:<br />
SELECT INET_NTOA(ip) FROM table<br />
returns '127.0.0.1'<br />
<br />
Details:<br />
<a href="http://dev.mysql.com/doc/refman/5.1/en/miscellaneous-functions.html" rel="nofollow" target="_blank">http://dev.mysql.com/doc/refman/5.1/en/miscellaneous-functions.html</a></span>
</code></div>
  </div>
 </div>
 <a name="79234"></a>
 <div class="note">
  <strong class='user'>darkshire</strong>
  <a href="#79234" class="date">16-Nov-2007 04:56</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
d_n at NOSPAM dot Loryx dot com<br />
13-Aug-2007 05:33<br />
Here are some tricks to convert from a "dotted" IP address to a LONG int, and backwards. This is very useful because accessing an IP addy in a database table is very much faster if it's stored as a BIGINT rather than in characters.<br />
<br />
IP to BIGINT:<br />
<span class="default">&lt;?php<br />
&nbsp; $ipArr&nbsp; &nbsp; </span><span class="keyword">= </span><span class="default">explode</span><span class="keyword">(</span><span class="string">'.'</span><span class="keyword">,</span><span class="default">$_SERVER</span><span class="keyword">[</span><span class="string">'REMOTE_ADDR'</span><span class="keyword">]);<br />
&nbsp; </span><span class="default">$ip&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">= </span><span class="default">$ipArr</span><span class="keyword">[</span><span class="default">0</span><span class="keyword">] * </span><span class="default">0x1000000<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">+ </span><span class="default">$ipArr</span><span class="keyword">[</span><span class="default">1</span><span class="keyword">] * </span><span class="default">0x10000<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">+ </span><span class="default">$ipArr</span><span class="keyword">[</span><span class="default">2</span><span class="keyword">] * </span><span class="default">0x100<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">+ </span><span class="default">$ipArr</span><span class="keyword">[</span><span class="default">3</span><span class="keyword">]<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; ;<br />
</span><span class="default">?&gt;<br />
</span><br />
This can be written in a bit more efficient way:<br />
<span class="default">&lt;?php<br />
&nbsp; $ipArr&nbsp; &nbsp; </span><span class="keyword">= </span><span class="default">explode</span><span class="keyword">(</span><span class="string">'.'</span><span class="keyword">,</span><span class="default">$_SERVER</span><span class="keyword">[</span><span class="string">'REMOTE_ADDR'</span><span class="keyword">]);<br />
&nbsp; </span><span class="default">$ip&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">= </span><span class="default">$ipArr</span><span class="keyword">[</span><span class="default">0</span><span class="keyword">]&lt;&lt;</span><span class="default">24<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">+ </span><span class="default">$ipArr</span><span class="keyword">[</span><span class="default">1</span><span class="keyword">]&lt;&lt;</span><span class="default">16<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">+ </span><span class="default">$ipArr</span><span class="keyword">[</span><span class="default">2</span><span class="keyword">] &lt;&lt;</span><span class="default">8<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">+ </span><span class="default">$ipArr</span><span class="keyword">[</span><span class="default">3</span><span class="keyword">]<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; ;<br />
</span><span class="default">?&gt;<br />
</span><br />
shift is more cheaper.</span>
</code></div>
  </div>
 </div>
 <a name="77576"></a>
 <div class="note">
  <strong class='user'>Paul</strong>
  <a href="#77576" class="date">04-Sep-2007 11:46</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
"always round it downwards"<br />
<br />
It seems to truncate, or round toward zero, rather than downward. If the float is negative, it is rounded up.</span>
</code></div>
  </div>
 </div>
 <a name="77056"></a>
 <div class="note">
  <strong class='user'>d_n at NOSPAM dot Loryx dot com</strong>
  <a href="#77056" class="date">13-Aug-2007 05:33</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Here are some tricks to convert from a "dotted" IP address to a LONG int, and backwards. This is very useful because accessing an IP addy in a database table is very much faster if it's stored as a BIGINT rather than in characters.<br />
<br />
IP to BIGINT:<br />
<span class="default">&lt;?php<br />
&nbsp; $ipArr&nbsp; &nbsp; </span><span class="keyword">= </span><span class="default">explode</span><span class="keyword">(</span><span class="string">'.'</span><span class="keyword">,</span><span class="default">$_SERVER</span><span class="keyword">[</span><span class="string">'REMOTE_ADDR'</span><span class="keyword">]);<br />
&nbsp; </span><span class="default">$ip&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">= </span><span class="default">$ipArr</span><span class="keyword">[</span><span class="default">0</span><span class="keyword">] * </span><span class="default">0x1000000<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">+ </span><span class="default">$ipArr</span><span class="keyword">[</span><span class="default">1</span><span class="keyword">] * </span><span class="default">0x10000<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">+ </span><span class="default">$ipArr</span><span class="keyword">[</span><span class="default">2</span><span class="keyword">] * </span><span class="default">0x100<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">+ </span><span class="default">$ipArr</span><span class="keyword">[</span><span class="default">3</span><span class="keyword">]<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; ;<br />
</span><span class="default">?&gt;<br />
</span><br />
IP as BIGINT read from db back to dotted form:<br />
<br />
Keep in mind, PHP integer operators are INTEGER -- not long. Also, since there is no integer divide in PHP, we save a couple of S-L-O-W floor (&lt;division&gt;)'s by doing bitshifts. We must use floor(/) for $ipArr[0] because though $ipVal is stored as a long value, $ipVal &gt;&gt; 24 will operate on a truncated, integer value of $ipVal! $ipVint is, however, a nice integer, so <br />
we can enjoy the bitshifts.<br />
<br />
<span class="default">&lt;?php<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; $ipVal </span><span class="keyword">= </span><span class="default">$row</span><span class="keyword">[</span><span class="string">'client_IP'</span><span class="keyword">];<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$ipArr </span><span class="keyword">= array(</span><span class="default">0 </span><span class="keyword">=&gt;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">floor</span><span class="keyword">(&nbsp; </span><span class="default">$ipVal&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">/ </span><span class="default">0x1000000</span><span class="keyword">) );<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$ipVint&nbsp;&nbsp; </span><span class="keyword">= </span><span class="default">$ipVal</span><span class="keyword">-(</span><span class="default">$ipArr</span><span class="keyword">[</span><span class="default">0</span><span class="keyword">]*</span><span class="default">0x1000000</span><span class="keyword">); </span><span class="comment">// for clarity<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$ipArr</span><span class="keyword">[</span><span class="default">1</span><span class="keyword">] = (</span><span class="default">$ipVint </span><span class="keyword">&amp; </span><span class="default">0xFF0000</span><span class="keyword">)&nbsp; &gt;&gt; </span><span class="default">16</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$ipArr</span><span class="keyword">[</span><span class="default">2</span><span class="keyword">] = (</span><span class="default">$ipVint </span><span class="keyword">&amp; </span><span class="default">0xFF00&nbsp; </span><span class="keyword">)&nbsp; &gt;&gt; </span><span class="default">8</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$ipArr</span><span class="keyword">[</span><span class="default">3</span><span class="keyword">] =&nbsp; </span><span class="default">$ipVint </span><span class="keyword">&amp; </span><span class="default">0xFF</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$ipDotted </span><span class="keyword">= </span><span class="default">implode</span><span class="keyword">(</span><span class="string">'.'</span><span class="keyword">, </span><span class="default">$ipArr</span><span class="keyword">);<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="75695"></a>
 <div class="note">
  <strong class='user'>me at troyswanson dot net</strong>
  <a href="#75695" class="date">12-Jun-2007 03:11</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
This note applies to machines that are using a 32 bit integer size.&nbsp; I imagine the same results occur in 64 bit machines as well (with the number 2^63-1).<br />
<br />
-2147483648 falls into the range of 32 bit signed integers (0b10000000000000000000000000000000), yet php treats it as a float.&nbsp; However, -2147483647-1 is treated as an integer.<br />
<br />
The following code demonstrates:<br />
<span class="default">&lt;?php<br />
&nbsp;&nbsp;&nbsp; var_dump</span><span class="keyword">(-</span><span class="default">2147483648</span><span class="keyword">); </span><span class="comment">//float(-2147483648)<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">var_dump</span><span class="keyword">(-</span><span class="default">2147483647 </span><span class="keyword">- </span><span class="default">1</span><span class="keyword">); </span><span class="comment">//int(-2147483648)<br />
</span><span class="default">?&gt;<br />
</span><br />
Regards</span>
</code></div>
  </div>
 </div>
 <a name="73790"></a>
 <div class="note">
  <strong class='user'>Jacek</strong>
  <a href="#73790" class="date">10-Mar-2007 04:51</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
On 64 bits machines max integer value is 0x7fffffffffffffff (9 223 372 036 854 775 807).</span>
</code></div>
  </div>
 </div>
 <a name="73766"></a>
 <div class="note">
  <a href="#73766" class="date">09-Mar-2007 07:26</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
To force the correct usage of 32-bit unsigned integer in some functions, just add '+0'&nbsp; just before processing them.<br />
<br />
for example <br />
echo(dechex("2724838310"));<br />
will print '7FFFFFFF'<br />
but it should print 'A269BBA6'<br />
<br />
When adding '+0' php will handle the 32bit unsigned integer<br />
correctly<br />
echo(dechex("2724838310"+0));<br />
will print 'A269BBA6'</span>
</code></div>
  </div>
 </div>
 <a name="71899"></a>
 <div class="note">
  <strong class='user'>popefelix at gmail dot com</strong>
  <a href="#71899" class="date">21-Dec-2006 06:50</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Be careful when using integer conversion to test something to see if it evaluates to a positive integer or not.&nbsp; You might get unexpected behaviour.<br />
<br />
To wit:<br />
<span class="default">&lt;?php<br />
error_reporting</span><span class="keyword">(</span><span class="default">E_ALL</span><span class="keyword">);<br />
require_once </span><span class="string">'Date.php'</span><span class="keyword">;<br />
<br />
</span><span class="default">$date </span><span class="keyword">= new </span><span class="default">Date</span><span class="keyword">();<br />
print </span><span class="string">"\$date is an instance of " </span><span class="keyword">. </span><span class="default">get_class</span><span class="keyword">(</span><span class="default">$date</span><span class="keyword">) . </span><span class="string">"\n"</span><span class="keyword">;<br />
</span><span class="default">$date </span><span class="keyword">+= </span><span class="default">0</span><span class="keyword">;<br />
print </span><span class="string">"\$date is now $date\n"</span><span class="keyword">;<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$date</span><span class="keyword">);<br />
<br />
</span><span class="default">$foo </span><span class="keyword">= new </span><span class="default">foo</span><span class="keyword">();<br />
print </span><span class="string">"\$foo is an instance of " </span><span class="keyword">. </span><span class="default">get_class</span><span class="keyword">(</span><span class="default">$foo</span><span class="keyword">) . </span><span class="string">"\n"</span><span class="keyword">;<br />
</span><span class="default">$foo </span><span class="keyword">+= </span><span class="default">0</span><span class="keyword">;<br />
print </span><span class="string">"\$foo is now $foo\n"</span><span class="keyword">;<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$foo</span><span class="keyword">);<br />
<br />
class </span><span class="default">foo </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; var </span><span class="default">$bar </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; var </span><span class="default">$baz </span><span class="keyword">= </span><span class="string">"la lal la"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; var </span><span class="default">$bak</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">foo</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$bak </span><span class="keyword">= </span><span class="default">3.14159</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
After the integer conversion, you might expect both $foo and $date to evaluate to 0.&nbsp; However, this is not the case:<br />
<br />
$date is an instance of Date<br />
<br />
Notice: Object of class Date could not be converted to int in /home/kpeters/work/sketches/ObjectSketch.php on line 7<br />
$date is now 1<br />
int(1)<br />
$foo is an instance of foo<br />
<br />
Notice: Object of class foo could not be converted to int in /home/kpeters/work/sketches/ObjectSketch.php on line 13<br />
$foo is now 1<br />
int(1)<br />
<br />
This is because the objects are first converted to boolean before being converted to int.</span>
</code></div>
  </div>
 </div>
 <a name="71709"></a>
 <div class="note">
  <strong class='user'>rustamabd@gmail-you-know-what</strong>
  <a href="#71709" class="date">12-Dec-2006 01:42</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Be careful with using the modulo operation on big numbers, it will cast a float argument to an int and may return wrong results. For example:<br />
<span class="default">&lt;?php<br />
&nbsp;&nbsp;&nbsp; $i </span><span class="keyword">= </span><span class="default">6887129852</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="string">"i=$i\n"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="string">"i%36="</span><span class="keyword">.(</span><span class="default">$i</span><span class="keyword">%</span><span class="default">36</span><span class="keyword">).</span><span class="string">"\n"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="string">"alternative i%36="</span><span class="keyword">.(</span><span class="default">$i</span><span class="keyword">-</span><span class="default">floor</span><span class="keyword">(</span><span class="default">$i</span><span class="keyword">/</span><span class="default">36</span><span class="keyword">)*</span><span class="default">36</span><span class="keyword">).</span><span class="string">"\n"</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span>Will output:<br />
i=6.88713E+009<br />
i%36=-24<br />
alternative i%36=20</span>
</code></div>
  </div>
 </div>
 <a name="69181"></a>
 <div class="note">
  <strong class='user'>jmw254 at cornell dot edu</strong>
  <a href="#69181" class="date">25-Aug-2006 10:14</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Try this one instead: <br />
<br />
function iplongtostring($ip)<br />
{<br />
&nbsp;&nbsp;&nbsp; $ip=floatval($ip); // otherwise it is capped at 127.255.255.255<br />
<br />
&nbsp;&nbsp;&nbsp; $a=($ip&gt;&gt;24)&amp;255;<br />
&nbsp;&nbsp;&nbsp; $b=($ip&gt;&gt;16)&amp;255;<br />
&nbsp;&nbsp;&nbsp; $c=($ip&gt;&gt;8)&amp;255;<br />
&nbsp;&nbsp;&nbsp; $d=$ip&amp;255;<br />
<br />
&nbsp;&nbsp;&nbsp; return "$a.$b.$c.$d";<br />
}</span>
</code></div>
  </div>
 </div>
 <a name="49417"></a>
 <div class="note">
  <strong class='user'>rickard_cedergren at yahoo dot com</strong>
  <a href="#49417" class="date">27-Jan-2005 01:15</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
When doing large subtractions on 32 bit unsigned integers the result sometimes end up negative. My example script converts a IPv4 address represented as a 32 bit unsigned integer to a dotted quad (similar to ip2long()), and adds a "fix" to the operation. <br />
<br />
&nbsp;&nbsp; /**************************<br />
&nbsp;&nbsp;&nbsp; * int_oct($ip) <br />
&nbsp;&nbsp;&nbsp; * Convert INTeger rep of IP to octal (dotted quad)<br />
&nbsp;&nbsp;&nbsp; */<br />
&nbsp;&nbsp; function int_oct($ip) {<br />
<br />
&nbsp;&nbsp; &nbsp;&nbsp; /* Set variable to float */<br />
&nbsp;&nbsp; &nbsp;&nbsp; settype($ip, float);<br />
<br />
&nbsp;&nbsp; &nbsp;&nbsp; /* FIX for silly PHP integer syndrome */<br />
&nbsp;&nbsp; &nbsp;&nbsp; $fix = 0;<br />
&nbsp;&nbsp; &nbsp;&nbsp; if($ip &gt; 2147483647) $fix = 16777216;<br />
<br />
&nbsp;&nbsp; &nbsp;&nbsp; if(is_numeric($ip)) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; return(sprintf("%u.%u.%u.%u",<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; $ip / 16777216,<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; (($ip % 16777216) + $fix) / 65536,<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; (($ip % 65536) + $fix / 256) / 256,<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; ($ip % 256) + $fix / 256 / 256<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; )<br />
&nbsp;&nbsp; &nbsp; );<br />
&nbsp;&nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp;&nbsp; else {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; return('');<br />
&nbsp;&nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; }</span>
</code></div>
  </div>
 </div>
 <a name="38478"></a>
 <div class="note">
  <a href="#38478" class="date">23-Dec-2003 10:18</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Sometimes you need to parse an unsigned<br />
32 bit integer. Here's a function I 've used:<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; function parse_unsigned_int($string) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; $x = (float)$string;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if ($x &gt; (float)2147483647)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; $x -= (float)"4294967296";<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return (int)$x;<br />
&nbsp;&nbsp;&nbsp; }</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=language.types.integer&amp;redirect=http://www.php.net/manual/en/language.types.integer.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.types.integer&amp;redirect=http://www.php.net/manual/en/language.types.integer.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/language.types.integer.php">show source</a> |
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