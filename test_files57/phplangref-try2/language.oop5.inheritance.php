<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Object Inheritance - Manual</title>
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
 <link rel="prev" href="language.oop5.visibility.php" />
 <link rel="next" href="language.oop5.paamayim-nekudotayim.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/oop5.inheritance" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/language.oop5.inheritance.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/language.oop5.inheritance.php" />
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
 <li class="active"><a href="language.oop5.inheritance.php">Object Inheritance</a></li>
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
 <li><a href="language.oop5.references.php">Objects and references</a></li>
 <li><a href="language.oop5.serialization.php">Object Serialization</a></li>
 <li><a href="language.oop5.changelog.php">OOP Changelog</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="language.oop5.paamayim-nekudotayim.php">Scope Resolution Operator (::)<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.oop5.visibility.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Visibility</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.oop5.inheritance.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/language.oop5.inheritance.php">Brazilian Portuguese</option>
    <option value="zh/language.oop5.inheritance.php">Chinese (Simplified)</option>
    <option value="fr/language.oop5.inheritance.php">French</option>
    <option value="de/language.oop5.inheritance.php">German</option>
    <option value="ja/language.oop5.inheritance.php">Japanese</option>
    <option value="pl/language.oop5.inheritance.php">Polish</option>
    <option value="ro/language.oop5.inheritance.php">Romanian</option>
    <option value="ru/language.oop5.inheritance.php">Russian</option>
    <option value="fa/language.oop5.inheritance.php">Persian</option>
    <option value="es/language.oop5.inheritance.php">Spanish</option>
    <option value="tr/language.oop5.inheritance.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="language.oop5.inheritance" class="sect1">
  <h2 class="title">Object Inheritance</h2>
  <p class="para">
   Inheritance is a well-established programming principle, and PHP makes use
   of this principle in its object model. This principle will affect the way
   many classes and objects relate to one another.
  </p>
  <p class="para">
   For example, when you extend a class, the subclass inherits all of the
   public and protected methods from the parent class. Unless a class overrides
   those methods, they will retain their original functionality.
  </p>
  <p class="para">
   This is useful for defining and abstracting functionality, and permits the
   implementation of additional functionality in similar objects without the
   need to reimplement all of the shared functionality.
  </p>

  <blockquote class="note"><p><strong class="note">Note</strong>: 
   <p class="para">
    Unless autoloading is used, then classes must be defined before they are 
    used. If a class extends another, then the parent class must be declared 
    before the child class structure. This rule applies to classes that inherit 
    other classes and interfaces.
   </p>
  </p></blockquote>

  <div class="sect2" id="language.oop5.inheritance.examples">
   <div class="example" id="language.oop5.inheritance.examples.ex1">
    <p><strong>Example #1 Inheritance Example</strong></p>
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /><br /></span><span style="color: #007700">class&nbsp;</span><span style="color: #0000BB">foo<br /></span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">printItem</span><span style="color: #007700">(</span><span style="color: #0000BB">$string</span><span style="color: #007700">)<br />&nbsp;&nbsp;&nbsp;&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">'Foo:&nbsp;'&nbsp;</span><span style="color: #007700">.&nbsp;</span><span style="color: #0000BB">$string&nbsp;</span><span style="color: #007700">.&nbsp;</span><span style="color: #0000BB">PHP_EOL</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />&nbsp;&nbsp;&nbsp;&nbsp;<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">printPHP</span><span style="color: #007700">()<br />&nbsp;&nbsp;&nbsp;&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">'PHP&nbsp;is&nbsp;great.'&nbsp;</span><span style="color: #007700">.&nbsp;</span><span style="color: #0000BB">PHP_EOL</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br /><br />class&nbsp;</span><span style="color: #0000BB">bar&nbsp;</span><span style="color: #007700">extends&nbsp;</span><span style="color: #0000BB">foo<br /></span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">printItem</span><span style="color: #007700">(</span><span style="color: #0000BB">$string</span><span style="color: #007700">)<br />&nbsp;&nbsp;&nbsp;&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">'Bar:&nbsp;'&nbsp;</span><span style="color: #007700">.&nbsp;</span><span style="color: #0000BB">$string&nbsp;</span><span style="color: #007700">.&nbsp;</span><span style="color: #0000BB">PHP_EOL</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br /><br /></span><span style="color: #0000BB">$foo&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">foo</span><span style="color: #007700">();<br /></span><span style="color: #0000BB">$bar&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">bar</span><span style="color: #007700">();<br /></span><span style="color: #0000BB">$foo</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">printItem</span><span style="color: #007700">(</span><span style="color: #DD0000">'baz'</span><span style="color: #007700">);&nbsp;</span><span style="color: #FF8000">//&nbsp;Output:&nbsp;'Foo:&nbsp;baz'<br /></span><span style="color: #0000BB">$foo</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">printPHP</span><span style="color: #007700">();&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;Output:&nbsp;'PHP&nbsp;is&nbsp;great'&nbsp;<br /></span><span style="color: #0000BB">$bar</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">printItem</span><span style="color: #007700">(</span><span style="color: #DD0000">'baz'</span><span style="color: #007700">);&nbsp;</span><span style="color: #FF8000">//&nbsp;Output:&nbsp;'Bar:&nbsp;baz'<br /></span><span style="color: #0000BB">$bar</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">printPHP</span><span style="color: #007700">();&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;Output:&nbsp;'PHP&nbsp;is&nbsp;great'<br /><br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

   </div>
  </div>

 </div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="language.oop5.paamayim-nekudotayim.php">Scope Resolution Operator (::)<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.oop5.visibility.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Visibility</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.oop5.inheritance.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=language.oop5.inheritance&amp;redirect=http://www.php.net/manual/en/language.oop5.inheritance.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.oop5.inheritance&amp;redirect=http://www.php.net/manual/en/language.oop5.inheritance.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Object Inheritance</strong>
 </div><div id="allnotes">
 <a name="108847"></a>
 <div class="note">
  <strong class='user'>maximark at libero dot it</strong>
  <a href="#108847" class="date">30-May-2012 09:55</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
