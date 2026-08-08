<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: ErrorException - Manual</title>
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
 <link rel="index" href="reserved.exceptions.php" />
 <link rel="prev" href="exception.clone.php" />
 <link rel="next" href="errorexception.construct.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/errorexception" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="alternate" href="/manual/en/feeds/class.errorexception.atom" type="application/atom+xml" />
 <link rel="canonical" href="http://php.net/manual/en/class.errorexception.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/class.errorexception.php" />
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
 <li class="header up"><a href="reserved.exceptions.php">Predefined Exceptions</a></li>
 <li><a href="class.exception.php">Exception</a></li>
 <li class="active"><a href="class.errorexception.php">ErrorException</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="errorexception.construct.php">ErrorException::__construct<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="exception.clone.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Exception::__clone</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/class.errorexception.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/class.errorexception.php">Brazilian Portuguese</option>
    <option value="zh/class.errorexception.php">Chinese (Simplified)</option>
    <option value="fr/class.errorexception.php">French</option>
    <option value="de/class.errorexception.php">German</option>
    <option value="ja/class.errorexception.php">Japanese</option>
    <option value="pl/class.errorexception.php">Polish</option>
    <option value="ro/class.errorexception.php">Romanian</option>
    <option value="ru/class.errorexception.php">Russian</option>
    <option value="fa/class.errorexception.php">Persian</option>
    <option value="es/class.errorexception.php">Spanish</option>
    <option value="tr/class.errorexception.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="class.errorexception" class="reference">
 <h1 class="title">ErrorException</h1>
 
 
 <div class="partintro"><p class="verinfo">(PHP 5 &gt;= 5.1.0)</p>
 

  <div class="section" id="errorexception.intro">
   <h2 class="title">Introduction</h2>
   <p class="para">
    An Error Exception.
   </p>
  </div>

 
  <div class="section" id="errorexception.synopsis">
   <h2 class="title">Class synopsis</h2>
 

   <div class="classsynopsis">
    <div class="ooclass"></div>
 

    <div class="classsynopsisinfo">
     <span class="ooclass">
      <strong class="classname">ErrorException</strong>
     </span>
 
     <span class="ooclass">
      <span class="modifier">extends</span>
      <a href="class.exception.php" class="classname">Exception</a>
     </span>
     {</div>

 
    <div class="classsynopsisinfo classsynopsisinfo_comment">/* Properties */</div>
    <div class="fieldsynopsis">
     <span class="modifier">protected</span>
     <span class="type">int</span>
      <var class="varname"><a href="class.errorexception.php#errorexception.props.severity">$<var class="varname">severity</var></a></var>
    ;</div>

 
    <div class="classsynopsisinfo classsynopsisinfo_comment">/* Methods */</div>
    <div class="constructorsynopsis dc-description">
   <span class="modifier">public</span>  <span class="methodname"><a href="errorexception.construct.php" class="methodname">__construct</a></span>
    ([ <span class="methodparam"><span class="type">string</span> <code class="parameter">$message</code><span class="initializer"> = &quot;&quot;</span></span>
   [, <span class="methodparam"><span class="type">int</span> <code class="parameter">$code</code><span class="initializer"> = 0</span></span>
   [, <span class="methodparam"><span class="type">int</span> <code class="parameter">$severity</code><span class="initializer"> = 1</span></span>
   [, <span class="methodparam"><span class="type">string</span> <code class="parameter">$filename</code><span class="initializer"> = __FILE__</span></span>
   [, <span class="methodparam"><span class="type">int</span> <code class="parameter">$lineno</code><span class="initializer"> = __LINE__</span></span>
   [, <span class="methodparam"><span class="type"><a href="class.exception.php" class="type Exception">Exception</a></span> <code class="parameter">$previous</code><span class="initializer"> = <strong><code>NULL</code></strong></span></span>
  ]]]]]] )</div>

    <div class="methodsynopsis dc-description">
   <span class="modifier">final</span> <span class="modifier">public</span> <span class="type">int</span> <span class="methodname"><a href="errorexception.getseverity.php" class="methodname">getSeverity</a></span>
    ( <span class="methodparam">void</span>
   )</div>

 
    <div class="classsynopsisinfo classsynopsisinfo_comment">/* Inherited methods */</div>
    <div class="methodsynopsis dc-description">
   <span class="modifier">final</span> <span class="modifier">public</span> <span class="type">string</span> <span class="methodname"><a href="exception.getmessage.php" class="methodname">Exception::getMessage</a></span>
    ( <span class="methodparam">void</span>
   )</div>
<div class="methodsynopsis dc-description">
   <span class="modifier">final</span> <span class="modifier">public</span> <span class="type">Exception</span> <span class="methodname"><a href="exception.getprevious.php" class="methodname">Exception::getPrevious</a></span>
    ( <span class="methodparam">void</span>
   )</div>
<div class="methodsynopsis dc-description">
   <span class="modifier">final</span> <span class="modifier">public</span> <span class="type">mixed</span> <span class="methodname"><a href="exception.getcode.php" class="methodname">Exception::getCode</a></span>
    ( <span class="methodparam">void</span>
   )</div>
<div class="methodsynopsis dc-description">
   <span class="modifier">final</span> <span class="modifier">public</span> <span class="type">string</span> <span class="methodname"><a href="exception.getfile.php" class="methodname">Exception::getFile</a></span>
    ( <span class="methodparam">void</span>
   )</div>
<div class="methodsynopsis dc-description">
   <span class="modifier">final</span> <span class="modifier">public</span> <span class="type">int</span> <span class="methodname"><a href="exception.getline.php" class="methodname">Exception::getLine</a></span>
    ( <span class="methodparam">void</span>
   )</div>
<div class="methodsynopsis dc-description">
   <span class="modifier">final</span> <span class="modifier">public</span> <span class="type">array</span> <span class="methodname"><a href="exception.gettrace.php" class="methodname">Exception::getTrace</a></span>
    ( <span class="methodparam">void</span>
   )</div>
<div class="methodsynopsis dc-description">
   <span class="modifier">final</span> <span class="modifier">public</span> <span class="type">string</span> <span class="methodname"><a href="exception.gettraceasstring.php" class="methodname">Exception::getTraceAsString</a></span>
    ( <span class="methodparam">void</span>
   )</div>
<div class="methodsynopsis dc-description">
   <span class="modifier">public</span> <span class="type">string</span>  <span class="methodname"><a href="exception.tostring.php" class="methodname">Exception::__toString</a></span>
    ( <span class="methodparam">void</span>
   )</div>
<div class="methodsynopsis dc-description">
   <span class="modifier">final</span> <span class="modifier">private</span> <span class="type">void</span> <span class="methodname"><a href="exception.clone.php" class="methodname">Exception::__clone</a></span>
    ( <span class="methodparam">void</span>
   )</div>

   }</div>

 
  </div>
 

  <div class="section" id="errorexception.props">
   <h2 class="title">Properties</h2>
   <dl>

    <dt id="errorexception.props.severity">
     <span class="term"><var class="varname"><var class="varname">severity</var></var></span>
     <dd>

      <p class="para">The severity of the exception</p>
     </dd>

    </dt>

   </dl>

  </div>


  <div class="section" id="errorexception.examples">
   <h2 class="title">Examples</h2>
   <p class="para">
    <div class="example" id="errorexception.example.error-handler">
     <p><strong>Example #1 Use  <span class="function"><a href="function.set-error-handler.php" class="function">set_error_handler()</a></span> to change error messages into ErrorException.</strong></p>
     <div class="example-contents">
 <div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">function&nbsp;</span><span style="color: #0000BB">exception_error_handler</span><span style="color: #007700">(</span><span style="color: #0000BB">$errno</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$errstr</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$errfile</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$errline&nbsp;</span><span style="color: #007700">)&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;throw&nbsp;new&nbsp;</span><span style="color: #0000BB">ErrorException</span><span style="color: #007700">(</span><span style="color: #0000BB">$errstr</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$errno</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">0</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$errfile</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$errline</span><span style="color: #007700">);<br />}<br /></span><span style="color: #0000BB">set_error_handler</span><span style="color: #007700">(</span><span style="color: #DD0000">"exception_error_handler"</span><span style="color: #007700">);<br /><br /></span><span style="color: #FF8000">/*&nbsp;Trigger&nbsp;exception&nbsp;*/<br /></span><span style="color: #0000BB">strpos</span><span style="color: #007700">();<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

     <div class="example-contents"><p>The above example will output
