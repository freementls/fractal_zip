<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Object Iteration - Manual</title>
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
 <link rel="index" href="language.oop5.php" />
 <link rel="prev" href="language.oop5.overloading.php" />
 <link rel="next" href="language.oop5.magic.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/oop5.iterations" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/language.oop5.iterations.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/language.oop5.iterations.php" />
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
 <li class="header up"><a href="language.oop5.php">Classes and Objects</a></li>
 <li><a href="oop5.intro.php">Introduction</a></li>
 <li><a href="language.oop5.basic.php">The Basics</a></li>
 <li><a href="language.oop5.properties.php">Properties</a></li>
 <li><a href="language.oop5.constants.php">Class Constants</a></li>
 <li><a href="language.oop5.autoload.php">Autoloading Classes</a></li>
 <li><a href="language.oop5.decon.php">Constructors and Destructors</a></li>
 <li><a href="language.oop5.visibility.php">Visibility</a></li>
 <li><a href="language.oop5.inheritance.php">Object Inheritance</a></li>
 <li><a href="language.oop5.paamayim-nekudotayim.php">Scope Resolution Operator (::)</a></li>
 <li><a href="language.oop5.static.php">Static Keyword</a></li>
 <li><a href="language.oop5.abstract.php">Class Abstraction</a></li>
 <li><a href="language.oop5.interfaces.php">Object Interfaces</a></li>
 <li><a href="language.oop5.traits.php">Traits</a></li>
 <li><a href="language.oop5.overloading.php">Overloading</a></li>
 <li class="active"><a href="language.oop5.iterations.php">Object Iteration</a></li>
 <li><a href="language.oop5.magic.php">Magic Methods</a></li>
 <li><a href="language.oop5.final.php">Final Keyword</a></li>
 <li><a href="language.oop5.cloning.php">Object Cloning</a></li>
 <li><a href="language.oop5.object-comparison.php">Comparing Objects</a></li>
 <li><a href="language.oop5.typehinting.php">Type Hinting</a></li>
 <li><a href="language.oop5.late-static-bindings.php">Late Static Bindings</a></li>
 <li><a href="language.oop5.references.php">Objects and references</a></li>
 <li><a href="language.oop5.serialization.php">Object Serialization</a></li>
 <li><a href="language.oop5.changelog.php">OOP Changelog</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="language.oop5.magic.php">Magic Methods<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.oop5.overloading.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Overloading</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.oop5.iterations.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/language.oop5.iterations.php">Brazilian Portuguese</option>
    <option value="zh/language.oop5.iterations.php">Chinese (Simplified)</option>
    <option value="fr/language.oop5.iterations.php">French</option>
    <option value="de/language.oop5.iterations.php">German</option>
    <option value="ja/language.oop5.iterations.php">Japanese</option>
    <option value="pl/language.oop5.iterations.php">Polish</option>
    <option value="ro/language.oop5.iterations.php">Romanian</option>
    <option value="ru/language.oop5.iterations.php">Russian</option>
    <option value="fa/language.oop5.iterations.php">Persian</option>
    <option value="es/language.oop5.iterations.php">Spanish</option>
    <option value="tr/language.oop5.iterations.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="language.oop5.iterations" class="sect1">
  <h2 class="title">Object Iteration</h2>
  <p class="para">

   PHP 5 provides a way for objects to be defined so it is possible to iterate
   through a list of items, with, for example a <a href="control-structures.foreach.php" class="link">foreach</a> statement. By default,
   all <a href="language.oop5.visibility.php" class="link">visible</a> properties will be used
   for the iteration.

  </p>

  <div class="example" id="example-209">
   <p><strong>Example #1 Simple Object Iteration</strong></p>
   <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">class&nbsp;</span><span style="color: #0000BB">MyClass<br /></span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;</span><span style="color: #0000BB">$var1&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'value&nbsp;1'</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;</span><span style="color: #0000BB">$var2&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'value&nbsp;2'</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;</span><span style="color: #0000BB">$var3&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'value&nbsp;3'</span><span style="color: #007700">;<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;protected&nbsp;</span><span style="color: #0000BB">$protected&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'protected&nbsp;var'</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;private&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$private&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'private&nbsp;var'</span><span style="color: #007700">;<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;function&nbsp;</span><span style="color: #0000BB">iterateVisible</span><span style="color: #007700">()&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"MyClass::iterateVisible:\n"</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;foreach(</span><span style="color: #0000BB">$this&nbsp;</span><span style="color: #007700">as&nbsp;</span><span style="color: #0000BB">$key&nbsp;</span><span style="color: #007700">=&gt;&nbsp;</span><span style="color: #0000BB">$value</span><span style="color: #007700">)&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;print&nbsp;</span><span style="color: #DD0000">"</span><span style="color: #0000BB">$key</span><span style="color: #DD0000">&nbsp;=&gt;&nbsp;</span><span style="color: #0000BB">$value</span><span style="color: #DD0000">\n"</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br /><br /></span><span style="color: #0000BB">$class&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">MyClass</span><span style="color: #007700">();<br /><br />foreach(</span><span style="color: #0000BB">$class&nbsp;</span><span style="color: #007700">as&nbsp;</span><span style="color: #0000BB">$key&nbsp;</span><span style="color: #007700">=&gt;&nbsp;</span><span style="color: #0000BB">$value</span><span style="color: #007700">)&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;print&nbsp;</span><span style="color: #DD0000">"</span><span style="color: #0000BB">$key</span><span style="color: #DD0000">&nbsp;=&gt;&nbsp;</span><span style="color: #0000BB">$value</span><span style="color: #DD0000">\n"</span><span style="color: #007700">;<br />}<br />echo&nbsp;</span><span style="color: #DD0000">"\n"</span><span style="color: #007700">;<br /><br /><br /></span><span style="color: #0000BB">$class</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">iterateVisible</span><span style="color: #007700">();<br /><br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
   </div>

   <div class="example-contents"><p>The above example will output:</p></div> 
   <div class="example-contents screen">
<div class="cdata"><pre>
var1 =&gt; value 1
var2 =&gt; value 2
var3 =&gt; value 3

