<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Comparing Objects - Manual</title>
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
 <link rel="prev" href="language.oop5.cloning.php" />
 <link rel="next" href="language.oop5.typehinting.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/oop5.object-comparison" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/language.oop5.object-comparison.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="@w{ZAGA2MN9}" />
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
 <li class="active"><a href="language.oop5.object-comparison.php">Comparing Objects</a></li>
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
  <a href="language.oop5.typehinting.php">Type Hinting<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.oop5.cloning.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Object Cloning</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.oop5.object-comparison.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/language.oop5.object-comparison.php">Brazilian Portuguese</option>
    <option value="zh/language.oop5.object-comparison.php">Chinese (Simplified)</option>
    <option value="fr/language.oop5.object-comparison.php">French</option>
    <option value="de/language.oop5.object-comparison.php">German</option>
    <option value="ja/language.oop5.object-comparison.php">Japanese</option>
    <option value="pl/language.oop5.object-comparison.php">Polish</option>
    <option value="ro/language.oop5.object-comparison.php">Romanian</option>
    <option value="ru/language.oop5.object-comparison.php">Russian</option>
    <option value="fa/language.oop5.object-comparison.php">Persian</option>
    <option value="es/language.oop5.object-comparison.php">Spanish</option>
    <option value="tr/language.oop5.object-comparison.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="language.oop5.object-comparison" class="sect1">
   <h2 class="title">Comparing Objects</h2>
   <p class="para">
    In PHP 5, object comparison is more complicated than in PHP 4 and more
    in accordance to what one will expect from an Object Oriented Language
    (not that PHP 5 is such a language).
   </p>
   <p class="para">
    When using the comparison operator (<em>==</em>), 
    object variables are compared in a simple manner, namely: Two object
    instances are equal if they have the same attributes and values, and are
    instances of the same class.
   </p>
   <p class="para">
    On the other hand, when using the identity operator (<em>===</em>),
    object variables are identical if and only if they refer to the same
    instance of the same class.
   </p>
   <p class="para">
    An example will clarify these rules.
    <div class="example" id="example-219">
     <p><strong>Example #1 Example of object comparison in PHP 5</strong></p>
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">function&nbsp;</span><span style="color: #0000BB">bool2str</span><span style="color: #007700">(</span><span style="color: #0000BB">$bool</span><span style="color: #007700">)<br />{<br />&nbsp;&nbsp;&nbsp;&nbsp;if&nbsp;(</span><span style="color: #0000BB">$bool&nbsp;</span><span style="color: #007700">===&nbsp;</span><span style="color: #0000BB">false</span><span style="color: #007700">)&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;return&nbsp;</span><span style="color: #DD0000">'FALSE'</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}&nbsp;else&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;return&nbsp;</span><span style="color: #DD0000">'TRUE'</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br /><br />function&nbsp;</span><span style="color: #0000BB">compareObjects</span><span style="color: #007700">(&amp;</span><span style="color: #0000BB">$o1</span><span style="color: #007700">,&nbsp;&amp;</span><span style="color: #0000BB">$o2</span><span style="color: #007700">)<br />{<br />&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">'o1&nbsp;==&nbsp;o2&nbsp;:&nbsp;'&nbsp;</span><span style="color: #007700">.&nbsp;</span><span style="color: #0000BB">bool2str</span><span style="color: #007700">(</span><span style="color: #0000BB">$o1&nbsp;</span><span style="color: #007700">==&nbsp;</span><span style="color: #0000BB">$o2</span><span style="color: #007700">)&nbsp;.&nbsp;</span><span style="color: #DD0000">"\n"</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">'o1&nbsp;!=&nbsp;o2&nbsp;:&nbsp;'&nbsp;</span><span style="color: #007700">.&nbsp;</span><span style="color: #0000BB">bool2str</span><span style="color: #007700">(</span><span style="color: #0000BB">$o1&nbsp;</span><span style="color: #007700">!=&nbsp;</span><span style="color: #0000BB">$o2</span><span style="color: #007700">)&nbsp;.&nbsp;</span><span style="color: #DD0000">"\n"</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">'o1&nbsp;===&nbsp;o2&nbsp;:&nbsp;'&nbsp;</span><span style="color: #007700">.&nbsp;</span><span style="color: #0000BB">bool2str</span><span style="color: #007700">(</span><span style="color: #0000BB">$o1&nbsp;</span><span style="color: #007700">===&nbsp;</span><span style="color: #0000BB">$o2</span><span style="color: #007700">)&nbsp;.&nbsp;</span><span style="color: #DD0000">"\n"</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">'o1&nbsp;!==&nbsp;o2&nbsp;:&nbsp;'&nbsp;</span><span style="color: #007700">.&nbsp;</span><span style="color: #0000BB">bool2str</span><span style="color: #007700">(</span><span style="color: #0000BB">$o1&nbsp;</span><span style="color: #007700">!==&nbsp;</span><span style="color: #0000BB">$o2</span><span style="color: #007700">)&nbsp;.&nbsp;</span><span style="color: #DD0000">"\n"</span><span style="color: #007700">;<br />}<br /><br />class&nbsp;</span><span style="color: #0000BB">Flag<br /></span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;</span><span style="color: #0000BB">$flag</span><span style="color: #007700">;<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;function&nbsp;</span><span style="color: #0000BB">Flag</span><span style="color: #007700">(</span><span style="color: #0000BB">$flag&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">true</span><span style="color: #007700">)&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">flag&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">$flag</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br /><br />class&nbsp;</span><span style="color: #0000BB">OtherFlag<br /></span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;</span><span style="color: #0000BB">$flag</span><span style="color: #007700">;<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;function&nbsp;</span><span style="color: #0000BB">OtherFlag</span><span style="color: #007700">(</span><span style="color: #0000BB">$flag&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">true</span><span style="color: #007700">)&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">flag&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">$flag</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br /><br /></span><span style="color: #0000BB">$o&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">Flag</span><span style="color: #007700">();<br /></span><span style="color: #0000BB">$p&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">Flag</span><span style="color: #007700">();<br /></span><span style="color: #0000BB">$q&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">$o</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">$r&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">OtherFlag</span><span style="color: #007700">();<br /><br />echo&nbsp;</span><span style="color: #DD0000">"Two&nbsp;instances&nbsp;of&nbsp;the&nbsp;same&nbsp;class\n"</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">compareObjects</span><span style="color: #007700">(</span><span style="color: #0000BB">$o</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$p</span><span style="color: #007700">);<br /><br />echo&nbsp;</span><span style="color: #DD0000">"\nTwo&nbsp;references&nbsp;to&nbsp;the&nbsp;same&nbsp;instance\n"</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">compareObjects</span><span style="color: #007700">(</span><span style="color: #0000BB">$o</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$q</span><span style="color: #007700">);<br /><br />echo&nbsp;</span><span style="color: #DD0000">"\nInstances&nbsp;of&nbsp;two&nbsp;different&nbsp;classes\n"</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">compareObjects</span><span style="color: #007700">(</span><span style="color: #0000BB">$o</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$r</span><span style="color: #007700">);<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

     <div class="example-contents"><p>The above example will output:</p></div>
     <div class="example-contents screen">
