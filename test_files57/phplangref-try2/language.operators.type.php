<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Type Operators - Manual</title>
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
 <link rel="prev" href="language.operators.array.php" />
 <link rel="next" href="language.control-structures.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/operators.type" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/language.operators.type.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/language.operators.type.php" />
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
 <li><a href="language.operators.errorcontrol.php">Error Control Operators</a></li>
 <li><a href="language.operators.execution.php">Execution Operators</a></li>
 <li><a href="language.operators.increment.php">Incrementing/Decrementing Operators</a></li>
 <li><a href="language.operators.logical.php">Logical Operators</a></li>
 <li><a href="language.operators.string.php">String Operators</a></li>
 <li><a href="language.operators.array.php">Array Operators</a></li>
 <li class="active"><a href="language.operators.type.php">Type Operators</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="language.control-structures.php">Control Structures<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.operators.array.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Array Operators</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.operators.type.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/language.operators.type.php">Brazilian Portuguese</option>
    <option value="zh/language.operators.type.php">Chinese (Simplified)</option>
    <option value="fr/language.operators.type.php">French</option>
    <option value="de/language.operators.type.php">German</option>
    <option value="ja/language.operators.type.php">Japanese</option>
    <option value="pl/language.operators.type.php">Polish</option>
    <option value="ro/language.operators.type.php">Romanian</option>
    <option value="ru/language.operators.type.php">Russian</option>
    <option value="fa/language.operators.type.php">Persian</option>
    <option value="es/language.operators.type.php">Spanish</option>
    <option value="tr/language.operators.type.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="language.operators.type" class="sect1">
   <h2 class="title">Type Operators</h2>
   <p class="para">
    <em>instanceof</em> is used to determine whether a PHP variable
    is an instantiated object of a certain
    <a href="language.oop5.basic.php#language.oop5.basic.class" class="link">class</a>:
    <div class="example" id="example-124">
     <p><strong>Example #1 Using <em>instanceof</em> with classes</strong></p>
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">class&nbsp;</span><span style="color: #0000BB">MyClass<br /></span><span style="color: #007700">{<br />}<br /><br />class&nbsp;</span><span style="color: #0000BB">NotMyClass<br /></span><span style="color: #007700">{<br />}<br /></span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">MyClass</span><span style="color: #007700">;<br /><br /></span><span style="color: #0000BB">var_dump</span><span style="color: #007700">(</span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">instanceof&nbsp;</span><span style="color: #0000BB">MyClass</span><span style="color: #007700">);<br /></span><span style="color: #0000BB">var_dump</span><span style="color: #007700">(</span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">instanceof&nbsp;</span><span style="color: #0000BB">NotMyClass</span><span style="color: #007700">);<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

     <div class="example-contents"><p>The above example will output:</p></div>
     <div class="example-contents screen">
<div class="cdata"><pre>
bool(true)
bool(false)
</pre></div>
     </div>
    </div>
   </p>
   <p class="para">
    <em>instanceof</em> can also be used to determine whether a variable
    is an instantiated object of a class that inherits from a parent class:
    <div class="example" id="example-125">
     <p><strong>Example #2 Using <em>instanceof</em> with inherited classes</strong></p>
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">class&nbsp;</span><span style="color: #0000BB">ParentClass<br /></span><span style="color: #007700">{<br />}<br /><br />class&nbsp;</span><span style="color: #0000BB">MyClass&nbsp;</span><span style="color: #007700">extends&nbsp;</span><span style="color: #0000BB">ParentClass<br /></span><span style="color: #007700">{<br />}<br /><br /></span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">MyClass</span><span style="color: #007700">;<br /><br /></span><span style="color: #0000BB">var_dump</span><span style="color: #007700">(</span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">instanceof&nbsp;</span><span style="color: #0000BB">MyClass</span><span style="color: #007700">);<br /></span><span style="color: #0000BB">var_dump</span><span style="color: #007700">(</span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">instanceof&nbsp;</span><span style="color: #0000BB">ParentClass</span><span style="color: #007700">);<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

     <div class="example-contents"><p>The above example will output:</p></div>
     <div class="example-contents screen">
<div class="cdata"><pre>
bool(true)
bool(true)
</pre></div>
     </div>
    </div>
   </p>
   <p class="para">
    To check if an object is <em class="emphasis">not</em> an instanceof a class, the
    <a href="language.operators.logical.php" class="link">logical <em>not</em>
    operator</a> can be used.
    <div class="example" id="example-126">
     <p><strong>Example #3 Using <em>instanceof</em> to check if object is <em class="emphasis">not</em> an
      instanceof a class</strong></p>
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">class&nbsp;</span><span style="color: #0000BB">MyClass<br /></span><span style="color: #007700">{<br />}<br /><br /></span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">MyClass</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">var_dump</span><span style="color: #007700">(!(</span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">instanceof&nbsp;</span><span style="color: #0000BB">stdClass</span><span style="color: #007700">));<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

    <div class="example-contents"><p>The above example will output:</p></div>
    <div class="example-contents screen">