MyClass::iterateVisible:
var1 =&gt; value 1
var2 =&gt; value 2
var3 =&gt; value 3
protected =&gt; protected var
private =&gt; private var
</pre></div>
   </div>

  </div>

 <p class="para">
  As the output shows, the <a href="control-structures.foreach.php" class="link">foreach</a> iterated through all of the
  <a href="language.oop5.visibility.php" class="link">visible</a> properties that could be
  accessed.
 </p>
 <p class="para">
  To take it a step further, the <span class="interfacename"><a href="class.iterator.php" class="interfacename">Iterator</a></span>
  <a href="language.oop5.interfaces.php" class="link">interface</a> may be implemented.
  This allows the object to dictate how it will be iterated and what values will
  be available on each iteration.
 </p>

  <div class="example" id="example-210">
   <p><strong>Example #2 Object Iteration implementing Iterator</strong></p>
   <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">class&nbsp;</span><span style="color: #0000BB">MyIterator&nbsp;</span><span style="color: #007700">implements&nbsp;</span><span style="color: #0000BB">Iterator<br /></span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;private&nbsp;</span><span style="color: #0000BB">$var&nbsp;</span><span style="color: #007700">=&nbsp;array();<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">__construct</span><span style="color: #007700">(</span><span style="color: #0000BB">$array</span><span style="color: #007700">)<br />&nbsp;&nbsp;&nbsp;&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;if&nbsp;(</span><span style="color: #0000BB">is_array</span><span style="color: #007700">(</span><span style="color: #0000BB">$array</span><span style="color: #007700">))&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">var&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">$array</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">rewind</span><span style="color: #007700">()<br />&nbsp;&nbsp;&nbsp;&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"rewinding\n"</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">reset</span><span style="color: #007700">(</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">var</span><span style="color: #007700">);<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />&nbsp;&nbsp;<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">current</span><span style="color: #007700">()<br />&nbsp;&nbsp;&nbsp;&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$var&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">current</span><span style="color: #007700">(</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">var</span><span style="color: #007700">);<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"current:&nbsp;</span><span style="color: #0000BB">$var</span><span style="color: #DD0000">\n"</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;return&nbsp;</span><span style="color: #0000BB">$var</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />&nbsp;&nbsp;<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">key</span><span style="color: #007700">()&nbsp;<br />&nbsp;&nbsp;&nbsp;&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$var&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">key</span><span style="color: #007700">(</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">var</span><span style="color: #007700">);<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"key:&nbsp;</span><span style="color: #0000BB">$var</span><span style="color: #DD0000">\n"</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;return&nbsp;</span><span style="color: #0000BB">$var</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />&nbsp;&nbsp;<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">next</span><span style="color: #007700">()&nbsp;<br />&nbsp;&nbsp;&nbsp;&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$var&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">next</span><span style="color: #007700">(</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">var</span><span style="color: #007700">);<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"next:&nbsp;</span><span style="color: #0000BB">$var</span><span style="color: #DD0000">\n"</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;return&nbsp;</span><span style="color: #0000BB">$var</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />&nbsp;&nbsp;<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">valid</span><span style="color: #007700">()<br />&nbsp;&nbsp;&nbsp;&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$key&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">key</span><span style="color: #007700">(</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">var</span><span style="color: #007700">);<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$var&nbsp;</span><span style="color: #007700">=&nbsp;(</span><span style="color: #0000BB">$key&nbsp;</span><span style="color: #007700">!==&nbsp;</span><span style="color: #0000BB">NULL&nbsp;</span><span style="color: #007700">&amp;&amp;&nbsp;</span><span style="color: #0000BB">$key&nbsp;</span><span style="color: #007700">!==&nbsp;</span><span style="color: #0000BB">FALSE</span><span style="color: #007700">);<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"valid:&nbsp;</span><span style="color: #0000BB">$var</span><span style="color: #DD0000">\n"</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;return&nbsp;</span><span style="color: #0000BB">$var</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br /><br />}<br /><br /></span><span style="color: #0000BB">$values&nbsp;</span><span style="color: #007700">=&nbsp;array(</span><span style="color: #0000BB">1</span><span style="color: #007700">,</span><span style="color: #0000BB">2</span><span style="color: #007700">,</span><span style="color: #0000BB">3</span><span style="color: #007700">);<br /></span><span style="color: #0000BB">$it&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">MyIterator</span><span style="color: #007700">(</span><span style="color: #0000BB">$values</span><span style="color: #007700">);<br /><br />foreach&nbsp;(</span><span style="color: #0000BB">$it&nbsp;</span><span style="color: #007700">as&nbsp;</span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">=&gt;&nbsp;</span><span style="color: #0000BB">$b</span><span style="color: #007700">)&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;print&nbsp;</span><span style="color: #DD0000">"</span><span style="color: #0000BB">$a</span><span style="color: #DD0000">:&nbsp;</span><span style="color: #0000BB">$b</span><span style="color: #DD0000">\n"</span><span style="color: #007700">;<br />}<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
   </div>

   <div class="example-contents"><p>The above example will output:</p></div>
   <div class="example-contents screen">
<div class="cdata"><pre>
rewinding
valid: 1
current: 1
key: 0
0: 1
next: 2
valid: 1
current: 2
key: 1
1: 2
next: 3
valid: 1
current: 3
key: 2
2: 3
next:
valid: 
</pre></div>
   </div>

  </div>

  <p class="para">
   The <span class="interfacename"><a href="class.iteratoraggregate.php" class="interfacename">IteratorAggregate</a></span>
   <a href="language.oop5.interfaces.php" class="link">interface</a>
   can be used as an alternative to implementing all of the
   <span class="interfacename"><a href="class.iterator.php" class="interfacename">Iterator</a></span> methods.
   <span class="interfacename"><a href="class.iteratoraggregate.php" class="interfacename">IteratorAggregate</a></span> only requires the
   implementation of a single method,
    <span class="methodname"><a href="iteratoraggregate.getiterator.php" class="methodname">IteratorAggregate::getIterator()</a></span>, which should return
   an instance of a class implementing <span class="interfacename"><a href="class.iterator.php" class="interfacename">Iterator</a></span>.
  </p>

  <div class="example" id="example-211">
   <p><strong>Example #3 Object Iteration implementing IteratorAggregate</strong></p>
   <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">class&nbsp;</span><span style="color: #0000BB">MyCollection&nbsp;</span><span style="color: #007700">implements&nbsp;</span><span style="color: #0000BB">IteratorAggregate<br /></span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;private&nbsp;</span><span style="color: #0000BB">$items&nbsp;</span><span style="color: #007700">=&nbsp;array();<br />&nbsp;&nbsp;&nbsp;&nbsp;private&nbsp;</span><span style="color: #0000BB">$count&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">0</span><span style="color: #007700">;<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;Required&nbsp;definition&nbsp;of&nbsp;interface&nbsp;IteratorAggregate<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">public&nbsp;function&nbsp;</span><span style="color: #0000BB">getIterator</span><span style="color: #007700">()&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;return&nbsp;new&nbsp;</span><span style="color: #0000BB">MyIterator</span><span style="color: #007700">(</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">items</span><span style="color: #007700">);<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">add</span><span style="color: #007700">(</span><span style="color: #0000BB">$value</span><span style="color: #007700">)&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">items</span><span style="color: #007700">[</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">count</span><span style="color: #007700">++]&nbsp;=&nbsp;</span><span style="color: #0000BB">$value</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br /><br /></span><span style="color: #0000BB">$coll&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">MyCollection</span><span style="color: #007700">();<br /></span><span style="color: #0000BB">$coll</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">add</span><span style="color: #007700">(</span><span style="color: #DD0000">'value&nbsp;1'</span><span style="color: #007700">);<br /></span><span style="color: #0000BB">$coll</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">add</span><span style="color: #007700">(</span><span style="color: #DD0000">'value&nbsp;2'</span><span style="color: #007700">);<br /></span><span style="color: #0000BB">$coll</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">add</span><span style="color: #007700">(</span><span style="color: #DD0000">'value&nbsp;3'</span><span style="color: #007700">);<br /><br />foreach&nbsp;(</span><span style="color: #0000BB">$coll&nbsp;</span><span style="color: #007700">as&nbsp;</span><span style="color: #0000BB">$key&nbsp;</span><span style="color: #007700">=&gt;&nbsp;</span><span style="color: #0000BB">$val</span><span style="color: #007700">)&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"key/value:&nbsp;[</span><span style="color: #0000BB">$key</span><span style="color: #DD0000">&nbsp;-&gt;&nbsp;</span><span style="color: #0000BB">$val</span><span style="color: #DD0000">]\n\n"</span><span style="color: #007700">;<br />}<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
   </div>

   <div class="example-contents"><p>The above example will output:</p></div>  
   <div class="example-contents screen">
