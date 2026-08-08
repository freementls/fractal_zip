<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Objects and references - Manual</title>
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
 <link rel="prev" href="language.oop5.late-static-bindings.php" />
 <link rel="next" href="language.oop5.serialization.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/oop5.references" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/language.oop5.references.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/language.oop5.references.php" />
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
 <li><a href="language.oop5.iterations.php">Object Iteration</a></li>
 <li><a href="language.oop5.magic.php">Magic Methods</a></li>
 <li><a href="language.oop5.final.php">Final Keyword</a></li>
 <li><a href="language.oop5.cloning.php">Object Cloning</a></li>
 <li><a href="language.oop5.object-comparison.php">Comparing Objects</a></li>
 <li><a href="language.oop5.typehinting.php">Type Hinting</a></li>
 <li><a href="language.oop5.late-static-bindings.php">Late Static Bindings</a></li>
 <li class="active"><a href="language.oop5.references.php">Objects and references</a></li>
 <li><a href="language.oop5.serialization.php">Object Serialization</a></li>
 <li><a href="language.oop5.changelog.php">OOP Changelog</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="language.oop5.serialization.php">Object Serialization<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.oop5.late-static-bindings.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Late Static Bindings</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.oop5.references.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/language.oop5.references.php">Brazilian Portuguese</option>
    <option value="zh/language.oop5.references.php">Chinese (Simplified)</option>
    <option value="fr/language.oop5.references.php">French</option>
    <option value="de/language.oop5.references.php">German</option>
    <option value="ja/language.oop5.references.php">Japanese</option>
    <option value="pl/language.oop5.references.php">Polish</option>
    <option value="ro/language.oop5.references.php">Romanian</option>
    <option value="ru/language.oop5.references.php">Russian</option>
    <option value="fa/language.oop5.references.php">Persian</option>
    <option value="es/language.oop5.references.php">Spanish</option>
    <option value="tr/language.oop5.references.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="language.oop5.references" class="sect1">
  <h2 class="title">Objects and references</h2>
  <p class="para">
   One of the key-points of PHP 5 OOP that is often mentioned is that 
   &quot;objects are passed by references by default&quot;. This is not completely true. 
   This section rectifies that general thought using some examples.
  </p>

  <p class="para">
   A PHP reference is an alias, which allows two different variables to write
   to the same value. As of PHP 5, an object variable doesn&#039;t contain the object
   itself as value anymore. It only contains an object identifier which allows
   object accessors to find the actual object. When an object is sent by 
   argument, returned or assigned to another variable, the different variables
   are not aliases: they hold a copy of the identifier, which points to the same
   object.
  </p>

  <div class="example" id="example-225">
   <p><strong>Example #1 References and Objects</strong></p>
   <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">class&nbsp;</span><span style="color: #0000BB">A&nbsp;</span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;</span><span style="color: #0000BB">$foo&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">1</span><span style="color: #007700">;<br />}&nbsp;&nbsp;<br /><br /></span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">A</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">$b&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">$a</span><span style="color: #007700">;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;$a&nbsp;and&nbsp;$b&nbsp;are&nbsp;copies&nbsp;of&nbsp;the&nbsp;same&nbsp;identifier<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;//&nbsp;($a)&nbsp;=&nbsp;($b)&nbsp;=&nbsp;&lt;id&gt;<br /></span><span style="color: #0000BB">$b</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">foo&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">2</span><span style="color: #007700">;<br />echo&nbsp;</span><span style="color: #0000BB">$a</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">foo</span><span style="color: #007700">.</span><span style="color: #DD0000">"\n"</span><span style="color: #007700">;<br /><br /><br /></span><span style="color: #0000BB">$c&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">A</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">$d&nbsp;</span><span style="color: #007700">=&nbsp;&amp;</span><span style="color: #0000BB">$c</span><span style="color: #007700">;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;$c&nbsp;and&nbsp;$d&nbsp;are&nbsp;references<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;//&nbsp;($c,$d)&nbsp;=&nbsp;&lt;id&gt;<br /><br /></span><span style="color: #0000BB">$d</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">foo&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">2</span><span style="color: #007700">;<br />echo&nbsp;</span><span style="color: #0000BB">$c</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">foo</span><span style="color: #007700">.</span><span style="color: #DD0000">"\n"</span><span style="color: #007700">;<br /><br /><br /></span><span style="color: #0000BB">$e&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">A</span><span style="color: #007700">;<br /><br />function&nbsp;</span><span style="color: #0000BB">foo</span><span style="color: #007700">(</span><span style="color: #0000BB">$obj</span><span style="color: #007700">)&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;($obj)&nbsp;=&nbsp;($e)&nbsp;=&nbsp;&lt;id&gt;<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$obj</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">foo&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">2</span><span style="color: #007700">;<br />}<br /><br /></span><span style="color: #0000BB">foo</span><span style="color: #007700">(</span><span style="color: #0000BB">$e</span><span style="color: #007700">);<br />echo&nbsp;</span><span style="color: #0000BB">$e</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">foo</span><span style="color: #007700">.</span><span style="color: #DD0000">"\n"</span><span style="color: #007700">;<br /><br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
   </div>

   <div class="example-contents"><p>The above example will output:</p></div>
   <div class="example-contents screen">
<div class="cdata"><pre>
2
2
2
</pre></div>
   </div>
  </div>
 </div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="language.oop5.serialization.php">Object Serialization<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.oop5.late-static-bindings.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Late Static Bindings</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.oop5.references.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=language.oop5.references&amp;redirect=http://www.php.net/manual/en/language.oop5.references.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.oop5.references&amp;redirect=http://www.php.net/manual/en/language.oop5.references.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Objects and references</strong>
 </div><div id="allnotes">
 <a name="109052"></a>
 <div class="note">
  <strong class='user'>kristof at viewranger dot com</strong>
  <a href="#109052" class="date">16-Jun-2012 02:07</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I hope this clarifies references a bit more:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">A </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public </span><span class="default">$foo </span><span class="keyword">= </span><span class="default">1</span><span class="keyword">;<br />
}&nbsp; <br />
<br />
</span><span class="default">$a </span><span class="keyword">= new </span><span class="default">A</span><span class="keyword">;<br />
</span><span class="default">$b </span><span class="keyword">= </span><span class="default">$a</span><span class="keyword">;<br />
</span><span class="default">$a</span><span class="keyword">-&gt;</span><span class="default">foo </span><span class="keyword">= </span><span class="default">2</span><span class="keyword">;<br />
</span><span class="default">$a </span><span class="keyword">= </span><span class="default">NULL</span><span class="keyword">;<br />
echo </span><span class="default">$b</span><span class="keyword">-&gt;</span><span class="default">foo</span><span class="keyword">.</span><span class="string">"\n"</span><span class="keyword">; </span><span class="comment">// 2<br />
<br />
</span><span class="default">$c </span><span class="keyword">= new </span><span class="default">A</span><span class="keyword">;<br />
</span><span class="default">$d </span><span class="keyword">= &amp;</span><span class="default">$c</span><span class="keyword">;<br />
</span><span class="default">$c</span><span class="keyword">-&gt;</span><span class="default">foo </span><span class="keyword">= </span><span class="default">2</span><span class="keyword">;<br />
</span><span class="default">$c </span><span class="keyword">= </span><span class="default">NULL</span><span class="keyword">;<br />
echo </span><span class="default">$d</span><span class="keyword">-&gt;</span><span class="default">foo</span><span class="keyword">.</span><span class="string">"\n"</span><span class="keyword">; </span><span class="comment">// Notice:&nbsp; Trying to get property of non-object...<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="106643"></a>
 <div class="note">
  <strong class='user'>Anonymous</strong>
  <a href="#106643" class="date">23-Nov-2011 07:57</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