<div class="cdata"><pre>
bool(true)
</pre></div>
     </div>
    </div>
   </p>
   <p class="para">
    Lastly, <em>instanceof</em> can also be used to determine whether
    a variable is an instantiated object of a class that implements an
    <a href="language.oop5.interfaces.php" class="link">interface</a>:
    <div class="example" id="example-127">
     <p><strong>Example #4 Using <em>instanceof</em> for class</strong></p>
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">interface&nbsp;</span><span style="color: #0000BB">MyInterface<br /></span><span style="color: #007700">{<br />}<br /><br />class&nbsp;</span><span style="color: #0000BB">MyClass&nbsp;</span><span style="color: #007700">implements&nbsp;</span><span style="color: #0000BB">MyInterface<br /></span><span style="color: #007700">{<br />}<br /><br /></span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">MyClass</span><span style="color: #007700">;<br /><br /></span><span style="color: #0000BB">var_dump</span><span style="color: #007700">(</span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">instanceof&nbsp;</span><span style="color: #0000BB">MyClass</span><span style="color: #007700">);<br /></span><span style="color: #0000BB">var_dump</span><span style="color: #007700">(</span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">instanceof&nbsp;</span><span style="color: #0000BB">MyInterface</span><span style="color: #007700">);<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

     <div class="example-contents"><p>The above example will output:</p></div>
     <div class="example-contents screen">
<div class="cdata"><pre>
bool(true)
bool(true)
</pre></div>
     </div>
    </div>
   </p>
   <p class="para">
    Although <em>instanceof</em> is usually used with a literal classname,
    it can also be used with another object or a string variable:
    <div class="example" id="example-128">
     <p><strong>Example #5 Using <em>instanceof</em> with other variables</strong></p>
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">interface&nbsp;</span><span style="color: #0000BB">MyInterface<br /></span><span style="color: #007700">{<br />}<br /><br />class&nbsp;</span><span style="color: #0000BB">MyClass&nbsp;</span><span style="color: #007700">implements&nbsp;</span><span style="color: #0000BB">MyInterface<br /></span><span style="color: #007700">{<br />}<br /><br /></span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">MyClass</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">$b&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">MyClass</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">$c&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'MyClass'</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">$d&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'NotMyClass'</span><span style="color: #007700">;<br /><br /></span><span style="color: #0000BB">var_dump</span><span style="color: #007700">(</span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">instanceof&nbsp;</span><span style="color: #0000BB">$b</span><span style="color: #007700">);&nbsp;</span><span style="color: #FF8000">//&nbsp;$b&nbsp;is&nbsp;an&nbsp;object&nbsp;of&nbsp;class&nbsp;MyClass<br /></span><span style="color: #0000BB">var_dump</span><span style="color: #007700">(</span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">instanceof&nbsp;</span><span style="color: #0000BB">$c</span><span style="color: #007700">);&nbsp;</span><span style="color: #FF8000">//&nbsp;$c&nbsp;is&nbsp;a&nbsp;string&nbsp;'MyClass'<br /></span><span style="color: #0000BB">var_dump</span><span style="color: #007700">(</span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">instanceof&nbsp;</span><span style="color: #0000BB">$d</span><span style="color: #007700">);&nbsp;</span><span style="color: #FF8000">//&nbsp;$d&nbsp;is&nbsp;a&nbsp;string&nbsp;'NotMyClass'<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

    <div class="example-contents"><p>The above example will output:</p></div>
    <div class="example-contents screen">
<div class="cdata"><pre>
bool(true)
bool(true)
bool(false)
</pre></div>
     </div>
    </div>
   </p>
   <p class="para">
    There are a few pitfalls to be aware of.  Before PHP version 5.1.0,
    <em>instanceof</em> would call  <span class="function"><a href="function.autoload.php" class="function">__autoload()</a></span>
    if the class name did not exist.  In addition, if the class was not loaded,
    a fatal error would occur.  This can be worked around by using a dynamic
    class reference, or a string variable containing the class name:
    <div class="example" id="example-129">
     <p><strong>Example #6 Avoiding classname lookups and fatal errors with <em>instanceof</em> in PHP 5.0</strong></p>
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br />$d&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'NotMyClass'</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">var_dump</span><span style="color: #007700">(</span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">instanceof&nbsp;</span><span style="color: #0000BB">$d</span><span style="color: #007700">);&nbsp;</span><span style="color: #FF8000">//&nbsp;no&nbsp;fatal&nbsp;error&nbsp;here<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

    <div class="example-contents"><p>The above example will output:</p></div>
    <div class="example-contents screen">