<div class="cdata"><pre>
rewinding
current: value 1
valid: 1
current: value 1
key: 0
key/value: [0 -&gt; value 1]

next: value 2
current: value 2
valid: 1
current: value 2
key: 1
key/value: [1 -&gt; value 2]

next: value 3
current: value 3
valid: 1
current: value 3
key: 2
key/value: [2 -&gt; value 3]

next:
current:
valid:
</pre></div>
   </div>

  </div>

  <blockquote class="note"><p><strong class="note">Note</strong>: 
   <p class="para">
    For more examples of iterators, see the
    <a href="spl.iterators.php" class="link">SPL Extension</a>.
   </p>
  </p></blockquote> 

 </div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="language.oop5.magic.php">Magic Methods<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.oop5.overloading.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Overloading</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.oop5.iterations.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=language.oop5.iterations&amp;redirect=http://www.php.net/manual/en/language.oop5.iterations.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.oop5.iterations&amp;redirect=http://www.php.net/manual/en/language.oop5.iterations.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Object Iteration</strong>
 </div><div id="allnotes">
 <a name="109267"></a>
 <div class="note">
  <strong class='user'>php dot net dot nsp at cvogt dot org</strong>
  <a href="#109267" class="date">01-Jul-2012 10:25</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
there is still an open bug about using current() etc. with iterators<br />
@w{JBVFFY7T}bug.php?id=49369</span>
</code></div>
  </div>
 </div>
 <a name="108657"></a>
 <div class="note">
  <strong class='user'>jille at hexon dot cx</strong>
  <a href="#108657" class="date">15-May-2012 12:29</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Please note that if you implement your iterator this way instead of with an IteratorAggregate you can not nest foreach-loops. This is because when the inner-loop is done the cursor is beyond the last element, then the outer-loop asks for the next element and finds the cursor beyond the last element as the innter-loop left it there.<br />
<br />
<span class="default">&lt;?php<br />
&nbsp;</span><span class="comment">// Wont work!<br />
</span><span class="keyword">foreach(</span><span class="default">$collection </span><span class="keyword">as </span><span class="default">$a</span><span class="keyword">) {<br />
&nbsp; foreach(</span><span class="default">$collection </span><span class="keyword">as </span><span class="default">$b</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$a</span><span class="keyword">-&gt;</span><span class="default">someFunc</span><span class="keyword">(</span><span class="default">$b</span><span class="keyword">));<br />
&nbsp; }<br />
}<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="96073"></a>
 <div class="note">
  <strong class='user'>uramihsayibok, gmail, com</strong>
  <a href="#96073" class="date">06-Feb-2010 08:23</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
With method names like "current", "key", and "next", one might think that you can use the corresponding functions on your objects. For instance,<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">class </span><span class="default">MyIterator </span><span class="keyword">implements </span><span class="default">Iterator </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">// as defined in example #2<br />
</span><span class="keyword">}<br />
<br />
</span><span class="default">$iterator </span><span class="keyword">= new </span><span class="default">MyIterator</span><span class="keyword">(array(</span><span class="default">1</span><span class="keyword">, </span><span class="default">2</span><span class="keyword">, </span><span class="default">3</span><span class="keyword">));<br />
</span><span class="default">reset</span><span class="keyword">(</span><span class="default">$iterator</span><span class="keyword">); </span><span class="comment">// calls $iterator-&gt;rewind?<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">current</span><span class="keyword">(</span><span class="default">$iterator</span><span class="keyword">)); </span><span class="comment">// calls $iterator-&gt;current()?<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
This doesn't work. It has been reported as a bug (and honestly it *would* make sense) but was rejected - objects can be treated as arrays... and that's what happens.</span>
</code></div>
  </div>
 </div>
 <a name="91921"></a>
 <div class="note">
  <strong class='user'>Peter &amp;#39;the Pete&amp;#39; de Pijd</strong>
  <a href="#91921" class="date">01-Jul-2009 10:12</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
