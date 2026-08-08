<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: for - Manual</title>
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
 <link rel="prev" href="control-structures.do.while.php" />
 <link rel="next" href="control-structures.foreach.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/for" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/control-structures.for.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/control-structures.for.php" />
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
 <li><a href="control-structures.while.php">while</a></li>
 <li><a href="control-structures.do.while.php">do-while</a></li>
 <li class="active"><a href="control-structures.for.php">for</a></li>
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
  <a href="control-structures.foreach.php">foreach<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="control-structures.do.while.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />do-while</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/control-structures.for.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/control-structures.for.php">Brazilian Portuguese</option>
    <option value="zh/control-structures.for.php">Chinese (Simplified)</option>
    <option value="fr/control-structures.for.php">French</option>
    <option value="de/control-structures.for.php">German</option>
    <option value="ja/control-structures.for.php">Japanese</option>
    <option value="pl/control-structures.for.php">Polish</option>
    <option value="ro/control-structures.for.php">Romanian</option>
    <option value="ru/control-structures.for.php">Russian</option>
    <option value="fa/control-structures.for.php">Persian</option>
    <option value="es/control-structures.for.php">Spanish</option>
    <option value="tr/control-structures.for.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="control-structures.for" class="sect1">
 <h2 class="title"><em>for</em></h2>
 <p class="verinfo">(PHP 4, PHP 5)</p>
 <p class="para">
  <em>for</em> loops are the most complex loops in PHP.
  They behave like their C counterparts.  The syntax of a
  <em>for</em> loop is:
  <div class="informalexample">
   <div class="example-contents">
<div class="cdata"><pre>
for (expr1; expr2; expr3)
    statement
</pre></div>
   </div>

  </div>
 </p>
 <p class="simpara">
  The first expression (<var class="varname"><var class="varname">expr1</var></var>) is
  evaluated (executed) once unconditionally at the beginning of the
  loop.
 </p>
 <p class="simpara">
  In the beginning of each iteration,
  <var class="varname"><var class="varname">expr2</var></var> is evaluated.  If it evaluates to
  <strong><code>TRUE</code></strong>, the loop continues and the nested
  statement(s) are executed.  If it evaluates to
  <strong><code>FALSE</code></strong>, the execution of the loop ends.
 </p>
 <p class="simpara">
  At the end of each iteration, <var class="varname"><var class="varname">expr3</var></var> is
  evaluated (executed).
 </p>
 <p class="simpara">
  Each of the expressions can be empty or contain multiple
  expressions separated by commas. In <var class="varname"><var class="varname">expr2</var></var>, all
  expressions separated by a comma are evaluated but the result is taken
  from the last part.
  <var class="varname"><var class="varname">expr2</var></var> being empty means the loop should
  be run indefinitely (PHP implicitly considers it as
  <strong><code>TRUE</code></strong>, like C).  This may not be as useless as
  you might think, since often you&#039;d want to end the loop using a
  conditional <a href="control-structures.break.php" class="link"><em>break</em></a>
  statement instead of using the <em>for</em> truth
  expression.
 </p>
 <p class="para">
  Consider the following examples.  All of them display the numbers
  1 through 10:
  <div class="informalexample">
   <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #FF8000">/*&nbsp;example&nbsp;1&nbsp;*/<br /><br /></span><span style="color: #007700">for&nbsp;(</span><span style="color: #0000BB">$i&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">1</span><span style="color: #007700">;&nbsp;</span><span style="color: #0000BB">$i&nbsp;</span><span style="color: #007700">&lt;=&nbsp;</span><span style="color: #0000BB">10</span><span style="color: #007700">;&nbsp;</span><span style="color: #0000BB">$i</span><span style="color: #007700">++)&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #0000BB">$i</span><span style="color: #007700">;<br />}<br /><br /></span><span style="color: #FF8000">/*&nbsp;example&nbsp;2&nbsp;*/<br /><br /></span><span style="color: #007700">for&nbsp;(</span><span style="color: #0000BB">$i&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">1</span><span style="color: #007700">;&nbsp;;&nbsp;</span><span style="color: #0000BB">$i</span><span style="color: #007700">++)&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;if&nbsp;(</span><span style="color: #0000BB">$i&nbsp;</span><span style="color: #007700">&gt;&nbsp;</span><span style="color: #0000BB">10</span><span style="color: #007700">)&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;break;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #0000BB">$i</span><span style="color: #007700">;<br />}<br /><br /></span><span style="color: #FF8000">/*&nbsp;example&nbsp;3&nbsp;*/<br /><br /></span><span style="color: #0000BB">$i&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">1</span><span style="color: #007700">;<br />for&nbsp;(;&nbsp;;&nbsp;)&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;if&nbsp;(</span><span style="color: #0000BB">$i&nbsp;</span><span style="color: #007700">&gt;&nbsp;</span><span style="color: #0000BB">10</span><span style="color: #007700">)&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;break;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #0000BB">$i</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$i</span><span style="color: #007700">++;<br />}<br /><br /></span><span style="color: #FF8000">/*&nbsp;example&nbsp;4&nbsp;*/<br /><br /></span><span style="color: #007700">for&nbsp;(</span><span style="color: #0000BB">$i&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">1</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$j&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">0</span><span style="color: #007700">;&nbsp;</span><span style="color: #0000BB">$i&nbsp;</span><span style="color: #007700">&lt;=&nbsp;</span><span style="color: #0000BB">10</span><span style="color: #007700">;&nbsp;</span><span style="color: #0000BB">$j&nbsp;</span><span style="color: #007700">+=&nbsp;</span><span style="color: #0000BB">$i</span><span style="color: #007700">,&nbsp;print&nbsp;</span><span style="color: #0000BB">$i</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$i</span><span style="color: #007700">++);<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
   </div>

  </div>
 </p>
 <p class="simpara">
  Of course, the first example appears to be the nicest one (or
  perhaps the fourth), but you may find that being able to use empty
  expressions in <em>for</em> loops comes in handy in many
  occasions.
 </p>
 <p class="para">
  PHP also supports the alternate &quot;colon syntax&quot; for
  <em>for</em> loops.
  <div class="informalexample">
   <div class="example-contents">
