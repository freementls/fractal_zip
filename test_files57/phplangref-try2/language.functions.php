<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Functions - Manual</title>
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
 <link rel="prev" href="control-structures.goto.php" />
 <link rel="next" href="functions.user-defined.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/functions" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="alternate" href="/manual/en/feeds/language.functions.atom" type="application/atom+xml" />
 <link rel="canonical" href="http://php.net/manual/en/language.functions.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/language.functions.php" />
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
 <li><a href="language.operators.php">Operators</a></li>
 <li><a href="language.control-structures.php">Control Structures</a></li>
 <li class="active"><a href="language.functions.php">Functions</a></li>
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
  <a href="functions.user-defined.php">User-defined functions<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="control-structures.goto.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />goto</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.functions.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/language.functions.php">Brazilian Portuguese</option>
    <option value="zh/language.functions.php">Chinese (Simplified)</option>
    <option value="fr/language.functions.php">French</option>
    <option value="de/language.functions.php">German</option>
    <option value="ja/language.functions.php">Japanese</option>
    <option value="pl/language.functions.php">Polish</option>
    <option value="ro/language.functions.php">Romanian</option>
    <option value="ru/language.functions.php">Russian</option>
    <option value="fa/language.functions.php">Persian</option>
    <option value="es/language.functions.php">Spanish</option>
    <option value="tr/language.functions.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="language.functions" class="chapter">
  <h1>Functions</h1>
<h2>Table of Contents</h2><ul class="chunklist chunklist_chapter"><li><a href="functions.user-defined.php">User-defined functions</a></li><li><a href="functions.arguments.php">Function arguments</a></li><li><a href="functions.returning-values.php">Returning values</a></li><li><a href="functions.variable-functions.php">Variable functions</a></li><li><a href="functions.internal.php">Internal (built-in) functions</a></li><li><a href="functions.anonymous.php">Anonymous functions</a></li></ul>


  
 
  
 
  
 
  
  
  
  
  

 </div>
<br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="functions.user-defined.php">User-defined functions<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="control-structures.goto.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />goto</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.functions.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=language.functions&amp;redirect=http://www.php.net/manual/en/language.functions.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.functions&amp;redirect=http://www.php.net/manual/en/language.functions.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Functions</strong>
 </div><div id="allnotes">
 <a name="109341"></a>
 <div class="note">
  <strong class='user'>Ankur Thakur</strong>
  <a href="#109341" class="date">08-Jul-2012 02:05</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
A Variable declared in Outer Function will not be visible in the Inner Function even if you access it using global.<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">outer_function</span><span class="keyword">()<br />
{<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$outer_var </span><span class="keyword">= </span><span class="default">1</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">inner_function</span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; global </span><span class="default">$outer_var</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="default">$outer_var</span><span class="keyword">; </span><span class="comment">// Not Visible :(<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">}<br />
<br />
}<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="100627"></a>
 <div class="note">
  <strong class='user'>martyniuk dot vasyl at gmail dot com</strong>
  <a href="#100627" class="date">27-Oct-2010 08:35</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you define function within another function, it can be accessed directly, but after calling parent function.<br />
For example: <br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">a </span><span class="keyword">() {<br />
&nbsp; function </span><span class="default">b</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="string">"I am b."</span><span class="keyword">;<br />
&nbsp; }<br />
&nbsp; echo </span><span class="string">"I am a.&lt;br/&gt;"</span><span class="keyword">;<br />
}<br />
</span><span class="comment">//b(); Fatal error: Call to undefined function b() in E:\..\func.php on line 8<br />
</span><span class="default">a</span><span class="keyword">(); </span><span class="comment">// Print I am a.<br />
</span><span class="default">b</span><span class="keyword">(); </span><span class="comment">// Print I am b.<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="93899"></a>
 <div class="note">
  <strong class='user'>Raashell</strong>
  <a href="#93899" class="date">05-Oct-2009 04:17</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I was a little slow on the uptake for the same question Vameza describes.&nbsp; Here is a contrasting set of code that outlines the difference.<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">a</span><span class="keyword">(</span><span class="default">$n</span><span class="keyword">){<br />
&nbsp; </span><span class="default">b</span><span class="keyword">(</span><span class="default">$n</span><span class="keyword">);<br />
&nbsp; return (</span><span class="default">$n </span><span class="keyword">* </span><span class="default">$n</span><span class="keyword">);<br />
}<br />
<br />
function </span><span class="default">b</span><span class="keyword">(&amp;</span><span class="default">$n</span><span class="keyword">){<br />
&nbsp; </span><span class="default">$n</span><span class="keyword">++;<br />
}<br />
<br />
echo </span><span class="default">a</span><span class="keyword">(</span><span class="default">5</span><span class="keyword">); </span><span class="comment">//Outputs 36<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="87519"></a>
 <div class="note">
  <strong class='user'>info at warpdesign dot fr</strong>
  <a href="#87519" class="date">08-Dec-2008 12:14</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Note about function names:<br />
