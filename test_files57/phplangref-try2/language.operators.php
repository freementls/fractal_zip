<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Operators - Manual</title>
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
 <link rel="prev" href="language.expressions.php" />
 <link rel="next" href="language.operators.precedence.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/operators" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="alternate" href="/manual/en/feeds/language.operators.atom" type="application/atom+xml" />
 <link rel="canonical" href="http://php.net/manual/en/language.operators.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="@w{AGGWDXF6}" />
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
 <li><a href="language.constants.php">Constants</a></li>
 <li><a href="language.expressions.php">Expressions</a></li>
 <li class="active"><a href="language.operators.php">Operators</a></li>
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
  <a href="language.operators.precedence.php">Operator Precedence<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.expressions.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Expressions</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.operators.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/language.operators.php">Brazilian Portuguese</option>
    <option value="zh/language.operators.php">Chinese (Simplified)</option>
    <option value="fr/language.operators.php">French</option>
    <option value="de/language.operators.php">German</option>
    <option value="ja/language.operators.php">Japanese</option>
    <option value="pl/language.operators.php">Polish</option>
    <option value="ro/language.operators.php">Romanian</option>
    <option value="ru/language.operators.php">Russian</option>
    <option value="fa/language.operators.php">Persian</option>
    <option value="es/language.operators.php">Spanish</option>
    <option value="tr/language.operators.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="language.operators" class="chapter">
  <h1>Operators</h1>
<h2>Table of Contents</h2><ul class="chunklist chunklist_chapter"><li><a href="language.operators.precedence.php">Operator Precedence</a></li><li><a href="language.operators.arithmetic.php">Arithmetic Operators</a></li><li><a href="language.operators.assignment.php">Assignment Operators</a></li><li><a href="language.operators.bitwise.php">Bitwise Operators</a></li><li><a href="language.operators.comparison.php">Comparison Operators</a></li><li><a href="language.operators.errorcontrol.php">Error Control Operators</a></li><li><a href="language.operators.execution.php">Execution Operators</a></li><li><a href="language.operators.increment.php">Incrementing/Decrementing Operators</a></li><li><a href="language.operators.logical.php">Logical Operators</a></li><li><a href="language.operators.string.php">String Operators</a></li><li><a href="language.operators.array.php">Array Operators</a></li><li><a href="language.operators.type.php">Type Operators</a></li></ul>

  <p class="simpara">
   An operator is something that takes one or more values (or
   expressions, in programming jargon) and yields another value (so that the
   construction itself becomes an expression).
  </p>
  <p class="para">
   Operators can be grouped according to the number of values they take. Unary
   operators take only one value, for example <em>!</em> (the
   <a href="language.operators.logical.php" class="link">logical not operator</a>) or
   <em>++</em> (the
   <a href="language.operators.increment.php" class="link">increment operator</a>).
   Binary operators take two values, such as the familiar
   <a href="language.operators.arithmetic.php" class="link">arithmetical operators</a>
   <em>+</em> (plus) and <em>-</em> (minus), and the
   majority of PHP operators fall into this category. Finally, there is a
   single <a href="language.operators.comparison.php#language.operators.comparison.ternary" class="link">ternary
   operator</a>, <em>? :</em>, which takes three values; this is
   usually referred to simply as &quot;the ternary operator&quot; (although it could
   perhaps more properly be called the conditional operator).
  </p>
  <p class="para">
   A full list of PHP operators follows in the section
   <a href="language.operators.precedence.php" class="link">Operator Precedence</a>.
   The section also explains operator precedence and associativity, which govern
   exactly how expressions containing several different operators are
   evaluated.
  </p>

  

  

  

  

  

  

  

  

  

  

  
  
 </div>
