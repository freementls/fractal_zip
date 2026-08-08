<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Variables - Manual</title>
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
 <link rel="prev" href="language.types.type-juggling.php" />
 <link rel="next" href="language.variables.basics.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/variables" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="alternate" href="/manual/en/feeds/language.variables.atom" type="application/atom+xml" />
 <link rel="canonical" href="http://php.net/manual/en/language.variables.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/language.variables.php" />
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
 <li class="active"><a href="language.variables.php">Variables</a></li>
 <li><a href="language.constants.php">Constants</a></li>
 <li><a href="language.expressions.php">Expressions</a></li>
 <li><a href="language.operators.php">Operators</a></li>
 <li><a href="language.control-structures.php">Control Structures</a></li>
 <li><a href="language.functions.php">Functions</a></li>
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
  <a href="language.variables.basics.php">Basics<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.types.type-juggling.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Type Juggling</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.variables.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/language.variables.php">Brazilian Portuguese</option>
    <option value="zh/language.variables.php">Chinese (Simplified)</option>
    <option value="fr/language.variables.php">French</option>
    <option value="de/language.variables.php">German</option>
    <option value="ja/language.variables.php">Japanese</option>
    <option value="pl/language.variables.php">Polish</option>
    <option value="ro/language.variables.php">Romanian</option>
    <option value="ru/language.variables.php">Russian</option>
    <option value="fa/language.variables.php">Persian</option>
    <option value="es/language.variables.php">Spanish</option>
    <option value="tr/language.variables.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="language.variables" class="chapter">
  <h1>Variables</h1>
<h2>Table of Contents</h2><ul class="chunklist chunklist_chapter"><li><a href="language.variables.basics.php">Basics</a></li><li><a href="language.variables.predefined.php">Predefined Variables</a></li><li><a href="language.variables.scope.php">Variable scope</a></li><li><a href="language.variables.variable.php">Variable variables</a></li><li><a href="language.variables.external.php">Variables From External Sources</a></li></ul>

  
  

  


  

  

  
     
 </div>
<br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="language.variables.basics.php">Basics<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.types.type-juggling.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Type Juggling</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.variables.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=language.variables&amp;redirect=http://www.php.net/manual/en/language.variables.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.variables&amp;redirect=http://www.php.net/manual/en/language.variables.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Variables</strong>
 </div><div id="allnotes">
 <a name="100195"></a>
 <div class="note">
  <strong class='user'>james at custom-made-sites dot com</strong>
  <a href="#100195" class="date">30-Sep-2010 04:55</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