<div class="cdata"><pre>
Two instances of the same class
o1 == o2 : TRUE
o1 != o2 : FALSE
o1 === o2 : FALSE
o1 !== o2 : TRUE

Two references to the same instance
o1 == o2 : TRUE
o1 != o2 : FALSE
o1 === o2 : TRUE
o1 !== o2 : FALSE

Instances of two different classes
o1 == o2 : FALSE
o1 != o2 : TRUE
o1 === o2 : FALSE
o1 !== o2 : TRUE
</pre></div>
     </div>
    </div>
   </p>
   <blockquote class="note"><p><strong class="note">Note</strong>: 
    <p class="para">
     Extensions can define own rules for their objects comparison.
    </p>
   </p></blockquote>
  </div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="language.oop5.typehinting.php">Type Hinting<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.oop5.cloning.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Object Cloning</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.oop5.object-comparison.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=language.oop5.object-comparison&amp;redirect=@w{ZAGA2MN9}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.oop5.object-comparison&amp;redirect=@w{ZAGA2MN9}">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Comparing Objects</strong>
 </div><div id="allnotes">
 <a name="106562"></a>
 <div class="note">
  <strong class='user'>f at francislacroix dot info</strong>
  <a href="#106562" class="date">18-Nov-2011 11:36</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
It should be noted that objects can be compared using only their accessible properties using get_object_vars().<br />
<br />
Per example, in this class:<br />
<br />
class X {<br />
&nbsp;&nbsp;&nbsp; public $a;<br />
&nbsp;&nbsp;&nbsp; private $b;<br />
&nbsp;&nbsp;&nbsp; public function __construct($a, $b) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; $this-&gt;a = $a;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; $this-&gt;b = $b;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
You can ignore the private properties when comparing them outside the class scope, like this:<br />
<br />
$a = new X(1, 1);<br />
$b = new X(1, 0);<br />
var_dump(get_object_vars($a) == get_object_vars($b));<br />
<br />
Just remember that get_object_vars() return different properties depending on the scope where you call it, so calling the above within one of X member will compare the private properties.</span>
</code></div>
  </div>
 </div>
 <a name="103125"></a>
 <div class="note">
  <strong class='user'>d dot e dot pope at gmail dot com</strong>
  <a href="#103125" class="date">27-Mar-2011 12:27</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Note that ALL object fields, public and private, are used in the equivalence check.&nbsp; If you want public-only equivalence you'll have to roll your own.<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">function </span><span class="default">bool2str</span><span class="keyword">(</span><span class="default">$bool</span><span class="keyword">)<br />
{<br />
&nbsp;&nbsp;&nbsp; if (</span><span class="default">$bool </span><span class="keyword">=== </span><span class="default">false</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="string">'FALSE'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; } else {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="string">'TRUE'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
class </span><span class="default">PubPrivFlag<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public </span><span class="default">$pubFlag</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; private </span><span class="default">$privFlag</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">__construct</span><span class="keyword">(</span><span class="default">$pub</span><span class="keyword">, </span><span class="default">$priv</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">pubFlag </span><span class="keyword">= </span><span class="default">$pub</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">privFlag </span><span class="keyword">= </span><span class="default">$priv</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
echo </span><span class="string">"Two instances of the same class with identical private fields\n"</span><span class="keyword">;<br />
</span><span class="default">$s </span><span class="keyword">= new </span><span class="default">PubPrivFlag</span><span class="keyword">(</span><span class="default">true</span><span class="keyword">, </span><span class="default">true</span><span class="keyword">);<br />
</span><span class="default">$t </span><span class="keyword">= new </span><span class="default">PubPrivFlag</span><span class="keyword">(</span><span class="default">true</span><span class="keyword">, </span><span class="default">true</span><span class="keyword">);<br />
echo </span><span class="string">'o1 == o2 : ' </span><span class="keyword">. </span><span class="default">bool2str</span><span class="keyword">(</span><span class="default">$s </span><span class="keyword">== </span><span class="default">$t</span><span class="keyword">) . </span><span class="string">"\n"</span><span class="keyword">;<br />
<br />
echo </span><span class="string">"Two instances of the same class with different private fields\n"</span><span class="keyword">;<br />
</span><span class="default">$s </span><span class="keyword">= new </span><span class="default">PubPrivFlag</span><span class="keyword">(</span><span class="default">true</span><span class="keyword">, </span><span class="default">true</span><span class="keyword">);<br />
</span><span class="default">$t </span><span class="keyword">= new </span><span class="default">PubPrivFlag</span><span class="keyword">(</span><span class="default">true</span><span class="keyword">, </span><span class="default">false</span><span class="keyword">);<br />
echo </span><span class="string">'o1 == o2 : ' </span><span class="keyword">. </span><span class="default">bool2str</span><span class="keyword">(</span><span class="default">$s </span><span class="keyword">== </span><span class="default">$t</span><span class="keyword">) . </span><span class="string">"\n"</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
This will output the following:<br />
<br />
Two instances of the same class with identical private fields<br />
o1 == o2 : TRUE<br />
Two instances of the same class with different private fields<br />
o1 == o2 : FALSE</span>
</code></div>
  </div>
 </div>
 <a name="102204"></a>
 <div class="note">
  <strong class='user'>SjH</strong>
  <a href="#102204" class="date">02-Feb-2011 04:15</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Interestingly enough due to the operator precidences changing the values of identical objects may not be as obvious as expected.<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">A </span><span class="keyword">{<br />
<br />
&nbsp;&nbsp;&nbsp; private </span><span class="default">$val</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">set</span><span class="keyword">(</span><span class="default">$val</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">val </span><span class="keyword">= </span><span class="default">$val</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$this</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; }<br />
<br />
}<br />
<br />
</span><span class="default">$a </span><span class="keyword">= new </span><span class="default">A</span><span class="keyword">;<br />
</span><span class="default">$b </span><span class="keyword">= new </span><span class="default">A</span><span class="keyword">;<br />
</span><span class="default">$c </span><span class="keyword">= </span><span class="default">$a</span><span class="keyword">;<br />
<br />
echo </span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$a</span><span class="keyword">-&gt;</span><span class="default">set</span><span class="keyword">(</span><span class="string">'foo'</span><span class="keyword">) == </span><span class="default">$b</span><span class="keyword">-&gt;</span><span class="default">set</span><span class="keyword">(</span><span class="string">'bar'</span><span class="keyword">)); <br />
</span><span class="comment">// Will evaluate as boolean false<br />
<br />
</span><span class="keyword">echo </span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$a</span><span class="keyword">-&gt;</span><span class="default">set</span><span class="keyword">(</span><span class="string">'foo'</span><span class="keyword">) == </span><span class="default">$c</span><span class="keyword">-&gt;</span><span class="default">set</span><span class="keyword">(</span><span class="string">'bar'</span><span class="keyword">));<br />
</span><span class="comment">// Will evaluate a boolean true<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="98725"></a>
 <div class="note">
  <strong class='user'>Anonymous</strong>
  <a href="#98725" class="date">02-Jul-2010 09:00</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Comparison using &lt;&gt; operators should be documented.&nbsp; Between two objects, at least in PHP5.3, the comparison operation stops and returns at the first unequal property found.<br />