this example could help:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">A </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public </span><span class="default">$testA </span><span class="keyword">= </span><span class="default">1</span><span class="keyword">;<br />
}&nbsp; <br />
<br />
class </span><span class="default">B </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public </span><span class="default">$testB </span><span class="keyword">= </span><span class="string">"class B"</span><span class="keyword">;<br />
}&nbsp; <br />
<br />
</span><span class="default">$a </span><span class="keyword">= new </span><span class="default">A</span><span class="keyword">;<br />
</span><span class="default">$b </span><span class="keyword">= </span><span class="default">$a</span><span class="keyword">;&nbsp; &nbsp;&nbsp; <br />
</span><span class="default">$b</span><span class="keyword">-&gt;</span><span class="default">testA </span><span class="keyword">= </span><span class="default">2</span><span class="keyword">;<br />
<br />
</span><span class="default">$c </span><span class="keyword">= new </span><span class="default">B</span><span class="keyword">;<br />
</span><span class="default">$a </span><span class="keyword">= </span><span class="default">$c</span><span class="keyword">;<br />
<br />
</span><span class="default">$a</span><span class="keyword">-&gt;</span><span class="default">testB </span><span class="keyword">= </span><span class="string">"Changed Class B"</span><span class="keyword">;<br />
<br />
echo </span><span class="string">"&lt;br/&gt; object a: "</span><span class="keyword">; </span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$a</span><span class="keyword">);<br />
echo </span><span class="string">"&lt;br/&gt; object b: "</span><span class="keyword">; </span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$b</span><span class="keyword">);<br />
echo </span><span class="string">"&lt;br/&gt; object c: "</span><span class="keyword">; </span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$c</span><span class="keyword">);<br />
<br />
</span><span class="comment">// by reference <br />
<br />
</span><span class="default">$aa </span><span class="keyword">= new </span><span class="default">A</span><span class="keyword">;<br />
</span><span class="default">$bb </span><span class="keyword">= &amp;</span><span class="default">$aa</span><span class="keyword">;&nbsp; &nbsp;&nbsp; <br />
</span><span class="default">$bb</span><span class="keyword">-&gt;</span><span class="default">testA </span><span class="keyword">= </span><span class="default">2</span><span class="keyword">;<br />
<br />
</span><span class="default">$cc </span><span class="keyword">= new </span><span class="default">B</span><span class="keyword">;<br />
</span><span class="default">$aa </span><span class="keyword">= </span><span class="default">$cc</span><span class="keyword">;<br />
<br />
</span><span class="default">$aa</span><span class="keyword">-&gt;</span><span class="default">testB </span><span class="keyword">= </span><span class="string">"Changed Class B"</span><span class="keyword">;<br />
<br />
echo </span><span class="string">"&lt;br/&gt; object aa: "</span><span class="keyword">; </span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$aa</span><span class="keyword">);<br />
echo </span><span class="string">"&lt;br/&gt; object bb: "</span><span class="keyword">; </span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$bb</span><span class="keyword">);<br />
echo </span><span class="string">"&lt;br/&gt; object cc: "</span><span class="keyword">; </span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$cc</span><span class="keyword">);<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="102242"></a>
 <div class="note">
  <strong class='user'>Rob Marscher</strong>
  <a href="#102242" class="date">03-Feb-2011 09:33</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Here's an example I created that helped me understand the difference between passing objects by reference and by value in php 5.<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">A </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public </span><span class="default">$foo </span><span class="keyword">= </span><span class="string">'empty'</span><span class="keyword">;<br />
}<br />
class </span><span class="default">B </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public </span><span class="default">$foo </span><span class="keyword">= </span><span class="string">'empty'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; public </span><span class="default">$bar </span><span class="keyword">= </span><span class="string">'hello'</span><span class="keyword">;<br />
}<br />
<br />
function </span><span class="default">normalAssignment</span><span class="keyword">(</span><span class="default">$obj</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$obj</span><span class="keyword">-&gt;</span><span class="default">foo </span><span class="keyword">= </span><span class="string">'changed'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$obj </span><span class="keyword">= new </span><span class="default">B</span><span class="keyword">;<br />
}<br />
<br />
function </span><span class="default">referenceAssignment</span><span class="keyword">(&amp;</span><span class="default">$obj</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$obj</span><span class="keyword">-&gt;</span><span class="default">foo </span><span class="keyword">= </span><span class="string">'changed'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$obj </span><span class="keyword">= new </span><span class="default">B</span><span class="keyword">;<br />
}<br />
<br />
</span><span class="default">$a </span><span class="keyword">= new </span><span class="default">A</span><span class="keyword">;<br />
</span><span class="default">normalAssignment</span><span class="keyword">(</span><span class="default">$a</span><span class="keyword">);<br />
echo </span><span class="default">get_class</span><span class="keyword">(</span><span class="default">$a</span><span class="keyword">), </span><span class="string">"\n"</span><span class="keyword">;<br />
echo </span><span class="string">"foo = {$a-&gt;foo}\n"</span><span class="keyword">;<br />
<br />
</span><span class="default">referenceAssignment</span><span class="keyword">(</span><span class="default">$a</span><span class="keyword">);<br />
echo </span><span class="default">get_class</span><span class="keyword">(</span><span class="default">$a</span><span class="keyword">), </span><span class="string">"\n"</span><span class="keyword">;<br />
echo </span><span class="string">"foo = {$a-&gt;foo}\n"</span><span class="keyword">;<br />
echo </span><span class="string">"bar = {$a-&gt;bar}\n"</span><span class="keyword">;<br />
<br />
</span><span class="comment">/*<br />
prints:<br />
A<br />
foo = changed<br />
B<br />
foo = empty<br />
bar = hello<br />
*/<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="101900"></a>
 <div class="note">
  <strong class='user'>Anonymous</strong>
  <a href="#101900" class="date">16-Jan-2011 08:51</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
