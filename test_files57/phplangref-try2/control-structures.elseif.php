<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: elseif/else if - Manual</title>
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
 <link rel="prev" href="control-structures.else.php" />
 <link rel="next" href="control-structures.alternative-syntax.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/elseif" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/control-structures.elseif.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/control-structures.elseif.php" />
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
 <li class="active"><a href="control-structures.elseif.php">elseif/else if</a></li>
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
  <a href="control-structures.alternative-syntax.php">Alternative syntax for control structures<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="control-structures.else.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />else</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/control-structures.elseif.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/control-structures.elseif.php">Brazilian Portuguese</option>
    <option value="zh/control-structures.elseif.php">Chinese (Simplified)</option>
    <option value="fr/control-structures.elseif.php">French</option>
    <option value="de/control-structures.elseif.php">German</option>
    <option value="ja/control-structures.elseif.php">Japanese</option>
    <option value="pl/control-structures.elseif.php">Polish</option>
    <option value="ro/control-structures.elseif.php">Romanian</option>
    <option value="ru/control-structures.elseif.php">Russian</option>
    <option value="fa/control-structures.elseif.php">Persian</option>
    <option value="es/control-structures.elseif.php">Spanish</option>
    <option value="tr/control-structures.elseif.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="control-structures.elseif" class="sect1">
 <h2 class="title"><em>elseif</em>/<em>else if</em></h2>
 <p class="verinfo">(PHP 4, PHP 5)</p>
 <p class="para">
  <em>elseif</em>, as its name suggests, is a combination
  of <em>if</em> and <em>else</em>.  Like
  <em>else</em>, it extends an <em>if</em>
  statement to execute a different statement in case the original
  <em>if</em> expression evaluates to
  <strong><code>FALSE</code></strong>.  However, unlike
  <em>else</em>, it will execute that alternative
  expression only if the <em>elseif</em> conditional
  expression evaluates to <strong><code>TRUE</code></strong>.  For example, the
  following code would display <span class="computeroutput">a is bigger than
  b</span>, <span class="computeroutput">a equal to b</span>
  or <span class="computeroutput">a is smaller than b</span>:
  <div class="informalexample">
   <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">if&nbsp;(</span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">&gt;&nbsp;</span><span style="color: #0000BB">$b</span><span style="color: #007700">)&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"a&nbsp;is&nbsp;bigger&nbsp;than&nbsp;b"</span><span style="color: #007700">;<br />}&nbsp;elseif&nbsp;(</span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">==&nbsp;</span><span style="color: #0000BB">$b</span><span style="color: #007700">)&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"a&nbsp;is&nbsp;equal&nbsp;to&nbsp;b"</span><span style="color: #007700">;<br />}&nbsp;else&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"a&nbsp;is&nbsp;smaller&nbsp;than&nbsp;b"</span><span style="color: #007700">;<br />}<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
   </div>

  </div>
 </p>
 <p class="simpara">
  There may be several <em>elseif</em>s within the same
  <em>if</em> statement.  The first
  <em>elseif</em> expression (if any) that evaluates to
  <strong><code>TRUE</code></strong> would be executed.  In PHP, you can also
  write &#039;else if&#039; (in two words) and the behavior would be identical
  to the one of &#039;elseif&#039; (in a single word).  The syntactic meaning
  is slightly different (if you&#039;re familiar with C, this is the same
  behavior) but the bottom line is that both would result in exactly
  the same behavior.
 </p>
 <p class="simpara">
  The <em>elseif</em> statement is only executed if the
  preceding <em>if</em> expression and any preceding
  <em>elseif</em> expressions evaluated to
  <strong><code>FALSE</code></strong>, and the current
  <em>elseif</em> expression evaluated to
  <strong><code>TRUE</code></strong>.
 </p>
 <blockquote class="note"><p><strong class="note">Note</strong>: 
  <span class="simpara">
   Note that <em>elseif</em> and <em>else if</em>
   will only be considered exactly the same when using curly brackets
   as in the above example.  When using a colon to define your
   <em>if</em>/<em>elseif</em> conditions, you must
   not separate <em>else if</em> into two words, or PHP will
   fail with a parse error.
  </span>
 </p></blockquote>
 <p class="para">
  <div class="informalexample">
   <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /><br /></span><span style="color: #FF8000">/*&nbsp;Incorrect&nbsp;Method:&nbsp;*/<br /></span><span style="color: #007700">if(</span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">&gt;&nbsp;</span><span style="color: #0000BB">$b</span><span style="color: #007700">):<br />&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #0000BB">$a</span><span style="color: #007700">.</span><span style="color: #DD0000">"&nbsp;is&nbsp;greater&nbsp;than&nbsp;"</span><span style="color: #007700">.</span><span style="color: #0000BB">$b</span><span style="color: #007700">;<br />else&nbsp;if(</span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">==&nbsp;</span><span style="color: #0000BB">$b</span><span style="color: #007700">):&nbsp;</span><span style="color: #FF8000">//&nbsp;Will&nbsp;not&nbsp;compile.<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">echo&nbsp;</span><span style="color: #DD0000">"The&nbsp;above&nbsp;line&nbsp;causes&nbsp;a&nbsp;parse&nbsp;error."</span><span style="color: #007700">;<br />endif;<br /><br /><br /></span><span style="color: #FF8000">/*&nbsp;Correct&nbsp;Method:&nbsp;*/<br /></span><span style="color: #007700">if(</span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">&gt;&nbsp;</span><span style="color: #0000BB">$b</span><span style="color: #007700">):<br />&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #0000BB">$a</span><span style="color: #007700">.</span><span style="color: #DD0000">"&nbsp;is&nbsp;greater&nbsp;than&nbsp;"</span><span style="color: #007700">.</span><span style="color: #0000BB">$b</span><span style="color: #007700">;<br />elseif(</span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">==&nbsp;</span><span style="color: #0000BB">$b</span><span style="color: #007700">):&nbsp;</span><span style="color: #FF8000">//&nbsp;Note&nbsp;the&nbsp;combination&nbsp;of&nbsp;the&nbsp;words.<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">echo&nbsp;</span><span style="color: #0000BB">$a</span><span style="color: #007700">.</span><span style="color: #DD0000">"&nbsp;equals&nbsp;"</span><span style="color: #007700">.</span><span style="color: #0000BB">$b</span><span style="color: #007700">;<br />else:<br />&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #0000BB">$a</span><span style="color: #007700">.</span><span style="color: #DD0000">"&nbsp;is&nbsp;neither&nbsp;greater&nbsp;than&nbsp;or&nbsp;equal&nbsp;to&nbsp;"</span><span style="color: #007700">.</span><span style="color: #0000BB">$b</span><span style="color: #007700">;<br />endif;<br /><br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
   </div>

  </div>
 </p>
</div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="control-structures.alternative-syntax.php">Alternative syntax for control structures<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="control-structures.else.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />else</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/control-structures.elseif.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=control-structures.elseif&amp;redirect=http://www.php.net/manual/en/control-structures.elseif.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=control-structures.elseif&amp;redirect=http://www.php.net/manual/en/control-structures.elseif.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>elseif/else if</strong>
 </div><div id="allnotes">
 <a name="99916"></a>
 <div class="note">
  <strong class='user'>Rudi</strong>
  <a href="#99916" class="date">14-Sep-2010 01:15</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Note that } elseif() { is somewhat faster than } else if() {<br />
<br />
===================================<br />
Test (100,000,000 runs):<br />
<span class="default">&lt;?php<br />
$start </span><span class="keyword">= </span><span class="default">microtime</span><span class="keyword">(</span><span class="default">true</span><span class="keyword">);<br />
for(</span><span class="default">$i </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">; </span><span class="default">$i </span><span class="keyword">&lt; </span><span class="default">100000000</span><span class="keyword">; </span><span class="default">$i</span><span class="keyword">++) {<br />
&nbsp;&nbsp;&nbsp; if(</span><span class="default">2 </span><span class="keyword">=== </span><span class="default">0</span><span class="keyword">) {} else if(</span><span class="default">2 </span><span class="keyword">=== </span><span class="default">1</span><span class="keyword">) {} else {}<br />
}<br />
</span><span class="default">$end </span><span class="keyword">= </span><span class="default">microtime</span><span class="keyword">(</span><span class="default">true</span><span class="keyword">);<br />
echo </span><span class="string">"1: "</span><span class="keyword">.(</span><span class="default">$end </span><span class="keyword">- </span><span class="default">$start</span><span class="keyword">).</span><span class="string">"\n"</span><span class="keyword">;<br />
unset(</span><span class="default">$start</span><span class="keyword">, </span><span class="default">$end</span><span class="keyword">);<br />
<br />
</span><span class="default">$start </span><span class="keyword">= </span><span class="default">microtime</span><span class="keyword">(</span><span class="default">true</span><span class="keyword">);<br />
for(</span><span class="default">$i </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">; </span><span class="default">$i </span><span class="keyword">&lt; </span><span class="default">100000000</span><span class="keyword">; </span><span class="default">$i</span><span class="keyword">++) {<br />
&nbsp;&nbsp;&nbsp; if(</span><span class="default">2 </span><span class="keyword">=== </span><span class="default">0</span><span class="keyword">) {} elseif(</span><span class="default">2 </span><span class="keyword">=== </span><span class="default">1</span><span class="keyword">) {} else {}<br />
}<br />
</span><span class="default">$end </span><span class="keyword">= </span><span class="default">microtime</span><span class="keyword">(</span><span class="default">true</span><span class="keyword">);<br />
echo </span><span class="string">"2: "</span><span class="keyword">.(</span><span class="default">$end </span><span class="keyword">- </span><span class="default">$start</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span><br />
===================================<br />
Result (depending on hardware configuration):<br />
1: 20.026723146439<br />
2: 20.20437502861</span>
</code></div>
  </div>
 </div>
 <a name="72769"></a>
 <div class="note">
  <a href="#72769" class="date">31-Jan-2007 02:54</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
There is no good way to interpret the dangling else.&nbsp; One must pick a way and apply rules based on that.&nbsp; <br />
<br />
Since there is no endif before an else, there is no easy way for PHP to know what you mean.</span>
</code></div>
  </div>
 </div>
 <a name="71982"></a>
 <div class="note">
  <strong class='user'>Vladimir Kornea</strong>
  <a href="#71982" class="date">27-Dec-2006 09:59</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The parser doesn't handle mixing alternative if syntaxes as reasonably as possible.<br />
<br />
The following is illegal (as it should be):<br />
<br />
&lt;?<br />
if($a):<br />
&nbsp;&nbsp;&nbsp; echo $a;<br />
else {<br />
&nbsp;&nbsp;&nbsp; echo $c;<br />
}<br />
?&gt;<br />
<br />
This is also illegal (as it should be):<br />
<br />
&lt;?<br />
if($a) {<br />
&nbsp;&nbsp;&nbsp; echo $a;<br />
}<br />
else:<br />
&nbsp;&nbsp;&nbsp; echo $c;<br />
endif;<br />
?&gt;<br />
<br />
But since the two alternative if syntaxes are not interchangeable, it's reasonable to expect that the parser wouldn't try matching else statements using one style to if statement using the alternative style. In other words, one would expect that this would work:<br />
<br />
&lt;?<br />
if($a):<br />
&nbsp;&nbsp;&nbsp; echo $a;<br />
&nbsp;&nbsp;&nbsp; if($b) {<br />
&nbsp;&nbsp; &nbsp;&nbsp; echo $b;<br />
&nbsp;&nbsp;&nbsp; }<br />
else:<br />
&nbsp;&nbsp;&nbsp; echo $c;<br />
endif;<br />
?&gt;<br />
<br />
Instead of concluding that the else statement was intended to match the if($b) statement (and erroring out), the parser could match the else statement to the if($a) statement, which shares its syntax.<br />
<br />
While it's understandable that the PHP developers don't consider this a bug, or don't consider it a bug worth their time, jsimlo was right to point out that mixing alternative if syntaxes might lead to unexpected results.</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=control-structures.elseif&amp;redirect=http://www.php.net/manual/en/control-structures.elseif.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=control-structures.elseif&amp;redirect=http://www.php.net/manual/en/control-structures.elseif.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/control-structures.elseif.php">show source</a> |
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