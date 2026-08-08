<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: while - Manual</title>
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
 <link rel="prev" href="control-structures.alternative-syntax.php" />
 <link rel="next" href="control-structures.do.while.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/while" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/control-structures.while.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/control-structures.while.php" />
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
 <li><a href="control-structures.else.php">else</a></li>
 <li><a href="control-structures.elseif.php">elseif/else if</a></li>
 <li><a href="control-structures.alternative-syntax.php">Alternative syntax for control structures</a></li>
 <li class="active"><a href="control-structures.while.php">while</a></li>
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
  <a href="control-structures.do.while.php">do-while<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="control-structures.alternative-syntax.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Alternative syntax for control structures</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/control-structures.while.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/control-structures.while.php">Brazilian Portuguese</option>
    <option value="zh/control-structures.while.php">Chinese (Simplified)</option>
    <option value="fr/control-structures.while.php">French</option>
    <option value="de/control-structures.while.php">German</option>
    <option value="ja/control-structures.while.php">Japanese</option>
    <option value="pl/control-structures.while.php">Polish</option>
    <option value="ro/control-structures.while.php">Romanian</option>
    <option value="ru/control-structures.while.php">Russian</option>
    <option value="fa/control-structures.while.php">Persian</option>
    <option value="es/control-structures.while.php">Spanish</option>
    <option value="tr/control-structures.while.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="control-structures.while" class="sect1">
 <h2 class="title"><em>while</em></h2>
 <p class="verinfo">(PHP 4, PHP 5)</p>
 <p class="para">
  <em>while</em> loops are the simplest type of loop in
  PHP.  They behave just like their C counterparts.  The basic form
  of a <em>while</em> statement is:
  <div class="informalexample">
   <div class="example-contents">
<div class="cdata"><pre>
while (expr)
    statement
</pre></div>
   </div>

  </div>
 </p>
 <p class="simpara">
  The meaning of a <em>while</em> statement is simple.  It
  tells PHP to execute the nested statement(s) repeatedly, as long
  as the <em>while</em> expression evaluates to
  <strong><code>TRUE</code></strong>.  The value of the expression is checked
  each time at the beginning of the loop, so even if this value
  changes during the execution of the nested statement(s), execution
  will not stop until the end of the iteration (each time PHP runs
  the statements in the loop is one iteration).  Sometimes, if the
  <em>while</em> expression evaluates to
  <strong><code>FALSE</code></strong> from the very beginning, the nested
  statement(s) won&#039;t even be run once.
 </p>
 <p class="para">
  Like with the <em>if</em> statement, you can group
  multiple statements within the same <em>while</em> loop
  by surrounding a group of statements with curly braces, or by
  using the alternate syntax:
  <div class="informalexample">
   <div class="example-contents">
<div class="cdata"><pre>
while (expr):
    statement
    ...
endwhile;
</pre></div>
   </div>

  </div>
 </p>
 <p class="para">
  The following examples are identical, and both print the numbers
  1 through 10:
  <div class="informalexample">
   <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #FF8000">/*&nbsp;example&nbsp;1&nbsp;*/<br /><br /></span><span style="color: #0000BB">$i&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">1</span><span style="color: #007700">;<br />while&nbsp;(</span><span style="color: #0000BB">$i&nbsp;</span><span style="color: #007700">&lt;=&nbsp;</span><span style="color: #0000BB">10</span><span style="color: #007700">)&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #0000BB">$i</span><span style="color: #007700">++;&nbsp;&nbsp;</span><span style="color: #FF8000">/*&nbsp;the&nbsp;printed&nbsp;value&nbsp;would&nbsp;be<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$i&nbsp;before&nbsp;the&nbsp;increment<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(post-increment)&nbsp;*/<br /></span><span style="color: #007700">}<br /><br /></span><span style="color: #FF8000">/*&nbsp;example&nbsp;2&nbsp;*/<br /><br /></span><span style="color: #0000BB">$i&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">1</span><span style="color: #007700">;<br />while&nbsp;(</span><span style="color: #0000BB">$i&nbsp;</span><span style="color: #007700">&lt;=&nbsp;</span><span style="color: #0000BB">10</span><span style="color: #007700">):<br />&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #0000BB">$i</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$i</span><span style="color: #007700">++;<br />endwhile;<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
   </div>

  </div>
 </p>