There seems to be some confusion here. The distinction between pointers and references is not particularly helpful.<br />
The behavior in some of the "comprehensive" examples already posted can be explained in simpler unifying terms. Hayley's code, for example, is doing EXACTLY what you should expect it should. (Using &gt;= 5.3)<br />
<br />
First principle:<br />
A pointer stores a memory address to access an object. Any time an object is assigned, a pointer is generated. (I haven't delved TOO deeply into the Zend engine yet, but as far as I can see, this applies)<br />
<br />
2nd principle, and source of the most confusion:<br />
Passing a variable to a function is done by default as a value pass, ie, you are working with a copy. "But objects are passed by reference!" A common misconception both here and in the Java world. I never said a copy OF WHAT. The default passing is done by value. Always. WHAT is being copied and passed, however, is the pointer. When using the "-&gt;", you will of course be accessing the same internals as the original variable in the caller function. Just using "=" will only play with copies.<br />
<br />
3rd principle:<br />
"&amp;" automatically and permanently sets another variable name/pointer to the same memory address as something else until you decouple them. It is correct to use the term "alias" here. Think of it as joining two pointers at the hip until forcibly separated with "unset()". This functionality exists both in the same scope and when an argument is passed to a function. Often the passed argument is called a "reference," due to certain distinctions between "passing by value" and "passing by reference" that were clearer in C and C++.<br />
<br />
Just remember: pointers to objects, not objects themselves, are passed to functions. These pointers are COPIES of the original unless you use "&amp;" in your parameter list to actually pass the originals. Only when you dig into the internals of an object will the originals change.<br />
<br />
Example:<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="comment">//The two are meant to be the same<br />
</span><span class="default">$a </span><span class="keyword">= </span><span class="string">"Clark Kent"</span><span class="keyword">; </span><span class="comment">//a==Clark Kent<br />
</span><span class="default">$b </span><span class="keyword">= &amp;</span><span class="default">$a</span><span class="keyword">; </span><span class="comment">//The two will now share the same fate.<br />
<br />
</span><span class="default">$b</span><span class="keyword">=</span><span class="string">"Superman"</span><span class="keyword">; </span><span class="comment">// $a=="Superman" too.<br />
</span><span class="keyword">echo </span><span class="default">$a</span><span class="keyword">; <br />
echo </span><span class="default">$a</span><span class="keyword">=</span><span class="string">"Clark Kent"</span><span class="keyword">; </span><span class="comment">// $b=="Clark Kent" too.<br />
</span><span class="keyword">unset(</span><span class="default">$b</span><span class="keyword">); </span><span class="comment">// $b divorced from $a<br />
</span><span class="default">$b</span><span class="keyword">=</span><span class="string">"Bizarro"</span><span class="keyword">;<br />
echo </span><span class="default">$a</span><span class="keyword">; </span><span class="comment">// $a=="Clark Kent" still, since $b is a free agent pointer now.<br />
<br />
//The two are NOT meant to be the same.<br />
</span><span class="default">$c</span><span class="keyword">=</span><span class="string">"King"</span><span class="keyword">;<br />
</span><span class="default">$d</span><span class="keyword">=</span><span class="string">"Pretender to the Throne"</span><span class="keyword">;<br />
echo </span><span class="default">$c</span><span class="keyword">.</span><span class="string">"\n"</span><span class="keyword">; </span><span class="comment">// $c=="King"<br />
</span><span class="keyword">echo </span><span class="default">$d</span><span class="keyword">.</span><span class="string">"\n"</span><span class="keyword">; </span><span class="comment">// $d=="Pretender to the Throne"<br />
</span><span class="default">swapByValue</span><span class="keyword">(</span><span class="default">$c</span><span class="keyword">, </span><span class="default">$d</span><span class="keyword">);<br />
echo </span><span class="default">$c</span><span class="keyword">.</span><span class="string">"\n"</span><span class="keyword">; </span><span class="comment">// $c=="King"<br />
</span><span class="keyword">echo </span><span class="default">$d</span><span class="keyword">.</span><span class="string">"\n"</span><span class="keyword">; </span><span class="comment">// $d=="Pretender to the Throne"<br />
</span><span class="default">swapByRef</span><span class="keyword">(</span><span class="default">$c</span><span class="keyword">, </span><span class="default">$d</span><span class="keyword">);<br />
echo </span><span class="default">$c</span><span class="keyword">.</span><span class="string">"\n"</span><span class="keyword">; </span><span class="comment">// $c=="Pretender to the Throne"<br />
</span><span class="keyword">echo </span><span class="default">$d</span><span class="keyword">.</span><span class="string">"\n"</span><span class="keyword">; </span><span class="comment">// $d=="King"<br />
<br />
</span><span class="keyword">function </span><span class="default">swapByValue</span><span class="keyword">(</span><span class="default">$x</span><span class="keyword">, </span><span class="default">$y</span><span class="keyword">){<br />
</span><span class="default">$temp</span><span class="keyword">=</span><span class="default">$x</span><span class="keyword">;<br />
</span><span class="default">$x</span><span class="keyword">=</span><span class="default">$y</span><span class="keyword">;<br />
</span><span class="default">$y</span><span class="keyword">=</span><span class="default">$temp</span><span class="keyword">;<br />
</span><span class="comment">//All this beautiful work will disappear<br />
//because it was done on COPIES of pointers.<br />
//The originals pointers still point as they did.<br />
</span><span class="keyword">}<br />
<br />
function </span><span class="default">swapByRef</span><span class="keyword">(&amp;</span><span class="default">$x</span><span class="keyword">, &amp;</span><span class="default">$y</span><span class="keyword">){<br />
&nbsp;</span><span class="default">$temp</span><span class="keyword">=</span><span class="default">$x</span><span class="keyword">;<br />
&nbsp;</span><span class="default">$x</span><span class="keyword">=</span><span class="default">$y</span><span class="keyword">;<br />
&nbsp;</span><span class="default">$y</span><span class="keyword">=</span><span class="default">$temp</span><span class="keyword">;<br />
&nbsp;</span><span class="comment">//Note the parameter list: now we switched 'em REAL good.<br />
</span><span class="keyword">}<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="97052"></a>
 <div class="note">
  <strong class='user'>Hayley Watson</strong>
  <a href="#97052" class="date">29-Mar-2010 07:21</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Using &amp;$this can result in some weird and counter-intuitive behaviour - it starts lying to you.<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">class </span><span class="default">Bar<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public </span><span class="default">$prop </span><span class="keyword">= </span><span class="default">42</span><span class="keyword">;<br />
}<br />
<br />
class </span><span class="default">Foo<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public </span><span class="default">$prop </span><span class="keyword">= </span><span class="default">17</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">boom</span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$bar </span><span class="keyword">= &amp;</span><span class="default">$this</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">"\$bar is an alias of \$this, a Foo.\n"</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">'$this is a '</span><span class="keyword">, </span><span class="default">get_class</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">), </span><span class="string">'; $bar is a '</span><span class="keyword">, </span><span class="default">get_class</span><span class="keyword">(</span><span class="default">$bar</span><span class="keyword">), </span><span class="string">"\n"</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">"Are they the same object? "</span><span class="keyword">, (</span><span class="default">$bar </span><span class="keyword">=== </span><span class="default">$this </span><span class="keyword">? </span><span class="string">"Yes\n" </span><span class="keyword">: </span><span class="string">"No\n"</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">"Are they equal? "</span><span class="keyword">, (</span><span class="default">$bar </span><span class="keyword">=== </span><span class="default">$this </span><span class="keyword">? </span><span class="string">"Yes\n" </span><span class="keyword">: </span><span class="string">"No\n"</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">'$this says its prop value is '</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">prop</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">' and $bar says it is '</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="default">$bar</span><span class="keyword">-&gt;</span><span class="default">prop</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">"\n"</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">"\n"</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$bar </span><span class="keyword">= new </span><span class="default">Bar</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">"\$bar has been made into a new Bar.\n"</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">'$this is a '</span><span class="keyword">, </span><span class="default">get_class</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">), </span><span class="string">'; $bar is a '</span><span class="keyword">, </span><span class="default">get_class</span><span class="keyword">(</span><span class="default">$bar</span><span class="keyword">), </span><span class="string">"\n"</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">"Are they the same object? "</span><span class="keyword">, (</span><span class="default">$bar </span><span class="keyword">=== </span><span class="default">$this </span><span class="keyword">? </span><span class="string">"Yes\n" </span><span class="keyword">: </span><span class="string">"No\n"</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">"Are they equal? "</span><span class="keyword">, (</span><span class="default">$bar </span><span class="keyword">=== </span><span class="default">$this </span><span class="keyword">? </span><span class="string">"Yes\n" </span><span class="keyword">: </span><span class="string">"No\n"</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">'$this says its prop value is '</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">prop</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">' and $bar says it is '</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="default">$bar</span><span class="keyword">-&gt;</span><span class="default">prop</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">"\n"</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="default">$t </span><span class="keyword">= new </span><span class="default">Foo</span><span class="keyword">;<br />
</span><span class="default">$t</span><span class="keyword">-&gt;</span><span class="default">boom</span><span class="keyword">();<br />
</span><span class="default">?&gt;<br />
</span>In the above $this claims to be a Bar (in fact it claims to be the very same object that $bar is), while still having all the properties and methods of a Foo.<br />
<br />
Fortunately it doesn't persist beyond the method where you committed the faux pas.<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">echo </span><span class="default">get_class</span><span class="keyword">(</span><span class="default">$t</span><span class="keyword">), </span><span class="string">"\t"</span><span class="keyword">, </span><span class="default">$t</span><span class="keyword">-&gt;</span><span class="default">prop</span><span class="keyword">;<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="95522"></a>
 <div class="note">
  <strong class='user'>miklcct at gmail dot com</strong>
  <a href="#95522" class="date">07-Jan-2010 01:34</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Notes on reference:<br />
A reference is not a pointer. However, an object handle IS a pointer. Example:<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">Foo </span><span class="keyword">{<br />
&nbsp; private static </span><span class="default">$used</span><span class="keyword">;<br />
&nbsp; private </span><span class="default">$id</span><span class="keyword">;<br />
&nbsp; public function </span><span class="default">__construct</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$id </span><span class="keyword">= </span><span class="default">$used</span><span class="keyword">++;<br />
&nbsp; }<br />
&nbsp; public function </span><span class="default">__clone</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$id </span><span class="keyword">= </span><span class="default">$used</span><span class="keyword">++;<br />
&nbsp; }<br />
}<br />
<br />
</span><span class="default">$a </span><span class="keyword">= new </span><span class="default">Foo</span><span class="keyword">; </span><span class="comment">// $a is a pointer pointing to Foo object 0<br />
</span><span class="default">$b </span><span class="keyword">= </span><span class="default">$a</span><span class="keyword">; </span><span class="comment">// $b is a pointer pointing to Foo object 0, however, $b is a copy of $a<br />
</span><span class="default">$c </span><span class="keyword">= &amp;</span><span class="default">$a</span><span class="keyword">; </span><span class="comment">// $c and $a are now references of a pointer pointing to Foo object 0<br />
</span><span class="default">$a </span><span class="keyword">= new </span><span class="default">Foo</span><span class="keyword">; </span><span class="comment">// $a and $c are now references of a pointer pointing to Foo object 1, $b is still a pointer pointing to Foo object 0<br />
</span><span class="keyword">unset(</span><span class="default">$a</span><span class="keyword">); </span><span class="comment">// A reference with reference count 1 is automatically converted back to a value. Now $c is a pointer to Foo object 1<br />
</span><span class="default">$a </span><span class="keyword">= &amp;</span><span class="default">$b</span><span class="keyword">; </span><span class="comment">// $a and $b are now references of a pointer pointing to Foo object 0<br />
</span><span class="default">$a </span><span class="keyword">= </span><span class="default">NULL</span><span class="keyword">; </span><span class="comment">// $a and $b now become a reference to NULL. Foo object 0 can be garbage collected now<br />
</span><span class="keyword">unset(</span><span class="default">$b</span><span class="keyword">); </span><span class="comment">// $b no longer exists and $a is now NULL<br />
</span><span class="default">$a </span><span class="keyword">= clone </span><span class="default">$c</span><span class="keyword">; </span><span class="comment">// $a is now a pointer to Foo object 2, $c remains a pointer to Foo object 1<br />
</span><span class="keyword">unset(</span><span class="default">$c</span><span class="keyword">); </span><span class="comment">// Foo object 1 can be garbage collected now.<br />
</span><span class="default">$c </span><span class="keyword">= </span><span class="default">$a</span><span class="keyword">; </span><span class="comment">// $c and $a are pointers pointing to Foo object 2<br />
</span><span class="keyword">unset(</span><span class="default">$a</span><span class="keyword">); </span><span class="comment">// Foo object 2 is still pointed by $c<br />
</span><span class="default">$a </span><span class="keyword">= &amp;</span><span class="default">$c</span><span class="keyword">; </span><span class="comment">// Foo object 2 has 1 pointers pointing to it only, that pointer has 2 references: $a and $c;<br />
</span><span class="keyword">const </span><span class="default">ABC </span><span class="keyword">= </span><span class="default">TRUE</span><span class="keyword">;<br />
if(</span><span class="default">ABC</span><span class="keyword">) {<br />
&nbsp; </span><span class="default">$a </span><span class="keyword">= </span><span class="default">NULL</span><span class="keyword">; </span><span class="comment">// Foo object 2 can be garbage collected now because $a and $c are now a reference to the same NULL value<br />
</span><span class="keyword">} else {<br />
&nbsp; unset(</span><span class="default">$a</span><span class="keyword">); </span><span class="comment">// Foo object 2 is still pointed to $c<br />
</span><span class="keyword">}</span>
</span>
</code></div>
  </div>
 </div>
 <a name="92955"></a>
 <div class="note">
  <strong class='user'>mjung at poczta dot onet dot pl</strong>
  <a href="#92955" class="date">15-Aug-2009 10:25</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Ultimate explanation to object references:<br />
