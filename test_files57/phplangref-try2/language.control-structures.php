<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Control Structures - Manual</title>
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
 <link rel="prev" href="language.operators.type.php" />
 <link rel="next" href="control-structures.intro.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/control-structures" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="alternate" href="/manual/en/feeds/language.control-structures.atom" type="application/atom+xml" />
 <link rel="canonical" href="http://php.net/manual/en/language.control-structures.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="@w{ARPHY8JH}" />
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
 <li><a href="language.variables.php">Variables</a></li>
 <li><a href="language.constants.php">Constants</a></li>
 <li><a href="language.expressions.php">Expressions</a></li>
 <li><a href="language.operators.php">Operators</a></li>
 <li class="active"><a href="language.control-structures.php">Control Structures</a></li>
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
  <a href="control-structures.intro.php">Introduction<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.operators.type.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Type Operators</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.control-structures.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/language.control-structures.php">Brazilian Portuguese</option>
    <option value="zh/language.control-structures.php">Chinese (Simplified)</option>
    <option value="fr/language.control-structures.php">French</option>
    <option value="de/language.control-structures.php">German</option>
    <option value="ja/language.control-structures.php">Japanese</option>
    <option value="pl/language.control-structures.php">Polish</option>
    <option value="ro/language.control-structures.php">Romanian</option>
    <option value="ru/language.control-structures.php">Russian</option>
    <option value="fa/language.control-structures.php">Persian</option>
    <option value="es/language.control-structures.php">Spanish</option>
    <option value="tr/language.control-structures.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="language.control-structures" class="chapter">
 <h1>Control Structures</h1>
<h2>Table of Contents</h2><ul class="chunklist chunklist_chapter"><li><a href="control-structures.intro.php">Introduction</a></li><li><a href="control-structures.if.php">if</a></li><li><a href="control-structures.else.php">else</a></li><li><a href="control-structures.elseif.php">elseif/else if</a></li><li><a href="control-structures.alternative-syntax.php">Alternative syntax for control structures</a></li><li><a href="control-structures.while.php">while</a></li><li><a href="control-structures.do.while.php">do-while</a></li><li><a href="control-structures.for.php">for</a></li><li><a href="control-structures.foreach.php">foreach</a></li><li><a href="control-structures.break.php">break</a></li><li><a href="control-structures.continue.php">continue</a></li><li><a href="control-structures.switch.php">switch</a></li><li><a href="control-structures.declare.php">declare</a></li><li><a href="function.return.php">return</a></li><li><a href="function.require.php">require</a></li><li><a href="function.include.php">include</a></li><li><a href="function.require-once.php">require_once</a></li><li><a href="function.include-once.php">include_once</a></li><li><a href="control-structures.goto.php">goto</a></li></ul>


 

 






 






 






 






 






 






 






 






 






 






 






 






 






 






 






 






 






 







</div>
<br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="control-structures.intro.php">Introduction<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.operators.type.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Type Operators</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.control-structures.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=language.control-structures&amp;redirect=@w{ARPHY8JH}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.control-structures&amp;redirect=@w{ARPHY8JH}">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Control Structures</strong>
 </div><div id="allnotes">
 <a name="108750"></a>
 <div class="note">
  <strong class='user'>Jeffrey</strong>
  <a href="#108750" class="date">23-May-2012 01:28</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