<div class="cdata"><pre>
for (expr1; expr2; expr3):
    statement
    ...
endfor;
</pre></div>
   </div>

  </div>
 </p>
 <p class="simpara">
  Its a common thing to many users to iterate though arrays like in the
  example below.
 </p>
 <p class="para">
  <div class="informalexample">
   <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #FF8000">/*<br />*&nbsp;This&nbsp;is&nbsp;an&nbsp;array&nbsp;with&nbsp;some&nbsp;data&nbsp;we&nbsp;want&nbsp;to&nbsp;modify<br />*&nbsp;when&nbsp;running&nbsp;through&nbsp;the&nbsp;for&nbsp;loop.<br />*/<br /></span><span style="color: #0000BB">$people&nbsp;</span><span style="color: #007700">=&nbsp;Array(<br />&nbsp;&nbsp;&nbsp;&nbsp;Array(</span><span style="color: #DD0000">'name'&nbsp;</span><span style="color: #007700">=&gt;&nbsp;</span><span style="color: #DD0000">'Kalle'</span><span style="color: #007700">,&nbsp;</span><span style="color: #DD0000">'salt'&nbsp;</span><span style="color: #007700">=&gt;&nbsp;</span><span style="color: #0000BB">856412</span><span style="color: #007700">),<br />&nbsp;&nbsp;&nbsp;&nbsp;Array(</span><span style="color: #DD0000">'name'&nbsp;</span><span style="color: #007700">=&gt;&nbsp;</span><span style="color: #DD0000">'Pierre'</span><span style="color: #007700">,&nbsp;</span><span style="color: #DD0000">'salt'&nbsp;</span><span style="color: #007700">=&gt;&nbsp;</span><span style="color: #0000BB">215863</span><span style="color: #007700">)<br />);<br /><br />for(</span><span style="color: #0000BB">$i&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">0</span><span style="color: #007700">;&nbsp;</span><span style="color: #0000BB">$i&nbsp;</span><span style="color: #007700">&lt;&nbsp;</span><span style="color: #0000BB">count</span><span style="color: #007700">(</span><span style="color: #0000BB">$people</span><span style="color: #007700">);&nbsp;++</span><span style="color: #0000BB">$i</span><span style="color: #007700">)<br />{<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$people</span><span style="color: #007700">[</span><span style="color: #0000BB">$i</span><span style="color: #007700">][</span><span style="color: #DD0000">'salt'</span><span style="color: #007700">]&nbsp;=&nbsp;</span><span style="color: #0000BB">rand</span><span style="color: #007700">(</span><span style="color: #0000BB">000000</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">999999</span><span style="color: #007700">);<br />}<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
   </div>

  </div>
 </p>
 <p class="simpara">
  The problem lies in the second for expression. This code can be slow
  because it has to calculate the size of the array on each iteration.
  Since the size never changes, it can be optimized easily using an
  intermediate variable to store the size and use in the loop instead
  of count. The example below illustrates this:
 </p>
 <p class="para">
  <div class="informalexample">
   <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br />$people&nbsp;</span><span style="color: #007700">=&nbsp;Array(<br />&nbsp;&nbsp;&nbsp;&nbsp;Array(</span><span style="color: #DD0000">'name'&nbsp;</span><span style="color: #007700">=&gt;&nbsp;</span><span style="color: #DD0000">'Kalle'</span><span style="color: #007700">,&nbsp;</span><span style="color: #DD0000">'salt'&nbsp;</span><span style="color: #007700">=&gt;&nbsp;</span><span style="color: #0000BB">856412</span><span style="color: #007700">),<br />&nbsp;&nbsp;&nbsp;&nbsp;Array(</span><span style="color: #DD0000">'name'&nbsp;</span><span style="color: #007700">=&gt;&nbsp;</span><span style="color: #DD0000">'Pierre'</span><span style="color: #007700">,&nbsp;</span><span style="color: #DD0000">'salt'&nbsp;</span><span style="color: #007700">=&gt;&nbsp;</span><span style="color: #0000BB">215863</span><span style="color: #007700">)<br />);<br /><br />for(</span><span style="color: #0000BB">$i&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">0</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$size&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">count</span><span style="color: #007700">(</span><span style="color: #0000BB">$people</span><span style="color: #007700">);&nbsp;</span><span style="color: #0000BB">$i&nbsp;</span><span style="color: #007700">&lt;&nbsp;</span><span style="color: #0000BB">$size</span><span style="color: #007700">;&nbsp;++</span><span style="color: #0000BB">$i</span><span style="color: #007700">)<br />{<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$people</span><span style="color: #007700">[</span><span style="color: #0000BB">$i</span><span style="color: #007700">][</span><span style="color: #DD0000">'salt'</span><span style="color: #007700">]&nbsp;=&nbsp;</span><span style="color: #0000BB">rand</span><span style="color: #007700">(</span><span style="color: #0000BB">000000</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">999999</span><span style="color: #007700">);<br />}<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
   </div>

  </div>
 </p>
</div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="control-structures.foreach.php">foreach<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="control-structures.do.while.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />do-while</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/control-structures.for.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=control-structures.for&amp;redirect=http://www.php.net/manual/en/control-structures.for.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=control-structures.for&amp;redirect=http://www.php.net/manual/en/control-structures.for.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>for</strong>
 </div><div id="allnotes">
 <a name="107427"></a>
 <div class="note">
  <strong class='user'>matthiaz</strong>
  <a href="#107427" class="date">08-Feb-2012 02:37</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Looping through letters is possible. I'm amazed at how few people know that.<br />
<br />
for($col = 'R'; $col != 'AD'; $col++) {<br />
&nbsp;&nbsp;&nbsp; echo $col.' ';<br />
}<br />
<br />
returns: R S T U V W X Y Z AA AB AC<br />
<br />
Take note that you can't use $col &lt; 'AD'. It only works with !=<br />
Very convenient when working with excel columns.</span>
</code></div>
  </div>
 </div>
 <a name="96893"></a>
 <div class="note">
  <strong class='user'>kanirockz at gmail dot com</strong>
  <a href="#96893" class="date">21-Mar-2010 11:49</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Here is another simple example for " for loops"<br />
<br />
<span class="default">&lt;?php<br />
<br />
$text</span><span class="keyword">=</span><span class="string">"Welcome to PHP"</span><span class="keyword">;<br />
</span><span class="default">$searchchar</span><span class="keyword">=</span><span class="string">"e"</span><span class="keyword">;<br />
</span><span class="default">$count</span><span class="keyword">=</span><span class="string">"0"</span><span class="keyword">; </span><span class="comment">//zero<br />
<br />
</span><span class="keyword">for(</span><span class="default">$i</span><span class="keyword">=</span><span class="string">"0"</span><span class="keyword">; </span><span class="default">$i</span><span class="keyword">&lt;</span><span class="default">strlen</span><span class="keyword">(</span><span class="default">$text</span><span class="keyword">); </span><span class="default">$i</span><span class="keyword">=</span><span class="default">$i</span><span class="keyword">+</span><span class="default">1</span><span class="keyword">){<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; if(</span><span class="default">substr</span><span class="keyword">(</span><span class="default">$text</span><span class="keyword">,</span><span class="default">$i</span><span class="keyword">,</span><span class="default">1</span><span class="keyword">)==</span><span class="default">$searchchar</span><span class="keyword">){<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp; </span><span class="default">$count</span><span class="keyword">=</span><span class="default">$count</span><span class="keyword">+</span><span class="default">1</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
}<br />
<br />
echo </span><span class="default">$count<br />
<br />
?&gt;<br />
</span><br />
this will be count how many "e" characters in that text (Welcome to PHP)</span>
</code></div>
  </div>
 </div>
 <a name="96892"></a>
 <div class="note">
  <strong class='user'>kanirockz at gmail dot com</strong>
  <a href="#96892" class="date">21-Mar-2010 11:48</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Here is another simple example for " for loops"<br />
<br />
<span class="default">&lt;?php<br />
<br />
$text</span><span class="keyword">=</span><span class="string">"Welcome to PHP"</span><span class="keyword">;<br />
</span><span class="default">$searchchar</span><span class="keyword">=</span><span class="string">"e"</span><span class="keyword">;<br />
</span><span class="default">$count</span><span class="keyword">=</span><span class="string">"0"</span><span class="keyword">; </span><span class="comment">//zero<br />
<br />
</span><span class="keyword">for(</span><span class="default">$i</span><span class="keyword">=</span><span class="string">"0"</span><span class="keyword">; </span><span class="default">$i</span><span class="keyword">&lt;</span><span class="default">strlen</span><span class="keyword">(</span><span class="default">$text</span><span class="keyword">); </span><span class="default">$i</span><span class="keyword">=</span><span class="default">$i</span><span class="keyword">+</span><span class="default">1</span><span class="keyword">){<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; if(</span><span class="default">substr</span><span class="keyword">(</span><span class="default">$text</span><span class="keyword">,</span><span class="default">$i</span><span class="keyword">,</span><span class="default">1</span><span class="keyword">)==</span><span class="default">$searchchar</span><span class="keyword">){<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp; </span><span class="default">$count</span><span class="keyword">=</span><span class="default">$count</span><span class="keyword">+</span><span class="default">1</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
}<br />
<br />
echo </span><span class="default">$count<br />
<br />
?&gt;<br />
</span><br />
this will be count how many "e" characters in that text (Welcome to PHP)</span>
</code></div>
  </div>
 </div>
 <a name="88117"></a>
 <div class="note">
  <strong class='user'>Steven</strong>
  <a href="#88117" class="date">11-Jan-2009 10:50</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Alternating form rows:<br />
<br />
<span class="default">&lt;?php<br />
<br />
$rows </span><span class="keyword">= </span><span class="default">4</span><span class="keyword">;<br />
<br />
echo </span><span class="string">'&lt;table&gt;&lt;tr&gt;'</span><span class="keyword">;<br />
<br />
for(</span><span class="default">$i </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">; </span><span class="default">$i </span><span class="keyword">&lt; </span><span class="default">10</span><span class="keyword">; </span><span class="default">$i</span><span class="keyword">++){<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="string">'&lt;td&gt;' </span><span class="keyword">. </span><span class="default">$i </span><span class="keyword">. </span><span class="string">'&lt;/td&gt;'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; if((</span><span class="default">$i </span><span class="keyword">+ </span><span class="default">1</span><span class="keyword">) % </span><span class="default">$rows </span><span class="keyword">== </span><span class="default">0</span><span class="keyword">){<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">'&lt;/tr&gt;&lt;tr&gt;'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
echo </span><span class="string">'&lt;/tr&gt;&lt;/table&gt;'</span><span class="keyword">;<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
Changing $rows will change how many columns are in a row.</span>
</code></div>
  </div>
 </div>
 <a name="88051"></a>
 <div class="note">
  <strong class='user'>dkimbel13 at gmail dot com</strong>
  <a href="#88051" class="date">07-Jan-2009 03:41</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Just a note on looping through an array using the for() loop.<br />
<br />
with the array...<br />
<span class="default">&lt;?php $array </span><span class="keyword">= array(</span><span class="string">"value1"</span><span class="keyword">,</span><span class="string">"value2"</span><span class="keyword">,</span><span class="string">"value3"</span><span class="keyword">); </span><span class="default">?&gt;<br />
</span><br />
then...<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">for(</span><span class="default">reset</span><span class="keyword">(</span><span class="default">$array</span><span class="keyword">),</span><span class="default">current</span><span class="keyword">(</span><span class="default">$array</span><span class="keyword">),</span><span class="default">next</span><span class="keyword">(</span><span class="default">$array</span><span class="keyword">){<br />
&nbsp;&nbsp;&nbsp; echo(</span><span class="string">"Element "</span><span class="keyword">.</span><span class="default">key</span><span class="keyword">(</span><span class="default">$array</span><span class="keyword">).</span><span class="string">" contains "</span><span class="keyword">.</span><span class="default">current</span><span class="keyword">(</span><span class="default">$array</span><span class="keyword">).</span><span class="string">"&lt;br/&gt;"</span><span class="keyword">;<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
is the equivalent of...<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">for(</span><span class="default">$i</span><span class="keyword">=</span><span class="default">0</span><span class="keyword">;</span><span class="default">$i</span><span class="keyword">&lt;</span><span class="default">count</span><span class="keyword">(</span><span class="default">$array</span><span class="keyword">);</span><span class="default">$i</span><span class="keyword">++){<br />
&nbsp;&nbsp;&nbsp; echo(</span><span class="string">"Element $i contains $array[$i]&lt;br/&gt;"</span><span class="keyword">);<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
I don't know if there is any advantage, just thought I would mention it.</span>
</code></div>
  </div>
 </div>
 <a name="82007"></a>
 <div class="note">
  <strong class='user'>http://badluck.tv</strong>
  <a href="#82007" class="date">24-Mar-2008 05:05</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Nested For Loop with the same iterator as the parent.<br />
(Well formatted so the resulting code is clean when executed).<br />
Useful for outputting a data array into a table, ie. images.<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="comment">//Dummy data<br />
</span><span class="default">$data </span><span class="keyword">= array(</span><span class="default">73</span><span class="keyword">,</span><span class="default">74</span><span class="keyword">,</span><span class="default">75</span><span class="keyword">,</span><span class="default">76</span><span class="keyword">,</span><span class="default">78</span><span class="keyword">,</span><span class="default">79</span><span class="keyword">,</span><span class="default">80</span><span class="keyword">,</span><span class="default">81</span><span class="keyword">,</span><span class="default">82</span><span class="keyword">,</span><span class="default">83</span><span class="keyword">,</span><span class="default">84</span><span class="keyword">,</span><span class="default">85</span><span class="keyword">,</span><span class="default">86</span><span class="keyword">,</span><span class="default">87</span><span class="keyword">);<br />
<br />
</span><span class="comment">//Our 'stepping' variable<br />
</span><span class="default">$g </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">;<br />
<br />
</span><span class="comment">//Our rowcount<br />
</span><span class="default">$rowcount </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">;<br />
<br />
echo </span><span class="string">"&lt;table cellspacing='0'&gt;\r"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; for (</span><span class="default">$i</span><span class="keyword">=</span><span class="default">0</span><span class="keyword">; </span><span class="default">$i</span><span class="keyword">&lt;</span><span class="default">count</span><span class="keyword">(</span><span class="default">$data</span><span class="keyword">); ) {<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$rowcount</span><span class="keyword">++;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">"&nbsp; &nbsp; &lt;tr&gt;\r"</span><span class="keyword">; </span><span class="comment">//New row<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$g </span><span class="keyword">= </span><span class="default">$i </span><span class="keyword">+ </span><span class="default">3</span><span class="keyword">; </span><span class="comment">//Set our nested limit<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">for( ; </span><span class="default">$i</span><span class="keyword">&lt;</span><span class="default">$g</span><span class="keyword">; </span><span class="default">$i</span><span class="keyword">++) { </span><span class="comment">//nested for loop<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">if (!isset(</span><span class="default">$data</span><span class="keyword">[</span><span class="default">$i</span><span class="keyword">])) { </span><span class="comment">//Allow us to break on incomplete rows<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">break;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">"&nbsp; &nbsp; &nbsp; &nbsp; &lt;td style='border: 1px #000 solid;'&gt;\r"</span><span class="keyword">; </span><span class="comment">//Out put a cell<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">echo </span><span class="string">"&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;p&gt;Row $rowcount &lt;br/&gt; Cell: $i &lt;br/&gt; Data: $data[$i]&lt;/p&gt;\r"</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">"&nbsp; &nbsp; &nbsp; &nbsp; &lt;/td&gt;\r"</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">"&nbsp; &nbsp; &lt;/tr&gt; \r"</span><span class="keyword">; </span><span class="comment">//End New Row<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">}<br />
<br />
echo </span><span class="string">"&lt;/table&gt;\r"</span><span class="keyword">;</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="75754"></a>
 <div class="note">
  <strong class='user'>eduardofleury at uol dot com dot br</strong>
  <a href="#75754" class="date">14-Jun-2007 06:18</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
<span class="default">&lt;?php<br />
</span><span class="comment">//this is a different way to use the 'for'<br />
//Essa é uma maneira diferente de usar o 'for'<br />
</span><span class="keyword">for(</span><span class="default">$i </span><span class="keyword">= </span><span class="default">$x </span><span class="keyword">= </span><span class="default">$z </span><span class="keyword">= </span><span class="default">1</span><span class="keyword">; </span><span class="default">$i </span><span class="keyword">&lt;= </span><span class="default">10</span><span class="keyword">;</span><span class="default">$i</span><span class="keyword">++,</span><span class="default">$x</span><span class="keyword">+=</span><span class="default">2</span><span class="keyword">,</span><span class="default">$z</span><span class="keyword">=&amp;</span><span class="default">$p</span><span class="keyword">){<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$p </span><span class="keyword">= </span><span class="default">$i </span><span class="keyword">+ </span><span class="default">$x</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; print </span><span class="string">"\$i = $i , \$x = $x , \$z = $z &lt;br /&gt;"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; <br />
}<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="69525"></a>
 <div class="note">
  <strong class='user'>lishevita at yahoo dot co (notcom) .uk</strong>
  <a href="#69525" class="date">08-Sep-2006 12:33</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
On the combination problem again...<br />
<br />
&nbsp;It seems to me like it would make more sense to go through systematically. That would take nested for loops, where each number was put through all of it's potentials sequentially. <br />
<br />
The following would give you all of the potential combinations of a four-digit decimal combination, printed in a comma delimited format:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">for(</span><span class="default">$a</span><span class="keyword">=</span><span class="default">0</span><span class="keyword">;</span><span class="default">$a</span><span class="keyword">&lt;</span><span class="default">10</span><span class="keyword">;</span><span class="default">$a</span><span class="keyword">++){<br />
&nbsp;&nbsp;&nbsp; for(</span><span class="default">$b</span><span class="keyword">=</span><span class="default">0</span><span class="keyword">;</span><span class="default">$b</span><span class="keyword">&lt;</span><span class="default">10</span><span class="keyword">;</span><span class="default">$b</span><span class="keyword">++){<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; for(</span><span class="default">$c</span><span class="keyword">=</span><span class="default">0</span><span class="keyword">;</span><span class="default">$c</span><span class="keyword">&lt;</span><span class="default">10</span><span class="keyword">;</span><span class="default">$c</span><span class="keyword">++){<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; for(</span><span class="default">$d</span><span class="keyword">=</span><span class="default">0</span><span class="keyword">;</span><span class="default">$d</span><span class="keyword">&lt;</span><span class="default">10</span><span class="keyword">;</span><span class="default">$d</span><span class="keyword">++){<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="default">$a</span><span class="keyword">.</span><span class="default">$b</span><span class="keyword">.</span><span class="default">$c</span><span class="keyword">.</span><span class="default">$d</span><span class="keyword">.</span><span class="string">", "</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; }<br />
&nbsp;&nbsp; &nbsp;&nbsp; }<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
Of course, if you know that the numbers you had used were in a smaller subset, you could just plunk your possible numbers into arrays $a, $b, $c, and $d and then do nested foreach loops as above.<br />
<br />
- Elizabeth</span>
</code></div>
  </div>
 </div>
 <a name="55494"></a>
 <div class="note">
  <strong class='user'>JustinB at harvest dot org</strong>
  <a href="#55494" class="date">04-Aug-2005 04:23</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
For those who are having issues with needing to evaluate multiple items in expression two, please note that it cannot be chained like expressions one and three can.&nbsp; Although many have stated this fact, most have not stated that there is still a way to do this:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">for(</span><span class="default">$i </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">, </span><span class="default">$x </span><span class="keyword">= </span><span class="default">$nums</span><span class="keyword">[</span><span class="string">'x_val'</span><span class="keyword">], </span><span class="default">$n </span><span class="keyword">= </span><span class="default">15</span><span class="keyword">; (</span><span class="default">$i </span><span class="keyword">&lt; </span><span class="default">23 </span><span class="keyword">&amp;&amp; </span><span class="default">$number </span><span class="keyword">!= </span><span class="default">24</span><span class="keyword">); </span><span class="default">$i</span><span class="keyword">++, </span><span class="default">$x </span><span class="keyword">+ </span><span class="default">5</span><span class="keyword">;) {<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">// Do Something with All Those Fun Numbers<br />
</span><span class="keyword">}<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="41670"></a>
 <div class="note">
  <strong class='user'>user at host dot com</strong>
  <a href="#41670" class="date">19-Apr-2004 03:53</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Also acceptable:<br />
<br />
<span class="default">&lt;?php<br />
&nbsp; </span><span class="keyword">for(</span><span class="default">$letter </span><span class="keyword">= </span><span class="default">ord</span><span class="keyword">(</span><span class="string">'a'</span><span class="keyword">); </span><span class="default">$letter </span><span class="keyword">&lt;= </span><span class="default">ord</span><span class="keyword">(</span><span class="string">'z'</span><span class="keyword">); </span><span class="default">$letter</span><span class="keyword">++)<br />
&nbsp;&nbsp; print </span><span class="default">chr</span><span class="keyword">(</span><span class="default">$letter</span><span class="keyword">);<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="34178"></a>
 <div class="note">
  <strong class='user'>bishop</strong>
  <a href="#34178" class="date">17-Jul-2003 01:23</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you're already using the fastest algorithms you can find (on the order of O(1), O(n), or O(n log n)), and you're still worried about loop speed, unroll your loops using e.g., Duff's Device:<br />
<br />
<span class="default">&lt;?php<br />
$n </span><span class="keyword">= </span><span class="default">$ITERATIONS </span><span class="keyword">% </span><span class="default">8</span><span class="keyword">;<br />
while (</span><span class="default">$n</span><span class="keyword">--) </span><span class="default">$val</span><span class="keyword">++;<br />
</span><span class="default">$n </span><span class="keyword">= (int)(</span><span class="default">$ITERATIONS </span><span class="keyword">/ </span><span class="default">8</span><span class="keyword">);<br />
while (</span><span class="default">$n</span><span class="keyword">--) {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$val</span><span class="keyword">++;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$val</span><span class="keyword">++;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$val</span><span class="keyword">++;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$val</span><span class="keyword">++;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$val</span><span class="keyword">++;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$val</span><span class="keyword">++;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$val</span><span class="keyword">++;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$val</span><span class="keyword">++;<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
(This is a modified form of Duff's original device, because PHP doesn't understand the original's egregious syntax.)<br />
<br />
That's algorithmically equivalent to the common form:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">for (</span><span class="default">$i </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">; </span><span class="default">$i </span><span class="keyword">&lt; </span><span class="default">$ITERATIONS</span><span class="keyword">; </span><span class="default">$i</span><span class="keyword">++) {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$val</span><span class="keyword">++;<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
$val++ can be whatever operation you need to perform ITERATIONS number of times.<br />
<br />
On my box, with no users, average run time across 100 samples with ITERATIONS = 10000000 (10 million) is:<br />
Duff version:&nbsp; &nbsp; &nbsp;&nbsp; 7.9857 s<br />
Obvious version: 27.608 s</span>
</code></div>
  </div>
 </div>
 <a name="13463"></a>
 <div class="note">
  <strong class='user'>nzamani at cyberworldz dot de</strong>
  <a href="#13463" class="date">17-Jun-2001 11:47</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The point about the speed in loops is, that the middle and the last expression are executed EVERY time it loops.<br />
So you should try to take everything that doesn't change out of the loop.<br />
Often you use a function to check the maximum of times it should loop. Like here:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">for (</span><span class="default">$i </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">; </span><span class="default">$i </span><span class="keyword">&lt;= </span><span class="default">somewhat_calcMax</span><span class="keyword">(); </span><span class="default">$i</span><span class="keyword">++) {<br />
&nbsp; </span><span class="default">somewhat_doSomethingWith</span><span class="keyword">(</span><span class="default">$i</span><span class="keyword">);<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
Faster would be:<br />
<br />
<span class="default">&lt;?php<br />
$maxI </span><span class="keyword">= </span><span class="default">somewhat_calcMax</span><span class="keyword">();<br />
for (</span><span class="default">$i </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">; </span><span class="default">$i </span><span class="keyword">&lt;= </span><span class="default">$maxI</span><span class="keyword">; </span><span class="default">$i</span><span class="keyword">++) {<br />
&nbsp; </span><span class="default">somewhat_doSomethingWith</span><span class="keyword">(</span><span class="default">$i</span><span class="keyword">);<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
And here a little trick:<br />
<br />
<span class="default">&lt;?php<br />
$maxI </span><span class="keyword">= </span><span class="default">somewhat_calcMax</span><span class="keyword">();<br />
for (</span><span class="default">$i </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">; </span><span class="default">$i </span><span class="keyword">&lt;= </span><span class="default">$maxI</span><span class="keyword">; </span><span class="default">somewhat_doSomethingWith</span><span class="keyword">(</span><span class="default">$i</span><span class="keyword">++)) ;<br />
</span><span class="default">?&gt;<br />
</span><br />
The $i gets changed after the copy for the function (post-increment).</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=control-structures.for&amp;redirect=http://www.php.net/manual/en/control-structures.for.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=control-structures.for&amp;redirect=http://www.php.net/manual/en/control-structures.for.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/control-structures.for.php">show source</a> |
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