NOTE: wording 'points to' could be easily replaced with 'refers ' and is used loosly.<br />
<span class="default">&lt;?php<br />
$a1 </span><span class="keyword">= new </span><span class="default">A</span><span class="keyword">(</span><span class="default">1</span><span class="keyword">);&nbsp; </span><span class="comment">// $a1 == handle1-1 to A(1)<br />
</span><span class="default">$a2 </span><span class="keyword">= </span><span class="default">$a1</span><span class="keyword">;&nbsp; &nbsp;&nbsp; </span><span class="comment">// $a2 == handle1-2 to A(1) - assigned by value (copy)<br />
</span><span class="default">$a3 </span><span class="keyword">= &amp;</span><span class="default">$a1</span><span class="keyword">;&nbsp; </span><span class="comment">// $a3 points to $a1 (handle1-1)<br />
</span><span class="default">$a3 </span><span class="keyword">= </span><span class="default">null</span><span class="keyword">;&nbsp; &nbsp; &nbsp; </span><span class="comment">// makes $a1==null, $a3 (still) points to $a1, $a2 == handle1-2 (same object instance A(1))<br />
</span><span class="default">$a2 </span><span class="keyword">= </span><span class="default">null</span><span class="keyword">;&nbsp; &nbsp; &nbsp; </span><span class="comment">// makes $a2 == null<br />
</span><span class="default">$a1 </span><span class="keyword">= new </span><span class="default">A</span><span class="keyword">(</span><span class="default">2</span><span class="keyword">); </span><span class="comment">//makes $a1 == handle2-1 to new object and $a3 (still) points to $a1 =&gt; handle2-1 (new object), so value of $a1 and $a3 is the new object and $a2 == null<br />
//By reference:<br />
</span><span class="default">$a4 </span><span class="keyword">= &amp;new </span><span class="default">A</span><span class="keyword">(</span><span class="default">4</span><span class="keyword">);&nbsp; </span><span class="comment">//$a4 points to handle4-1 to A(4)<br />
</span><span class="default">$a5 </span><span class="keyword">= </span><span class="default">$a4</span><span class="keyword">;&nbsp;&nbsp; </span><span class="comment">// $a5 == handle4-2 to A(4) (copy)<br />
</span><span class="default">$a6 </span><span class="keyword">= &amp;</span><span class="default">$a4</span><span class="keyword">;&nbsp; </span><span class="comment">//$a6 points to (handle4-1), not to $a4 (reference to reference references the referenced object handle4-1 not the reference itself)<br />
<br />
</span><span class="default">$a4 </span><span class="keyword">= &amp;new </span><span class="default">A</span><span class="keyword">(</span><span class="default">40</span><span class="keyword">); </span><span class="comment">// $a4 points to handle40-1, $a5 == handle4-2 and $a6 still points to handle4-1 to A(4)<br />
</span><span class="default">$a6 </span><span class="keyword">= </span><span class="default">null</span><span class="keyword">;&nbsp; </span><span class="comment">// sets handle4-1 to null; $a5 == handle4-2 = A(4); $a4 points to handle40-1; $a6 points to null<br />
</span><span class="default">$a6 </span><span class="keyword">=&amp;</span><span class="default">$a4</span><span class="keyword">; </span><span class="comment">// $a6 points to handle40-1<br />
</span><span class="default">$a7 </span><span class="keyword">= &amp;</span><span class="default">$a6</span><span class="keyword">; </span><span class="comment">//$a7 points to handle40-1<br />
</span><span class="default">$a8 </span><span class="keyword">= &amp;</span><span class="default">$a7</span><span class="keyword">; </span><span class="comment">//$a8 points to handle40-1<br />
</span><span class="default">$a5 </span><span class="keyword">= </span><span class="default">$a7</span><span class="keyword">;&nbsp; </span><span class="comment">//$a5 == handle40-2 (copy)<br />
</span><span class="default">$a6 </span><span class="keyword">= </span><span class="default">null</span><span class="keyword">; </span><span class="comment">//makes handle40-1 null, all variables pointing to (hanlde40-1 ==null) are null, except ($a5 == handle40-2 = A(40))<br />
</span><span class="default">?&gt;<br />
</span>Hope this helps.</span>
</code></div>
  </div>
 </div>
 <a name="91627"></a>
 <div class="note">
  <strong class='user'>Aaron Bond</strong>
  <a href="#91627" class="date">19-Jun-2009 06:08</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I've bumped into a behavior that helped clarify the difference between objects and identifiers for me.<br />
