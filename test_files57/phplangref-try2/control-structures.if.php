<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: if - Manual</title>
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
 <link rel="prev" href="control-structures.intro.php" />
 <link rel="next" href="control-structures.else.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/if" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/control-structures.if.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/control-structures.if.php" />
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
 <li class="active"><a href="control-structures.if.php">if</a></li>
 <li><a href="control-structures.else.php">else</a></li>
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
  <a href="control-structures.else.php">else<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="control-structures.intro.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Introduction</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/control-structures.if.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/control-structures.if.php">Brazilian Portuguese</option>
    <option value="zh/control-structures.if.php">Chinese (Simplified)</option>
    <option value="fr/control-structures.if.php">French</option>
    <option value="de/control-structures.if.php">German</option>
    <option value="ja/control-structures.if.php">Japanese</option>
    <option value="pl/control-structures.if.php">Polish</option>
    <option value="ro/control-structures.if.php">Romanian</option>
    <option value="ru/control-structures.if.php">Russian</option>
    <option value="fa/control-structures.if.php">Persian</option>
    <option value="es/control-structures.if.php">Spanish</option>
    <option value="tr/control-structures.if.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="control-structures.if" class="sect1">
 <h2 class="title"><em>if</em></h2>
 <p class="verinfo">(PHP 4, PHP 5)</p>
 <p class="para">
  The <em>if</em> construct is one of the most important
  features of many languages, PHP included.  It allows for
  conditional execution of code fragments.  PHP features an
  <em>if</em> structure that is similar to that of C:
  <div class="informalexample">
   <div class="example-contents">
<div class="cdata"><pre>
if (expr)
  statement
</pre></div>
   </div>

  </div>
 </p>
 <p class="simpara">
  As described in <a href="language.expressions.php" class="link">the section about
  expressions</a>, <span class="replaceable">expression</span> is evaluated to its
  Boolean value.  If <span class="replaceable">expression</span> evaluates to <strong><code>TRUE</code></strong>,
  PHP will execute <span class="replaceable">statement</span>, and if it evaluates
  to <strong><code>FALSE</code></strong> - it&#039;ll ignore it. More information about what values evaluate
  to <strong><code>FALSE</code></strong> can be found in the <a href="language.types.boolean.php#language.types.boolean.casting" class="link">&#039;Converting to boolean&#039;</a>
  section.
 </p>
 <p class="para">
  The following example would display <span class="computeroutput">a is bigger
  than b</span> if <var class="varname"><var class="varname">$a</var></var> is bigger
  than <var class="varname"><var class="varname">$b</var></var>:
  <div class="informalexample">
   <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">if&nbsp;(</span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">&gt;&nbsp;</span><span style="color: #0000BB">$b</span><span style="color: #007700">)<br />&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"a&nbsp;is&nbsp;bigger&nbsp;than&nbsp;b"</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
   </div>

  </div>
 </p>
 <p class="para">
  Often you&#039;d want to have more than one statement to be executed
  conditionally.  Of course, there&#039;s no need to wrap each statement
  with an <em>if</em> clause.  Instead, you can group
  several statements into a statement group.  For example, this code
  would display <span class="computeroutput">a is bigger than b</span>
  if <var class="varname"><var class="varname">$a</var></var> is bigger than
  <var class="varname"><var class="varname">$b</var></var>, and would then assign the value of
  <var class="varname"><var class="varname">$a</var></var> into <var class="varname"><var class="varname">$b</var></var>:
  <div class="informalexample">
   <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">if&nbsp;(</span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">&gt;&nbsp;</span><span style="color: #0000BB">$b</span><span style="color: #007700">)&nbsp;{<br />&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"a&nbsp;is&nbsp;bigger&nbsp;than&nbsp;b"</span><span style="color: #007700">;<br />&nbsp;&nbsp;</span><span style="color: #0000BB">$b&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">$a</span><span style="color: #007700">;<br />}<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
   </div>

  </div>
 </p>
 <p class="simpara">
  <em>If</em> statements can be nested infinitely within other
  <em>if</em> statements, which provides you with complete
  flexibility for conditional execution of the various parts of your
  program.
 </p>
</div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="control-structures.else.php">else<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="control-structures.intro.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Introduction</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/control-structures.if.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=control-structures.if&amp;redirect=http://www.php.net/manual/en/control-structures.if.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=control-structures.if&amp;redirect=http://www.php.net/manual/en/control-structures.if.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>if</strong>
 </div><div id="allnotes">
 <a name="109540"></a>
 <div class="note">
  <strong class='user'>johannes dot kingma at gmail dot com</strong>
  <a href="#109540" class="date">27-Jul-2012 08:46</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
in order to check two conditions at the same time use one of the binary operators 'and' or 'or'. So instead of<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">if( </span><span class="default">cond1 </span><span class="keyword">) {<br />
&nbsp; if( </span><span class="default">cond2 </span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">// statements<br />
&nbsp; </span><span class="keyword">}<br />
&nbsp; else {<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">// statements<br />
&nbsp; </span><span class="keyword">}<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
write:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">if( </span><span class="default">cond1 </span><span class="keyword">and </span><span class="default">cond2 </span><span class="keyword">) {<br />
&nbsp; </span><span class="comment">// statements <br />
</span><span class="keyword">}<br />
else {<br />
&nbsp; </span><span class="comment">// statements<br />
</span><span class="keyword">}<br />
</span><span class="default">?&gt;<br />
</span>be aware though that when cond1 is false cond2 will not be evaluated.</span>
</code></div>
  </div>
 </div>
 <a name="108224"></a>
 <div class="note">
  <strong class='user'>ehsan at chavoshi dot com</strong>
  <a href="#108224" class="date">09-Apr-2012 04:05</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
