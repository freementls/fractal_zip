<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Expressions - Manual</title>
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
 <link rel="prev" href="language.constants.predefined.php" />
 <link rel="next" href="language.operators.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/expressions" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/language.expressions.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/language.expressions.php" />
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
 <li class="active"><a href="language.expressions.php">Expressions</a></li>
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
  <a href="language.operators.php">Operators<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.constants.predefined.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Magic constants</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.expressions.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/language.expressions.php">Brazilian Portuguese</option>
    <option value="zh/language.expressions.php">Chinese (Simplified)</option>
    <option value="fr/language.expressions.php">French</option>
    <option value="de/language.expressions.php">German</option>
    <option value="ja/language.expressions.php">Japanese</option>
    <option value="pl/language.expressions.php">Polish</option>
    <option value="ro/language.expressions.php">Romanian</option>
    <option value="ru/language.expressions.php">Russian</option>
    <option value="fa/language.expressions.php">Persian</option>
    <option value="es/language.expressions.php">Spanish</option>
    <option value="tr/language.expressions.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="language.expressions" class="chapter">
   <h1>Expressions</h1>

   <p class="simpara">
    Expressions are the most important building stones of PHP.  In PHP,
    almost anything you write is an expression.  The simplest yet
    most accurate way to define an expression is &quot;anything that has a
    value&quot;.
   </p>
   <p class="simpara">
    The most basic forms of expressions are constants and variables.
    When you type &quot;<var class="varname"><var class="varname">$a</var></var> = 5&quot;, you&#039;re assigning &#039;5&#039; into
    <var class="varname"><var class="varname">$a</var></var>.  &#039;5&#039;, obviously,
    has the value 5, or in other words &#039;5&#039; is an expression with the
    value of 5 (in this case, &#039;5&#039; is an integer constant).
   </p>
   <p class="simpara">
    After this assignment, you&#039;d expect <var class="varname"><var class="varname">$a</var></var>&#039;s value to be 5 as
    well, so if you wrote <var class="varname"><var class="varname">$b</var></var> = <var class="varname"><var class="varname">$a</var></var>, you&#039;d expect it to behave just as
    if you wrote <var class="varname"><var class="varname">$b</var></var> = 5.  In other words, <var class="varname"><var class="varname">$a</var></var> is an expression with the
    value of 5 as well.  If everything works right, this is exactly
    what will happen.
   </p>
   <p class="para">
    Slightly more complex examples for expressions are functions.  For
    instance, consider the following function:
    <div class="informalexample">
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">function&nbsp;</span><span style="color: #0000BB">foo&nbsp;</span><span style="color: #007700">()<br />{<br />&nbsp;&nbsp;&nbsp;&nbsp;return&nbsp;</span><span style="color: #0000BB">5</span><span style="color: #007700">;<br />}<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

    </div>
   </p>
   <p class="simpara">
    Assuming you&#039;re familiar with the concept of functions (if you&#039;re
    not, take a look at the chapter about <a href="language.functions.php" class="link">functions</a>), you&#039;d assume
    that typing <em>$c = foo()</em> is essentially just like
    writing <em>$c = 5</em>, and you&#039;re right.  Functions
    are expressions with the value of their return value.  Since <em>foo()</em>
    returns 5, the value of the expression &#039;<em>foo()</em>&#039; is 5.  Usually
    functions don&#039;t just return a static value but compute something.
   </p>
   <p class="simpara">
    Of course, values in PHP don&#039;t have to be integers, and very often
    they aren&#039;t.  PHP supports four scalar value types: <span class="type"><a href="language.types.integer.php" class="type integer">integer</a></span>
    values, floating point values (<span class="type"><a href="language.types.float.php" class="type float">float</a></span>), <span class="type"><a href="language.types.string.php" class="type string">string</a></span>
    values and <span class="type"><a href="language.types.boolean.php" class="type boolean">boolean</a></span> values (scalar values are values that you
    can&#039;t &#039;break&#039; into smaller pieces, unlike arrays, for instance). PHP also 
    supports two composite (non-scalar) types: arrays and objects. Each of
    these value types can be assigned into variables or returned from functions.
   </p>
   <p class="simpara">
    PHP takes expressions much further, in the same way many other languages
    do.  PHP is an expression-oriented language, in the
    sense that almost everything is an expression.  Consider the
    example we&#039;ve already dealt with, &#039;<var class="varname"><var class="varname">$a</var></var> = 5&#039;.  It&#039;s easy to see that
    there are two values involved here, the value of the integer
    constant &#039;5&#039;, and the value of <var class="varname"><var class="varname">$a</var></var> which is being updated to 5 as
    well.  But the truth is that there&#039;s one additional value involved
    here, and that&#039;s the value of the assignment itself.  The
    assignment itself evaluates to the assigned value, in this case 5.
    In practice, it means that &#039;<var class="varname"><var class="varname">$a</var></var> = 5&#039;, regardless of what it does,
    is an expression with the value 5.  Thus, writing something like
    &#039;<var class="varname"><var class="varname">$b</var></var> = (<var class="varname"><var class="varname">$a</var></var> = 5)&#039; is like writing
    &#039;<var class="varname"><var class="varname">$a</var></var> = 5; <var class="varname"><var class="varname">$b</var></var> = 5;&#039; (a semicolon
    marks the end of a statement).  Since assignments are parsed in a
    right to left order, you can also write &#039;<var class="varname"><var class="varname">$b</var></var> = <var class="varname"><var class="varname">$a</var></var> = 5&#039;.
   </p>
   <p class="simpara">
    Another good example of expression orientation is pre- and
    post-increment and decrement.  Users of PHP and many other
    languages may be familiar with the notation of <em>variable++</em> and
    <em>variable--</em>.  These are <a href="language.operators.increment.php" class="link">
    increment and decrement operators</a>.  In PHP, like in C, there
    are two types of increment - pre-increment and post-increment.
    Both pre-increment and post-increment essentially increment the
    variable, and the effect on the variable is identical.  The
    difference is with the value of the increment expression.
    Pre-increment, which is written &#039;++<var class="varname"><var class="varname">$variable</var></var>&#039;, evaluates to the
    incremented value (PHP increments the variable before reading its
    value, thus the name &#039;pre-increment&#039;).  Post-increment, which is
    written &#039;<var class="varname"><var class="varname">$variable</var></var>++&#039; evaluates to the original value of
    $variable, before it was incremented (PHP increments the variable
    after reading its value, thus the name &#039;post-increment&#039;).
   </p>
   <p class="simpara">
    A very common type of expressions are <a href="language.operators.comparison.php" class="link">comparison</a>
    expressions. These expressions evaluate to either  <strong><code>FALSE</code></strong> or <strong><code>TRUE</code></strong>. PHP
    supports &gt; (bigger than), &gt;= (bigger than or equal to), == (equal),
    != (not equal), &lt; (smaller than) and &lt;= (smaller than or equal to).
    The language also supports a set of strict equivalence operators: ===
    (equal to and same type) and !== (not equal to or not same type).
    These expressions are most commonly used inside conditional execution,
    such as <em>if</em> statements.
   </p>
   <p class="simpara">
    The last example of expressions we&#039;ll deal with here is combined
    operator-assignment expressions.  You already know that if you
    want to increment <var class="varname"><var class="varname">$a</var></var> by 1, you can simply write
    &#039;<var class="varname"><var class="varname">$a</var></var>++&#039; or &#039;++<var class="varname"><var class="varname">$a</var></var>&#039;.
    But what if you want to add more than one to it, for instance 3?
    You could write &#039;<var class="varname"><var class="varname">$a</var></var>++&#039; multiple times, but this
    is obviously not a very efficient or comfortable way.  A much more
    common practice is to write &#039;<var class="varname"><var class="varname">$a</var></var> =
    <var class="varname"><var class="varname">$a</var></var> + 3&#039;.  &#039;<var class="varname"><var class="varname">$a</var></var> + 3&#039; evaluates
    to the value of <var class="varname"><var class="varname">$a</var></var> plus 3, and is assigned back
    into <var class="varname"><var class="varname">$a</var></var>, which results in incrementing <var class="varname"><var class="varname">$a</var></var>
    by 3.  In PHP, as in several other languages like C, you can write this
    in a shorter way, which with time would become clearer and quicker to
    understand as well. Adding 3 to the current value of <var class="varname"><var class="varname">$a</var></var>
    can be written &#039;<var class="varname"><var class="varname">$a</var></var> += 3&#039;.  This means exactly
    &quot;take the value of <var class="varname"><var class="varname">$a</var></var>, add 3 to it, and assign it
    back into <var class="varname"><var class="varname">$a</var></var>&quot;. In addition to being shorter and
    clearer, this also results in faster execution.  The value of
    &#039;<var class="varname"><var class="varname">$a</var></var> += 3&#039;, like the value of a regular assignment, is
    the assigned value. Notice that it is NOT 3, but the combined value
    of <var class="varname"><var class="varname">$a</var></var> plus 3 (this is the value that&#039;s
    assigned into <var class="varname"><var class="varname">$a</var></var>).  Any two-place operator can be used
    in this operator-assignment mode, for example &#039;<var class="varname"><var class="varname">$a</var></var> -= 5&#039;
    (subtract 5 from the value of <var class="varname"><var class="varname">$a</var></var>), &#039;<var class="varname"><var class="varname">$b</var></var> *= 7&#039;
    (multiply the value of <var class="varname"><var class="varname">$b</var></var> by 7), etc.
   </p>
   <p class="para">
    There is one more expression that may seem odd if you haven&#039;t seen
    it in other languages, the ternary conditional operator:
   </p>
   <p class="para">
    <div class="informalexample">
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br />$first&nbsp;</span><span style="color: #007700">?&nbsp;</span><span style="color: #0000BB">$second&nbsp;</span><span style="color: #007700">:&nbsp;</span><span style="color: #0000BB">$third<br />?&gt;</span>
</span>
</code></div>
     </div>

    </div>
   </p>
   <p class="para">
    If the value of the first subexpression is <strong><code>TRUE</code></strong> (non-zero), then
    the second subexpression is evaluated, and that is the result of
    the conditional expression. Otherwise, the third subexpression is
    evaluated, and that is the value.
   </p>
   <p class="para">
    The following example should help you understand pre- and
    post-increment and expressions in general a bit better:
   </p>
   <p class="para">
    <div class="informalexample">
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">function&nbsp;</span><span style="color: #0000BB">double</span><span style="color: #007700">(</span><span style="color: #0000BB">$i</span><span style="color: #007700">)<br />{<br />&nbsp;&nbsp;&nbsp;&nbsp;return&nbsp;</span><span style="color: #0000BB">$i</span><span style="color: #007700">*</span><span style="color: #0000BB">2</span><span style="color: #007700">;<br />}<br /></span><span style="color: #0000BB">$b&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">5</span><span style="color: #007700">;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">/*&nbsp;assign&nbsp;the&nbsp;value&nbsp;five&nbsp;into&nbsp;the&nbsp;variable&nbsp;$a&nbsp;and&nbsp;$b&nbsp;*/<br /></span><span style="color: #0000BB">$c&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">$a</span><span style="color: #007700">++;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">/*&nbsp;post-increment,&nbsp;assign&nbsp;original&nbsp;value&nbsp;of&nbsp;$a&nbsp;<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(5)&nbsp;to&nbsp;$c&nbsp;*/<br /></span><span style="color: #0000BB">$e&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">$d&nbsp;</span><span style="color: #007700">=&nbsp;++</span><span style="color: #0000BB">$b</span><span style="color: #007700">;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">/*&nbsp;pre-increment,&nbsp;assign&nbsp;the&nbsp;incremented&nbsp;value&nbsp;of&nbsp;<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$b&nbsp;(6)&nbsp;to&nbsp;$d&nbsp;and&nbsp;$e&nbsp;*/<br /><br />/*&nbsp;at&nbsp;this&nbsp;point,&nbsp;both&nbsp;$d&nbsp;and&nbsp;$e&nbsp;are&nbsp;equal&nbsp;to&nbsp;6&nbsp;*/<br /><br /></span><span style="color: #0000BB">$f&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">double</span><span style="color: #007700">(</span><span style="color: #0000BB">$d</span><span style="color: #007700">++);&nbsp;&nbsp;</span><span style="color: #FF8000">/*&nbsp;assign&nbsp;twice&nbsp;the&nbsp;value&nbsp;of&nbsp;$d&nbsp;before<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;the&nbsp;increment,&nbsp;2*6&nbsp;=&nbsp;12&nbsp;to&nbsp;$f&nbsp;*/<br /></span><span style="color: #0000BB">$g&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">double</span><span style="color: #007700">(++</span><span style="color: #0000BB">$e</span><span style="color: #007700">);&nbsp;&nbsp;</span><span style="color: #FF8000">/*&nbsp;assign&nbsp;twice&nbsp;the&nbsp;value&nbsp;of&nbsp;$e&nbsp;after<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;the&nbsp;increment,&nbsp;2*7&nbsp;=&nbsp;14&nbsp;to&nbsp;$g&nbsp;*/<br /></span><span style="color: #0000BB">$h&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">$g&nbsp;</span><span style="color: #007700">+=&nbsp;</span><span style="color: #0000BB">10</span><span style="color: #007700">;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">/*&nbsp;first,&nbsp;$g&nbsp;is&nbsp;incremented&nbsp;by&nbsp;10&nbsp;and&nbsp;ends&nbsp;with&nbsp;the&nbsp;<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;value&nbsp;of&nbsp;24.&nbsp;the&nbsp;value&nbsp;of&nbsp;the&nbsp;assignment&nbsp;(24)&nbsp;is&nbsp;<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;then&nbsp;assigned&nbsp;into&nbsp;$h,&nbsp;and&nbsp;$h&nbsp;ends&nbsp;with&nbsp;the&nbsp;value&nbsp;<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;of&nbsp;24&nbsp;as&nbsp;well.&nbsp;*/<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

    </div>
   </p>
   <p class="simpara">
    Some expressions can be considered as statements. In
    this case, a statement has the form of &#039;<em>expr ;</em>&#039; that is, an
    expression followed by a semicolon.  In <em>&#039;$b = $a = 5;&#039;</em>,
    <em>&#039;$a = 5&#039;</em> is a valid expression, but it&#039;s not a statement
    by itself. <em>&#039;$b = $a = 5;&#039;</em> however is a valid statement.
   </p>
   <p class="simpara">
    One last thing worth mentioning is the truth value of expressions.
    In many events, mainly in conditional execution and loops, you&#039;re
    not interested in the specific value of the expression, but only
    care about whether it means <strong><code>TRUE</code></strong> or <strong><code>FALSE</code></strong>.
    
    
    
    The constants <strong><code>TRUE</code></strong> and <strong><code>FALSE</code></strong> (case-insensitive) are the two 
    possible boolean values. When necessary, an expression is 
    automatically converted to boolean. See the 
    <a href="language.types.type-juggling.php#language.types.typecasting" class="link">section about
    type-casting</a> for details about how.
   </p>
   <p class="simpara">
    PHP provides a full and powerful implementation of expressions, and
    documenting it entirely goes beyond the scope of this manual. The
    above examples should give you a good idea about what expressions
    are and how you can construct useful expressions. Throughout the
    rest of this manual we&#039;ll write <var class="varname"><var class="varname">expr</var></var>
    to indicate any valid PHP expression.
   </p>
  </div>
<br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="language.operators.php">Operators<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.constants.predefined.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Magic constants</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.expressions.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=language.expressions&amp;redirect=http://www.php.net/manual/en/language.expressions.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.expressions&amp;redirect=http://www.php.net/manual/en/language.expressions.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Expressions</strong>
 </div><div id="allnotes">
 <a name="108902"></a>
 <div class="note">
  <strong class='user'>phvandeberghe at hotmail dot com</strong>
  <a href="#108902" class="date">03-Jun-2012 08:03</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
About the multiply (star) character:<br />
Note that if you try to use the '*' in a comparaison you will have many results...<br />
<br />
<span class="default">&lt;?php<br />
$a </span><span class="keyword">= </span><span class="string">'*'</span><span class="keyword">;<br />
switch(</span><span class="default">$a</span><span class="keyword">)<br />
{<br />
&nbsp; case </span><span class="default">1</span><span class="keyword">: echo </span><span class="string">' (int)one'</span><span class="keyword">;<br />
&nbsp; case </span><span class="default">0</span><span class="keyword">: echo </span><span class="string">' (int)zero'</span><span class="keyword">;<br />
&nbsp; case </span><span class="string">'0'</span><span class="keyword">: echo </span><span class="string">' (string)zero'</span><span class="keyword">;<br />
&nbsp; case </span><span class="string">'*'</span><span class="keyword">: echo </span><span class="string">' (string)star'</span><span class="keyword">;<br />
&nbsp; case </span><span class="default">true</span><span class="keyword">: echo </span><span class="string">' (bool)true'</span><span class="keyword">;<br />
&nbsp; case </span><span class="default">false</span><span class="keyword">: echo </span><span class="string">' (bool)false'</span><span class="keyword">;<br />
}<br />
</span><span class="comment">// ouput<br />
// (int)zero (string)zero (string)star (bool)true (bool)false<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="107687"></a>
 <div class="note">
  <strong class='user'>antickon at gmail dot com</strong>
  <a href="#107687" class="date">26-Feb-2012 07:29</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
evaluation order of subexpressions is not strictly defined for all operators<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">a</span><span class="keyword">() {echo </span><span class="string">'a'</span><span class="keyword">;}<br />
function </span><span class="default">b</span><span class="keyword">() {echo </span><span class="string">'b'</span><span class="keyword">;}<br />
</span><span class="default">a</span><span class="keyword">() == </span><span class="default">b</span><span class="keyword">(); </span><span class="comment">// outputs "ab", ie evaluates left-to-right<br />
<br />
</span><span class="default">$a </span><span class="keyword">= </span><span class="default">3</span><span class="keyword">;<br />
</span><span class="default">var_dump</span><span class="keyword">( </span><span class="default">$a </span><span class="keyword">== </span><span class="default">$a </span><span class="keyword">= </span><span class="default">4 </span><span class="keyword">); </span><span class="comment">// outputs bool(true), ie evaluates right-to-left<br />
</span><span class="default">?&gt;<br />
</span><br />
this is not a bug: "we [php developers] make no guarantee about the order of evaluation".<br />
See @w{JBVFFY7T}bug.php?id=61188</span>
</code></div>
  </div>
 </div>
 <a name="90327"></a>
 <div class="note">
  <strong class='user'>Magnus Deininger, dma05 at web dot de</strong>
  <a href="#90327" class="date">16-Apr-2009 08:04</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Note that even though PHP borrows large portions of its syntax from C, the ',' is treated quite differently. It's not possible to create combined expressions in PHP using the comma-operator that C has, except in for() loops.<br />
<br />
Example (parse error):<br />
<br />
<span class="default">&lt;?php<br />
<br />
$a </span><span class="keyword">= </span><span class="default">2</span><span class="keyword">, </span><span class="default">$b </span><span class="keyword">= </span><span class="default">4</span><span class="keyword">;<br />
<br />
echo </span><span class="default">$a</span><span class="keyword">.</span><span class="string">"\n"</span><span class="keyword">;<br />
echo </span><span class="default">$b</span><span class="keyword">.</span><span class="string">"\n"</span><span class="keyword">;<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
Example (works):<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">for (</span><span class="default">$a </span><span class="keyword">= </span><span class="default">2</span><span class="keyword">, </span><span class="default">$b </span><span class="keyword">= </span><span class="default">4</span><span class="keyword">; </span><span class="default">$a </span><span class="keyword">&lt; </span><span class="default">3</span><span class="keyword">; </span><span class="default">$a</span><span class="keyword">++)<br />
{<br />
&nbsp; echo </span><span class="default">$a</span><span class="keyword">.</span><span class="string">"\n"</span><span class="keyword">;<br />
&nbsp; echo </span><span class="default">$b</span><span class="keyword">.</span><span class="string">"\n"</span><span class="keyword">;<br />
}<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
This is because PHP doesn't actually have a proper comma-operator, it's only supported as syntactic sugar in for() loop headers. In C, it would have been perfectly legitimate to have this:<br />
<br />
int f()<br />
{<br />
&nbsp; int a, b;<br />
&nbsp; a = 2, b = 4;<br />
<br />
&nbsp; return a;<br />
}<br />
<br />
or even this:<br />
<br />
int g()<br />
{<br />
&nbsp; int a, b;<br />
&nbsp; a = (2, b = 4);<br />
<br />
&nbsp; return a;<br />
}<br />
<br />
In f(), a would have been set to 2, and b would have been set to 4.<br />
In g(), (2, b = 4) would be a single expression which evaluates to 4, so both a and b would have been set to 4.</span>
</code></div>
  </div>
 </div>
 <a name="84184"></a>
 <div class="note">
  <strong class='user'>phpsourcecode at blogspot dot com</strong>
  <a href="#84184" class="date">02-Jul-2008 06:37</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The ternary conditional operator is a useful way of avoiding <br />
<a href="http://phpsourcecode.blogspot.com" rel="nofollow" target="_blank">http://phpsourcecode.blogspot.com</a><br />
<br />
inconvenient if statements.&nbsp; They can even be used in the middle of a string concatenation, if you use parentheses. <br />
<br />
Example:<br />
<br />
if ( $wakka ) {<br />
&nbsp; $string = 'foo' ;<br />
} else {<br />
&nbsp; $string = 'bar' ;<br />
}<br />
<br />
The above can be expressed like the following:<br />
<br />
$string = $wakka ? 'foo' : 'bar' ;<br />
<br />
If $wakka is true, $string is assigned 'foo', and if it's false, $string is assigned 'bar'.<br />
<br />
To do the same in a concatenation, try:<br />
<br />
$string = $otherString . ( $wakka ? 'foo' : 'bar' ) ;</span>
</code></div>
  </div>
 </div>
 <a name="81849"></a>
 <div class="note">
  <strong class='user'>denzoo at gmail dot com</strong>
  <a href="#81849" class="date">16-Mar-2008 04:52</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
To jvm at jvmyers dot com:<br />
Your first two if statements just check if there's anything in the string, if you wish to actually execute the code in your string you need eval().</span>
</code></div>
  </div>
 </div>
 <a name="81363"></a>
 <div class="note">
  <strong class='user'>jvm at jvmyers dot com</strong>
  <a href="#81363" class="date">24-Feb-2008 12:20</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
<span class="default">&lt;?php<br />
</span><span class="comment">// Compound booleans expressed as string args in an 'if' statement don't work as expected:<br />
//<br />
//&nbsp; &nbsp; Context:<br />
//&nbsp; &nbsp; &nbsp; &nbsp; 1.&nbsp; I generate an array of counters<br />
//&nbsp; &nbsp; &nbsp; &nbsp; 2.&nbsp; I dynamically generate a compound boolean based on selected counters in the array<br />
//&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Note: since the real array is sparse, I must use the 'empty' operator<br />
//&nbsp; &nbsp; &nbsp; &nbsp; 3.&nbsp; When I submit the compound boolean as the expression of an 'if' statement, <br />
//&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; the 'if' appears to resolve ONLY the first element of the compound boolean.<br />
//&nbsp; &nbsp; Conclusion: appears to be a short-circuiting issue<br />
<br />
</span><span class="default">$aArray </span><span class="keyword">= array(</span><span class="default">1</span><span class="keyword">,</span><span class="default">0</span><span class="keyword">);<br />
<br />
</span><span class="comment">// Case 1: 'if' expression passed as string:<br />
<br />
</span><span class="default">$sCondition </span><span class="keyword">= </span><span class="string">"!empty($aArray[0]) &amp;&amp; !empty($aArray[1])"</span><span class="keyword">;<br />
if (</span><span class="default">$sCondition</span><span class="keyword">)<br />
{<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="string">"1. Conditions met&lt;br /&gt;"</span><span class="keyword">;<br />
}<br />
else<br />
{<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="string">"1. Conditions not met&lt;br /&gt;"</span><span class="keyword">;<br />
}<br />
<br />
</span><span class="comment">// Case 1 output:&nbsp; "1. Conditions met"<br />
<br />
// Case 2: same as Case 1, but using catenation operator<br />
<br />
</span><span class="keyword">if (</span><span class="string">""</span><span class="keyword">.</span><span class="default">$sCondition</span><span class="keyword">.</span><span class="string">""</span><span class="keyword">)<br />
{<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="string">"2. Conditions met&lt;br /&gt;"</span><span class="keyword">;<br />
}<br />
else<br />
{<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="string">"2. Conditions not met&lt;br /&gt;"</span><span class="keyword">;<br />
}<br />
<br />
</span><span class="comment">// Case 2 output:&nbsp; "2. Conditions met"<br />
<br />
// Case 3: same 'if' expression but passed in context:<br />
<br />
</span><span class="keyword">if (!empty(</span><span class="default">$aArray</span><span class="keyword">[</span><span class="default">0</span><span class="keyword">]) &amp;&amp; !empty(</span><span class="default">$aArray</span><span class="keyword">[</span><span class="default">1</span><span class="keyword">]))<br />
{<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="string">"3. Conditions met&lt;br /&gt;"</span><span class="keyword">;<br />
}<br />
else<br />
{<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="string">"3. Conditions not met&lt;br /&gt;"</span><span class="keyword">;<br />
}<br />
<br />
</span><span class="comment">// Case 3 output:&nbsp; "3. Conditions not met"<br />
<br />
// jvm@jvmyers.com<br />
</span><span class="default">?&gt;<br />
</span><br />
PS: the bug folks say this "does not imply a bug in PHP itself."&nbsp; Sure bugs me!</span>
</code></div>
  </div>
 </div>
 <a name="78636"></a>
 <div class="note">
  <strong class='user'>petruzanauticoyahoo?com!ar</strong>
  <a href="#78636" class="date">20-Oct-2007 08:41</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Regarding the ternary operator, I would rather say that the best option is to enclose all the expression in parantheses, to avoid errors and improve clarity:<br />
<br />
<span class="default">&lt;?php<br />
&nbsp;&nbsp; </span><span class="keyword">print ( </span><span class="default">$a </span><span class="keyword">&gt; </span><span class="default">1 </span><span class="keyword">? </span><span class="string">"many" </span><span class="keyword">: </span><span class="string">"just one" </span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span><br />
PS: for php, C++, and any other language that has it.</span>
</code></div>
  </div>
 </div>
 <a name="77291"></a>
 <div class="note">
  <strong class='user'>winks716</strong>
  <a href="#77291" class="date">23-Aug-2007 01:42</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
reply to egonfreeman at gmail dot com<br />
04-Apr-2007 07:45 <br />
<br />
the second example u mentioned as follow:<br />
=====================================<br />
<br />
$n = 3;<br />
$n * $n++<br />
<br />
from 3 * 3 into 3 * 4. Post- operations operate on a variable after it has been 'checked', but it doesn't necessarily state that it should happen AFTER an evaluation is over (on the contrary, as a matter of fact).<br />
<br />
===========================================<br />
<br />
everything works correctly but one sentence should be modified:<br />
<br />
"from 3 * 3 into 3 * 4"&nbsp; should be "from 3 * 3 into 4 * 3"<br />
<br />
best regards~ :)</span>
</code></div>
  </div>
 </div>
 <a name="76571"></a>
 <div class="note">
  <strong class='user'>george dot langley at shaw dot ca</strong>
  <a href="#76571" class="date">20-Jul-2007 11:01</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Here's a quick example of Pre and Post-incrementation, in case anyone does feel confused (ref anonymous poster 31 May 2005)<br />
<br />
<span class="default">&lt;?PHP<br />
</span><span class="keyword">echo </span><span class="string">"Using Pre-increment ++\$a:&lt;br&gt;"</span><span class="keyword">;<br />
</span><span class="default">$a </span><span class="keyword">= </span><span class="default">1</span><span class="keyword">;<br />
echo </span><span class="string">"\$a = $a&lt;br&gt;"</span><span class="keyword">;<br />
</span><span class="default">$b </span><span class="keyword">= ++</span><span class="default">$a</span><span class="keyword">;<br />
echo </span><span class="string">"\$b = ++\$a, so \$b = $b and \$a = $a&lt;br&gt;"</span><span class="keyword">;<br />
echo </span><span class="string">"&lt;br&gt;"</span><span class="keyword">;<br />
echo </span><span class="string">"Using Post-increment \$a++:&lt;br&gt;"</span><span class="keyword">;<br />
</span><span class="default">$a </span><span class="keyword">= </span><span class="default">1</span><span class="keyword">;<br />
echo </span><span class="string">"\$a = $a&lt;br&gt;"</span><span class="keyword">;<br />
</span><span class="default">$b </span><span class="keyword">= </span><span class="default">$a</span><span class="keyword">++;<br />
echo </span><span class="string">"\$b = \$a++, so \$b = $b and \$a = $a&lt;br&gt;"</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
HTH</span>
</code></div>
  </div>
 </div>
 <a name="74308"></a>
 <div class="note">
  <strong class='user'>egonfreeman at gmail dot com</strong>
  <a href="#74308" class="date">04-Apr-2007 07:45</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
It is worthy to mention that:<br />
<br />
$n = 3;<br />
$n * --$n<br />
<br />
WILL RETURN 4 instead of 6.<br />
<br />
It can be a hard to spot "error", because in our human thought process this really isn't an error at all! But you have to remember that PHP (as it is with many other high-level languages) evaluates its statements RIGHT-TO-LEFT, and therefore "--$n" comes BEFORE multiplying, so - in the end - it's really "2 * 2", not "3 * 2".<br />
<br />
It is also worthy to mention that the same behavior will change:<br />
<br />
$n = 3;<br />
$n * $n++<br />
<br />
from 3 * 3 into 3 * 4. Post- operations operate on a variable after it has been 'checked', but it doesn't necessarily state that it should happen AFTER an evaluation is over (on the contrary, as a matter of fact).<br />
<br />
So, if you ever find yourself on a 'wild goose chase' for a bug in that "impossible-to-break, so-very-simple" piece of code that uses pre-/post-'s, remember this post. :)<br />
<br />
(just thought I'd check it out - turns out I was right :P)</span>
</code></div>
  </div>
 </div>
 <a name="73261"></a>
 <div class="note">
  <strong class='user'>shawnster</strong>
  <a href="#73261" class="date">14-Feb-2007 04:56</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
An easy fix (although intuitively tough to do...) is to reverse the comparison.<br />
<br />
if (5 == $a) {}<br />
<br />
If you forget the second '=', you'll get a parse error for trying to assign a value to a non-variable.</span>
</code></div>
  </div>
 </div>
 <a name="72692"></a>
 <div class="note">
  <strong class='user'>nabil_kadimi at hotmail dot com</strong>
  <a href="#72692" class="date">29-Jan-2007 07:46</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Attention! php will not warn you if you write (1) When you mean (2)<br />
<br />
(1)<br />
&lt;?<br />
if($a=0) <br />
&nbsp;&nbsp;&nbsp; echo "condition is true";<br />
else <br />
&nbsp;&nbsp;&nbsp; echo "condition is false";<br />
//output: condition is false<br />
?&gt;<br />
<br />
(2)<br />
&lt;?<br />
if($a==0) <br />
&nbsp;&nbsp;&nbsp; echo "condition is true";<br />
else <br />
&nbsp;&nbsp;&nbsp; echo "condition is false";<br />
//output: condition is true<br />
?&gt;</span>
</code></div>
  </div>
 </div>
 <a name="60899"></a>
 <div class="note">
  <strong class='user'>richard at phase4 dot ie</strong>
  <a href="#60899" class="date">19-Jan-2006 12:00</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Follow up on Martin K. There are no hard and fast rules regarding operator precedence. Newbies should definitely learn them, but if their use results in code that is not easy to read you should use parentheses. The two important things are that it works properly AND is maintainable by you and others.</span>
</code></div>
  </div>
 </div>
 <a name="57998"></a>
 <div class="note">
  <strong class='user'>Martin K</strong>
  <a href="#57998" class="date">20-Oct-2005 06:28</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
At 04-Feb-2005 05:13, tom at darlingpet dot com said:<br />
&gt; It's also a good idea to use parenthesis when using something SIMILAR to:<br />
&gt; <br />
&gt; <span class="default">&lt;?php<br />
</span><span class="keyword">&gt; echo (</span><span class="default">trim</span><span class="keyword">(</span><span class="default">$var</span><span class="keyword">)==</span><span class="string">""</span><span class="keyword">) ? </span><span class="string">"empty" </span><span class="keyword">: </span><span class="string">"not empty"</span><span class="keyword">;<br />
&gt; </span><span class="default">?&gt;<br />
</span><br />
No, it's a BAD idea.<br />
<br />
All the short-circuiting operators, including the ternary conditional operator, have LOWER precedence than the comparison operators, so they almost NEVER need parentheses around their subexpressions.<br />
<br />
Inserting the parentheses suggested above does not change the meaning of the code, but their use misleads inexperienced programmers to expect that things like this will work in a similar manner:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">my_print</span><span class="keyword">(</span><span class="default">$a</span><span class="keyword">) { print(</span><span class="default">$a</span><span class="keyword">); }<br />
</span><span class="default">my_print </span><span class="keyword">(</span><span class="default">trim</span><span class="keyword">(</span><span class="default">$var</span><span class="keyword">)==</span><span class="string">""</span><span class="keyword">) ? </span><span class="string">"empty" </span><span class="keyword">: </span><span class="string">"not empty"</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
when of course it doesn't.<br />
<br />
Rather than worrying that code doesn't work as expected, simply learn the precedence rules (<a href="@w{AGGWDXF6}" rel="nofollow" target="_blank">@w{AGGWDXF6}</a>) so that one expects the right things.</span>
</code></div>
  </div>
 </div>
 <a name="55969"></a>
 <div class="note">
  <strong class='user'>stochnagara at hotmail dot com</strong>
  <a href="#55969" class="date">19-Aug-2005 05:06</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
12345alex at gmx dot net 's case is actually handled by the === operator. That's what he needs.<br />
<br />
There is also another widely used function. I call it myself is_nil which is true for NULL,FALSE,array() and '', but not for 0 and "0".<br />
<br />
function is_nil ($value) {<br />
&nbsp;return !$value &amp;&amp; $value !== 0 &amp;&amp; $value !== '0';<br />
}<br />
<br />
Another useful function is "get first argument if it is not empty or get second argument otherwise". The code is:<br />
<br />
function def ($value, $defaultValue) {<br />
&nbsp;return is_nil ($value) ? $defaultValue : $value;<br />
}</span>
</code></div>
  </div>
 </div>
 <a name="55815"></a>
 <div class="note">
  <strong class='user'>12345alex at gmx dot net</strong>
  <a href="#55815" class="date">14-Aug-2005 07:00</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
this code:<br />
&nbsp;&nbsp;&nbsp; print array() == NULL ? "True" : "False";<br />
&nbsp;&nbsp;&nbsp; print " (" . (array() == NULL) . ")\n";<br />
<br />
&nbsp;&nbsp;&nbsp; $arr = array();<br />
&nbsp;&nbsp;&nbsp; print array() == $arr ? "True" : "False";<br />
&nbsp;&nbsp;&nbsp; print " (" . (array() == $arr) . ")\n";<br />
<br />
&nbsp;&nbsp;&nbsp; print count(array()) . "\n";<br />
&nbsp;&nbsp;&nbsp; print count(NULL) . "\n";<br />
<br />
will output (on php4 and php5):<br />
&nbsp;&nbsp;&nbsp; True (1)<br />
&nbsp;&nbsp;&nbsp; True (1)<br />
&nbsp;&nbsp;&nbsp; 0<br />
&nbsp;&nbsp;&nbsp; 0<br />
<br />
so to decide wether i have NULL or an empty array i will also have to use gettype(). this seems some kind of weird for me, although if is this is a bug, somebody should have noticed it before.<br />
<br />
alex</span>
</code></div>
  </div>
 </div>
 <a name="54179"></a>
 <div class="note">
  <strong class='user'>sabinx at gmail dot com</strong>
  <a href="#54179" class="date">26-Jun-2005 11:25</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Pre- and Post-Incrementation, I believe, are important to note and in the correct place. The section deals with the value of an expression. ++$a and $a++ have different values, and both forms have valid uses.<br />
<br />
And, because it can be confusing, it is that much more important to note. Although it could be worded better, it does belong.</span>
</code></div>
  </div>
 </div>
 <a name="53404"></a>
 <div class="note">
  <a href="#53404" class="date">31-May-2005 12:07</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I don't see why it is necessary here to explain pre- and post- incrementing.<br />
<br />
This is something that will confuse new users of PHP, even longer time programmers will sometimes miss a the fine details of a construct like that.<br />
<br />
If something has a side-effect it should be on a line of it's own, or at least be an expression of it's own and not part of an assignment, condition or whatever.</span>
</code></div>
  </div>
 </div>
 <a name="49666"></a>
 <div class="note">
  <strong class='user'>tom at darlingpet dot com</strong>
  <a href="#49666" class="date">04-Feb-2005 02:13</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Something I've noticed with ternary expressions is if you do something like :<br />
<br />
&lt;?= $var=="something" ? "is something" : "not something"; ?&gt;<br />
<br />
It will give wacky results sometimes...<br />
<br />
So be sure to enclose the ternary expression in parenthesis when ever necessary (such as having multiple expressions or nested ternary expressions)<br />
<br />
The above could look like:<br />
<br />
&lt;?= ($var=="something") ? "is something" : "not something"; ?&gt;<br />
<br />
It's also a good idea to use parenthesis when using something SIMILAR to:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">echo (</span><span class="default">trim</span><span class="keyword">(</span><span class="default">$var</span><span class="keyword">)==</span><span class="string">""</span><span class="keyword">) ? </span><span class="string">"empty" </span><span class="keyword">: </span><span class="string">"not empty"</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
In some cases other than the &lt;?= ?&gt; example, not placing the entire expression in appropriate parenthesis might yield undesirable results as well.. but I'm not quite sure.</span>
</code></div>
  </div>
 </div>
 <a name="29789"></a>
 <div class="note">
  <strong class='user'>stian at datanerd dot net</strong>
  <a href="#29789" class="date">25-Feb-2003 02:37</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The short-circuit feature is indeed intended, and there are two types of evaluators, those who DO short-circuit, and those who DON'T, || / &amp;&amp; and | / &amp; respectively.<br />
The latter method is the bitwise operators, but works great with the usual boolean values ( 0/1 )<br />
<br />
So if you don't want short-circuiting, try using the | and &amp; instead.<br />
<br />
Read more about the bitwise operators here:<br />
<a href="@w{5B3FC3DK}" rel="nofollow" target="_blank">@w{5B3FC3DK}</a></span>
</code></div>
  </div>
 </div>
 <a name="24119"></a>
 <div class="note">
  <strong class='user'>oliver at hankeln-online dot de</strong>
  <a href="#24119" class="date">07-Aug-2002 06:06</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The short-circuiting IS a feature. It is also available in C, so I suppose the developers won´t remove it in future PHP versions.<br />
<br />
It is rather nice to write:<br />
<br />
$file=fopen("foo","r") or die("Error!");<br />
<br />
Greets,<br />
Oliver</span>
</code></div>
  </div>
 </div>
 <a name="23399"></a>
 <div class="note">
  <strong class='user'>php at cotest dot com</strong>
  <a href="#23399" class="date">17-Jul-2002 11:08</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
It should probably be mentioned that the short-circuiting of expressions (mentioned in some of the comments above) is often called "lazy evaluation" (in case someone else searches for the term "lazy" on this page and comes up empty!).</span>
</code></div>
  </div>
 </div>
 <a name="21750"></a>
 <div class="note">
  <strong class='user'>Mattias at mail dot ee</strong>
  <a href="#21750" class="date">25-May-2002 03:29</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
A note about the short-circuit behaviour of the boolean operators.<br />
<br />
1. if (func1() || func2())<br />
Now, if func1() returns true, func2() isn't run, since the expression<br />
will be true anyway.<br />
<br />
2. if (func1() &amp;&amp; func2())<br />
Now, if func1() returns false, func2() isn't run, since the expression<br />
will be false anyway.<br />
<br />
The reason for this behaviour comes probably from the programming<br />
language C, on which PHP seems to be based on. There the<br />
short-circuiting can be a very useful tool. For example:<br />
<br />
int * myarray = a_func_to_set_myarray(); // init the array<br />
if (myarray != NULL &amp;&amp; myarray[0] != 4321) // check<br />
&nbsp;&nbsp;&nbsp; myarray[0] = 1234;<br />
<br />
Now, the pointer myarray is checked for being not null, then the<br />
contents of the array is validated. This is important, because if<br />
you try to access an array whose address is invalid, the program<br />
will crash and die a horrible death. But thanks to the short<br />
circuiting, if myarray == NULL then myarray[0] won't be accessed,<br />
and the program will work fine.</span>
</code></div>
  </div>
 </div>
 <a name="11883"></a>
 <div class="note">
  <strong class='user'>yasuo_ohgaki at hotmail dot com</strong>
  <a href="#11883" class="date">11-Mar-2001 11:14</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Manual defines "expression is anything that has value", Therefore, parser will give error for following code.<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">(</span><span class="default">$val</span><span class="keyword">) ? echo(</span><span class="string">'true'</span><span class="keyword">) : echo(</span><span class="string">'false'</span><span class="keyword">);<br />
</span><span class="default">Note</span><span class="keyword">: </span><span class="string">"? : " </span><span class="default">operator has this syntax&nbsp; </span><span class="string">"expr ? expr : expr;"<br />
</span><span class="default">?&gt;<br />
</span><br />
since echo does not have(return) value and ?: expects expression(value).<br />
<br />
However, if function/language constructs that have/return value, such as include(), parser compiles code.<br />
<br />
Note: User defined functions always have/return value without explicit return statement (returns NULL if there is no return statement). Therefore, user defined functions are always valid expressions. <br />
[It may be useful to have VOID as new type to prevent programmer to use function as RVALUE by mistake]<br />
<br />
For example,<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">(</span><span class="default">$val</span><span class="keyword">) ? include(</span><span class="string">'true.inc'</span><span class="keyword">) : include(</span><span class="string">'false.inc'</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span><br />
is valid, since "include" returns value.<br />
<br />
The fact "echo" does not return value(="echo" is not a expression), is less obvious to me. <br />
<br />
Print() and Echo() is NOT identical since print() has/returns value and can be a valid expression.</span>
</code></div>
  </div>
 </div>
 <a name="9801"></a>
 <div class="note">
  <strong class='user'>anthony at n dot o dot s dot p dot a dot m dot trams dot com</strong>
  <a href="#9801" class="date">24-Nov-2000 11:01</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The ternary conditional operator is a useful way of avoiding inconvenient if statements.&nbsp; They can even be used in the middle of a string concatenation, if you use parentheses.&nbsp; <br />
<br />
Example:<br />
<br />
if ( $wakka ) {<br />
&nbsp; $string = 'foo' ;<br />
} else {<br />
&nbsp; $string = 'bar' ;<br />
}<br />
<br />
The above can be expressed like the following:<br />
<br />
$string = $wakka ? 'foo' : 'bar' ;<br />
<br />
If $wakka is true, $string is assigned 'foo', and if it's false, $string is assigned 'bar'.<br />
<br />
To do the same in a concatenation, try:<br />
<br />
$string = $otherString . ( $wakka ? 'foo' : 'bar' ) ;</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=language.expressions&amp;redirect=http://www.php.net/manual/en/language.expressions.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.expressions&amp;redirect=http://www.php.net/manual/en/language.expressions.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/language.expressions.php">show source</a> |
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