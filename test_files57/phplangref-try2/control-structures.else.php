<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: else - Manual</title>
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
 <link rel="index" href="language.control-structures.php" />
 <link rel="prev" href="control-structures.if.php" />
 <link rel="next" href="control-structures.elseif.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/else" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/control-structures.else.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/control-structures.else.php" />
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
 <li class="header up"><a href="language.control-structures.php">Control Structures</a></li>
 <li><a href="control-structures.intro.php">Introduction</a></li>
 <li><a href="control-structures.if.php">if</a></li>
 <li class="active"><a href="control-structures.else.php">else</a></li>
 <li><a href="control-structures.elseif.php">elseif/else if</a></li>
 <li><a href="control-structures.alternative-syntax.php">Alternative syntax for control structures</a></li>
 <li><a href="control-structures.while.php">while</a></li>
 <li><a href="control-structures.do.while.php">do-while</a></li>
 <li><a href="control-structures.for.php">for</a></li>
 <li><a href="control-structures.foreach.php">foreach</a></li>
 <li><a href="control-structures.break.php">break</a></li>
 <li><a href="control-structures.continue.php">continue</a></li>
 <li><a href="control-structures.switch.php">switch</a></li>
 <li><a href="control-structures.declare.php">declare</a></li>
 <li><a href="function.return.php">return</a></li>
 <li><a href="function.require.php">require</a></li>
 <li><a href="function.include.php">include</a></li>
 <li><a href="function.require-once.php">require_<span class="w"> </span>once</a></li>
 <li><a href="function.include-once.php">include_<span class="w"> </span>once</a></li>
 <li><a href="control-structures.goto.php">goto</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="control-structures.elseif.php">elseif/else if<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="control-structures.if.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />if</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/control-structures.else.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/control-structures.else.php">Brazilian Portuguese</option>
    <option value="zh/control-structures.else.php">Chinese (Simplified)</option>
    <option value="fr/control-structures.else.php">French</option>
    <option value="de/control-structures.else.php">German</option>
    <option value="ja/control-structures.else.php">Japanese</option>
    <option value="pl/control-structures.else.php">Polish</option>
    <option value="ro/control-structures.else.php">Romanian</option>
    <option value="ru/control-structures.else.php">Russian</option>
    <option value="fa/control-structures.else.php">Persian</option>
    <option value="es/control-structures.else.php">Spanish</option>
    <option value="tr/control-structures.else.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="control-structures.else" class="sect1">
 <h2 class="title"><em>else</em></h2>
 <p class="verinfo">(PHP 4, PHP 5)</p>
 <p class="para">
  Often you&#039;d want to execute a statement if a certain condition is
  met, and a different statement if the condition is not met.  This
  is what <em>else</em> is for.  <em>else</em>
  extends an <em>if</em> statement to execute a statement
  in case the expression in the <em>if</em> statement
  evaluates to <strong><code>FALSE</code></strong>.  For example, the following
  code would display <span class="computeroutput">a is greater than
  b</span> if <var class="varname"><var class="varname">$a</var></var> is greater than
  <var class="varname"><var class="varname">$b</var></var>, and <span class="computeroutput">a is NOT greater
  than b</span> otherwise:
  <div class="informalexample">
   <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">if&nbsp;(</span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">&gt;&nbsp;</span><span style="color: #0000BB">$b</span><span style="color: #007700">)&nbsp;{<br />&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"a&nbsp;is&nbsp;greater&nbsp;than&nbsp;b"</span><span style="color: #007700">;<br />}&nbsp;else&nbsp;{<br />&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"a&nbsp;is&nbsp;NOT&nbsp;greater&nbsp;than&nbsp;b"</span><span style="color: #007700">;<br />}<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
   </div>

  </div>

  The <em>else</em> statement is only executed if the
  <em>if</em> expression evaluated to
  <strong><code>FALSE</code></strong>, and if there were any
  <em>elseif</em> expressions - only if they evaluated to
  <strong><code>FALSE</code></strong> as well (see <a href="control-structures.elseif.php" class="link">elseif</a>).

 </p>