You can use a simple if and echo structure :<br />
<br />
$i==1 and print "i is 1"<br />
is identical with<br />
if ($i ==1)<br />
&nbsp; echo "i is 1";</span>
</code></div>
  </div>
 </div>
 <a name="108030"></a>
 <div class="note">
  <strong class='user'>marcin_wo_wroc at o2 dot pl</strong>
  <a href="#108030" class="date">23-Mar-2012 10:26</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Here's something uncommon:<br />
How to make one else statement for two nested if conditions?<br />
aka: how to make one else for two ifs.<br />
<br />
By default people simply copy &amp; paste code in else like that:<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">if ( </span><span class="default">is_object</span><span class="keyword">(</span><span class="default">$times</span><span class="keyword">)){<br />
&nbsp;if ((float)</span><span class="default">$times</span><span class="keyword">-&gt;</span><span class="default">getTime</span><span class="keyword">()&gt;</span><span class="default">0</span><span class="keyword">){<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">//code<br />
&nbsp;</span><span class="keyword">}else{<br />
&nbsp;&nbsp; &nbsp; </span><span class="comment">//else code Alpha<br />
&nbsp;</span><span class="keyword">}<br />
}else{<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">//duplicated else code Alpha<br />
</span><span class="keyword">}<br />
</span><span class="default">?&gt;<br />
</span><br />
but it's much better and easier to simply use one condition inside of another, like that:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">if ( </span><span class="default">is_object</span><span class="keyword">(</span><span class="default">$times</span><span class="keyword">) ? (float)</span><span class="default">$times</span><span class="keyword">-&gt;</span><span class="default">getTime</span><span class="keyword">()&gt;</span><span class="default">0 </span><span class="keyword">: </span><span class="default">false</span><span class="keyword">){<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">//put here your code that gonna be executed if $times is an object and if $times-&gt;getTime() is greater than zero<br />
&nbsp;&nbsp;&nbsp; //condition is the same as:<br />
&nbsp;&nbsp;&nbsp; //if (is_object($times)){<br />
&nbsp;&nbsp;&nbsp; //&nbsp; &nbsp; if ((float)$times-&gt;getTime()&gt;0){<br />
&nbsp;&nbsp;&nbsp; //&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; //code<br />
&nbsp;&nbsp;&nbsp; //&nbsp; &nbsp; } <br />
&nbsp;&nbsp;&nbsp; //}<br />
</span><span class="keyword">}else{<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">//put here your else statement for conditions above<br />
</span><span class="keyword">}<br />
</span><span class="default">?&gt;<br />
</span><br />
simple &amp; beautiful :)</span>
</code></div>
  </div>
 </div>
 <a name="107706"></a>
 <div class="note">
  <strong class='user'>sofwan at sofwan dot net</strong>
  <a href="#107706" class="date">28-Feb-2012 09:31</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
