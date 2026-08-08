<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Properties - Manual</title>
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
 <link rel="prev" href="language.oop5.basic.php" />
 <link rel="next" href="language.oop5.constants.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/oop5.properties" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/language.oop5.properties.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/language.oop5.properties.php" />
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
 <li class="active"><a href="language.oop5.properties.php">Properties</a></li>
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
 <li><a href="language.oop5.references.php">Objects and references</a></li>
 <li><a href="language.oop5.serialization.php">Object Serialization</a></li>
 <li><a href="language.oop5.changelog.php">OOP Changelog</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="language.oop5.constants.php">Class Constants<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.oop5.basic.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />The Basics</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.oop5.properties.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/language.oop5.properties.php">Brazilian Portuguese</option>
    <option value="zh/language.oop5.properties.php">Chinese (Simplified)</option>
    <option value="fr/language.oop5.properties.php">French</option>
    <option value="de/language.oop5.properties.php">German</option>
    <option value="ja/language.oop5.properties.php">Japanese</option>
    <option value="pl/language.oop5.properties.php">Polish</option>
    <option value="ro/language.oop5.properties.php">Romanian</option>
    <option value="ru/language.oop5.properties.php">Russian</option>
    <option value="fa/language.oop5.properties.php">Persian</option>
    <option value="es/language.oop5.properties.php">Spanish</option>
    <option value="tr/language.oop5.properties.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="language.oop5.properties" class="sect1">
  <h2 class="title">Properties</h2>

  <p class="para">
   Class member variables are called &quot;properties&quot;. You may also see
   them referred to using other terms such as &quot;attributes&quot; or
   &quot;fields&quot;, but for the purposes of this reference we will use
   &quot;properties&quot;. They are defined by using one of the
   keywords <em>public</em>, <em>protected</em>,
   or <em>private</em>, followed by a normal variable
   declaration. This declaration may include an initialization, but
   this initialization must be a constant value--that is, it must be
   able to be evaluated at compile time and must not depend on
   run-time information in order to be evaluated.
  </p>
  <p class="para">
   See <a href="language.oop5.visibility.php" class="xref">Visibility</a> for more
   information on the meanings
   of <em>public</em>, <em>protected</em>,
   and <em>private</em>.
  </p>
  <blockquote class="note"><p><strong class="note">Note</strong>: 
   <p class="para">
    In order to maintain backward compatibility with PHP 4, PHP 5 will
    still accept the use of the keyword <em>var</em> in
    property declarations instead of (or in addition
    to) <em>public</em>, <em>protected</em>,
    or <em>private</em>. However, <em>var</em> is
    no longer required. In versions of PHP from 5.0 to 5.1.3, the use
    of <em>var</em> was considered deprecated and would
    issue an <strong><code>E_STRICT</code></strong> warning, but since PHP
    5.1.3 it is no longer deprecated and does not issue the warning.
   </p>
   <p class="para">
    If you declare a property using <em>var</em> instead of
    one of <em>public</em>, <em>protected</em>,
    or <em>private</em>, then PHP 5 will treat the property
    as if it had been declared as <em>public</em>.
   </p>
  </p></blockquote>
  <p class="para">
   Within class methods the properties, constants, and methods may be
   accessed by using the form <var class="varname"><var class="varname">$this->property</var></var>
   (where <em>property</em> is the name of the property)
   unless the access is to a static property within the context of a
   static class method, in which case it is accessed using the
   form <var class="varname"><var class="varname">self::$property</var></var>. See <a href="language.oop5.static.php" class="link">Static
   Keyword</a> for more information.
  </p>
  <p class="para">
   The pseudo-variable <var class="varname"><var class="varname">$this</var></var> is available inside
   any class method when that method is called from within an object
   context. <var class="varname"><var class="varname">$this</var></var> is a reference to the calling
   object (usually the object to which the method belongs, but
   possibly another object, if the method is called
   <a href="language.oop5.static.php" class="link">statically</a> from the context
   of a secondary object).
  </p>

  <p class="para">
   <div class="example" id="example-170">
    <p><strong>Example #1 property declarations</strong></p>
    <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">class&nbsp;</span><span style="color: #0000BB">SimpleClass<br /></span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;invalid&nbsp;property&nbsp;declarations:<br />&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">public&nbsp;</span><span style="color: #0000BB">$var1&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'hello&nbsp;'&nbsp;</span><span style="color: #007700">.&nbsp;</span><span style="color: #DD0000">'world'</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;public&nbsp;</span><span style="color: #0000BB">$var2&nbsp;</span><span style="color: #007700">=&nbsp;&lt;&lt;&lt;EOD<br /></span><span style="color: #DD0000">hello&nbsp;world<br /></span><span style="color: #007700">EOD;<br />&nbsp;&nbsp;&nbsp;public&nbsp;</span><span style="color: #0000BB">$var3&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">1</span><span style="color: #007700">+</span><span style="color: #0000BB">2</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;public&nbsp;</span><span style="color: #0000BB">$var4&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">self</span><span style="color: #007700">::</span><span style="color: #0000BB">myStaticMethod</span><span style="color: #007700">();<br />&nbsp;&nbsp;&nbsp;public&nbsp;</span><span style="color: #0000BB">$var5&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">$myVar</span><span style="color: #007700">;<br /><br />&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;valid&nbsp;property&nbsp;declarations:<br />&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">public&nbsp;</span><span style="color: #0000BB">$var6&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">myConstant</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;public&nbsp;</span><span style="color: #0000BB">$var7&nbsp;</span><span style="color: #007700">=&nbsp;array(</span><span style="color: #0000BB">true</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">false</span><span style="color: #007700">);<br /><br />&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;This&nbsp;is&nbsp;allowed&nbsp;only&nbsp;in&nbsp;PHP&nbsp;5.3.0&nbsp;and&nbsp;later.<br />&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">public&nbsp;</span><span style="color: #0000BB">$var8&nbsp;</span><span style="color: #007700">=&nbsp;&lt;&lt;&lt;'EOD'<br /></span><span style="color: #DD0000">hello&nbsp;world<br /></span><span style="color: #007700">EOD;<br />}<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>
   
   </div>
  </p>

  <blockquote class="note"><p><strong class="note">Note</strong>: 
   <p class="para">
    There are some nice functions to handle classes and objects. You
    might want to take a look at
    the <a href="ref.classobj.php" class="link">Class/Object Functions</a>.
   </p>
  </p></blockquote>

  <p class="para">
   Unlike
   <a href="language.types.string.php#language.types.string.syntax.heredoc" class="link">heredocs</a>, 
   <a href="language.types.string.php#language.types.string.syntax.nowdoc" class="link">nowdocs</a>
   can be used in any static data context, including property
   declarations.
   <div class="example" id="example-171">
    <p><strong>Example #2 Example of using a nowdoc to initialize a property</strong></p>
    <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">class&nbsp;</span><span style="color: #0000BB">foo&nbsp;</span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;As&nbsp;of&nbsp;PHP&nbsp;5.3.0<br />&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">public&nbsp;</span><span style="color: #0000BB">$bar&nbsp;</span><span style="color: #007700">=&nbsp;&lt;&lt;&lt;'EOT'<br /></span><span style="color: #DD0000">bar<br /></span><span style="color: #007700">EOT;<br />}<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

   </div>
  </p>
  <blockquote class="note"><p><strong class="note">Note</strong>: 
   <p class="para">
    Nowdoc support was added in PHP 5.3.0.
   </p>
  </p></blockquote>
 </div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="language.oop5.constants.php">Class Constants<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.oop5.basic.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />The Basics</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.oop5.properties.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=language.oop5.properties&amp;redirect=http://www.php.net/manual/en/language.oop5.properties.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.oop5.properties&amp;redirect=http://www.php.net/manual/en/language.oop5.properties.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Properties</strong>
 </div><div id="allnotes">
 <a name="108320"></a>
 <div class="note">
  <strong class='user'>Anonymous</strong>
  <a href="#108320" class="date">17-Apr-2012 09:16</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