<br />
<span class="default">&lt;?php<br />
<br />
$o1 </span><span class="keyword">= new </span><span class="default">stdClass</span><span class="keyword">();<br />
</span><span class="default">$o1</span><span class="keyword">-&gt;</span><span class="default">prop1 </span><span class="keyword">= </span><span class="string">'c'</span><span class="keyword">;<br />
</span><span class="default">$o1</span><span class="keyword">-&gt;</span><span class="default">prop2 </span><span class="keyword">= </span><span class="default">25</span><span class="keyword">;<br />
</span><span class="default">$o1</span><span class="keyword">-&gt;</span><span class="default">prop3 </span><span class="keyword">= </span><span class="default">201</span><span class="keyword">;<br />
</span><span class="default">$o1</span><span class="keyword">-&gt;</span><span class="default">prop4 </span><span class="keyword">= </span><span class="default">1000</span><span class="keyword">;<br />
<br />
</span><span class="default">$o2 </span><span class="keyword">= new </span><span class="default">stdClass</span><span class="keyword">();<br />
</span><span class="default">$o2</span><span class="keyword">-&gt;</span><span class="default">prop1 </span><span class="keyword">= </span><span class="string">'c'</span><span class="keyword">;<br />
</span><span class="default">$o2</span><span class="keyword">-&gt;</span><span class="default">prop2 </span><span class="keyword">= </span><span class="default">25</span><span class="keyword">;<br />
</span><span class="default">$o2</span><span class="keyword">-&gt;</span><span class="default">prop3 </span><span class="keyword">= </span><span class="default">200</span><span class="keyword">;<br />
</span><span class="default">$o2</span><span class="keyword">-&gt;</span><span class="default">prop4 </span><span class="keyword">= </span><span class="default">9999</span><span class="keyword">;<br />
<br />
echo (int)(</span><span class="default">$o1 </span><span class="keyword">&lt; </span><span class="default">$o2</span><span class="keyword">); </span><span class="comment">// 0<br />
</span><span class="keyword">echo (int)(</span><span class="default">$o1 </span><span class="keyword">&gt; </span><span class="default">$o2</span><span class="keyword">); </span><span class="comment">// 1<br />
<br />
</span><span class="default">$o1</span><span class="keyword">-&gt;</span><span class="default">prop3 </span><span class="keyword">= </span><span class="default">200</span><span class="keyword">;<br />
<br />
echo (int)(</span><span class="default">$o1 </span><span class="keyword">&lt; </span><span class="default">$o2</span><span class="keyword">); </span><span class="comment">// 1<br />
</span><span class="keyword">echo (int)(</span><span class="default">$o1 </span><span class="keyword">&gt; </span><span class="default">$o2</span><span class="keyword">); </span><span class="comment">// 0<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="93968"></a>
 <div class="note">
  <strong class='user'>RPaseur at NationalPres dot org</strong>
  <a href="#93968" class="date">08-Oct-2009 04:09</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