something similar to:</p></div>
     <div class="example-contents screen">
<div class="cdata"><pre>
Fatal error: Uncaught exception &#039;ErrorException&#039; with message &#039;Wrong parameter count for strpos()&#039; in /home/bjori/tmp/ex.php:8
Stack trace:
#0 [internal function]: exception_error_handler(2, &#039;Wrong parameter...&#039;, &#039;/home/bjori/php...&#039;, 8, Array)
#1 /home/bjori/php/cleandocs/test.php(8): strpos()
#2 {main}
  thrown in /home/bjori/tmp/ex.php on line 8
</pre></div>
     </div>
    </div>
   </p>
  </div>
 
 </div>
 
 



 



 



 



 
<h2>Table of Contents</h2><ul class="chunklist chunklist_reference"><li><a href="errorexception.construct.php">ErrorException::__construct</a> — Constructs the exception</li><li><a href="errorexception.getseverity.php">ErrorException::getSeverity</a> — Gets the exception severity</li></ul>
</div>
<br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="errorexception.construct.php">ErrorException::__construct<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="exception.clone.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Exception::__clone</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/class.errorexception.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=class.errorexception&amp;redirect=http://www.php.net/manual/en/class.errorexception.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=class.errorexception&amp;redirect=http://www.php.net/manual/en/class.errorexception.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>ErrorException</strong>
 </div><div id="allnotes">
 <a name="106148"></a>
 <div class="note">
  <strong class='user'>Lucas Biernot</strong>
  <a href="#106148" class="date">14-Oct-2011 12:37</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