This script has been running around for a while and has add many additions and changes, however I noticed that if you run it and there is an object assigned to a variable it will fail with 'Object of Class 'XXXX' cannot be converted to string'<br />
so I changed it a little to ignore objects within the script and display the variables only:<br />
<br />
Btw, it has haelped me an ENOURMOUS amount over the years especially when keeping track of variables that are being sent through request and if session variables are alive<br />
<br />
<span class="default">&lt;?php <br />
<br />
</span><span class="comment">//this can be removed if you want to find out if you page has actually started a session, - this is sometimes a problem when relying on a session being started by an included/required file<br />
</span><span class="keyword">if(!isset(</span><span class="default">$_SESSION</span><span class="keyword">))<br />
{<br />
</span><span class="default">session_start</span><span class="keyword">();<br />
}<br />
<br />
</span><span class="comment">//now begin the table<br />
</span><span class="keyword">echo </span><span class="string">'&lt;table border=1&gt;&lt;tr&gt; &lt;th&gt;variable&lt;/th&gt; &lt;th&gt;value&lt;/th&gt; &lt;/tr&gt;'</span><span class="keyword">; <br />
foreach(</span><span class="default">get_defined_vars</span><span class="keyword">() as </span><span class="default">$key </span><span class="keyword">=&gt; </span><span class="default">$value</span><span class="keyword">) <br />
{ <br />
</span><span class="comment">//if the returned value is NOT an object...<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">if (</span><span class="default">is_array </span><span class="keyword">(</span><span class="default">$value</span><span class="keyword">) &amp;&amp; !</span><span class="default">is_object</span><span class="keyword">(</span><span class="default">$key</span><span class="keyword">) &amp;&amp; !</span><span class="default">is_object</span><span class="keyword">(</span><span class="default">$value</span><span class="keyword">)) <br />
&nbsp;&nbsp;&nbsp; { <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">'&lt;tr&gt;&lt;td&gt;$'</span><span class="keyword">.</span><span class="default">$key </span><span class="keyword">.</span><span class="string">'&lt;/td&gt;&lt;td&gt;'</span><span class="keyword">; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if ( </span><span class="default">sizeof</span><span class="keyword">(</span><span class="default">$value</span><span class="keyword">)&gt;</span><span class="default">0 </span><span class="keyword">) <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; { <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">'"&lt;table border=1&gt;&lt;tr&gt; &lt;th&gt;key&lt;/th&gt; &lt;th&gt;value&lt;/th&gt; &lt;/tr&gt;'</span><span class="keyword">; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; foreach (</span><span class="default">$value </span><span class="keyword">as </span><span class="default">$skey </span><span class="keyword">=&gt; </span><span class="default">$svalue </span><span class="keyword">) <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; {<br />
&nbsp;</span><span class="comment">//and if these values are NOT an object...<br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">if ( !</span><span class="default">is_object</span><span class="keyword">(</span><span class="default">$skey</span><span class="keyword">) &amp;&amp; !</span><span class="default">is_object</span><span class="keyword">(</span><span class="default">$svalue</span><span class="keyword">)) <br />
&nbsp;&nbsp;&nbsp; { <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">'&lt;tr&gt;&lt;td&gt;[' </span><span class="keyword">. </span><span class="default">$skey </span><span class="keyword">.</span><span class="string">']&lt;/td&gt;&lt;td&gt;"'</span><span class="keyword">. </span><span class="default">$svalue </span><span class="keyword">.</span><span class="string">'"&lt;/td&gt;&lt;/tr&gt;'</span><span class="keyword">; <br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; } <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">'&lt;/table&gt;"'</span><span class="keyword">; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; } <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; else <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; { <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">'EMPTY'</span><span class="keyword">; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; } <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">'&lt;/td&gt;&lt;/tr&gt;'</span><span class="keyword">; <br />
&nbsp;&nbsp;&nbsp; }else<br />
<br />
&nbsp;&nbsp;&nbsp; { <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">'&lt;tr&gt;&lt;td&gt;[' </span><span class="keyword">. </span><span class="default">$key </span><span class="keyword">.</span><span class="string">']&lt;/td&gt;&lt;td&gt;"'</span><span class="keyword">. </span><span class="default">$value </span><span class="keyword">.</span><span class="string">'"&lt;/td&gt;&lt;/tr&gt;'</span><span class="keyword">; <br />
&nbsp;&nbsp;&nbsp; } <br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; <br />
} <br />
<br />
echo </span><span class="string">'&lt;/table&gt;'</span><span class="keyword">;<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="99211"></a>
 <div class="note">
  <strong class='user'>justgook at gmail dot com</strong>
  <a href="#99211" class="date">03-Aug-2010 01:38</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I found interstate solution to work with arrays<br />
<br />
<span class="default">&lt;?php<br />
$vars</span><span class="keyword">[</span><span class="string">'product'</span><span class="keyword">][</span><span class="string">'price'</span><span class="keyword">]=</span><span class="default">11</span><span class="keyword">;<br />
<br />
</span><span class="default">$aa</span><span class="keyword">=</span><span class="string">'product'</span><span class="keyword">;<br />
</span><span class="default">$bb</span><span class="keyword">=</span><span class="string">'price'</span><span class="keyword">;<br />
<br />
echo </span><span class="default">$vars</span><span class="keyword">{</span><span class="default">$aa</span><span class="keyword">}{</span><span class="default">$bb</span><span class="keyword">};<br />
<br />
</span><span class="comment">//prints 11<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="99132"></a>
 <div class="note">
  <strong class='user'>dimitrov dot adrian at gmail dot com</strong>
  <a href="#99132" class="date">29-Jul-2010 06:52</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
This is mine type casting lib, that is very useful for me.<br />
<br />
<span class="default">&lt;?php <br />
<br />
</span><span class="keyword">function </span><span class="default">CAST_TO_INT</span><span class="keyword">(</span><span class="default">$var</span><span class="keyword">, </span><span class="default">$min </span><span class="keyword">= </span><span class="default">FALSE</span><span class="keyword">, </span><span class="default">$max </span><span class="keyword">= </span><span class="default">FALSE</span><span class="keyword">)<br />
{<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$var </span><span class="keyword">= </span><span class="default">is_int</span><span class="keyword">(</span><span class="default">$var</span><span class="keyword">) ? </span><span class="default">$var </span><span class="keyword">: (int)(</span><span class="default">is_scalar</span><span class="keyword">(</span><span class="default">$var</span><span class="keyword">) ? </span><span class="default">$var </span><span class="keyword">: </span><span class="default">0</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; if (</span><span class="default">$min </span><span class="keyword">!== </span><span class="default">FALSE </span><span class="keyword">&amp;&amp; </span><span class="default">$var </span><span class="keyword">&lt; </span><span class="default">$min</span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$min</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; elseif(</span><span class="default">$max </span><span class="keyword">!== </span><span class="default">FALSE </span><span class="keyword">&amp;&amp; </span><span class="default">$var </span><span class="keyword">&gt; </span><span class="default">$max</span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$max</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">$var</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
}<br />
<br />
function </span><span class="default">CAST_TO_FLOAT</span><span class="keyword">(</span><span class="default">$var</span><span class="keyword">, </span><span class="default">$min </span><span class="keyword">= </span><span class="default">FALSE</span><span class="keyword">, </span><span class="default">$max </span><span class="keyword">= </span><span class="default">FALSE</span><span class="keyword">)<br />
{<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$var </span><span class="keyword">= </span><span class="default">is_float</span><span class="keyword">(</span><span class="default">$var</span><span class="keyword">) ? </span><span class="default">$var </span><span class="keyword">: (float)(</span><span class="default">is_scalar</span><span class="keyword">(</span><span class="default">$var</span><span class="keyword">) ? </span><span class="default">$var </span><span class="keyword">: </span><span class="default">0</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; if (</span><span class="default">$min </span><span class="keyword">!== </span><span class="default">FALSE </span><span class="keyword">&amp;&amp; </span><span class="default">$var </span><span class="keyword">&lt; </span><span class="default">$min</span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$min</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; elseif(</span><span class="default">$max </span><span class="keyword">!== </span><span class="default">FALSE </span><span class="keyword">&amp;&amp; </span><span class="default">$var </span><span class="keyword">&gt; </span><span class="default">$max</span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$max</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">$var</span><span class="keyword">;<br />
}<br />
<br />
function </span><span class="default">CAST_TO_BOOL</span><span class="keyword">(</span><span class="default">$var</span><span class="keyword">)<br />
{<br />
&nbsp;&nbsp;&nbsp; return (bool)(</span><span class="default">is_bool</span><span class="keyword">(</span><span class="default">$var</span><span class="keyword">) ? </span><span class="default">$var </span><span class="keyword">: </span><span class="default">is_scalar</span><span class="keyword">(</span><span class="default">$var</span><span class="keyword">) ? </span><span class="default">$var </span><span class="keyword">: </span><span class="default">FALSE</span><span class="keyword">);<br />
}<br />
<br />
function </span><span class="default">CAST_TO_STRING</span><span class="keyword">(</span><span class="default">$var</span><span class="keyword">, </span><span class="default">$length </span><span class="keyword">= </span><span class="default">FALSE</span><span class="keyword">)<br />
{<br />
&nbsp;&nbsp;&nbsp; if (</span><span class="default">$length </span><span class="keyword">!== </span><span class="default">FALSE </span><span class="keyword">&amp;&amp; </span><span class="default">is_int</span><span class="keyword">(</span><span class="default">$length</span><span class="keyword">) &amp;&amp; </span><span class="default">$length </span><span class="keyword">&gt; </span><span class="default">0</span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">substr</span><span class="keyword">(</span><span class="default">trim</span><span class="keyword">(</span><span class="default">is_string</span><span class="keyword">(</span><span class="default">$var</span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; ? </span><span class="default">$var<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">: (</span><span class="default">is_scalar</span><span class="keyword">(</span><span class="default">$var</span><span class="keyword">) ? </span><span class="default">$var </span><span class="keyword">: </span><span class="string">''</span><span class="keyword">)), </span><span class="default">0</span><span class="keyword">, </span><span class="default">$length</span><span class="keyword">);<br />
<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">trim</span><span class="keyword">(<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">is_string</span><span class="keyword">(</span><span class="default">$var</span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; ? </span><span class="default">$var<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">: (</span><span class="default">is_scalar</span><span class="keyword">(</span><span class="default">$var</span><span class="keyword">) ? </span><span class="default">$var </span><span class="keyword">: </span><span class="string">''</span><span class="keyword">));<br />
}<br />
<br />
function </span><span class="default">CAST_TO_ARRAY</span><span class="keyword">(</span><span class="default">$var</span><span class="keyword">)<br />
{<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">is_array</span><span class="keyword">(</span><span class="default">$var</span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; ? </span><span class="default">$var<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">: </span><span class="default">is_scalar</span><span class="keyword">(</span><span class="default">$var</span><span class="keyword">) &amp;&amp; </span><span class="default">$var<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">? array(</span><span class="default">$var</span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; : </span><span class="default">is_object</span><span class="keyword">(</span><span class="default">$var</span><span class="keyword">) ? (array)</span><span class="default">$var </span><span class="keyword">: array();<br />
}<br />
<br />
function </span><span class="default">CAST_TO_OBJECT</span><span class="keyword">(</span><span class="default">$var</span><span class="keyword">)<br />
{<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">is_object</span><span class="keyword">(</span><span class="default">$var</span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; ? </span><span class="default">$var<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">: </span><span class="default">is_scalar</span><span class="keyword">(</span><span class="default">$var</span><span class="keyword">) &amp;&amp; </span><span class="default">$var<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">? (object)</span><span class="default">$var<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">: </span><span class="default">is_array</span><span class="keyword">(</span><span class="default">$var</span><span class="keyword">) ? (object)</span><span class="default">$var </span><span class="keyword">: (object)</span><span class="default">NULL</span><span class="keyword">;<br />
}<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="84581"></a>
 <div class="note">
  <strong class='user'>Anonymous</strong>
  <a href="#84581" class="date">20-Jul-2008 06:25</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
[EDIT by danbrown AT php DOT net: The function provided by this author will give you all defined variables at runtime.&nbsp; It was originally written by (john DOT t DOT gold AT gmail DOT com), but contained some errors that were corrected in subsequent posts by (ned AT wgtech DOT com) and (taliesin AT gmail DOT com).]<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">echo </span><span class="string">'&lt;table border=1&gt;&lt;tr&gt; &lt;th&gt;variable&lt;/th&gt; &lt;th&gt;value&lt;/th&gt; &lt;/tr&gt;'</span><span class="keyword">;<br />
foreach( </span><span class="default">get_defined_vars</span><span class="keyword">() as </span><span class="default">$key </span><span class="keyword">=&gt; </span><span class="default">$value</span><span class="keyword">) <br />
{<br />
&nbsp;&nbsp;&nbsp; if (</span><span class="default">is_array </span><span class="keyword">(</span><span class="default">$value</span><span class="keyword">) )<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">'&lt;tr&gt;&lt;td&gt;$'</span><span class="keyword">.</span><span class="default">$key </span><span class="keyword">.</span><span class="string">'&lt;/td&gt;&lt;td&gt;'</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if ( </span><span class="default">sizeof</span><span class="keyword">(</span><span class="default">$value</span><span class="keyword">)&gt;</span><span class="default">0 </span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">'"&lt;table border=1&gt;&lt;tr&gt; &lt;th&gt;key&lt;/th&gt; &lt;th&gt;value&lt;/th&gt; &lt;/tr&gt;'</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; foreach (</span><span class="default">$value </span><span class="keyword">as </span><span class="default">$skey </span><span class="keyword">=&gt; </span><span class="default">$svalue</span><span class="keyword">) <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">'&lt;tr&gt;&lt;td&gt;[' </span><span class="keyword">. </span><span class="default">$skey </span><span class="keyword">.</span><span class="string">']&lt;/td&gt;&lt;td&gt;"'</span><span class="keyword">. </span><span class="default">$svalue </span><span class="keyword">.</span><span class="string">'"&lt;/td&gt;&lt;/tr&gt;'</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">'&lt;/table&gt;"'</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; else<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">'EMPTY'</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">'&lt;/td&gt;&lt;/tr&gt;'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; else<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">'&lt;tr&gt;&lt;td&gt;$' </span><span class="keyword">. </span><span class="default">$key </span><span class="keyword">.</span><span class="string">'&lt;/td&gt;&lt;td&gt;"'</span><span class="keyword">. </span><span class="default">$value </span><span class="keyword">.</span><span class="string">'"&lt;/td&gt;&lt;/tr&gt;'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
echo </span><span class="string">'&lt;/table&gt;'</span><span class="keyword">;<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="76245"></a>
 <div class="note">
  <strong class='user'>alexandre at nospam dot gaigalas dot net</strong>
  <a href="#76245" class="date">06-Jul-2007 09:13</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Here's a simple solution for retrieving the variable name, based on the lucas (<a href="http://www.php.net/manual/en/language.variables.php#49997" rel="nofollow" target="_blank">http://www.php.net/manual/en/language.variables.php#49997</a>) solution, but shorter, just two lines =)<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">var_name</span><span class="keyword">(&amp;</span><span class="default">$var</span><span class="keyword">, </span><span class="default">$scope</span><span class="keyword">=</span><span class="default">0</span><span class="keyword">)<br />
{<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$old </span><span class="keyword">= </span><span class="default">$var</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; if ((</span><span class="default">$key </span><span class="keyword">= </span><span class="default">array_search</span><span class="keyword">(</span><span class="default">$var </span><span class="keyword">= </span><span class="string">'unique'</span><span class="keyword">.</span><span class="default">rand</span><span class="keyword">().</span><span class="string">'value'</span><span class="keyword">, !</span><span class="default">$scope </span><span class="keyword">? </span><span class="default">$GLOBALS </span><span class="keyword">: </span><span class="default">$scope</span><span class="keyword">)) &amp;&amp; </span><span class="default">$var </span><span class="keyword">= </span><span class="default">$old</span><span class="keyword">) return </span><span class="default">$key</span><span class="keyword">;&nbsp; <br />
}<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="73373"></a>
 <div class="note">
  <strong class='user'>jsb17 at cornell dot edu</strong>
  <a href="#73373" class="date">20-Feb-2007 08:48</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
As an addendum to David's 10-Nov-2005 posting, remember that curly braces literally mean "evaluate what's inside the curly braces" so, you can squeeze the variable variable creation into one line, like this:<br />
<br />
<span class="default">&lt;?php<br />
&nbsp; </span><span class="keyword">${</span><span class="string">"title_default_" </span><span class="keyword">. </span><span class="default">$title</span><span class="keyword">} = </span><span class="string">"selected"</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
and then, for example:<br />
<br />
<span class="default">&lt;?php<br />
&nbsp; $title_select </span><span class="keyword">= &lt;&lt;&lt;END<br />
</span><span class="default">&nbsp; &nbsp; &lt;select name="title"&gt;<br />
&nbsp;&nbsp; &nbsp;&nbsp; &lt;option&gt;Select&lt;/option&gt;<br />
&nbsp;&nbsp; &nbsp;&nbsp; &lt;option $title_default_Mr&nbsp; value="Mr"&gt;Mr&lt;/option&gt;<br />
&nbsp;&nbsp; &nbsp;&nbsp; &lt;option $title_default_Ms&nbsp; value="Ms"&gt;Ms&lt;/option&gt;<br />
&nbsp;&nbsp; &nbsp;&nbsp; &lt;option $title_default_Mrs value="Mrs"&gt;Mrs&lt;/option&gt;<br />
&nbsp;&nbsp; &nbsp;&nbsp; &lt;option $title_default_Dr&nbsp; value="Dr"&gt;Dr&lt;/option&gt;<br />
&nbsp;&nbsp;&nbsp; &lt;/select&gt;<br />
</span><span class="keyword">END;<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="72590"></a>
 <div class="note">
  <strong class='user'>code at slater dot fr</strong>
  <a href="#72590" class="date">25-Jan-2007 02:10</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Here's a pair of functions to encode/decode any string to be a valid php and javascript variable name.<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">function </span><span class="default">label_encode</span><span class="keyword">(</span><span class="default">$txt</span><span class="keyword">) {<br />
&nbsp; <br />
&nbsp; </span><span class="comment">// add Z to the begining to avoid that the resulting <br />
&nbsp; // label is a javascript keyword or it starts with a <br />
&nbsp; // number<br />
&nbsp; </span><span class="default">$txt </span><span class="keyword">= </span><span class="string">'Z'</span><span class="keyword">.</span><span class="default">$txt</span><span class="keyword">;<br />
&nbsp; <br />
&nbsp; </span><span class="comment">// encode as urlencoded data<br />
&nbsp; </span><span class="default">$txt </span><span class="keyword">= </span><span class="default">rawurlencode</span><span class="keyword">(</span><span class="default">$txt</span><span class="keyword">);<br />
&nbsp; <br />
&nbsp; </span><span class="comment">// replace illegal characters<br />
&nbsp; </span><span class="default">$illegal </span><span class="keyword">= array(</span><span class="string">'%'</span><span class="keyword">, </span><span class="string">'-'</span><span class="keyword">, </span><span class="string">'.'</span><span class="keyword">);<br />
&nbsp; </span><span class="default">$ok </span><span class="keyword">= array(</span><span class="string">'é'</span><span class="keyword">, </span><span class="string">'è'</span><span class="keyword">, </span><span class="string">'à'</span><span class="keyword">);<br />
&nbsp; </span><span class="default">$txt </span><span class="keyword">= </span><span class="default">str_replace</span><span class="keyword">(</span><span class="default">$illegal</span><span class="keyword">,</span><span class="default">$ok</span><span class="keyword">, </span><span class="default">$txt</span><span class="keyword">);<br />
&nbsp; <br />
&nbsp; return </span><span class="default">$txt</span><span class="keyword">;<br />
}<br />
<br />
function </span><span class="default">label_decode</span><span class="keyword">(</span><span class="default">$txt</span><span class="keyword">) {<br />
&nbsp; <br />
&nbsp; </span><span class="comment">// replace illegal characters<br />
&nbsp; </span><span class="default">$illegal </span><span class="keyword">= array(</span><span class="string">'%'</span><span class="keyword">, </span><span class="string">'-'</span><span class="keyword">, </span><span class="string">'.'</span><span class="keyword">);<br />
&nbsp; </span><span class="default">$ok </span><span class="keyword">= array(</span><span class="string">'é'</span><span class="keyword">, </span><span class="string">'è'</span><span class="keyword">, </span><span class="string">'à'</span><span class="keyword">);<br />
&nbsp; </span><span class="default">$txt </span><span class="keyword">= </span><span class="default">str_replace</span><span class="keyword">(</span><span class="default">$ok</span><span class="keyword">, </span><span class="default">$illegal</span><span class="keyword">, </span><span class="default">$txt</span><span class="keyword">);<br />
&nbsp; <br />
&nbsp; </span><span class="comment">// unencode<br />
&nbsp; </span><span class="default">$txt </span><span class="keyword">= </span><span class="default">rawurldecode</span><span class="keyword">(</span><span class="default">$txt</span><span class="keyword">);<br />
&nbsp; <br />
&nbsp; </span><span class="comment">// remove the leading Z and return<br />
&nbsp; </span><span class="keyword">return </span><span class="default">substr</span><span class="keyword">(</span><span class="default">$txt</span><span class="keyword">,</span><span class="default">1</span><span class="keyword">);<br />
}<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="66376"></a>
 <div class="note">
  <strong class='user'>molnaromatic at gmail dot com</strong>
  <a href="#66376" class="date">20-May-2006 05:44</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Simple sample and variables and html "templates":<br />
The PHP code:<br />
variables.php:<br />
<span class="default">&lt;?php<br />
$SYSN</span><span class="keyword">[</span><span class="string">"title"</span><span class="keyword">] = </span><span class="string">"This is Magic!"</span><span class="keyword">;<br />
</span><span class="default">$SYSN</span><span class="keyword">[</span><span class="string">"HEADLINE"</span><span class="keyword">] = </span><span class="string">"Ez magyarul van"</span><span class="keyword">; </span><span class="comment">// This is hungarian<br />
</span><span class="default">$SYSN</span><span class="keyword">[</span><span class="string">"FEAR"</span><span class="keyword">] = </span><span class="string">"Bell in my heart"</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
index.php:<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">include(</span><span class="string">"variables.php"</span><span class="keyword">);<br />
include(</span><span class="string">"template.html"</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span><br />
The template:<br />
template.html<br />
<br />
&lt;html&gt;<br />
&lt;head&gt;&lt;title&gt;&lt;?=$SYSN["title"]?&gt;&lt;/title&gt;&lt;/head&gt;<br />
&lt;body&gt;<br />
&lt;H1&gt;&lt;?=$SYSN["HEADLINE"]?&gt;&lt;/H1&gt;<br />
&lt;p&gt;&lt;?=$SYSN["FEAR"]?&gt;&lt;/p&gt;<br />
&lt;/body&gt;<br />
&lt;/html&gt;<br />
This is simple, quick and very flexibile</span>
</code></div>
  </div>
 </div>
 <a name="59088"></a>
 <div class="note">
  <strong class='user'>Mike at ImmortalSoFar dot com</strong>
  <a href="#59088" class="date">25-Nov-2005 02:03</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
References and "return" can be flakey:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="comment">//&nbsp; This only returns a copy, despite the dereferencing in the function definition<br />
</span><span class="keyword">function &amp;</span><span class="default">GetLogin </span><span class="keyword">()<br />
{<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">$_SESSION</span><span class="keyword">[</span><span class="string">'Login'</span><span class="keyword">];<br />
}<br />
<br />
</span><span class="comment">//&nbsp; This gives a syntax error<br />
</span><span class="keyword">function &amp;</span><span class="default">GetLogin </span><span class="keyword">()<br />
{<br />
&nbsp;&nbsp;&nbsp; return &amp;</span><span class="default">$_SESSION</span><span class="keyword">[</span><span class="string">'Login'</span><span class="keyword">];<br />
}<br />
<br />
</span><span class="comment">//&nbsp; This works<br />
</span><span class="keyword">function &amp;</span><span class="default">GetLogin </span><span class="keyword">()<br />
{<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$ret </span><span class="keyword">= &amp;</span><span class="default">$_SESSION</span><span class="keyword">[</span><span class="string">'Login'</span><span class="keyword">];<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">$ret</span><span class="keyword">;<br />
}<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="58632"></a>
 <div class="note">
  <strong class='user'>david at removethisbit dot futuresbright dot com</strong>
  <a href="#58632" class="date">10-Nov-2005 01:25</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
When using variable variables this is invalid:<br />
<br />
<span class="default">&lt;?php<br />
$my_variable_</span><span class="keyword">{</span><span class="default">$type</span><span class="keyword">}</span><span class="default">_name </span><span class="keyword">= </span><span class="default">true</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
to get around this do something like:<br />
<br />
<span class="default">&lt;?php<br />
$n</span><span class="keyword">=</span><span class="string">"my_variable_{$type}_name"</span><span class="keyword">;<br />
${</span><span class="default">$n</span><span class="keyword">} = </span><span class="default">true</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
(or $$n - I tend to use curly brackets out of habit as it helps t reduce bugs ...)</span>
</code></div>
  </div>
 </div>
 <a name="56351"></a>
 <div class="note">
  <strong class='user'>Chris Hester</strong>
  <a href="#56351" class="date">31-Aug-2005 05:09</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Variables can also be assigned together.<br />
<br />
<span class="default">&lt;?php<br />
$a </span><span class="keyword">= </span><span class="default">$b </span><span class="keyword">= </span><span class="default">$c </span><span class="keyword">= </span><span class="default">1</span><span class="keyword">;<br />
echo </span><span class="default">$a</span><span class="keyword">.</span><span class="default">$b</span><span class="keyword">.</span><span class="default">$c</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
This outputs 111.</span>
</code></div>
  </div>
 </div>
 <a name="54617"></a>
 <div class="note">
  <strong class='user'>Mike Fotes</strong>
  <a href="#54617" class="date">09-Jul-2005 11:46</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
In conditional assignment of variables, be careful because the strings may take over the value of the variable if you do something like this:<br />
<br />
<span class="default">&lt;?php<br />
$condition </span><span class="keyword">= </span><span class="default">true</span><span class="keyword">;<br />
<br />
</span><span class="comment">// Outputs " &lt;-- That should say test"<br />
</span><span class="keyword">echo </span><span class="string">"test" </span><span class="keyword">. (</span><span class="default">$condition</span><span class="keyword">) ? </span><span class="string">" &lt;-- That should say test" </span><span class="keyword">: </span><span class="string">""</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
You will need to enclose the conditional statement and assignments in parenthesis to have it work correctly:<br />
<br />
<span class="default">&lt;?php<br />
$condition </span><span class="keyword">= </span><span class="default">true</span><span class="keyword">;<br />
<br />
</span><span class="comment">// Outputs "test &lt;-- That should say test"<br />
</span><span class="keyword">echo </span><span class="string">"test" </span><span class="keyword">. ((</span><span class="default">$condition</span><span class="keyword">) ? </span><span class="string">" &lt;-- That should say test " </span><span class="keyword">: </span><span class="string">""</span><span class="keyword">);<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="52924"></a>
 <div class="note">
  <strong class='user'>josh at PraxisStudios dot com</strong>
  <a href="#52924" class="date">17-May-2005 01:06</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
As with echo, you can define a variable like this:<br />
<br />
<span class="default">&lt;?php<br />
<br />
$text </span><span class="keyword">= &lt;&lt;&lt;END<br />
</span><span class="default"><br />
&lt;table&gt;<br />
&nbsp;&nbsp;&nbsp; &lt;tr&gt;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; &lt;td&gt;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; $outputdata<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; &lt;/td&gt;<br />
&nbsp;&nbsp; &nbsp; &lt;/tr&gt;<br />
&lt;/table&gt;<br />
<br />
</span><span class="keyword">END;<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
The closing END; must be on a line by itself (no whitespace).<br />
<br />
[EDIT by danbrown AT php DOT net: This note illustrates HEREDOC syntax.&nbsp; For more information on this and similar features, please read the "Strings" section of the manual here: <a href="@w{GRSENCRS}" rel="nofollow" target="_blank">@w{GRSENCRS}</a> ]</span>
</code></div>
  </div>
 </div>
 <a name="51688"></a>
 <div class="note">
  <strong class='user'>mike at go dot online dot pt</strong>
  <a href="#51688" class="date">07-Apr-2005 09:18</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
In addition to what jospape at hotmail dot com and ringo78 at xs4all dot nl wrote, here's the sintax for arrays:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="comment">//considering 2 arrays<br />
</span><span class="default">$foo1 </span><span class="keyword">= array (</span><span class="string">"a"</span><span class="keyword">, </span><span class="string">"b"</span><span class="keyword">, </span><span class="string">"c"</span><span class="keyword">);<br />
</span><span class="default">$foo2 </span><span class="keyword">= array (</span><span class="string">"d"</span><span class="keyword">, </span><span class="string">"e"</span><span class="keyword">, </span><span class="string">"f"</span><span class="keyword">);<br />
<br />
</span><span class="comment">//and 2 variables that hold integers<br />
</span><span class="default">$num </span><span class="keyword">= </span><span class="default">1</span><span class="keyword">;<br />
</span><span class="default">$cell </span><span class="keyword">= </span><span class="default">2</span><span class="keyword">;<br />
<br />
echo ${</span><span class="default">foo</span><span class="keyword">.</span><span class="default">$num</span><span class="keyword">}[</span><span class="default">$cell</span><span class="keyword">]; </span><span class="comment">// outputs "c"<br />
<br />
</span><span class="default">$num </span><span class="keyword">= </span><span class="default">2</span><span class="keyword">;<br />
</span><span class="default">$cell </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">;<br />
<br />
echo ${</span><span class="default">foo</span><span class="keyword">.</span><span class="default">$num</span><span class="keyword">}[</span><span class="default">$cell</span><span class="keyword">]; </span><span class="comment">// outputs "d"<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="49997"></a>
 <div class="note">
  <strong class='user'>lucas dot karisny at linuxmail dot org</strong>
  <a href="#49997" class="date">14-Feb-2005 04:42</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Here's a function to get the name of a given variable.&nbsp; Explanation and examples below.<br />
<br />
<span class="default">&lt;?php<br />
&nbsp; </span><span class="keyword">function </span><span class="default">vname</span><span class="keyword">(&amp;</span><span class="default">$var</span><span class="keyword">, </span><span class="default">$scope</span><span class="keyword">=</span><span class="default">false</span><span class="keyword">, </span><span class="default">$prefix</span><span class="keyword">=</span><span class="string">'unique'</span><span class="keyword">, </span><span class="default">$suffix</span><span class="keyword">=</span><span class="string">'value'</span><span class="keyword">)<br />
&nbsp; {<br />
&nbsp;&nbsp;&nbsp; if(</span><span class="default">$scope</span><span class="keyword">) </span><span class="default">$vals </span><span class="keyword">= </span><span class="default">$scope</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; else&nbsp; &nbsp; &nbsp; </span><span class="default">$vals </span><span class="keyword">= </span><span class="default">$GLOBALS</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$old </span><span class="keyword">= </span><span class="default">$var</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$var </span><span class="keyword">= </span><span class="default">$new </span><span class="keyword">= </span><span class="default">$prefix</span><span class="keyword">.</span><span class="default">rand</span><span class="keyword">().</span><span class="default">$suffix</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$vname </span><span class="keyword">= </span><span class="default">FALSE</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; foreach(</span><span class="default">$vals </span><span class="keyword">as </span><span class="default">$key </span><span class="keyword">=&gt; </span><span class="default">$val</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp;&nbsp; if(</span><span class="default">$val </span><span class="keyword">=== </span><span class="default">$new</span><span class="keyword">) </span><span class="default">$vname </span><span class="keyword">= </span><span class="default">$key</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$var </span><span class="keyword">= </span><span class="default">$old</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">$vname</span><span class="keyword">;<br />
&nbsp; }<br />
</span><span class="default">?&gt;<br />
</span><br />
Explanation:<br />
<br />
The problem with figuring out what value is what key in that variables scope is that several variables might have the same value.&nbsp; To remedy this, the variable is passed by reference and its value is then modified to a random value to make sure there will be a unique match.&nbsp; Then we loop through the scope the variable is contained in and when there is a match of our modified value, we can grab the correct key.<br />
<br />
Examples:<br />
<br />
1.&nbsp; Use of a variable contained in the global scope (default):<br />
<span class="default">&lt;?php<br />
&nbsp; $my_global_variable </span><span class="keyword">= </span><span class="string">"My global string."</span><span class="keyword">;<br />
&nbsp; echo </span><span class="default">vname</span><span class="keyword">(</span><span class="default">$my_global_variable</span><span class="keyword">); </span><span class="comment">// Outputs:&nbsp; my_global_variable<br />
</span><span class="default">?&gt;<br />
</span><br />
2.&nbsp; Use of a local variable:<br />
<span class="default">&lt;?php<br />
&nbsp; </span><span class="keyword">function </span><span class="default">my_local_func</span><span class="keyword">()<br />
&nbsp; {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$my_local_variable </span><span class="keyword">= </span><span class="string">"My local string."</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">vname</span><span class="keyword">(</span><span class="default">$my_local_variable</span><span class="keyword">, </span><span class="default">get_defined_vars</span><span class="keyword">());<br />
&nbsp; }<br />
&nbsp; echo </span><span class="default">my_local_func</span><span class="keyword">(); </span><span class="comment">// Outputs: my_local_variable<br />
</span><span class="default">?&gt;<br />
</span><br />
3.&nbsp; Use of an object property:<br />
<span class="default">&lt;?php<br />
&nbsp; </span><span class="keyword">class </span><span class="default">myclass<br />
&nbsp; </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">__constructor</span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">my_object_property </span><span class="keyword">= </span><span class="string">"My object property&nbsp; string."</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp; }<br />
&nbsp; </span><span class="default">$obj </span><span class="keyword">= new </span><span class="default">myclass</span><span class="keyword">;<br />
&nbsp; echo </span><span class="default">vname</span><span class="keyword">(</span><span class="default">$obj</span><span class="keyword">-&gt;</span><span class="default">my_object_property</span><span class="keyword">, </span><span class="default">$obj</span><span class="keyword">); </span><span class="comment">// Outputs: my_object_property<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="49035"></a>
 <div class="note">
  <strong class='user'>ringo78 at xs4all dot nl</strong>
  <a href="#49035" class="date">14-Jan-2005 12:27</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
<span class="default">&lt;?php<br />
</span><span class="comment">// I am beginning to like curly braces.<br />
// I hope this helps for you work with them<br />
</span><span class="default">$filename0</span><span class="keyword">=</span><span class="string">"k"</span><span class="keyword">;<br />
</span><span class="default">$filename1</span><span class="keyword">=</span><span class="string">"kl"</span><span class="keyword">;<br />
</span><span class="default">$filename2</span><span class="keyword">=</span><span class="string">"klm"</span><span class="keyword">;<br />
&nbsp;</span><span class="default">$i</span><span class="keyword">=</span><span class="default">0</span><span class="keyword">;<br />
for (</span><span class="default">$varname </span><span class="keyword">= </span><span class="default">sprintf</span><span class="keyword">(</span><span class="string">"filename%d"</span><span class="keyword">,</span><span class="default">$i</span><span class="keyword">);&nbsp;&nbsp; isset&nbsp; ( ${</span><span class="default">$varname</span><span class="keyword">} ) ;&nbsp;&nbsp; </span><span class="default">$varname </span><span class="keyword">= </span><span class="default">sprintf</span><span class="keyword">(</span><span class="string">"filename%d"</span><span class="keyword">, </span><span class="default">$i</span><span class="keyword">)&nbsp; )&nbsp; { <br />
&nbsp;&nbsp;&nbsp; echo </span><span class="string">"${$varname} &lt;br&gt;"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$varname </span><span class="keyword">= </span><span class="default">sprintf</span><span class="keyword">(</span><span class="string">"filename%d"</span><span class="keyword">,</span><span class="default">$i</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$i</span><span class="keyword">++;<br />
}<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="48798"></a>
 <div class="note">
  <strong class='user'>Carel Solomon</strong>
  <a href="#48798" class="date">07-Jan-2005 03:02</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
You can also construct a variable name by concatenating two different variables, such as:<br />
<br />
<span class="default">&lt;?php<br />
<br />
$arg </span><span class="keyword">= </span><span class="string">"foo"</span><span class="keyword">;<br />
</span><span class="default">$val </span><span class="keyword">= </span><span class="string">"bar"</span><span class="keyword">;<br />
<br />
</span><span class="comment">//${$arg$val} = "in valid";&nbsp; &nbsp;&nbsp; // Invalid<br />
</span><span class="keyword">${</span><span class="default">$arg </span><span class="keyword">. </span><span class="default">$val</span><span class="keyword">} = </span><span class="string">"working"</span><span class="keyword">;<br />
<br />
echo </span><span class="default">$foobar</span><span class="keyword">;&nbsp; &nbsp;&nbsp; </span><span class="comment">// "working";<br />
//echo $arg$val;&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; // Invalid<br />
//echo ${$arg$val};&nbsp; &nbsp;&nbsp; // Invalid<br />
</span><span class="keyword">echo ${</span><span class="default">$arg </span><span class="keyword">. </span><span class="default">$val</span><span class="keyword">};&nbsp; &nbsp; </span><span class="comment">// "working"<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
Carel</span>
</code></div>
  </div>
 </div>
 <a name="42653"></a>
 <div class="note">
  <strong class='user'>raja shahed  at  christine nothdurfter  dot com</strong>
  <a href="#42653" class="date">25-May-2004 10:58</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
<span class="default">&lt;?php<br />
error_reporting</span><span class="keyword">(</span><span class="default">E_ALL</span><span class="keyword">);<br />
<br />
</span><span class="default">$name </span><span class="keyword">= </span><span class="string">"Christine_Nothdurfter"</span><span class="keyword">;<br />
</span><span class="comment">// not Christine Nothdurfter<br />
// you are not allowed to leave a space inside a variable name ;)<br />
</span><span class="keyword">$</span><span class="default">$name </span><span class="keyword">= </span><span class="string">"'s students of Tyrolean language "</span><span class="keyword">;<br />
<br />
print </span><span class="string">" $name{$$name}&lt;br&gt;"</span><span class="keyword">;<br />
print&nbsp; </span><span class="string">"$name$Christine_Nothdurfter"</span><span class="keyword">;<br />
</span><span class="comment">// same<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="39194"></a>
 <div class="note">
  <strong class='user'>webmaster at daersys dot net</strong>
  <a href="#39194" class="date">20-Jan-2004 08:15</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
You don't necessarily have to escape the dollar-sign before a variable if you want to output its name.<br />
<br />
You can use single quotes instead of double quotes, too.<br />
<br />
For instance:<br />
<br />
<span class="default">&lt;?php<br />
$var </span><span class="keyword">= </span><span class="string">"test"</span><span class="keyword">;<br />
<br />
echo </span><span class="string">"$var"</span><span class="keyword">; </span><span class="comment">// Will output the string "test"<br />
<br />
</span><span class="keyword">echo </span><span class="string">"\$var"</span><span class="keyword">; </span><span class="comment">// Will output the string "$var"<br />
<br />
</span><span class="keyword">echo </span><span class="string">'$var'</span><span class="keyword">; </span><span class="comment">// Will do the exact same thing as the previous line<br />
</span><span class="default">?&gt;<br />
</span><br />
Why?<br />
Well, the reason for this is that the PHP Parser will not attempt to parse strings encapsulated in single quotes (as opposed to strings within double quotes) and therefore outputs exactly what it's being fed with :)<br />
<br />
To output the value of a variable within a single-quote-encapsulated string you'll have to use something along the lines of the following code:<br />
<br />
<span class="default">&lt;?php<br />
$var </span><span class="keyword">= </span><span class="string">'test'</span><span class="keyword">;<br />
</span><span class="comment">/*<br />
Using single quotes here seeing as I don't need the parser to actually parse the content of this variable but merely treat it as an ordinary string<br />
*/<br />
<br />
</span><span class="keyword">echo </span><span class="string">'$var = "' </span><span class="keyword">. </span><span class="default">$var </span><span class="keyword">. </span><span class="string">'"'</span><span class="keyword">;<br />
</span><span class="comment">/*<br />
Will output:<br />
$var = "test"<br />
*/<br />
</span><span class="default">?&gt;<br />
</span><br />
HTH<br />
- Daerion</span>
</code></div>
  </div>
 </div>
 <a name="28501"></a>
 <div class="note">
  <strong class='user'>unleaded at nospam dot unleadedonline dot net</strong>
  <a href="#28501" class="date">14-Jan-2003 06:37</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
References are great if you want to point to a variable which you don't quite know the value yet ;)<br />
<br />
eg:<br />
<br />
<span class="default">&lt;?php<br />
$error_msg </span><span class="keyword">= &amp;</span><span class="default">$messages</span><span class="keyword">[</span><span class="string">'login_error'</span><span class="keyword">]; </span><span class="comment">// Create a reference<br />
<br />
</span><span class="default">$messages</span><span class="keyword">[</span><span class="string">'login_error'</span><span class="keyword">] = </span><span class="string">'test'</span><span class="keyword">; </span><span class="comment">// Then later on set the referenced value<br />
<br />
</span><span class="keyword">echo </span><span class="default">$error_msg</span><span class="keyword">; </span><span class="comment">// echo the 'referenced value'<br />
</span><span class="default">?&gt;<br />
</span><br />
The output will be:<br />
<br />
test</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=language.variables&amp;redirect=http://www.php.net/manual/en/language.variables.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.variables&amp;redirect=http://www.php.net/manual/en/language.variables.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/language.variables.php">show source</a> |
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