It seems that only numbers can be compared between them but actually an alphabet can be compare too. For example :<br />
<br />
<span class="default">&lt;?php<br />
&nbsp;</span><span class="comment">// Number comparison<br />
&nbsp; </span><span class="default">$a</span><span class="keyword">=</span><span class="string">"C"</span><span class="keyword">;<br />
&nbsp; </span><span class="default">$b</span><span class="keyword">=</span><span class="string">"X"</span><span class="keyword">;<br />
&nbsp; if (</span><span class="default">$a</span><span class="keyword">&lt;</span><span class="default">$b</span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; {<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="default">$a</span><span class="keyword">.</span><span class="string">"is smaller than"</span><span class="keyword">.</span><span class="default">$b</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <br />
</span><span class="comment">// Result : C is smaller than X<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="104785"></a>
 <div class="note">
  <strong class='user'>bimal at sanjaal dot com</strong>
  <a href="#104785" class="date">07-Jul-2011 02:21</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
PHP compares numbers inside quotations, in an interesting way.<br />
This can create confusions to those who did not refer this manual, but expected something different.<br />
<br />
<span class="default">&lt;?php<br />
<br />
define</span><span class="keyword">(</span><span class="string">'NUMBER'</span><span class="keyword">, </span><span class="default">13</span><span class="keyword">);<br />
</span><span class="default">$number </span><span class="keyword">= </span><span class="default">NUMBER</span><span class="keyword">;<br />
<br />
if(</span><span class="string">'13_2' </span><span class="keyword">== </span><span class="default">NUMBER</span><span class="keyword">) { echo(</span><span class="string">'Why matched?'</span><span class="keyword">); };<br />
if(</span><span class="string">'13_2' </span><span class="keyword">== </span><span class="string">"{$number}"</span><span class="keyword">) { echo(</span><span class="string">'Why not matched?'</span><span class="keyword">); }<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
In the above example, the first comparison matches; that you don't expect.<br />
<br />
The second comparison does not match.</span>
</code></div>
  </div>
 </div>
 <a name="103202"></a>
 <div class="note">
  <strong class='user'>lallemand dot mathieu at gmail dot com</strong>
  <a href="#103202" class="date">31-Mar-2011 06:01</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Be careful when you chain inline "if" :<br />
<br />
<span class="default">&lt;?php<br />
$x </span><span class="keyword">= </span><span class="default">1</span><span class="keyword">;<br />
</span><span class="default">$y </span><span class="keyword">= </span><span class="default">3</span><span class="keyword">;<br />
echo (</span><span class="default">$x</span><span class="keyword">==</span><span class="default">1</span><span class="keyword">) ? </span><span class="string">"One" </span><span class="keyword">: (</span><span class="default">$y </span><span class="keyword">== </span><span class="default">2</span><span class="keyword">) ? </span><span class="string">"Two" </span><span class="keyword">: </span><span class="string">"None"</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
Expected result : "One".<br />
Result on screen : "Two".<br />
<br />
Pretty disapointing isn't it ?<br />
<br />
So, if you want to chain inline "if" you have to use parentesis on each test like below:<br />
<br />
<span class="default">&lt;?php<br />
$x</span><span class="keyword">=</span><span class="default">1</span><span class="keyword">;<br />
</span><span class="default">$y</span><span class="keyword">=</span><span class="default">3</span><span class="keyword">;<br />
echo (</span><span class="default">$x</span><span class="keyword">==</span><span class="default">1</span><span class="keyword">) ? </span><span class="string">"One" </span><span class="keyword">: ( (</span><span class="default">$y</span><span class="keyword">==</span><span class="default">2</span><span class="keyword">) ? </span><span class="string">"Two" </span><span class="keyword">: </span><span class="string">"None" </span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span><br />
Result on screen : "One".<br />
<br />
Hope it helps !</span>
</code></div>
  </div>
 </div>
 <a name="102404"></a>
 <div class="note">
  <strong class='user'>Donny Nyamweya</strong>
  <a href="#102404" class="date">11-Feb-2011 08:30</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
In addition to the traditional syntax for if (condition) action;<br />
I am fond of the ternary operator that does the same thing, but with fewer words and code to type:<br />
<br />
(condition ? action_if_true: action_if_false;)<br />
<br />
example<br />
<br />
(x &gt; y? 'Passed the test' : 'Failed the test')</span>
</code></div>
  </div>
 </div>
 <a name="102060"></a>
 <div class="note">
  <strong class='user'>Christian L.</strong>
  <a href="#102060" class="date">25-Jan-2011 10:58</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
An other way for controls is the ternary operator (see Comparison Operators) that can be used as follows:<br />
<br />
<span class="default">&lt;?php<br />
$v </span><span class="keyword">= </span><span class="default">1</span><span class="keyword">;<br />
<br />
</span><span class="default">$r </span><span class="keyword">= (</span><span class="default">1 </span><span class="keyword">== </span><span class="default">$v</span><span class="keyword">) ? </span><span class="string">'Yes' </span><span class="keyword">: </span><span class="string">'No'</span><span class="keyword">; </span><span class="comment">// $r is set to 'Yes'<br />
</span><span class="default">$r </span><span class="keyword">= (</span><span class="default">3 </span><span class="keyword">== </span><span class="default">$v</span><span class="keyword">) ? </span><span class="string">'Yes' </span><span class="keyword">: </span><span class="string">'No'</span><span class="keyword">; </span><span class="comment">// $r is set to 'No'<br />
<br />
</span><span class="keyword">echo (</span><span class="default">1 </span><span class="keyword">== </span><span class="default">$v</span><span class="keyword">) ? </span><span class="string">'Yes' </span><span class="keyword">: </span><span class="string">'No'</span><span class="keyword">; </span><span class="comment">// 'Yes' will be printed<br />
<br />
// and since PHP 5.3<br />
</span><span class="default">$v </span><span class="keyword">= </span><span class="string">'My Value'</span><span class="keyword">;<br />
</span><span class="default">$r </span><span class="keyword">= (</span><span class="default">$v</span><span class="keyword">) ?: </span><span class="string">'No Value'</span><span class="keyword">; </span><span class="comment">// $r is set to 'My Value' because $v is evaluated to TRUE<br />
<br />
</span><span class="default">$v </span><span class="keyword">= </span><span class="string">''</span><span class="keyword">;<br />
echo (</span><span class="default">$v</span><span class="keyword">) ?: </span><span class="string">'No Value'</span><span class="keyword">; </span><span class="comment">// 'No Value' will be printed because $v is evaluated to FALSE<br />
</span><span class="default">?&gt;<br />
</span><br />
Parentheses can be left out in all examples above.</span>
</code></div>
  </div>
 </div>
 <a name="101724"></a>
 <div class="note">
  <strong class='user'>techguy14 at gmail dot com</strong>
  <a href="#101724" class="date">06-Jan-2011 01:39</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
You can have 'nested' if statements withing a single if statement, using additional parenthesis.<br />
For example, instead of having:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">if( </span><span class="default">$a </span><span class="keyword">== </span><span class="default">1 </span><span class="keyword">|| </span><span class="default">$a </span><span class="keyword">== </span><span class="default">2 </span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; if( </span><span class="default">$b </span><span class="keyword">== </span><span class="default">3 </span><span class="keyword">|| </span><span class="default">$b </span><span class="keyword">== </span><span class="default">4 </span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if( </span><span class="default">$c </span><span class="keyword">== </span><span class="default">5 </span><span class="keyword">|| $ </span><span class="default">d </span><span class="keyword">== </span><span class="default">6 </span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="comment">//Do something here.<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">}<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
You could just simply do this:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">if( (</span><span class="default">$a</span><span class="keyword">==</span><span class="default">1 </span><span class="keyword">|| </span><span class="default">$a</span><span class="keyword">==</span><span class="default">2</span><span class="keyword">) &amp;&amp; (</span><span class="default">$b</span><span class="keyword">==</span><span class="default">3 </span><span class="keyword">|| </span><span class="default">$b</span><span class="keyword">==</span><span class="default">4</span><span class="keyword">) &amp;&amp; (</span><span class="default">$c</span><span class="keyword">==</span><span class="default">5 </span><span class="keyword">|| </span><span class="default">$c</span><span class="keyword">==</span><span class="default">6</span><span class="keyword">) ) {<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">//do that something here.<br />
</span><span class="keyword">}<br />
</span><span class="default">?&gt;<br />
</span><br />
Hope this helps!</span>
</code></div>
  </div>
 </div>
 <a name="101034"></a>
 <div class="note">
  <strong class='user'>admin at leonard !spam challis dot com</strong>
  <a href="#101034" class="date">22-Nov-2010 04:41</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
When using if statements without the curly braces, remember than only one statement will be executed as part of that condition. If you want to place multiple statements you must use curly braces, and not just put them on the same line.<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">if (</span><span class="default">1</span><span class="keyword">==</span><span class="default">0</span><span class="keyword">) echo </span><span class="string">"Test 1."</span><span class="keyword">; echo </span><span class="string">"Test 2"</span><span class="keyword">;<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
Whereas some people would expect nothing to be displayed, this piece of code will show: "Test 2".</span>
</code></div>
  </div>
 </div>
 <a name="99915"></a>
 <div class="note">
  <strong class='user'>Rudi</strong>
  <a href="#99915" class="date">14-Sep-2010 01:14</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Note that safe type checking (using === and !== instead of == and !=) is in general somewhat faster. When you're using non-safe type checking and a conversion is really needed for checking, safe type checking is considerably faster.<br />
<br />
===================================<br />
Test (100,000,000 runs):<br />
<span class="default">&lt;?php<br />
$start </span><span class="keyword">= </span><span class="default">microtime</span><span class="keyword">(</span><span class="default">true</span><span class="keyword">);<br />
for(</span><span class="default">$i </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">; </span><span class="default">$i </span><span class="keyword">&lt; </span><span class="default">100000000</span><span class="keyword">; </span><span class="default">$i</span><span class="keyword">++)<br />
&nbsp;&nbsp;&nbsp; if(</span><span class="default">5 </span><span class="keyword">== </span><span class="default">10</span><span class="keyword">) {}<br />
</span><span class="default">$end </span><span class="keyword">= </span><span class="default">microtime</span><span class="keyword">(</span><span class="default">true</span><span class="keyword">);<br />
echo </span><span class="string">"1: "</span><span class="keyword">.(</span><span class="default">$end </span><span class="keyword">- </span><span class="default">$start</span><span class="keyword">).</span><span class="string">"&lt;br /&gt;\n"</span><span class="keyword">;<br />
unset(</span><span class="default">$start</span><span class="keyword">, </span><span class="default">$end</span><span class="keyword">);<br />
<br />
</span><span class="default">$start </span><span class="keyword">= </span><span class="default">microtime</span><span class="keyword">(</span><span class="default">true</span><span class="keyword">);<br />
for(</span><span class="default">$i </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">; </span><span class="default">$i </span><span class="keyword">&lt; </span><span class="default">100000000</span><span class="keyword">; </span><span class="default">$i</span><span class="keyword">++)<br />
&nbsp;&nbsp;&nbsp; if(</span><span class="string">'foobar' </span><span class="keyword">== </span><span class="default">10</span><span class="keyword">) {}<br />
</span><span class="default">$end </span><span class="keyword">= </span><span class="default">microtime</span><span class="keyword">(</span><span class="default">true</span><span class="keyword">);<br />
echo </span><span class="string">"2: "</span><span class="keyword">.(</span><span class="default">$end </span><span class="keyword">- </span><span class="default">$start</span><span class="keyword">).</span><span class="string">"&lt;br /&gt;\n"</span><span class="keyword">;<br />
unset(</span><span class="default">$start</span><span class="keyword">, </span><span class="default">$end</span><span class="keyword">);<br />
<br />
</span><span class="default">$start </span><span class="keyword">= </span><span class="default">microtime</span><span class="keyword">(</span><span class="default">true</span><span class="keyword">);<br />
for(</span><span class="default">$i </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">; </span><span class="default">$i </span><span class="keyword">&lt; </span><span class="default">100000000</span><span class="keyword">; </span><span class="default">$i</span><span class="keyword">++)<br />
&nbsp;&nbsp;&nbsp; if(</span><span class="default">5 </span><span class="keyword">=== </span><span class="default">10</span><span class="keyword">) {}<br />
</span><span class="default">$end </span><span class="keyword">= </span><span class="default">microtime</span><span class="keyword">(</span><span class="default">true</span><span class="keyword">);<br />
echo </span><span class="string">"3: "</span><span class="keyword">.(</span><span class="default">$end </span><span class="keyword">- </span><span class="default">$start</span><span class="keyword">).</span><span class="string">"&lt;br /&gt;\n"</span><span class="keyword">;<br />
unset(</span><span class="default">$start</span><span class="keyword">, </span><span class="default">$end</span><span class="keyword">);<br />
<br />
</span><span class="default">$start </span><span class="keyword">= </span><span class="default">microtime</span><span class="keyword">(</span><span class="default">true</span><span class="keyword">);<br />
for(</span><span class="default">$i </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">; </span><span class="default">$i </span><span class="keyword">&lt; </span><span class="default">100000000</span><span class="keyword">; </span><span class="default">$i</span><span class="keyword">++)<br />
&nbsp;&nbsp;&nbsp; if(</span><span class="string">'foobar' </span><span class="keyword">=== </span><span class="default">10</span><span class="keyword">) {}<br />
</span><span class="default">$end </span><span class="keyword">= </span><span class="default">microtime</span><span class="keyword">(</span><span class="default">true</span><span class="keyword">);<br />
echo </span><span class="string">"4: "</span><span class="keyword">.(</span><span class="default">$end </span><span class="keyword">- </span><span class="default">$start</span><span class="keyword">).</span><span class="string">"&lt;br /&gt;\n"</span><span class="keyword">;<br />
unset(</span><span class="default">$start</span><span class="keyword">, </span><span class="default">$end</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span><br />
===================================<br />
Result (depending on hardware configuration):<br />
1: 16.779544115067<br />
2: 21.305675029755<br />
3: 16.345532178879<br />
4: 15.991420030594</span>
</code></div>
  </div>
 </div>
 <a name="93852"></a>
 <div class="note">
  <strong class='user'>austinderrick2 at gmail dot com</strong>
  <a href="#93852" class="date">03-Oct-2009 04:50</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
As an added note to the guy below, in such a case, use the !== operator like this.<br />
<br />
$nkey = array_search($needle, $haystack);<br />
<br />
if ($nkey !== false) { ...<br />
<br />
The !== and the === compare the "types". So, with this type of comparision, 0 is not the same as the FALSE returned by the array_search array when it can not find a match. :)<br />
<br />
Quoted Text:<br />
<br />
===================================<br />
Be careful with stuff like<br />
<br />
if ($nkey = array_search($needle, $haystack)) { ...<br />
<br />
if the returned key is actually the key 0, then the if won't be executed<br />
===================================</span>
</code></div>
  </div>
 </div>
 <a name="93220"></a>
 <div class="note">
  <strong class='user'>jm+phpweb at roth dot lu</strong>
  <a href="#93220" class="date">28-Aug-2009 02:25</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Be careful with stuff like<br />
<br />
if ($nkey = array_search($needle, $haystack)) { ...<br />
<br />
if the returned key is actually the key 0, then the if won't be executed</span>
</code></div>
  </div>
 </div>
 <a name="90073"></a>
 <div class="note">
  <strong class='user'>strata_ranger at hotmail dot com</strong>
  <a href="#90073" class="date">04-Apr-2009 05:13</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Although most programmers are aware of this already, if for whatever reason you need to 'break' out of an if() block (which, unlike switch() is not considered a looping structure) just wrap it in an appropriate looping structure, such as a do-while(false):<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">do if (</span><span class="default">$foo</span><span class="keyword">)<br />
{<br />
&nbsp; </span><span class="comment">// Do something first...<br />
<br />
&nbsp; // Shall we continue with this block, or exit now?<br />
&nbsp; </span><span class="keyword">if (</span><span class="default">$abort_if_block</span><span class="keyword">) break;<br />
<br />
&nbsp; </span><span class="comment">// Continue doing something...<br />
<br />
</span><span class="keyword">} while (</span><span class="default">false</span><span class="keyword">);<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="90033"></a>
 <div class="note">
  <strong class='user'>Anonymous</strong>
  <a href="#90033" class="date">02-Apr-2009 04:32</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you need to do something when a function return FALSE and nothing when it return TRUE you can do it like that :<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">call</span><span class="keyword">()<br />
{<br />
return </span><span class="default">FALSE</span><span class="keyword">;<br />
}<br />
<br />
if(</span><span class="default">call</span><span class="keyword">()==</span><span class="default">TRUE</span><span class="keyword">) </span><span class="comment">// or if(call())<br />
</span><span class="keyword">{<br />
</span><span class="comment">// nothing to do<br />
</span><span class="keyword">}<br />
else<br />
{<br />
</span><span class="comment">// do something here<br />
</span><span class="keyword">}<br />
</span><span class="default">?&gt;<br />
</span><br />
You can also write it like this :<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">if(!</span><span class="default">call</span><span class="keyword">()==</span><span class="default">TRUE</span><span class="keyword">) </span><span class="comment">// or if(!call())<br />
</span><span class="keyword">{<br />
</span><span class="comment">// do something here<br />
</span><span class="keyword">}<br />
</span><span class="comment">// here '!' will invert 'FALSE' (from call()) into 'TRUE'<br />
</span><span class="default">?&gt;<br />
</span>/!\ WARNING /!\<br />
The '!' only work with booleans !<br />
Check <a href="http://fr.php.net/manual/en/language.types.boolean.php" rel="nofollow" target="_blank">http://fr.php.net/manual/en/language.types.boolean.php</a> to know if you can use '!'<br />
<br />
If you want to compare two strings and use '!' be careful how you use it !!!!<br />
<span class="default">&lt;?php<br />
$string1 </span><span class="keyword">= </span><span class="string">"cake"</span><span class="keyword">;<br />
</span><span class="default">$string2 </span><span class="keyword">= </span><span class="string">"foo"</span><span class="keyword">;<br />
<br />
if(!</span><span class="default">$string1</span><span class="keyword">==</span><span class="default">$string2</span><span class="keyword">)<br />
{<br />
echo </span><span class="string">"cake is a lie"</span><span class="keyword">;<br />
}<br />
</span><span class="comment">//this will ALWAYS fail without exception because '!' is applied to $string1 and not to '$string1==$string2'<br />
<br />
//to work, you have to do like this<br />
</span><span class="keyword">if(!(</span><span class="default">$string1</span><span class="keyword">==</span><span class="default">$string2</span><span class="keyword">))<br />
{<br />
echo </span><span class="string">"cake is a lie"</span><span class="keyword">;<br />
}<br />
</span><span class="comment">//it will display 'cake is a lie' because ($string1==$string2) return FALSE and '!' will invert it into TRUE<br />
</span><span class="default">?&gt;<br />
</span>For array/float, it's the same !</span>
</code></div>
  </div>
 </div>
 <a name="89429"></a>
 <div class="note">
  <strong class='user'>contact at bsorin dot romania</strong>
  <a href="#89429" class="date">07-Mar-2009 08:28</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
This has got the better part of my last 2 hours, so I'm putting it here, maybe it will save someone some time.<br />
<br />
I had a<br />
<br />
if (function1() &amp;&amp; function2())<br />
<br />
statement. Before returning true or false, function1() and function2() had to output some text. The trick is that, if function1() returns false, function2() is not called at all. It seems I should have known that, but it slipped my mind.</span>
</code></div>
  </div>
 </div>
 <a name="86005"></a>
 <div class="note">
  <strong class='user'>Anonymous</strong>
  <a href="#86005" class="date">28-Sep-2008 05:03</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Re : henryk dot kwak at gmail dot com<br />
<span class="default">&lt;?php </span><span class="keyword">function </span><span class="default">message</span><span class="keyword">(</span><span class="default">$m</span><span class="keyword">) <br />
{ <br />
echo </span><span class="string">"$m &lt;br /&gt;\r"</span><span class="keyword">; <br />
return </span><span class="default">true</span><span class="keyword">; <br />
} <br />
</span><span class="default">$k</span><span class="keyword">=</span><span class="default">false</span><span class="keyword">; <br />
if (</span><span class="default">message</span><span class="keyword">(</span><span class="string">"first"</span><span class="keyword">)&amp;&amp; </span><span class="default">$k </span><span class="keyword">&amp;&amp; </span><span class="default">message</span><span class="keyword">(</span><span class="string">"second"</span><span class="keyword">)){;} <br />
</span><span class="comment">// will show <br />
//first <br />
</span><span class="keyword">class <br />
</span><span class="default">$k</span><span class="keyword">=</span><span class="default">true</span><span class="keyword">; <br />
if (</span><span class="default">message</span><span class="keyword">(</span><span class="string">"first"</span><span class="keyword">)&amp;&amp; </span><span class="default">$k </span><span class="keyword">&amp;&amp; </span><span class="default">message</span><span class="keyword">(</span><span class="string">"second"</span><span class="keyword">)){;} <br />
</span><span class="comment">// will show <br />
//first <br />
//second&nbsp; <br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="85931"></a>
 <div class="note">
  <strong class='user'>john</strong>
  <a href="#85931" class="date">24-Sep-2008 08:24</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
@henryk (and everybody):<br />
<br />
You should put your arguments in order by *least* likely to be true. That way if php is going to be able to quit checking, it will happen sooner rather than later, and your script will run (what amounts to unnoticeably) faster.<br />
<br />
At least, that makes the most sense to me, but I don't claim omniscience.</span>
</code></div>
  </div>
 </div>
 <a name="85396"></a>
 <div class="note">
  <strong class='user'>Wiseguy</strong>
  <a href="#85396" class="date">28-Aug-2008 07:22</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
RE: chrislabricole at yahoo dot fr on 09-Aug-2008 05:53<br />
<br />
You're referring to the ternary operator.<br />
<br />
<a href="http://php.net/manual/en/language.operators.comparison.php" rel="nofollow" target="_blank">http://php.net/manual/en/language.operators.comparison.php</a></span>
</code></div>
  </div>
 </div>
 <a name="85105"></a>
 <div class="note">
  <strong class='user'>jchau at bu dot edu</strong>
  <a href="#85105" class="date">14-Aug-2008 10:50</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
RE: henryk dot kwak at gmail dot com's comment from 04-May-2008 05:01<br />
<br />
I think you made a mistake. <br />
<br />
For maximum efficiency, assuming each expression requires the same amount of processing, the expression that is least likely to be true should come first for expressions connected by &amp;&amp; (and).&nbsp; This will reduce the probability that later expressions will need to be evaluated.&nbsp; <br />
<br />
The opposite is true for || (or).&nbsp; If the most likely expression comes first, then the probability of needing to evaluate later expressions is reduced.</span>
</code></div>
  </div>
 </div>
 <a name="85001"></a>
 <div class="note">
  <strong class='user'>chrislabricole at yahoo dot fr</strong>
  <a href="#85001" class="date">09-Aug-2008 05:53</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
You can do IF with this pattern :<br />
<span class="default">&lt;?php<br />
$var </span><span class="keyword">= </span><span class="default">TRUE</span><span class="keyword">;<br />
echo </span><span class="default">$var</span><span class="keyword">==</span><span class="default">TRUE </span><span class="keyword">? </span><span class="string">'TRUE' </span><span class="keyword">: </span><span class="string">'FALSE'</span><span class="keyword">; </span><span class="comment">// get TRUE<br />
</span><span class="keyword">echo </span><span class="default">$var</span><span class="keyword">==</span><span class="default">FALSE </span><span class="keyword">? </span><span class="string">'TRUE' </span><span class="keyword">: </span><span class="string">'FALSE'</span><span class="keyword">; </span><span class="comment">// get FALSE<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="82974"></a>
 <div class="note">
  <strong class='user'>henryk dot kwak at gmail dot com</strong>
  <a href="#82974" class="date">04-May-2008 05:01</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
When you use if command with many condidions like<br />
if ( expr1 &amp;&amp; expr2 &amp;&amp; expr3 &amp;&amp; etc. ) <br />
it is more effective to put expressions in special order<br />
Firstly you should put that, which has the biggest<br />
probability to occur. <br />
This is because PHP checks each condition in order from left to right and it takes some time to check each condition.</span>
</code></div>
  </div>
 </div>
 <a name="81698"></a>
 <div class="note">
  <strong class='user'>grawity at gmail dot com</strong>
  <a href="#81698" class="date">10-Mar-2008 03:41</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
re: #80305<br />
<br />
Again useful for newbies:<br />
<br />
if you need to compare a variable with a value, instead of doing<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">if (</span><span class="default">$foo </span><span class="keyword">== </span><span class="default">3</span><span class="keyword">) </span><span class="default">bar</span><span class="keyword">();<br />
</span><span class="default">?&gt;<br />
</span><br />
do<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">if (</span><span class="default">3 </span><span class="keyword">== </span><span class="default">$foo</span><span class="keyword">) </span><span class="default">bar</span><span class="keyword">();<br />
</span><span class="default">?&gt;<br />
</span><br />
this way, if you forget a =, it will become<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">if (</span><span class="default">3 </span><span class="keyword">= </span><span class="default">$foo</span><span class="keyword">) </span><span class="default">bar</span><span class="keyword">();<br />
</span><span class="default">?&gt;<br />
</span><br />
and PHP will report an error.</span>
</code></div>
  </div>
 </div>
 <a name="80305"></a>
 <div class="note">
  <strong class='user'>redrobinuk at aol dot com</strong>
  <a href="#80305" class="date">09-Jan-2008 02:54</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
This is aimed at PHP beginners but many of us do this&nbsp; Ocasionally...<br />
<br />
When writing an if statement that compares two values, remember not to use a single = statement.<br />
<br />
eg: <br />
<span class="default">&lt;?php<br />
</span><span class="keyword">if (</span><span class="default">$a </span><span class="keyword">= </span><span class="default">$b</span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; print(</span><span class="string">"something"</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; }<br />
</span><span class="default">?&gt;<br />
</span>This will assign $a the value $b and output the statement.<br />
<br />
To see if $a is exactly equal to $b (value not type) It should be:<br />
<span class="default">&lt;?php<br />
&nbsp;&nbsp; &nbsp; </span><span class="keyword">if (</span><span class="default">$a </span><span class="keyword">== </span><span class="default">$b</span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; print(</span><span class="string">"something"</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; }<br />
</span><span class="default">?&gt;<br />
</span>Simple stuff but it can cause havok deep in classes/functions etc...</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=control-structures.if&amp;redirect=http://www.php.net/manual/en/control-structures.if.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=control-structures.if&amp;redirect=http://www.php.net/manual/en/control-structures.if.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/control-structures.if.php">show source</a> |
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