This is what seems to work for checking isFirst() and isLast() on a class implementing the Iterator interface - without destroying the internal array pointer:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="comment">// Let $this-&gt;_elements be your internal array with<br />
// array("one", "two", "three")<br />
</span><span class="keyword">public function </span><span class="default">isFirst</span><span class="keyword">()<br />
{<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$hasPrevious </span><span class="keyword">= </span><span class="default">prev</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">_elements</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">// now undo<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">if (</span><span class="default">$hasPrevious</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">next</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">_elements</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; } else {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">reset</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">_elements</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; return !</span><span class="default">$hasPrevious</span><span class="keyword">;<br />
}<br />
<br />
public function </span><span class="default">isLast</span><span class="keyword">()<br />
{<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$hasNext </span><span class="keyword">= </span><span class="default">next</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">_elements</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">// now undo<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">if (</span><span class="default">$hasNext</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">prev</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">_elements</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; } else {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">end</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">_elements</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; return !</span><span class="default">$hasNext</span><span class="keyword">;<br />
}<br />
<br />
</span><span class="comment">// usage<br />
</span><span class="keyword">foreach (</span><span class="default">$myInterator </span><span class="keyword">as </span><span class="default">$value</span><span class="keyword">) {<br />
&nbsp; echo </span><span class="default">$value</span><span class="keyword">;<br />
&nbsp; if (</span><span class="default">$myInterator</span><span class="keyword">-&gt;</span><span class="default">isFirst</span><span class="keyword">()) {<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="string">" (first)"</span><span class="keyword">;<br />
&nbsp; }<br />
&nbsp; if (</span><span class="default">$myInterator</span><span class="keyword">-&gt;</span><span class="default">isLast</span><span class="keyword">()) {<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="string">" (last)"</span><span class="keyword">;<br />
&nbsp; }<br />
&nbsp; echo </span><span class="string">" - value is still the same: "</span><span class="keyword">, </span><span class="default">$value</span><span class="keyword">, </span><span class="string">"&lt;br /&gt;"</span><span class="keyword">;<br />
}<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
output:<br />
one (first) - value is still the same: one<br />
two - value is still the same: two<br />
three (last) - value is still the same: three<br />
<br />
This can be helpfull for designing CSS elements where the first or last element must have a different padding/margin than the others.</span>
</code></div>
  </div>
 </div>
 <a name="86371"></a>
 <div class="note">
  <strong class='user'>hlegius at gmail dot com</strong>
  <a href="#86371" class="date">15-Oct-2008 05:06</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Iterator interface usign key() next() rewind() is MORE slow than extends ArrayIterator with ArrayIterator::next(), ArrayIterator::rewind(), etc.,</span>
</code></div>
  </div>
 </div>
 <a name="81508"></a>
 <div class="note">
  <strong class='user'>wavetrex A(nospam)T gmail DOT com</strong>
  <a href="#81508" class="date">01-Mar-2008 02:50</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
By reading the posts below I wondered if it really is impossible to make an ArrayAccess implementation really behave like a true array ( by being multi level )<br />
<br />
Seems like it's not impossible. Not very preety but usable<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">class </span><span class="default">ArrayAccessImpl </span><span class="keyword">implements </span><span class="default">ArrayAccess </span><span class="keyword">{<br />
<br />
&nbsp; private </span><span class="default">$data </span><span class="keyword">= array();<br />
<br />
&nbsp; public function </span><span class="default">offsetUnset</span><span class="keyword">(</span><span class="default">$index</span><span class="keyword">) {}<br />
<br />
&nbsp; public function </span><span class="default">offsetSet</span><span class="keyword">(</span><span class="default">$index</span><span class="keyword">, </span><span class="default">$value</span><span class="keyword">) {<br />
</span><span class="comment">//&nbsp; &nbsp; echo ("SET: ".$index."&lt;br&gt;");<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">if(isset(</span><span class="default">$data</span><span class="keyword">[</span><span class="default">$index</span><span class="keyword">])) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; unset(</span><span class="default">$data</span><span class="keyword">[</span><span class="default">$index</span><span class="keyword">]);<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$u </span><span class="keyword">= &amp;</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">data</span><span class="keyword">[</span><span class="default">$index</span><span class="keyword">];<br />
&nbsp;&nbsp;&nbsp; if(</span><span class="default">is_array</span><span class="keyword">(</span><span class="default">$value</span><span class="keyword">)) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$u </span><span class="keyword">= new </span><span class="default">ArrayAccessImpl</span><span class="keyword">();<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; foreach(</span><span class="default">$value </span><span class="keyword">as </span><span class="default">$idx</span><span class="keyword">=&gt;</span><span class="default">$e</span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$u</span><span class="keyword">[</span><span class="default">$idx</span><span class="keyword">]=</span><span class="default">$e</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; } else<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$u</span><span class="keyword">=</span><span class="default">$value</span><span class="keyword">;<br />
&nbsp; }<br />
<br />
&nbsp; public function </span><span class="default">offsetGet</span><span class="keyword">(</span><span class="default">$index</span><span class="keyword">) {<br />
</span><span class="comment">//&nbsp; &nbsp; echo ("GET: ".$index."&lt;br&gt;");<br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">if(!isset(</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">data</span><span class="keyword">[</span><span class="default">$index</span><span class="keyword">]))<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">data</span><span class="keyword">[</span><span class="default">$index</span><span class="keyword">]=new </span><span class="default">ArrayAccessImpl</span><span class="keyword">();<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">data</span><span class="keyword">[</span><span class="default">$index</span><span class="keyword">];<br />
&nbsp; }<br />
<br />
&nbsp; public function </span><span class="default">offsetExists</span><span class="keyword">(</span><span class="default">$index</span><span class="keyword">) {<br />
</span><span class="comment">//&nbsp; &nbsp; echo ("EXISTS: ".$index."&lt;br&gt;");<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">if(isset(</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">data</span><span class="keyword">[</span><span class="default">$index</span><span class="keyword">])) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if(</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">data</span><span class="keyword">[</span><span class="default">$index</span><span class="keyword">] instanceof </span><span class="default">ArrayAccessImpl</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; if(</span><span class="default">count</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">data</span><span class="keyword">[</span><span class="default">$index</span><span class="keyword">]-&gt;</span><span class="default">data</span><span class="keyword">)&gt;</span><span class="default">0</span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">true</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; else<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">false</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; } else<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">true</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; } else<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">false</span><span class="keyword">;<br />
&nbsp; }<br />
<br />
}<br />
<br />
echo </span><span class="string">"ArrayAccess implementation that behaves like a multi-level array&lt;hr /&gt;"</span><span class="keyword">;<br />
<br />
</span><span class="default">$data </span><span class="keyword">= new </span><span class="default">ArrayAccessImpl</span><span class="keyword">();<br />
<br />
</span><span class="default">$data</span><span class="keyword">[</span><span class="string">'string'</span><span class="keyword">]=</span><span class="string">"Just a simple string"</span><span class="keyword">;<br />
</span><span class="default">$data</span><span class="keyword">[</span><span class="string">'number'</span><span class="keyword">]=</span><span class="default">33</span><span class="keyword">;<br />
</span><span class="default">$data</span><span class="keyword">[</span><span class="string">'array'</span><span class="keyword">][</span><span class="string">'another_string'</span><span class="keyword">]=</span><span class="string">"Alpha"</span><span class="keyword">;<br />
</span><span class="default">$data</span><span class="keyword">[</span><span class="string">'array'</span><span class="keyword">][</span><span class="string">'some_object'</span><span class="keyword">]=new </span><span class="default">stdClass</span><span class="keyword">();<br />
</span><span class="default">$data</span><span class="keyword">[</span><span class="string">'array'</span><span class="keyword">][</span><span class="string">'another_array'</span><span class="keyword">][</span><span class="string">'x'</span><span class="keyword">][</span><span class="string">'y'</span><span class="keyword">]=</span><span class="string">"LOL @ Whoever said it can't be done !"</span><span class="keyword">;<br />
</span><span class="default">$data</span><span class="keyword">[</span><span class="string">'blank_array'</span><span class="keyword">]=array();<br />
<br />
echo </span><span class="string">"'array' Isset? "</span><span class="keyword">; </span><span class="default">print_r</span><span class="keyword">(isset(</span><span class="default">$data</span><span class="keyword">[</span><span class="string">'array'</span><span class="keyword">])); echo </span><span class="string">"&lt;hr /&gt;"</span><span class="keyword">;<br />
echo </span><span class="string">"&lt;pre&gt;"</span><span class="keyword">; </span><span class="default">print_r</span><span class="keyword">(</span><span class="default">$data</span><span class="keyword">[</span><span class="string">'array'</span><span class="keyword">][</span><span class="string">'non_existent'</span><span class="keyword">]); echo </span><span class="string">"&lt;/pre&gt;If attempting to read an offset that doesn't exist it returns a blank object! Use isset() to check if it exists!&lt;br&gt;"</span><span class="keyword">;<br />
echo </span><span class="string">"'non_existent' Isset? "</span><span class="keyword">; </span><span class="default">print_r</span><span class="keyword">(isset(</span><span class="default">$data</span><span class="keyword">[</span><span class="string">'array'</span><span class="keyword">][</span><span class="string">'non_existent'</span><span class="keyword">])); echo </span><span class="string">"&lt;br /&gt;"</span><span class="keyword">;<br />
echo </span><span class="string">"&lt;pre&gt;"</span><span class="keyword">; </span><span class="default">print_r</span><span class="keyword">(</span><span class="default">$data</span><span class="keyword">[</span><span class="string">'blank_array'</span><span class="keyword">]); echo </span><span class="string">"&lt;/pre&gt;A blank array unfortunately returns similar results :(&lt;br /&gt;"</span><span class="keyword">;<br />
echo </span><span class="string">"'blank_array' Isset? "</span><span class="keyword">; </span><span class="default">print_r</span><span class="keyword">(isset(</span><span class="default">$data</span><span class="keyword">[</span><span class="string">'blank_array'</span><span class="keyword">])); echo </span><span class="string">"&lt;hr /&gt;"</span><span class="keyword">;<br />
echo </span><span class="string">"&lt;pre&gt;"</span><span class="keyword">; </span><span class="default">print_r</span><span class="keyword">(</span><span class="default">$data</span><span class="keyword">); echo </span><span class="string">"&lt;/pre&gt; (non_existent remains in the structure. If someone can help to solve this I'll appreciate it)&lt;hr /&gt;"</span><span class="keyword">;<br />
<br />
echo </span><span class="string">"Display some value that exists: "</span><span class="keyword">.</span><span class="default">$data</span><span class="keyword">[</span><span class="string">'array'</span><span class="keyword">][</span><span class="string">'another_string'</span><span class="keyword">];<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
(in the two links mentioned below by artur at jedlinski... they say you can't use references, so I didn't used them.<br />
My implementation uses recursive objects)<br />
<br />
If anyone finds a better (cleaner) sollution, please e-mail me.<br />
Thanks,<br />
Wave.</span>
</code></div>
  </div>
 </div>
 <a name="75215"></a>
 <div class="note">
  <strong class='user'>doctorrock83_at_gmail.com</strong>
  <a href="#75215" class="date">18-May-2007 03:10</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Please remember that actually the only PHP iterating structure that uses Iterator is foreach().<br />