<br />
When we hand off an object variable, we get an identifier to that object's value.&nbsp; This means that if I were to mutate the object from a passed variable, ALL variables originating from that instance of the object will change.&nbsp; <br />
<br />
HOWEVER, if I set that object variable to new instance, it replaces the identifier itself with a new identifier and leaves the old instance in tact.<br />
<br />
Take the following example:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">A </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public </span><span class="default">$foo </span><span class="keyword">= </span><span class="default">1</span><span class="keyword">;<br />
}&nbsp; <br />
<br />
class </span><span class="default">B </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">foo</span><span class="keyword">(</span><span class="default">A $bar</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$bar</span><span class="keyword">-&gt;</span><span class="default">foo </span><span class="keyword">= </span><span class="default">42</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">bar</span><span class="keyword">(</span><span class="default">A $bar</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$bar </span><span class="keyword">= new </span><span class="default">A</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="default">$f </span><span class="keyword">= new </span><span class="default">A</span><span class="keyword">;<br />
</span><span class="default">$g </span><span class="keyword">= new </span><span class="default">B</span><span class="keyword">;<br />
echo </span><span class="default">$f</span><span class="keyword">-&gt;</span><span class="default">foo </span><span class="keyword">. </span><span class="string">"\n"</span><span class="keyword">;<br />
<br />
</span><span class="default">$g</span><span class="keyword">-&gt;</span><span class="default">foo</span><span class="keyword">(</span><span class="default">$f</span><span class="keyword">);<br />
echo </span><span class="default">$f</span><span class="keyword">-&gt;</span><span class="default">foo </span><span class="keyword">. </span><span class="string">"\n"</span><span class="keyword">;<br />
<br />
</span><span class="default">$g</span><span class="keyword">-&gt;</span><span class="default">bar</span><span class="keyword">(</span><span class="default">$f</span><span class="keyword">);<br />
echo </span><span class="default">$f</span><span class="keyword">-&gt;</span><span class="default">foo </span><span class="keyword">. </span><span class="string">"\n"</span><span class="keyword">;<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
If object variables were always references, we'd expect the following output:<br />
1<br />
42<br />
1<br />
<br />
However, we get:<br />
1<br />
42<br />
42<br />
<br />
The reason for this is simple.&nbsp; In the bar function of the B class, we replace the identifier you passed in, which identified the same instance of the A class as your $f variable, with a brand new A class identifier.&nbsp; Creating a new instance of A doesn't mutate $f because $f wasn't passed as a reference.<br />
<br />
To get the reference behavior, one would have to enter the following for class B:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">B </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">foo</span><span class="keyword">(</span><span class="default">A $bar</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$bar</span><span class="keyword">-&gt;</span><span class="default">foo </span><span class="keyword">= </span><span class="default">42</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">bar</span><span class="keyword">(</span><span class="default">A </span><span class="keyword">&amp;</span><span class="default">$bar</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$bar </span><span class="keyword">= new </span><span class="default">A</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
The foo function doesn't require a reference, because it is MUTATING an object instance that $bar identifies.&nbsp; But bar will be REPLACING the object instance.&nbsp; If only an identifier is passed, the variable identifier will be overwritten but the object instance will be left in place.</span>
</code></div>
  </div>
 </div>
 <a name="86374"></a>
 <div class="note">
  <strong class='user'>Ivan Bertona</strong>
  <a href="#86374" class="date">15-Oct-2008 08:45</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
A point that in my opinion is not stressed enough in the manual page is that in PHP5, passing an object as an argument of a function call with no use of the &amp; operator means passing BY VALUE an unique identifier for that object (intended as instance of a class), which will be stored in another variable that has function scope.<br />
<br />
This behaviour is the same used in Java, where indeed there is no notion of passing arguments by reference. On the other hand, in PHP you can pass a value by reference (in PHP we refer to references as "aliases"), and this poses a threat if you are not aware of what you are really doing. Please consider these two classes:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">A <br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">__toString</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="string">"Class A"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
&nbsp;&nbsp;&nbsp; <br />
class </span><span class="default">B<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">__toString</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="string">"Class B"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
In the first test case we make two objects out of the classes A and B, then swap the variables using a temp one and the normal assignment operator (=).<br />
<br />
<span class="default">&lt;?php<br />
$a </span><span class="keyword">= new </span><span class="default">A</span><span class="keyword">();<br />
</span><span class="default">$b </span><span class="keyword">= new </span><span class="default">B</span><span class="keyword">();<br />
&nbsp;&nbsp;&nbsp; <br />
</span><span class="default">$temp </span><span class="keyword">= </span><span class="default">$a</span><span class="keyword">;<br />
</span><span class="default">$a </span><span class="keyword">= </span><span class="default">$b</span><span class="keyword">;<br />
</span><span class="default">$b </span><span class="keyword">= </span><span class="default">$temp</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; <br />
print(</span><span class="string">'$a: ' </span><span class="keyword">. </span><span class="default">$a </span><span class="keyword">. </span><span class="string">"\n"</span><span class="keyword">);<br />
print(</span><span class="string">'$b: ' </span><span class="keyword">. </span><span class="default">$b </span><span class="keyword">. </span><span class="string">"\n"</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span><br />
As expected the script will output:<br />
<br />
$a: Class B<br />
$b: Class A<br />
<br />
Now consider the following snippet. It is similar to the former but the assignment $a = &amp;$b makes $a an ALIAS of $b.<br />
<br />
<span class="default">&lt;?php<br />
$a </span><span class="keyword">= new </span><span class="default">A</span><span class="keyword">();<br />
</span><span class="default">$b </span><span class="keyword">= new </span><span class="default">B</span><span class="keyword">();<br />
&nbsp;&nbsp;&nbsp; <br />
</span><span class="default">$temp </span><span class="keyword">= </span><span class="default">$a</span><span class="keyword">;<br />
</span><span class="default">$a </span><span class="keyword">= &amp;</span><span class="default">$b</span><span class="keyword">;<br />
</span><span class="default">$b </span><span class="keyword">= </span><span class="default">$temp</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; <br />
print(</span><span class="string">'$a: ' </span><span class="keyword">. </span><span class="default">$a </span><span class="keyword">. </span><span class="string">"\n"</span><span class="keyword">);<br />
print(</span><span class="string">'$b: ' </span><span class="keyword">. </span><span class="default">$b </span><span class="keyword">. </span><span class="string">"\n"</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span><br />
This script will output:<br />
<br />
$a: Class A<br />
$b: Class A<br />
<br />
That is, modifying $b reflects the same assignment on $a... The two variables end pointing to the same object, and the other one is lost. To sum up is a good practice NOT using aliasing when handling PHP5 objects, unless your are really really sure of what you are doing.</span>
</code></div>
  </div>
 </div>
 <a name="86050"></a>
 <div class="note">
  <strong class='user'>lazybones_senior</strong>
  <a href="#86050" class="date">30-Sep-2008 06:57</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
WHOA... KEEP IT SIMPLE!<br />
<br />
In regards to secure_admin's note: You've used OOP to simplify PHP's ability to create and use object references. Now use PHP's static keyword to simplify your OOP.<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">class </span><span class="default">DataModelControl </span><span class="keyword">{<br />
&nbsp; protected static </span><span class="default">$data </span><span class="keyword">= </span><span class="default">256</span><span class="keyword">; </span><span class="comment">// default value; <br />
&nbsp; </span><span class="keyword">protected </span><span class="default">$name</span><span class="keyword">;<br />
<br />
&nbsp; public function </span><span class="default">__construct</span><span class="keyword">(</span><span class="default">$dmcName</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">name </span><span class="keyword">= </span><span class="default">$dmcName</span><span class="keyword">;<br />
&nbsp; }<br />
<br />
&nbsp; public static function </span><span class="default">setData</span><span class="keyword">(</span><span class="default">$dmcData</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; if(</span><span class="default">is_numeric</span><span class="keyword">(</span><span class="default">$dmcData</span><span class="keyword">)) {<br />
&nbsp;&nbsp; &nbsp;&nbsp; </span><span class="default">self</span><span class="keyword">::</span><span class="default">$data </span><span class="keyword">= </span><span class="default">$dmcData</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp; }<br />
<br />
&nbsp; public function </span><span class="default">__toString</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; return </span><span class="string">"DataModelControl [name=$this-&gt;name, data=" </span><span class="keyword">. </span><span class="default">self</span><span class="keyword">::</span><span class="default">$data </span><span class="keyword">. </span><span class="string">"]"</span><span class="keyword">;<br />
&nbsp; }&nbsp;&nbsp; <br />
}<br />
<br />
</span><span class="comment"># create several instances of DataModelControl...<br />
</span><span class="default">$dmc1 </span><span class="keyword">= new </span><span class="default">DataModelControl</span><span class="keyword">(</span><span class="string">'dmc1'</span><span class="keyword">);<br />
</span><span class="default">$dmc2 </span><span class="keyword">= new </span><span class="default">DataModelControl</span><span class="keyword">(</span><span class="string">'dmc2'</span><span class="keyword">);<br />
</span><span class="default">$dmc3 </span><span class="keyword">= new </span><span class="default">DataModelControl</span><span class="keyword">(</span><span class="string">'dmc3'</span><span class="keyword">);<br />
echo </span><span class="default">$dmc1 </span><span class="keyword">. </span><span class="string">'&lt;br&gt;'</span><span class="keyword">;<br />
echo </span><span class="default">$dmc2 </span><span class="keyword">. </span><span class="string">'&lt;br&gt;'</span><span class="keyword">;<br />
echo </span><span class="default">$dmc3 </span><span class="keyword">. </span><span class="string">'&lt;br&gt;&lt;br&gt;'</span><span class="keyword">;<br />
<br />
</span><span class="comment"># To change data, use any DataModelControl object...<br />
</span><span class="default">$dmc2</span><span class="keyword">-&gt;</span><span class="default">setData</span><span class="keyword">(</span><span class="default">512</span><span class="keyword">);<br />
</span><span class="comment"># Or, call setData() directly from the class...<br />
</span><span class="default">DataModelControl</span><span class="keyword">::</span><span class="default">setData</span><span class="keyword">(</span><span class="default">1024</span><span class="keyword">);<br />
echo </span><span class="default">$dmc1 </span><span class="keyword">. </span><span class="string">'&lt;br&gt;'</span><span class="keyword">;<br />
echo </span><span class="default">$dmc2 </span><span class="keyword">. </span><span class="string">'&lt;br&gt;'</span><span class="keyword">;<br />
echo </span><span class="default">$dmc3 </span><span class="keyword">. </span><span class="string">'&lt;br&gt;&lt;br&gt;'</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
&nbsp;DataModelControl [name=dmc1, data=256]<br />
&nbsp;DataModelControl [name=dmc2, data=256]<br />
&nbsp;DataModelControl [name=dmc3, data=256]<br />
<br />
&nbsp;DataModelControl [name=dmc1, data=1024]<br />
&nbsp;DataModelControl [name=dmc2, data=1024]<br />
&nbsp;DataModelControl [name=dmc3, data=1024]<br />
<br />
... even better! Now, PHP creates one copy of $data, that is shared amongst all DataModelControl objects.</span>
</code></div>
  </div>
 </div>
 <a name="86047"></a>
 <div class="note">
  <strong class='user'>secure_admin</strong>
  <a href="#86047" class="date">30-Sep-2008 04:38</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
USE OOP to ACCESS OBJECT REFERENCES<br />
<br />
The PHP language itself offers a slew of nifty operators that can copy, clone, and alias objects and references in many ways. But that kind of syntax looks rather fearsome. Here, I use OOP to get the same results, but with cleaner and more practical code. Below, one DataModel object is instantiated so that many instances of DataControl can use and alter it. Regardless of how PHP works, the OOP styled setup keeps all DataControl instances "on the same page" because they are all looking at the "same model" - which this code clearly shows.<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">class </span><span class="default">DataModel </span><span class="keyword">{<br />
&nbsp; protected </span><span class="default">$name</span><span class="keyword">, </span><span class="default">$data</span><span class="keyword">;<br />
<br />
&nbsp; public function </span><span class="default">__construct</span><span class="keyword">(</span><span class="default">$dmName</span><span class="keyword">, </span><span class="default">$dmData</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">name </span><span class="keyword">= </span><span class="default">$dmName</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">setData</span><span class="keyword">(</span><span class="default">$dmData</span><span class="keyword">);<br />
&nbsp; }<br />
<br />
&nbsp; public function </span><span class="default">setData</span><span class="keyword">(</span><span class="default">$dmData</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; if(</span><span class="default">is_numeric</span><span class="keyword">(</span><span class="default">$dmData</span><span class="keyword">)) {<br />
&nbsp;&nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">data </span><span class="keyword">= </span><span class="default">$dmData</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp; }<br />
<br />
&nbsp; public function </span><span class="default">__toString</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; return </span><span class="string">"DataModel [name=$this-&gt;name, data=$this-&gt;data]"</span><span class="keyword">;<br />
&nbsp; }&nbsp;&nbsp; <br />
}<br />
<br />
class </span><span class="default">DataControl </span><span class="keyword">{<br />
&nbsp; protected </span><span class="default">$name</span><span class="keyword">, </span><span class="default">$model</span><span class="keyword">;<br />
<br />
&nbsp; public function </span><span class="default">__construct</span><span class="keyword">(</span><span class="default">$dcName</span><span class="keyword">, </span><span class="default">$dcModel</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">name </span><span class="keyword">= </span><span class="default">$dcName</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">model </span><span class="keyword">= </span><span class="default">$dcModel</span><span class="keyword">;<br />
&nbsp; }<br />
<br />
&nbsp; public function </span><span class="default">setData</span><span class="keyword">(</span><span class="default">$dmData</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">model</span><span class="keyword">-&gt;</span><span class="default">setData</span><span class="keyword">(</span><span class="default">$dmData</span><span class="keyword">);<br />
&nbsp; }<br />
<br />
&nbsp; public function </span><span class="default">__toString</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; return </span><span class="string">"DataController [name=$this-&gt;name, model=" </span><span class="keyword">. </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">model</span><span class="keyword">-&gt;</span><span class="default">__toString</span><span class="keyword">() . </span><span class="string">"]"</span><span class="keyword">;<br />
&nbsp; }<br />
}<br />
<br />
</span><span class="comment"># create one instance of DataModel...<br />
</span><span class="default">$model </span><span class="keyword">= new </span><span class="default">DataModel</span><span class="keyword">(</span><span class="string">'dm1'</span><span class="keyword">, </span><span class="default">128</span><span class="keyword">);<br />
echo </span><span class="default">$model </span><span class="keyword">. </span><span class="string">'&lt;br&gt;&lt;br&gt;'</span><span class="keyword">;<br />
<br />
</span><span class="comment"># create several instances of DataControl, passing $model to each one...<br />
</span><span class="default">$dc1 </span><span class="keyword">= new </span><span class="default">DataControl</span><span class="keyword">(</span><span class="string">'dc1'</span><span class="keyword">, </span><span class="default">$model</span><span class="keyword">);<br />
</span><span class="default">$dc2 </span><span class="keyword">= new </span><span class="default">DataControl</span><span class="keyword">(</span><span class="string">'dc2'</span><span class="keyword">, </span><span class="default">$model</span><span class="keyword">);<br />
</span><span class="default">$dc3 </span><span class="keyword">= new </span><span class="default">DataControl</span><span class="keyword">(</span><span class="string">'dc3'</span><span class="keyword">, </span><span class="default">$model</span><span class="keyword">);<br />
echo </span><span class="default">$dc1 </span><span class="keyword">. </span><span class="string">'&lt;br&gt;'</span><span class="keyword">;<br />
echo </span><span class="default">$dc2 </span><span class="keyword">. </span><span class="string">'&lt;br&gt;'</span><span class="keyword">;<br />
echo </span><span class="default">$dc3 </span><span class="keyword">. </span><span class="string">'&lt;br&gt;&lt;br&gt;'</span><span class="keyword">;<br />
<br />
</span><span class="comment"># To change data, use any $dataControl-&gt;setData()...<br />
</span><span class="default">$dc3</span><span class="keyword">-&gt;</span><span class="default">setData</span><span class="keyword">(</span><span class="default">512</span><span class="keyword">);<br />
<br />
echo </span><span class="default">$dc1 </span><span class="keyword">. </span><span class="string">'&lt;br&gt;'</span><span class="keyword">;<br />
echo </span><span class="default">$dc2 </span><span class="keyword">. </span><span class="string">'&lt;br&gt;'</span><span class="keyword">;<br />
echo </span><span class="default">$dc3 </span><span class="keyword">. </span><span class="string">'&lt;br&gt;&lt;br&gt;'</span><span class="keyword">;<br />
<br />
</span><span class="default">?&gt;<br />
</span> * * * output * * *<br />
DataModel [name=dm1, data=128]<br />
<br />
DataController [name=dc1, model=DataModel [name=dm1, data=128]]<br />
DataController [name=dc2, model=DataModel [name=dm1, data=128]]<br />
DataController [name=dc3, model=DataModel [name=dm1, data=128]]<br />
<br />
DataController [name=dc1, model=DataModel [name=dm1, data=512]]<br />
DataController [name=dc2, model=DataModel [name=dm1, data=512]]<br />
DataController [name=dc3, model=DataModel [name=dm1, data=512]]</span>
</code></div>
  </div>
 </div>
 <a name="86042"></a>
 <div class="note">
  <strong class='user'>wbcarts at juno dot com</strong>
  <a href="#86042" class="date">30-Sep-2008 01:23</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
A BIT DILUTED... but it's alright!<br />
<br />
In the PHP example above, the function foo($obj), will actually create a $foo property to "any object" passed to it - which brings some confusion to me:<br />
&nbsp; $obj = new stdClass();<br />
&nbsp; foo($obj);&nbsp; &nbsp; // tags on a $foo property to the object<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; // why is this method here?<br />
Furthermore, in OOP, it is not a good idea for "global functions" to operate on an object's properties... and it is not a good idea for your class objects to let them. To illustrate the point, the example should be:<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">class </span><span class="default">A </span><span class="keyword">{<br />
&nbsp; protected </span><span class="default">$foo </span><span class="keyword">= </span><span class="default">1</span><span class="keyword">;<br />
<br />
&nbsp; public function </span><span class="default">getFoo</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">foo</span><span class="keyword">;<br />
&nbsp; }<br />
<br />
&nbsp; public function </span><span class="default">setFoo</span><span class="keyword">(</span><span class="default">$val</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; if(</span><span class="default">$val </span><span class="keyword">&gt; </span><span class="default">0 </span><span class="keyword">&amp;&amp; </span><span class="default">$val </span><span class="keyword">&lt; </span><span class="default">10</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">foo </span><span class="keyword">= </span><span class="default">$val</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp; }<br />
<br />
&nbsp; public function </span><span class="default">__toString</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; return </span><span class="string">"A [foo=$this-&gt;foo]"</span><span class="keyword">;<br />
&nbsp; }<br />
}<br />
<br />
</span><span class="default">$a </span><span class="keyword">= new </span><span class="default">A</span><span class="keyword">();<br />
</span><span class="default">$b </span><span class="keyword">= </span><span class="default">$a</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="comment">// $a and $b are copies of the same identifier<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; // ($a) = ($b) = &lt;id&gt;<br />
</span><span class="default">$b</span><span class="keyword">-&gt;</span><span class="default">setFoo</span><span class="keyword">(</span><span class="default">2</span><span class="keyword">);<br />
echo </span><span class="default">$a</span><span class="keyword">-&gt;</span><span class="default">getFoo</span><span class="keyword">() . </span><span class="string">'&lt;br&gt;'</span><span class="keyword">;<br />
<br />
</span><span class="default">$c </span><span class="keyword">= new </span><span class="default">A</span><span class="keyword">();<br />
</span><span class="default">$d </span><span class="keyword">= &amp;</span><span class="default">$c</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// $c and $d are references<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; // ($c,$d) = &lt;id&gt;<br />
</span><span class="default">$d</span><span class="keyword">-&gt;</span><span class="default">setFoo</span><span class="keyword">(</span><span class="default">2</span><span class="keyword">);<br />
echo </span><span class="default">$c </span><span class="keyword">. </span><span class="string">'&lt;br&gt;'</span><span class="keyword">;<br />
<br />
</span><span class="default">$e </span><span class="keyword">= new </span><span class="default">A</span><span class="keyword">();<br />
</span><span class="default">$e</span><span class="keyword">-&gt;</span><span class="default">setFoo</span><span class="keyword">(</span><span class="default">16</span><span class="keyword">);&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// will be ignored<br />
</span><span class="keyword">echo </span><span class="default">$e</span><span class="keyword">;<br />
<br />
</span><span class="default">?&gt;<br />
</span> - - -<br />
&nbsp;2<br />
&nbsp;A [foo=2]<br />
&nbsp;A [foo=1]<br />
&nbsp;- - -<br />
Because the global function foo() has been deleted, class A is more defined, robust and will handle all foo operations... and only for objects of type A. I can now take it for granted and see clearly that your are talking about "A" objects and their references. But it still reminds me too much of cloning and object comparisons, which to me borders on machine-like programming and not object-oriented programming, which is a totally different way to think.</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=language.oop5.references&amp;redirect=http://www.php.net/manual/en/language.oop5.references.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.oop5.references&amp;redirect=http://www.php.net/manual/en/language.oop5.references.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/language.oop5.references.php">show source</a> |
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