</div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="control-structures.do.while.php">do-while<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="control-structures.alternative-syntax.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Alternative syntax for control structures</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/control-structures.while.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=control-structures.while&amp;redirect=http://www.php.net/manual/en/control-structures.while.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=control-structures.while&amp;redirect=http://www.php.net/manual/en/control-structures.while.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>while</strong>
 </div><div id="allnotes">
 <a name="96591"></a>
 <div class="note">
  <strong class='user'>ravenswd at gmail dot com</strong>
  <a href="#96591" class="date">06-Mar-2010 07:34</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I find it often clearer to set a simple flag ($finished) to false at the start of the loop, and have the program set it to true when it's finished doing whatever it's trying to do. Then the code is more self-documenting: WHILE NOT FINISHED keep going through the loop. FINISHED EQUALS TRUE when you're done. Here's an example. This is the code I use to generate a random filename and ensure that there is not already an existing file with the same name. I've added very verbose comments to it to make it clear how it works:<br />
<br />
<span class="default">&lt;?php<br />
$finaldir </span><span class="keyword">= </span><span class="string">'download'</span><span class="keyword">;<br />
<br />
</span><span class="default">$finished </span><span class="keyword">= </span><span class="default">false</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// we're not finished yet (we just started)<br />
</span><span class="keyword">while ( ! </span><span class="default">$finished </span><span class="keyword">):&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// while not finished<br />
&nbsp; </span><span class="default">$rn </span><span class="keyword">= </span><span class="default">rand</span><span class="keyword">();&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="comment">// random number<br />
&nbsp; </span><span class="default">$outfile </span><span class="keyword">= </span><span class="default">$finaldir</span><span class="keyword">.</span><span class="string">'/'</span><span class="keyword">.</span><span class="default">$rn</span><span class="keyword">.</span><span class="string">'.gif'</span><span class="keyword">;&nbsp;&nbsp; </span><span class="comment">// output file name<br />
&nbsp; </span><span class="keyword">if ( ! </span><span class="default">file_exists</span><span class="keyword">(</span><span class="default">$outfile</span><span class="keyword">) ):&nbsp; &nbsp; &nbsp; &nbsp; </span><span class="comment">// if file DOES NOT exist...<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$finished </span><span class="keyword">= </span><span class="default">true</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="comment">// ...we are finished<br />
&nbsp; </span><span class="keyword">endif;<br />
endwhile;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="comment">// (if not finished, re-start WHILE loop)<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="93919"></a>
 <div class="note">
  <strong class='user'>scott at mstech dot com</strong>
  <a href="#93919" class="date">06-Oct-2009 02:48</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Just a note about using the continue statement to forego the remainder of a loop - be SURE you're not issuing the continue statement from within a SWITCH case - doing so will not continue the while loop, but rather the switch statement itself.<br />
<br />
While that may seem obvious to some, it took a little bit of testing for me, so hopefully this helps someone else.</span>
</code></div>
  </div>
 </div>
 <a name="84023"></a>
 <div class="note">
  <strong class='user'>s dot seitz at netz-haut dot de</strong>
  <a href="#84023" class="date">24-Jun-2008 08:21</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Due to the fact that php only interprets the necessary elements to get a result, I found it convenient to concatenate different sql queries into one statement:<br />