<br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="language.operators.precedence.php">Operator Precedence<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.expressions.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Expressions</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.operators.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=language.operators&amp;redirect=@w{AGGWDXF6}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.operators&amp;redirect=@w{AGGWDXF6}">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Operators</strong>
 </div><div id="allnotes">
 <a name="87904"></a>
 <div class="note">
  <strong class='user'>pgarvin76+php dot net at NOSPAMgmail dot com</strong>
  <a href="#87904" class="date">29-Dec-2008 04:43</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Method chaining is read left to right (left associative):<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">class </span><span class="default">Test_Method_Chain<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">One</span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">"One" </span><span class="keyword">. </span><span class="default">PHP_EOL</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$this</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">Two</span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">"Two" </span><span class="keyword">. </span><span class="default">PHP_EOL</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$this</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">Three</span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">"Three" </span><span class="keyword">. </span><span class="default">PHP_EOL</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$this</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="default">$test </span><span class="keyword">= new </span><span class="default">Test_Method_Chain</span><span class="keyword">();<br />
<br />
</span><span class="default">$test</span><span class="keyword">-&gt;</span><span class="default">One</span><span class="keyword">()-&gt;</span><span class="default">Two</span><span class="keyword">()-&gt;</span><span class="default">Three</span><span class="keyword">();<br />
<br />
</span><span class="comment">/* Ouputs:<br />
One<br />
Two<br />
Three<br />
*/<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="86566"></a>
 <div class="note">
  <strong class='user'>ddascalescu at gmail dot com</strong>
  <a href="#86566" class="date">23-Oct-2008 06:53</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The -&gt; operator, not listed above, is called "object operator" (T_OBJECT_OPERATOR).</span>
</code></div>
  </div>
 </div>
 <a name="84862"></a>
 <div class="note">
  <strong class='user'>figroc at gmail dot com</strong>
  <a href="#84862" class="date">02-Aug-2008 03:30</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The variable symbol '$' should be considered as the highest-precedence operator, so that the variable variables such as $$a[0] won't confuse the parser.&nbsp; [<a href="@w{QGD8CT77}]" rel="nofollow" target="_blank">@w{QGD8CT77}]</a></span>
</code></div>
  </div>
 </div>
 <a name="78911"></a>
 <div class="note">
  <strong class='user'>phpnet dot 20 dot dpnsubs at xoxy dot net</strong>
  <a href="#78911" class="date">01-Nov-2007 02:13</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Note that in php the ternary operator ?: has a left associativity unlike in C and C++ where it has right associativity.<br />
<br />
You cannot write code like this (as you may have accustomed to in C/C++):<br />
<span class="default">&lt;?php<br />
$a </span><span class="keyword">= </span><span class="default">2</span><span class="keyword">;<br />
echo (<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$a </span><span class="keyword">== </span><span class="default">1 </span><span class="keyword">? </span><span class="string">'one' </span><span class="keyword">: <br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$a </span><span class="keyword">== </span><span class="default">2 </span><span class="keyword">? </span><span class="string">'two' </span><span class="keyword">: <br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$a </span><span class="keyword">== </span><span class="default">3 </span><span class="keyword">? </span><span class="string">'three' </span><span class="keyword">: <br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$a </span><span class="keyword">== </span><span class="default">4 </span><span class="keyword">? </span><span class="string">'four' </span><span class="keyword">: </span><span class="string">'other'</span><span class="keyword">);<br />
echo </span><span class="string">"\n"</span><span class="keyword">;<br />
</span><span class="comment">// prints 'four'<br />
</span><span class="default">?&gt;<br />
</span><br />
You need to add brackets to get the results you want:<br />
&lt;?<br />
$a = 2;<br />
<br />
echo ($a == 1 ? 'one' : <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; ($a == 2 ? 'two' : <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; ($a == 3 ? 'three' : <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; ($a == 4 ? 'four' : 'other') ) ) );<br />
echo "\n";<br />
//prints 'two'<br />
<br />
?&gt;</span>
</code></div>
  </div>
 </div>
 <a name="78397"></a>
 <div class="note">
  <strong class='user'>Gautam</strong>
  <a href="#78397" class="date">10-Oct-2007 03:22</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