It's a bad practice to ignore notices. Well written program doesn't generate notices, so imho notice is simply an error and it should raise an exception and eventually terminate the script. You can catch exception wherever you want to and perform proper action.</span>
</code></div>
  </div>
 </div>
 <a name="103754"></a>
 <div class="note">
  <strong class='user'>vog at notjusthosting dot com</strong>
  <a href="#103754" class="date">02-May-2011 05:14</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
There is a contradiction between the class synopsis and the example code.<br />
<br />
The class synopsis states that the error code ($code) is the 2nd argument, while the example code provides the error code ($errno) as the 3rd argument.<br />
<br />
Assuming that the class synopsis is correct, the example code should be corrected to:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">exception_error_handler</span><span class="keyword">(</span><span class="default">$errno</span><span class="keyword">, </span><span class="default">$errstr</span><span class="keyword">, </span><span class="default">$errfile</span><span class="keyword">, </span><span class="default">$errline </span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; throw new </span><span class="default">ErrorException</span><span class="keyword">(</span><span class="default">$errstr</span><span class="keyword">, </span><span class="default">$errno</span><span class="keyword">, </span><span class="default">0</span><span class="keyword">, </span><span class="default">$errfile</span><span class="keyword">, </span><span class="default">$errline</span><span class="keyword">);<br />
}<br />
</span><span class="default">set_error_handler</span><span class="keyword">(</span><span class="string">"exception_error_handler"</span><span class="keyword">);<br />
<br />
</span><span class="comment">/* Trigger exception */<br />
</span><span class="default">strpos</span><span class="keyword">();<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="95415"></a>
 <div class="note">
  <strong class='user'>triplepoint at gmail dot com</strong>
  <a href="#95415" class="date">01-Jan-2010 08:16</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