<br />
<span class="default">&lt;?php<br />
<br />
$q1 </span><span class="keyword">= </span><span class="string">'some query on a set of tables'</span><span class="keyword">;<br />
</span><span class="default">$q2 </span><span class="keyword">= </span><span class="string">'similar query on a another set of tables'</span><span class="keyword">;<br />
<br />
if ( (</span><span class="default">$r1</span><span class="keyword">=</span><span class="default">mysql_query</span><span class="keyword">(</span><span class="default">$q1</span><span class="keyword">)) &amp;&amp; (</span><span class="default">$r2</span><span class="keyword">=</span><span class="default">mysql_query</span><span class="keyword">(</span><span class="default">$q2</span><span class="keyword">)) ) {<br />
<br />
&nbsp;&nbsp; &nbsp; while ((</span><span class="default">$row</span><span class="keyword">=</span><span class="default">mysql_fetch_assoc</span><span class="keyword">(</span><span class="default">$r1</span><span class="keyword">))||(</span><span class="default">$row</span><span class="keyword">=</span><span class="default">mysql_fetch_assoc</span><span class="keyword">(</span><span class="default">$r2</span><span class="keyword">))) {<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; </span><span class="comment">/* do something with $row coming from $r1 and $r2 */<br />
<br />
&nbsp;&nbsp; &nbsp;&nbsp; </span><span class="keyword">}<br />
&nbsp;&nbsp; }<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
[EDIT BY danbrown AT php DOT net: Contains a bugfix supplied by "Ira" on 14-AUG-09 to address an extra '(' in the leading `if` statement.]</span>
</code></div>
  </div>
 </div>
 <a name="73449"></a>
 <div class="note">
  <strong class='user'>dominik at deobald dot org</strong>
  <a href="#73449" class="date">23-Feb-2007 08:50</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
@stuart:<br />
<br />
There's nothing strange or unexpected about your loop's behaviour.<br />
<br />
&gt; So in effect the main while loop is only doing one iteration... and not 4 as expected....<br />
<br />
That's the wrong conclusion. The outer "while" does all four iterations. However the "inner" loop does nothing for the second, third and fourth run.<br />
<br />
&gt; I think it would be good to have an explaination of this strange behaviour. <br />
<br />
Here it is:<br />
<br />
<span class="default">&lt;?PHP<br />
$i</span><span class="keyword">=</span><span class="default">0</span><span class="keyword">;<br />
while(</span><span class="default">$i </span><span class="keyword">&lt; </span><span class="default">count</span><span class="keyword">(</span><span class="default">$one</span><span class="keyword">)) {<br />
&nbsp;&nbsp; <br />
&nbsp;&nbsp; while(</span><span class="default">$a </span><span class="keyword">= </span><span class="default">each</span><span class="keyword">(</span><span class="default">$two</span><span class="keyword">)) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; echo </span><span class="default">$a</span><span class="keyword">[</span><span class="default">1</span><span class="keyword">].</span><span class="string">" - "</span><span class="keyword">.</span><span class="default">$one</span><span class="keyword">[</span><span class="default">$i</span><span class="keyword">].</span><span class="string">", "</span><span class="keyword">; <br />
&nbsp;&nbsp; }<br />
&nbsp;&nbsp; </span><span class="default">$i</span><span class="keyword">++;<br />
&nbsp;&nbsp; <br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
The "problem" is your use of "each", which reached the last item after the first iteration of the outer loop. After that, when you come back to the second iteration with the outer loop, "each" still is at the end of the array $two.<br />
<br />
If you add a reset($two) in front of the inner "while", you'll get the result you expect.</span>
</code></div>
  </div>
 </div>
 <a name="65243"></a>
 <div class="note">
  <strong class='user'>startide at free dot fr</strong>
  <a href="#65243" class="date">27-Apr-2006 08:08</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Talking about while, dropdown menus, and ternary operator which was mentionned before, you can combine them to have drop menu built with a value selected according to your wishses.<br />
