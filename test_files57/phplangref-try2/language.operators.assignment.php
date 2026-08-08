<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Assignment Operators - Manual</title>
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
 <link rel="prev" href="language.operators.arithmetic.php" />
 <link rel="next" href="language.operators.bitwise.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/operators.assignment" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/language.operators.assignment.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="@w{99AQHN5V}" />
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
 <li><a href="language.operators.precedence.php">Operator Precedence</a></li>
 <li><a href="language.operators.arithmetic.php">Arithmetic Operators</a></li>
 <li class="active"><a href="language.operators.assignment.php">Assignment Operators</a></li>
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
  <a href="language.operators.bitwise.php">Bitwise Operators<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.operators.arithmetic.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Arithmetic Operators</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.operators.assignment.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/language.operators.assignment.php">Brazilian Portuguese</option>
    <option value="zh/language.operators.assignment.php">Chinese (Simplified)</option>
    <option value="fr/language.operators.assignment.php">French</option>
    <option value="de/language.operators.assignment.php">German</option>
    <option value="ja/language.operators.assignment.php">Japanese</option>
    <option value="pl/language.operators.assignment.php">Polish</option>
    <option value="ro/language.operators.assignment.php">Romanian</option>
    <option value="ru/language.operators.assignment.php">Russian</option>
    <option value="fa/language.operators.assignment.php">Persian</option>
    <option value="es/language.operators.assignment.php">Spanish</option>
    <option value="tr/language.operators.assignment.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="language.operators.assignment" class="sect1">
   <h2 class="title">Assignment Operators</h2>
   <p class="simpara">
    The basic assignment operator is &quot;=&quot;. Your first inclination might
    be to think of this as &quot;equal to&quot;. Don&#039;t. It really means that the
    left operand gets set to the value of the expression on the
    right (that is, &quot;gets set to&quot;).
   </p>
   <p class="para">
    The value of an assignment expression is the value assigned. That
    is, the value of &quot;<em>$a = 3</em>&quot; is 3. This allows you to do some tricky
    things:
    <div class="informalexample">
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /><br />$a&nbsp;</span><span style="color: #007700">=&nbsp;(</span><span style="color: #0000BB">$b&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">4</span><span style="color: #007700">)&nbsp;+&nbsp;</span><span style="color: #0000BB">5</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;$a&nbsp;is&nbsp;equal&nbsp;to&nbsp;9&nbsp;now,&nbsp;and&nbsp;$b&nbsp;has&nbsp;been&nbsp;set&nbsp;to&nbsp;4.<br /><br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

    </div>
   </p>
   <p class="para">
    For <span class="type"><span class="type arrays">arrays</span></span>, assigning a value to a named key is performed using
    the &quot;=&gt;&quot; operator. The <a href="language.operators.precedence.php" class="link">precedence</a>
    of this operator is the same as other assignment operators.
   </p>
   <p class="para">
    In addition to the basic assignment operator, there are &quot;combined
    operators&quot; for all of the <a href="language.operators.php" class="link">binary
    arithmetic</a>, array union and string operators that allow you to use a value in an
    expression and then set its value to the result of that expression. For
    example:
    <div class="informalexample">
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /><br />$a&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">3</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">+=&nbsp;</span><span style="color: #0000BB">5</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;sets&nbsp;$a&nbsp;to&nbsp;8,&nbsp;as&nbsp;if&nbsp;we&nbsp;had&nbsp;said:&nbsp;$a&nbsp;=&nbsp;$a&nbsp;+&nbsp;5;<br /></span><span style="color: #0000BB">$b&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">"Hello&nbsp;"</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">$b&nbsp;</span><span style="color: #007700">.=&nbsp;</span><span style="color: #DD0000">"There!"</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;sets&nbsp;$b&nbsp;to&nbsp;"Hello&nbsp;There!",&nbsp;just&nbsp;like&nbsp;$b&nbsp;=&nbsp;$b&nbsp;.&nbsp;"There!";<br /><br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

    </div>
   </p>
   <p class="para">
    Note that the assignment copies the original variable to the new
    one (assignment by value), so changes to one will not affect the
    other. This may also have relevance if you need to copy something
    like a large array inside a tight loop.
   </p>
   <p class="para">
    An exception to the usual assignment by value behaviour within PHP occurs
    with <span class="type"><a href="language.types.object.php" class="type object">object</a></span>s, which are assigned by reference in PHP 5.
    Objects may be explicitly copied via the <a href="language.oop5.cloning.php" class="link">clone</a> keyword.
   </p>

   <div class="sect2" id="language.operators.assignment.reference">
    <h3 class="title">Assignment by Reference</h3>
    <p class="para">
     Assignment by reference is also supported, using the
     &quot;<span class="computeroutput">$var = &amp;$othervar;</span>&quot; syntax.
     Assignment by reference means that both variables end up pointing at the
     same data, and nothing is copied anywhere.
    </p>
    <p class="para">
     <div class="example" id="example-114">
      <p><strong>Example #1 Assigning by reference</strong></p>
      <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br />$a&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">3</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">$b&nbsp;</span><span style="color: #007700">=&nbsp;&amp;</span><span style="color: #0000BB">$a</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;$b&nbsp;is&nbsp;a&nbsp;reference&nbsp;to&nbsp;$a<br /><br /></span><span style="color: #007700">print&nbsp;</span><span style="color: #DD0000">"</span><span style="color: #0000BB">$a</span><span style="color: #DD0000">\n"</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;prints&nbsp;3<br /></span><span style="color: #007700">print&nbsp;</span><span style="color: #DD0000">"</span><span style="color: #0000BB">$b</span><span style="color: #DD0000">\n"</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;prints&nbsp;3<br /><br /></span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">4</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;change&nbsp;$a<br /><br /></span><span style="color: #007700">print&nbsp;</span><span style="color: #DD0000">"</span><span style="color: #0000BB">$a</span><span style="color: #DD0000">\n"</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;prints&nbsp;4<br /></span><span style="color: #007700">print&nbsp;</span><span style="color: #DD0000">"</span><span style="color: #0000BB">$b</span><span style="color: #DD0000">\n"</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;prints&nbsp;4&nbsp;as&nbsp;well,&nbsp;since&nbsp;$b&nbsp;is&nbsp;a&nbsp;reference&nbsp;to&nbsp;$a,&nbsp;which&nbsp;has<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;//&nbsp;been&nbsp;changed<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
      </div>

     </div>
    </p>
    <p class="para">
     As of PHP 5, the <a href="language.oop5.basic.php#language.oop5.basic.new" class="link">new</a>
     operator returns a reference automatically, so assigning the result of
     <a href="language.oop5.basic.php#language.oop5.basic.new" class="link">new</a> by reference results
     in an <strong><code>E_DEPRECATED</code></strong> message in PHP 5.3 and later, and
     an <strong><code>E_STRICT</code></strong> message in earlier versions.
    </p>
    <p class="para">
     For example, this code will result in a warning:
     <div class="informalexample">
      <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">class&nbsp;</span><span style="color: #0000BB">C&nbsp;</span><span style="color: #007700">{}<br /><br /></span><span style="color: #FF8000">/*&nbsp;The&nbsp;following&nbsp;line&nbsp;generates&nbsp;the&nbsp;following&nbsp;error&nbsp;message:<br />&nbsp;*&nbsp;Deprecated:&nbsp;Assigning&nbsp;the&nbsp;return&nbsp;value&nbsp;of&nbsp;new&nbsp;by&nbsp;reference&nbsp;is&nbsp;deprecated&nbsp;in...<br />&nbsp;*/<br /></span><span style="color: #0000BB">$o&nbsp;</span><span style="color: #007700">=&nbsp;&amp;new&nbsp;</span><span style="color: #0000BB">C</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
      </div>

     </div>
    </p>
    <p class="para">
     More information on references and their potential uses can be found in
     the <a href="language.references.php" class="link">References Explained</a>
     section of the manual.
    </p>
   </div>
  </div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="language.operators.bitwise.php">Bitwise Operators<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.operators.arithmetic.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Arithmetic Operators</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.operators.assignment.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=language.operators.assignment&amp;redirect=@w{99AQHN5V}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.operators.assignment&amp;redirect=@w{99AQHN5V}">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Assignment Operators</strong>
 </div><div id="allnotes">
 <a name="105622"></a>
 <div class="note">
  <strong class='user'>haubertj at alfredstate dot edu</strong>
  <a href="#105622" class="date">01-Sep-2011 07:19</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
[[&nbsp;&nbsp; Editor's note: You are much better off using the foreach (array_expression as $key =&gt; $value) control structure in this case&nbsp;&nbsp; ]]<br />
<br />
When using <br />
<br />
&lt;php<br />
while ($var = current($array) {<br />
#do stuff<br />
next($aray)<br />
?&gt;<br />
<br />
to process an array, if current($array) happens to be falsy but not === false it will still end the loop.&nbsp; In such a case strict typing must be used.<br />
<br />
Like this:<br />
<br />
&lt;php<br />
while (($var = current($array)) !== FALSE) {<br />
#do stuff<br />
next($aray)<br />
?&gt;<br />
<br />
Of course if your array may contain actual FALSE values you will have to deal with those some other way.</span>
</code></div>
  </div>
 </div>
 <a name="102385"></a>
 <div class="note">
  <strong class='user'>Peter, Moscow</strong>
  <a href="#102385" class="date">11-Feb-2011 01:44</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Using $text .= "additional text"; instead of $text =&nbsp; $text ."additional text"; can seriously enhance performance due to memory allocation efficiency. <br />
<br />
I reduced execution time from 5 sec to .5 sec (10 times) by simply switching to the first pattern for a loop with 900 iterations over a string $text that reaches 800K by the end.</span>
</code></div>
  </div>
 </div>
 <a name="80899"></a>
 <div class="note">
  <strong class='user'>Hayley Watson</strong>
  <a href="#80899" class="date">05-Feb-2008 05:54</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
You could also take adam at gmail dot com's xor-assignment operator and use the fact that it's right-associative:<br />
<br />
$a ^= $b ^= $a ^= $b;</span>
</code></div>
  </div>
 </div>
 <a name="78345"></a>
 <div class="note">
  <strong class='user'>Hayley Watson</strong>
  <a href="#78345" class="date">07-Oct-2007 03:22</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
bradlis7 at bradlis7 dot com's description is a bit confusing. Here it is rephrased.<br />
<br />
<span class="default">&lt;?php<br />
$a </span><span class="keyword">= </span><span class="string">'a'</span><span class="keyword">;<br />
</span><span class="default">$b </span><span class="keyword">= </span><span class="string">'b'</span><span class="keyword">;<br />
<br />
</span><span class="default">$a </span><span class="keyword">.= </span><span class="default">$b </span><span class="keyword">.= </span><span class="string">"foo"</span><span class="keyword">;<br />
<br />
echo </span><span class="default">$a</span><span class="keyword">,</span><span class="string">"\n"</span><span class="keyword">,</span><span class="default">$b</span><span class="keyword">;</span><span class="default">?&gt;<br />
</span>outputs<br />
<br />
abfoo<br />
bfoo<br />
<br />
Because the assignment operators are right-associative and evaluate to the result of the assignment<br />
<span class="default">&lt;?php<br />
$a </span><span class="keyword">.= </span><span class="default">$b </span><span class="keyword">.= </span><span class="string">"foo"</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span>is equivalent to<br />
<span class="default">&lt;?php<br />
$a </span><span class="keyword">.= (</span><span class="default">$b </span><span class="keyword">.= </span><span class="string">"foo"</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span>and therefore<br />
<span class="default">&lt;?php<br />
$b </span><span class="keyword">.= </span><span class="string">"foo"</span><span class="keyword">;<br />
</span><span class="default">$a </span><span class="keyword">.= </span><span class="default">$b</span><span class="keyword">;<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="69182"></a>
 <div class="note">
  <strong class='user'>adam at gmail dot com</strong>
  <a href="#69182" class="date">25-Aug-2006 10:38</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
or you could use the xor-assignment operator..<br />
$a ^= $b;<br />
$b ^= $a;<br />
$a ^= $b;</span>
</code></div>
  </div>
 </div>
 <a name="55848"></a>
 <div class="note">
  <strong class='user'>bradlis7 at bradlis7 dot com</strong>
  <a href="#55848" class="date">15-Aug-2005 08:13</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Note whenever you do this<br />
<br />
<span class="default">&lt;?php<br />
$a </span><span class="keyword">.= </span><span class="default">$b </span><span class="keyword">.= </span><span class="string">"bla bla"</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
it comes out to be the same as the following:<br />
<br />
<span class="default">&lt;?php<br />
$a </span><span class="keyword">.= </span><span class="default">$b</span><span class="keyword">.</span><span class="string">"bla bla"</span><span class="keyword">;<br />
</span><span class="default">$b </span><span class="keyword">.= </span><span class="string">"bla bla"</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
So $a actually becomes $a and the final $b string. I'm sure it's the same with numerical assignments (+=, *=...).</span>
</code></div>
  </div>
 </div>
 <a name="40084"></a>
 <div class="note">
  <strong class='user'>straz at mac dot nospam dot com</strong>
  <a href="#40084" class="date">20-Feb-2004 10:18</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
This page really ought to have table of assignment operators,<br />
namely,<br />
<br />
See the Arithmetic Operators page (<a href="@w{372C46DE}" rel="nofollow" target="_blank">@w{372C46DE}</a>)<br />
Assignment&nbsp; &nbsp; Same as:<br />
$a += $b&nbsp; &nbsp;&nbsp; $a = $a + $b&nbsp; &nbsp; Addition<br />
$a -= $b&nbsp; &nbsp;&nbsp; $a = $a - $b&nbsp; &nbsp;&nbsp; Subtraction<br />
$a *= $b&nbsp; &nbsp;&nbsp; $a = $a * $b&nbsp; &nbsp;&nbsp; Multiplication<br />
$a /= $b&nbsp; &nbsp;&nbsp; $a = $a / $b&nbsp; &nbsp; Division<br />
$a %= $b&nbsp; &nbsp;&nbsp; $a = $a % $b&nbsp; &nbsp; Modulus<br />
<br />
See the String Operators page(<a href="@w{GYKCBYFH}" rel="nofollow" target="_blank">@w{GYKCBYFH}</a>)<br />
$a .= $b&nbsp; &nbsp;&nbsp; $a = $a . $b&nbsp; &nbsp; &nbsp;&nbsp; Concatenate<br />
<br />
See the Bitwise Operators page (<a href="@w{5B3FC3DK}" rel="nofollow" target="_blank">@w{5B3FC3DK}</a>)<br />
$a &amp;= $b&nbsp; &nbsp;&nbsp; $a = $a &amp; $b&nbsp; &nbsp;&nbsp; Bitwise And<br />
$a |= $b&nbsp; &nbsp;&nbsp; $a = $a | $b&nbsp; &nbsp; &nbsp; Bitwise Or<br />
$a ^= $b&nbsp; &nbsp;&nbsp; $a = $a ^ $b&nbsp; &nbsp; &nbsp;&nbsp; Bitwise Xor<br />
$a &lt;&lt;= $b&nbsp; &nbsp;&nbsp; $a = $a &lt;&lt; $b&nbsp; &nbsp;&nbsp; Left shift<br />
$a &gt;&gt;= $b&nbsp; &nbsp;&nbsp; $a = $a &gt;&gt; $b&nbsp; &nbsp; &nbsp; Right shift</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=language.operators.assignment&amp;redirect=@w{99AQHN5V}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.operators.assignment&amp;redirect=@w{99AQHN5V}">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/language.operators.assignment.php">show source</a> |
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