In case this saves anyone any time, I spent ages working out why the following didn't work:<br />
<br />
class MyClass<br />
{<br />
&nbsp;&nbsp;&nbsp; private $foo = FALSE;<br />
<br />
&nbsp;&nbsp;&nbsp; public function __construct()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; $this-&gt;$foo = TRUE;<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo($this-&gt;$foo);<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
$bar = new MyClass();<br />
<br />
giving "Fatal error: Cannot access empty property in ...test_class.php on line 8"<br />
<br />
The subtle change of removing the $ before accesses of $foo fixes this:<br />
<br />
class MyClass<br />
{<br />
&nbsp;&nbsp;&nbsp; private $foo = FALSE;<br />
<br />
&nbsp;&nbsp;&nbsp; public function __construct()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; $this-&gt;foo = TRUE;<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo($this-&gt;foo);<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
$bar = new MyClass();<br />
<br />
I guess because it's treating $foo like a variable in the first example, so trying to call $this-&gt;FALSE (or something along those lines) which makes no sense. It's obvious once you've realised, but there aren't any examples of accessing on this page that show that.</span>
</code></div>
  </div>
 </div>
 <a name="102880"></a>
 <div class="note">
  <strong class='user'>Anonymous</strong>
  <a href="#102880" class="date">11-Mar-2011 03:18</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
$this can be cast to array.&nbsp; But when doing so, it prefixes the property names/new array keys with certain data depending on the property classification.&nbsp; Public property names are not changed.&nbsp; Protected properties are prefixed with a space-padded '*'.&nbsp; Private properties are prefixed with the space-padded class name...<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">class </span><span class="default">test<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public </span><span class="default">$var1 </span><span class="keyword">= </span><span class="default">1</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; protected </span><span class="default">$var2 </span><span class="keyword">= </span><span class="default">2</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; private </span><span class="default">$var3 </span><span class="keyword">= </span><span class="default">3</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; static </span><span class="default">$var4 </span><span class="keyword">= </span><span class="default">4</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">toArray</span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return (array) </span><span class="default">$this</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="default">$t </span><span class="keyword">= new </span><span class="default">test</span><span class="keyword">;<br />
</span><span class="default">print_r</span><span class="keyword">(</span><span class="default">$t</span><span class="keyword">-&gt;</span><span class="default">toArray</span><span class="keyword">());<br />
<br />
</span><span class="comment">/* outputs:<br />
<br />
Array<br />
(<br />
&nbsp;&nbsp;&nbsp; [var1] =&gt; 1<br />
&nbsp;&nbsp;&nbsp; [ * var2] =&gt; 2<br />
&nbsp;&nbsp;&nbsp; [ test var3] =&gt; 3<br />
)<br />
<br />
*/<br />
</span><span class="default">?&gt;<br />
</span><br />
This is documented behavior when converting any object to an array (see &lt;/language.types.array.php#language.types.array.casting&gt; PHP manual page).&nbsp; All properties regardless of visibility will be shown when casting an object to array (with exceptions of a few built-in objects).<br />
<br />
To get an array with all property names unaltered, use the 'get_object_vars($this)' function in any method within class scope to retrieve an array of all properties regardless of external visibility, or 'get_object_vars($object)' outside class scope to retrieve an array of only public properties (see: &lt;/function.get-object-vars.php&gt; PHP manual page).</span>
</code></div>
  </div>
 </div>
 <a name="98267"></a>
 <div class="note">
  <strong class='user'>zzzzBov</strong>
  <a href="#98267" class="date">04-Jun-2010 09:21</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Do not confuse php's version of properties with properties in other languages (C++ for example).&nbsp; In php, properties are the same as attributes, simple variables without functionality.&nbsp; They should be called attributes, not properties.<br />
<br />
Properties have implicit accessor and mutator functionality.&nbsp; I've created an abstract class that allows implicit property functionality.<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">abstract class </span><span class="default">PropertyObject<br />
</span><span class="keyword">{<br />
&nbsp; public function </span><span class="default">__get</span><span class="keyword">(</span><span class="default">$name</span><span class="keyword">)<br />
&nbsp; {<br />
&nbsp;&nbsp;&nbsp; if (</span><span class="default">method_exists</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">, (</span><span class="default">$method </span><span class="keyword">= </span><span class="string">'get_'</span><span class="keyword">.</span><span class="default">$name</span><span class="keyword">)))<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp;&nbsp; return </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">$method</span><span class="keyword">();<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; else return;<br />
&nbsp; }<br />
&nbsp; <br />
&nbsp; public function </span><span class="default">__isset</span><span class="keyword">(</span><span class="default">$name</span><span class="keyword">)<br />
&nbsp; {<br />
&nbsp;&nbsp;&nbsp; if (</span><span class="default">method_exists</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">, (</span><span class="default">$method </span><span class="keyword">= </span><span class="string">'isset_'</span><span class="keyword">.</span><span class="default">$name</span><span class="keyword">)))<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp;&nbsp; return </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">$method</span><span class="keyword">();<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; else return;<br />
&nbsp; }<br />
&nbsp; <br />
&nbsp; public function </span><span class="default">__set</span><span class="keyword">(</span><span class="default">$name</span><span class="keyword">, </span><span class="default">$value</span><span class="keyword">)<br />
&nbsp; {<br />
&nbsp;&nbsp;&nbsp; if (</span><span class="default">method_exists</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">, (</span><span class="default">$method </span><span class="keyword">= </span><span class="string">'set_'</span><span class="keyword">.</span><span class="default">$name</span><span class="keyword">)))<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">$method</span><span class="keyword">(</span><span class="default">$value</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp; }<br />
&nbsp; <br />
&nbsp; public function </span><span class="default">__unset</span><span class="keyword">(</span><span class="default">$name</span><span class="keyword">)<br />
&nbsp; {<br />
&nbsp;&nbsp;&nbsp; if (</span><span class="default">method_exists</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">, (</span><span class="default">$method </span><span class="keyword">= </span><span class="string">'unset_'</span><span class="keyword">.</span><span class="default">$name</span><span class="keyword">)))<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">$method</span><span class="keyword">();<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp; }<br />
}<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
after extending this class, you can create accessors and mutators that will be called automagically, using php's magic methods, when the corresponding property is accessed.</span>
</code></div>
  </div>
 </div>
 <a name="94665"></a>
 <div class="note">
  <strong class='user'>Anonymous</strong>
  <a href="#94665" class="date">17-Nov-2009 07:51</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
As of PHP 5.3.0, heredocs can also be used in property declarations.<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">foo </span><span class="keyword">{<br />
&nbsp;&nbsp; </span><span class="comment">// As of PHP 5.3.0<br />
&nbsp;&nbsp; </span><span class="keyword">public </span><span class="default">$bar </span><span class="keyword">= &lt;&lt;&lt;EOT<br />
</span><span class="default">bar<br />
</span><span class="keyword">EOT;<br />
}<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=language.oop5.properties&amp;redirect=http://www.php.net/manual/en/language.oop5.properties.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.oop5.properties&amp;redirect=http://www.php.net/manual/en/language.oop5.properties.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/language.oop5.properties.php">show source</a> |
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