<div class="cdata"><pre>
bool(false)
</pre></div>
     </div>
    </div>
   </p>
   <p class="simpara">
    The <em>instanceof</em> operator was introduced in PHP 5.
    Before this time  <span class="function"><a href="function.is-a.php" class="function">is_a()</a></span> was used but
     <span class="function"><a href="function.is-a.php" class="function">is_a()</a></span> has since been deprecated in favor of
    <em>instanceof</em>. Note that as of PHP 5.3.0,
     <span class="function"><a href="function.is-a.php" class="function">is_a()</a></span> is no longer deprecated.
   </p>
   <p class="para">
    See also  <span class="function"><a href="function.get-class.php" class="function">get_class()</a></span> and
     <span class="function"><a href="function.is-a.php" class="function">is_a()</a></span>.
   </p>
  </div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="language.control-structures.php">Control Structures<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.operators.array.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Array Operators</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.operators.type.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=language.operators.type&amp;redirect=http://www.php.net/manual/en/language.operators.type.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.operators.type&amp;redirect=http://www.php.net/manual/en/language.operators.type.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Type Operators</strong>
 </div><div id="allnotes">
 <a name="108696"></a>
 <div class="note">
  <strong class='user'>wbcarts at juno dot com</strong>
  <a href="#108696" class="date">18-May-2012 02:14</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
SIMPLE, CLEAN, CLEAR use of the instanceof OPERATOR<br />
<br />
First, define a couple of simple PHP Objects to work on -- I'll introduce Circle and Point. Here's the class definitions for both:<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">class </span><span class="default">Circle<br />
</span><span class="keyword">{<br />
&nbsp; protected </span><span class="default">$radius </span><span class="keyword">= </span><span class="default">1.0</span><span class="keyword">;<br />
<br />
&nbsp; </span><span class="comment">/*<br />
&nbsp;&nbsp; * This function is the reason we are going to use the<br />
&nbsp;&nbsp; * instanceof operator below.<br />
&nbsp;&nbsp; */<br />
&nbsp; </span><span class="keyword">public function </span><span class="default">setRadius</span><span class="keyword">(</span><span class="default">$r</span><span class="keyword">)<br />
&nbsp; {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">radius </span><span class="keyword">= </span><span class="default">$r</span><span class="keyword">;<br />
&nbsp; }<br />
<br />
&nbsp; public function </span><span class="default">__toString</span><span class="keyword">()<br />
&nbsp; {<br />
&nbsp;&nbsp;&nbsp; return </span><span class="string">'Circle [radius=' </span><span class="keyword">. </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">radius </span><span class="keyword">. </span><span class="string">']'</span><span class="keyword">;<br />
&nbsp; }<br />
}<br />
<br />
class </span><span class="default">Point<br />
</span><span class="keyword">{<br />
&nbsp; protected </span><span class="default">$x </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">;<br />
&nbsp; protected </span><span class="default">$y </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">;<br />
<br />
&nbsp; </span><span class="comment">/*<br />
&nbsp;&nbsp; * This function is the reason we are going to use the<br />
&nbsp;&nbsp; * instanceof operator below.<br />
&nbsp;&nbsp; */<br />
&nbsp; </span><span class="keyword">public function </span><span class="default">setLocation</span><span class="keyword">(</span><span class="default">$x</span><span class="keyword">, </span><span class="default">$y</span><span class="keyword">)<br />
&nbsp; {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">x </span><span class="keyword">= </span><span class="default">$x</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">y </span><span class="keyword">= </span><span class="default">$y</span><span class="keyword">;<br />
&nbsp; }<br />
<br />
&nbsp; public function </span><span class="default">__toString</span><span class="keyword">()<br />
&nbsp; {<br />
&nbsp;&nbsp;&nbsp; return </span><span class="string">'Point [x=' </span><span class="keyword">. </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">x </span><span class="keyword">. </span><span class="string">', y=' </span><span class="keyword">. </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">y </span><span class="keyword">. </span><span class="string">']'</span><span class="keyword">;<br />
&nbsp; }<br />
}<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
Now instantiate a few instances of these types. Note, I will put them in an array (collection) so we can iterate through them quickly.<br />
<br />
<span class="default">&lt;?php<br />
<br />
$myCollection </span><span class="keyword">= array(</span><span class="default">123</span><span class="keyword">, </span><span class="string">'abc'</span><span class="keyword">, </span><span class="string">'Hello World!'</span><span class="keyword">,<br />
&nbsp; new </span><span class="default">Circle</span><span class="keyword">(), new </span><span class="default">Circle</span><span class="keyword">(), new </span><span class="default">Circle</span><span class="keyword">(),<br />
&nbsp; new </span><span class="default">Point</span><span class="keyword">(), new </span><span class="default">Point</span><span class="keyword">(), new </span><span class="default">Point</span><span class="keyword">());<br />
<br />
</span><span class="default">$i </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">;<br />
foreach(</span><span class="default">$myCollection </span><span class="keyword">AS </span><span class="default">$item</span><span class="keyword">)<br />
{<br />
&nbsp; </span><span class="comment">/*<br />
&nbsp;&nbsp; * The setRadius() function is written in the Circle class<br />
&nbsp;&nbsp; * definition above, so make sure $item is an instance of<br />
&nbsp;&nbsp; * type Circle BEFORE calling it AND to avoid PHP PMS!<br />
&nbsp;&nbsp; */<br />
&nbsp; </span><span class="keyword">if(</span><span class="default">$item </span><span class="keyword">instanceof </span><span class="default">Circle</span><span class="keyword">)<br />
&nbsp; {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$item</span><span class="keyword">-&gt;</span><span class="default">setRadius</span><span class="keyword">(</span><span class="default">$i</span><span class="keyword">);<br />
&nbsp; }<br />
<br />
&nbsp; </span><span class="comment">/*<br />
&nbsp;&nbsp; * The setLocation() function is written in the Point class<br />
&nbsp;&nbsp; * definition above, so make sure $item is an instance of <br />
&nbsp;&nbsp; * type Point BEFORE calling it AND to stay out of the ER!<br />
&nbsp;&nbsp; */<br />
&nbsp; </span><span class="keyword">if(</span><span class="default">$item </span><span class="keyword">instanceof </span><span class="default">Point</span><span class="keyword">)<br />
&nbsp; {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$item</span><span class="keyword">-&gt;</span><span class="default">setLocation</span><span class="keyword">(</span><span class="default">$i</span><span class="keyword">, </span><span class="default">$i</span><span class="keyword">);<br />
&nbsp; }<br />
<br />
&nbsp; echo </span><span class="string">'$myCollection[' </span><span class="keyword">. </span><span class="default">$i</span><span class="keyword">++ . </span><span class="string">'] = ' </span><span class="keyword">. </span><span class="default">$item </span><span class="keyword">. </span><span class="string">'&lt;br&gt;'</span><span class="keyword">;<br />
}<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
$myCollection[0] = 123<br />
$myCollection[1] = abc<br />
$myCollection[2] = Hello World!<br />
$myCollection[3] = Circle [radius=3]<br />
$myCollection[4] = Circle [radius=4]<br />
$myCollection[5] = Circle [radius=5]<br />
$myCollection[6] = Point [x=6, y=6]<br />
$myCollection[7] = Point [x=7, y=7]<br />
$myCollection[8] = Point [x=8, y=8]</span>
</code></div>
  </div>
 </div>
 <a name="106882"></a>
 <div class="note">
  <strong class='user'>Sudarshan Wadkar</strong>
  <a href="#106882" class="date">15-Dec-2011 04:34</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I don't see any mention of "namespaces" on this page so I thought I would chime in. The instanceof operator takes FQCN as second operator when you pass it as string and not a simple class name. It will not resolve it even if you have a `use MyNamespace\Bar;` at the top level. Here is what I am trying to say:<br />