MULTI INHERITANCE<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">A<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp; public function </span><span class="default">meth1</span><span class="keyword">()<br />
&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp;&nbsp; echo </span><span class="string">"meth1"</span><span class="keyword">;<br />
&nbsp;&nbsp; }<br />
&nbsp;&nbsp; public function </span><span class="default">meth2</span><span class="keyword">()<br />
&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp;&nbsp; </span><span class="default">B</span><span class="keyword">::</span><span class="default">meth2</span><span class="keyword">();<br />
&nbsp;&nbsp; }<br />
}<br />
<br />
class </span><span class="default">B<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp; public function </span><span class="default">meth2</span><span class="keyword">()<br />
&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp;&nbsp; echo </span><span class="string">"&lt;br/&gt;meth2 "</span><span class="keyword">.</span><span class="default">get_class</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">);<br />
&nbsp;&nbsp; }<br />
}<br />
</span><span class="default">$a </span><span class="keyword">= new </span><span class="default">A</span><span class="keyword">();<br />
</span><span class="default">$a</span><span class="keyword">-&gt;</span><span class="default">meth2</span><span class="keyword">();<br />
<br />
</span><span class="default">$b </span><span class="keyword">= new </span><span class="default">B</span><span class="keyword">();<br />
</span><span class="default">$b</span><span class="keyword">-&gt;</span><span class="default">meth2</span><span class="keyword">();<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="102142"></a>
 <div class="note">
  <strong class='user'>msg2maciej at aol dot com</strong>
  <a href="#102142" class="date">28-Jan-2011 04:08</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