CONTROL STRUCTURE -- BOOLEAN REQUIRED<br />
<br />
If you are not sure what will work in your IF statements, try DISECTING your variables. Below I've written three (3) empty CLASS DEFINITIONS (Point, Dimension, and Rectangle), and declared an array called $items that holds all the PHP types you can imagine -- booleans, strings, empty strings, integers, floats, null, arrays, empty arrays, and objects too. The rest of the code really chews up the current $item and spits it out for lunch... Try running this code so you can see the HTML TABLE that is created -- it'll be worth your while.<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">class </span><span class="default">Point </span><span class="keyword">{ }<br />
class </span><span class="default">Dimension </span><span class="keyword">{ }<br />
class </span><span class="default">Rectangle </span><span class="keyword">{ }<br />
<br />
</span><span class="default">$items </span><span class="keyword">= array(</span><span class="default">true</span><span class="keyword">, </span><span class="default">false</span><span class="keyword">, </span><span class="default">null</span><span class="keyword">, </span><span class="default">23</span><span class="keyword">, </span><span class="default">0</span><span class="keyword">, -</span><span class="default">26</span><span class="keyword">, </span><span class="default">4.21</span><span class="keyword">, </span><span class="default">0.0</span><span class="keyword">, -</span><span class="default">3.76</span><span class="keyword">,<br />
&nbsp; </span><span class="string">'hello'</span><span class="keyword">, </span><span class="string">''</span><span class="keyword">, array(</span><span class="default">1</span><span class="keyword">, </span><span class="default">2</span><span class="keyword">, </span><span class="default">3</span><span class="keyword">), array(</span><span class="string">''</span><span class="keyword">, </span><span class="string">''</span><span class="keyword">, </span><span class="string">''</span><span class="keyword">), array(),<br />
&nbsp; new </span><span class="default">stdClass</span><span class="keyword">(), new </span><span class="default">Point</span><span class="keyword">(), new </span><span class="default">Dimension</span><span class="keyword">(), new </span><span class="default">Rectangle</span><span class="keyword">());<br />
<br />
echo </span><span class="string">'&lt;table cellpadding="4" border="1"&gt;<br />
&nbsp; &lt;tr&gt;<br />
&nbsp;&nbsp;&nbsp; &lt;th&gt;syntax&lt;/th&gt;<br />
&nbsp;&nbsp;&nbsp; &lt;th&gt;value&lt;/th&gt;<br />
&nbsp;&nbsp;&nbsp; &lt;th&gt;type&lt;/th&gt;<br />
&nbsp;&nbsp;&nbsp; &lt;th&gt;empty&lt;/th&gt;<br />
&nbsp;&nbsp;&nbsp; &lt;th&gt;boolean&lt;/th&gt;<br />
&nbsp; &lt;/tr&gt;' </span><span class="keyword">. </span><span class="string">"\n"</span><span class="keyword">;<br />
<br />
foreach(</span><span class="default">$items </span><span class="keyword">AS </span><span class="default">$item</span><span class="keyword">)<br />
{<br />
&nbsp; </span><span class="default">$booleanValue </span><span class="keyword">= (boolean)</span><span class="default">$item</span><span class="keyword">;<br />
&nbsp; </span><span class="default">$empty </span><span class="keyword">= (empty(</span><span class="default">$item</span><span class="keyword">) ? </span><span class="string">'EMPTY' </span><span class="keyword">: </span><span class="string">'&amp;nbsp;'</span><span class="keyword">);<br />
&nbsp; </span><span class="default">$type </span><span class="keyword">= </span><span class="default">gettype</span><span class="keyword">(</span><span class="default">$item</span><span class="keyword">);<br />
&nbsp; </span><span class="default">$syntax </span><span class="keyword">= </span><span class="string">'if((boolean)'</span><span class="keyword">;<br />
<br />
&nbsp; </span><span class="default">$val</span><span class="keyword">;<br />
<br />
&nbsp; if(</span><span class="default">$type </span><span class="keyword">== </span><span class="default">boolean</span><span class="keyword">)<br />
&nbsp; {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$val </span><span class="keyword">= (</span><span class="default">$booleanValue </span><span class="keyword">? </span><span class="string">'true' </span><span class="keyword">: </span><span class="string">'false'</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$syntax </span><span class="keyword">.= (</span><span class="default">$val </span><span class="keyword">. </span><span class="string">')'</span><span class="keyword">);<br />
&nbsp; }<br />
&nbsp; else if(</span><span class="default">$type </span><span class="keyword">== </span><span class="string">'NULL'</span><span class="keyword">)<br />
&nbsp; {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$val </span><span class="keyword">= </span><span class="string">'null'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$syntax </span><span class="keyword">.= </span><span class="string">'null)'</span><span class="keyword">;<br />
&nbsp; }<br />
&nbsp; else if(</span><span class="default">$type </span><span class="keyword">== </span><span class="default">double </span><span class="keyword">&amp;&amp; !</span><span class="default">$booleanValue</span><span class="keyword">)<br />
&nbsp; {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$val </span><span class="keyword">= </span><span class="string">'0.0'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$syntax </span><span class="keyword">.= </span><span class="string">'0.0)'</span><span class="keyword">;<br />
&nbsp; }<br />
&nbsp; else if(</span><span class="default">$type </span><span class="keyword">== </span><span class="default">string</span><span class="keyword">)<br />
&nbsp; {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$val </span><span class="keyword">= </span><span class="string">'\'' </span><span class="keyword">. </span><span class="default">$item </span><span class="keyword">. </span><span class="string">'\''</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$syntax </span><span class="keyword">.= (</span><span class="default">$val </span><span class="keyword">. </span><span class="string">')'</span><span class="keyword">);<br />
&nbsp; }<br />
&nbsp; else if(</span><span class="default">$type </span><span class="keyword">== </span><span class="string">'array'</span><span class="keyword">)<br />
&nbsp; {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$val </span><span class="keyword">= </span><span class="default">$item</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$syntax </span><span class="keyword">.= </span><span class="string">'$array)'</span><span class="keyword">;<br />
&nbsp; }<br />
&nbsp; else if(</span><span class="default">$type </span><span class="keyword">== </span><span class="string">'object'</span><span class="keyword">)<br />
&nbsp; {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$val </span><span class="keyword">= </span><span class="default">get_class</span><span class="keyword">(</span><span class="default">$item</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$syntax </span><span class="keyword">.= (</span><span class="string">'$' </span><span class="keyword">. </span><span class="default">strtolower</span><span class="keyword">(</span><span class="default">$val</span><span class="keyword">) . </span><span class="string">')'</span><span class="keyword">);<br />
&nbsp; }<br />
&nbsp; else<br />
&nbsp; {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$val </span><span class="keyword">= </span><span class="default">$item</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$syntax </span><span class="keyword">.= (</span><span class="default">$val </span><span class="keyword">. </span><span class="string">')'</span><span class="keyword">);<br />
&nbsp; }<br />
<br />
&nbsp; echo </span><span class="string">'&nbsp; &lt;tr style="color: ' </span><span class="keyword">. (</span><span class="default">$booleanValue </span><span class="keyword">? </span><span class="string">'#006600' </span><span class="keyword">: </span><span class="string">'#880000'</span><span class="keyword">) . </span><span class="string">';"&gt;<br />
&nbsp;&nbsp;&nbsp; &lt;td&gt;&lt;code&gt;' </span><span class="keyword">. </span><span class="default">$syntax </span><span class="keyword">. </span><span class="string">'&lt;/code&gt;&lt;/td&gt;<br />
&nbsp;&nbsp;&nbsp; &lt;td&gt;' </span><span class="keyword">. </span><span class="default">$val </span><span class="keyword">. </span><span class="string">'&lt;/td&gt;<br />
&nbsp;&nbsp;&nbsp; &lt;td&gt;' </span><span class="keyword">. </span><span class="default">$type </span><span class="keyword">. </span><span class="string">'&lt;/td&gt;<br />
&nbsp;&nbsp;&nbsp; &lt;td&gt;' </span><span class="keyword">. </span><span class="default">$empty </span><span class="keyword">. </span><span class="string">'&lt;/td&gt;<br />
&nbsp;&nbsp;&nbsp; &lt;td&gt;' </span><span class="keyword">. (</span><span class="default">$booleanValue </span><span class="keyword">? </span><span class="string">'TRUE' </span><span class="keyword">: </span><span class="string">'FALSE'</span><span class="keyword">) . </span><span class="string">'&lt;/td&gt;<br />
&nbsp; &lt;/tr&gt;' </span><span class="keyword">. </span><span class="string">"\n"</span><span class="keyword">;<br />
}<br />
<br />
echo </span><span class="string">'&lt;/table&gt;' </span><span class="keyword">. </span><span class="string">"\n"</span><span class="keyword">;<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
Looking at the HTML output: notice that even integers and floats with a value of zero are considered EMPTY, and all that are EMPTY are FALSE boolean values. And take a gander at the boolean type with a false value... somebody is covering there bases!</span>
</code></div>
  </div>
 </div>
 <a name="77434"></a>
 <div class="note">
  <strong class='user'>wintermute</strong>
  <a href="#77434" class="date">29-Aug-2007 12:45</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Sinured: You can do the same thing with logical OR; if the first test is true, the second will never be executed.<br />
<br />
<span class="default">&lt;?PHP<br />
</span><span class="keyword">if (empty(</span><span class="default">$user_id</span><span class="keyword">) || </span><span class="default">in_array</span><span class="keyword">(</span><span class="default">$user_id</span><span class="keyword">, </span><span class="default">$banned_list</span><span class="keyword">))<br />
{<br />
exit();<br />
}<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="76839"></a>
 <div class="note">
  <strong class='user'>Sinured</strong>
  <a href="#76839" class="date">01-Aug-2007 11:59</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
As mentioned below, PHP stops evaluating expressions as soon as the result is clear. So a nice shortcut for if-statements is logical AND -- if the left expression is false, then the right expression can’t possibly change the result anymore, so it’s not executed.<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="comment">/* defines MYAPP_DIR if not already defined */<br />
</span><span class="keyword">if (!</span><span class="default">defined</span><span class="keyword">(</span><span class="string">'MYAPP_DIR'</span><span class="keyword">)) {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">define</span><span class="keyword">(</span><span class="string">'MYAPP_DIR'</span><span class="keyword">, </span><span class="default">dirname</span><span class="keyword">(</span><span class="default">getcwd</span><span class="keyword">()));<br />
}<br />
<br />
</span><span class="comment">/* the same */<br />
</span><span class="keyword">!</span><span class="default">defined</span><span class="keyword">(</span><span class="string">'MYAPP_DIR'</span><span class="keyword">) &amp;&amp; </span><span class="default">define</span><span class="keyword">(</span><span class="string">'MYAPP_DIR'</span><span class="keyword">, </span><span class="default">dirname</span><span class="keyword">(</span><span class="default">getcwd</span><span class="keyword">()));<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="65705"></a>
 <div class="note">
  <strong class='user'>dougnoel</strong>
  <a href="#65705" class="date">05-May-2006 06:29</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Further response to Niels:<br />
<br />
It's not laziness, it's optimization.&nbsp; It saves CPUs cycles.&nbsp; However, it's good to know, as it allows you to optimize your code when writing.&nbsp; For example, when determining if someone has permissions to delete an object, you can do something like the following:<br />
<br />
if ($is_admin &amp;&amp; $has_delete_permissions)<br />
<br />
If only an admin can have those permissions, there's no need to check for the permissions if the user is not an admin.</span>
</code></div>
  </div>
 </div>
 <a name="48490"></a>
 <div class="note">
  <strong class='user'>niels dot laukens at tijd dot com</strong>
  <a href="#48490" class="date">26-Dec-2004 07:49</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
For the people that know C: php is lazy when evaluating expressions. That is, as soon as it knows the outcome, it'll stop processing.<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">if ( </span><span class="default">FALSE </span><span class="keyword">&amp;&amp; </span><span class="default">some_function</span><span class="keyword">() )<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="string">"something"</span><span class="keyword">;<br />
</span><span class="comment">// some_function() will not be called, since php knows that it will never have to execute the if-block<br />
</span><span class="default">?&gt;<br />
</span><br />
This comes in nice in situations like this:<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">if ( </span><span class="default">file_exists</span><span class="keyword">(</span><span class="default">$filename</span><span class="keyword">) &amp;&amp; </span><span class="default">filemtime</span><span class="keyword">(</span><span class="default">$filename</span><span class="keyword">) &gt; </span><span class="default">time</span><span class="keyword">() )<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">do_something</span><span class="keyword">();<br />
</span><span class="comment">// filemtime will never give an file-not-found-error, since php will stop parsing as soon as file_exists returns FALSE<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=language.control-structures&amp;redirect=@w{ARPHY8JH}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.control-structures&amp;redirect=@w{ARPHY8JH}">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/language.control-structures.php">show source</a> |
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