<br />
## testinclude.php ##<br />
<span class="default">&lt;?php<br />
namespace Bar1</span><span class="keyword">;<br />
{<br />
class </span><span class="default">Foo1</span><span class="keyword">{ }<br />
}<br />
</span><span class="default">namespace Bar2</span><span class="keyword">;<br />
{<br />
class </span><span class="default">Foo2</span><span class="keyword">{ }<br />
}<br />
</span><span class="default">?&gt;<br />
</span>## test.php ##<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">include(</span><span class="string">'testinclude.php'</span><span class="keyword">);<br />
use </span><span class="default">Bar1Foo1 </span><span class="keyword">as </span><span class="default">Foo</span><span class="keyword">;<br />
</span><span class="default">$foo1 </span><span class="keyword">= new </span><span class="default">Foo</span><span class="keyword">(); </span><span class="default">$className </span><span class="keyword">= </span><span class="string">'Bar1\Foo1'</span><span class="keyword">;<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$foo1 </span><span class="keyword">instanceof </span><span class="default">Bar1Foo1</span><span class="keyword">);<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$foo1 </span><span class="keyword">instanceof </span><span class="default">$className</span><span class="keyword">);<br />
</span><span class="default">$className </span><span class="keyword">= </span><span class="string">'Foo'</span><span class="keyword">;<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$foo1 </span><span class="keyword">instanceof </span><span class="default">$className</span><span class="keyword">);<br />
use </span><span class="default">Bar2Foo2</span><span class="keyword">;<br />
</span><span class="default">$foo2 </span><span class="keyword">= new </span><span class="default">Foo2</span><span class="keyword">(); </span><span class="default">$className </span><span class="keyword">= </span><span class="string">'Bar2\Foo2'</span><span class="keyword">;<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$foo2 </span><span class="keyword">instanceof </span><span class="default">Bar2Foo2</span><span class="keyword">);<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$foo2 </span><span class="keyword">instanceof </span><span class="default">$className</span><span class="keyword">);<br />
</span><span class="default">$className </span><span class="keyword">= </span><span class="string">'Foo2'</span><span class="keyword">;<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$foo2 </span><span class="keyword">instanceof </span><span class="default">$className</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span>## stdout ##<br />
bool(true)<br />
bool(true)<br />
bool(false)<br />
bool(true)<br />
bool(true)<br />
bool(false)</span>
</code></div>
  </div>
 </div>
 <a name="103459"></a>
 <div class="note">
  <strong class='user'>jmullee at yahoo dot com</strong>
  <a href="#103459" class="date">14-Apr-2011 06:29</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
