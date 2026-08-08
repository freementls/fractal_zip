<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: declare - Manual</title>
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
 <link rel="prev" href="control-structures.switch.php" />
 <link rel="next" href="function.return.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/declare" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/control-structures.declare.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="@w{EGQPMCFT}" />
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
 <li><a href="control-structures.for.php">for</a></li>
 <li><a href="control-structures.foreach.php">foreach</a></li>
 <li><a href="control-structures.break.php">break</a></li>
 <li><a href="control-structures.continue.php">continue</a></li>
 <li><a href="control-structures.switch.php">switch</a></li>
 <li class="active"><a href="control-structures.declare.php">declare</a></li>
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
  <a href="function.return.php">return<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="control-structures.switch.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />switch</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/control-structures.declare.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/control-structures.declare.php">Brazilian Portuguese</option>
    <option value="zh/control-structures.declare.php">Chinese (Simplified)</option>
    <option value="fr/control-structures.declare.php">French</option>
    <option value="de/control-structures.declare.php">German</option>
    <option value="ja/control-structures.declare.php">Japanese</option>
    <option value="pl/control-structures.declare.php">Polish</option>
    <option value="ro/control-structures.declare.php">Romanian</option>
    <option value="ru/control-structures.declare.php">Russian</option>
    <option value="fa/control-structures.declare.php">Persian</option>
    <option value="es/control-structures.declare.php">Spanish</option>
    <option value="tr/control-structures.declare.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="control-structures.declare" class="sect1">
 <h2 class="title"><em>declare</em></h2>
 <p class="verinfo">(PHP 4, PHP 5)</p>
 <p class="para">
  The <em>declare</em> construct is used to
  set execution directives for a block of code.
  The syntax of <em>declare</em> is similar to
  the syntax of other flow control constructs:
  <div class="informalexample">
   <div class="example-contents">
<div class="cdata"><pre>
declare (directive)
    statement