</div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="control-structures.elseif.php">elseif/else if<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="control-structures.if.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />if</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/control-structures.else.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=control-structures.else&amp;redirect=http://www.php.net/manual/en/control-structures.else.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=control-structures.else&amp;redirect=http://www.php.net/manual/en/control-structures.else.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>else</strong>
 </div><div id="allnotes">
 <a name="104753"></a>
 <div class="note">
  <strong class='user'>php at keith tyler dot com</strong>
  <a href="#104753" class="date">05-Jul-2011 10:52</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
This is valid syntax:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">if (</span><span class="default">$a</span><span class="keyword">) print </span><span class="string">"a is true"</span><span class="keyword">;<br />
else print </span><span class="string">"a is false"</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
A holdover from the bash-style compatibility in older PHP versions, perhaps.</span>
</code></div>
  </div>
 </div>
 <a name="100656"></a>
 <div class="note">
  <strong class='user'>smoldar at gmail dot com</strong>
  <a href="#100656" class="date">28-Oct-2010 07:21</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
You can use the if to make a Yes/No field, verify if the statement is real or not and show the correct option checked.<br />
<br />
<span class="default">&lt;?php </span><span class="keyword">if(</span><span class="default">$variable </span><span class="keyword">== </span><span class="string">'S'</span><span class="keyword">) {</span><span class="default">?&gt;<br />
</span>&lt;input name="blah" type="radio" value="Y" checked="checked"&gt; Yes<br />
&lt;input name="blah" type="radio" value="N"&gt; No<br />
<span class="default">&lt;?php </span><span class="keyword">} else {</span><span class="default">?&gt;<br />
</span>&lt;input name="blah" type="radio" value="Y"&gt; Yes<br />
&lt;input name="blah" type="radio" value="N" checked="checked"&gt; No<br />
<span class="default">&lt;?php </span><span class="keyword">}</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="92972"></a>
 <div class="note">
  <strong class='user'>Larry H-C</strong>
  <a href="#92972" class="date">17-Aug-2009 10:08</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
When you escape out of HTML, you can get an UNEXPECTED T_ELSE error with the following:<br />
<br />
Error:<br />
<br />
&lt;? if( $condition ) { <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; dosomething; <br />
&nbsp;&nbsp; } <br />
?&gt;<br />
<br />
&lt;? else { <br />
&nbsp;&nbsp; &nbsp; &nbsp; dosomethingelse; <br />
&nbsp;&nbsp; } <br />
?&gt;<br />
<br />
Correct:<br />
<br />
&lt;? if( $condition ) { <br />
&nbsp;&nbsp; &nbsp; &nbsp; dosomething; <br />
?&gt;<br />
<br />
&lt;? } else { <br />
&nbsp;&nbsp; &nbsp; &nbsp; dosomethingelse; <br />
&nbsp;&nbsp; } <br />
?&gt;<br />
<br />
Apparently the compiler thinks a ?&gt; &lt;? breaks the connection between the } and the else</span>
</code></div>
  </div>
 </div>
 <a name="82771"></a>
 <div class="note">
  <strong class='user'>Theoden</strong>
  <a href="#82771" class="date">24-Apr-2008 05:42</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
At Caliban Darklock<br />
<br />
I don't know if it is just improvements in the parser, but there is a negligible difference in the performance of "elseif" vs "else if" as of version 5. One thousandth of a second in your example and 8 thousandths if the eval statement is repeated 5 times. <br />
If the constructs are in regular code, then there appears to be no difference. This leads me to believe that the difference in the eval code is from there being an extra parser token. <br />
<br />
Also the main performance burden of recursive functions is the stack operations of changing the context. In this case I believe that it would parse to very similar (if not identical) jmp controls.<br />
<br />
In summary, use your preference. Readability and maintainability rank far higher on the priority scale.<br />
<br />
One Additional note, there appears to be a limit of the number of "else if" statements (perhaps nested statements in general) that php will handle before starting to get screwy. This limit is about 1100. "elseif" is not affected by this.</span>
</code></div>
  </div>
 </div>
 <a name="82472"></a>
 <div class="note">
  <strong class='user'>dormeydo at gmail dot com</strong>
  <a href="#82472" class="date">12-Apr-2008 04:51</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