SimpleXML Objects are different, even if made from the same XML.<br />
<br />
<span class="default">&lt;?php </span><span class="comment">// RAY_SimpleXML_compare.php<br />
</span><span class="default">error_reporting</span><span class="keyword">(</span><span class="default">E_ALL</span><span class="keyword">);<br />
echo </span><span class="string">"&lt;pre&gt;\n"</span><span class="keyword">;<br />
<br />
</span><span class="comment">// TWO SimpleXML OBJECTS ARE NOT EQUAL WITH COMPARISON OPERATORS.&nbsp; PHP 5.2.10<br />
<br />
// AN XML STRING<br />
</span><span class="default">$xml </span><span class="keyword">= </span><span class="string">'&lt;?xml version="1.0" encoding="utf-8"?&gt;<br />
&lt;thing&gt;<br />
&nbsp; &lt;number&gt;123456&lt;/number&gt;<br />
&nbsp; &lt;email&gt;user@example.com&lt;/email&gt;<br />
&nbsp; &lt;state&gt;CA&lt;/state&gt;<br />
&lt;/thing&gt;'</span><span class="keyword">;<br />
<br />
</span><span class="comment">// SHOW THE XML STRING<br />
</span><span class="keyword">echo </span><span class="default">htmlentities</span><span class="keyword">(</span><span class="default">$xml</span><span class="keyword">);<br />
<br />
</span><span class="comment">// MAKE TWO OBJECTS<br />
</span><span class="default">$obj1 </span><span class="keyword">= </span><span class="default">SimpleXML_Load_String</span><span class="keyword">(</span><span class="default">$xml</span><span class="keyword">);<br />
</span><span class="default">$obj2 </span><span class="keyword">= </span><span class="default">SimpleXML_Load_String</span><span class="keyword">(</span><span class="default">$xml</span><span class="keyword">);<br />
<br />
</span><span class="comment">// COMPARE OBJECTS AND FIND THAT THIS ECHOS NOTHING AT ALL<br />
</span><span class="keyword">if (</span><span class="default">$obj1 </span><span class="keyword">=== </span><span class="default">$obj2</span><span class="keyword">) echo </span><span class="string">"\n\nOBJECTS IDENTICAL "</span><span class="keyword">;<br />
if (</span><span class="default">$obj1 </span><span class="keyword">==&nbsp; </span><span class="default">$obj2</span><span class="keyword">) echo </span><span class="string">"\n\nOBJECTS EQUAL "</span><span class="keyword">;<br />
<br />
</span><span class="comment">// SHOW THE OBJECTS - NOTE DIFFERENT SimpleXMLElement NUMBERS<br />
</span><span class="keyword">echo </span><span class="string">"\n\n"</span><span class="keyword">;<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$obj1</span><span class="keyword">);<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$obj2</span><span class="keyword">);<br />
<br />
</span><span class="comment">// ITERATE OVER THE OBJECTS<br />
</span><span class="keyword">foreach (</span><span class="default">$obj1 </span><span class="keyword">as </span><span class="default">$key </span><span class="keyword">=&gt; </span><span class="default">$val1</span><span class="keyword">)<br />
{<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$val2 </span><span class="keyword">= </span><span class="default">$obj2</span><span class="keyword">-&gt;</span><span class="default">$key</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$val1</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$val2</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; if (</span><span class="default">$val1 </span><span class="keyword">== </span><span class="default">$val2</span><span class="keyword">) echo </span><span class="string">"\n\nOBJECTS EQUAL"</span><span class="keyword">; </span><span class="comment">// ECHOS NOTHING<br />
<br />
// RECAST AS STRINGS AND COMPARE AGAIN<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$val1 </span><span class="keyword">= (string)</span><span class="default">$val1</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$val2 </span><span class="keyword">= (string)</span><span class="default">$val2</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; if (</span><span class="default">$val1 </span><span class="keyword">=== </span><span class="default">$val2</span><span class="keyword">) echo </span><span class="string">"STRINGS IDENTICAL: $key =&gt; $val1 \n\n"</span><span class="keyword">; </span><span class="comment">// CONMPARISON SHOWS STRINGS IDENTICAL<br />
</span><span class="keyword">}<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="85769"></a>
 <div class="note">
  <strong class='user'>Hayley Watson</strong>
  <a href="#85769" class="date">16-Sep-2008 03:33</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