<br />
&lt;select name="whatever"&gt;<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">while (</span><span class="default">$data </span><span class="keyword">= </span><span class="default">mysql_fetch_assoc</span><span class="keyword">(</span><span class="default">$requeteID</span><span class="keyword">))<br />
{<br />
&nbsp; </span><span class="default">$menu </span><span class="keyword">.= </span><span class="string">'&lt;option value="'</span><span class="keyword">.</span><span class="default">$data</span><span class="keyword">[</span><span class="string">'id'</span><span class="keyword">].</span><span class="string">'"'</span><span class="keyword">;<br />
&nbsp; </span><span class="default">$menu </span><span class="keyword">.= (</span><span class="default">$data</span><span class="keyword">[</span><span class="string">'id'</span><span class="keyword">] == </span><span class="default">$_GET</span><span class="keyword">[</span><span class="string">'id'</span><span class="keyword">] ? </span><span class="string">' selected&gt;' </span><span class="keyword">:</span><span class="string">'&gt;'</span><span class="keyword">);<br />
&nbsp; </span><span class="default">$menu </span><span class="keyword">.= </span><span class="default">$data</span><span class="keyword">[</span><span class="string">'name'</span><span class="keyword">].</span><span class="string">'&lt;/option&gt;'</span><span class="keyword">;<br />
}<br />
echo </span><span class="default">$menu</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span>&lt;/select&gt;<br />
<br />
Therefore if you are creating a form to select data from database, and want the form displayed when search is done to show what parameters have been chosen that will do the trick !!<br />
<br />
Let's say I make a search between different sports, I choose football in my form, send my query... then displays are show, the menu will have football selected because of the ternary operator that displays "selected&gt;" on the &lt;option&gt; ;) Enjoy ^^</span>
</code></div>
  </div>
 </div>
 <a name="63930"></a>
 <div class="note">
  <strong class='user'>sub7ime at yahoo dot com</strong>
  <a href="#63930" class="date">04-Apr-2006 12:00</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I was reading the excellent post by wbryson at gmail dot com and I wanted to just add that the ? : syntax is known as the 'ternary operator' for those who want to learn more about it.</span>
</code></div>
  </div>
 </div>
 <a name="52741"></a>
 <div class="note">
  <strong class='user'>chris mushy</strong>
  <a href="#52741" class="date">11-May-2005 06:43</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Just a note to stuart - the reason for this behaviour is because using the while(value = each(array)) construct increments the internal counter of the array as its looped through. Therefore if you intend to repeat the loop, you need to reset the counter. eg:<br />
<br />
$one = array("10", "20", "30", "40");<br />
$two = array("a", "b", "c", "d");<br />
<br />
$i=0;<br />
while($i &lt; count($one)) {<br />
&nbsp;&nbsp; reset($two);<br />
&nbsp;&nbsp; while($a = each($two)) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; echo $a[1]." - ".$one[$i].", "; <br />
&nbsp;&nbsp; }<br />
&nbsp;&nbsp; $i++;<br />
&nbsp;&nbsp; <br />
}<br />
<br />
This produces:<br />
<br />
a - 10, b - 10, c - 10, d - 10, a - 20, b - 20, c - 20, d - 20, a - 30, b - 30, c - 30, d - 30, a - 40, b - 40, c - 40, d - 40,</span>
</code></div>
  </div>
 </div>
 <a name="52733"></a>
 <div class="note">
  <strong class='user'>stuart</strong>
  <a href="#52733" class="date">11-May-2005 02:06</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
A note to anyone nesting a while loop inside a while loop....<br />
<br />
Consider the example below:<br />
<br />
$one = array("10", "20", "30", "40");<br />
$two = array("a", "b", "c", "d");<br />
<br />
$i=0;<br />
while($i &lt; count($one)) {<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; while($a = each($two)) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo $a[1]." - ".$one[$i].", "; <br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; $i++;<br />
&nbsp;&nbsp;&nbsp; <br />
}<br />
<br />
This will return the following:<br />
a - 10, b - 10, c - 10, d - 10<br />
<br />
So in effect the main while loop is only doing one iteration... and not 4 as expected....<br />
<br />
Now the example below works as expected..<br />
$i=0;<br />
while($i &lt; count($one)) {<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; foreach($two as $a) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo $a." - ".$one[$i]."\n"; <br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; $i++;<br />
&nbsp;&nbsp;&nbsp; <br />
}<br />
<br />
by returning:<br />
a - 10, b - 10, c - 10, d - 10, a - 20, b - 20, c - 20, d - 20, a - 30, b - 30, c - 30, d - 30, a - 40, b - 40, c - 40, d - 40<br />
<br />
So there is clearly a difference on how while statements work in comparison to other looping structures.<br />
<br />
I think it would be good to have an explaination of this strange behaviour.</span>
</code></div>
  </div>
 </div>
 <a name="50900"></a>
 <div class="note">
  <a href="#50900" class="date">13-Mar-2005 09:54</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