<span class="default">&lt;?php<br />
<br />
$result1 </span><span class="keyword">= </span><span class="default">7 </span><span class="keyword">+ </span><span class="default">8 </span><span class="keyword">* </span><span class="default">9</span><span class="keyword">/</span><span class="default">3 </span><span class="keyword">-</span><span class="default">4</span><span class="keyword">;<br />
</span><span class="default">$result2 </span><span class="keyword">= </span><span class="default">7 </span><span class="keyword">+ </span><span class="default">8 </span><span class="keyword">* (</span><span class="default">9</span><span class="keyword">/</span><span class="default">3 </span><span class="keyword">-</span><span class="default">4</span><span class="keyword">);<br />
</span><span class="default">$result3 </span><span class="keyword">=(</span><span class="default">7 </span><span class="keyword">+ </span><span class="default">8</span><span class="keyword">)* </span><span class="default">9</span><span class="keyword">/</span><span class="default">3 </span><span class="keyword">-</span><span class="default">4</span><span class="keyword">;<br />
<br />
echo </span><span class="string">"Result1 for 7 + 8 * 9/3 -4 = $result1&nbsp; Result2 for 7 + 8 * (9/3 -4) = $result2 and Result3 (7 + 8)* 9/3 -4 = $result3 "<br />
</span><span class="comment">/*<br />
&nbsp;which gives results as under<br />
&nbsp;Result1 for 7 + 8 * 9/3 -4 = 27 Result2 for 7 + 8 * (9/3 -4) = -1 and Result3 (7 + 8)* 9/3 -4 = 41<br />
&nbsp;Execution Order is 1) expression in brackets 2) division 3) multiplication 4) addition and 5) subtraction <br />
*/<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="78358"></a>
 <div class="note">
  <strong class='user'>janturon at email dot cz</strong>
  <a href="#78358" class="date">08-Oct-2007 06:42</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
This is very common problem: set one variable to another, if it is not empty. If it is, set it to something else.<br />
For example: set $bar to $foo, if $foo is empty, set $bar to "undefined";<br />
<br />
if(!empty($foo)) $bar= $foo; else $bar= "undefined";<br />
<br />
OR operator can shorten it:<br />
<br />
$bar= @$foo or $bar= "undefined";</span>
</code></div>
  </div>
 </div>
 <a name="76385"></a>
 <div class="note">
  <strong class='user'>me at robrosenbaum dot com</strong>
  <a href="#76385" class="date">12-Jul-2007 12:16</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The scope resolution operator ::, which is missing from the list above, has higher precedence than [], and lower precedence than 'new'. This means that self::$array[$var] works as expected.</span>
</code></div>
  </div>
 </div>
 <a name="75641"></a>
 <div class="note">
  <strong class='user'>madcoder at gmail dot com</strong>
  <a href="#75641" class="date">09-Jun-2007 03:17</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