An alternative and very useful syntax is the following one:<br />
<br />
statement ? execute if true : execute if false<br />
<br />
Ths is very usefull for dynamic outout inside strings, for example:<br />
<br />
print('$a is ' . ($a &gt; $b ? 'bigger than' : ($a == $b ? 'equal to' : 'smaler than' )) .&nbsp; '&nbsp; $b');<br />
<br />
This will print "$a is smaler than $b" is $b is bigger than $a, "$a is bigger than $b" if $a si bigger and "$a is equal to $b" if they are same.</span>
</code></div>
  </div>
 </div>
 <a name="76636"></a>
 <div class="note">
  <strong class='user'>mitch at mitchellbeaumont dot com</strong>
  <a href="#76636" class="date">24-Jul-2007 12:09</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
At gwmpro at yahoo dot com<br />
<br />
The curly brace is not required however, for readability and maintenance, many developers would consider it bad style not to include them.</span>
</code></div>
  </div>
 </div>
 <a name="47252"></a>
 <div class="note">
  <strong class='user'>Caliban Darklock</strong>
  <a href="#47252" class="date">08-Nov-2004 11:24</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you're coming from another language that does not have the "elseif" construct (e.g. C++), it's important to recognise that "else if" is a nested language construct and "elseif" is a linear language construct; they may be compared in performance to a recursive loop as opposed to an iterative loop. <br />