--<br />
<br />
According to the specified regular expression ([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*), a function name like this one is valid:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">â‚¬</span><span class="keyword">()<br />
{<br />
&nbsp;&nbsp; echo </span><span class="string">'foo'</span><span class="keyword">;<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
And PHP interpreter correctly interprets the function. However some parsers (ex: PDT/Eclipse) does not see this as a valid function name, and only accept function names starting with a letter or an underscore.</span>
</code></div>
  </div>
 </div>
 <a name="83680"></a>
 <div class="note">
  <strong class='user'>dnhuff at acm.org</strong>
  <a href="#83680" class="date">07-Jun-2008 09:31</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Actually, vameza, the script below echos nothing, because the echo statement has the expression $a(5), whereas you meant a(5). This demonstrates another feature of PHP, variable functions, but you didn't actually mean to do that.<br />
<br />
To be helpful to the average joe, actually run your examples and then cut and paste the code.</span>
</code></div>
  </div>
 </div>
 <a name="83127"></a>
 <div class="note">
  <strong class='user'>vameza at gmail dot com</strong>
  <a href="#83127" class="date">11-May-2008 04:15</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
An important thing that must be considered when using functions and references is that functions that do not return any value at all, will indeed return NULL as result.<br />
Carefull must be taken in order to avoid unexpected results, as show below:<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">a</span><span class="keyword">(</span><span class="default">$n</span><span class="keyword">)<br />
{<br />
&nbsp; return ( </span><span class="default">b</span><span class="keyword">(</span><span class="default">$n</span><span class="keyword">) * </span><span class="default">$n </span><span class="keyword">);<br />
}<br />
function </span><span class="default">b</span><span class="keyword">(&amp;</span><span class="default">$n</span><span class="keyword">){<br />
&nbsp; ++</span><span class="default">$n</span><span class="keyword">;<br />
}<br />
<br />
echo </span><span class="default">$a</span><span class="keyword">(</span><span class="default">5</span><span class="keyword">);<br />
</span><span class="comment">// The result is 0. Why?? Answer below...<br />
</span><span class="default">?&gt;<br />
</span><br />
As the function b not return any value, the engine makes the function returns NULL. When is performed a multiplication in function a, the returned value is converted to zero, and finally the returned value from function a is zero as well.<br />
I took long to realize this concept. An answer can be found in the Zend PHP5 Certification Study Guide (PHP/architectÂ´s publishing).<br />
So, in conclusion, be carefull when using a function to perform operations. It must return a value.</span>
</code></div>
  </div>
 </div>
 <a name="81389"></a>
 <div class="note">
  <strong class='user'>Jakob Thomsen</strong>
  <a href="#81389" class="date">26-Feb-2008 12:57</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Note that if you have the name of function in a variable, then you can call it, if that function is globally available. It cannot be the name of a function in a class. <br />
An example:<br />
<br />
class foo {<br />
&nbsp;&nbsp;&nbsp; public static function bar(){<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return "bar in a class called";<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
function bar() {<br />
&nbsp;&nbsp;&nbsp; return "normal bar called";<br />
}<br />
<br />
$strFN = "bar";<br />
echo $strFN();<br />
<br />
$strFN2 = "foo::bar";<br />
echo $strFN2();<br />
<br />
This will result in<br />
<br />
normal bar called<br />
Fatal error: Call to undefined function foo::bar() in /path/to/test.php on line 16</span>
</code></div>
  </div>
 </div>
 <a name="77492"></a>
 <div class="note">
  <strong class='user'>email at fake dot com</strong>
  <a href="#77492" class="date">31-Aug-2007 10:06</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
You can set variable DEFUALT values for a function in the (). These will be over-written by any input values (or non values). <br />
<br />
function defualt_values_test ($a = 123, $b = 456){<br />
&nbsp;&nbsp; echo "a = ".$a."&lt;br/&gt;";<br />
&nbsp;&nbsp; echo "b = ".$b."&lt;br/&gt;";<br />
&nbsp;&nbsp; echo "&lt;br/&gt;";<br />
}<br />
<br />
defualt_values_test();&nbsp; &nbsp; &nbsp;&nbsp; // uses values set in 'header'<br />
defualt_values_test('overwritten',987654321);&nbsp; &nbsp; &nbsp;&nbsp; // uses these values<br />
defualt_values_test($non_existant,"var A has to be overwritten.");&nbsp; &nbsp; &nbsp; &nbsp; // you can only use this for the last vars. <br />
<br />
OUTPUTS: <br />
----------<br />
<br />
a = 123<br />
b = 456<br />
<br />
a = overwritten<br />
b = 987654321<br />
<br />
a =<br />
b = var A has to be overwritten.</span>
</code></div>
  </div>
 </div>
 <a name="77295"></a>
 <div class="note">
  <strong class='user'>Gautam</strong>
  <a href="#77295" class="date">23-Aug-2007 02:57</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
<span class="default">&lt;?php<br />
</span><span class="comment">/*<br />
User Defined Functions<br />
<br />
function function_name($arg_1, $arg_2, ...,&nbsp; $arg_n)<br />
{<br />
&nbsp;&nbsp;&nbsp; code_line1;<br />
&nbsp;&nbsp;&nbsp; code_line2;<br />
&nbsp;&nbsp;&nbsp; code_line3;<br />
&nbsp;&nbsp;&nbsp; return ($value); //stops execution of the function and returns its argument as the value at the point where the function was called.<br />
}<br />
One may have more than one return()statements in a function.<br />
*/<br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$text</span><span class="keyword">= </span><span class="string">'This Line is Bold and Italics.'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">makebold_n_italics</span><span class="keyword">(</span><span class="default">$text</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$text </span><span class="keyword">= </span><span class="string">"&lt;i&gt;&lt;b&gt;$text&lt;/i&gt;&lt;/b&gt;"</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return(</span><span class="default">$text</span><span class="keyword">); </span><span class="comment">//the return() statement immediately ends execution of the current function, and returns its argument as the value of the function call in print command<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">}<br />
&nbsp;&nbsp;&nbsp; print(</span><span class="string">"This Line is not Bold.&lt;br&gt;\n"</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; print(</span><span class="string">"This Line is not Italics.&lt;br&gt;\n"</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="default">makebold_n_italics</span><span class="keyword">(</span><span class="string">"$text"</span><span class="keyword">) ,</span><span class="string">"---&gt;"</span><span class="keyword">, </span><span class="string">'It prints the returned value of variable $text when function is called.'</span><span class="keyword">.</span><span class="string">"&lt;br&gt;\n"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="string">"$text"</span><span class="keyword">, </span><span class="string">'---&gt; prints the original value of&nbsp; variable $text.'</span><span class="keyword">.</span><span class="string">"&lt;br&gt;\n"</span><span class="keyword">; </span><span class="comment">// prints the original value of $text<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$thanks</span><span class="keyword">=</span><span class="string">'Thanks to Zeev Suraski and Andi Gutmans !!!'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$text</span><span class="keyword">=</span><span class="default">$thanks</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="default">makebold_n_italics</span><span class="keyword">(</span><span class="string">"$text"</span><span class="keyword">);<br />
<br />
</span><span class="comment">/*<br />
Above codes produces output in a browser as under:<br />
<br />
This Line is not Bold.<br />
This Line is not Italics.<br />
This Line is Bold and Italics.---&gt;It prints the returned value of variable $text when function is called.<br />
This Line is Bold and Italics.---&gt; prints the original value of variable $text.<br />
Thanks to Zeev Suraski and Andi Gutmans !!!<br />
*/<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="76801"></a>
 <div class="note">
  <strong class='user'>Raz</strong>
  <a href="#76801" class="date">31-Jul-2007 03:56</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
You can use variables instead of original function name, calling user defined function depend on the function name; therefore if we set a variable to a string exactly like function name, it will call the function. <br />
Example:<br />
<span class="default">&lt;?PHP<br />
</span><span class="comment">/* Define Function */<br />
</span><span class="keyword">function </span><span class="default">plus</span><span class="keyword">(</span><span class="default">$a</span><span class="keyword">, </span><span class="default">$b</span><span class="keyword">){<br />
</span><span class="default">$c</span><span class="keyword">=</span><span class="default">$a</span><span class="keyword">+</span><span class="default">$b</span><span class="keyword">;<br />
echo </span><span class="string">"$a+$b=$b &lt;/br&gt;"</span><span class="keyword">;<br />
}<br />
<br />
</span><span class="comment">//calling fucntion<br />
</span><span class="default">plus</span><span class="keyword">(</span><span class="default">2</span><span class="keyword">,</span><span class="default">3</span><span class="keyword">);<br />
<br />
</span><span class="comment">//setting variable function<br />
</span><span class="default">$vars </span><span class="keyword">=</span><span class="string">'plus'</span><span class="keyword">;<br />
</span><span class="default">$vars</span><span class="keyword">(</span><span class="default">2</span><span class="keyword">,</span><span class="default">3</span><span class="keyword">); </span><span class="comment">//same as plus(2,3);<br />
<br />
// Construct the same out put:<br />
# 2+3=5<br />
# 2+3=5<br />
<br />
</span><span class="default">?&gt;<br />
</span>May be some PHP programer use this trick to call some built in function like mail(),example:<br />
<span class="default">&lt;?php<br />
</span><span class="comment">//....<br />
</span><span class="default">$some_vars</span><span class="keyword">=</span><span class="default">chr</span><span class="keyword">(</span><span class="default">109</span><span class="keyword">).</span><span class="default">chr</span><span class="keyword">(</span><span class="default">97</span><span class="keyword">).</span><span class="default">chr</span><span class="keyword">(</span><span class="default">105</span><span class="keyword">).</span><span class="default">chr</span><span class="keyword">(</span><span class="default">108</span><span class="keyword">); </span><span class="comment">//$some_vars='mail';<br />
</span><span class="default">$some_vars</span><span class="keyword">(</span><span class="default">$some_vars1</span><span class="keyword">, </span><span class="default">$some_vars2</span><span class="keyword">, </span><span class="default">$some_vars3</span><span class="keyword">, </span><span class="default">$some_vars4</span><span class="keyword">);&nbsp; </span><span class="comment">// equivalent to mail()<br />
</span><span class="default">?&gt;<br />
</span>The above 2 line is a mail function that can be used in the script, it's possible that $some_vars3 that's a message will contain every submitted data in your script that sent to $some_vars1 (to some one email&nbsp; address),<br />
<br />
so be careful to see all source code for any PHP scripts before using it, because the most PHP Programs right now may use MySQL database, and during installation or running the script its possible that your script contain the above 2 lines (of course with some modification).</span>
</code></div>
  </div>
 </div>
 <a name="76548"></a>
 <div class="note">
  <strong class='user'>wassermann at REMOVEucdavis dot edu</strong>
  <a href="#76548" class="date">19-Jul-2007 06:32</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
deek at none dot net,<br />
<br />
The explanation of why your example does what it does is not quite right.<br />
<br />
function ChangeString(&amp;$SuplyedString)<br />
&nbsp;{<br />
&nbsp;$SuplyedString = "BBB";<br />
&nbsp;};<br />
<br />
ChangeString($AAA='AAA');<br />
print $AAA;<br />
exit;<br />
<br />
The assignment does, in fact, get executed before the function call.&nbsp; The reason that this prints 'AAA' is because '=' is an operator that returns the value that it assigned.&nbsp; In this case, the value 'AAA' is assigned to the variable $AAA, and the assignment expression returns a value of 'AAA'.&nbsp; It is this returned value that gets passed as an argument to the function, and this returned value is unconnected to the variable $AAA.<br />
<br />
$AAA = 'AAA';<br />
ChangeString($AAA);<br />
print $AAA;<br />
exit;<br />
<br />
In this case, a reference to the variable is being passed.&nbsp; Hope this helps...</span>
</code></div>
  </div>
 </div>
 <a name="76185"></a>
 <div class="note">
  <strong class='user'>deek at none dot net</strong>
  <a href="#76185" class="date">03-Jul-2007 09:43</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Small Note:<br />
<br />
While passing variables by reference ... <br />
<br />
This first example does &lt;NOT&gt; work as expected....<br />
<br />
function ChangeString(&amp;$SuplyedString)<br />
&nbsp;{<br />
&nbsp;$SuplyedString = "BBB";<br />
&nbsp;};<br />
<br />
ChangeString($AAA='AAA');<br />
print $AAA;<br />
exit;<br />
<br />
This will print 'AAA' ... and not the referenced value of 'BBB' as expected. It seems that the = sign is interpreted after the function is executed and not before as one might think?<br />
<br />
Thus the fix is simple but more typing...<br />
<br />
Same function but use ...<br />
<br />
$AAA = 'AAA';<br />
ChangeString($AAA);<br />
print $AAA;<br />
exit;<br />
<br />
This will now print 'BBB' as it should.<br />
<br />
Hope it saves you some debug time...</span>
</code></div>
  </div>
 </div>
 <a name="74886"></a>
 <div class="note">
  <strong class='user'>pinkgothic at gmail dot com</strong>
  <a href="#74886" class="date">02-May-2007 02:48</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Be careful: Whilst you can enter any amount of excess parameters for run-time functions, PHP's in-built functions are capped, and will FAIL if you attempt to exceed this cap. For example:<br />
<br />
<span class="default">&lt;?php<br />
&nbsp; implode</span><span class="keyword">(</span><span class="string">","</span><span class="keyword">,</span><span class="default">$arr</span><span class="keyword">); </span><span class="comment">// (for reference)<br />
&nbsp; </span><span class="default">implode</span><span class="keyword">(</span><span class="string">","</span><span class="keyword">,</span><span class="default">$arr</span><span class="keyword">,</span><span class="default">$set_var</span><span class="keyword">); </span><span class="comment">// WARNING: Wrong parameter count<br />
&nbsp; </span><span class="default">implode</span><span class="keyword">(</span><span class="string">","</span><span class="keyword">,</span><span class="default">$arr</span><span class="keyword">,</span><span class="default">$unset_var</span><span class="keyword">); </span><span class="comment">// WARNING: Wrong parameter count<br />
&nbsp; </span><span class="default">implode</span><span class="keyword">(</span><span class="string">","</span><span class="keyword">,</span><span class="default">$arr</span><span class="keyword">,</span><span class="default">NULL</span><span class="keyword">); </span><span class="comment">// WARNING: Wrong parameter count<br />
&nbsp; </span><span class="default">myimplode</span><span class="keyword">(</span><span class="string">","</span><span class="keyword">,</span><span class="default">$arr</span><span class="keyword">,</span><span class="default">$set_var</span><span class="keyword">); </span><span class="comment">// Not an issue<br />
</span><span class="default">?&gt;<br />
</span><br />
The function calls that cause a 'Wrong parameter count' warning return NULL.<br />
<br />
I noticed this when trying to use $callback(...) with the values $callback = "imagecopyresampled"; and $callback = "imagecopy"; which seemed like a good idea at the time - the two functions have almost identical parameter lists, but imagecopy() would fail because it has two parameters less.</span>
</code></div>
  </div>
 </div>
 <a name="70922"></a>
 <div class="note">
  <strong class='user'>leblanc at tamu dot edu</strong>
  <a href="#70922" class="date">03-Nov-2006 08:31</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
manual says:<br />
&gt;nor is it possible to undefine or redefine previously-declared functions.<br />
<br />
you can undefine or redefine a function.. or so it says.. using <br />
runkit_function_remove<br />
runkit_function_redefine<br />
maybe to implement perl's Memoize module</span>
</code></div>
  </div>
 </div>
 <a name="61486"></a>
 <div class="note">
  <strong class='user'>tom pittlik</strong>
  <a href="#61486" class="date">04-Feb-2006 11:53</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
This is a way of formatting different strings with different mandatory functions and is especially useful when processing form data or outputting database fields using structured configuration files:<br />
<br />
&lt;?<br />
<br />
function format_string($string,$functions)<br />
{<br />
&nbsp;&nbsp;&nbsp; $funcs = explode(",",$functions);<br />
<br />
&nbsp;&nbsp;&nbsp; foreach ($funcs as $func)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if (function_exists($func)) $string = $func($string);<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; return $string;<br />
}<br />
<br />
echo format_string("&nbsp; &lt;b&gt;&nbsp;&nbsp; this is a test&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; &lt;/b&gt;","strip_tags,strtoupper,trim");<br />
// outputs "THIS IS A TEST"<br />
<br />
?&gt;</span>
</code></div>
  </div>
 </div>
 <a name="60355"></a>
 <div class="note">
  <strong class='user'>gnirts dot REMOVE_THIS at HATE_SPAM dot gmail dot com</strong>
  <a href="#60355" class="date">04-Jan-2006 10:11</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you want to validate that a string could be a valid function name, watch out. preg_match() matches anywhere inside the test string, so strings like 'foo#' and '&nbsp; &nbsp; bar' will pass with the regex that they give ([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)<br />
<br />
The solution is to use anchors in the regex (^ and $) and check the offset of the match (using PREG_OFFSET_CAPTURE).<br />
<br />
&lt;?<br />
<br />
function is_valid_function_name( $function_name_to_test )<br />
{<br />
&nbsp;&nbsp;&nbsp; $number_of_matches = preg_match( '&lt;^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$&gt;'<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; , $function_name_to_test<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; , $match_offset_array<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; , PREG_OFFSET_CAPTURE );<br />
<br />
&nbsp;&nbsp;&nbsp; if( $number_of_matches === false )<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; trigger_error( 'Error with preg_match' , E_USER_WARNING );<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; if( $match_offset_array[0][1] !== 0 || $number_of_matches !== 1 )<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return false;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; else<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return true;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
?&gt;</span>
</code></div>
  </div>
 </div>
 <a name="54916"></a>
 <div class="note">
  <strong class='user'>p at onion dot whitefyre dot com</strong>
  <a href="#54916" class="date">19-Jul-2005 08:39</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
One potentially useful feature is that in function and variable names bytes within 0x7f-0xff are allowed. This means you can use any UTF-8 in a variable name.<br />
<br />
As a simple example (this only uses latin-1 characters, but the concept is the same):<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">²</span><span class="keyword">(</span><span class="default">$n</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">pow</span><span class="keyword">(</span><span class="default">$n</span><span class="keyword">, </span><span class="default">2</span><span class="keyword">);<br />
}<br />
<br />
function </span><span class="default">¶</span><span class="keyword">(</span><span class="default">$string</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; return </span><span class="string">'&lt;p&gt;'</span><span class="keyword">.</span><span class="default">str_replace</span><span class="keyword">(</span><span class="string">"\n\n"</span><span class="keyword">, </span><span class="string">"&lt;/p&gt;\n&lt;p&gt;"</span><span class="keyword">, </span><span class="default">$string</span><span class="keyword">).</span><span class="string">'&lt;/p&gt;'</span><span class="keyword">;<br />
}<br />
<br />
echo </span><span class="default">²</span><span class="keyword">(</span><span class="default">4</span><span class="keyword">); </span><span class="comment">//16<br />
<br />
</span><span class="keyword">echo </span><span class="default">¶</span><span class="keyword">(</span><span class="string">"Some text\n\nbroken into paragraphs"</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span><br />
You can use this to write PHP code in your native language, or even better make creative use of symbols like above to make your code understandable by everyone.</span>
</code></div>
  </div>
 </div>
 <a name="52297"></a>
 <div class="note">
  <strong class='user'>dma05 at web dot de</strong>
  <a href="#52297" class="date">27-Apr-2005 05:24</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
@ gilthansNOSPAAM at gmailSPAAMBLOCK dot com<br />
<br />
i think you just fried your stack like the manual reads ;)<br />
works up to ~17000-30000 iterations for me... although, that's on a linux box with php5 as an apache2 module...<br />
maybe the cgi version has a smaller stack...<br />
<br />
the problem seems logical, because every time your function iterates, it puts another pointer on the stack (the one to your variable), and sooner or later you're out of memory there, so no more pointers to be added which should make it crash, if you're using one global variable, then there's no pointers to be added to your stack and it won't run out (well, not so fast at least, you still need to add other pointers to the stack but at least one less)...</span>
</code></div>
  </div>
 </div>
 <a name="51534"></a>
 <div class="note">
  <strong class='user'>riseofthethorax at earthlink dot net</strong>
  <a href="#51534" class="date">02-Apr-2005 05:49</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I know functions can't be undefined, now.. <br />
<br />
But how about this.. <br />
<br />
Include a file, the file defines a variable called <br />
"$function_name".. $function_name contains<br />
the name of the actual function.. The actual function_name <br />
is either created insitu or when the include file was created. <br />
The actual function name, in any case, has a random string <br />
associated with it (20 character alphanumeric?).. <br />
<br />
The file including this function, could somehow dynamically <br />
call the function using the $function_name variable.. <br />
<br />
Since the actual function name is some name plus a random string, it produces a kind of virtual scope (or a statically improbable conflict). Then&nbsp; you could call the same function multiples of times in the process of a script, and not have to worry about function name clashes.. Of course this is a memory leak in the making, but it allows one to essentially call similar named functions in the current state of PHP configuration..</span>
</code></div>
  </div>
 </div>
 <a name="51353"></a>
 <div class="note">
  <strong class='user'>gilthansNOSPAAM at gmailSPAAMBLOCK dot com</strong>
  <a href="#51353" class="date">28-Mar-2005 09:36</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Note that a function that calls a variable by reference CANNOT be used recursively, it will generate a CGI error (at least on my windows platform).<br />
Thus:<br />
<span class="default">&lt;?php<br />
$foo </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">;<br />
function </span><span class="default">bar</span><span class="keyword">(&amp;</span><span class="default">$foo</span><span class="keyword">){<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$foo</span><span class="keyword">++;<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="default">$foo</span><span class="keyword">.</span><span class="string">"\n"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; if(</span><span class="default">$foo </span><span class="keyword">&lt; </span><span class="default">10</span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">bar</span><span class="keyword">(</span><span class="default">$foo</span><span class="keyword">);<br />
}<br />
</span><span class="default">?&gt;<br />
</span>Will NOT work.<br />
Instead, you should just use global variables.<br />
<span class="default">&lt;?php<br />
$foo </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">;<br />
function </span><span class="default">bar</span><span class="keyword">(){<br />
&nbsp;&nbsp;&nbsp; global </span><span class="default">$foo</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$foo</span><span class="keyword">++;<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="default">$foo</span><span class="keyword">.</span><span class="string">"\n"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; if(</span><span class="default">$foo </span><span class="keyword">&lt; </span><span class="default">10</span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">bar</span><span class="keyword">(</span><span class="default">$foo</span><span class="keyword">);<br />
}<br />
</span><span class="default">?&gt;<br />
</span>This, of course, assuming that you need to use $foo in other functions or parts of code. Otherwise you can simply pass the variable regulary and there should be no problems.</span>
</code></div>
  </div>
 </div>
 <a name="49749"></a>
 <div class="note">
  <strong class='user'>roy at intelligentdashimaging dot com</strong>
  <a href="#49749" class="date">07-Feb-2005 01:17</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Here's how to get static function behavior and instantiated object behavior in the same function<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="comment">// Test of static/non-static method overloading, sort of, for php<br />
</span><span class="keyword">class </span><span class="default">test </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; var </span><span class="default">$Id </span><span class="keyword">= </span><span class="default">2</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">priint </span><span class="keyword">(</span><span class="default">$id </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if (</span><span class="default">$id </span><span class="keyword">== </span><span class="default">0</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">Id</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="default">$id</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
</span><span class="default">$tc </span><span class="keyword">= new </span><span class="default">test </span><span class="keyword">();<br />
</span><span class="default">$tc</span><span class="keyword">-&gt;</span><span class="default">priint </span><span class="keyword">();<br />
echo </span><span class="string">"\n"</span><span class="keyword">;<br />
</span><span class="default">test</span><span class="keyword">::</span><span class="default">priint </span><span class="keyword">(</span><span class="default">1</span><span class="keyword">);<br />
</span><span class="comment">/* output:<br />
2<br />
1<br />
*/<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="48376"></a>
 <div class="note">
  <strong class='user'>spy-j at rainbowtroopers dot com</strong>
  <a href="#48376" class="date">21-Dec-2004 03:01</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Take care when using parameters passed by reference to return new values in it:<br />
<br />
<span class="default">&lt;?PHP<br />
<br />
</span><span class="keyword">function </span><span class="default">foo</span><span class="keyword">( &amp;</span><span class="default">$refArray</span><span class="keyword">, ....) {<br />
&nbsp; <br />
&nbsp; UNSET(</span><span class="default">$refArray</span><span class="keyword">); </span><span class="comment">//&lt;-- fatal!!!<br />
<br />
&nbsp; </span><span class="keyword">for (....) {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$refArray</span><span class="keyword">[] = ....<br />
&nbsp; }</span><span class="comment">//end for<br />
<br />
</span><span class="keyword">}</span><span class="comment">//end foo<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
I tried to make sure the passed variable is empty so i can fill it up as an array. So i put the UNSET. Unfortunately, this seems to cause a loss with the reference to the passed variable: the filled array was NOT returned in the passed argument refArray!<br />
<br />
I have found that replacing unset as follows seems to work correctly:<br />
<br />
<span class="default">&lt;?PHP<br />
<br />
</span><span class="keyword">function </span><span class="default">bar</span><span class="keyword">( &amp;</span><span class="default">$refArray</span><span class="keyword">, ....) {<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp; if (!EMPTY(</span><span class="default">$refArray</span><span class="keyword">)) </span><span class="default">$refArray </span><span class="keyword">= </span><span class="string">''</span><span class="keyword">;<br />
<br />
&nbsp; : : : <br />
<br />
}</span><span class="comment">//end bar<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="44616"></a>
 <div class="note">
  <strong class='user'>chris at infinitycubed dot net</strong>
  <a href="#44616" class="date">10-Aug-2004 06:39</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Remember, theres an additional overhead when calling functions, so if performance is key to what you are doing, you want to avoid calling out to other functions.<br />
<br />
In a function I was making that found out the distance and angle from 1 point to another, there were 3 calls to a small validation function. It averaged ~0.017 seconds for 400 calls to the function, and with these calls replaced with the actual code, this lowered to 0.011-0.012. So, if you need performance, avoid using other functions.</span>
</code></div>
  </div>
 </div>
 <a name="44189"></a>
 <div class="note">
  <strong class='user'>jose at rondamagazine dot com</strong>
  <a href="#44189" class="date">17-Jul-2004 06:34</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
A PHP4 -&gt; PHP5 migration issue:<br />
<br />
In PHP5, you can't declare a function inside a class method and call it from inside the method. An example:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">A </span><span class="keyword">{<br />
&nbsp;&nbsp; function </span><span class="default">f</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp;&nbsp; function </span><span class="default">inside_f</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; echo </span><span class="string">"I'm inside_f"</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp;&nbsp; echo </span><span class="string">"I'm f"</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp;&nbsp; </span><span class="default">inside_f</span><span class="keyword">();<br />
&nbsp;&nbsp; }<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
PHP5 reports an "Fatal error:&nbsp; Non-static method A::inside_f() cannot be called statically ..."<br />
<br />
Solution: Convert inside_f() to a class method, so you can call it from f() as $this-&gt;inside_f().<br />
This will work in PHP4 and PHP5.<br />
And, if you don't mind PHP4 compatibility, you should probably declare inside_f() as a private method, because it is declared inside f() for its private use (at least, that's what I believed I was doing; only now have I discovered that the original inside_f(), as declared, is a global function).</span>
</code></div>
  </div>
 </div>
 <a name="34676"></a>
 <div class="note">
  <strong class='user'>removeloop at removesuperinfinite dot com</strong>
  <a href="#34676" class="date">03-Aug-2003 01:56</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Method overloading is however permitted.<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">A </span><span class="keyword">{<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; function </span><span class="default">A</span><span class="keyword">() { }<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; function </span><span class="default">ech</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$a </span><span class="keyword">= </span><span class="default">func_get_args</span><span class="keyword">();<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; for( </span><span class="default">$t</span><span class="keyword">=</span><span class="default">0</span><span class="keyword">;</span><span class="default">$t</span><span class="keyword">&lt;</span><span class="default">count</span><span class="keyword">(</span><span class="default">$a</span><span class="keyword">); </span><span class="default">$t</span><span class="keyword">++ ) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="default">$a</span><span class="keyword">[</span><span class="default">$t</span><span class="keyword">];<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
}&nbsp; &nbsp; &nbsp; &nbsp; <br />
<br />
</span><span class="default">$test </span><span class="keyword">= new </span><span class="default">A</span><span class="keyword">();<br />
</span><span class="default">$test</span><span class="keyword">-&gt;</span><span class="default">ech</span><span class="keyword">(</span><span class="default">0</span><span class="keyword">,</span><span class="default">1</span><span class="keyword">,</span><span class="default">2</span><span class="keyword">,</span><span class="default">3</span><span class="keyword">,</span><span class="default">4</span><span class="keyword">,</span><span class="default">5</span><span class="keyword">,</span><span class="default">6</span><span class="keyword">,</span><span class="default">7</span><span class="keyword">,</span><span class="default">8</span><span class="keyword">,</span><span class="default">9</span><span class="keyword">);<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
// output:<br />
// 0123456789</span>
</code></div>
  </div>
 </div>
 <a name="32823"></a>
 <div class="note">
  <strong class='user'>kop at meme dot com</strong>
  <a href="#32823" class="date">08-Jun-2003 03:08</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
You can preface a (builtin php ?) function call with an @ sign, as in:<br />
<br />
@foo();<br />
<br />
This will supress the injection of error messages into the data stream output to the web client.&nbsp; You might do this, for example, to supress the display of error messages were foo() a database function and the database server was down.&nbsp; However, you're probably better off using php configuration directives or error handling functions than using this feature.<br />
<br />
See the section on error handling functions.</span>
</code></div>
  </div>
 </div>
 <a name="31954"></a>
 <div class="note">
  <strong class='user'>Storm</strong>
  <a href="#31954" class="date">09-May-2003 07:55</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I think it worthy of noting (for noobies such as myself):<br />
You can define access to a global variable before it is defined when defining a function as long as the variable is defined before the function is called. This had me baffled for a few hours til I tried it out...lol. Here's a quick example:<br />
<br />
Perfectly valid:<br />
-------------------------<br />
function hello() {<br />
&nbsp;&nbsp; global $hi;<br />
<br />
&nbsp;&nbsp; echo $hi;<br />
}<br />
<br />
$hi = 'Hi There!'; // Var defined after function is defined<br />
<br />
hello();<br />
-------------------------<br />
<br />
I know most of the Gurus persay already knew this, but I didn't! :p This helps ;-)</span>
</code></div>
  </div>
 </div>
 <a name="31145"></a>
 <div class="note">
  <strong class='user'>php at simoneast dot net</strong>
  <a href="#31145" class="date">10-Apr-2003 09:59</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you're frustrated by not having access to global variables from within your functions, instead of declaring each one (particularly if you don't know them all) there are a couple of workarounds...<br />
<br />
If your function just needs to read global variables...<br />
<br />
function aCoolFunction () {<br />
&nbsp;&nbsp;&nbsp; extract($GLOBALS);<br />
....<br />
<br />
This creates a copy of all the global variables in the function scope.&nbsp; Notice that because it's a copy of the variables, changing them won't affect the variables outside the function and the copies are lost at the conclusion of the function.<br />
<br />
If you need to write to your global variables, I haven't tested it, but you could probably loop through the $GLOBALS array and make a "global" declaration for each one.&nbsp; Then you could modify the variables.<br />
<br />
Please note that this shouldn't be standard practice, but only in the case where a function needs access to all the global variables when they may be different from one call to another.&nbsp; Use the "global var1, var2..." declaration where possible.<br />
<br />
Hope that helps some people.<br />
<br />
Simon.</span>
</code></div>
  </div>
 </div>
 <a name="30271"></a>
 <div class="note">
  <strong class='user'>nutbar at innocent dot com</strong>
  <a href="#30271" class="date">12-Mar-2003 12:06</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Regarding the comments about having to declare global variables inside of functions before you can use them...<br />
<br />
Lots of you seem to complain about having to declare lots of variables, when really there's one simple solution to this:<br />
<br />
global $GLOBALS;<br />
<br />
This will define the $GLOBALS variable inside your code, and since that variable is basically like the mother of all variables - *presto*, you now have access to any variable in PHP.</span>
</code></div>
  </div>
 </div>
 <a name="28765"></a>
 <div class="note">
  <strong class='user'>mittag  /// add  /// marcmittag  /// de</strong>
  <a href="#28765" class="date">23-Jan-2003 04:31</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
To devciti at yahoo dot com<br />
<br />
The section "returning values" of the docu says:<br />
<br />
=====<br />
You can't return multiple values from a function, but similar results can be obtained by returning a list. <br />
<br />
function small_numbers()<br />
{<br />
&nbsp;&nbsp;&nbsp; return array (0, 1, 2);<br />
}<br />
list ($zero, $one, $two) = small_numbers();<br />
=====</span>
</code></div>
  </div>
 </div>
 <a name="28076"></a>
 <div class="note">
  <strong class='user'>arathorn at ifrance dot com</strong>
  <a href="#28076" class="date">31-Dec-2002 07:02</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If u want to put somes variables in function that was'nt passed by it, you must use "global" :<br />
<br />
<span class="default">&lt;?php<br />
<br />
$op2 </span><span class="keyword">= </span><span class="default">blabla</span><span class="keyword">;<br />
</span><span class="default">$op3 </span><span class="keyword">= </span><span class="default">blabla</span><span class="keyword">;<br />
<br />
function </span><span class="default">foo</span><span class="keyword">(</span><span class="default">$op1</span><span class="keyword">)<br />
{<br />
&nbsp; global </span><span class="default">$op2</span><span class="keyword">, </span><span class="default">$op3</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="default">$op1</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="default">$op2</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="default">$op3</span><span class="keyword">;<br />
}<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="27907"></a>
 <div class="note">
  <strong class='user'>misc dot anders at feder dot dk</strong>
  <a href="#27907" class="date">24-Dec-2002 03:28</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
PHP allows you to address functions in a very dynamic way:<br />
<br />
$foo = "bar";<br />
$foo("fubar");<br />
<br />
The above will call the bar function with the "fubar" argument.</span>
</code></div>
  </div>
 </div>
 <a name="26322"></a>
 <div class="note">
  <strong class='user'>albaity at php4web dot com</strong>
  <a href="#26322" class="date">26-Oct-2002 07:06</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
To use class from function <br />
you need first to load class OUT the function <br />
and then you can use the class functions from your function <br />
example : <br />
class Cart<br />
{<br />
&nbsp;&nbsp;&nbsp; var $items;&nbsp; // Items in our shopping cart<br />
&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; // Add $num articles of $artnr to the cart<br />
&nbsp;<br />
&nbsp;&nbsp;&nbsp; function add_item ($artnr, $num)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; $this-&gt;items[$artnr] += $num;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; // Take $num articles of $artnr out of the cart<br />
&nbsp;<br />
&nbsp;&nbsp;&nbsp; function remove_item ($artnr, $num)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if ($this-&gt;items[$artnr] &gt; $num) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; $this-&gt;items[$artnr] -= $num;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return true;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; } else {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return false;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
------------------------<br />
<span class="default">&lt;?php<br />
$cart </span><span class="keyword">= new </span><span class="default">Cart</span><span class="keyword">;<br />
<br />
function </span><span class="default">additem</span><span class="keyword">(</span><span class="default">$var1</span><span class="keyword">,</span><span class="default">$var2</span><span class="keyword">){<br />
</span><span class="default">$cart</span><span class="keyword">-&gt;</span><span class="default">add_item</span><span class="keyword">(</span><span class="default">$var1</span><span class="keyword">, </span><span class="default">$var2</span><span class="keyword">);<br />
}<br />
</span><span class="default">additem</span><span class="keyword">(</span><span class="default">1</span><span class="keyword">,</span><span class="default">10</span><span class="keyword">);<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="24239"></a>
 <div class="note">
  <strong class='user'>germanAlonso at keltoi-web dot com</strong>
  <a href="#24239" class="date">10-Aug-2002 07:17</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Although yasuo_ohgaki@hotmail.com has already pointed the recursion support on PHP, here's another example, wich shows clearly the mechanism of recursive algorithms:<br />
<br />
function fact($i){<br />
&nbsp; if($i==1){<br />
&nbsp;&nbsp;&nbsp; return 1;<br />
&nbsp; }else{<br />
&nbsp;&nbsp;&nbsp; return $i*fact($i-1);<br />
&nbsp; }<br />
}<br />
<br />
It returns $i! (supposing $i is a valid positive integer greater than 0).</span>
</code></div>
  </div>
 </div>
 <a name="21150"></a>
 <div class="note">
  <strong class='user'>bishop</strong>
  <a href="#21150" class="date">30-Apr-2002 07:54</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Consider:<br />
<br />
function a() {<br />
&nbsp;&nbsp;&nbsp; function b() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo 'I am b';<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; echo 'I am a';<br />
}<br />
<br />
a();<br />
a();<br />
<br />
As you might NOT expect, the second call to a() fails with a "Cannot redeclare b()" error.&nbsp; This behaviour is correct, insofar as PHP doesn't "allow functions to be redefined."<br />
<br />
A work around:<br />
function a() {<br />
&nbsp;&nbsp;&nbsp; if ( ! function_exists('b') ) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; function b() {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; echo 'I am b';<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; echo 'I am a';<br />
}</span>
</code></div>
  </div>
 </div>
 <a name="19060"></a>
 <div class="note">
  <strong class='user'>fabio at city dot ac dot uk</strong>
  <a href="#19060" class="date">14-Feb-2002 06:57</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
As a corollary to other people's contributions in this section, you have to be careful when transforming a piece of code in to a function (say F1). If this piece of code contains calls to another function (say F2), then each variable used in F2 and defined in F1 must be declared as GLOBAL both in F1 and F2. This is tricky.</span>
</code></div>
  </div>
 </div>
 <a name="16814"></a>
 <div class="note">
  <strong class='user'>xpaz at somm dot com</strong>
  <a href="#16814" class="date">14-Nov-2001 12:47</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
It is possible to define a function from inside another function. <br />
The result is that the inner function does not exist until the outer function gets executed. <br />
<br />
For example, the following code:<br />
<br />
function a () {<br />
&nbsp; function b() {<br />
&nbsp;&nbsp;&nbsp; echo "I am b.\n";<br />
&nbsp; }<br />
&nbsp; echo "I am a.\n";<br />
}<br />
if (function_exists("b")) echo "b is defined.\n"; else echo "b is not defined.\n";<br />
a();<br />
if (function_exists("b")) echo "b is defined.\n"; else echo "b is not defined.\n";<br />
<br />
echoes:<br />
<br />
b is not defined.<br />
I am a.<br />
b is defined.<br />
<br />
Classes too can be defined inside functions, and will not exist until the outer function gets executed.</span>
</code></div>
  </div>
 </div>
 <a name="12314"></a>
 <div class="note">
  <strong class='user'>aboyd at ssti dot com</strong>
  <a href="#12314" class="date">04-Apr-2001 08:34</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
[Editor's note: put your includes in the beginning of your script. You can call an included function, after it has been included&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; --jeroen]<br />
<br />
The documentation states: "In PHP 3, functions must be defined before they are referenced. No such requirement exists in PHP 4."<br />
<br />
I thought it wise to note here that there is in fact a limitation: you cannot bury your function in an include() or require().&nbsp; If the function is in an include()'d file, there is NO way to call that function beforehand.&nbsp; The workaround is to put the function directly in the file that calls the function.</span>
</code></div>
  </div>
 </div>
 <a name="10129"></a>
 <div class="note">
  <strong class='user'>kop at meme dot com</strong>
  <a href="#10129" class="date">14-Dec-2000 03:14</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
See also about controlling the generation of error messages by putting @ in front of the function before you call it, in the section "error control operators".</span>
</code></div>
  </div>
 </div>
 <a name="5284"></a>
 <div class="note">
  <strong class='user'>GMCardoe at netherworldrpg dot net</strong>
  <a href="#5284" class="date">24-Apr-2000 02:02</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Stack overflow means your function called itself recursivly too many times and just completely filled up the processes stack. That error is there to stop a recursive call from completely taking up the entire system memory.</span>
</code></div>
  </div>
 </div>
 <a name="3923"></a>
 <div class="note">
  <strong class='user'>cap at capsi dot cx</strong>
  <a href="#3923" class="date">22-Feb-2000 04:19</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
When using a function within a function, using global in the inner function will not make variables available that have been first initialized within the outer function.</span>
</code></div>
  </div>
 </div>
 <a name="3603"></a>
 <div class="note">
  <strong class='user'>php at paintbot dot com</strong>
  <a href="#3603" class="date">04-Feb-2000 07:32</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Important Note to All New Users: functions do NOT have default access to GLOBAL variables.&nbsp; You must specify globals as such in your function using the 'global' type/keyword.&nbsp; See the section on variables:scope.<br />
<br />
This note should also be added to the documentation, as it would help the majority of programmers who use languages where globals are, well, global (that is, available from anywhere).&nbsp; The scoping rules should also not be buried in subsection 4 of the variables section.&nbsp; It should be front and center because I think this is probably one of the most non-standard and thus confusing design choices of PHP. <br />
<br />
[Ed. note: the variables $_GET, $_POST, $_REQUEST, $_SESSION, and $_FILES are superglobals, which means you don't need the global keyword to use them inside a function]</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=language.functions&amp;redirect=http://www.php.net/manual/en/language.functions.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.functions&amp;redirect=http://www.php.net/manual/en/language.functions.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/language.functions.php">show source</a> |
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