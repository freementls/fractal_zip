<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Error Control Operators - Manual</title>
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
 <link rel="prev" href="language.operators.comparison.php" />
 <link rel="next" href="language.operators.execution.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/operators.errorcontrol" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/language.operators.errorcontrol.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="@w{YWEACPZ3}" />
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
 <li><a href="language.operators.assignment.php">Assignment Operators</a></li>
 <li><a href="language.operators.bitwise.php">Bitwise Operators</a></li>
 <li><a href="language.operators.comparison.php">Comparison Operators</a></li>
 <li class="active"><a href="language.operators.errorcontrol.php">Error Control Operators</a></li>
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
  <a href="language.operators.execution.php">Execution Operators<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.operators.comparison.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Comparison Operators</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.operators.errorcontrol.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/language.operators.errorcontrol.php">Brazilian Portuguese</option>
    <option value="zh/language.operators.errorcontrol.php">Chinese (Simplified)</option>
    <option value="fr/language.operators.errorcontrol.php">French</option>
    <option value="de/language.operators.errorcontrol.php">German</option>
    <option value="ja/language.operators.errorcontrol.php">Japanese</option>
    <option value="pl/language.operators.errorcontrol.php">Polish</option>
    <option value="ro/language.operators.errorcontrol.php">Romanian</option>
    <option value="ru/language.operators.errorcontrol.php">Russian</option>
    <option value="fa/language.operators.errorcontrol.php">Persian</option>
    <option value="es/language.operators.errorcontrol.php">Spanish</option>
    <option value="tr/language.operators.errorcontrol.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="language.operators.errorcontrol" class="sect1">
   <h2 class="title">Error Control Operators</h2>
   <p class="simpara">
    PHP supports one error control operator: the at sign (@). When
    prepended to an expression in PHP, any error messages that might
    be generated by that expression will be ignored.
   </p>
   <p class="simpara">
    If you have set a custom error handler function with 
     <span class="function"><a href="function.set-error-handler.php" class="function">set_error_handler()</a></span> then it will still get 
    called, but this custom error handler can (and should) call  <span class="function"><a href="function.error-reporting.php" class="function">error_reporting()</a></span>
    which will return 0 when the call that triggered the error was preceded by an @.
   </p>
   <p class="simpara">
    If the <a href="errorfunc.configuration.php#ini.track-errors" class="link"><strong class="option unknown">track_errors</strong>
</a>
    feature is enabled, any error message generated by the expression
    will be saved in the variable
    <var class="varname"><var class="varname"><a href="reserved.variables.phperrormsg.php" class="classname">$php_errormsg</a></var></var>.
    This variable will be overwritten on each error, so check early if you
    want to use it.
   </p>
   <p class="para">
    <div class="informalexample">
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #FF8000">/*&nbsp;Intentional&nbsp;file&nbsp;error&nbsp;*/<br /></span><span style="color: #0000BB">$my_file&nbsp;</span><span style="color: #007700">=&nbsp;@</span><span style="color: #0000BB">file&nbsp;</span><span style="color: #007700">(</span><span style="color: #DD0000">'non_existent_file'</span><span style="color: #007700">)&nbsp;or<br />&nbsp;&nbsp;&nbsp;&nbsp;die&nbsp;(</span><span style="color: #DD0000">"Failed&nbsp;opening&nbsp;file:&nbsp;error&nbsp;was&nbsp;'</span><span style="color: #0000BB">$php_errormsg</span><span style="color: #DD0000">'"</span><span style="color: #007700">);<br /><br /></span><span style="color: #FF8000">//&nbsp;this&nbsp;works&nbsp;for&nbsp;any&nbsp;expression,&nbsp;not&nbsp;just&nbsp;functions:<br /></span><span style="color: #0000BB">$value&nbsp;</span><span style="color: #007700">=&nbsp;@</span><span style="color: #0000BB">$cache</span><span style="color: #007700">[</span><span style="color: #0000BB">$key</span><span style="color: #007700">];<br /></span><span style="color: #FF8000">//&nbsp;will&nbsp;not&nbsp;issue&nbsp;a&nbsp;notice&nbsp;if&nbsp;the&nbsp;index&nbsp;$key&nbsp;doesn't&nbsp;exist.<br /><br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

    </div>
   </p>
   <blockquote class="note"><p><strong class="note">Note</strong>: 
    <span class="simpara">
     The @-operator works only on
     <a href="language.expressions.php" class="link">expressions</a>. A simple rule
     of thumb is: if you can take the value of something, you can prepend
     the @ operator to it. For instance, you can prepend it to variables,
     function and  <span class="function"><a href="function.include.php" class="function">include</a></span> calls, constants, and
     so forth. You cannot prepend it to function or class definitions,
     or conditional structures such as <em>if</em> and
     <a href="control-structures.foreach.php" class="link">foreach</a>, and so forth.
    </span>
   </p></blockquote>
   <p class="simpara">
    See also  <span class="function"><a href="function.error-reporting.php" class="function">error_reporting()</a></span> and the manual section for
    <a href="ref.errorfunc.php" class="link">Error Handling and Logging functions</a>.
   </p>
   <div class="warning"><strong class="warning">Warning</strong>
    <p class="para">
     Currently the &quot;@&quot; error-control operator prefix will even disable
     error reporting for critical errors that will terminate script
     execution. Among other things, this means that if you use &quot;@&quot; to
     suppress errors from a certain function and either it isn&#039;t
     available or has been mistyped, the script will die right there
     with no indication as to why.
    </p>
   </div>
  </div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="language.operators.execution.php">Execution Operators<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.operators.comparison.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Comparison Operators</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.operators.errorcontrol.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=language.operators.errorcontrol&amp;redirect=@w{YWEACPZ3}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.operators.errorcontrol&amp;redirect=@w{YWEACPZ3}">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Error Control Operators</strong>
 </div><div id="allnotes">
 <a name="104545"></a>
 <div class="note">
  <strong class='user'>bohwaz</strong>
  <a href="#104545" class="date">22-Jun-2011 06:27</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you use the ErrorException exception to have a unified error management, I'll advise you to test against error_reporting in the error handler, not in the exception handler as you might encounter some headaches like blank pages as error_reporting might not be transmitted to exception handler.<br />