PHP supports single class inheritance. My bare idea on accessing protected methods with power of abstracts and sort of "multi-class inheritance SIMULATION":<br />
<br />
<span class="default">&lt;?php<br />
error_reporting</span><span class="keyword">(</span><span class="default">E_ALL</span><span class="keyword">);<br />
<br />
abstract class </span><span class="default">Base </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; abstract protected function </span><span class="default">__construct </span><span class="keyword">();<br />
&nbsp;&nbsp;&nbsp; abstract protected function </span><span class="default">hello_left </span><span class="keyword">();<br />
&nbsp;&nbsp;&nbsp; abstract protected function </span><span class="default">hello_right </span><span class="keyword">();<br />
}<br />
<br />
abstract class </span><span class="default">NotImplemented_Left </span><span class="keyword">extends </span><span class="default">Base </span><span class="keyword">{<br />
protected function </span><span class="default">hello_right </span><span class="keyword">() {<br />
echo </span><span class="string">'well, wont see that'</span><span class="keyword">; }}<br />
<br />
abstract class </span><span class="default">NotImplemented_Right </span><span class="keyword">extends </span><span class="default">Base </span><span class="keyword">{<br />
protected function </span><span class="default">hello_left </span><span class="keyword">() {<br />
echo </span><span class="string">'well, wont see that'</span><span class="keyword">; }}<br />
<br />
class </span><span class="default">Left </span><span class="keyword">extends </span><span class="default">NotImplemented_Left </span><span class="keyword">{<br />
protected function </span><span class="default">__construct </span><span class="keyword">() {&nbsp; &nbsp; &nbsp; &nbsp; </span><span class="comment"># limited visibility, no access from "outside"<br />
</span><span class="keyword">echo </span><span class="default">__CLASS__</span><span class="keyword">.</span><span class="string">'::protected __construct'</span><span class="keyword">. </span><span class="string">"\n"</span><span class="keyword">; }<br />
protected function </span><span class="default">hello_left </span><span class="keyword">() {&nbsp; &nbsp; &nbsp; &nbsp; </span><span class="comment"># limited visibility, no access from "outside"<br />
</span><span class="keyword">echo </span><span class="string">'protected hello_left in ' </span><span class="keyword">. </span><span class="default">__CLASS__ </span><span class="keyword">. </span><span class="string">"\n"</span><span class="keyword">; }}<br />
<br />
class </span><span class="default">Right </span><span class="keyword">extends </span><span class="default">NotImplemented_Right </span><span class="keyword">{<br />
protected function </span><span class="default">__construct </span><span class="keyword">() {&nbsp; &nbsp; &nbsp; &nbsp; </span><span class="comment"># limited visibility, no access from "outside"<br />
</span><span class="keyword">echo </span><span class="default">__CLASS__</span><span class="keyword">.</span><span class="string">'::protected __construct'</span><span class="keyword">. </span><span class="string">"\n"</span><span class="keyword">; }<br />
protected function </span><span class="default">hello_right </span><span class="keyword">() {&nbsp; &nbsp; &nbsp; &nbsp; </span><span class="comment"># limited visibility, no access from "outside"<br />
</span><span class="keyword">echo </span><span class="string">'protected hello_right in ' </span><span class="keyword">. </span><span class="default">__CLASS__ </span><span class="keyword">. </span><span class="string">"\n"</span><span class="keyword">; }<br />
protected function </span><span class="default">hello_left </span><span class="keyword">() {<br />
echo </span><span class="string">"wont see that, and easy to get rid of it from here\n"</span><span class="keyword">; }}<br />
<br />
class </span><span class="default">Center </span><span class="keyword">extends </span><span class="default">Base </span><span class="keyword">{<br />
private </span><span class="default">$left</span><span class="keyword">;<br />
private </span><span class="default">$right</span><span class="keyword">;<br />
public function </span><span class="default">__construct </span><span class="keyword">() {<br />
echo </span><span class="string">'welcome in ' </span><span class="keyword">. </span><span class="default">__CLASS__ </span><span class="keyword">. </span><span class="string">"\n"</span><span class="keyword">;<br />
echo </span><span class="string">'Center::'</span><span class="keyword">; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">left </span><span class="keyword">= new </span><span class="default">Left</span><span class="keyword">;<br />
echo </span><span class="string">'Center::'</span><span class="keyword">; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">right </span><span class="keyword">= new </span><span class="default">Right</span><span class="keyword">;<br />
echo </span><span class="string">" oh and\n"</span><span class="keyword">;<br />
</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">hello_left</span><span class="keyword">();<br />
</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">hello_right</span><span class="keyword">();<br />
}<br />
public function </span><span class="default">hello_left </span><span class="keyword">() {&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="comment"># calling class Left<br />
</span><span class="keyword">echo </span><span class="default">__CLASS__</span><span class="keyword">.</span><span class="string">'::'</span><span class="keyword">; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">left</span><span class="keyword">-&gt;</span><span class="default">hello_left</span><span class="keyword">(); }<br />
public function </span><span class="default">hello_right </span><span class="keyword">() {&nbsp; &nbsp; &nbsp; &nbsp; </span><span class="comment"># calling class Right<br />
</span><span class="keyword">echo </span><span class="default">__CLASS__</span><span class="keyword">.</span><span class="string">'::'</span><span class="keyword">; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">right</span><span class="keyword">-&gt;</span><span class="default">hello_right</span><span class="keyword">(); }<br />
}<br />
<br />
</span><span class="default">$c </span><span class="keyword">= new </span><span class="default">Center</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
Produces:<br />
<br />
welcome in Center<br />
Center::Left::protected __construct<br />
Center::Right::protected __construct<br />
&nbsp;oh and<br />
Center::protected hello_left in Left<br />
Center::protected hello_right in Right</span>
</code></div>
  </div>
 </div>
 <a name="100912"></a>
 <div class="note">
  <strong class='user'>OZ</strong>
  <a href="#100912" class="date">14-Nov-2010 03:24</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Model for Mixins pattern:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">interface </span><span class="default">IMixinsCaller<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">__mixin_get_property</span><span class="keyword">(</span><span class="default">$property</span><span class="keyword">);<br />
<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">__mixin_set_property</span><span class="keyword">(</span><span class="default">$property</span><span class="keyword">, </span><span class="default">$value</span><span class="keyword">);<br />
<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">__mixin_call</span><span class="keyword">(</span><span class="default">$method</span><span class="keyword">, </span><span class="default">$value</span><span class="keyword">);<br />
}<br />
<br />
abstract class </span><span class="default">MixinsCaller </span><span class="keyword">implements </span><span class="default">IMixinsCaller<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; protected </span><span class="default">$mixins </span><span class="keyword">= array();<br />
<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">__call</span><span class="keyword">(</span><span class="default">$name</span><span class="keyword">, </span><span class="default">$arguments</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if (!empty(</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">mixins</span><span class="keyword">))<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; foreach (</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">mixins </span><span class="keyword">as </span><span class="default">$mixin</span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; if (</span><span class="default">method_exists</span><span class="keyword">(</span><span class="default">$mixin</span><span class="keyword">, </span><span class="default">$name</span><span class="keyword">))<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">call_user_func_array</span><span class="keyword">(array(</span><span class="default">$mixin</span><span class="keyword">, </span><span class="default">$name</span><span class="keyword">), </span><span class="default">$arguments</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">trigger_error</span><span class="keyword">(</span><span class="string">'Non-existent method was called in class '</span><span class="keyword">.</span><span class="default">__CLASS__</span><span class="keyword">.</span><span class="string">': '</span><span class="keyword">.</span><span class="default">$name</span><span class="keyword">, </span><span class="default">E_USER_WARNING</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">__mixin_get_property</span><span class="keyword">(</span><span class="default">$property</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if (</span><span class="default">property_exists</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">, </span><span class="default">$property</span><span class="keyword">))<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">$property</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">trigger_error</span><span class="keyword">(</span><span class="string">'Non-existent property was get in class '</span><span class="keyword">.</span><span class="default">__CLASS__</span><span class="keyword">.</span><span class="string">': '</span><span class="keyword">.</span><span class="default">$property</span><span class="keyword">, </span><span class="default">E_USER_WARNING</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">__mixin_set_property</span><span class="keyword">(</span><span class="default">$property</span><span class="keyword">, </span><span class="default">$value</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if (</span><span class="default">property_exists</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">, </span><span class="default">$property</span><span class="keyword">))<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">$property </span><span class="keyword">= </span><span class="default">$value</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">trigger_error</span><span class="keyword">(</span><span class="string">'Non-existent property was set in class '</span><span class="keyword">.</span><span class="default">__CLASS__</span><span class="keyword">.</span><span class="string">': '</span><span class="keyword">.</span><span class="default">$property</span><span class="keyword">, </span><span class="default">E_USER_WARNING</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">__mixin_call</span><span class="keyword">(</span><span class="default">$method</span><span class="keyword">, </span><span class="default">$value</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if (</span><span class="default">method_exists</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">, </span><span class="default">$method</span><span class="keyword">))<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">call_user_func_array</span><span class="keyword">(array(</span><span class="default">$this</span><span class="keyword">, </span><span class="default">$method</span><span class="keyword">), </span><span class="default">$value</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">trigger_error</span><span class="keyword">(</span><span class="string">'Non-existent method was called in class '</span><span class="keyword">.</span><span class="default">__CLASS__</span><span class="keyword">.</span><span class="string">': '</span><span class="keyword">.</span><span class="default">$method</span><span class="keyword">, </span><span class="default">E_USER_WARNING</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">AddMixin</span><span class="keyword">(</span><span class="default">$mixin</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">mixins</span><span class="keyword">[] = </span><span class="default">$mixin</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
abstract class </span><span class="default">Mixin<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">/** @var IMixinsCaller $parent_object */<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">private </span><span class="default">$parent_object</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">__construct</span><span class="keyword">(</span><span class="default">IMixinsCaller $parent_object</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">parent_object </span><span class="keyword">= </span><span class="default">$parent_object</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">__get</span><span class="keyword">(</span><span class="default">$property</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">parent_object</span><span class="keyword">-&gt;</span><span class="default">__mixin_get_property</span><span class="keyword">(</span><span class="default">$property</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">__set</span><span class="keyword">(</span><span class="default">$property</span><span class="keyword">, </span><span class="default">$value</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">parent_object</span><span class="keyword">-&gt;</span><span class="default">__mixin_set_property</span><span class="keyword">(</span><span class="default">$property</span><span class="keyword">, </span><span class="default">$value</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">__call</span><span class="keyword">(</span><span class="default">$method</span><span class="keyword">, </span><span class="default">$value</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">parent_object</span><span class="keyword">-&gt;</span><span class="default">__mixin_call</span><span class="keyword">(</span><span class="default">$method</span><span class="keyword">, </span><span class="default">$value</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="100063"></a>
 <div class="note">
  <strong class='user'>janturon at email dot cz</strong>
  <a href="#100063" class="date">22-Sep-2010 10:11</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Here is an easy way how to implement multiple inheritance in PHP using magic methods. Let's have this class:<br />
<br />
class Extender {<br />
&nbsp; private $objs = array();<br />
&nbsp; public function __construct() {<br />
&nbsp;&nbsp;&nbsp; $drones = func_get_args();<br />
&nbsp;&nbsp;&nbsp; foreach($drones as $drone) $this-&gt;objs[$drone] = new $drone();<br />
&nbsp;&nbsp;&nbsp; foreach($this-&gt;objs as $obj) $obj-&gt;hive = $this;<br />
&nbsp; }<br />
&nbsp; public function __get($attr) {<br />
&nbsp;&nbsp;&nbsp; foreach($this-&gt;objs as $obj)<br />
&nbsp;&nbsp; &nbsp;&nbsp; if(isset($obj-&gt;$attr))<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return $obj-&gt;$attr;<br />
&nbsp; }<br />
&nbsp; public function __set($attr,$val) {<br />
&nbsp;&nbsp;&nbsp; if($attr=="hive") return;<br />
&nbsp;&nbsp;&nbsp; foreach($this-&gt;objs as $obj)<br />
&nbsp;&nbsp; &nbsp;&nbsp; if(isset($obj-&gt;$attr))<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; $obj-&gt;$attr = $val;<br />
&nbsp; }<br />
&nbsp; public function __call($meth,$args) {<br />
&nbsp;&nbsp;&nbsp; foreach($this-&gt;objs as $obj)<br />
&nbsp;&nbsp; &nbsp;&nbsp; if(method_exists($obj,$meth))<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return call_user_func_array(array($obj,$meth),$args);<br />
&nbsp; }<br />
}<br />
<br />
Now multiple inheritance can be written this way ($hive is reference to child classes)<br />
<br />
class c1 {<br />
&nbsp; public $p1 = "x";<br />
&nbsp; function f1() { echo $this-&gt;p1; }<br />
}<br />
<br />
class c2 {<br />
&nbsp; public $p2 = "x";<br />
&nbsp; function f2() { echo $this-&gt;p2; }<br />
}<br />
<br />
class c_extends_c1_c2 {<br />
&nbsp; static $hive;<br />
&nbsp; function test() {<br />
&nbsp;&nbsp;&nbsp; $this-&gt;hive-&gt;p1 = "hello,";<br />
&nbsp;&nbsp;&nbsp; $this-&gt;hive-&gt;p2 = "world!";<br />
&nbsp;&nbsp;&nbsp; return $this-&gt;hive-&gt;f1() . $this-&gt;hive-&gt;f2();<br />
&nbsp; }<br />
}<br />
<br />
And here is how to use it:<br />
<br />
$obj = new Extender("c1","c2","c_extends_c1_c2");<br />
echo $obj-&gt;test(); //"hello,world!"</span>
</code></div>
  </div>
 </div>
 <a name="100005"></a>
 <div class="note">
  <strong class='user'>strata_ranger at hotmail dot com</strong>
  <a href="#100005" class="date">19-Sep-2010 09:37</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I was recently extending a PEAR class when I encountered a situation where I wanted to call a constructor two levels up the class hierarchy, ignoring the immediate parent.&nbsp; In such a case, you need to explicitly reference the class name using the :: operator.<br />
<br />
Fortunately, just like using the 'parent' keyword PHP correctly recognizes that you are calling the function from a protected context inside the object's class hierarchy.<br />
<br />
E.g:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">foo<br />
</span><span class="keyword">{<br />
&nbsp; public function </span><span class="default">something</span><span class="keyword">()<br />
&nbsp; {<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="default">__CLASS__</span><span class="keyword">; </span><span class="comment">// foo<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">);<br />
&nbsp; }<br />
}<br />
<br />
class </span><span class="default">foo_bar </span><span class="keyword">extends </span><span class="default">foo<br />
</span><span class="keyword">{<br />
&nbsp; public function </span><span class="default">something</span><span class="keyword">()<br />
&nbsp; {<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="default">__CLASS__</span><span class="keyword">; </span><span class="comment">// foo_bar<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">);<br />
&nbsp; }<br />
}<br />
<br />
class </span><span class="default">foo_bar_baz </span><span class="keyword">extends </span><span class="default">foo_bar<br />
</span><span class="keyword">{<br />
&nbsp; public function </span><span class="default">something</span><span class="keyword">()<br />
&nbsp; {<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="default">__CLASS__</span><span class="keyword">; </span><span class="comment">// foo_bar_baz<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">);<br />
&nbsp; }<br />
<br />
&nbsp; public function </span><span class="default">call</span><span class="keyword">()<br />
&nbsp; {<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="default">self</span><span class="keyword">::</span><span class="default">something</span><span class="keyword">(); </span><span class="comment">// self<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">echo </span><span class="default">parent</span><span class="keyword">::</span><span class="default">something</span><span class="keyword">(); </span><span class="comment">// parent<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">echo </span><span class="default">foo</span><span class="keyword">::</span><span class="default">something</span><span class="keyword">(); </span><span class="comment">// grandparent<br />
&nbsp; </span><span class="keyword">}<br />
}<br />
<br />
</span><span class="default">error_reporting</span><span class="keyword">(-</span><span class="default">1</span><span class="keyword">);<br />
<br />
</span><span class="default">$obj </span><span class="keyword">= new </span><span class="default">foo_bar_baz</span><span class="keyword">();<br />
</span><span class="default">$obj</span><span class="keyword">-&gt;</span><span class="default">call</span><span class="keyword">();<br />
<br />
</span><span class="comment">// Output similar to:<br />
// foo_bar_baz<br />
// object(foo_bar_baz)[1]<br />
// foo_bar<br />
// object(foo_bar_baz)[1]<br />
// foo<br />
// object(foo_bar_baz)[1]<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="97333"></a>
 <div class="note">
  <strong class='user'>jackdracona at msn dot com</strong>
  <a href="#97333" class="date">14-Apr-2010 08:53</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Here is some clarification about PHP inheritance – there is a lot of bad information on the net.&nbsp; PHP does support Multi-level inheritance.&nbsp; (I tested it using version 5.2.9).&nbsp; It does not support multiple inheritance.<br />
&nbsp;<br />
This means that you cannot have one class extend 2 other classes (see the extends keyword).&nbsp; However, you can have one class extend another, which extends another, and so on. <br />
&nbsp;<br />
Example:<br />
&nbsp;<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">A </span><span class="keyword">{<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// more code here<br />
</span><span class="keyword">}<br />
&nbsp;<br />
class </span><span class="default">B </span><span class="keyword">extends </span><span class="default">A </span><span class="keyword">{<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// more code here<br />
</span><span class="keyword">}<br />
&nbsp;<br />
class </span><span class="default">C </span><span class="keyword">extends </span><span class="default">B </span><span class="keyword">{<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// more code here<br />
</span><span class="keyword">}<br />
&nbsp;<br />
&nbsp;<br />
</span><span class="default">$someObj </span><span class="keyword">= new </span><span class="default">A</span><span class="keyword">();&nbsp; </span><span class="comment">// no problems<br />
</span><span class="default">$someOtherObj </span><span class="keyword">= new </span><span class="default">B</span><span class="keyword">(); </span><span class="comment">// no problems<br />
</span><span class="default">$lastObj </span><span class="keyword">= new </span><span class="default">C</span><span class="keyword">(); </span><span class="comment">// still no problems<br />
&nbsp;<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="97181"></a>
 <div class="note">
  <strong class='user'>php at sleep is the enemy dot co dot uk</strong>
  <a href="#97181" class="date">07-Apr-2010 02:36</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Here's fun, an attempt to make some degree of multiple inheritance work in PHP using mixins. It's not particularly pretty, doesn't support method visibility modifiers and, if put to any meaningful purpose, could well make your call stack balloon to Ruby-on-Rails-esque proportions, but it does work.<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">abstract class </span><span class="default">Mix </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; protected </span><span class="default">$_mixMap </span><span class="keyword">= array();<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">__construct</span><span class="keyword">(){<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">_mixMap </span><span class="keyword">= </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">collectMixins</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">__call</span><span class="keyword">(</span><span class="default">$method</span><span class="keyword">, </span><span class="default">$args</span><span class="keyword">){<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// doesn't pass scope<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; //return call_user_func_array(array($className, $method), $args);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; // Error: Given object is not an instance of the class this method was declared in<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; //$method = new ReflectionMethod($className, $method);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; //return $method-&gt;invokeArgs($this, $args);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$payload </span><span class="keyword">= </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">buildMixinPayload</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">_mixMap</span><span class="keyword">, </span><span class="default">$method</span><span class="keyword">, </span><span class="default">$args</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if(!</span><span class="default">$payload</span><span class="keyword">) throw new </span><span class="default">Exception</span><span class="keyword">(</span><span class="string">'Method ' </span><span class="keyword">. </span><span class="default">$method </span><span class="keyword">. </span><span class="string">' not found'</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; list(</span><span class="default">$mixinMethod</span><span class="keyword">, list(</span><span class="default">$method</span><span class="keyword">, </span><span class="default">$args</span><span class="keyword">)) = </span><span class="default">$payload</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">$mixinMethod</span><span class="keyword">(</span><span class="default">$method</span><span class="keyword">, </span><span class="default">$args</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; protected function </span><span class="default">collectMixins</span><span class="keyword">(</span><span class="default">$class</span><span class="keyword">){<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; static </span><span class="default">$found </span><span class="keyword">= array();<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; static </span><span class="default">$branch </span><span class="keyword">= array();<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if(empty(</span><span class="default">$branch</span><span class="keyword">)) </span><span class="default">$branch</span><span class="keyword">[] = </span><span class="default">get_class</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$mixins </span><span class="keyword">= array();<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; foreach(</span><span class="default">array_reverse</span><span class="keyword">(</span><span class="default">get_class_methods</span><span class="keyword">(</span><span class="default">$class</span><span class="keyword">)) as </span><span class="default">$method</span><span class="keyword">){<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; if(</span><span class="default">preg_match</span><span class="keyword">(</span><span class="string">'/^mixin(\w+)$/'</span><span class="keyword">, </span><span class="default">$method</span><span class="keyword">, </span><span class="default">$matches</span><span class="keyword">)){<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$className </span><span class="keyword">= </span><span class="default">$matches</span><span class="keyword">[</span><span class="default">1</span><span class="keyword">];<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; if(</span><span class="default">in_array</span><span class="keyword">(</span><span class="default">$className</span><span class="keyword">, </span><span class="default">$branch</span><span class="keyword">))<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; throw new </span><span class="default">Exception</span><span class="keyword">(</span><span class="string">'Circular reference detected ' </span><span class="keyword">. </span><span class="default">implode</span><span class="keyword">(</span><span class="string">' &gt; '</span><span class="keyword">, </span><span class="default">$branch</span><span class="keyword">) . </span><span class="string">' &gt; ' </span><span class="keyword">. </span><span class="default">$className</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; if(!</span><span class="default">in_array</span><span class="keyword">(</span><span class="default">$className</span><span class="keyword">, </span><span class="default">$found</span><span class="keyword">)){<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; if(!</span><span class="default">class_exists</span><span class="keyword">(</span><span class="default">$className</span><span class="keyword">)) throw new </span><span class="default">Exception</span><span class="keyword">(</span><span class="string">'Class ' </span><span class="keyword">. </span><span class="default">$className </span><span class="keyword">. </span><span class="string">' not found'</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// populate props from mixin class<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">foreach(</span><span class="default">get_class_vars</span><span class="keyword">(</span><span class="default">$className</span><span class="keyword">) as </span><span class="default">$key </span><span class="keyword">=&gt; </span><span class="default">$value</span><span class="keyword">){&nbsp; &nbsp; &nbsp; &nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; if(!</span><span class="default">property_exists</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">, </span><span class="default">$key</span><span class="keyword">)) </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">$key </span><span class="keyword">= </span><span class="default">$value</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$found</span><span class="keyword">[] = </span><span class="default">$branch</span><span class="keyword">[] = </span><span class="default">$className</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$mixins</span><span class="keyword">[</span><span class="default">$className</span><span class="keyword">] = </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">collectMixins</span><span class="keyword">(</span><span class="default">$className</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$branch </span><span class="keyword">= array(</span><span class="default">get_class</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">));<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$mixins</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; protected function </span><span class="default">buildMixinPayload</span><span class="keyword">(</span><span class="default">$mixins</span><span class="keyword">, </span><span class="default">$method</span><span class="keyword">, </span><span class="default">$args</span><span class="keyword">){<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; foreach(</span><span class="default">$mixins </span><span class="keyword">as </span><span class="default">$className </span><span class="keyword">=&gt; </span><span class="default">$parents</span><span class="keyword">){<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$mixinMethod </span><span class="keyword">= </span><span class="string">'mixin' </span><span class="keyword">. </span><span class="default">$className</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; if(</span><span class="default">method_exists</span><span class="keyword">(</span><span class="default">$className</span><span class="keyword">, </span><span class="default">$method</span><span class="keyword">)) return array(</span><span class="default">$mixinMethod</span><span class="keyword">, array(</span><span class="default">$method</span><span class="keyword">, </span><span class="default">$args</span><span class="keyword">));<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; if(!empty(</span><span class="default">$parents</span><span class="keyword">) &amp;&amp; </span><span class="default">$return </span><span class="keyword">= </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">buildMixinPayload</span><span class="keyword">(</span><span class="default">$parents</span><span class="keyword">, </span><span class="default">$method</span><span class="keyword">, </span><span class="default">$args</span><span class="keyword">)){<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return array(</span><span class="default">$mixinMethod</span><span class="keyword">, </span><span class="default">$return</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">false</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
}<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="97081"></a>
 <div class="note">
  <strong class='user'>php at sleep is the enemy dot co dot uk</strong>
  <a href="#97081" class="date">31-Mar-2010 07:47</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Here's some example usage of the mixin class.<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">class </span><span class="default">Lunch </span><span class="keyword">extends </span><span class="default">Mix </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; public </span><span class="default">$edible </span><span class="keyword">= </span><span class="default">true</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">/*<br />
&nbsp;&nbsp; &nbsp; * Circular references are, of course, illegal and will be detected<br />
&nbsp;&nbsp; &nbsp; */<br />
&nbsp;&nbsp;&nbsp; /*<br />
&nbsp;&nbsp;&nbsp; public function mixinSteakAndKidneyPie($method, $args){<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return SteakAndKidneyPie::$method(@$args[0], @$args[1], @$args[2], @$args[3], @$args[4], @$args[5]);<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; //*/<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">public function </span><span class="default">isEdible</span><span class="keyword">(</span><span class="default">$affirm</span><span class="keyword">, </span><span class="default">$negate</span><span class="keyword">){<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">edible </span><span class="keyword">? </span><span class="default">$affirm </span><span class="keyword">: </span><span class="default">$negate</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
class </span><span class="default">Pie </span><span class="keyword">extends </span><span class="default">Mix </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">/*<br />
&nbsp;&nbsp; &nbsp; * class tokens are bound at compile time so need to be explicitly declared<br />
&nbsp;&nbsp; &nbsp; * Need to make sure there are enough argument placeholders to cover all mixed in methods of Lunch<br />
&nbsp;&nbsp; &nbsp; * Late static binding may improve this situation<br />
&nbsp;&nbsp; &nbsp; */<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">public function </span><span class="default">mixinLunch</span><span class="keyword">(</span><span class="default">$method</span><span class="keyword">, </span><span class="default">$args</span><span class="keyword">){<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">Lunch</span><span class="keyword">::</span><span class="default">$method</span><span class="keyword">(@</span><span class="default">$args</span><span class="keyword">[</span><span class="default">0</span><span class="keyword">], @</span><span class="default">$args</span><span class="keyword">[</span><span class="default">1</span><span class="keyword">], @</span><span class="default">$args</span><span class="keyword">[</span><span class="default">2</span><span class="keyword">], @</span><span class="default">$args</span><span class="keyword">[</span><span class="default">3</span><span class="keyword">], @</span><span class="default">$args</span><span class="keyword">[</span><span class="default">4</span><span class="keyword">], @</span><span class="default">$args</span><span class="keyword">[</span><span class="default">5</span><span class="keyword">]);<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">buildPie</span><span class="keyword">(</span><span class="default">$sep </span><span class="keyword">= </span><span class="string">','</span><span class="keyword">){<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="string">'Crust' </span><span class="keyword">. </span><span class="default">$sep </span><span class="keyword">. </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">getFilling</span><span class="keyword">() . </span><span class="default">$sep </span><span class="keyword">. </span><span class="string">'More Crust'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">getFilling</span><span class="keyword">(){<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">edible </span><span class="keyword">= </span><span class="default">false</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="string">'Baking beans'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
class </span><span class="default">SteakAndKidney </span><span class="keyword">extends </span><span class="default">Mix </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">mixinLunch</span><span class="keyword">(</span><span class="default">$method</span><span class="keyword">, </span><span class="default">$args</span><span class="keyword">){<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">Lunch</span><span class="keyword">::</span><span class="default">$method</span><span class="keyword">(@</span><span class="default">$args</span><span class="keyword">[</span><span class="default">0</span><span class="keyword">], @</span><span class="default">$args</span><span class="keyword">[</span><span class="default">1</span><span class="keyword">], @</span><span class="default">$args</span><span class="keyword">[</span><span class="default">2</span><span class="keyword">], @</span><span class="default">$args</span><span class="keyword">[</span><span class="default">3</span><span class="keyword">], @</span><span class="default">$args</span><span class="keyword">[</span><span class="default">4</span><span class="keyword">], @</span><span class="default">$args</span><span class="keyword">[</span><span class="default">5</span><span class="keyword">]);<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">/*<br />
&nbsp;&nbsp; &nbsp; * everything to be mixed in must be public<br />
&nbsp;&nbsp; &nbsp; * protected/private methods called from within mixed in methods will fail<br />
&nbsp;&nbsp; &nbsp; */<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">public </span><span class="default">$filling </span><span class="keyword">= </span><span class="string">'Steak and Kidney'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">getFilling</span><span class="keyword">(){<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">edible </span><span class="keyword">= </span><span class="default">true</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">filling</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
class </span><span class="default">SteakAndKidneyPie </span><span class="keyword">extends </span><span class="default">Mix </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">/*<br />
&nbsp;&nbsp; &nbsp; * order of mixin declaration significant<br />
&nbsp;&nbsp; &nbsp; * later declarations override earlier ones<br />
&nbsp;&nbsp; &nbsp; */<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">public function </span><span class="default">mixinSteakAndKidney</span><span class="keyword">(</span><span class="default">$method</span><span class="keyword">, </span><span class="default">$args</span><span class="keyword">){<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">SteakAndKidney</span><span class="keyword">::</span><span class="default">$method</span><span class="keyword">(@</span><span class="default">$args</span><span class="keyword">[</span><span class="default">0</span><span class="keyword">], @</span><span class="default">$args</span><span class="keyword">[</span><span class="default">1</span><span class="keyword">], @</span><span class="default">$args</span><span class="keyword">[</span><span class="default">2</span><span class="keyword">], @</span><span class="default">$args</span><span class="keyword">[</span><span class="default">3</span><span class="keyword">], @</span><span class="default">$args</span><span class="keyword">[</span><span class="default">4</span><span class="keyword">], @</span><span class="default">$args</span><span class="keyword">[</span><span class="default">5</span><span class="keyword">]);<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">mixinPie</span><span class="keyword">(</span><span class="default">$method</span><span class="keyword">, </span><span class="default">$args</span><span class="keyword">){<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">Pie</span><span class="keyword">::</span><span class="default">$method</span><span class="keyword">(@</span><span class="default">$args</span><span class="keyword">[</span><span class="default">0</span><span class="keyword">], @</span><span class="default">$args</span><span class="keyword">[</span><span class="default">1</span><span class="keyword">], @</span><span class="default">$args</span><span class="keyword">[</span><span class="default">2</span><span class="keyword">], @</span><span class="default">$args</span><span class="keyword">[</span><span class="default">3</span><span class="keyword">], @</span><span class="default">$args</span><span class="keyword">[</span><span class="default">4</span><span class="keyword">], @</span><span class="default">$args</span><span class="keyword">[</span><span class="default">5</span><span class="keyword">]);<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">/*<br />
&nbsp;&nbsp; &nbsp; * Pick specific methods like so:<br />
&nbsp;&nbsp; &nbsp; */<br />
&nbsp;&nbsp;&nbsp; //*<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">public function </span><span class="default">getFilling</span><span class="keyword">(){<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">SteakAndKidney</span><span class="keyword">::</span><span class="default">getFilling</span><span class="keyword">();<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">//*/<br />
&nbsp;&nbsp;&nbsp; <br />
</span><span class="keyword">}<br />
<br />
</span><span class="default">$pie </span><span class="keyword">= new </span><span class="default">SteakAndKidneyPie</span><span class="keyword">();<br />
echo </span><span class="default">$pie</span><span class="keyword">-&gt;</span><span class="default">buildPie</span><span class="keyword">(</span><span class="string">' | '</span><span class="keyword">);<br />
echo </span><span class="string">'&lt;br/&gt;Pie ' </span><span class="keyword">. </span><span class="default">$pie</span><span class="keyword">-&gt;</span><span class="default">isEdible</span><span class="keyword">(</span><span class="string">'is'</span><span class="keyword">, </span><span class="string">'is not'</span><span class="keyword">) . </span><span class="string">' Edible'</span><span class="keyword">;<br />
<br />
</span><span class="comment">/*<br />
OUTPUTS:<br />
Crust | Steak and Kidney | More Crust<br />
Pie is Edible<br />
*/<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="94288"></a>
 <div class="note">
  <strong class='user'>jarrod at squarecrow dot com</strong>
  <a href="#94288" class="date">27-Oct-2009 06:01</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
You can force a class to be strictly an inheritable class by using the "abstract" keyword. When you define a class with abstract, any attempt to instantiate a separate instance of it will result in a fatal error. This is useful for situations like a base class where it would be inherited by multiple child classes yet you want to restrict the ability to instantiate it by itself.<br />
<br />
Example........<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">abstract class </span><span class="default">Cheese<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp; &nbsp;&nbsp; </span><span class="comment">//can ONLY be inherited by another class<br />
</span><span class="keyword">}<br />
<br />
class </span><span class="default">Cheddar </span><span class="keyword">extends </span><span class="default">Cheese<br />
</span><span class="keyword">{<br />
}<br />
<br />
</span><span class="default">$dinner </span><span class="keyword">= new </span><span class="default">Cheese</span><span class="keyword">; </span><span class="comment">//fatal error<br />
</span><span class="default">$lunch </span><span class="keyword">= new </span><span class="default">Cheddar</span><span class="keyword">; </span><span class="comment">//works!<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=language.oop5.inheritance&amp;redirect=http://www.php.net/manual/en/language.oop5.inheritance.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.oop5.inheritance&amp;redirect=http://www.php.net/manual/en/language.oop5.inheritance.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/language.oop5.inheritance.php">show source</a> |
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