<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Operator Precedence - Manual</title>
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
 <link rel="index" href="language.operators.php" />
 <link rel="prev" href="language.operators.php" />
 <link rel="next" href="language.operators.arithmetic.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/operators.precedence" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/language.operators.precedence.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="@w{8VGQ7ZJQ}" />
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
 <li class="header up"><a href="language.operators.php">Operators</a></li>
 <li class="active"><a href="language.operators.precedence.php">Operator Precedence</a></li>
 <li><a href="language.operators.arithmetic.php">Arithmetic Operators</a></li>
 <li><a href="language.operators.assignment.php">Assignment Operators</a></li>
 <li><a href="language.operators.bitwise.php">Bitwise Operators</a></li>
 <li><a href="language.operators.comparison.php">Comparison Operators</a></li>
 <li><a href="language.operators.errorcontrol.php">Error Control Operators</a></li>
 <li><a href="language.operators.execution.php">Execution Operators</a></li>
 <li><a href="language.operators.increment.php">Incrementing/Decrementing Operators</a></li>
 <li><a href="language.operators.logical.php">Logical Operators</a></li>
 <li><a href="language.operators.string.php">String Operators</a></li>
 <li><a href="language.operators.array.php">Array Operators</a></li>
 <li><a href="language.operators.type.php">Type Operators</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="language.operators.arithmetic.php">Arithmetic Operators<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.operators.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Operators</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.operators.precedence.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/language.operators.precedence.php">Brazilian Portuguese</option>
    <option value="zh/language.operators.precedence.php">Chinese (Simplified)</option>
    <option value="fr/language.operators.precedence.php">French</option>
    <option value="de/language.operators.precedence.php">German</option>
    <option value="ja/language.operators.precedence.php">Japanese</option>
    <option value="pl/language.operators.precedence.php">Polish</option>
    <option value="ro/language.operators.precedence.php">Romanian</option>
    <option value="ru/language.operators.precedence.php">Russian</option>
    <option value="fa/language.operators.precedence.php">Persian</option>
    <option value="es/language.operators.precedence.php">Spanish</option>
    <option value="tr/language.operators.precedence.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="language.operators.precedence" class="sect1">
   <h2 class="title">Operator Precedence</h2>
   <p class="para">
    The precedence of an operator specifies how &quot;tightly&quot; it binds two
    expressions together. For example, in the expression <em>1 +
    5 * 3</em>, the answer is <em>16</em> and not
    <em>18</em> because the multiplication (&quot;*&quot;) operator
    has a higher precedence than the addition (&quot;+&quot;) operator.
    Parentheses may be used to force precedence, if necessary. For
    instance: <em>(1 + 5) * 3</em> evaluates to
    <em>18</em>.
   </p>
   <p class="para">
    When operators have equal precedence, their associativity decides
    whether they are evaluated starting from the right, or starting from
    the left - see the examples below.
   </p>
   <p class="para">
    The following table lists the operators in order of precedence, with
    the highest-precedence ones at the top. Operators on the same line
    have equal precedence, in which case associativity decides the order
    of evaluation.
    <table class="doctable table">
     <caption><strong>Operator Precedence</strong></caption>
     
      <thead>
       <tr>
        <th>Associativity</th>
        <th>Operators</th>
        <th>Additional Information</th>
       </tr>

      </thead>

      <tbody class="tbody">
       <tr>
        <td>non-associative</td>
        <td>clone new</td>
        <td><a href="language.oop5.cloning.php" class="link">clone</a> and <a href="language.oop5.basic.php#language.oop5.basic.new" class="link">new</a></td>
       </tr>

       <tr>
        <td>left</td>
        <td>[</td>
        <td> <span class="function"><a href="function.array.php" class="function">array()</a></span></td>
       </tr>

       <tr>
        <td>right</td>
        <td>++ -- ~ (int) (float) (string) (array) (object) (bool) @</td>
        <td>
         <a href="language.types.php" class="link">types</a> and <a href="language.operators.increment.php" class="link">increment/decrement</a>
        </td>
       </tr>

       <tr>
        <td>non-associative</td>
        <td>instanceof</td>
        <td>
         <a href="language.types.php" class="link">types</a>
        </td>
       </tr>

       <tr>
        <td>right</td>
        <td>!</td>
        <td>
         <a href="language.operators.logical.php" class="link">logical</a>
        </td>
       </tr>

       <tr>
        <td>left</td>
        <td>* / %</td>
        <td>
         <a href="language.operators.arithmetic.php" class="link">arithmetic</a>
        </td>
       </tr>

       <tr>
        <td>left</td>
        <td>+ - .</td>
        <td>
         <a href="language.operators.arithmetic.php" class="link">arithmetic</a> and
         <a href="language.operators.string.php" class="link">string</a></td>
       </tr>

       <tr>
        <td>left</td>
        <td>&lt;&lt; &gt;&gt;</td>
        <td>
         <a href="language.operators.bitwise.php" class="link">bitwise</a>
        </td>
       </tr>

       <tr>
        <td>non-associative</td>
        <td>&lt; &lt;= &gt; &gt;=</td>
        <td>
         <a href="language.operators.comparison.php" class="link">comparison</a>
        </td>
       </tr>

       <tr>
        <td>non-associative</td>
        <td>== != === !== &lt;&gt;</td>
        <td>
         <a href="language.operators.comparison.php" class="link">comparison</a>
        </td>
       </tr>

       <tr>
        <td>left</td>
        <td>&amp;</td>
        <td>
         <a href="language.operators.bitwise.php" class="link">bitwise</a> and
         <a href="language.references.php" class="link">references</a></td>
       </tr>

       <tr>
        <td>left</td>
        <td>^</td>
        <td>
         <a href="language.operators.bitwise.php" class="link">bitwise</a>
        </td>
       </tr>

       <tr>
        <td>left</td>
        <td>|</td>
        <td>
         <a href="language.operators.bitwise.php" class="link">bitwise</a>
        </td>
       </tr>

       <tr>
        <td>left</td>
        <td>&amp;&amp;</td>
        <td>
         <a href="language.operators.logical.php" class="link">logical</a>
        </td>
       </tr>

       <tr>
        <td>left</td>
        <td>||</td>
        <td>
         <a href="language.operators.logical.php" class="link">logical</a>
        </td>
       </tr>

       <tr>
        <td>left</td>
        <td>? :</td>
        <td>
         <a href="language.operators.comparison.php#language.operators.comparison.ternary" class="link">ternary</a>
        </td>
       </tr>

       <tr>
        <td>right</td>
        <td>
         = += -= *= /= .= %= &amp;= |= ^= &lt;&lt;= &gt;&gt;= =&gt;
        </td>
        <td>
         <a href="language.operators.assignment.php" class="link">assignment</a>
        </td>
       </tr>

       <tr>
        <td>left</td>
        <td>and</td>
        <td>
         <a href="language.operators.logical.php" class="link">logical</a>
        </td>
       </tr>

       <tr>
        <td>left</td>
        <td>xor</td>
        <td>
         <a href="language.operators.logical.php" class="link">logical</a>
        </td>
       </tr>

       <tr>
        <td>left</td>
        <td>or</td>
        <td>
         <a href="language.operators.logical.php" class="link">logical</a>
        </td>
       </tr>

       <tr>
        <td>left</td>
        <td>,</td>
        <td>many uses</td>
       </tr>

      </tbody>
     
    </table>

   </p>
   <p class="para">
    For operators of equal precedence, left associativity means that
    evaluation proceeds from left to right, and right associativity means
    the opposite.
    <div class="example" id="example-113">
     <p><strong>Example #1 Associativity</strong></p>
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br />$a&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">3&nbsp;</span><span style="color: #007700">*&nbsp;</span><span style="color: #0000BB">3&nbsp;</span><span style="color: #007700">%&nbsp;</span><span style="color: #0000BB">5</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;(3&nbsp;*&nbsp;3)&nbsp;%&nbsp;5&nbsp;=&nbsp;4<br /></span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">true&nbsp;</span><span style="color: #007700">?&nbsp;</span><span style="color: #0000BB">0&nbsp;</span><span style="color: #007700">:&nbsp;</span><span style="color: #0000BB">true&nbsp;</span><span style="color: #007700">?&nbsp;</span><span style="color: #0000BB">1&nbsp;</span><span style="color: #007700">:&nbsp;</span><span style="color: #0000BB">2</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;(true&nbsp;?&nbsp;0&nbsp;:&nbsp;true)&nbsp;?&nbsp;1&nbsp;:&nbsp;2&nbsp;=&nbsp;2<br /><br /></span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">1</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">$b&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">2</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">$b&nbsp;</span><span style="color: #007700">+=&nbsp;</span><span style="color: #0000BB">3</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;$a&nbsp;=&nbsp;($b&nbsp;+=&nbsp;3)&nbsp;-&gt;&nbsp;$a&nbsp;=&nbsp;5,&nbsp;$b&nbsp;=&nbsp;5<br /><br />//&nbsp;mixing&nbsp;++&nbsp;and&nbsp;+&nbsp;produces&nbsp;undefined&nbsp;behavior<br /></span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">1</span><span style="color: #007700">;<br />echo&nbsp;++</span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">+&nbsp;</span><span style="color: #0000BB">$a</span><span style="color: #007700">++;&nbsp;</span><span style="color: #FF8000">//&nbsp;may&nbsp;print&nbsp;4&nbsp;or&nbsp;5<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

    </div>
    Use of parentheses, even when not strictly necessary, can often increase
    readability of the code.
   </p>
   <blockquote class="note"><p><strong class="note">Note</strong>: 
    <p class="para">
     Although <em>=</em> has a lower precedence than
     most other operators, PHP will still allow expressions
     similar to the following: <em>if (!$a = foo())</em>,
     in which case the return value of <em>foo()</em> is
     put into <var class="varname"><var class="varname">$a</var></var>.
    </p>
   </p></blockquote>
  </div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="language.operators.arithmetic.php">Arithmetic Operators<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.operators.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Operators</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.operators.precedence.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=language.operators.precedence&amp;redirect=@w{8VGQ7ZJQ}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.operators.precedence&amp;redirect=@w{8VGQ7ZJQ}">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Operator Precedence</strong>
 </div><div id="allnotes">
 <a name="109325"></a>
 <div class="note">
  <strong class='user'>Carsten Milkau</strong>
  <a href="#109325" class="date">06-Jul-2012 12:06</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Beware the unusual order of bit-wise operators and comparison operators, this has often lead to bugs in my experience. For instance:<br />
<br />
<span class="default">&lt;?php </span><span class="keyword">if ( </span><span class="default">$flags </span><span class="keyword">&amp; </span><span class="default">MASK&nbsp; </span><span class="keyword">== </span><span class="default">1</span><span class="keyword">) </span><span class="default">do_something</span><span class="keyword">(); </span><span class="default">?&gt;<br />
</span><br />
will not do what you might expect from other languages. Use<br />
<br />
<span class="default">&lt;?php </span><span class="keyword">if ((</span><span class="default">$flags </span><span class="keyword">&amp; </span><span class="default">MASK</span><span class="keyword">) == </span><span class="default">1</span><span class="keyword">) </span><span class="default">do_something</span><span class="keyword">(); </span><span class="default">?&gt;<br />
</span><br />
in PHP instead.</span>
</code></div>
  </div>
 </div>
 <a name="108854"></a>
 <div class="note">
  <strong class='user'>nahidacm at gmail dot com</strong>
  <a href="#108854" class="date">30-May-2012 03:40</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
From PHP 5.4.0 Array Dereferencing is Supported<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">func</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; return array(</span><span class="string">'string'</span><span class="keyword">);<br />
}<br />
</span><span class="default">func</span><span class="keyword">()[</span><span class="default">0</span><span class="keyword">];&nbsp; </span><span class="comment">// Allowed from PHP 5.4, early will generate syntax error<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="104716"></a>
 <div class="note">
  <strong class='user'>Christopher Schramm</strong>
  <a href="#104716" class="date">02-Jul-2011 09:29</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