In response to mathiasrav at gmail dot com:<br />
<br />
The reason for that behavior is the parentheses.&nbsp; From the description:<br />
<br />
"Parentheses may be used to force precedence, if necessary. For instance: (1 + 5) * 3 evaluates to 18."<br />
<br />
So the order of operations says that even though the equality operator has higher precedence, the parentheses in your statement force the assignment operator to a higher precedence than the equality operator.<br />
<br />
That said, it still doesn't work the way you expect it to.&nbsp; Neither way works, for these reasons:<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">if ( </span><span class="default">$a </span><span class="keyword">!= (</span><span class="default">$a </span><span class="keyword">= </span><span class="default">$b</span><span class="keyword">) )<br />
</span><span class="default">?&gt;<br />
</span><br />
Order of operations says to do the parentheses first.&nbsp; So you end up with:<br />
<span class="default">&lt;?php<br />
$a </span><span class="keyword">= </span><span class="default">$b</span><span class="keyword">;<br />
if ( </span><span class="default">$a </span><span class="keyword">!= </span><span class="default">$a </span><span class="keyword">)<br />
</span><span class="default">?&gt;<br />
</span><br />
Which is obviously going to be false.&nbsp; Without the parentheses:<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">if ( </span><span class="default">$a </span><span class="keyword">!= </span><span class="default">$a </span><span class="keyword">= </span><span class="default">$b </span><span class="keyword">)<br />
</span><span class="default">?&gt;<br />
</span><br />
Order of operations says to do the inequality first, then the assignment, so you have:<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">if ( </span><span class="default">$a </span><span class="keyword">!= </span><span class="default">$a </span><span class="keyword">);<br />
</span><span class="default">$a </span><span class="keyword">= </span><span class="default">$b</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
Which again is not what you expected, and again will always be false.&nbsp; But because you are only working with values of 0 and 1, you can make use of the XOR operator:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">if ( </span><span class="default">$a </span><span class="keyword">^= </span><span class="default">$b </span><span class="keyword">)<br />
</span><span class="default">?&gt;<br />
</span><br />
This will only be true if 1) $a is 0 and $b is 1, or 2) $a is 1 and $b is 0.&nbsp; That is precisely what you wanted, and it even does the assignment the way you expected it to.<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">foreach (</span><span class="default">$ourstring </span><span class="keyword">as </span><span class="default">$c</span><span class="keyword">) {<br />
&nbsp; if (</span><span class="default">$bold </span><span class="keyword">^= </span><span class="default">$c</span><span class="keyword">[</span><span class="string">'bold'</span><span class="keyword">]) </span><span class="default">$resstring </span><span class="keyword">.= </span><span class="default">bold</span><span class="keyword">;<br />
&nbsp; if (</span><span class="default">$underline </span><span class="keyword">^= </span><span class="default">$c</span><span class="keyword">[</span><span class="string">'underline'</span><span class="keyword">]) </span><span class="default">$resstring </span><span class="keyword">.= </span><span class="default">underline</span><span class="keyword">;<br />
&nbsp; </span><span class="default">$resstring </span><span class="keyword">.= </span><span class="default">$c</span><span class="keyword">[</span><span class="default">0</span><span class="keyword">];<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
That code now works and produces the output you expected.</span>
</code></div>
  </div>
 </div>
 <a name="67997"></a>
 <div class="note">
  <strong class='user'>golotyuk at gmail dot com</strong>
  <a href="#67997" class="date">09-Jul-2006 09:51</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Simple POST and PRE incremnt sample:<br />
<br />
<span class="default">&lt;?php<br />
<br />
$b </span><span class="keyword">= </span><span class="default">5</span><span class="keyword">;<br />
</span><span class="default">$a </span><span class="keyword">= ( ( ++</span><span class="default">$b </span><span class="keyword">) &gt; </span><span class="default">5 </span><span class="keyword">); </span><span class="comment">// Pre-increment test<br />
</span><span class="keyword">echo (int)</span><span class="default">$a</span><span class="keyword">;<br />
<br />
</span><span class="default">$b </span><span class="keyword">= </span><span class="default">5</span><span class="keyword">;<br />
</span><span class="default">$a </span><span class="keyword">= ( ( </span><span class="default">$b</span><span class="keyword">++ ) &gt; </span><span class="default">5 </span><span class="keyword">); </span><span class="comment">// Post-increment test<br />
</span><span class="keyword">echo (int)</span><span class="default">$a</span><span class="keyword">;<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
This will output 10, because of the difference in post- and pre-increment operations</span>
</code></div>
  </div>
 </div>
 <a name="56433"></a>
 <div class="note">
  <strong class='user'>rick at nomorespam dot fourfront dot ltd dot uk</strong>
  <a href="#56433" class="date">02-Sep-2005 03:51</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