<br />
So instead of :<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">function </span><span class="default">exception_error_handler</span><span class="keyword">(</span><span class="default">$errno</span><span class="keyword">, </span><span class="default">$errstr</span><span class="keyword">, </span><span class="default">$errfile</span><span class="keyword">, </span><span class="default">$errline </span><span class="keyword">)<br />
{<br />
&nbsp;&nbsp;&nbsp; throw new </span><span class="default">ErrorException</span><span class="keyword">(</span><span class="default">$errstr</span><span class="keyword">, </span><span class="default">0</span><span class="keyword">, </span><span class="default">$errno</span><span class="keyword">, </span><span class="default">$errfile</span><span class="keyword">, </span><span class="default">$errline</span><span class="keyword">);<br />
}<br />
<br />
</span><span class="default">set_error_handler</span><span class="keyword">(</span><span class="string">"exception_error_handler"</span><span class="keyword">);<br />
<br />
function </span><span class="default">catchException</span><span class="keyword">(</span><span class="default">$e</span><span class="keyword">)<br />
{<br />
&nbsp;&nbsp;&nbsp; if (</span><span class="default">error_reporting</span><span class="keyword">() === </span><span class="default">0</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return;<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">// Do some stuff<br />
</span><span class="keyword">}<br />
<br />
</span><span class="default">set_exception_handler</span><span class="keyword">(</span><span class="string">'catchException'</span><span class="keyword">);<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
It would be better to do :<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">function </span><span class="default">exception_error_handler</span><span class="keyword">(</span><span class="default">$errno</span><span class="keyword">, </span><span class="default">$errstr</span><span class="keyword">, </span><span class="default">$errfile</span><span class="keyword">, </span><span class="default">$errline </span><span class="keyword">)<br />
{<br />
&nbsp;&nbsp;&nbsp; if (</span><span class="default">error_reporting</span><span class="keyword">() === </span><span class="default">0</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return;<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; throw new </span><span class="default">ErrorException</span><span class="keyword">(</span><span class="default">$errstr</span><span class="keyword">, </span><span class="default">0</span><span class="keyword">, </span><span class="default">$errno</span><span class="keyword">, </span><span class="default">$errfile</span><span class="keyword">, </span><span class="default">$errline</span><span class="keyword">);<br />
}<br />
<br />
</span><span class="default">set_error_handler</span><span class="keyword">(</span><span class="string">"exception_error_handler"</span><span class="keyword">);<br />
<br />
function </span><span class="default">catchException</span><span class="keyword">(</span><span class="default">$e</span><span class="keyword">)<br />
{<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">// Do some stuff<br />
</span><span class="keyword">}<br />
<br />
</span><span class="default">set_exception_handler</span><span class="keyword">(</span><span class="string">'catchException'</span><span class="keyword">);<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="102543"></a>
 <div class="note">
  <strong class='user'>anthon at piwik dot org</strong>
  <a href="#102543" class="date">20-Feb-2011 12:39</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you're wondering what the performance impact of using the @ operator is, consider this example.&nbsp; Here, the second script (using the @ operator) takes 1.75x as long to execute...almost double the time of the first script.<br />
<br />
So while yes, there is some overhead, per iteration, we see that the @ operator added only .005 ms per call.&nbsp; Not reason enough, imho, to avoid using the @ operator.<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">x</span><span class="keyword">() { }<br />
for (</span><span class="default">$i </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">; </span><span class="default">$i </span><span class="keyword">&lt; </span><span class="default">1000000</span><span class="keyword">; </span><span class="default">$i</span><span class="keyword">++) { </span><span class="default">x</span><span class="keyword">(); }<br />
</span><span class="default">?&gt;<br />
</span><br />
real&nbsp; &nbsp; 0m7.617s<br />
user&nbsp; &nbsp; 0m6.788s<br />
sys&nbsp; &nbsp; 0m0.792s<br />
<br />
vs<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">x</span><span class="keyword">() { }<br />
for (</span><span class="default">$i </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">; </span><span class="default">$i </span><span class="keyword">&lt; </span><span class="default">1000000</span><span class="keyword">; </span><span class="default">$i</span><span class="keyword">++) { @</span><span class="default">x</span><span class="keyword">(); }<br />
</span><span class="default">?&gt;<br />
</span><br />
real&nbsp; &nbsp; 0m13.333s<br />
user&nbsp; &nbsp; 0m12.437s<br />
sys&nbsp; &nbsp; 0m0.836s</span>
</code></div>
  </div>
 </div>
 <a name="99805"></a>
 <div class="note">
  <strong class='user'>auser at anexample dot com</strong>
  <a href="#99805" class="date">08-Sep-2010 04:02</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Be aware that using @ is dog-slow, as PHP incurs overhead to suppressing errors in this way. It's a trade-off between speed and convenience.</span>
</code></div>
  </div>
 </div>
 <a name="98895"></a>
 <div class="note">
  <strong class='user'>darren at powerssa dot com</strong>
  <a href="#98895" class="date">14-Jul-2010 09:33</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
After some time investigating as to why I was still getting errors that were supposed to be suppressed with @ I found the following.<br />
<br />
1. If you have set your own default error handler then the error still gets sent to the error handler regardless of the @ sign.<br />
<br />
2. As mentioned below the @ suppression only changes the error level for that call. This is not to say that in your error handler you can check the given $errno for a value of 0 as the $errno will still refer to the TYPE(not the error level) of error e.g. E_WARNING or E_ERROR etc<br />
<br />
3. The @ only changes the rumtime error reporting level just for that one call to 0. This means inside your custom error handler you can check the current runtime error_reporting level using error_reporting() (note that one must NOT pass any parameter to this function if you want to get the current value) and if its zero then you know that it has been suppressed.<br />
<span class="default">&lt;?php<br />
</span><span class="comment">// Custom error handler<br />
</span><span class="keyword">function </span><span class="default">myErrorHandler</span><span class="keyword">(</span><span class="default">$errno</span><span class="keyword">, </span><span class="default">$errstr</span><span class="keyword">, </span><span class="default">$errfile</span><span class="keyword">, </span><span class="default">$errline</span><span class="keyword">)<br />
{<br />
&nbsp;&nbsp;&nbsp; if ( </span><span class="default">0 </span><span class="keyword">== </span><span class="default">error_reporting </span><span class="keyword">() ) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// Error reporting is currently turned off or suppressed with @<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">return;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">// Do your normal custom error reporting here<br />
</span><span class="keyword">}<br />
</span><span class="default">?&gt;<br />
</span><br />
For more info on setting a custom error handler see: <a href="http://php.net/manual/en/function.set-error-handler.php" rel="nofollow" target="_blank">http://php.net/manual/en/function.set-error-handler.php</a><br />
For more info on error_reporting see: <a href="http://www.php.net/manual/en/function.error-reporting.php" rel="nofollow" target="_blank">http://www.php.net/manual/en/function.error-reporting.php</a></span>
</code></div>
  </div>
 </div>
 <a name="94004"></a>
 <div class="note">
  <strong class='user'>M. T.</strong>
  <a href="#94004" class="date">11-Oct-2009 09:20</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Be aware of using error control operator in statements before include() like this:<br />
<br />
<span class="default">&lt;?PHP<br />
<br />
</span><span class="keyword">(@include(</span><span class="string">"file.php"</span><span class="keyword">))<br />
&nbsp;OR die(</span><span class="string">"Could not find file.php!"</span><span class="keyword">);<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
This cause, that error reporting level is set to zero also for the included file. So if there are some errors in the included file, they will be not displayed.</span>
</code></div>
  </div>
 </div>
 <a name="93300"></a>
 <div class="note">
  <strong class='user'>dsbeam at gmail dot com</strong>
  <a href="#93300" class="date">01-Sep-2009 07:22</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Though error suppression can be dangerous at times, it can be useful as well.&nbsp; I've found the following statements roughly equivalent:<br />
<br />
&nbsp;&nbsp; &nbsp; if( isset( $var ) &amp;&amp; $var === $something )<br />
&nbsp;&nbsp; &nbsp; if( @$var === $something )<br />
<br />
EXCEPT when you're comparing against a boolean value (when $something is false).&nbsp; In that case, if it's not set the conditional will still be triggered.<br />
<br />
I've found this useful when I want to check a value that might not exist:<br />
<br />
&nbsp;&nbsp; &nbsp; if( @$_SERVER[ 'HTTP_REFERER' ] !== '/www/some/path/file' )<br />
<br />
or when we want to see if a checkbox / radio button have been submitted with a post action<br />
<br />
&nbsp;&nbsp; &nbsp; if( @$_POST[ 'checkbox' ] === 'yes' )<br />
<br />
Just letting you guys know my findings, :)</span>
</code></div>
  </div>
 </div>
 <a name="90987"></a>
 <div class="note">
  <strong class='user'>gerrywastaken</strong>
  <a href="#90987" class="date">19-May-2009 01:46</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Error suppression should be avoided if possible as it doesn't just suppress the error that you are trying to stop, but will also suppress errors that you didn't predict would ever occur. This will make debugging a nightmare.<br />
<br />
It is far better to test for the condition that you know will cause an error before preceding to run the code. This way only the error that you know about will be suppressed and not all future errors associated with that piece of code.<br />
<br />
There may be a good reason for using outright error suppression in favor of the method I have suggested, however in the many years I've spent programming web apps I've yet to come across a situation where it was a good solution. The examples given on this manual page are certainly not situations where the error control operator should be used.</span>
</code></div>
  </div>
 </div>
 <a name="85042"></a>
 <div class="note">
  <strong class='user'>taras dot dot dot di at gmail dot com</strong>
  <a href="#85042" class="date">12-Aug-2008 08:29</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I was confused as to what the @ symbol actually does, and after a few experiments have concluded the following:<br />
<br />
* the error handler that is set gets called regardless of what level the error reporting is set on, or whether the statement is preceeded with @<br />
<br />
* it is up to the error handler to impart some meaning on the different error levels. You could make your custom error handler echo all errors, even if error reporting is set to NONE.<br />
<br />
* so what does the @ operator do? It temporarily sets the error reporting level to 0 for that line. If that line triggers an error, the error handler will still be called, but it will be called with an error level of 0<br />
<br />
Hope this helps someone</span>
</code></div>
  </div>
 </div>
 <a name="83457"></a>
 <div class="note">
  <strong class='user'>beatngu</strong>
  <a href="#83457" class="date">27-May-2008 02:29</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
NB The @ operator doesn't work when throwing errors as exceptions using the ErrorException class</span>
</code></div>
  </div>
 </div>
 <a name="72126"></a>
 <div class="note">
  <strong class='user'>nospam at blog dot fileville dot net</strong>
  <a href="#72126" class="date">03-Jan-2007 11:58</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you want to log all the error messages for a php script from a session you can use something like this:<br />
<span class="default">&lt;?php<br />
&nbsp;session_start</span><span class="keyword">();<br />
&nbsp; function </span><span class="default">error</span><span class="keyword">(</span><span class="default">$error</span><span class="keyword">, </span><span class="default">$return</span><span class="keyword">=</span><span class="default">FALSE</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp;&nbsp; global </span><span class="default">$php_errormsg</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp;&nbsp; if(isset(</span><span class="default">$_SESSION</span><span class="keyword">[</span><span class="string">'php_errors'</span><span class="keyword">])) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$_SESSION</span><span class="keyword">[</span><span class="string">'php_errors'</span><span class="keyword">] = array();&nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp; </span><span class="default">$_SESSION</span><span class="keyword">[</span><span class="string">'php_errors'</span><span class="keyword">][] = </span><span class="default">$error</span><span class="keyword">; </span><span class="comment">// Maybe use $php_errormsg<br />
&nbsp; </span><span class="keyword">if(</span><span class="default">$return </span><span class="keyword">== </span><span class="default">TRUE</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$message </span><span class="keyword">= </span><span class="string">""</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; foreach(</span><span class="default">$_SESSION</span><span class="keyword">[</span><span class="string">'php_errors'</span><span class="keyword">] as </span><span class="default">$php_error</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$messages </span><span class="keyword">.= </span><span class="default">$php_error</span><span class="keyword">.</span><span class="string">"\n"</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; }&nbsp; <br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">$messages</span><span class="keyword">; </span><span class="comment">// Or you can use use $_SESSION['php_errors']<br />
&nbsp; </span><span class="keyword">}<br />
}<br />
</span><span class="default">?&gt;<br />
</span>Hope this helps someone...</span>
</code></div>
  </div>
 </div>
 <a name="71713"></a>
 <div class="note">
  <a href="#71713" class="date">12-Dec-2006 05:52</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
error_reporting()==0 for detecting the @ error suppression assumes that you did not set the error level to 0 in the first place.<br />
<br />
However, typically if you want to set your own error handler, you would set the error_reporting to 0. Therefore, an alternative to detect the @ error suppression is required.</span>
</code></div>
  </div>
 </div>
 <a name="70375"></a>
 <div class="note">
  <strong class='user'>programming at kennebel dot com</strong>
  <a href="#70375" class="date">13-Oct-2006 06:38</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
To suppress errors for a new class/object:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="comment">// Tested: PHP 5.1.2 ~ 2006-10-13<br />
<br />
// Typical Example<br />
</span><span class="default">$var </span><span class="keyword">= @</span><span class="default">some_function</span><span class="keyword">();<br />
<br />
</span><span class="comment">// Class/Object Example<br />
</span><span class="default">$var </span><span class="keyword">= @new </span><span class="default">some_class</span><span class="keyword">();<br />
<br />
</span><span class="comment">// Does NOT Work!<br />
//$var = new @some_class(); // syntax error<br />
</span><span class="default">?&gt;<br />
</span><br />
I found this most useful when connecting to a<br />
database, where i wanted to control the errors<br />
and warnings displayed to the client, while still<br />
using the class style of access.</span>
</code></div>
  </div>
 </div>
 <a name="50565"></a>
 <div class="note">
  <strong class='user'>me at hesterc dot fsnet dot co dot uk</strong>
  <a href="#50565" class="date">03-Mar-2005 08:25</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you wish to display some text when an error occurs, echo doesn't work. Use print instead. This is explained on the following link 'What is the difference between echo and print?':<br />
<br />
<a href="http://www.faqts.com/knowledge_base/view.phtml/aid/1/fid/40" rel="nofollow" target="_blank">http://www.faqts.com/knowledge_base/view.phtml/aid/1/fid/40</a><br />
<br />
It says "print can be used as part of a more complex expression where echo cannot".<br />
<br />
Also, you can add multiple code to the result when an error occurs by separating each line with "and". Here is an example:<br />
<br />
<span class="default">&lt;?php<br />
$my_file </span><span class="keyword">= @</span><span class="default">file </span><span class="keyword">(</span><span class="string">'non_existent_file'</span><span class="keyword">) or print </span><span class="string">'File not found.' </span><span class="keyword">and </span><span class="default">$string </span><span class="keyword">= </span><span class="string">' Honest!' </span><span class="keyword">and print </span><span class="default">$string </span><span class="keyword">and </span><span class="default">$fp </span><span class="keyword">= </span><span class="default">fopen </span><span class="keyword">(</span><span class="string">'error_log.txt'</span><span class="keyword">, </span><span class="string">'wb+'</span><span class="keyword">) and </span><span class="default">fwrite</span><span class="keyword">(</span><span class="default">$fp</span><span class="keyword">, </span><span class="default">$string</span><span class="keyword">) and </span><span class="default">fclose</span><span class="keyword">(</span><span class="default">$fp</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span><br />
A shame you can't use curly brackets above to enclose multiple lines of code, like you can with an if statement or a loop. It could make for a single long line of code. You could always call a function instead.</span>
</code></div>
  </div>
 </div>
 <a name="48491"></a>
 <div class="note">
  <strong class='user'>frogger at netsurf dot de</strong>
  <a href="#48491" class="date">26-Dec-2004 08:19</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Better use the function trigger_error() (<a href="http://de.php.net/manual/en/function.trigger-error.php" rel="nofollow" target="_blank">http://de.php.net/manual/en/function.trigger-error.php</a>)<br />
to display defined notices, warnings and errors than check the error level your self. this lets you write messages to logfiles if defined in the php.ini, output<br />
messages in dependency to the error_reporting() level and suppress output using the @-sign.</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=language.operators.errorcontrol&amp;redirect=@w{YWEACPZ3}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.operators.errorcontrol&amp;redirect=@w{YWEACPZ3}">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/language.operators.errorcontrol.php">show source</a> |
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