( - the function call operator - has higher precedence than ++ and --, but lower precedence than [.<br />
<br />
Therefore you can do the following:<br />
<br />
<span class="default">&lt;?php<br />
$func</span><span class="keyword">[</span><span class="default">0</span><span class="keyword">] = </span><span class="string">'exit'</span><span class="keyword">;<br />
</span><span class="default">$func</span><span class="keyword">[</span><span class="default">0</span><span class="keyword">]();<br />
</span><span class="default">?&gt;<br />
</span><br />
But the following will cause a syntax error:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">func</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; return array(</span><span class="string">'string'</span><span class="keyword">);<br />
}<br />
</span><span class="default">func</span><span class="keyword">()[</span><span class="default">0</span><span class="keyword">];<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="102341"></a>
 <div class="note">
  <strong class='user'>charles at pilger dot com dot br</strong>
  <a href="#102341" class="date">09-Feb-2011 12:00</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Be very careful with the precedence. See this code:<br />
<br />
<span class="default">&lt;?php<br />
$a </span><span class="keyword">= </span><span class="default">1</span><span class="keyword">;<br />
</span><span class="default">$b </span><span class="keyword">= </span><span class="default">null</span><span class="keyword">;<br />
</span><span class="default">$c </span><span class="keyword">= isset(</span><span class="default">$a</span><span class="keyword">) &amp;&amp; isset(</span><span class="default">$b</span><span class="keyword">);<br />
</span><span class="default">$d </span><span class="keyword">= ( isset(</span><span class="default">$a</span><span class="keyword">) and isset(</span><span class="default">$b</span><span class="keyword">) );<br />
</span><span class="default">$e </span><span class="keyword">= isset(</span><span class="default">$a</span><span class="keyword">) and isset(</span><span class="default">$b</span><span class="keyword">);<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$a</span><span class="keyword">, </span><span class="default">$b</span><span class="keyword">, </span><span class="default">$c</span><span class="keyword">, </span><span class="default">$d</span><span class="keyword">, </span><span class="default">$e</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span><br />
Result:<br />
<br />
int(1)<br />
NULL<br />
bool(false)<br />
bool(false)<br />
bool(true) &lt;==</span>
</code></div>
  </div>
 </div>
 <a name="98871"></a>
 <div class="note">
  <strong class='user'>kiamlaluno at avpnet dot org</strong>
  <a href="#98871" class="date">12-Jul-2010 04:41</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Be careful of the difference between<br />
<br />
<span class="default">&lt;?php<br />
$obj </span><span class="keyword">= new class::</span><span class="default">$staticVariable</span><span class="keyword">();<br />
</span><span class="default">?&gt;<br />
</span><br />
<span class="default">&lt;?php<br />
$value </span><span class="keyword">= class::</span><span class="default">$staticVariable</span><span class="keyword">();<br />
</span><span class="default">?&gt;<br />
</span><br />
In the first case, the object class will depend on the static variable class::$staticVariable, while in the second case it will be invoked the method whose name is contained in the variable $staticVariable.</span>
</code></div>
  </div>
 </div>
 <a name="91377"></a>
 <div class="note">
  <strong class='user'>headden at karelia dot ru</strong>
  <a href="#91377" class="date">09-Jun-2009 04:02</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Although example above already shows it, I'd like to explicitly state that ?: associativity DIFFERS from that of C++. I.e. convenient switch/case-like expressions of the form<br />
<br />
$i==1 ? "one" :<br />
$i==2 ? "two" :<br />
$i==3 ? "three" :<br />
"error";<br />
<br />
will not work in PHP as expected</span>
</code></div>
  </div>
 </div>
 <a name="88799"></a>
 <div class="note">
  <strong class='user'>Pies</strong>
  <a href="#88799" class="date">08-Feb-2009 11:22</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
You can use the "or" and "and" keywords' lower precedence for a bit of syntax candy:<br />
<br />
<span class="default">&lt;?php<br />
<br />
$page </span><span class="keyword">= (int) @</span><span class="default">$_GET</span><span class="keyword">[</span><span class="string">'page'</span><span class="keyword">] or </span><span class="default">$page </span><span class="keyword">= </span><span class="default">1</span><span class="keyword">;<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=language.operators.precedence&amp;redirect=@w{8VGQ7ZJQ}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.operators.precedence&amp;redirect=@w{8VGQ7ZJQ}">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/language.operators.precedence.php">show source</a> |
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