A quick note to any C developers out there, assignment expressions are not interpreted as you may expect - take the following code ;-<br />
<br />
<span class="default">&lt;?php<br />
$a</span><span class="keyword">=array(</span><span class="default">1</span><span class="keyword">,</span><span class="default">2</span><span class="keyword">,</span><span class="default">3</span><span class="keyword">);<br />
</span><span class="default">$b</span><span class="keyword">=array(</span><span class="default">4</span><span class="keyword">,</span><span class="default">5</span><span class="keyword">,</span><span class="default">6</span><span class="keyword">);<br />
</span><span class="default">$c</span><span class="keyword">=</span><span class="default">1</span><span class="keyword">;<br />
<br />
</span><span class="default">$a</span><span class="keyword">[</span><span class="default">$c</span><span class="keyword">++]=</span><span class="default">$b</span><span class="keyword">[</span><span class="default">$c</span><span class="keyword">++];<br />
<br />
</span><span class="default">print_r</span><span class="keyword">( </span><span class="default">$a </span><span class="keyword">) ;<br />
</span><span class="default">?&gt;<br />
</span><br />
This will output;-<br />
Array ( [0] =&gt; 1 [1] =&gt; 6 [2] =&gt; 3 )<br />
as if the code said;-<br />
$a[1]=$b[2];<br />
<br />
Under a C compiler the result is;-<br />
Array ( [0] =&gt; 1 [1] =&gt; 5 [2] =&gt; 3 )<br />
as if the code said;-<br />
$a[1]=$b[1];<br />
<br />
It would appear that in php the increment in the left side of the assignment is processed prior to processing the right side of the assignment, whereas in C, neither increment occurs until after the assignment.</span>
</code></div>
  </div>
 </div>
 <a name="43111"></a>
 <div class="note">
  <a href="#43111" class="date">09-Jun-2004 05:58</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
of course this should be clear, but i think it has to be mentioned espacially:<br />
<br />
AND is not the same like &amp;&amp;<br />
<br />
for example:<br />
<br />
<span class="default">&lt;?php $a </span><span class="keyword">&amp;&amp; </span><span class="default">$b </span><span class="keyword">|| </span><span class="default">$c</span><span class="keyword">; </span><span class="default">?&gt;<br />
</span>is not the same like<br />
<span class="default">&lt;?php $a </span><span class="keyword">AND </span><span class="default">$b </span><span class="keyword">|| </span><span class="default">$c</span><span class="keyword">; </span><span class="default">?&gt;<br />
</span><br />
the first thing is<br />
(a and b) or c<br />
<br />
the second<br />
a and (b or c)<br />
<br />
'cause || has got a higher priority than and, but less than &amp;&amp;<br />
<br />
of course, using always [ &amp;&amp; and || ] or [ AND and OR ] would be okay, but than you should at least respect the following:<br />
<br />
<span class="default">&lt;?php $a </span><span class="keyword">= </span><span class="default">$b </span><span class="keyword">&amp;&amp; </span><span class="default">$c</span><span class="keyword">; </span><span class="default">?&gt;<br />
&lt;?php $a </span><span class="keyword">= </span><span class="default">$b </span><span class="keyword">AND </span><span class="default">$c</span><span class="keyword">; </span><span class="default">?&gt;<br />
</span><br />
the first code will set $a to the result of the comparison $b with $c, both have to be true, while the second code line will set $a like $b and THAN - after that - compare the success of this with the value of $c<br />
<br />
maybe usefull for some tricky coding and helpfull to prevent bugs :D<br />
<br />
greetz, Warhog</span>
</code></div>
  </div>
 </div>
 <a name="12147"></a>
 <div class="note">
  <strong class='user'>yasuo_ohgaki at hotmail dot com</strong>
  <a href="#12147" class="date">25-Mar-2001 11:53</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Other Language books' operator precedence section usually include "(" and ")" - with exception of a Perl book that I have. (In PHP "{" and "}" should also be considered also). However, PHP Manual is not listed "(" and ")" in precedence list. It looks like "(" and ")" has higher precedence as it should be.<br />
<br />
Note: If you write following code, you would need "()" to get expected value.<br />
<br />
<span class="default">&lt;?php<br />
$bar </span><span class="keyword">= </span><span class="default">true</span><span class="keyword">;<br />
</span><span class="default">$str </span><span class="keyword">= </span><span class="string">"TEST"</span><span class="keyword">. (</span><span class="default">$bar </span><span class="keyword">? </span><span class="string">'true' </span><span class="keyword">: </span><span class="string">'false'</span><span class="keyword">) .</span><span class="string">"TEST"</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
Without "(" and ")" you will get only "true" in $str. <br />
(PHP4.0.4pl1/Apache DSO/Linux, PHP4.0.5RC1/Apache DSO/W2K Server)<br />
It's due to precedence, probably.</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=language.operators&amp;redirect=@w{AGGWDXF6}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.operators&amp;redirect=@w{AGGWDXF6}">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/language.operators.php">show source</a> |
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