<br />
Any each() or list() applied to an Object implementing iterator will not provide the expected result</span>
</code></div>
  </div>
 </div>
 <a name="74662"></a>
 <div class="note">
  <strong class='user'>artur at jedlinski dot pl</strong>
  <a href="#74662" class="date">22-Apr-2007 12:38</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
One should be aware that ArrayAccess functionality described by "just_somedood at yahoo dot com" below is currently broken and thus it's pretty unusable.<br />
<br />
Read following links to find more:<br />
<a href="http://bugs.php.net/bug.php?id=34783" rel="nofollow" target="_blank">http://bugs.php.net/bug.php?id=34783</a><br />
<a href="http://bugs.php.net/bug.php?id=32983" rel="nofollow" target="_blank">http://bugs.php.net/bug.php?id=32983</a></span>
</code></div>
  </div>
 </div>
 <a name="73527"></a>
 <div class="note">
  <strong class='user'>rune at zedeler dot dk</strong>
  <a href="#73527" class="date">27-Feb-2007 08:00</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The iterator template from knj at aider dot dk does not yield correct results.<br />
If you do<br />
&lt;?<br />
reset($a);<br />
next($a);<br />
echo current($a);<br />
?&gt;<br />
where $a is defined over the suggested template, then the first element will be output, not the second, as expected.</span>
</code></div>
  </div>
 </div>
 <a name="68764"></a>
 <div class="note">
  <strong class='user'>baldurien at bbnwn dot eu</strong>
  <a href="#68764" class="date">09-Aug-2006 06:01</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Beware of how works iterator in PHP if you come from Java!<br />