returns true for any ancestor class, not just immediate superclasses.<br />
<br />
php -r "class Mammal{}; class Primate extends Mammal {}; class Human extends Primate{}; \$h=new Human(); echo (\$h instanceof Mammal)?'Yes':'no';"<br />
<br />
Yes</span>
</code></div>
  </div>
 </div>
 <a name="103205"></a>
 <div class="note">
  <strong class='user'>Jennifer</strong>
  <a href="#103205" class="date">31-Mar-2011 06:40</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I was getting frustrated by instanceof not taking a string for its first argument so I wrote this function that takes strings or objects for both args and deals with both classes and interfaces.&nbsp; I hope it's useful to someone.<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="comment">/**<br />
&nbsp;* @desc: replacement for instanceof that accept strings or objects for both args<br />
&nbsp;* @param: Mixed $object- string or Object<br />
&nbsp;* @param: Mixed $class- string or Object<br />
&nbsp;* @return: Boolean<br />
&nbsp;*/<br />
</span><span class="keyword">function </span><span class="default">oneof</span><span class="keyword">(</span><span class="default">$object</span><span class="keyword">, </span><span class="default">$class</span><span class="keyword">){<br />
&nbsp;&nbsp;&nbsp; if(</span><span class="default">is_object</span><span class="keyword">(</span><span class="default">$object</span><span class="keyword">)) return </span><span class="default">$object </span><span class="keyword">instanceof </span><span class="default">$class</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; if(</span><span class="default">is_string</span><span class="keyword">(</span><span class="default">$object</span><span class="keyword">)){<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if(</span><span class="default">is_object</span><span class="keyword">(</span><span class="default">$class</span><span class="keyword">)) </span><span class="default">$class</span><span class="keyword">=</span><span class="default">get_class</span><span class="keyword">(</span><span class="default">$class</span><span class="keyword">);<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if(</span><span class="default">class_exists</span><span class="keyword">(</span><span class="default">$class</span><span class="keyword">)) return </span><span class="default">is_subclass_of</span><span class="keyword">(</span><span class="default">$object</span><span class="keyword">, </span><span class="default">$class</span><span class="keyword">) || </span><span class="default">$object</span><span class="keyword">==</span><span class="default">$class</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if(</span><span class="default">interface_exists</span><span class="keyword">(</span><span class="default">$class</span><span class="keyword">)) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$reflect </span><span class="keyword">= new </span><span class="default">ReflectionClass</span><span class="keyword">(</span><span class="default">$object</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return !</span><span class="default">$reflect</span><span class="keyword">-&gt;</span><span class="default">implementsInterface</span><span class="keyword">(</span><span class="default">$class</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">false</span><span class="keyword">;<br />
}<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="102988"></a>
 <div class="note">
  <strong class='user'>fbableus</strong>
  <a href="#102988" class="date">18-Mar-2011 06:05</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you want to test if a classname is an instance of a class, the instanceof operator won't work.<br />
<br />
<span class="default">&lt;?php<br />
$classname </span><span class="keyword">= </span><span class="string">'MyClass'</span><span class="keyword">;<br />
if( </span><span class="default">$classname </span><span class="keyword">instanceof </span><span class="default">MyParentClass</span><span class="keyword">) echo </span><span class="string">'Child of it'</span><span class="keyword">;<br />
else echo </span><span class="string">'Not child of it'</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
Will always output <br />
Not child of it<br />
<br />
You must use a ReflectionClass :<br />
<span class="default">&lt;?php<br />
$classname </span><span class="keyword">= </span><span class="string">'MyClass'</span><span class="keyword">;<br />
</span><span class="default">$myReflection </span><span class="keyword">= new </span><span class="default">ReflectionClass</span><span class="keyword">(</span><span class="default">$classname</span><span class="keyword">);<br />
if( </span><span class="default">$myReflection</span><span class="keyword">-&gt;</span><span class="default">isSubclassOf</span><span class="keyword">(</span><span class="string">'MyParentClass'</span><span class="keyword">)) echo&nbsp; </span><span class="string">'Child of it'</span><span class="keyword">;<br />
else echo </span><span class="string">'Not child of it'</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
Will output the good result.<br />
If you're testing an interface, use implementsInterface() instead of isSublassOf().</span>
</code></div>
  </div>
 </div>
 <a name="99384"></a>
 <div class="note">
  <strong class='user'>mauritsdajong at gmail dot com</strong>
  <a href="#99384" class="date">13-Aug-2010 03:38</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Sometimes you want to typehint objects INSIDE an array, but I think you can't.<br />
<br />
Instead, you can use this function to check the classes inside this array:<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">public </span><span class="default">checkObjectsArray</span><span class="keyword">(array </span><span class="default">$array</span><span class="keyword">, </span><span class="default">$classname</span><span class="keyword">, </span><span class="default">$strict </span><span class="keyword">= </span><span class="default">false</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; if (!</span><span class="default">$strict</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; foreach (</span><span class="default">$array </span><span class="keyword">as </span><span class="default">$element</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; if (!(</span><span class="default">$element </span><span class="keyword">instanceof </span><span class="default">$classname</span><span class="keyword">)) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">false</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; else {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; foreach (</span><span class="default">$array </span><span class="keyword">as </span><span class="default">$element</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; if (</span><span class="default">get_class</span><span class="keyword">(</span><span class="default">$element</span><span class="keyword">) != </span><span class="default">$classname</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">false</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">true</span><span class="keyword">;<br />
}<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="98926"></a>
 <div class="note">
  <strong class='user'>phil dot taylor at gmail dot com</strong>
  <a href="#98926" class="date">15-Jul-2010 04:13</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
It seems like instanceOf is using a string comparison. Longer class names take longer to check in conditional statements<br />
<br />
eg.<br />
<br />
if ($f instanceOf HelloWorldTestClass) <br />
<br />
is much slower than<br />
<br />
if ($f instanceOf HWT)</span>
</code></div>
  </div>
 </div>
 <a name="87053"></a>
 <div class="note">
  <strong class='user'>jtaal at eljakim dot nl</strong>
  <a href="#87053" class="date">17-Nov-2008 05:37</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
You can use "self" to reference to the current class:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">myclass </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">mymethod</span><span class="keyword">(</span><span class="default">$otherObject</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if (</span><span class="default">$otherObject </span><span class="keyword">instanceof </span><span class="default">self</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$otherObject</span><span class="keyword">-&gt;</span><span class="default">mymethod</span><span class="keyword">(</span><span class="default">null</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="string">'works!'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="default">$a </span><span class="keyword">= new </span><span class="default">myclass</span><span class="keyword">();<br />
print </span><span class="default">$a</span><span class="keyword">-&gt;</span><span class="default">mymethod</span><span class="keyword">(</span><span class="default">$a</span><span class="keyword">);<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="85239"></a>
 <div class="note">
  <strong class='user'>kevin dot benton at beatport dot com</strong>
  <a href="#85239" class="date">21-Aug-2008 09:31</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Example #5 could also be extended to include...<br />
<br />
var_dump($a instanceof MyInterface);<br />
<br />
The new result would be<br />
<br />
bool(true)<br />
<br />
So - instanceof is smart enough to know that a class that implements an interface is an instance of the interface, not just the class.&nbsp; I didn't see that point made clearly enough in the explanation at the top.</span>
</code></div>
  </div>
 </div>
 <a name="80495"></a>
 <div class="note">
  <strong class='user'>ejohnson82 at gmail dot com</strong>
  <a href="#80495" class="date">18-Jan-2008 01:59</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The PHP parser generates a parse error on either of the two lines that are commented out here.&nbsp; <br />
Apparently the 'instanceof' construct will take a string variable in the second spot, but it will NOT take a string... lame<br />
<br />
class Bar {}<br />
$b = new Bar;<br />
$b_class = "Bar";<br />
var_export($b instanceof Bar); // this is ok<br />
var_export($b instanceof $b_class); // this is ok<br />
//var_export($f instanceof "Bar"); // this is syntactically illegal<br />
//var_export($f instanceof 'Bar'); // this is syntactically illegal</span>
</code></div>
  </div>
 </div>
 <a name="76582"></a>
 <div class="note">
  <strong class='user'>julien plee using g mail dot com</strong>
  <a href="#76582" class="date">20-Jul-2007 06:56</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Response to vinyanov at poczta dot onet dot pl:<br />
<br />
You mentionned "the instanceof operator will not accept a string as its first operand". However, this behavior is absolutely right and therefore, you're misleading the meaning of an instance.<br />
<br />
<span class="default">&lt;?php </span><span class="string">'ClassA' </span><span class="keyword">instanceof </span><span class="string">'ClassB'</span><span class="keyword">; </span><span class="default">?&gt;</span> means "the class named ClassA is an instance of the class named ClassB". This is a nonsense sentence because when you instanciate a class, you ALWAYS obtain an object. Consequently, you only can ask if an object is an instance of a class.<br />
<br />
I believe asking if "a ClassA belongs to a ClassB" (or "a ClassA is a class of (type) ClassB") or even "a ClassA is (also) a ClassB" is more appropriate. But the first is not implemented and the second only works with objects, just like the instanceof operator.<br />
<br />
Plus, I just have tested your code and it does absolutely NOT do the same as instanceof (extended to classes)! I can't advise anyone to reuse it. The use of <span class="default">&lt;?php is_instance_of </span><span class="keyword">(</span><span class="default">$instanceOfA</span><span class="keyword">, </span><span class="string">'ClassB'</span><span class="keyword">); </span><span class="default">?&gt;</span> raises a warning "include_once(Object id #1.php) …" when using __autoload (trying to look for $instanceOfA as if it was a class name).<br />
<br />
Finally, here is a fast (to me) sample function code to verify if an object or class:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">kind_of </span><span class="keyword">(&amp;</span><span class="default">$object_or_class</span><span class="keyword">, </span><span class="default">$class</span><span class="keyword">)<br />
{<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">is_object </span><span class="keyword">(</span><span class="default">$object_or_class</span><span class="keyword">) ? <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$object_or_class </span><span class="keyword">instanceof </span><span class="default">$class<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">: (</span><span class="default">is_subclass_of </span><span class="keyword">(</span><span class="default">$object_or_class $class</span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; || </span><span class="default">strtolower </span><span class="keyword">(</span><span class="default">$object_or_class</span><span class="keyword">) == </span><span class="default">strtolower </span><span class="keyword">(</span><span class="default">$class</span><span class="keyword">));<br />
} <br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="76352"></a>
 <div class="note">
  <strong class='user'>jphaas at gmail dot com</strong>
  <a href="#76352" class="date">11-Jul-2007 10:50</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Posting this so the word typeof appears on this page, so that this page will show up when you google 'php typeof'.&nbsp; ...yeah, former Java user.</span>
</code></div>
  </div>
 </div>
 <a name="75871"></a>
 <div class="note">
  <strong class='user'>vinyanov at poczta dot onet dot pl</strong>
  <a href="#75871" class="date">19-Jun-2007 03:57</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Unfortunately the instanceof operator will not accept a string as its first operand. So I wrote this function. It does exactly the same (ie, successively checks identicalness, inheritance and implementation). Just on strings.<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">function </span><span class="default">is_instance_of</span><span class="keyword">(</span><span class="default">$sub</span><span class="keyword">, </span><span class="default">$super</span><span class="keyword">)<br />
{<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$sub </span><span class="keyword">= (string)</span><span class="default">$sub</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$super </span><span class="keyword">= </span><span class="default">is_object</span><span class="keyword">(</span><span class="default">$super</span><span class="keyword">) ? </span><span class="default">get_class</span><span class="keyword">(</span><span class="default">$super</span><span class="keyword">) : (string)</span><span class="default">$super</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; switch(</span><span class="default">true</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; case </span><span class="default">$sub </span><span class="keyword">=== </span><span class="default">$super</span><span class="keyword">; </span><span class="comment">// well ... conformity<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">case </span><span class="default">is_subclass_of</span><span class="keyword">(</span><span class="default">$sub</span><span class="keyword">, </span><span class="default">$super</span><span class="keyword">):<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; case </span><span class="default">in_array</span><span class="keyword">(</span><span class="default">$super</span><span class="keyword">, </span><span class="default">class_implements</span><span class="keyword">(</span><span class="default">$sub</span><span class="keyword">)):<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">true</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; default:<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">false</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="comment">// testing<br />
<br />
</span><span class="keyword">interface </span><span class="default">X </span><span class="keyword">{}<br />
class </span><span class="default">A </span><span class="keyword">{}<br />
class </span><span class="default">B </span><span class="keyword">extends </span><span class="default">A </span><span class="keyword">{}<br />
class </span><span class="default">C </span><span class="keyword">extends </span><span class="default">B </span><span class="keyword">{}<br />
class </span><span class="default">D </span><span class="keyword">implements </span><span class="default">X </span><span class="keyword">{}<br />
<br />
</span><span class="default">$i </span><span class="keyword">= </span><span class="string">'is_instance_of'</span><span class="keyword">;<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$i</span><span class="keyword">(</span><span class="string">'A'</span><span class="keyword">, </span><span class="string">'A'</span><span class="keyword">), </span><span class="default">$i</span><span class="keyword">(</span><span class="string">'B'</span><span class="keyword">, </span><span class="string">'A'</span><span class="keyword">), </span><span class="default">$i</span><span class="keyword">(</span><span class="string">'C'</span><span class="keyword">, </span><span class="string">'A'</span><span class="keyword">), </span><span class="default">$i</span><span class="keyword">(</span><span class="string">'D'</span><span class="keyword">, </span><span class="string">'X'</span><span class="keyword">));<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="73853"></a>
 <div class="note">
  <strong class='user'>jeanyves dot terrien at orange-ftgroup dot com</strong>
  <a href="#73853" class="date">13-Mar-2007 12:34</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Cross version function even if you are working in php4<br />
(instanceof is an undefined operator for php4)<br />
<br />
&nbsp;&nbsp; function isMemberOf($classename) {<br />
&nbsp;&nbsp; &nbsp;&nbsp; $ver = floor(phpversion());<br />
&nbsp;&nbsp; &nbsp;&nbsp; if($ver &gt; 4) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; $instanceof = create_function ('$obj,$classname','return $obj instanceof $classname;');<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; return $instanceof($this,$classname);<br />
&nbsp;&nbsp; &nbsp;&nbsp; } else {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; // Php4 uses lowercase for classname.<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; return is_a($this, strtolower($classname));<br />
&nbsp;&nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; } // end function isMemberOf</span>
</code></div>
  </div>
 </div>
 <a name="73609"></a>
 <div class="note">
  <strong class='user'>soletan at toxa dot de</strong>
  <a href="#73609" class="date">03-Mar-2007 04:04</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Please note: != is a separate operator with separate semantics. Thinking about language grammar it's kind of ridicilous to negate an operator. Of course, it's possible to negate the result of a function (like is_a()), since it isn't negating the function itself or its semantics.<br />
<br />
instanceof is a binary operator, and so used in binary terms like this<br />
<br />
terma instanceof termb<br />
<br />
while ! (negation) is a unary operator and so may be applied to a single term like this<br />
<br />
!term<br />
<br />
And a term never consists of an operator, only! There is no such construct in any language (please correct me!). However, instanceof doesn't finally support nested terms in every operand position ("terma" or "termb" above) as negation does:<br />
<br />
!!!!!!!!!!!!!!term == term<br />
<br />
So back again, did you ever write<br />
<br />
a !!!!!!!!!!!!= b<br />
<br />
to test equivalence?</span>
</code></div>
  </div>
 </div>
 <a name="71562"></a>
 <div class="note">
  <strong class='user'>mikael dot knutsson at gmail dot com</strong>
  <a href="#71562" class="date">05-Dec-2006 08:34</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I can confirm what thisbizness at gmail dot com said just below in PHP 5.2, furthermore, people looking to use this as a "if $a is not instance of A" for error throwing purposes or other, just type it like this: <br />
<span class="default">&lt;?php<br />
</span><span class="keyword">if( !</span><span class="default">$a </span><span class="keyword">instanceof </span><span class="default">A </span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; throw new </span><span class="default">Exception</span><span class="keyword">( </span><span class="string">'$a is not instance of A.' </span><span class="keyword">);<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
This also works if $a is not an object, or not even set (you will get an E_NOTICE if it isn't set though).<br />
A note worth making is that if you are unsure of if class A is present when making this comparison, and you don't want to trigger the __autoload() magic method, scroll down for examples of how to get around this.<br />
<br />
I was unsure about it at first since most other operators have their own negative (like !=) or they are/can be used as function calls (like !is_a()) but it is this simple. Hope it helps someone.<br />
<br />
Until again!</span>
</code></div>
  </div>
 </div>
 <a name="50101"></a>
 <div class="note">
  <strong class='user'>archanglmr at yahoo dot com</strong>
  <a href="#50101" class="date">17-Feb-2005 06:37</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Negated instanceof doesn't seem to be documented. When I read instanceof I think of it as a compairson operator (which I suppose it's not).<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">A </span><span class="keyword">{}<br />
class </span><span class="default">X </span><span class="keyword">{}<br />
<br />
</span><span class="comment">//parse error from !<br />
</span><span class="keyword">if (new </span><span class="default">X </span><span class="keyword">!instanceof </span><span class="default">A</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; throw new </span><span class="default">Exception</span><span class="keyword">(</span><span class="string">'X is not an A'</span><span class="keyword">);<br />
}<br />
</span><span class="comment">//proper way to negate instanceof ?<br />
</span><span class="keyword">if (!(new </span><span class="default">X </span><span class="keyword">instanceof </span><span class="default">A</span><span class="keyword">)) {<br />
&nbsp;&nbsp;&nbsp; throw new </span><span class="default">Exception</span><span class="keyword">(</span><span class="string">'X is not an A'</span><span class="keyword">);<br />
}<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="48311"></a>
 <div class="note">
  <strong class='user'>d dot schneider at 24you dot de</strong>
  <a href="#48311" class="date">18-Dec-2004 12:42</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
use this for cross-version development...<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">function </span><span class="default">is_instance_of</span><span class="keyword">(</span><span class="default">$IIO_INSTANCE</span><span class="keyword">, </span><span class="default">$IIO_CLASS</span><span class="keyword">){<br />
&nbsp;&nbsp;&nbsp; if(</span><span class="default">floor</span><span class="keyword">(</span><span class="default">phpversion</span><span class="keyword">()) &gt; </span><span class="default">4</span><span class="keyword">){<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if(</span><span class="default">$IIO_INSTANCE </span><span class="keyword">instanceof </span><span class="default">$IIO_CLASS</span><span class="keyword">){<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">true</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; else{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">false</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; elseif(</span><span class="default">floor</span><span class="keyword">(</span><span class="default">phpversion</span><span class="keyword">()) &gt; </span><span class="default">3</span><span class="keyword">){<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">is_a</span><span class="keyword">(</span><span class="default">$IIO_INSTANCE</span><span class="keyword">, </span><span class="default">$IIO_CLASS</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; else{<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">false</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=language.operators.type&amp;redirect=http://www.php.net/manual/en/language.operators.type.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.operators.type&amp;redirect=http://www.php.net/manual/en/language.operators.type.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/language.operators.type.php">show source</a> |
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