<br />
<span class="default">&lt;?php<br />
$limit</span><span class="keyword">=</span><span class="default">1000</span><span class="keyword">;<br />
for(</span><span class="default">$idx</span><span class="keyword">=</span><span class="default">0</span><span class="keyword">;</span><span class="default">$idx</span><span class="keyword">&lt;</span><span class="default">$limit</span><span class="keyword">;</span><span class="default">$idx</span><span class="keyword">++)&nbsp; <br />
{ </span><span class="default">$list</span><span class="keyword">[]=</span><span class="string">"if(false) echo \"$idx;\n\"; else"</span><span class="keyword">; }<br />
</span><span class="default">$list</span><span class="keyword">[]=</span><span class="string">" echo \"$idx\n\";"</span><span class="keyword">;<br />
</span><span class="default">$space</span><span class="keyword">=</span><span class="default">implode</span><span class="keyword">(</span><span class="string">" "</span><span class="keyword">,</span><span class="default">$list</span><span class="keyword">);| </span><span class="comment">// if ... else if ... else<br />
</span><span class="default">$nospace</span><span class="keyword">=</span><span class="default">implode</span><span class="keyword">(</span><span class="string">""</span><span class="keyword">,</span><span class="default">$list</span><span class="keyword">); </span><span class="comment">// if ... elseif ... else<br />
</span><span class="default">$start</span><span class="keyword">=</span><span class="default">array_sum</span><span class="keyword">(</span><span class="default">explode</span><span class="keyword">(</span><span class="string">" "</span><span class="keyword">,</span><span class="default">microtime</span><span class="keyword">()));<br />
eval(</span><span class="default">$space</span><span class="keyword">);<br />
</span><span class="default">$end</span><span class="keyword">=</span><span class="default">array_sum</span><span class="keyword">(</span><span class="default">explode</span><span class="keyword">(</span><span class="string">" "</span><span class="keyword">,</span><span class="default">microtime</span><span class="keyword">()));<br />
echo </span><span class="default">$end</span><span class="keyword">-</span><span class="default">$start </span><span class="keyword">. </span><span class="string">" seconds\n"</span><span class="keyword">;<br />
</span><span class="default">$start</span><span class="keyword">=</span><span class="default">array_sum</span><span class="keyword">(</span><span class="default">explode</span><span class="keyword">(</span><span class="string">" "</span><span class="keyword">,</span><span class="default">microtime</span><span class="keyword">()));<br />
eval(</span><span class="default">$nospace</span><span class="keyword">);<br />
</span><span class="default">$end</span><span class="keyword">=</span><span class="default">array_sum</span><span class="keyword">(</span><span class="default">explode</span><span class="keyword">(</span><span class="string">" "</span><span class="keyword">,</span><span class="default">microtime</span><span class="keyword">()));<br />
echo </span><span class="default">$end</span><span class="keyword">-</span><span class="default">$start </span><span class="keyword">. </span><span class="string">" seconds\n"</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
This test should show that "elseif" executes in roughly two-thirds the time of "else if". (Increasing $limit will also eventually cause a parser stack overflow error, but the level where this happens is ridiculous in real world terms. Nobody normally nests if() blocks to more than a thousand levels unless they're trying to break things, which is a whole different problem.)<br />
<br />
There is still a need for "else if", as you may have additional code to be executed unconditionally at some rung of the ladder; an "else if" construction allows this unconditional code to be elegantly inserted before or after the entire rest of the process. Consider the following elseif() ladder:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">if(</span><span class="default">$a</span><span class="keyword">) { </span><span class="default">conditional1</span><span class="keyword">(); }<br />
elseif(</span><span class="default">$b</span><span class="keyword">) { </span><span class="default">conditional2</span><span class="keyword">(); }<br />
elseif(</span><span class="default">$c</span><span class="keyword">) { </span><span class="default">conditional3</span><span class="keyword">(); }<br />
elseif(</span><span class="default">$d</span><span class="keyword">) { </span><span class="default">conditional4</span><span class="keyword">(); }<br />
elseif(</span><span class="default">$e</span><span class="keyword">) { </span><span class="default">conditional5</span><span class="keyword">(); }<br />
elseif(</span><span class="default">$f</span><span class="keyword">) { </span><span class="default">conditional6</span><span class="keyword">(); }<br />
elseif(</span><span class="default">$g</span><span class="keyword">) { </span><span class="default">conditional7</span><span class="keyword">(); }<br />
elseif(</span><span class="default">$h</span><span class="keyword">) { </span><span class="default">conditional8</span><span class="keyword">(); }<br />
else { </span><span class="default">conditional9</span><span class="keyword">(); }<br />
</span><span class="default">?&gt;<br />
</span><br />
To insert unconditional preprocessing code for $e onward, one need only split the "elseif":<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">if(</span><span class="default">$a</span><span class="keyword">) { </span><span class="default">conditional1</span><span class="keyword">(); }<br />
elseif(</span><span class="default">$b</span><span class="keyword">) { </span><span class="default">conditional2</span><span class="keyword">(); }<br />
elseif(</span><span class="default">$c</span><span class="keyword">) { </span><span class="default">conditional3</span><span class="keyword">(); }<br />
elseif(</span><span class="default">$d</span><span class="keyword">) { </span><span class="default">conditional4</span><span class="keyword">(); }<br />
else {<br />
....</span><span class="default">unconditional</span><span class="keyword">();<br />
....if(</span><span class="default">$e</span><span class="keyword">) { </span><span class="default">conditional5</span><span class="keyword">(); }<br />
....elseif(</span><span class="default">$f</span><span class="keyword">) { </span><span class="default">conditional6</span><span class="keyword">(); }<br />
....elseif(</span><span class="default">$g</span><span class="keyword">) { </span><span class="default">conditional7</span><span class="keyword">(); }<br />
....elseif(</span><span class="default">$h</span><span class="keyword">) { </span><span class="default">conditional8</span><span class="keyword">(); }<br />
....else { </span><span class="default">conditional9</span><span class="keyword">(); }<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
The alternative is to duplicate the unconditional code throughout the construct.</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=control-structures.else&amp;redirect=http://www.php.net/manual/en/control-structures.else.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=control-structures.else&amp;redirect=http://www.php.net/manual/en/control-structures.else.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/control-structures.else.php">show source</a> |
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