This has already been mentioned (see jazfresh at hotmail.com's note), but here it is again in more detail because for objects the difference between == and === is significant.<br />
<br />
Loose equality (==) over objects is recursive: if the properties of the two objects being compared are themselves objects, then those properties will also be compared using ==.<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">Link<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public </span><span class="default">$link</span><span class="keyword">; function </span><span class="default">__construct</span><span class="keyword">(</span><span class="default">$link</span><span class="keyword">) { </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">link </span><span class="keyword">= </span><span class="default">$link</span><span class="keyword">; }<br />
}<br />
class </span><span class="default">Leaf<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public </span><span class="default">$leaf</span><span class="keyword">; function </span><span class="default">__construct</span><span class="keyword">(</span><span class="default">$leaf</span><span class="keyword">) { </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">leaf </span><span class="keyword">= </span><span class="default">$leaf</span><span class="keyword">; }<br />
}<br />
<br />
</span><span class="default">$leaf1 </span><span class="keyword">= new </span><span class="default">Leaf</span><span class="keyword">(</span><span class="default">42</span><span class="keyword">);<br />
</span><span class="default">$leaf2 </span><span class="keyword">= new </span><span class="default">Leaf</span><span class="keyword">(</span><span class="default">42</span><span class="keyword">);<br />
<br />
</span><span class="default">$link1 </span><span class="keyword">= new </span><span class="default">Link</span><span class="keyword">(</span><span class="default">$leaf1</span><span class="keyword">);<br />
</span><span class="default">$link2 </span><span class="keyword">= new </span><span class="default">Link</span><span class="keyword">(</span><span class="default">$leaf2</span><span class="keyword">);<br />
<br />
echo </span><span class="string">"Comparing Leaf object equivalence: is \$leaf1==\$leaf2? "</span><span class="keyword">, (</span><span class="default">$leaf1 </span><span class="keyword">== </span><span class="default">$leaf2&nbsp; </span><span class="keyword">? </span><span class="string">"Yes" </span><span class="keyword">: </span><span class="string">"No"</span><span class="keyword">), </span><span class="string">"\n"</span><span class="keyword">;<br />
echo </span><span class="string">"Comparing Leaf object identity: is \$leaf1===\$leaf2? "</span><span class="keyword">,&nbsp;&nbsp; (</span><span class="default">$leaf1 </span><span class="keyword">=== </span><span class="default">$leaf2 </span><span class="keyword">? </span><span class="string">"Yes" </span><span class="keyword">: </span><span class="string">"No"</span><span class="keyword">), </span><span class="string">"\n"</span><span class="keyword">;<br />
echo </span><span class="string">"\n"</span><span class="keyword">;<br />
echo </span><span class="string">"Comparing Link object equivalence: is \$link1==\$link2? "</span><span class="keyword">,(</span><span class="default">$link1 </span><span class="keyword">== </span><span class="default">$link2&nbsp; </span><span class="keyword">? </span><span class="string">"Yes" </span><span class="keyword">: </span><span class="string">"No"</span><span class="keyword">), </span><span class="string">"\n"</span><span class="keyword">;<br />
echo </span><span class="string">"Comparing Link object identity: is \$link1===\$link2? "</span><span class="keyword">,&nbsp; (</span><span class="default">$link1 </span><span class="keyword">=== </span><span class="default">$link2 </span><span class="keyword">? </span><span class="string">"Yes" </span><span class="keyword">: </span><span class="string">"No"</span><span class="keyword">), </span><span class="string">"\n"</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
Even though $link1 and $link2 contain different Leaf objects, they are still equivalent because the Leaf objects are themselves equivalent.<br />
<br />
The practical upshot is that using "==" when "===" would be more appropriate can result in a severe performance penalty, especially if the objects are large and/or complex. In fact, if there are any circular relationships involved between the objects or (recursively) any of their properties, then a fatal error can result because of the implied infinite loop.<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">Foo </span><span class="keyword">{ public </span><span class="default">$foo</span><span class="keyword">; }<br />
</span><span class="default">$t </span><span class="keyword">= new </span><span class="default">Foo</span><span class="keyword">; </span><span class="default">$t</span><span class="keyword">-&gt;</span><span class="default">foo </span><span class="keyword">= </span><span class="default">$t</span><span class="keyword">;<br />
</span><span class="default">$g </span><span class="keyword">= new </span><span class="default">Foo</span><span class="keyword">; </span><span class="default">$g</span><span class="keyword">-&gt;</span><span class="default">foo </span><span class="keyword">= </span><span class="default">$g</span><span class="keyword">;<br />
<br />
echo </span><span class="string">"Strict identity:&nbsp;&nbsp; "</span><span class="keyword">, (</span><span class="default">$t</span><span class="keyword">===</span><span class="default">$g </span><span class="keyword">? </span><span class="string">"True" </span><span class="keyword">: </span><span class="string">"False"</span><span class="keyword">),</span><span class="string">"\n"</span><span class="keyword">;<br />
echo </span><span class="string">"Loose equivalence: "</span><span class="keyword">, (</span><span class="default">$t</span><span class="keyword">==</span><span class="default">$g&nbsp; </span><span class="keyword">? </span><span class="string">"True" </span><span class="keyword">: </span><span class="string">"False"</span><span class="keyword">), </span><span class="string">"\n"</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
So preference should be given to comparing objects with "===" rather than "=="; if two distinct objects are to be compared for equivalence, try to do so by examining suitable individual properties. (Maybe PHP could get a magic "__equals" method that gets used to evaluate "=="? :) )</span>
</code></div>
  </div>
 </div>
 <a name="85606"></a>
 <div class="note">
  <strong class='user'>wbcarts at juno dot com</strong>
  <a href="#85606" class="date">08-Sep-2008 01:36</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