virtualjosh at yahoo dot com (Hosh) wrote on: 16-Aug-2003 12:52<br />
<br />
The speedtest is interesting. But the seemingly fastest way contains a pitfall for beginners who just use it because it is fast and fast is cool ;)<br />
<br />
Walking through an array with next() will cut of the first entry, as this is the way next() works ;)<br />
<br />
If you really need to do it this way, make sure your array contains an empty entry at the beginning. Another way would be to use<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">while (</span><span class="default">$this </span><span class="keyword">= </span><span class="default">current</span><span class="keyword">(</span><span class="default">$array</span><span class="keyword">) ){<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">do_something</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">next</span><span class="keyword">(</span><span class="default">$array</span><span class="keyword">);<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
There is an impact on speed for sure but I did not test it. I would advise to stick with conventional methods because current(),next() in while loops is too error prone for me.</span>
</code></div>
  </div>
 </div>
 <a name="50204"></a>
 <div class="note">
  <strong class='user'>Ilene Jones</strong>
  <a href="#50204" class="date">21-Feb-2005 01:12</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
For Perl programmers, break is similar to last<br />
<br />
while (1) {<br />
&nbsp;&nbsp; while(cond) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; if (error) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; break 2; // in perl this could have been last;<br />
&nbsp;&nbsp; &nbsp; &nbsp; }<br />
&nbsp;&nbsp; }<br />
}</span>
</code></div>
  </div>
 </div>
 <a name="47889"></a>
 <div class="note">
  <strong class='user'>corychristison[AT]NSPAMlavacube[dot]com</strong>
  <a href="#47889" class="date">03-Dec-2004 03:08</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