<br />
In Java, iterator works like this :<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">interface </span><span class="default">Iterator</span><span class="keyword">&lt;</span><span class="default">O</span><span class="keyword">&gt; {<br />
&nbsp; </span><span class="default">boolean hasNext</span><span class="keyword">();<br />
&nbsp; </span><span class="default">O next</span><span class="keyword">();<br />
&nbsp; </span><span class="default">void remove</span><span class="keyword">();<br />
}<br />
</span><span class="default">?&gt;<br />
</span>But in php, the interface is this (I kept the generics and type because it's easier to understand)<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">interface </span><span class="default">Iterator</span><span class="keyword">&lt;</span><span class="default">O</span><span class="keyword">&gt; {<br />
&nbsp; </span><span class="default">boolean valid</span><span class="keyword">();<br />
&nbsp; </span><span class="default">mixed key</span><span class="keyword">();<br />
&nbsp; </span><span class="default">O current</span><span class="keyword">();<br />
&nbsp; </span><span class="default">void next</span><span class="keyword">();<br />
&nbsp; </span><span class="default">void previous</span><span class="keyword">();<br />
&nbsp; </span><span class="default">void rewind</span><span class="keyword">();<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
1. valid() is more or less the equivalent of hasNext()<br />
2. next() is not the equivalent of java next(). It returns nothing, while Java next() method return the next object, and move to next object in Collections. PHP's next() method will simply move forward.<br />
<br />
Here is a sample with an array, first in java, then in php :<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">ArrayIterator</span><span class="keyword">&lt;</span><span class="default">O</span><span class="keyword">&gt; implements </span><span class="default">Iterator</span><span class="keyword">&lt;</span><span class="default">O</span><span class="keyword">&gt; {<br />
&nbsp; private final </span><span class="default">O</span><span class="keyword">[] array;<br />
&nbsp; private </span><span class="default">int index </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">;<br />
<br />
&nbsp; public </span><span class="default">ArrayIterator</span><span class="keyword">(</span><span class="default">O</span><span class="keyword">[] array) {<br />
&nbsp;&nbsp; &nbsp; </span><span class="default">this</span><span class="keyword">.array = array;<br />
&nbsp; }<br />
&nbsp; <br />
&nbsp; public </span><span class="default">boolean hasNext</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">index </span><span class="keyword">&lt; array.</span><span class="default">length</span><span class="keyword">;<br />
&nbsp; }&nbsp; <br />
<br />
&nbsp; public </span><span class="default">O next</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; if ( !</span><span class="default">hasNext</span><span class="keyword">()) <br />
&nbsp;&nbsp; &nbsp; &nbsp; throw new </span><span class="default">NoSuchElementException</span><span class="keyword">(</span><span class="string">'at end of array'</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; return array[</span><span class="default">index</span><span class="keyword">++];<br />
&nbsp; }<br />
<br />
&nbsp; public </span><span class="default">void remove</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; throw new </span><span class="default">UnsupportedOperationException</span><span class="keyword">(</span><span class="string">'remove() not supported in array'</span><span class="keyword">);<br />
&nbsp; }<br />
}<br />
</span><span class="default">?&gt;</span> <br />
<br />
And here is the same in php (using the appropriate function) :<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="comment">/**<br />
&nbsp;* Since the array is not mutable, it should use an internal <br />
&nbsp;* index over the number of elements for the previous/next <br />
&nbsp;* validation.<br />
&nbsp;*/<br />
</span><span class="keyword">class </span><span class="default">ArrayIterator </span><span class="keyword">implements </span><span class="default">Iterator </span><span class="keyword">{<br />
&nbsp; private </span><span class="default">$array</span><span class="keyword">;<br />
&nbsp; public function </span><span class="default">__construct</span><span class="keyword">(</span><span class="default">$array</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; if ( !</span><span class="default">is_array</span><span class="keyword">(</span><span class="default">$array</span><span class="keyword">)) <br />
&nbsp;&nbsp; &nbsp;&nbsp; throw new </span><span class="default">IllegalArgumentException</span><span class="keyword">(</span><span class="string">'argument 0 is not an array'</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">array </span><span class="keyword">= array;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">rewind</span><span class="keyword">();<br />
&nbsp; }<br />
&nbsp; public function </span><span class="default">valid</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">current</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">array</span><span class="keyword">) !== </span><span class="default">false</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">// that's the bad method (should use arrays_keys, + index)<br />
&nbsp; </span><span class="keyword">}<br />
&nbsp; public function </span><span class="default">key</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; return </span><span class="default">key</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">array</span><span class="keyword">);<br />
&nbsp; }<br />
&nbsp; public function </span><span class="default">current</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">current</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">array</span><span class="keyword">);<br />
&nbsp; }<br />
&nbsp; public function </span><span class="default">next</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; if ( </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">valid</span><span class="keyword">()) <br />
&nbsp;&nbsp; &nbsp;&nbsp; throw new </span><span class="default">NoSuchElementException</span><span class="keyword">(</span><span class="string">'at end of array'</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">next</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">array</span><span class="keyword">);<br />
&nbsp; }<br />
&nbsp; public function </span><span class="default">previous</span><span class="keyword">()&nbsp; {<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">// fails if current() = first item of array<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">previous</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">array</span><span class="keyword">);<br />
&nbsp; }<br />
&nbsp; public function </span><span class="default">rewind</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; </span><span class="default">reset</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">array</span><span class="keyword">);<br />
&nbsp; }<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
The difference is notable : don't expect next() to return something like in Java, instead use current(). This also means that you have to prefetch your collection to set the current() object. For instance, if you try to make a Directory iterator (like the one provided by PECL), rewind should invoke next() to set the first element and so on. (and the constructor should call rewind())<br />
<br />
Also, another difference :<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">ArrayIterable</span><span class="keyword">&lt;</span><span class="default">O</span><span class="keyword">&gt; implements </span><span class="default">Iterable</span><span class="keyword">&lt;</span><span class="default">O</span><span class="keyword">&gt; {<br />
&nbsp; private final </span><span class="default">O</span><span class="keyword">[] array;<br />
<br />
&nbsp; public </span><span class="default">ArrayIterable</span><span class="keyword">(</span><span class="default">O</span><span class="keyword">[] array) {<br />
&nbsp;&nbsp; &nbsp; </span><span class="default">this</span><span class="keyword">.array = array;<br />
&nbsp; }&nbsp; <br />
<br />
&nbsp; public </span><span class="default">Iterator</span><span class="keyword">&lt;</span><span class="default">O</span><span class="keyword">&gt; </span><span class="default">iterator</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; return new </span><span class="default">ArrayIterator</span><span class="keyword">(array);<br />
&nbsp; }<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
When using an Iterable, in Java 1.5, you may do such loops :<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">for ( </span><span class="default">String s </span><span class="keyword">: new </span><span class="default">ArrayIterable</span><span class="keyword">&lt;</span><span class="default">String</span><span class="keyword">&gt;(new </span><span class="default">String</span><span class="keyword">[] {</span><span class="string">"a"</span><span class="keyword">, </span><span class="string">"b"</span><span class="keyword">})) {<br />
&nbsp; ...<br />
}<br />
</span><span class="default">?&gt;<br />
</span>Which is the same as :<br />
<br />
<span class="default">&lt;?php<br />
Iterator</span><span class="keyword">&lt;</span><span class="default">String</span><span class="keyword">&gt; </span><span class="default">it </span><span class="keyword">= new </span><span class="default">ArrayIterable</span><span class="keyword">&lt;</span><span class="default">String</span><span class="keyword">&gt;(new </span><span class="default">String</span><span class="keyword">[] {</span><span class="string">"a"</span><span class="keyword">, </span><span class="string">"b"</span><span class="keyword">});<br />
while (</span><span class="default">it</span><span class="keyword">.</span><span class="default">hasNext</span><span class="keyword">()) {<br />
&nbsp; </span><span class="default">String s </span><span class="keyword">= </span><span class="default">it</span><span class="keyword">.</span><span class="default">next</span><span class="keyword">();<br />
&nbsp; ...<br />
}<br />
</span><span class="default">?&gt;<br />
</span>While in PHP it's not the case :<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">foreach ( </span><span class="default">$iterator </span><span class="keyword">as </span><span class="default">$current </span><span class="keyword">) {<br />
&nbsp; ...<br />
}<br />
</span><span class="default">?&gt;<br />
</span>Is the same as :<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">for ( </span><span class="default">$iterator</span><span class="keyword">-&gt;</span><span class="default">rewind</span><span class="keyword">(); </span><span class="default">$iterator</span><span class="keyword">-&gt;</span><span class="default">valid</span><span class="keyword">(); </span><span class="default">$iterator</span><span class="keyword">-&gt;</span><span class="default">next</span><span class="keyword">()) {<br />
&nbsp; </span><span class="default">$current </span><span class="keyword">= </span><span class="default">$iterator</span><span class="keyword">-&gt;</span><span class="default">current</span><span class="keyword">();<br />
&nbsp; ...<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
(I think we may also use IteratorAggregate to do it like with Iterable).<br />
<br />
Take that in mind if you come from Java.<br />
<br />
I hope this explanation is not too long...</span>
</code></div>
  </div>
 </div>
 <a name="65668"></a>
 <div class="note">
  <strong class='user'>chad 0x40 herballure 0x2e com</strong>
  <a href="#65668" class="date">05-May-2006 06:46</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The example code given for valid() will break if the array contains a FALSE value. This code prints out a single "bool(true)" and exits the loop when it gets to the FALSE:<br />