COMPARING OBJECTS using PHP's usort() method.<br />
<br />
PHP and MySQL both provide ways to sort your data already, and it is a good idea to use that if possible. However, since this section is on comparing your own PHP objects (and that you may need to alter the sorting method in PHP), here is an example of how you can do that using PHP's "user-defined" sort method, usort() and your own class compare() methods.<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="comment">/*<br />
&nbsp;* Employee.php<br />
&nbsp;*<br />
&nbsp;* This class defines a compare() method, which tells PHP the sorting rules<br />
&nbsp;* for this object - which is to sort by emp_id.<br />
&nbsp;*<br />
&nbsp;*/<br />
</span><span class="keyword">class </span><span class="default">Employee<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public </span><span class="default">$first</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; public </span><span class="default">$last</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; public </span><span class="default">$emp_id</span><span class="keyword">;&nbsp; &nbsp;&nbsp; </span><span class="comment">// the property we're interested in...<br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">public function </span><span class="default">__construct</span><span class="keyword">(</span><span class="default">$emp_first</span><span class="keyword">, </span><span class="default">$emp_last</span><span class="keyword">, </span><span class="default">$emp_ID</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">first </span><span class="keyword">= </span><span class="default">$emp_first</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">last </span><span class="keyword">= </span><span class="default">$emp_last</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">emp_id </span><span class="keyword">= </span><span class="default">$emp_ID</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">/*<br />
&nbsp;&nbsp; &nbsp; * define the rules for sorting this object - using emp_id.<br />
&nbsp;&nbsp; &nbsp; * Make sure this function returns a -1, 0, or 1.<br />
&nbsp;&nbsp; &nbsp; */<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">public static function </span><span class="default">compare</span><span class="keyword">(</span><span class="default">$a</span><span class="keyword">, </span><span class="default">$b</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if (</span><span class="default">$a</span><span class="keyword">-&gt;</span><span class="default">emp_id </span><span class="keyword">&lt; </span><span class="default">$b</span><span class="keyword">-&gt;</span><span class="default">emp_id</span><span class="keyword">) return -</span><span class="default">1</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; else if(</span><span class="default">$a</span><span class="keyword">-&gt;</span><span class="default">emp_id </span><span class="keyword">== </span><span class="default">$b</span><span class="keyword">-&gt;</span><span class="default">emp_id</span><span class="keyword">) return </span><span class="default">0</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; else return </span><span class="default">1</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">__toString</span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="string">"Employee[first=$this-&gt;first, last=$this-&gt;last, emp_id=$this-&gt;emp_id]"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="comment"># create a PHP array and initialize it with Employee objects.<br />
</span><span class="default">$employees </span><span class="keyword">= array(<br />
&nbsp; new </span><span class="default">Employee</span><span class="keyword">(</span><span class="string">"John"</span><span class="keyword">, </span><span class="string">"Smith"</span><span class="keyword">, </span><span class="default">345</span><span class="keyword">),<br />
&nbsp; new </span><span class="default">Employee</span><span class="keyword">(</span><span class="string">"Jane"</span><span class="keyword">, </span><span class="string">"Doe"</span><span class="keyword">, </span><span class="default">231</span><span class="keyword">),<br />
&nbsp; new </span><span class="default">Employee</span><span class="keyword">(</span><span class="string">"Mike"</span><span class="keyword">, </span><span class="string">"Barnes"</span><span class="keyword">, </span><span class="default">522</span><span class="keyword">),<br />
&nbsp; new </span><span class="default">Employee</span><span class="keyword">(</span><span class="string">"Vicky"</span><span class="keyword">, </span><span class="string">"Jones"</span><span class="keyword">, </span><span class="default">107</span><span class="keyword">),<br />
&nbsp; new </span><span class="default">Employee</span><span class="keyword">(</span><span class="string">"John"</span><span class="keyword">, </span><span class="string">"Doe"</span><span class="keyword">, </span><span class="default">2</span><span class="keyword">),<br />
&nbsp; new </span><span class="default">Employee</span><span class="keyword">(</span><span class="string">"Kevin"</span><span class="keyword">, </span><span class="string">"Patterson"</span><span class="keyword">, </span><span class="default">89</span><span class="keyword">)<br />
);<br />
<br />
</span><span class="comment"># sort the $employees array using Employee compare() method.<br />
</span><span class="default">usort</span><span class="keyword">(</span><span class="default">$employees</span><span class="keyword">, array(</span><span class="string">"Employee"</span><span class="keyword">, </span><span class="string">"compare"</span><span class="keyword">));<br />
<br />
</span><span class="comment"># print the results<br />
</span><span class="keyword">foreach(</span><span class="default">$employees </span><span class="keyword">as </span><span class="default">$employee</span><span class="keyword">)<br />
{<br />
&nbsp; echo </span><span class="default">$employee </span><span class="keyword">. </span><span class="string">'&lt;br&gt;'</span><span class="keyword">;<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
Results are now sorted by emp_id:<br />
<br />
Employee[first=John, last=Doe, emp_id=2]<br />
Employee[first=Kevin, last=Patterson, emp_id=89]<br />
Employee[first=Vicky, last=Jones, emp_id=107]<br />
Employee[first=Jane, last=Doe, emp_id=231]<br />
Employee[first=John, last=Smith, emp_id=345]<br />
Employee[first=Mike, last=Barnes, emp_id=522]<br />
<br />
Important Note: Your PHP code will never directly call the Employee's compare() method, but PHP's usort() calls it many many times. Also, when defining the rules for sorting, make sure to get to a "primitive type" level... that is, down to a number or string, and that the function returns a -1, 0, or 1, for reliable and consistent results.<br />
<br />
Also see: <a href="http://www.php.net/manual/en/function.usort.php" rel="nofollow" target="_blank">http://www.php.net/manual/en/function.usort.php</a> for more examples of PHP's sorting facilities.</span>
</code></div>
  </div>
 </div>
 <a name="85578"></a>
 <div class="note">
  <strong class='user'>wbcarts at juno dot com</strong>
  <a href="#85578" class="date">06-Sep-2008 02:02</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
COMPARISONS AND EQUALITY are NOT the same<br />
<br />
I'm not sure that the PHP Example #1 above is clear enough. In my own experience, I have found there is a distinct difference between a "comparison" and a "test for equality". The difference is found in the possible return values of the function being used, for example.<br />
<br />
/*<br />
&nbsp;* Test two values for EQUALITY - returns (boolean) TRUE or FALSE.<br />
&nbsp;*/<br />
function equals($a, $b)<br />
{<br />
&nbsp; return ($a == $b);<br />
}<br />
<br />
/*<br />
&nbsp;* COMPARE two values - returns (int) -1, 0, or 1.<br />
&nbsp;*/<br />
function compare($a, $b)<br />
{<br />
&nbsp; if($a &lt; $b) return -1;<br />
&nbsp; else if($a == $b) return 0;<br />
&nbsp; else if($a &gt; $b) return 1;<br />
&nbsp; else return -1;<br />
}<br />
<br />
My examples clarify the difference between "making a comparison" and "testing for equality". You can substitute any of the "==" with "===" for example, but the point is on the possible return values of the function. All tests for EQUALITY will return TRUE or FALSE, and a COMPARISON will give a "&lt;", "==", or "&gt;" answers... which you can then use for sorting.</span>
</code></div>
  </div>
 </div>
 <a name="81600"></a>
 <div class="note">
  <strong class='user'>cross+php at distal dot com</strong>
  <a href="#81600" class="date">05-Mar-2008 08:50</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
In response to "rune at zedeler dot dk"s comment about class contents being equal, I have a similar issue.&nbsp; I want to sort an array of objects using sort().<br />
<br />
I know I can do it with usort(), but I'm used to C++ where you can define operators that allow comparison.&nbsp; I see in the zend source code that it calls a compare_objects function, but I don't see any way to implement that function for an object.&nbsp; Would it have to be an extension to provide that interface?<br />
<br />
If so, I'd like to suggest that you allow equivalence and/or comparison operations to be defined in a class definition in PHP.&nbsp; Then, the sorts of things rune and I want to do would be much easier.</span>
</code></div>
  </div>
 </div>
 <a name="73804"></a>
 <div class="note">
  <strong class='user'>dionyziz at deviantart dot com</strong>
  <a href="#73804" class="date">10-Mar-2007 07:20</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Note that classes deriving from the same parent aren't considered equal when comparing even using ==; they should also be objects of the same child class.<br />
<br />
<span class="default">&lt;?php<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">class </span><span class="default">Mom </span><span class="keyword">{<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; private </span><span class="default">$mAttribute</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; public function </span><span class="default">Mom</span><span class="keyword">( </span><span class="default">$attribute </span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">mAttribute </span><span class="keyword">= </span><span class="default">$attribute</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; public function </span><span class="default">Attribute</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; return </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">mAttribute</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; final class </span><span class="default">Sister </span><span class="keyword">extends </span><span class="default">Mom </span><span class="keyword">{<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; public function </span><span class="default">Sister</span><span class="keyword">( </span><span class="default">$attribute </span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">Mom</span><span class="keyword">( </span><span class="default">$attribute </span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; final class </span><span class="default">Brother </span><span class="keyword">extends </span><span class="default">Mom </span><span class="keyword">{<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; public function </span><span class="default">Brother</span><span class="keyword">( </span><span class="default">$attribute </span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">Mom</span><span class="keyword">( </span><span class="default">$attribute </span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$sister </span><span class="keyword">= new </span><span class="default">Sister</span><span class="keyword">( </span><span class="default">5 </span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$brother </span><span class="keyword">= new </span><span class="default">Brother</span><span class="keyword">( </span><span class="default">5 </span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; </span><span class="default">assert</span><span class="keyword">( </span><span class="default">$sister </span><span class="keyword">== </span><span class="default">$brother </span><span class="keyword">); </span><span class="comment">// will FAIL!<br />
</span><span class="default">?&gt;<br />
</span><br />
This assertion will fail, because sister and brother are not of the same child class!<br />
<br />
If you want to compare based on the parent class object type only, you might have to define a function for comparisons like these, and use it instead of the == operator:<br />
<br />
<span class="default">&lt;?php<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">function </span><span class="default">SiblingsEqual</span><span class="keyword">( </span><span class="default">$a</span><span class="keyword">, </span><span class="default">$b </span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if ( !( </span><span class="default">$a </span><span class="keyword">instanceof </span><span class="default">Mom </span><span class="keyword">) ) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">false</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if ( !( </span><span class="default">$b </span><span class="keyword">instanceof </span><span class="default">Mom </span><span class="keyword">) ) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">false</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if ( </span><span class="default">$a</span><span class="keyword">-&gt;</span><span class="default">Attribute</span><span class="keyword">() != </span><span class="default">$b</span><span class="keyword">-&gt;</span><span class="default">Attribute</span><span class="keyword">() ) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">false</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">true</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">assert</span><span class="keyword">( </span><span class="default">SiblingsEqual</span><span class="keyword">( </span><span class="default">$sister</span><span class="keyword">, </span><span class="default">$brother </span><span class="keyword">) ); </span><span class="comment">// will succeed<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="73542"></a>
 <div class="note">
  <strong class='user'>rune at zedeler dot dk</strong>
  <a href="#73542" class="date">28-Feb-2007 08:34</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Whoops, apparently I hadn't checked the array-part of the below very well.<br />
Forgot to test if the arrays had same length, and had some misaligned parenthesis.<br />
This one should work better :+)<br />
<br />
&lt;?<br />
function deepCompare($a,$b) {<br />
&nbsp; if(is_object($a) &amp;&amp; is_object($b)) {<br />
&nbsp;&nbsp;&nbsp; if(get_class($a)!=get_class($b))<br />
&nbsp;&nbsp; &nbsp;&nbsp; return false;<br />
&nbsp;&nbsp;&nbsp; foreach($a as $key =&gt; $val) {<br />
&nbsp;&nbsp; &nbsp;&nbsp; if(!deepCompare($val,$b-&gt;$key))<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return false;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; return true;<br />
&nbsp; }<br />
&nbsp; else if(is_array($a) &amp;&amp; is_array($b)) {<br />
&nbsp;&nbsp;&nbsp; while(!is_null(key($a)) &amp;&amp; !is_null(key($b))) {<br />
&nbsp;&nbsp; &nbsp;&nbsp; if (key($a)!==key($b) || !deepCompare(current($a),current($b)))<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return false;<br />
&nbsp;&nbsp; &nbsp;&nbsp; next($a); next($b);<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; return is_null(key($a)) &amp;&amp; is_null(key($b));<br />
&nbsp; }<br />
&nbsp; else<br />
&nbsp;&nbsp;&nbsp; return $a===$b;<br />
}<br />
?&gt;</span>
</code></div>
  </div>
 </div>
 <a name="73528"></a>
 <div class="note">
  <strong class='user'>rune at zedeler dot dk</strong>
  <a href="#73528" class="date">27-Feb-2007 08:27</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I haven't found a build-in function to check whether two obects are identical - that is, all their fields are identical.<br />
In other words,<br />
<br />
&lt;?<br />
class A {<br />
&nbsp; var $x;<br />
&nbsp; function __construct($x) { $this-&gt;x = $x; }<br />
<br />
}<br />
$identical1 = new A(42);<br />
$identical2 = new A(42);<br />
$different = new A('42');<br />
?&gt;<br />
<br />
Comparing the objects with "==" will claim that all three of them are equal. Comparing with "===" will claim that all are un-equal.<br />
I have found no build-in function to check that the two identicals are <br />
identical, but not identical to the different.<br />
<br />
The following function does that:<br />
<br />
&lt;?<br />
function deepCompare($a,$b) {<br />
&nbsp; if(is_object($a) &amp;&amp; is_object($b)) {<br />
&nbsp;&nbsp;&nbsp; if(get_class($a)!=get_class($b))<br />
&nbsp;&nbsp; &nbsp;&nbsp; return false;<br />
&nbsp;&nbsp;&nbsp; foreach($a as $key =&gt; $val) {<br />
&nbsp;&nbsp; &nbsp;&nbsp; if(!deepCompare($val,$b-&gt;$key))<br />
&nbsp;&nbsp;&nbsp; return false;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; return true;<br />
&nbsp; }<br />
&nbsp; else if(is_array($a) &amp;&amp; is_array($b)) {<br />
&nbsp;&nbsp;&nbsp; while(!is_null(key($a) &amp;&amp; !is_null(key($b)))) {<br />
&nbsp;&nbsp; &nbsp;&nbsp; if (key($a)!==key($b) || !deepCompare(current($a),current($b)))<br />
&nbsp;&nbsp;&nbsp; return false;<br />
&nbsp;&nbsp; &nbsp;&nbsp; next($a); next($b);<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; return true;<br />
&nbsp; }<br />
&nbsp; else<br />
&nbsp;&nbsp;&nbsp; return $a===$b;<br />
}<br />
?&gt;</span>
</code></div>
  </div>
 </div>
 <a name="71623"></a>
 <div class="note">
  <strong class='user'>jazfresh at hotmail.com</strong>
  <a href="#71623" class="date">08-Dec-2006 02:36</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Note that when comparing object attributes, the comparison is recursive (at least, it is with PHP 5.2). That is, if $a-&gt;x contains an object then that will be compared with $b-&gt;x in the same manner. Be aware that this can lead to recursion errors:<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">Foo </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public </span><span class="default">$x</span><span class="keyword">;<br />
}<br />
</span><span class="default">$a </span><span class="keyword">= new </span><span class="default">Foo</span><span class="keyword">();<br />
</span><span class="default">$b </span><span class="keyword">= new </span><span class="default">Foo</span><span class="keyword">();<br />
</span><span class="default">$a</span><span class="keyword">-&gt;</span><span class="default">x </span><span class="keyword">= </span><span class="default">$b</span><span class="keyword">;<br />
</span><span class="default">$b</span><span class="keyword">-&gt;</span><span class="default">x </span><span class="keyword">= </span><span class="default">$a</span><span class="keyword">;<br />
<br />
</span><span class="default">print_r</span><span class="keyword">(</span><span class="default">$a </span><span class="keyword">== </span><span class="default">$b</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span>Results in:<br />
PHP Fatal error:&nbsp; Nesting level too deep - recursive dependency? in test.php on line 11</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=language.oop5.object-comparison&amp;redirect=@w{ZAGA2MN9}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.oop5.object-comparison&amp;redirect=@w{ZAGA2MN9}">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/language.oop5.object-comparison.php">show source</a> |
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