While can do wonders if you need something to queue writing to a file while something else has access to it.<br />
<br />
Here is my simple example:<br />
<br />
<span class="default">&lt;?php<br />
<br />
&nbsp; </span><span class="keyword">function </span><span class="default">write </span><span class="keyword">(</span><span class="default">$data</span><span class="keyword">, </span><span class="default">$file</span><span class="keyword">, </span><span class="default">$write_mode</span><span class="keyword">=</span><span class="string">"w"</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$lock </span><span class="keyword">= </span><span class="default">$file </span><span class="keyword">. </span><span class="string">".lock"</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; </span><span class="comment">// run the write fix, to stop any clashes that may occur<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">write_fix</span><span class="keyword">(</span><span class="default">$lock</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; </span><span class="comment">// create a new lock file after write_fix() for this writing session<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">touch</span><span class="keyword">( </span><span class="default">$lock </span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; </span><span class="comment">// write to your file<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$open </span><span class="keyword">= </span><span class="default">fopen</span><span class="keyword">(</span><span class="default">$file</span><span class="keyword">, </span><span class="default">$write_mode</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">fwrite</span><span class="keyword">(</span><span class="default">$open</span><span class="keyword">, </span><span class="default">$data</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">fclose</span><span class="keyword">(</span><span class="default">$open</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; </span><span class="comment">// kill your current lock<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">unlink</span><span class="keyword">(</span><span class="default">$lock</span><span class="keyword">);<br />
&nbsp; }<br />
<br />
&nbsp; function </span><span class="default">write_fix </span><span class="keyword">(</span><span class="default">$lock_file</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; while( </span><span class="default">file_exists</span><span class="keyword">(</span><span class="default">$lock_file</span><span class="keyword">){<br />
&nbsp;&nbsp; &nbsp;&nbsp; </span><span class="comment">// do something in here?<br />
&nbsp;&nbsp; &nbsp;&nbsp; // maybe sleep for a few microseconds<br />
&nbsp;&nbsp; &nbsp;&nbsp; // to maintain stability, if this is going to <br />
&nbsp;&nbsp; &nbsp;&nbsp; // take a while ?? [just a suggestion]<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">}<br />
&nbsp; }<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
This method is not recommended for use with programs that will be needing a good few seconds to write to a file, as the while function will eat up alot of process cycles.&nbsp; However, this method does work, and is easy to implement.&nbsp; It also groups the writing functions into one easy to use function, making life easier. :-)</span>
</code></div>
  </div>
 </div>
 <a name="35022"></a>
 <div class="note">
  <strong class='user'>virtualjosh at yahoo dot com (Hosh)</strong>
  <a href="#35022" class="date">15-Aug-2003 03:52</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I made a test traversing an array (simple, but long, numeric array with numeric keys). My test had a cycle per method, and multiplied each array element by 100.. These were my results:<br />
<br />
******************************************************<br />
30870 Element Array Traversing<br />
<br />
[test_time] [BEGINS/RESETS @ time_start = 1060977996.689]<br />
0.2373 seg later -&gt; while (list ($key, $val) = each ($array)) ENDS<br />
<br />
[test_time] [BEGINS/RESETS @ time_start = 1060977996.9414] <br />
0.1916 seg later -&gt; while (list ($key,) = each ($array))&nbsp; ENDS<br />
<br />
[test_time] [BEGINS/RESETS @ time_start = 1060977997.1513]<br />
0.1714 seg later -&gt; foreach ($array AS $key=&gt;$value) ENDS<br />
<br />
[test_time] [BEGINS/RESETS @ time_start = 1060977997.3378]<br />
0.0255 seg later -&gt; while ($next = next($array)) ENDS<br />
<br />
[test_time] [BEGINS/RESETS @ time_start = 1060977997.3771]<br />
0.1735 seg later -&gt; foreach ($array AS $value) ENDS<br />
**************************************************************<br />
<br />
foreach is fatser than a while (list&nbsp; - each), true. <br />
However, a while(next) was faster than foreach.<br />
<br />
These were the winning codes:<br />
<br />
$array = $save;<br />
test_time("",1);<br />
foreach ($array AS $key=&gt;$value)<br />
&nbsp;&nbsp;&nbsp; $array[$key] = $array[$key] * 100;<br />
test_time("foreach (\$array AS \$key=&gt;\$value)");<br />
<br />
$array = $save;<br />
test_time("",1);<br />
reset($array);<br />
while ($next = next($array))<br />
{&nbsp; &nbsp; $key = key($array);<br />
&nbsp;&nbsp;&nbsp; $array[$key] = $array[$key] * 100;<br />
}&nbsp; &nbsp; &nbsp; &nbsp; <br />
test_time("while (\$next = next(\$array))");<br />
*********************************************************<br />
The improvement seems huge, but it isnt that dramatic in real practice. Results varied... I have a very long bidimensional array, and saw no more than a 2 sec diference, but on 140+ second scripts.&nbsp; Notice though that you lose control of the $key&nbsp; value (unless you have numeric keys, which I tend to avoid), but it is not always necessary.&nbsp; <br />
<br />
I generally stick to foreach. However, this time, I was getting Allowed Memory Size Exceeded errors with Apache. Remember foreach copies the original array, so this now makes two huge 2D arrays in memory and alot of work for Apache. If you are getting this error, check your loops. Dont use the whole array on a foreach. Instead get the keys and acces the cells directlly. Also, try and use unset and Referencing on the huge arrays.<br />
<br />
Working on your array and loops is a much better workaround than saving to temporary tables and unsetting (much slower).</span>
</code></div>
  </div>
 </div>
 <a name="33900"></a>
 <div class="note">
  <strong class='user'>Merve</strong>
  <a href="#33900" class="date">10-Jul-2003 09:49</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
This is an easy way for all you calculator creators to make it do factorials. The code is this:<br />
<br />
<span class="default">&lt;?php<br />
$c </span><span class="keyword">= (</span><span class="default">$a</span><span class="keyword">-</span><span class="default">1</span><span class="keyword">);<br />
</span><span class="default">$d </span><span class="keyword">= </span><span class="default">$a</span><span class="keyword">;<br />
while (</span><span class="default">$c</span><span class="keyword">&gt;=</span><span class="default">1</span><span class="keyword">)<br />
{<br />
</span><span class="default">$a </span><span class="keyword">= (</span><span class="default">$a</span><span class="keyword">*</span><span class="default">$c</span><span class="keyword">);<br />
</span><span class="default">$c</span><span class="keyword">--;<br />
}<br />
print (</span><span class="string">" $d! = $a"</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span><br />
$a changes, and so does c, so we have to make a new variable, $d, for the end statement.</span>
</code></div>
  </div>
 </div>
 <a name="33436"></a>
 <div class="note">
  <strong class='user'>bens at effortlessis dot com</strong>
  <a href="#33436" class="date">25-Jun-2003 05:32</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I recently did a performance analysis, comparing while() and foreach() when traversing an array. <br />
<br />
Foreach() is nearly 2x faster - an effect most notable when traversing large, multi-dimensional arrays. <br />
<br />
Here's my code: <br />
&lt;?<br />
<br />
for ($i=0; $i&lt;10000; $i++)<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; $a[$i]=$i*2;<br />
<br />
echo "list time: \n".$start=mktime()."\n";<br />
for ($i=0; $i&lt;1000; $i++)<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; reset($a);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; while(list($k, $v)=each($a))<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; echo "";<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
echo ($finish=mktime())."\n";<br />
$result=$finish-$start;<br />
echo "Result: $result seconds\n\n";<br />
<br />
echo "for time: \n".$start=mktime()."\n";<br />
for ($i=0; $i&lt;1000; $i++)<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; foreach($a as $k =&gt; $v)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; echo "";<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
echo ($finish=mktime())."\n";<br />
$result=$finish-$start;<br />
echo "Result: $result seconds\n\n";<br />
?&gt;<br />
<br />
And here's the results on an 1800+ Athlon: <br />
list time:<br />
1056579474<br />
1056579512<br />
Result: 38 seconds<br />
<br />
fore time:<br />
1056579512<br />
1056579533<br />
Result: 21 seconds</span>
</code></div>
  </div>
 </div>
 <a name="19408"></a>
 <div class="note">
  <strong class='user'>chayes at antenna dot nl</strong>
  <a href="#19408" class="date">26-Feb-2002 02:42</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
At the end of the while (list / each) loop the array pointer will be at the end. <br />
This means the second while loop on that array will be skipped!<br />
<br />
You can put the array pointer back with the reset($myArray) function.<br />
<br />
example: <br />
<br />
<span class="default">&lt;?php<br />
$myArray</span><span class="keyword">=array(</span><span class="string">'aa'</span><span class="keyword">,</span><span class="string">'bb'</span><span class="keyword">,</span><span class="string">'cc'</span><span class="keyword">,</span><span class="string">'dd'</span><span class="keyword">);<br />
&nbsp;while (list (</span><span class="default">$key</span><span class="keyword">, </span><span class="default">$val</span><span class="keyword">) = </span><span class="default">each </span><span class="keyword">(</span><span class="default">$myArray</span><span class="keyword">) ) echo </span><span class="default">$val</span><span class="keyword">; <br />
</span><span class="default">reset</span><span class="keyword">(</span><span class="default">$myArray</span><span class="keyword">);<br />
&nbsp;while (list (</span><span class="default">$key</span><span class="keyword">, </span><span class="default">$val</span><span class="keyword">) = </span><span class="default">each </span><span class="keyword">(</span><span class="default">$myArray</span><span class="keyword">) ) echo </span><span class="default">$val</span><span class="keyword">;<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="19030"></a>
 <div class="note">
  <strong class='user'>moriarty at all-ears dot co dot uk</strong>
  <a href="#19030" class="date">13-Feb-2002 12:58</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you want to skip an iteration of a while loop, you can use continue.<br />
<br />
This will result in the rest of the present iteration being skipped, and it will go back to the start of the loop for the next iteration.<br />
<br />
Moriarty</span>
</code></div>
  </div>
 </div>
 <a name="12246"></a>
 <div class="note">
  <strong class='user'>yohgaki at hotmail dot com</strong>
  <a href="#12246" class="date">01-Apr-2001 01:09</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you want to traverse array, foreach() is faster than while() a little.<br />
[Benched with PHP4.0.4pl1/Apache DSO/Linux]<br />
<br />
i.e.<br />
foreach ($array as $k =&gt; $v)<br />
is a little faster than<br />
while (list($k,$v) = each($array))<br />
<br />
You might want to use foreach for large arrays.</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=control-structures.while&amp;redirect=http://www.php.net/manual/en/control-structures.while.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=control-structures.while&amp;redirect=http://www.php.net/manual/en/control-structures.while.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/control-structures.while.php">show source</a> |
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