As noted below, it's important to realize that unless caught, any Exception thrown will halt the script.&nbsp; So converting EVERY notice, warning, or error to an ErrorException will halt your script when something harmlesss like E_USER_NOTICE is triggered.<br />
<br />
It seems to me the best use of the ErrorException class is something like this:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">custom_error_handler</span><span class="keyword">(</span><span class="default">$number</span><span class="keyword">, </span><span class="default">$string</span><span class="keyword">, </span><span class="default">$file</span><span class="keyword">, </span><span class="default">$line</span><span class="keyword">, </span><span class="default">$context</span><span class="keyword">) <br />
{<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">// Determine if this error is one of the enabled ones in php config (php.ini, .htaccess, etc)<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$error_is_enabled </span><span class="keyword">= (bool)(</span><span class="default">$number </span><span class="keyword">&amp; </span><span class="default">ini_get</span><span class="keyword">(</span><span class="string">'error_reporting'</span><span class="keyword">) );<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">// -- FATAL ERROR<br />
&nbsp;&nbsp;&nbsp; // throw an Error Exception, to be handled by whatever Exception handling logic is available in this context<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">if( </span><span class="default">in_array</span><span class="keyword">(</span><span class="default">$number</span><span class="keyword">, array(</span><span class="default">E_USER_ERROR</span><span class="keyword">, </span><span class="default">E_RECOVERABLE_ERROR</span><span class="keyword">)) &amp;&amp; </span><span class="default">$error_is_enabled </span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; throw new </span><span class="default">ErrorException</span><span class="keyword">(</span><span class="default">$errstr</span><span class="keyword">, </span><span class="default">0</span><span class="keyword">, </span><span class="default">$errno</span><span class="keyword">, </span><span class="default">$errfile</span><span class="keyword">, </span><span class="default">$errline</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">// -- NON-FATAL ERROR/WARNING/NOTICE<br />
&nbsp;&nbsp;&nbsp; // Log the error if it's enabled, otherwise just ignore it<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">else if( </span><span class="default">$error_is_enabled </span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">error_log</span><span class="keyword">( </span><span class="default">$string</span><span class="keyword">, </span><span class="default">0 </span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">false</span><span class="keyword">; </span><span class="comment">// Make sure this ends up in $php_errormsg, if appropriate<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">}<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
Setting this function as the error handler will result in ErrorExceptions only being thrown for E_USER_ERROR and E_RECOVERABLE_ERROR, while other enabled error types will simply get error_log()'ed.<br />
<br />
It's worth noting again that no matter what you do, "E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING, and most of E_STRICT" will never reach your custom error handler, and therefore will not be converted into ErrorExceptions.&nbsp; Plan accordingly.</span>
</code></div>
  </div>
 </div>
 <a name="95072"></a>
 <div class="note">
  <strong class='user'>randallgirard at hotmail dot com</strong>
  <a href="#95072" class="date">11-Dec-2009 03:32</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
E_USER_WARNING, E_USER_NOTICE, and any other non-terminating error codes, are useless and act like E_USER_ERROR (which terminate) when you combine a custom ERROR_HANDLER with ErrorException and do not CATCH the error. There is NO way to return execution to the parent scope in the EXCEPTION_HANDLER.<br />
<br />
<span class="default">&lt;?php<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; error_reporting</span><span class="keyword">(</span><span class="default">E_ALL</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">define</span><span class="keyword">(</span><span class="string">'DEBUG'</span><span class="keyword">, </span><span class="default">true</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">define</span><span class="keyword">(</span><span class="string">'LINEBREAK'</span><span class="keyword">, </span><span class="string">"\r\n"</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; </span><span class="default">error</span><span class="keyword">::</span><span class="default">initiate</span><span class="keyword">(</span><span class="string">'./error_backtrace.log'</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; try<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">trigger_error</span><span class="keyword">(</span><span class="string">"First error"</span><span class="keyword">, </span><span class="default">E_USER_NOTICE</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; catch ( </span><span class="default">ErrorException $e </span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; print(</span><span class="string">"Caught the error: "</span><span class="keyword">.</span><span class="default">$e</span><span class="keyword">-&gt;</span><span class="default">getMessage</span><span class="keyword">.</span><span class="string">"&lt;br /&gt;\r\n" </span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; </span><span class="default">trigger_error</span><span class="keyword">(</span><span class="string">"This event WILL fire"</span><span class="keyword">, </span><span class="default">E_USER_NOTICE</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; </span><span class="default">trigger_error</span><span class="keyword">(</span><span class="string">"This event will NOT fire"</span><span class="keyword">, </span><span class="default">E_USER_NOTICE</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; abstract class </span><span class="default">error </span><span class="keyword">{<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; public static </span><span class="default">$LIST </span><span class="keyword">= array();<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; private function </span><span class="default">__construct</span><span class="keyword">() {}<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; public static function </span><span class="default">initiate</span><span class="keyword">( </span><span class="default">$log </span><span class="keyword">= </span><span class="default">false </span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">set_error_handler</span><span class="keyword">( </span><span class="string">'error::err_handler' </span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">set_exception_handler</span><span class="keyword">( </span><span class="string">'error::exc_handler' </span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; if ( </span><span class="default">$log </span><span class="keyword">!== </span><span class="default">false </span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; if ( ! </span><span class="default">ini_get</span><span class="keyword">(</span><span class="string">'log_errors'</span><span class="keyword">) )<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">ini_set</span><span class="keyword">(</span><span class="string">'log_errors'</span><span class="keyword">, </span><span class="default">true</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; if ( ! </span><span class="default">ini_get</span><span class="keyword">(</span><span class="string">'error_log'</span><span class="keyword">) )<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">ini_set</span><span class="keyword">(</span><span class="string">'error_log'</span><span class="keyword">, </span><span class="default">$log</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; public static function </span><span class="default">err_handler</span><span class="keyword">(</span><span class="default">$errno</span><span class="keyword">, </span><span class="default">$errstr</span><span class="keyword">, </span><span class="default">$errfile</span><span class="keyword">, </span><span class="default">$errline</span><span class="keyword">, </span><span class="default">$errcontext</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$l </span><span class="keyword">= </span><span class="default">error_reporting</span><span class="keyword">();<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; if ( </span><span class="default">$l </span><span class="keyword">&amp; </span><span class="default">$errno </span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$exit </span><span class="keyword">= </span><span class="default">false</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; switch ( </span><span class="default">$errno </span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; case </span><span class="default">E_USER_ERROR</span><span class="keyword">:<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$type </span><span class="keyword">= </span><span class="string">'Fatal Error'</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$exit </span><span class="keyword">= </span><span class="default">true</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; break;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; case </span><span class="default">E_USER_WARNING</span><span class="keyword">:<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; case </span><span class="default">E_WARNING</span><span class="keyword">:<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$type </span><span class="keyword">= </span><span class="string">'Warning'</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; break;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; case </span><span class="default">E_USER_NOTICE</span><span class="keyword">:<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; case </span><span class="default">E_NOTICE</span><span class="keyword">:<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; case @</span><span class="default">E_STRICT</span><span class="keyword">:<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$type </span><span class="keyword">= </span><span class="string">'Notice'</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; break;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; case @</span><span class="default">E_RECOVERABLE_ERROR</span><span class="keyword">:<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$type </span><span class="keyword">= </span><span class="string">'Catchable'</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; break;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; default:<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$type </span><span class="keyword">= </span><span class="string">'Unknown Error'</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$exit </span><span class="keyword">= </span><span class="default">true</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; break;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$exception </span><span class="keyword">= new </span><span class="default">ErrorException</span><span class="keyword">(</span><span class="default">$type</span><span class="keyword">.</span><span class="string">': '</span><span class="keyword">.</span><span class="default">$errstr</span><span class="keyword">, </span><span class="default">0</span><span class="keyword">, </span><span class="default">$errno</span><span class="keyword">, </span><span class="default">$errfile</span><span class="keyword">, </span><span class="default">$errline</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; if ( </span><span class="default">$exit </span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">exc_handler</span><span class="keyword">(</span><span class="default">$exception</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; exit();<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; else<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; throw </span><span class="default">$exception</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">false</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; function </span><span class="default">exc_handler</span><span class="keyword">(</span><span class="default">$exception</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$log </span><span class="keyword">= </span><span class="default">$exception</span><span class="keyword">-&gt;</span><span class="default">getMessage</span><span class="keyword">() . </span><span class="string">"\n" </span><span class="keyword">. </span><span class="default">$exception</span><span class="keyword">-&gt;</span><span class="default">getTraceAsString</span><span class="keyword">() . </span><span class="default">LINEBREAK</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; if ( </span><span class="default">ini_get</span><span class="keyword">(</span><span class="string">'log_errors'</span><span class="keyword">) )<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">error_log</span><span class="keyword">(</span><span class="default">$log</span><span class="keyword">, </span><span class="default">0</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; print(</span><span class="string">"Unhandled Exception" </span><span class="keyword">. (</span><span class="default">DEBUG </span><span class="keyword">? </span><span class="string">" - $log" </span><span class="keyword">: </span><span class="string">''</span><span class="keyword">));<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; }<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="91244"></a>
 <div class="note">
  <strong class='user'>gozlan at nett dot co dot il</strong>
  <a href="#91244" class="date">01-Jun-2009 03:00</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you want to control when the exception will be display or when not, you can change the value and enter if condition statement:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">lockdown</span><span class="keyword">(</span><span class="default">$exception</span><span class="keyword">)<br />
{<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="string">" &lt;strong&gt;lockdown:&lt;/strong&gt; " </span><span class="keyword">, </span><span class="default">$exception</span><span class="keyword">-&gt;</span><span class="default">getMessage</span><span class="keyword">();<br />
}<br />
<br />
</span><span class="default">$lockdown </span><span class="keyword">= </span><span class="default">true</span><span class="keyword">; </span><span class="comment">//you can change to false if you want to disable the lockdown.<br />
</span><span class="keyword">if (</span><span class="default">$lockdown</span><span class="keyword">){<br />
</span><span class="default">set_exception_handler</span><span class="keyword">(</span><span class="string">'lockdown'</span><span class="keyword">);<br />
throw new </span><span class="default">Exception</span><span class="keyword">(</span><span class="string">'the website is under construction, we will be back soon'</span><span class="keyword">);<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
the output will be:<br />
lockdown: the website is under construcytion, we will be back soon<br />
<br />
and nothing else will be shown in the entire website, just the lockdown comment.</span>
</code></div>
  </div>
 </div>
 <a name="89221"></a>
 <div class="note">
  <strong class='user'>e dot sand at elisand dot com</strong>
  <a href="#89221" class="date">26-Feb-2009 11:11</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you're looking for a quick one-line way to change all errors in to Exceptions, you can do it this way:<br />
<br />
set_error_handler(create_function('$a, $b, $c, $d', 'throw new ErrorException($b, 0, $a, $c, $d);'), E_ALL);<br />
<br />
using create_function() saves you a few lines of code by embedding it all in to a single line, plus saves your namespace from a function name that should only ever be called in the event of an error (this saves from direct invocation).</span>
</code></div>
  </div>
 </div>
 <a name="89132"></a>
 <div class="note">
  <strong class='user'>luke at cywh dot com</strong>
  <a href="#89132" class="date">23-Feb-2009 11:56</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
To add to the comments made by chris AT cmbuckley DOT co DOT uk about the ErrorException problem with args:<br />
<br />
I noticed that the problem is in the ErrorException class itself, not the Exception class. When using just the exception class, it's no longer an issue. Besides the args problem, the only difference between Exception and ErrorException in the stack trace is that the args are left out of the error handler exception function. I'm not sure if this was on purpose or not, but it shouldn't hurt to show this information anyway.<br />
<br />
So instead of using this broken extended class, you can ignore it and make your own extended class and avoid the problem all together:<br />
<br />
<span class="default">&lt;?php<br />
<br />
header</span><span class="keyword">(</span><span class="string">'Content-Type: text/plain'</span><span class="keyword">);<br />
<br />
class </span><span class="default">ErrorHandler </span><span class="keyword">extends </span><span class="default">Exception </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; protected </span><span class="default">$severity</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">__construct</span><span class="keyword">(</span><span class="default">$message</span><span class="keyword">, </span><span class="default">$code</span><span class="keyword">, </span><span class="default">$severity</span><span class="keyword">, </span><span class="default">$filename</span><span class="keyword">, </span><span class="default">$lineno</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">message </span><span class="keyword">= </span><span class="default">$message</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">code </span><span class="keyword">= </span><span class="default">$code</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">severity </span><span class="keyword">= </span><span class="default">$severity</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">file </span><span class="keyword">= </span><span class="default">$filename</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">line </span><span class="keyword">= </span><span class="default">$lineno</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">getSeverity</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">severity</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
function </span><span class="default">exception_error_handler</span><span class="keyword">(</span><span class="default">$errno</span><span class="keyword">, </span><span class="default">$errstr</span><span class="keyword">, </span><span class="default">$errfile</span><span class="keyword">, </span><span class="default">$errline </span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; throw new </span><span class="default">ErrorHandler</span><span class="keyword">(</span><span class="default">$errstr</span><span class="keyword">, </span><span class="default">0</span><span class="keyword">, </span><span class="default">$errno</span><span class="keyword">, </span><span class="default">$errfile</span><span class="keyword">, </span><span class="default">$errline</span><span class="keyword">);<br />
}<br />
<br />
</span><span class="default">set_error_handler</span><span class="keyword">(</span><span class="string">"exception_error_handler"</span><span class="keyword">, </span><span class="default">E_ALL</span><span class="keyword">);<br />
<br />
function </span><span class="default">A</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$foo</span><span class="keyword">-&gt;</span><span class="default">bar</span><span class="keyword">; </span><span class="comment">// Purposely cause error<br />
</span><span class="keyword">}<br />
<br />
function </span><span class="default">B</span><span class="keyword">(</span><span class="default">$c</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">A</span><span class="keyword">();<br />
}<br />
<br />
try {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">B</span><span class="keyword">(</span><span class="string">'foobar'</span><span class="keyword">);<br />
} catch (</span><span class="default">Exception $e</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$e</span><span class="keyword">-&gt;</span><span class="default">getTrace</span><span class="keyword">());<br />
}<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
The only thing I wish I could do was remove the entry for the error handler function because it's quite irrelevant. Maybe that's what they were trying to do with the ErrorException class? Either way, you can't change it because the trace functions are final, and the variable is private.</span>
</code></div>
  </div>
 </div>
 <a name="86985"></a>
 <div class="note">
  <strong class='user'>chris AT cmbuckley DOT co DOT uk</strong>
  <a href="#86985" class="date">13-Nov-2008 04:24</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The backtrace of ErrorException is broken in PHP 5.2 (listed at <a href="http://bugs.php.net/bug.php?id=46449" rel="nofollow" target="_blank">http://bugs.php.net/bug.php?id=46449</a> and explained at <a href="http://bugs.php.net/bug.php?id=45895#c140511" rel="nofollow" target="_blank">http://bugs.php.net/bug.php?id=45895#c140511</a>).<br />
<br />
A simple fix for your exception handler:<br />
<br />
<span class="default">&lt;?php<br />
$backtrace </span><span class="keyword">= </span><span class="default">$exception</span><span class="keyword">-&gt;</span><span class="default">getTrace</span><span class="keyword">();<br />
<br />
if (</span><span class="default">$exception </span><span class="keyword">instanceof </span><span class="default">ErrorException</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; for (</span><span class="default">$i </span><span class="keyword">= </span><span class="default">count</span><span class="keyword">(</span><span class="default">$backtrace</span><span class="keyword">) - </span><span class="default">1</span><span class="keyword">; </span><span class="default">$i </span><span class="keyword">&gt; </span><span class="default">0</span><span class="keyword">; --</span><span class="default">$i</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$backtrace</span><span class="keyword">[</span><span class="default">$i</span><span class="keyword">][</span><span class="string">'args'</span><span class="keyword">] = </span><span class="default">$backtrace</span><span class="keyword">[</span><span class="default">$i </span><span class="keyword">- </span><span class="default">1</span><span class="keyword">][</span><span class="string">'args'</span><span class="keyword">];<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="85646"></a>
 <div class="note">
  <strong class='user'>makariverslund at gmail dot com</strong>
  <a href="#85646" class="date">10-Sep-2008 06:08</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Building upon the notes present, here is a set of functions that transforms the error into an exception and reports the error to screen / log depending upon the PHP ini settings.<br />
<br />
//Begin helper function definition<br />
function ReportError ($msg)<br />
{<br />
&nbsp;&nbsp;&nbsp; // be sure that the supplied parameter is a string and not empty<br />
&nbsp;&nbsp;&nbsp; if (empty ($msg) || !is_string ($msg))<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; throw new ErrorException ('Invalid parameter supplied to ReportError', 0, E_ERROR);<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; // retrieve error settings<br />
&nbsp;&nbsp;&nbsp; $display&nbsp; &nbsp; = strtolower (ini_get ('display_errors'));<br />
&nbsp;&nbsp;&nbsp; $log&nbsp; &nbsp; &nbsp; &nbsp; = strtolower (ini_get ('log_errors'));<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; // check if we're displaying errors<br />
&nbsp;&nbsp;&nbsp; if ($display === 'on' || $display === '1' || $display === 1 || $display === 'true' || $display === true)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo $msg;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; // check if we're logging errors<br />
&nbsp;&nbsp;&nbsp; if ($log === 'on' || $log === '1' || $log === 1 || $log === 'true' || $log === true)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; $result = error_log ($msg);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; // check for error while logging<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if (!$result)<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; throw new ErrorException ('Attempt to write message to error log failed in ReportError', 0, E_ERROR);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
//Begin main function definition<br />
function ErrorsAsExceptions ($level, $msg, $fileName, $lineNumber)<br />
{<br />
&nbsp;&nbsp;&nbsp; // do nothing if error reporting is turned off<br />
&nbsp;&nbsp;&nbsp; if (error_reporting () === 0)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return;<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; // be sure received error is supposed to be reported<br />
&nbsp;&nbsp;&nbsp; if (error_reporting () &amp; $level)<br />
&nbsp;&nbsp;&nbsp; {&nbsp; &nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; try<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; // report error to appropriate channels<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; ReportError ($reportMsg);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; catch (ErrorException $e)<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; // ignore errors while reporting<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; // go ahead and throw the exception<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; throw new ErrorException ($msg, 0, $level, $fileName, $lineNumber);<br />
&nbsp;&nbsp;&nbsp; }<br />
}</span>
</code></div>
  </div>
 </div>
 <a name="84767"></a>
 <div class="note">
  <strong class='user'>troelskn at gmail dot com</strong>
  <a href="#84767" class="date">29-Jul-2008 04:08</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The following snippet installs an error-handler that turns errors into exceptions. It respects error-reporting level, so that you can still use error-suppression:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">exceptions_error_handler</span><span class="keyword">(</span><span class="default">$severity</span><span class="keyword">, </span><span class="default">$message</span><span class="keyword">, </span><span class="default">$filename</span><span class="keyword">, </span><span class="default">$lineno</span><span class="keyword">) {<br />
&nbsp; if (</span><span class="default">error_reporting</span><span class="keyword">() == </span><span class="default">0</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; return;<br />
&nbsp; }<br />
&nbsp; if (</span><span class="default">error_reporting</span><span class="keyword">() &amp; </span><span class="default">$severity</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; throw new </span><span class="default">ErrorException</span><span class="keyword">(</span><span class="default">$message</span><span class="keyword">, </span><span class="default">0</span><span class="keyword">, </span><span class="default">$severity</span><span class="keyword">, </span><span class="default">$filename</span><span class="keyword">, </span><span class="default">$lineno</span><span class="keyword">);<br />
&nbsp; }<br />
}<br />
</span><span class="default">set_error_handler</span><span class="keyword">(</span><span class="string">'exceptions_error_handler'</span><span class="keyword">);<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=class.errorexception&amp;redirect=http://www.php.net/manual/en/class.errorexception.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=class.errorexception&amp;redirect=http://www.php.net/manual/en/class.errorexception.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/class.errorexception.php">show source</a> |
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