<br />
<span class="default">&lt;?php<br />
$A </span><span class="keyword">= array(</span><span class="default">TRUE</span><span class="keyword">, </span><span class="default">FALSE</span><span class="keyword">, </span><span class="default">TRUE</span><span class="keyword">, </span><span class="default">TRUE</span><span class="keyword">);<br />
while(</span><span class="default">current</span><span class="keyword">(</span><span class="default">$A</span><span class="keyword">) !== </span><span class="default">FALSE</span><span class="keyword">) {<br />
&nbsp; </span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">current</span><span class="keyword">(</span><span class="default">$A</span><span class="keyword">));<br />
&nbsp; </span><span class="default">next</span><span class="keyword">(</span><span class="default">$A</span><span class="keyword">);<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
Instead, the key() function should be used, since it returns NULL only at the end of the array. This code displays all four elements and then exits:<br />
<br />
<span class="default">&lt;?php<br />
$A </span><span class="keyword">= array(</span><span class="default">TRUE</span><span class="keyword">, </span><span class="default">FALSE</span><span class="keyword">, </span><span class="default">TRUE</span><span class="keyword">, </span><span class="default">TRUE</span><span class="keyword">);<br />
while(!</span><span class="default">is_null</span><span class="keyword">(</span><span class="default">key</span><span class="keyword">(</span><span class="default">$A</span><span class="keyword">))) {<br />
&nbsp; </span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">current</span><span class="keyword">(</span><span class="default">$A</span><span class="keyword">));<br />
&nbsp; </span><span class="default">next</span><span class="keyword">(</span><span class="default">$A</span><span class="keyword">);<br />
}<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="55545"></a>
 <div class="note">
  <strong class='user'>markushe at web dot de</strong>
  <a href="#55545" class="date">06-Aug-2005 11:05</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Just something i noticed:<br />
It seems, that when you are implementing the interface Iterator, yout method key() has to return a string or integer.<br />
<br />
I was trying to return a object an got this error:<br />
Illegal type returned from MyClass::key()</span>
</code></div>
  </div>
 </div>
 <a name="54223"></a>
 <div class="note">
  <strong class='user'>just_somedood at yahoo dot com</strong>
  <a href="#54223" class="date">27-Jun-2005 12:20</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
To clarify on php at moechofe's post, you CAN use the SPL to overide the array operator for a class.&nbsp; This, with the new features of object, and autoloading (among a buch of other things) has me completely sold on PHP5.&nbsp; You can also find this information on the SPL portion of the manual, but I'll post it here as well so it isn't passed up.&nbsp; The below Collection class will let you use the class as an array, while also using the foreach iterator:<br />
<br />
<span class="default">&lt;?php <br />
<br />
</span><span class="keyword">class </span><span class="default">Collection </span><span class="keyword">implements </span><span class="default">ArrayAccess</span><span class="keyword">,</span><span class="default">IteratorAggregate<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public </span><span class="default">$objectArray </span><span class="keyword">= Array();<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">//**these are the required iterator functions&nbsp; &nbsp; <br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">function </span><span class="default">offsetExists</span><span class="keyword">(</span><span class="default">$offset</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if(isset(</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">objectArray</span><span class="keyword">[</span><span class="default">$offset</span><span class="keyword">]))&nbsp; return </span><span class="default">TRUE</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; else return </span><span class="default">FALSE</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <br />
&nbsp;&nbsp;&nbsp; }&nbsp; &nbsp; <br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; function &amp; </span><span class="default">offsetGet</span><span class="keyword">(</span><span class="default">$offset</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {&nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if (</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">offsetExists</span><span class="keyword">(</span><span class="default">$offset</span><span class="keyword">))&nbsp; return </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">objectArray</span><span class="keyword">[</span><span class="default">$offset</span><span class="keyword">];<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; else return (</span><span class="default">false</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">offsetSet</span><span class="keyword">(</span><span class="default">$offset</span><span class="keyword">, </span><span class="default">$value</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if (</span><span class="default">$offset</span><span class="keyword">)&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">objectArray</span><span class="keyword">[</span><span class="default">$offset</span><span class="keyword">] = </span><span class="default">$value</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; else&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">objectArray</span><span class="keyword">[] = </span><span class="default">$value</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">offsetUnset</span><span class="keyword">(</span><span class="default">$offset</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; unset (</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">objectArray</span><span class="keyword">[</span><span class="default">$offset</span><span class="keyword">]);<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; function &amp; </span><span class="default">getIterator</span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return new </span><span class="default">ArrayIterator</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">objectArray</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">//**end required iterator functions<br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">public function </span><span class="default">doSomething</span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">"I'm doing something"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
I LOVE the new SPL stuff in PHP!&nbsp; An example of usage is below:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">Contact<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; protected </span><span class="default">$name </span><span class="keyword">= </span><span class="default">NULL</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">set_name</span><span class="keyword">(</span><span class="default">$name</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">name </span><span class="keyword">= </span><span class="default">$name</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">get_name</span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return (</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">name</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="default">$bob </span><span class="keyword">= new </span><span class="default">Collection</span><span class="keyword">();<br />
</span><span class="default">$bob</span><span class="keyword">-&gt;</span><span class="default">doSomething</span><span class="keyword">();<br />
</span><span class="default">$bob</span><span class="keyword">[] = new </span><span class="default">Contact</span><span class="keyword">();<br />
</span><span class="default">$bob</span><span class="keyword">[</span><span class="default">5</span><span class="keyword">] = new </span><span class="default">Contact</span><span class="keyword">();<br />
</span><span class="default">$bob</span><span class="keyword">[</span><span class="default">0</span><span class="keyword">]-&gt;</span><span class="default">set_name</span><span class="keyword">(</span><span class="string">"Superman"</span><span class="keyword">);<br />
</span><span class="default">$bob</span><span class="keyword">[</span><span class="default">5</span><span class="keyword">]-&gt;</span><span class="default">set_name</span><span class="keyword">(</span><span class="string">"a name of a guy"</span><span class="keyword">);<br />
<br />
foreach (</span><span class="default">$bob </span><span class="keyword">as </span><span class="default">$aContact</span><span class="keyword">)<br />
{<br />
&nbsp;&nbsp; &nbsp; echo </span><span class="default">$aContact</span><span class="keyword">-&gt;</span><span class="default">get_name</span><span class="keyword">() . </span><span class="string">"\r\n"</span><span class="keyword">;<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
Would work just fine.&nbsp; This makes code so much simpler and easy to follow, it's great.&nbsp; This is exactly the direction I had hoped PHP5 was going!</span>
</code></div>
  </div>
 </div>
 <a name="53273"></a>
 <div class="note">
  <strong class='user'>PrzemekG_ at poczta dot onet dot pl</strong>
  <a href="#53273" class="date">27-May-2005 04:20</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you want to do someting like this:<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">foreach(</span><span class="default">$MyObject </span><span class="keyword">as </span><span class="default">$key </span><span class="keyword">=&gt; &amp;</span><span class="default">$value</span><span class="keyword">)<br />
&nbsp;&nbsp; </span><span class="default">$value </span><span class="keyword">= </span><span class="string">'new '</span><span class="keyword">.</span><span class="default">$value</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span>you must return values by reference in your iterator object:<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">MyObject </span><span class="keyword">implements </span><span class="default">Iterator<br />
</span><span class="keyword">{<br />
</span><span class="comment">/* ...... other iterator functions ...... */<br />
/* return by reference */<br />
</span><span class="keyword">public function &amp;</span><span class="default">current</span><span class="keyword">()<br />
{<br />
&nbsp;&nbsp; return </span><span class="default">$something</span><span class="keyword">;<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
This won't change values:<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">foreach(</span><span class="default">$MyObject </span><span class="keyword">as </span><span class="default">$key </span><span class="keyword">=&gt; </span><span class="default">$value</span><span class="keyword">)<br />
&nbsp;&nbsp; </span><span class="default">$value </span><span class="keyword">= </span><span class="string">'new '</span><span class="keyword">.</span><span class="default">$value</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
This will change values:<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">foreach(</span><span class="default">$MyObject </span><span class="keyword">as </span><span class="default">$key </span><span class="keyword">=&gt; &amp;</span><span class="default">$value</span><span class="keyword">)<br />
&nbsp;&nbsp; </span><span class="default">$value </span><span class="keyword">= </span><span class="string">'new '</span><span class="keyword">.</span><span class="default">$value</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
I think this should be written somewhere in the documentations, but I couldn't find it.</span>
</code></div>
  </div>
 </div>
 <a name="51771"></a>
 <div class="note">
  <strong class='user'>elias at need dot spam</strong>
  <a href="#51771" class="date">10-Apr-2005 03:15</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The MyIterator::valid() method above ist bad, because it<br />
breaks on entries with 0 or empty strings, use key() instead:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">public function </span><span class="default">valid</span><span class="keyword">()<br />
{<br />
&nbsp;&nbsp;&nbsp; return ! </span><span class="default">is_null</span><span class="keyword">(</span><span class="default">key</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">var</span><span class="keyword">));<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
read about current() drawbacks:<br />
<a href="http://php.net/current" rel="nofollow" target="_blank">http://php.net/current</a></span>
</code></div>
  </div>
 </div>
 <a name="50490"></a>
 <div class="note">
  <strong class='user'>strrev('ed.relpmeur@ekneos');</strong>
  <a href="#50490" class="date">01-Mar-2005 02:25</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Use the SPL ArrayAccess interface to call an object as array:<br />
<br />
<a href="http://www.php.net/~helly/php/ext/spl/interfaceArrayAccess.html" rel="nofollow" target="_blank">http://www.php.net/~helly/php/ext/spl/interfaceArrayAccess.html</a></span>
</code></div>
  </div>
 </div>
 <a name="50235"></a>
 <div class="note">
  <strong class='user'>phpnet at nicecupofteaandasitdown dot com</strong>
  <a href="#50235" class="date">22-Feb-2005 08:09</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
You should be prepared for your iterator's current method to be called before its next method is ever called. This certainly happens in a foreach loop. If your means of finding the next item is expensive you might want to use something like this<br />
<br />
private $item;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
function next() {<br />
&nbsp;&nbsp;&nbsp; $this-&gt;item = &amp;$this-&gt;getNextItem();<br />
&nbsp;&nbsp;&nbsp; return $this-&gt;item;<br />
}<br />
&nbsp;&nbsp;&nbsp; <br />
public function current() {<br />
&nbsp;&nbsp; &nbsp; if(!isset($this-&gt;item)) $this-&gt;next();<br />
&nbsp;&nbsp;&nbsp; return $this-&gt;item;<br />
}</span>
</code></div>
  </div>
 </div>
 <a name="48317"></a>
 <div class="note">
  <strong class='user'>knj at aider dot dk</strong>
  <a href="#48317" class="date">18-Dec-2004 07:19</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
if you in a string define classes that implements IteratorAggregate.<br />
you cant use the default;<br />
&lt;?<br />
...<br />
public function getIterator() {<br />
&nbsp;&nbsp; &nbsp; &nbsp; return new MyIterator(\\$this-&gt;&lt;What ever&gt;);<br />
}<br />
..<br />
?&gt;<br />
at least not if you want to use eval(&lt;The string&gt;).<br />
You have to use:<br />
&lt;?<br />
...<br />
public function getIterator() {<br />
&nbsp;&nbsp; &nbsp;&nbsp; \\$arrayObj=new ArrayObject(\\$this-&gt;&lt;What ever&gt;);<br />
&nbsp;&nbsp; &nbsp;&nbsp; return \\$arrayObj-&gt;getIterator();<br />
}<br />
...<br />
?&gt;</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=language.oop5.iterations&amp;redirect=http://www.php.net/manual/en/language.oop5.iterations.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.oop5.iterations&amp;redirect=http://www.php.net/manual/en/language.oop5.iterations.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/language.oop5.iterations.php">show source</a> |
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