</pre></div>
   </div>

  </div>
 </p>
 <p class="para">
  The <em>directive</em> section allows the
  behavior of the <em>declare</em> block to
  be set.
  Currently only two directives are recognized: the
  <em>ticks</em> directive (See below for more
  information on the
  <a href="control-structures.declare.php#control-structures.declare.ticks" class="link">ticks</a>
  directive) and the <em>encoding</em> directive (See below for more
  information on the
  <a href="control-structures.declare.php#control-structures.declare.encoding" class="link">encoding</a>
  directive).
 </p>
 <blockquote class="note"><p><strong class="note">Note</strong>: 
  <span class="simpara">
   The encoding directive was added in PHP 5.3.0
  </span>
 </p></blockquote>
 <p class="para">
  The <em>statement</em> part of the
  <em>declare</em> block will be executed - how
  it is executed and what side effects occur during execution
  may depend on the directive set in the
  <em>directive</em> block.
 </p>
 <p class="para">
  The <em>declare</em> construct can also be used in the global
  scope, affecting all code following it (however if the file with
  <em>declare</em> was included then it does not affect the parent
  file).
  <div class="informalexample">
   <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #FF8000">//&nbsp;these&nbsp;are&nbsp;the&nbsp;same:<br /><br />//&nbsp;you&nbsp;can&nbsp;use&nbsp;this:<br /></span><span style="color: #007700">declare(</span><span style="color: #0000BB">ticks</span><span style="color: #007700">=</span><span style="color: #0000BB">1</span><span style="color: #007700">)&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;entire&nbsp;script&nbsp;here<br /></span><span style="color: #007700">}<br /><br /></span><span style="color: #FF8000">//&nbsp;or&nbsp;you&nbsp;can&nbsp;use&nbsp;this:<br /></span><span style="color: #007700">declare(</span><span style="color: #0000BB">ticks</span><span style="color: #007700">=</span><span style="color: #0000BB">1</span><span style="color: #007700">);<br /></span><span style="color: #FF8000">//&nbsp;entire&nbsp;script&nbsp;here<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
   </div>

  </div>
 </p>

 <div class="sect2" id="control-structures.declare.ticks">
  <h3 class="title">Ticks</h3>
  <p class="para">A tick is an event that occurs for every
  <var class="varname"><var class="varname">N</var></var> low-level tickable statements executed
  by the parser within the <em>declare</em> block.
  The value for <var class="varname"><var class="varname">N</var></var> is specified
  using <code class="code">ticks=<var class="varname"><var class="varname">N</var></var></code>
  within the <em>declare</em> blocks&#039;s
  <em>directive</em> section.
 </p>
 <p class="para">
  Not all statements are tickable. Typically, condition
  expressions and argument expressions are not tickable.
 </p>
 <p class="para">
  The event(s) that occur on each tick are specified using the
   <span class="function"><a href="function.register-tick-function.php" class="function">register_tick_function()</a></span>. See the example
  below for more details. Note that more than one event can occur
  for each tick.
 </p>
 <p class="para">
  <div class="example" id="example-132">
   <p><strong>Example #1 Tick usage example</strong></p>
   <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /><br /></span><span style="color: #007700">declare(</span><span style="color: #0000BB">ticks</span><span style="color: #007700">=</span><span style="color: #0000BB">1</span><span style="color: #007700">);<br /><br /></span><span style="color: #FF8000">//&nbsp;A&nbsp;function&nbsp;called&nbsp;on&nbsp;each&nbsp;tick&nbsp;event<br /></span><span style="color: #007700">function&nbsp;</span><span style="color: #0000BB">tick_handler</span><span style="color: #007700">()<br />{<br />&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"tick_handler()&nbsp;called\n"</span><span style="color: #007700">;<br />}<br /><br /></span><span style="color: #0000BB">register_tick_function</span><span style="color: #007700">(</span><span style="color: #DD0000">'tick_handler'</span><span style="color: #007700">);<br /><br /></span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">1</span><span style="color: #007700">;<br /><br />if&nbsp;(</span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">&gt;&nbsp;</span><span style="color: #0000BB">0</span><span style="color: #007700">)&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">+=&nbsp;</span><span style="color: #0000BB">2</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;print(</span><span style="color: #0000BB">$a</span><span style="color: #007700">);<br />}<br /><br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
   </div>

  </div>
 </p>
 <p class="para">
  <div class="example" id="example-133">
   <p><strong>Example #2 Ticks usage example</strong></p>
   <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /><br /></span><span style="color: #007700">function&nbsp;</span><span style="color: #0000BB">tick_handler</span><span style="color: #007700">()<br />{<br />&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"tick_handler()&nbsp;called\n"</span><span style="color: #007700">;<br />}<br /><br /></span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">1</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">tick_handler</span><span style="color: #007700">();<br /><br />if&nbsp;(</span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">&gt;&nbsp;</span><span style="color: #0000BB">0</span><span style="color: #007700">)&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">+=&nbsp;</span><span style="color: #0000BB">2</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">tick_handler</span><span style="color: #007700">();<br />&nbsp;&nbsp;&nbsp;&nbsp;print(</span><span style="color: #0000BB">$a</span><span style="color: #007700">);<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">tick_handler</span><span style="color: #007700">();<br />}<br /></span><span style="color: #0000BB">tick_handler</span><span style="color: #007700">();<br /><br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
   </div>

  </div>
 </p>
 <p class="simpara">
  See also  <span class="function"><a href="function.register-tick-function.php" class="function">register_tick_function()</a></span> and
   <span class="function"><a href="function.unregister-tick-function.php" class="function">unregister_tick_function()</a></span>.
 </p>
 </div>
 <div class="sect2" id="control-structures.declare.encoding">
  <h3 class="title">Encoding</h3>
  <p class="para">
    A script&#039;s encoding can be specified per-script using the encoding directive.
  <div class="example" id="example-134">
   <p><strong>Example #3 Declaring an encoding for the script.</strong></p>
    <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">declare(</span><span style="color: #0000BB">encoding</span><span style="color: #007700">=</span><span style="color: #DD0000">'ISO-8859-1'</span><span style="color: #007700">);<br /></span><span style="color: #FF8000">//&nbsp;code&nbsp;here<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

   </div>
  </p>

  <div class="caution"><strong class="caution">Caution</strong>
   <p class="simpara">
    When combined with namespaces, the only legal syntax for declare
    is <em>declare(encoding=&#039;...&#039;);</em> where <em>...</em>
    is the encoding value.  <em>declare(encoding=&#039;...&#039;) {}</em>
    will result in a parse error when combined with namespaces.
   </p>
  </div>
  <p class="para">
   The encoding declare value is ignored in PHP 5.3 unless php is compiled with
   <em>--enable-zend-multibyte</em>.
  </p>
  <p class="para">
   Note that PHP does not expose whether <em>--enable-zend-multibyte</em> was 
   used to compile PHP other than by  <span class="function"><a href="function.phpinfo.php" class="function">phpinfo()</a></span>.
  </p>
  <p class="para">
   See also <a href="ini.core.php#ini.zend.script-encoding" class="link">zend.script_encoding</a>.
  </p>
   
 </div>
</div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="function.return.php">return<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="control-structures.switch.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />switch</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/control-structures.declare.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=control-structures.declare&amp;redirect=@w{EGQPMCFT}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=control-structures.declare&amp;redirect=@w{EGQPMCFT}">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>declare</strong>
 </div><div id="allnotes">
 <a name="103226"></a>
 <div class="note">
  <strong class='user'>Tom Samplonius</strong>
  <a href="#103226" class="date">01-Apr-2011 03:02</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The tick handler is intended for code profiling.&nbsp; You can use it to determine the number of time units (ticks) that a chunk of code takes.&nbsp; And you can vary the tick frequency so profiling doesn't impact your specific code too much.&nbsp; A tick handler can gather other useful performance data, besides just counting ticks.<br />
<br />
You can use the tick handler to poll that your connection is a alive, but this will block your entire script.&nbsp; Polling connection status is no substitute for checking return values, and using timeouts on any function that connects to an external system.</span>
</code></div>
  </div>
 </div>
 <a name="101483"></a>
 <div class="note">
  <strong class='user'>ramamneh at gmail dot com</strong>
  <a href="#101483" class="date">19-Dec-2010 11:15</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
check loaded server connection <br />
<br />
<span class="default">&lt;?php<br />
$connection&nbsp; </span><span class="keyword">= </span><span class="default">false</span><span class="keyword">;<br />
function </span><span class="default">checkConnection</span><span class="keyword">( </span><span class="default">$connectionWaitingTime </span><span class="keyword">= </span><span class="default">3 </span><span class="keyword">)<br />
{ <br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">// check connection &amp; time <br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">global </span><span class="default">$time</span><span class="keyword">,</span><span class="default">$connection</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; if( (</span><span class="default">$t </span><span class="keyword">= (</span><span class="default">time</span><span class="keyword">() - </span><span class="default">$time</span><span class="keyword">)) &gt;= </span><span class="default">$waitingTime&nbsp; </span><span class="keyword">&amp;&amp; !</span><span class="default">$connection</span><span class="keyword">){&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo (</span><span class="string">"&lt;p&gt; Server not responding&nbsp; for &lt;strong&gt;$t&lt;/strong&gt; seconds !! &lt;/p&gt;"</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; die(</span><span class="string">"Connection aborted"</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
}<br />
<br />
</span><span class="default">register_tick_function</span><span class="keyword">(</span><span class="string">"checkConnection"</span><span class="keyword">);<br />
</span><span class="default">$time </span><span class="keyword">= </span><span class="default">time</span><span class="keyword">();<br />
declare (</span><span class="default">ticks</span><span class="keyword">=</span><span class="default">1</span><span class="keyword">)<br />
{<br />
&nbsp;&nbsp;&nbsp; while( </span><span class="default">true </span><span class="keyword">){ </span><span class="comment">// connecting to loaded server<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">}<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$connection </span><span class="keyword">= </span><span class="default">true </span><span class="keyword">;<br />
}<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="100137"></a>
 <div class="note">
  <strong class='user'>Anonymous</strong>
  <a href="#100137" class="date">27-Sep-2010 02:10</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
It's amazing how many people didn't grasp the concept here. Note the wording in the documentation. It states that the tick handler is called every n native execution cycles. That means native instructions, not including system calls (i'm guessing). This can give you a very good idea if you need to optimize a particular part of your script, since you can measure quite effectively how many native instructions are in your actual code.<br />
<br />
A good profiler would take that into account, and force you, the developer, to include calls to the profiler as you're entering and leaving every function. That way you'd be able to keep an eye on how many cycles it took each function to complete. Independent of time.<br />
<br />
That is extremely powerful, and not to be underestimated. A good solution would allow aggregate stats, so the total time in a function would be counted, including inside called functions.</span>
</code></div>
  </div>
 </div>
 <a name="88929"></a>
 <div class="note">
  <strong class='user'>markandrewslade at dontspamemeat dot gmail</strong>
  <a href="#88929" class="date">13-Feb-2009 09:06</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Note that the two methods for calling declare are not identical.<br />
<br />
Method 1:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="comment">// Print "tick" with a timestamp and optional suffix.<br />
</span><span class="keyword">function </span><span class="default">do_tick</span><span class="keyword">(</span><span class="default">$str </span><span class="keyword">= </span><span class="string">''</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; list(</span><span class="default">$sec</span><span class="keyword">, </span><span class="default">$usec</span><span class="keyword">) = </span><span class="default">explode</span><span class="keyword">(</span><span class="string">' '</span><span class="keyword">, </span><span class="default">microtime</span><span class="keyword">());<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">printf</span><span class="keyword">(</span><span class="string">"[%.4f] Tick.%s\n"</span><span class="keyword">, </span><span class="default">$sec </span><span class="keyword">+ </span><span class="default">$usec</span><span class="keyword">, </span><span class="default">$str</span><span class="keyword">);<br />
}<br />
</span><span class="default">register_tick_function</span><span class="keyword">(</span><span class="string">'do_tick'</span><span class="keyword">);<br />
<br />
</span><span class="comment">// Tick once before declaring so we have a point of reference.<br />
</span><span class="default">do_tick</span><span class="keyword">(</span><span class="string">'--start--'</span><span class="keyword">);<br />
<br />
</span><span class="comment">// Method 1<br />
</span><span class="keyword">declare(</span><span class="default">ticks</span><span class="keyword">=</span><span class="default">1</span><span class="keyword">);<br />
while(</span><span class="default">1</span><span class="keyword">) </span><span class="default">sleep</span><span class="keyword">(</span><span class="default">1</span><span class="keyword">);<br />
<br />
</span><span class="comment">/* Output:<br />
[1234544435.7160] Tick.--start--<br />
[1234544435.7161] Tick.<br />
[1234544435.7162] Tick.<br />
[1234544436.7163] Tick.<br />
[1234544437.7166] Tick.<br />
*/<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
Method 2:<br />
<span class="default">&lt;?php<br />
</span><span class="comment">// Print "tick" with a timestamp and optional suffix.<br />
</span><span class="keyword">function </span><span class="default">do_tick</span><span class="keyword">(</span><span class="default">$str </span><span class="keyword">= </span><span class="string">''</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; list(</span><span class="default">$sec</span><span class="keyword">, </span><span class="default">$usec</span><span class="keyword">) = </span><span class="default">explode</span><span class="keyword">(</span><span class="string">' '</span><span class="keyword">, </span><span class="default">microtime</span><span class="keyword">());<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">printf</span><span class="keyword">(</span><span class="string">"[%.4f] Tick.%s\n"</span><span class="keyword">, </span><span class="default">$sec </span><span class="keyword">+ </span><span class="default">$usec</span><span class="keyword">, </span><span class="default">$str</span><span class="keyword">);<br />
}<br />
</span><span class="default">register_tick_function</span><span class="keyword">(</span><span class="string">'do_tick'</span><span class="keyword">);<br />
<br />
</span><span class="comment">// Tick once before declaring so we have a point of reference.<br />
</span><span class="default">do_tick</span><span class="keyword">(</span><span class="string">'--start--'</span><span class="keyword">);<br />
<br />
</span><span class="comment">// Method 2<br />
</span><span class="keyword">declare(</span><span class="default">ticks</span><span class="keyword">=</span><span class="default">1</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; while(</span><span class="default">1</span><span class="keyword">) </span><span class="default">sleep</span><span class="keyword">(</span><span class="default">1</span><span class="keyword">);<br />
}<br />
<br />
</span><span class="comment">/* Output: <br />
[1234544471.6486] Tick.--start--<br />
[1234544472.6489] Tick.<br />
[1234544473.6490] Tick.<br />
[1234544474.6492] Tick.<br />
[1234544475.6493] Tick.<br />
*/<br />
</span><span class="default">?&gt;<br />
</span><br />
Notice that when using {} after declare, do_tick wasn't auto-called until about 1 second after we entered the declare {} block.&nbsp; However when not using the {}, do_tick was auto-called not once but twice immediately after calling declare();.<br />
<br />
I'm assuming this is due to how PHP handles ticking internally.&nbsp; That is, declare() without the {} seems to trigger more low-level instructions which in turn fires tick a few times (if ticks=1) in the act of declaring.</span>
</code></div>
  </div>
 </div>
 <a name="85290"></a>
 <div class="note">
  <strong class='user'>anotheruser at example dot com</strong>
  <a href="#85290" class="date">23-Aug-2008 04:50</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Code evaluation script which uses debug_backtrace() to get execution time in ns, relative current line number, function, file, and calling function info on each tick, and shove it all in $script_stats array.&nbsp; See debug_backtrace manual to customize what info is collected.<br />
<br />
Warning: this will exhaust allowed memory very easily, so adjust tick counter according to the size of your code.&nbsp; Also, array_key_exists checking on debug_backtrace arrays is removed here only to keep this example simple, but should be added to avoid a large number of resulting PHP Notice errors.<br />
<br />
<span class="default">&lt;?php<br />
<br />
$script_stats </span><span class="keyword">= array();<br />
</span><span class="default">$time </span><span class="keyword">= </span><span class="default">microtime</span><span class="keyword">(</span><span class="default">true</span><span class="keyword">);<br />
<br />
function </span><span class="default">track_stats</span><span class="keyword">(){<br />
&nbsp;&nbsp;&nbsp; global </span><span class="default">$script_stats</span><span class="keyword">,</span><span class="default">$time</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$trace </span><span class="keyword">= </span><span class="default">debug_backtrace</span><span class="keyword">();<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$exe_time </span><span class="keyword">= (</span><span class="default">microtime</span><span class="keyword">(</span><span class="default">true</span><span class="keyword">) - </span><span class="default">$time</span><span class="keyword">) * </span><span class="default">1000</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$func_args </span><span class="keyword">= </span><span class="default">implode</span><span class="keyword">(</span><span class="string">", "</span><span class="keyword">,</span><span class="default">$trace</span><span class="keyword">[</span><span class="default">1</span><span class="keyword">][</span><span class="string">"args"</span><span class="keyword">]);<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$script_stats</span><span class="keyword">[] = array(<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="string">"current_time" </span><span class="keyword">=&gt; </span><span class="default">microtime</span><span class="keyword">(</span><span class="default">true</span><span class="keyword">),<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="string">"memory" </span><span class="keyword">=&gt; </span><span class="default">memory_get_usage</span><span class="keyword">(</span><span class="default">true</span><span class="keyword">),<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="string">"file" </span><span class="keyword">=&gt; </span><span class="default">$trace</span><span class="keyword">[</span><span class="default">1</span><span class="keyword">][</span><span class="string">"file"</span><span class="keyword">].</span><span class="string">': '</span><span class="keyword">.</span><span class="default">$trace</span><span class="keyword">[</span><span class="default">1</span><span class="keyword">][</span><span class="string">"line"</span><span class="keyword">],<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="string">"function" </span><span class="keyword">=&gt; </span><span class="default">$trace</span><span class="keyword">[</span><span class="default">1</span><span class="keyword">][</span><span class="string">"function"</span><span class="keyword">].</span><span class="string">'('</span><span class="keyword">.</span><span class="default">$func_args</span><span class="keyword">.</span><span class="string">')'</span><span class="keyword">,<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="string">"called_by" </span><span class="keyword">=&gt; </span><span class="default">$trace</span><span class="keyword">[</span><span class="default">2</span><span class="keyword">][</span><span class="string">"function"</span><span class="keyword">].</span><span class="string">' in '</span><span class="keyword">.</span><span class="default">$trace</span><span class="keyword">[</span><span class="default">2</span><span class="keyword">][</span><span class="string">"file"</span><span class="keyword">].</span><span class="string">': '</span><span class="keyword">.</span><span class="default">$trace</span><span class="keyword">[</span><span class="default">2</span><span class="keyword">][</span><span class="string">"line"</span><span class="keyword">],<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="string">"ns" </span><span class="keyword">=&gt; </span><span class="default">$exe_time<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$time </span><span class="keyword">= </span><span class="default">microtime</span><span class="keyword">(</span><span class="default">true</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
declare(</span><span class="default">ticks </span><span class="keyword">= </span><span class="default">1</span><span class="keyword">);<br />
</span><span class="default">register_tick_function</span><span class="keyword">(</span><span class="string">"track_stats"</span><span class="keyword">);<br />
<br />
</span><span class="comment">// the rest of your project code<br />
<br />
// output $script_stats into a html table or something<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="80284"></a>
 <div class="note">
  <strong class='user'>zabmilenko at charter dot net</strong>
  <a href="#80284" class="date">08-Jan-2008 01:49</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you misspell the directive, you won't get any error or warning.&nbsp; The declare block will simply act as a nest for statements:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">declare(</span><span class="default">tocks</span><span class="keyword">=</span><span class="string">"four hundred"</span><span class="keyword">)<br />
{<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">// Has no affect on code and produces<br />
&nbsp;&nbsp;&nbsp; // no error or warning.<br />
</span><span class="keyword">}<br />
</span><span class="default">?&gt;<br />
</span><br />
Tested in php 5.2.5 on XPsp2</span>
</code></div>
  </div>
 </div>
 <a name="80252"></a>
 <div class="note">
  <strong class='user'>rsemil at gmail dot com</strong>
  <a href="#80252" class="date">06-Jan-2008 06:30</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
rosen_ivanov's solution can be replaced by a simple call to memory_get_peak_usage() if you're running at least PHP 5.2.0</span>
</code></div>
  </div>
 </div>
 <a name="69242"></a>
 <div class="note">
  <strong class='user'>rosen_ivanov at abv dot bg</strong>
  <a href="#69242" class="date">28-Aug-2006 06:06</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
As Chris already noted, ticks doesn't make your script multi-threaded, but they are still great. I use them mainly for profiling - for example, placing the following at the very beginning of the script allows you to monitor its memory usage:<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">function </span><span class="default">profiler</span><span class="keyword">(</span><span class="default">$return</span><span class="keyword">=</span><span class="default">false</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; static </span><span class="default">$m</span><span class="keyword">=</span><span class="default">0</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; if (</span><span class="default">$return</span><span class="keyword">) return </span><span class="string">"$m bytes"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; if ((</span><span class="default">$mem</span><span class="keyword">=</span><span class="default">memory_get_usage</span><span class="keyword">())&gt;</span><span class="default">$m</span><span class="keyword">) </span><span class="default">$m </span><span class="keyword">= </span><span class="default">$mem</span><span class="keyword">;<br />
}<br />
<br />
</span><span class="default">register_tick_function</span><span class="keyword">(</span><span class="string">'profiler'</span><span class="keyword">);<br />
declare(</span><span class="default">ticks</span><span class="keyword">=</span><span class="default">1</span><span class="keyword">);<br />
<br />
</span><span class="comment">/*<br />
Your code here<br />
*/<br />
<br />
</span><span class="keyword">echo </span><span class="default">profiler</span><span class="keyword">(</span><span class="default">true</span><span class="keyword">);<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
This approach is more accurate than calling memory_get_usage only in the end of the script. It has some performance overhead though :)</span>
</code></div>
  </div>
 </div>
 <a name="66812"></a>
 <div class="note">
  <strong class='user'>aeolianmeson at NOSPAM dot blitzeclipse dot com</strong>
  <a href="#66812" class="date">30-May-2006 12:06</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The scope of the declare() call if used without a block is a little unpredictable, in my experience. It appears that if placed in a method or function, it may not apply to the calls that ensue, like the following:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">a</span><span class="keyword">()<br />
{<br />
&nbsp;&nbsp; declare(</span><span class="default">ticks</span><span class="keyword">=</span><span class="default">2</span><span class="keyword">);<br />
&nbsp;&nbsp; </span><span class="default">b</span><span class="keyword">();<br />
}<br />
<br />
function </span><span class="default">b</span><span class="keyword">()<br />
{<br />
&nbsp;&nbsp; </span><span class="comment">// The declare may not apply here, sometimes.<br />
</span><span class="keyword">}<br />
</span><span class="default">?&gt;<br />
</span><br />
So, if all of a sudden the signals are getting ignored, check this. At the risk of losing the ability to make a mathematical science out of placing a number of activities at varying durations of ticks like many people have chosen to do, I've found it simple to just put this at the top of the code, and just make it global.</span>
</code></div>
  </div>
 </div>
 <a name="59867"></a>
 <div class="note">
  <strong class='user'>warhog at warhog dot net</strong>
  <a href="#59867" class="date">18-Dec-2005 12:39</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
as i read about ticks the first time i thought "wtf, useless crap" - but then i discovered some usefull application...<br />
<br />
you can declare a tick-function which checks each n executions of your script whether the connection is still alive or not, very usefull for some kind of scripts to decrease serverload<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">function </span><span class="default">check_connection</span><span class="keyword">()<br />
{ if (</span><span class="default">connection_aborted</span><span class="keyword">())<br />
&nbsp;&nbsp; { </span><span class="comment">// do something here, e.g. close database connections<br />
&nbsp;&nbsp; &nbsp;&nbsp; // (or&nbsp; use a shutdown function for this<br />
&nbsp;&nbsp; &nbsp;&nbsp; </span><span class="keyword">exit; }<br />
}<br />
<br />
</span><span class="default">register_tick_function</span><span class="keyword">(</span><span class="string">"connection"</span><span class="keyword">);<br />
<br />
declare (</span><span class="default">ticks</span><span class="keyword">=</span><span class="default">20</span><span class="keyword">)<br />
{<br />
&nbsp; </span><span class="comment">// put your PHP-Script here<br />
&nbsp; // you may increase/decrease the number of ticks<br />
</span><span class="keyword">}<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="50447"></a>
 <div class="note">
  <strong class='user'>chris-at-free-source.com</strong>
  <a href="#50447" class="date">28-Feb-2005 12:16</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Also note that PHP is run in a single thread and so everything it does will be one line of code at a time.&nbsp; I'm not aware of any true threading support in PHP, the closest you can get is to fork.<br />
<br />
so, declare tick doens't "multi-thread" at all, it is simply is a way to automaticaly call a function every n-lines of code.</span>
</code></div>
  </div>
 </div>
 <a name="33788"></a>
 <div class="note">
  <strong class='user'>fok at nho dot com dot br</strong>
  <a href="#33788" class="date">07-Jul-2003 06:45</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
This is a very simple example using ticks to execute a external script to show rx/tx data from the server<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">function </span><span class="default">traf</span><span class="keyword">(){<br />
&nbsp; </span><span class="default">passthru</span><span class="keyword">( </span><span class="string">'./traf.sh' </span><span class="keyword">);<br />
&nbsp; echo </span><span class="string">"&lt;br /&gt;\n"</span><span class="keyword">;<br />
&nbsp; </span><span class="default">flush</span><span class="keyword">(); </span><span class="comment">// keeps it flowing to the browser...<br />
&nbsp; </span><span class="default">sleep</span><span class="keyword">( </span><span class="default">1 </span><span class="keyword">);<br />
}<br />
<br />
</span><span class="default">register_tick_function</span><span class="keyword">( </span><span class="string">"traf" </span><span class="keyword">);<br />
<br />
declare( </span><span class="default">ticks</span><span class="keyword">=</span><span class="default">1 </span><span class="keyword">){<br />
&nbsp; while( </span><span class="default">true </span><span class="keyword">){}&nbsp;&nbsp; </span><span class="comment">// to keep it running...<br />
</span><span class="keyword">}<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
contents of traf.sh:<br />
# Shows TX/RX for eth0 over 1sec<br />
#!/bin/bash<br />
<br />
TX1=`cat /proc/net/dev | grep "eth0" | cut -d: -f2 | awk '{print $9}'`<br />
RX1=`cat /proc/net/dev | grep "eth0" | cut -d: -f2 | awk '{print $1}'`<br />
sleep 1<br />
TX2=`cat /proc/net/dev | grep "eth0" | cut -d: -f2 | awk '{print $9}'`<br />
RX2=`cat /proc/net/dev | grep "eth0" | cut -d: -f2 | awk '{print $1}'`<br />
<br />
echo -e "TX: $[ $TX2 - $TX1 ] bytes/s \t RX: $[ $RX2 - $RX1 ] bytes/s"<br />
#--= the end. =--</span>
</code></div>
  </div>
 </div>
 <a name="29085"></a>
 <div class="note">
  <strong class='user'>daniel@swn</strong>
  <a href="#29085" class="date">01-Feb-2003 11:56</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
<span class="default">&lt;?php<br />
ob_end_clean</span><span class="keyword">();<br />
</span><span class="default">ob_implicit_flush</span><span class="keyword">(</span><span class="default">1</span><span class="keyword">);<br />
<br />
function </span><span class="default">a</span><span class="keyword">() {<br />
&nbsp;for(</span><span class="default">$i</span><span class="keyword">=</span><span class="default">0</span><span class="keyword">;</span><span class="default">$i</span><span class="keyword">&lt;=</span><span class="default">100000</span><span class="keyword">;</span><span class="default">$i</span><span class="keyword">++) { }<br />
&nbsp;echo </span><span class="string">"function a() "</span><span class="keyword">;<br />
}<br />
function </span><span class="default">b</span><span class="keyword">() {<br />
&nbsp;for(</span><span class="default">$i</span><span class="keyword">=</span><span class="default">0</span><span class="keyword">;</span><span class="default">$i</span><span class="keyword">&lt;=</span><span class="default">100000</span><span class="keyword">;</span><span class="default">$i</span><span class="keyword">++) { }<br />
&nbsp;echo </span><span class="string">"function b() "</span><span class="keyword">;<br />
}<br />
<br />
</span><span class="default">register_tick_function </span><span class="keyword">(</span><span class="string">"a"</span><span class="keyword">);<br />
</span><span class="default">register_tick_function </span><span class="keyword">(</span><span class="string">"b"</span><span class="keyword">);<br />
<br />
declare (</span><span class="default">ticks</span><span class="keyword">=</span><span class="default">4</span><span class="keyword">)<br />
{<br />
&nbsp;&nbsp;&nbsp; while(</span><span class="default">true</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">sleep</span><span class="keyword">(</span><span class="default">1</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">"\n&lt;br&gt;&lt;b&gt;"</span><span class="keyword">.</span><span class="default">time</span><span class="keyword">().</span><span class="string">"&lt;/b&gt;&lt;br&gt;\n"</span><span class="keyword">;;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
</span><span class="default">?&gt;<br />
</span>You will see that a() and b() are slowing down this process. They are in fact not executed every second as expected. So this function is not a real alternative for multithreading using some slow functions..there is no difference to this way: while (true) { a(); b(); sleep(1); }</span>
</code></div>
  </div>
 </div>
 <a name="28278"></a>
 <div class="note">
  <strong class='user'>xxoes</strong>
  <a href="#28278" class="date">08-Jan-2003 02:23</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If i use ticks i must declare all functions before i call the function.<br />
<br />
example:<br />
<br />
Dosn't work<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">ticks</span><span class="keyword">() {<br />
&nbsp;&nbsp; echo </span><span class="string">"tick"</span><span class="keyword">;<br />
}<br />
</span><span class="default">register_tick_function</span><span class="keyword">(</span><span class="string">"ticks"</span><span class="keyword">);<br />
<br />
declare (</span><span class="default">ticks</span><span class="keyword">=</span><span class="default">1</span><span class="keyword">) </span><span class="default">1</span><span class="keyword">;<br />
<br />
echo </span><span class="string">""</span><span class="keyword">;<br />
echo </span><span class="string">""</span><span class="keyword">;<br />
<br />
</span><span class="default">foo</span><span class="keyword">(); </span><span class="comment">// Call to undefined function.<br />
<br />
</span><span class="keyword">function </span><span class="default">foo</span><span class="keyword">() {<br />
&nbsp;&nbsp; echo </span><span class="string">"foo"</span><span class="keyword">;<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
Work<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">ticks</span><span class="keyword">() {<br />
&nbsp;&nbsp; echo </span><span class="string">"tick"</span><span class="keyword">;<br />
}<br />
</span><span class="default">register_tick_function</span><span class="keyword">(</span><span class="string">"ticks"</span><span class="keyword">);<br />
<br />
</span><span class="comment">//declare (ticks=1) 1;<br />
<br />
</span><span class="keyword">echo </span><span class="string">""</span><span class="keyword">;<br />
echo </span><span class="string">""</span><span class="keyword">;<br />
<br />
</span><span class="default">foo</span><span class="keyword">();<br />
<br />
function </span><span class="default">foo</span><span class="keyword">() {<br />
&nbsp;&nbsp; echo </span><span class="string">"foo"</span><span class="keyword">;<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
win2k : PHP 4.3.0 (cgi-fcgi)</span>
</code></div>
  </div>
 </div>
 <a name="20012"></a>
 <div class="note">
  <strong class='user'>rob_spamsux at rauchmedien dot ihatespam dot com</strong>
  <a href="#20012" class="date">19-Mar-2002 02:45</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Correction to above note:<br />
<br />
Apparently, the end brace '}' at the end of the statement causes a tick.<br />
<br />
So using<br />
<br />
------------<br />
declare (ticks=1) echo "1 tick after this prints";<br />
------------<br />
<br />
gives the expected behavior of causing 1 tick.<br />
<br />
Note: the tick is issued after the statement executes.<br />
<br />
Also, after playing around with this, I found that it is not really the multi-tasking I had expected. It behaves the same as simply calling the functions. I.e. each function must finish before passing the baton to the next function. They do not run in parallel.<br />
<br />
It also seems that they always run in the order in which they were registered.<br />
<br />
So,<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">------------<br />
</span><span class="comment"># register tick functions<br />
</span><span class="default">register_tick_function </span><span class="keyword">(</span><span class="string">"a"</span><span class="keyword">);<br />
</span><span class="default">register_tick_function </span><span class="keyword">(</span><span class="string">"b"</span><span class="keyword">);<br />
<br />
</span><span class="comment"># make the tick functions run<br />
</span><span class="keyword">declare (</span><span class="default">ticks</span><span class="keyword">=</span><span class="default">1</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span>------------<br />
<br />
is equivalent to<br />
<br />
------------<br />
a();<br />
b();<br />
------------<br />
<br />
It is simply a convenient way to have functions called periodically while some other code is being executed. I.e. you could use it to periodically check the status of something and then exit the script or do something else based on the status.</span>
</code></div>
  </div>
 </div>
 <a name="20010"></a>
 <div class="note">
  <strong class='user'>rob_spamsux at rauchmedien dot ihatespam dot com</strong>
  <a href="#20010" class="date">19-Mar-2002 01:58</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Here is an example of multi-tasking / multi-threading:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="comment"># declare functions<br />
</span><span class="keyword">function </span><span class="default">a</span><span class="keyword">() {<br />
&nbsp; echo </span><span class="string">"a"</span><span class="keyword">;<br />
}<br />
function </span><span class="default">b</span><span class="keyword">() {<br />
&nbsp; echo </span><span class="string">"b"</span><span class="keyword">;<br />
}<br />
<br />
</span><span class="comment"># register tick functions<br />
</span><span class="default">register_tick_function </span><span class="keyword">(</span><span class="string">"a"</span><span class="keyword">);<br />
</span><span class="default">register_tick_function </span><span class="keyword">(</span><span class="string">"b"</span><span class="keyword">);<br />
<br />
</span><span class="comment"># make the tick functions run<br />
</span><span class="keyword">declare (</span><span class="default">ticks</span><span class="keyword">=</span><span class="default">1</span><span class="keyword">);<br />
<br />
</span><span class="comment"># that's all there is to it.<br />
</span><span class="default">?&gt;<br />
</span><br />
Notes:<br />
This will make functions a and b run once each at the same time.<br />
<br />
If you try:<br />
<br />
declare (ticks=1) {<br />
&nbsp; 1;<br />
}<br />
<br />
They will run twice each. That is because it seems to be an undocumented fact that there is always an extra tick.<br />
<br />
Therefore:<br />
<br />
declare (ticks=2) {<br />
&nbsp; 1;<br />
}<br />
<br />
Will cause them to run once.</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=control-structures.declare&amp;redirect=@w{EGQPMCFT}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=control-structures.declare&amp;redirect=@w{EGQPMCFT}">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/control-structures.declare.php">show source</a> |
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