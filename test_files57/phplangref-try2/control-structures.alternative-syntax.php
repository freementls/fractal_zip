<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Alternative syntax for control structures - Manual</title>
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
 <link rel="prev" href="control-structures.elseif.php" />
 <link rel="next" href="control-structures.while.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/alternative-syntax" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/control-structures.alternative-syntax.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="@w{SDBH83Q4}" />
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
 <li class="active"><a href="control-structures.alternative-syntax.php">Alternative syntax for control structures</a></li>
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
  <a href="control-structures.while.php">while<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="control-structures.elseif.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />elseif/else if</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/control-structures.alternative-syntax.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/control-structures.alternative-syntax.php">Brazilian Portuguese</option>
    <option value="zh/control-structures.alternative-syntax.php">Chinese (Simplified)</option>
    <option value="fr/control-structures.alternative-syntax.php">French</option>
    <option value="de/control-structures.alternative-syntax.php">German</option>
    <option value="ja/control-structures.alternative-syntax.php">Japanese</option>
    <option value="pl/control-structures.alternative-syntax.php">Polish</option>
    <option value="ro/control-structures.alternative-syntax.php">Romanian</option>
    <option value="ru/control-structures.alternative-syntax.php">Russian</option>
    <option value="fa/control-structures.alternative-syntax.php">Persian</option>
    <option value="es/control-structures.alternative-syntax.php">Spanish</option>
    <option value="tr/control-structures.alternative-syntax.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="control-structures.alternative-syntax" class="sect1">
 <h2 class="title">Alternative syntax for control structures</h2>
 <p class="verinfo">(PHP 4, PHP 5)</p>
 <p class="para">
  PHP offers an alternative syntax for some of its control
  structures; namely, <em>if</em>,
  <em>while</em>, <em>for</em>,
  <em>foreach</em>, and <em>switch</em>.
  In each case, the basic form of the alternate syntax is to change
  the opening brace to a colon (:) and the closing brace to
  <em>endif;</em>, <em>endwhile;</em>,
  <em>endfor;</em>, <em>endforeach;</em>, or
  <em>endswitch;</em>, respectively.
  <div class="informalexample">
   <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php&nbsp;</span><span style="color: #007700">if&nbsp;(</span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">==&nbsp;</span><span style="color: #0000BB">5</span><span style="color: #007700">):&nbsp;</span><span style="color: #0000BB">?&gt;<br /></span>A&nbsp;is&nbsp;equal&nbsp;to&nbsp;5<br /><span style="color: #0000BB">&lt;?php&nbsp;</span><span style="color: #007700">endif;&nbsp;</span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
   </div>

  </div>
 </p>
 <p class="simpara">
  In the above example, the HTML block &quot;A is equal to 5&quot; is nested within an
  <em>if</em> statement written in the alternative syntax.  The
  HTML block would be displayed only if <var class="varname"><var class="varname">$a</var></var> is equal to 5.
 </p>
 <p class="para">
  The alternative syntax applies to <em>else</em> and
  <em>elseif</em> as well.  The following is an
  <em>if</em> structure with <em>elseif</em> and
  <em>else</em> in the alternative format:
  <div class="informalexample">
   <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">if&nbsp;(</span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">==&nbsp;</span><span style="color: #0000BB">5</span><span style="color: #007700">):<br />&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"a&nbsp;equals&nbsp;5"</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"..."</span><span style="color: #007700">;<br />elseif&nbsp;(</span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">==&nbsp;</span><span style="color: #0000BB">6</span><span style="color: #007700">):<br />&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"a&nbsp;equals&nbsp;6"</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"!!!"</span><span style="color: #007700">;<br />else:<br />&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"a&nbsp;is&nbsp;neither&nbsp;5&nbsp;nor&nbsp;6"</span><span style="color: #007700">;<br />endif;<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
   </div>

  </div>
 </p>
 <blockquote class="note"><p><strong class="note">Note</strong>: 
  <p class="para">
   Mixing syntaxes in the same control block is not supported.
  </p>
 </p></blockquote>
 <p class="para">
  See also <a href="control-structures.while.php" class="link">while</a>,
  <a href="control-structures.for.php" class="link">for</a>, and <a href="control-structures.if.php" class="link">if</a> for further examples.
 </p>
</div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="control-structures.while.php">while<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="control-structures.elseif.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />elseif/else if</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/control-structures.alternative-syntax.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=control-structures.alternative-syntax&amp;redirect=@w{SDBH83Q4}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=control-structures.alternative-syntax&amp;redirect=@w{SDBH83Q4}">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Alternative syntax for control structures</strong>
 </div><div id="allnotes">
 <a name="106012"></a>
 <div class="note">
  <strong class='user'>temec987 at gmail dot com</strong>
  <a href="#106012" class="date">02-Oct-2011 07:00</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
A simple alternative to an if statement, which is almost like a ternary operator, is the use of AND. Consider the following:<br />
<br />
<span class="default">&lt;?php<br />
&nbsp;&nbsp; &nbsp; $value </span><span class="keyword">= </span><span class="string">'Jesus'</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp; &nbsp; </span><span class="comment">// This is a simple if statement<br />
&nbsp;&nbsp; &nbsp; </span><span class="keyword">if( isset( </span><span class="default">$value </span><span class="keyword">) )<br />
&nbsp;&nbsp; &nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; print </span><span class="default">$value</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; }<br />
<br />
&nbsp;&nbsp; &nbsp; print </span><span class="string">'&lt;br /&gt;'</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp; &nbsp; </span><span class="comment">// This is an alternative<br />
&nbsp;&nbsp; &nbsp; </span><span class="keyword">isset( </span><span class="default">$value </span><span class="keyword">) AND print( </span><span class="default">$value </span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span><br />
This does not work with echo() for some reason. I find this extremely useful!</span>
</code></div>
  </div>
 </div>
 <a name="98970"></a>
 <div class="note">
  <strong class='user'>dmgx dot michael at gmail dot com</strong>
  <a href="#98970" class="date">19-Jul-2010 08:42</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you follow MVC design pattern then only your view files should have HTML in them to begin with.&nbsp; Using the braceless syntax in these files only further separates them thematically from the rest of the code.<br />
<br />
The major advantage of braceless syntax is that braces get lost while jumping into and out of php mode, especially if you use php short tags (which contrary to what is stated elsewhere, if you are using htaccess to deploy mod_rewrite in your application it is safe to use short tags in your application.&nbsp; The server admins CANNOT deny short tags to you while simultaneously granting mod_rewrite (and why they would even try is beyond me).<br />
<br />
Another thing I've noted in the examples above - it is safe to omit the ending semicolon prior to a script close tag, and it's slightly easier to read. &lt;? endforeach ?&gt; than <span class="default">&lt;?php </span><span class="keyword">endforeach; </span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="91167"></a>
 <div class="note">
  <strong class='user'>ej at iconcept dot fo</strong>
  <a href="#91167" class="date">28-May-2009 05:30</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
if statement in 1 line<br />
<span class="default">&lt;?php<br />
$hour </span><span class="keyword">= </span><span class="default">11</span><span class="keyword">;<br />
<br />
print </span><span class="default">$foo </span><span class="keyword">= (</span><span class="default">$hour </span><span class="keyword">&lt; </span><span class="default">12</span><span class="keyword">) ? </span><span class="string">"Good morning!" </span><span class="keyword">: </span><span class="string">"Good afternoon!"</span><span class="keyword">;<br />
<br />
</span><span class="default">?&gt;<br />
</span>return Good morning!</span>
</code></div>
  </div>
 </div>
 <a name="89860"></a>
 <div class="note">
  <strong class='user'>flyingmana</strong>
  <a href="#89860" class="date">26-Mar-2009 01:43</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
It seems to me, that many people think that<br />
<br />
<span class="default">&lt;?php </span><span class="keyword">if (</span><span class="default">$a </span><span class="keyword">== </span><span class="default">5</span><span class="keyword">): </span><span class="default">?&gt;<br />
</span>A ist gleich 5<br />
<span class="default">&lt;?php </span><span class="keyword">endif; </span><span class="default">?&gt;<br />
</span><br />
is only with alternate syntax possible, but <br />
<br />
<span class="default">&lt;?php </span><span class="keyword">if (</span><span class="default">$a </span><span class="keyword">== </span><span class="default">5</span><span class="keyword">){ </span><span class="default">?&gt;<br />
</span>A ist gleich 5<br />
<span class="default">&lt;?php </span><span class="keyword">}; </span><span class="default">?&gt;<br />
</span><br />
is also possible.<br />
<br />
alternate syntax makes the code only clearer and easyer to read</span>
</code></div>
  </div>
 </div>
 <a name="88698"></a>
 <div class="note">
  <strong class='user'>SM</strong>
  <a href="#88698" class="date">03-Feb-2009 05:12</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The control structure should be like in BASIC languages:<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">if (</span><span class="default">$a </span><span class="keyword">== </span><span class="default">12</span><span class="keyword">):<br />
&nbsp; echo </span><span class="string">"a is 12\n"</span><span class="keyword">;<br />
</span><span class="default">end </span><span class="keyword">if;<br />
<br />
while (</span><span class="default">true</span><span class="keyword">):<br />
&nbsp; echo </span><span class="string">"loop loop loop\n"</span><span class="keyword">;<br />
</span><span class="default">end </span><span class="keyword">while;<br />
</span><span class="default">?&gt;<br />
</span><br />
or just use end operator like in Ruby<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">if (</span><span class="default">$a </span><span class="keyword">== </span><span class="default">12</span><span class="keyword">):<br />
&nbsp; echo </span><span class="string">"a is 12\n"</span><span class="keyword">;<br />
</span><span class="default">end</span><span class="keyword">;<br />
<br />
while (</span><span class="default">true</span><span class="keyword">):<br />
&nbsp; echo </span><span class="string">"loop loop loop\n"</span><span class="keyword">;<br />
</span><span class="default">end</span><span class="keyword">;<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="86875"></a>
 <div class="note">
  <strong class='user'>mido_alone2001 at yahoo dot com</strong>
  <a href="#86875" class="date">07-Nov-2008 06:05</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Hello , when you going to make a script , you must try easist way to do and fastest way to parse ..<br />
using alternative-syntax is very useful to shorten your code<br />
e.g :<br />
If you want to do:<br />
<br />
<span class="default">&lt;?php<br />
&nbsp;&nbsp;&nbsp; $a</span><span class="keyword">=</span><span class="default">1 </span><span class="keyword">;<br />
if (</span><span class="default">$a</span><span class="keyword">==</span><span class="default">1</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; echo </span><span class="string">"&lt;table border=1&gt;&lt;tr&gt;&lt;td&gt;$a is equal to one&nbsp; &nbsp; &lt;/td&gt;&lt;/tr&gt;&lt;/table&gt; " </span><span class="keyword">;<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
You can do it using alternative-syntax as following :<br />
<br />
<span class="default">&lt;?php<br />
&nbsp;&nbsp;&nbsp; $a</span><span class="keyword">=</span><span class="default">1 </span><span class="keyword">;<br />
if (</span><span class="default">$a</span><span class="keyword">==</span><span class="default">1</span><span class="keyword">) :</span><span class="default">?&gt;<br />
</span>&lt;table border=1&gt;&lt;tr&gt;&lt;td&gt;&lt;?echo $a ;?&gt; &amp;nbsp;is equal to one &lt;/td&gt;&lt;/tr&gt;&lt;/table&gt;<br />
<span class="default">&lt;?php </span><span class="keyword">endif ; </span><span class="default">?&gt;<br />
</span><br />
So the HTML code Won't excuted until the condition is true<br />
<br />
[EDIT BY danbrown AT php DOT net: Contains a bug fix provided by (gmdebby AT gmail DOT com).]</span>
</code></div>
  </div>
 </div>
 <a name="80668"></a>
 <div class="note">
  <strong class='user'>jeremia at gmx dot at</strong>
  <a href="#80668" class="date">28-Jan-2008 06:52</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you wan't to use the alternative syntax for switch statements this won't work:<br />
<br />
&lt;div&gt;<br />
<span class="default">&lt;?php </span><span class="keyword">switch(</span><span class="default">$variable</span><span class="keyword">): </span><span class="default">?&gt;<br />
&lt;?php </span><span class="keyword">case </span><span class="default">1</span><span class="keyword">: </span><span class="default">?&gt;<br />
</span>&lt;div&gt;<br />
Newspage<br />
&lt;/div&gt;<br />
<span class="default">&lt;?php </span><span class="keyword">break;</span><span class="default">?&gt;<br />
&lt;?php </span><span class="keyword">case </span><span class="default">2</span><span class="keyword">: </span><span class="default">?&gt;<br />
</span>&lt;/div&gt;<br />
Forum<br />
&lt;div&gt;<br />
<span class="default">&lt;?php </span><span class="keyword">break;</span><span class="default">?&gt;<br />
&lt;?php </span><span class="keyword">endswitch;</span><span class="default">?&gt;<br />
</span>&lt;/div&gt;<br />
<br />
Instead you have to workaround like this:<br />
<br />
&lt;div&gt;<br />
<span class="default">&lt;?php </span><span class="keyword">switch(</span><span class="default">$variable</span><span class="keyword">): <br />
case </span><span class="default">1</span><span class="keyword">: </span><span class="default">?&gt;<br />
</span>&lt;div&gt;<br />
Newspage<br />
&lt;/div&gt;<br />
<span class="default">&lt;?php </span><span class="keyword">break;</span><span class="default">?&gt;<br />
&lt;?php </span><span class="keyword">case </span><span class="default">2</span><span class="keyword">: </span><span class="default">?&gt;<br />
</span>&lt;/div&gt;<br />
Forum<br />
&lt;div&gt;<br />
<span class="default">&lt;?php </span><span class="keyword">break;</span><span class="default">?&gt;<br />
&lt;?php </span><span class="keyword">endswitch;</span><span class="default">?&gt;<br />
</span>&lt;/div&gt;</span>
</code></div>
  </div>
 </div>
 <a name="78546"></a>
 <div class="note">
  <strong class='user'>spa</strong>
  <a href="#78546" class="date">16-Oct-2007 04:40</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
[EDITOR'S NOTE: reference to deleted note removed]<br />
<br />
The end_; structure sometimes makes it easier to tell which block statement end you are looking at.&nbsp; It's much harder to tell which nested block a } belongs to than an end_;</span>
</code></div>
  </div>
 </div>
 <a name="54206"></a>
 <div class="note">
  <strong class='user'>skippy at zuavra dot net</strong>
  <a href="#54206" class="date">27-Jun-2005 04:32</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If it needs saying, this alternative syntax is excellent for improving legibility (for both PHP and HTML!) in situations where you have a mix of them.<br />
<br />
Interface templates are very often in need of this, especially since the PHP code in them is usually written by one person (who is more of a programmer) and the HTML gets modified by another person (who is more of a web designer). Clear separation in such cases is extremely useful.<br />
<br />
See the default templates that come with WordPress 1.5+ (www.wordpress.org) for practical and smart examples of this alternative syntax.</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=control-structures.alternative-syntax&amp;redirect=@w{SDBH83Q4}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=control-structures.alternative-syntax&amp;redirect=@w{SDBH83Q4}">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/control-structures.alternative-syntax.php">show source</a> |
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