<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: The Basics - Manual</title>
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
 <link rel="prev" href="oop5.intro.php" />
 <link rel="next" href="language.oop5.properties.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/oop5.basic" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/language.oop5.basic.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/language.oop5.basic.php" />
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
 <li class="active"><a href="language.oop5.basic.php">The Basics</a></li>
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
 <li><a href="language.oop5.references.php">Objects and references</a></li>
 <li><a href="language.oop5.serialization.php">Object Serialization</a></li>
 <li><a href="language.oop5.changelog.php">OOP Changelog</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="language.oop5.properties.php">Properties<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="oop5.intro.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Introduction</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.oop5.basic.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/language.oop5.basic.php">Brazilian Portuguese</option>
    <option value="zh/language.oop5.basic.php">Chinese (Simplified)</option>
    <option value="fr/language.oop5.basic.php">French</option>
    <option value="de/language.oop5.basic.php">German</option>
    <option value="ja/language.oop5.basic.php">Japanese</option>
    <option value="pl/language.oop5.basic.php">Polish</option>
    <option value="ro/language.oop5.basic.php">Romanian</option>
    <option value="ru/language.oop5.basic.php">Russian</option>
    <option value="fa/language.oop5.basic.php">Persian</option>
    <option value="es/language.oop5.basic.php">Spanish</option>
    <option value="tr/language.oop5.basic.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="language.oop5.basic" class="sect1">
  <h2 class="title">The Basics</h2>

  <div class="sect2" id="language.oop5.basic.class">
   <h3 class="title">class</h3>
   <p class="para">
    Basic class definitions begin with the
    keyword <em>class</em>, followed by a class name,
    followed by a pair of curly braces which enclose the definitions
    of the properties and methods belonging to the class.
   </p>
   <p class="para">
    The class name can be any valid label which is a not a
    PHP <a href="reserved.php" class="link">reserved word</a>. A valid class
    name starts with a letter or underscore, followed by any number of
    letters, numbers, or underscores. As a regular expression, it
    would be expressed thus:
    <em>[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*</em>.
   </p>
   <p class="para">
    A class may contain its
    own <a href="language.oop5.constants.php" class="link">constants</a>, <a href="language.oop5.properties.php" class="link">variables</a>
    (called &quot;properties&quot;), and functions (called &quot;methods&quot;).
   </p>
   <div class="example" id="example-164">
    <p><strong>Example #1 Simple Class definition</strong></p>
    <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">class&nbsp;</span><span style="color: #0000BB">SimpleClass<br /></span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;property&nbsp;declaration<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">public&nbsp;</span><span style="color: #0000BB">$var&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'a&nbsp;default&nbsp;value'</span><span style="color: #007700">;<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;method&nbsp;declaration<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">public&nbsp;function&nbsp;</span><span style="color: #0000BB">displayVar</span><span style="color: #007700">()&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">var</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

   </div>
   <p class="para">
    The pseudo-variable <var class="varname"><var class="varname">$this</var></var> is available when a
    method is called from within an object
    context. <var class="varname"><var class="varname">$this</var></var> is a reference to the calling
    object (usually the object to which the method belongs, but
    possibly another object, if the method is called
    <a href="language.oop5.static.php" class="link">statically</a> from the context
    of a secondary object).
   </p>
   <p class="para">
    <div class="example" id="language.oop5.basic.class.this">
     <p><strong>Example #2 Some examples of the <var class="varname"><var class="varname">$this</var></var> pseudo-variable</strong></p>
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">class&nbsp;</span><span style="color: #0000BB">A<br /></span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;function&nbsp;</span><span style="color: #0000BB">foo</span><span style="color: #007700">()<br />&nbsp;&nbsp;&nbsp;&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;if&nbsp;(isset(</span><span style="color: #0000BB">$this</span><span style="color: #007700">))&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">'$this&nbsp;is&nbsp;defined&nbsp;('</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #0000BB">get_class</span><span style="color: #007700">(</span><span style="color: #0000BB">$this</span><span style="color: #007700">);<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">")\n"</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}&nbsp;else&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"\$this&nbsp;is&nbsp;not&nbsp;defined.\n"</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br /><br />class&nbsp;</span><span style="color: #0000BB">B<br /></span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;function&nbsp;</span><span style="color: #0000BB">bar</span><span style="color: #007700">()<br />&nbsp;&nbsp;&nbsp;&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;Note:&nbsp;the&nbsp;next&nbsp;line&nbsp;will&nbsp;issue&nbsp;a&nbsp;warning&nbsp;if&nbsp;E_STRICT&nbsp;is&nbsp;enabled.<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">A</span><span style="color: #007700">::</span><span style="color: #0000BB">foo</span><span style="color: #007700">();<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br /><br /></span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">A</span><span style="color: #007700">();<br /></span><span style="color: #0000BB">$a</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">foo</span><span style="color: #007700">();<br /><br /></span><span style="color: #FF8000">//&nbsp;Note:&nbsp;the&nbsp;next&nbsp;line&nbsp;will&nbsp;issue&nbsp;a&nbsp;warning&nbsp;if&nbsp;E_STRICT&nbsp;is&nbsp;enabled.<br /></span><span style="color: #0000BB">A</span><span style="color: #007700">::</span><span style="color: #0000BB">foo</span><span style="color: #007700">();<br /></span><span style="color: #0000BB">$b&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">B</span><span style="color: #007700">();<br /></span><span style="color: #0000BB">$b</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">bar</span><span style="color: #007700">();<br /><br /></span><span style="color: #FF8000">//&nbsp;Note:&nbsp;the&nbsp;next&nbsp;line&nbsp;will&nbsp;issue&nbsp;a&nbsp;warning&nbsp;if&nbsp;E_STRICT&nbsp;is&nbsp;enabled.<br /></span><span style="color: #0000BB">B</span><span style="color: #007700">::</span><span style="color: #0000BB">bar</span><span style="color: #007700">();<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

     <div class="example-contents"><p>The above example will output:</p></div>
     <div class="example-contents screen">
<div class="cdata"><pre>
$this is defined (A)
$this is not defined.
$this is defined (B)
$this is not defined.
</pre></div>
     </div>
    </div>
   </p>
  </div>

  <div class="sect2" id="language.oop5.basic.new">
   <h3 class="title">new</h3>
   <p class="para">
    To create an instance of a class, the <em>new</em> keyword must
    be used.  An object will always be created unless the object has a
    <a href="language.oop5.decon.php" class="link">constructor</a> defined that throws an
    <a href="language.exceptions.php" class="link">exception</a> on error. Classes
    should be defined before instantiation (and in some cases this is a
    requirement).
   </p>
   <p class="para">
    If a <span class="type"><a href="language.types.string.php" class="type string">string</a></span> containing the name of a class is used with
    <em>new</em>, a new instance of that class will be created. If
    the class is in a namespace, its fully qualified name must be used when
    doing this.
   </p>
   <div class="example" id="example-166">
    <p><strong>Example #3 Creating an instance</strong></p>
    <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br />$instance&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">SimpleClass</span><span style="color: #007700">();<br /><br /></span><span style="color: #FF8000">//&nbsp;This&nbsp;can&nbsp;also&nbsp;be&nbsp;done&nbsp;with&nbsp;a&nbsp;variable:<br /></span><span style="color: #0000BB">$className&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'Foo'</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">$instance&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">$className</span><span style="color: #007700">();&nbsp;</span><span style="color: #FF8000">//&nbsp;Foo()<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

   </div>
   <p class="para">
    In the class context, it is possible to create a new object by
    <em>new self</em> and <em>new parent</em>.
   </p>
   <p class="para">
    When assigning an already created instance of a class to a new variable, the new variable
    will access the same instance as the object that was assigned. This
    behaviour is the same when passing instances to a function. A copy
    of an already created object can be made by
    <a href="language.oop5.cloning.php" class="link">cloning</a> it.
   </p>
   <div class="example" id="example-167">
    <p><strong>Example #4 Object Assignment</strong></p>
    <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /><br />$instance&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">SimpleClass</span><span style="color: #007700">();<br /><br /></span><span style="color: #0000BB">$assigned&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">=&nbsp;&nbsp;</span><span style="color: #0000BB">$instance</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">$reference&nbsp;&nbsp;</span><span style="color: #007700">=&amp;&nbsp;</span><span style="color: #0000BB">$instance</span><span style="color: #007700">;<br /><br /></span><span style="color: #0000BB">$instance</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">var&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'$assigned&nbsp;will&nbsp;have&nbsp;this&nbsp;value'</span><span style="color: #007700">;<br /><br /></span><span style="color: #0000BB">$instance&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">null</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;$instance&nbsp;and&nbsp;$reference&nbsp;become&nbsp;null<br /><br /></span><span style="color: #0000BB">var_dump</span><span style="color: #007700">(</span><span style="color: #0000BB">$instance</span><span style="color: #007700">);<br /></span><span style="color: #0000BB">var_dump</span><span style="color: #007700">(</span><span style="color: #0000BB">$reference</span><span style="color: #007700">);<br /></span><span style="color: #0000BB">var_dump</span><span style="color: #007700">(</span><span style="color: #0000BB">$assigned</span><span style="color: #007700">);<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

    <div class="example-contents"><p>The above example will output:</p></div>
    <div class="example-contents screen">
<div class="cdata"><pre>
NULL
NULL
object(SimpleClass)#1 (1) {
   [&quot;var&quot;]=&gt;
     string(30) &quot;$assigned will have this value&quot;
}
</pre></div>
    </div>
   </div>
   <p class="para">
    PHP 5.3.0 introduced a couple of new ways to create instances of an
    object:
   </p>
   <div class="example" id="example-168">
    <p><strong>Example #5 Creating new objects</strong></p>
    <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">class&nbsp;</span><span style="color: #0000BB">Test<br /></span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;static&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">getNew</span><span style="color: #007700">()<br />&nbsp;&nbsp;&nbsp;&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;return&nbsp;new&nbsp;static;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br /><br />class&nbsp;</span><span style="color: #0000BB">Child&nbsp;</span><span style="color: #007700">extends&nbsp;</span><span style="color: #0000BB">Test<br /></span><span style="color: #007700">{}<br /><br /></span><span style="color: #0000BB">$obj1&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">Test</span><span style="color: #007700">();<br /></span><span style="color: #0000BB">$obj2&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">$obj1</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">var_dump</span><span style="color: #007700">(</span><span style="color: #0000BB">$obj1&nbsp;</span><span style="color: #007700">!==&nbsp;</span><span style="color: #0000BB">$obj2</span><span style="color: #007700">);<br /><br /></span><span style="color: #0000BB">$obj3&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">Test</span><span style="color: #007700">::</span><span style="color: #0000BB">getNew</span><span style="color: #007700">();<br /></span><span style="color: #0000BB">var_dump</span><span style="color: #007700">(</span><span style="color: #0000BB">$obj3&nbsp;</span><span style="color: #007700">instanceof&nbsp;</span><span style="color: #0000BB">Test</span><span style="color: #007700">);<br /><br /></span><span style="color: #0000BB">$obj4&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">Child</span><span style="color: #007700">::</span><span style="color: #0000BB">getNew</span><span style="color: #007700">();<br /></span><span style="color: #0000BB">var_dump</span><span style="color: #007700">(</span><span style="color: #0000BB">$obj4&nbsp;</span><span style="color: #007700">instanceof&nbsp;</span><span style="color: #0000BB">Child</span><span style="color: #007700">);<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

    <div class="example-contents"><p>The above example will output:</p></div>
    <div class="example-contents screen">
<div class="cdata"><pre>
bool(true)
bool(true)
bool(true)
</pre></div>
    </div>
   </div>
  </div>

  <div class="sect2" id="language.oop5.basic.extends">
   <h3 class="title">extends</h3>
   <p class="para">
    A class can inherit the methods and properties of another class by
    using the keyword <em>extends</em> in the class
    declaration. It is not possible to extend multiple classes; a
    class can only inherit from one base class.
   </p>
   <p class="para">
    The inherited methods and properties can be overridden by
    redeclaring them with the same name defined in the parent
    class. However, if the parent class has defined a method
    as <a href="language.oop5.final.php" class="link">final</a>, that method
    may not be overridden.  It is possible to access the overridden
    methods or static properties by referencing them
    with <a href="language.oop5.paamayim-nekudotayim.php" class="link">parent::</a>.
   </p>
   <p class="para">
    When overriding methods, the parameter signature should remain the same or
    PHP will generate an <strong><code>E_STRICT</code></strong> level error. This does
    not apply to the constructor, which allows overriding with different
    parameters.
   </p>
   <div class="example" id="example-169">
    <p><strong>Example #6 Simple Class Inheritance</strong></p>
    <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">class&nbsp;</span><span style="color: #0000BB">ExtendClass&nbsp;</span><span style="color: #007700">extends&nbsp;</span><span style="color: #0000BB">SimpleClass<br /></span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;Redefine&nbsp;the&nbsp;parent&nbsp;method<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">function&nbsp;</span><span style="color: #0000BB">displayVar</span><span style="color: #007700">()<br />&nbsp;&nbsp;&nbsp;&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"Extending&nbsp;class\n"</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">parent</span><span style="color: #007700">::</span><span style="color: #0000BB">displayVar</span><span style="color: #007700">();<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br /><br /></span><span style="color: #0000BB">$extended&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">ExtendClass</span><span style="color: #007700">();<br /></span><span style="color: #0000BB">$extended</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">displayVar</span><span style="color: #007700">();<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

    <div class="example-contents"><p>The above example will output:</p></div>
    <div class="example-contents screen">
<div class="cdata"><pre>
Extending class
a default value
</pre></div>
    </div>
   </div>
  </div>

 </div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="language.oop5.properties.php">Properties<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="oop5.intro.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Introduction</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.oop5.basic.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=language.oop5.basic&amp;redirect=http://www.php.net/manual/en/language.oop5.basic.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.oop5.basic&amp;redirect=http://www.php.net/manual/en/language.oop5.basic.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>The Basics</strong>
 </div><div id="allnotes">
 <a name="108992"></a>
 <div class="note">
  <strong class='user'>ccheeboon at yahoo dot com</strong>
  <a href="#108992" class="date">11-Jun-2012 06:32</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
there are time we would like an object to self destruct if the initializing argument provided during object creation, dictate the object should exist at all.<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">TestClass<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; private </span><span class="default">$have_girlfren</span><span class="keyword">=</span><span class="default">false</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">//<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">private function </span><span class="default">__construct</span><span class="keyword">( </span><span class="default">$have_girlfren </span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">"sixth day&lt;br&gt;<br />
&nbsp;"</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">have_girlfren</span><span class="keyword">=</span><span class="default">$have_girlfren</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">existenceRequirement</span><span class="keyword">();&nbsp; &nbsp; &nbsp; &nbsp; <br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">//<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">private function </span><span class="default">existenceRequirement</span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if( </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">have_girlfren</span><span class="keyword">==</span><span class="default">false </span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">'i want to die&lt;br&gt;<br />
'</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">__destruct</span><span class="keyword">();<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; throw new </span><span class="default">Exception</span><span class="keyword">();<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">///////////////////////<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">function </span><span class="default">__destruct</span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">"i am dead&lt;br&gt;<br />
"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">//////////////////<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">static function </span><span class="default">getNewInstance</span><span class="keyword">( </span><span class="default">$have_girlfren</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; try{<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$new_obj</span><span class="keyword">=new </span><span class="default">TestClass</span><span class="keyword">( </span><span class="default">$have_girlfren</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$new_obj</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; catch(</span><span class="default">Exception $e</span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; unset(</span><span class="default">$new_obj</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">"rest in peace&lt;br&gt;<br />
"</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">false</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }&nbsp; &nbsp; <br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">//<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">function </span><span class="default">gotGirlFren</span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">have_girlfren</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="default">$test_obj</span><span class="keyword">=</span><span class="default">TestClass</span><span class="keyword">::</span><span class="default">getNewInstance</span><span class="keyword">( </span><span class="default">false </span><span class="keyword">);<br />
if( </span><span class="default">$test_obj </span><span class="keyword">)<br />
echo </span><span class="string">"bye test oby closing&lt;br&gt;<br />
"</span><span class="keyword">;<br />
else<br />
echo </span><span class="string">"instance commit suicide&lt;br&gt;<br />
"</span><span class="keyword">;<br />
echo </span><span class="string">"--------------&lt;br&gt;<br />
"</span><span class="keyword">;<br />
echo </span><span class="string">"-the end--"</span><span class="keyword">;<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="108736"></a>
 <div class="note">
  <strong class='user'>Jeffrey</strong>
  <a href="#108736" class="date">21-May-2012 10:36</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The PHYSICS OF THE SYSTEM<br />
<br />
You cannot take the PHYSICS out of any SYSTEM (or environment) -- man-made OR natural. In every system, there lies the laws of physics (the laws of nature). For example, a moving object -- in a natural environment OR in a lab -- is always subject to the same forces that exist anywhere. Even a computer program has an environment, for example:<br />
<br />
<span class="default">&lt;?php<br />
<br />
?&gt;<br />
</span><br />
Even though it had a very short life, it had an ENVIRONMENT which had POTENTIAL TO DO WORK, which made it a good candidate for a useful SYSTEM. If we think about a 3-Dimensional space as an environment, it is easy to imagine putting 'things' or 'objects' in that space AND doing things to them.<br />
<br />
In the next invocation of PHP below, I am going to:<br />
&nbsp;1. Create a 3-Dimensional space or ENVIRONMENT -- just by using the '&lt;?php' tag.<br />
&nbsp;2. Define the SYSTEM we want to use -- laws, rules, policies, structure, properties etc. -- with valid PHP class definitions.<br />
&nbsp;3. Instantiate objects with the 'new' operator so we can poke it for a while.<br />
&nbsp;4. Output some data to see if they obey the rules we wrote.<br />
&nbsp;5. Here we go!<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="comment">// 1. check, ENVIRONMENT created.<br />
<br />
</span><span class="keyword">class </span><span class="default">Wheel<br />
</span><span class="keyword">{<br />
&nbsp; public </span><span class="default">$cir </span><span class="keyword">= </span><span class="default">3.0</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="comment">// circumference in feet.<br />
&nbsp; </span><span class="keyword">public </span><span class="default">$rpm </span><span class="keyword">= </span><span class="default">0.0</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="comment">// rotations (spins) per minute.<br />
<br />
&nbsp; </span><span class="keyword">public function </span><span class="default">__toString</span><span class="keyword">()<br />
&nbsp; {<br />
&nbsp;&nbsp;&nbsp; return </span><span class="string">'Wheel [' </span><span class="keyword">.<br />
&nbsp;&nbsp;&nbsp; </span><span class="string">'cir=' </span><span class="keyword">. </span><span class="default">number_format</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">cir</span><span class="keyword">, </span><span class="default">2</span><span class="keyword">) . </span><span class="string">' ft, ' </span><span class="keyword">.<br />
&nbsp;&nbsp;&nbsp; </span><span class="string">'rpm=' </span><span class="keyword">. </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">rpm </span><span class="keyword">.<br />
&nbsp;&nbsp;&nbsp; </span><span class="string">']'</span><span class="keyword">;<br />
&nbsp; }<br />
}<br />
</span><span class="comment">// Wheel defined...<br />
<br />
</span><span class="keyword">class </span><span class="default">Speedometer<br />
</span><span class="keyword">{<br />
&nbsp; public </span><span class="default">$wheel</span><span class="keyword">;<br />
<br />
&nbsp; public function </span><span class="default">__construct</span><span class="keyword">(</span><span class="default">Wheel $wheel</span><span class="keyword">)<br />
&nbsp; {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">wheel </span><span class="keyword">= </span><span class="default">$wheel</span><span class="keyword">;<br />
&nbsp; }<br />
<br />
&nbsp; public function </span><span class="default">getSpeed</span><span class="keyword">()<br />
&nbsp; {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$speed </span><span class="keyword">= </span><span class="default">0.0</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$wheel </span><span class="keyword">= </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">wheel</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp;&nbsp; if(</span><span class="default">$wheel</span><span class="keyword">-&gt;</span><span class="default">rpm </span><span class="keyword">!= </span><span class="default">0.0</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp;&nbsp; </span><span class="default">$fpm </span><span class="keyword">= </span><span class="default">$wheel</span><span class="keyword">-&gt;</span><span class="default">cir </span><span class="keyword">* </span><span class="default">$wheel</span><span class="keyword">-&gt;</span><span class="default">rpm</span><span class="keyword">; </span><span class="comment">// feet per minute<br />
&nbsp;&nbsp; &nbsp;&nbsp; </span><span class="default">$fph </span><span class="keyword">= </span><span class="default">$fpm </span><span class="keyword">* </span><span class="default">60</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// feet per hour<br />
&nbsp;&nbsp; &nbsp;&nbsp; </span><span class="default">$speed </span><span class="keyword">= </span><span class="default">$fph </span><span class="keyword">/ </span><span class="default">5280</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// MPH = $fph / feet per mile<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">}<br />
<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">$speed</span><span class="keyword">;<br />
&nbsp; }<br />
<br />
&nbsp; public function </span><span class="default">__toString</span><span class="keyword">()<br />
&nbsp; {<br />
&nbsp;&nbsp;&nbsp; return </span><span class="string">'Speedometer [' </span><span class="keyword">.<br />
&nbsp;&nbsp;&nbsp; </span><span class="string">'speed=' </span><span class="keyword">. </span><span class="default">number_format</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">getSpeed</span><span class="keyword">(), </span><span class="default">2</span><span class="keyword">) . </span><span class="string">' MPH, ' </span><span class="keyword">.<br />
&nbsp;&nbsp;&nbsp; </span><span class="string">'wheel=' </span><span class="keyword">. </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">wheel </span><span class="keyword">.<br />
&nbsp;&nbsp;&nbsp; </span><span class="string">']'</span><span class="keyword">;<br />
&nbsp; }<br />
}<br />
</span><span class="comment">// Speedometer defined.<br />
// 2. check, SYSTEM created.<br />
<br />
</span><span class="default">$wheel </span><span class="keyword">= new </span><span class="default">Wheel</span><span class="keyword">();<br />
</span><span class="default">$speedometer </span><span class="keyword">= new </span><span class="default">Speedometer</span><span class="keyword">(</span><span class="default">$wheel</span><span class="keyword">);<br />
</span><span class="comment">// 3. check, Objects instantiated.<br />
<br />
</span><span class="keyword">while(</span><span class="default">$wheel</span><span class="keyword">-&gt;</span><span class="default">rpm </span><span class="keyword">&lt;= </span><span class="default">1500</span><span class="keyword">)<br />
{<br />
&nbsp; echo </span><span class="default">$speedometer </span><span class="keyword">. </span><span class="string">"&lt;br&gt;\n"</span><span class="keyword">;<br />
&nbsp; </span><span class="default">$wheel</span><span class="keyword">-&gt;</span><span class="default">rpm </span><span class="keyword">+= </span><span class="default">100</span><span class="keyword">;<br />
}<br />
</span><span class="comment">// 4. check, data outputted.<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
Speedometer [speed=0.00 MPH, wheel=Wheel [cir=3.00 ft, rpm=0]]<br />
Speedometer [speed=3.41 MPH, wheel=Wheel [cir=3.00 ft, rpm=100]]<br />
Speedometer [speed=6.82 MPH, wheel=Wheel [cir=3.00 ft, rpm=200]]<br />
... etc ...<br />
... etc ...<br />
Speedometer [speed=51.14 MPH, wheel=Wheel [cir=3.00 ft, rpm=1500]]<br />
<br />
5. check, there it went.</span>
</code></div>
  </div>
 </div>
 <a name="106262"></a>
 <div class="note">
  <strong class='user'>Manish Gupta</strong>
  <a href="#106262" class="date">22-Oct-2011 10:07</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Some thing that may be obvious to the seasoned PHP programmer, but may surprise someone coming over from C++:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">Foo<br />
</span><span class="keyword">{<br />
</span><span class="default">$bar </span><span class="keyword">= </span><span class="string">'Hi There'</span><span class="keyword">;<br />
<br />
public function Print(){<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="default">$bar</span><span class="keyword">;<br />
}<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
Gives an error saying Print used undefined variable. One has to explicitly use (notice the use of <span class="default">&lt;?php $this</span><span class="keyword">-&gt;</span><span class="default">bar ?&gt;</span>):<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">Foo<br />
</span><span class="keyword">{<br />
</span><span class="default">$bar </span><span class="keyword">= </span><span class="string">'Hi There'</span><span class="keyword">;<br />
<br />
public function Print(){<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="default">this</span><span class="keyword">-&gt;</span><span class="default">$bar</span><span class="keyword">;<br />
}<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
&nbsp;<span class="default">&lt;?php </span><span class="keyword">echo </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">bar</span><span class="keyword">; </span><span class="default">?&gt;</span> refers to the class member, while using $bar means using an uninitialized variable in the local context of the member function.</span>
</code></div>
  </div>
 </div>
 <a name="102275"></a>
 <div class="note">
  <strong class='user'>Marcus</strong>
  <a href="#102275" class="date">05-Feb-2011 11:00</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Here's another simple example.<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="comment">// PHP 5<br />
<br />
// class definition<br />
</span><span class="keyword">class </span><span class="default">Bear </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">// define properties<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">public </span><span class="default">$name</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; public </span><span class="default">$weight</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; public </span><span class="default">$age</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; public </span><span class="default">$sex</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; public </span><span class="default">$colour</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">// constructor<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">public function </span><span class="default">__construct</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">age </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">weight </span><span class="keyword">= </span><span class="default">100</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">// define methods<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">public function </span><span class="default">eat</span><span class="keyword">(</span><span class="default">$units</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">name</span><span class="keyword">.</span><span class="string">" is eating "</span><span class="keyword">.</span><span class="default">$units</span><span class="keyword">.</span><span class="string">" units of food... "</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">weight </span><span class="keyword">+= </span><span class="default">$units</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">run</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">name</span><span class="keyword">.</span><span class="string">" is running... "</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">kill</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">name</span><span class="keyword">.</span><span class="string">" is killing prey... "</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">sleep</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">name</span><span class="keyword">.</span><span class="string">" is sleeping... "</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="comment">// extended class definition<br />
</span><span class="keyword">class </span><span class="default">PolarBear </span><span class="keyword">extends </span><span class="default">Bear </span><span class="keyword">{<br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">// constructor<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">public function </span><span class="default">__construct</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">parent</span><span class="keyword">::</span><span class="default">__construct</span><span class="keyword">();<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">colour </span><span class="keyword">= </span><span class="string">"white"</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">weight </span><span class="keyword">= </span><span class="default">600</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">// define methods<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">public function </span><span class="default">swim</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">name</span><span class="keyword">.</span><span class="string">" is swimming... "</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="100314"></a>
 <div class="note">
  <strong class='user'>Doug</strong>
  <a href="#100314" class="date">07-Oct-2010 07:29</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
What is the difference between&nbsp; $this&nbsp; and&nbsp; self ?<br />
<br />
Inside a class definition, $this refers to the current object, while&nbsp; self&nbsp; refers to the current class.<br />
<br />
It is necessary to refer to a class element using&nbsp; self ,<br />
and refer to an object element using&nbsp; $this .<br />
Note also how an object variable must be preceded by a keyword in its definition.<br />
<br />
The following example illustrates a few cases:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">Classy </span><span class="keyword">{<br />
<br />
const&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">STAT </span><span class="keyword">= </span><span class="string">'S' </span><span class="keyword">; </span><span class="comment">// no dollar sign for constants (they are always static)<br />
</span><span class="keyword">static&nbsp; &nbsp;&nbsp; </span><span class="default">$stat </span><span class="keyword">= </span><span class="string">'Static' </span><span class="keyword">;<br />
public&nbsp; &nbsp;&nbsp; </span><span class="default">$publ </span><span class="keyword">= </span><span class="string">'Public' </span><span class="keyword">;<br />
private&nbsp; &nbsp; </span><span class="default">$priv </span><span class="keyword">= </span><span class="string">'Private' </span><span class="keyword">;<br />
protected&nbsp; </span><span class="default">$prot </span><span class="keyword">= </span><span class="string">'Protected' </span><span class="keyword">;<br />
<br />
function </span><span class="default">__construct</span><span class="keyword">( ){&nbsp; }<br />
<br />
public function </span><span class="default">showMe</span><span class="keyword">( ){<br />
&nbsp;&nbsp;&nbsp; print </span><span class="string">'&lt;br&gt; self::STAT: '&nbsp; </span><span class="keyword">.&nbsp; </span><span class="default">self</span><span class="keyword">::</span><span class="default">STAT </span><span class="keyword">; </span><span class="comment">// refer to a (static) constant like this<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">print </span><span class="string">'&lt;br&gt; self::$stat: ' </span><span class="keyword">. </span><span class="default">self</span><span class="keyword">::</span><span class="default">$stat </span><span class="keyword">; </span><span class="comment">// static variable<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">print </span><span class="string">'&lt;br&gt;$this-&gt;stat: '&nbsp; </span><span class="keyword">. </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">stat </span><span class="keyword">; </span><span class="comment">// legal, but not what you might think: empty result<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">print </span><span class="string">'&lt;br&gt;$this-&gt;publ: '&nbsp; </span><span class="keyword">. </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">publ </span><span class="keyword">; </span><span class="comment">// refer to an object variable like this<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">print </span><span class="string">'&lt;br&gt;' </span><span class="keyword">;<br />
}<br />
}<br />
</span><span class="default">$me </span><span class="keyword">= new </span><span class="default">Classy</span><span class="keyword">( ) ;<br />
</span><span class="default">$me</span><span class="keyword">-&gt;</span><span class="default">showMe</span><span class="keyword">( ) ;<br />
<br />
</span><span class="comment">/* Produces this output:<br />
self::STAT: S<br />
self::$stat: Static<br />
$this-&gt;stat:<br />
$this-&gt;publ: Public<br />
*/<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="97573"></a>
 <div class="note">
  <strong class='user'>ben dot corne at gmail dot com</strong>
  <a href="#97573" class="date">26-Apr-2010 12:35</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
method calling context aware. By this I mean it will get treated differently while being in a new statement compared to being in a regular call.<br />
<br />
Example:<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">Foo </span><span class="keyword">{<br />
&nbsp; private </span><span class="default">$className </span><span class="keyword">= </span><span class="string">'Bar'</span><span class="keyword">;<br />
&nbsp; <br />
&nbsp; public function </span><span class="default">make</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; return new </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">className</span><span class="keyword">();<br />
&nbsp; }<br />
&nbsp; <br />
&nbsp; public function </span><span class="default">callClassName</span><span class="keyword">() {<br />
&nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">className</span><span class="keyword">();<br />
&nbsp; }<br />
<br />
&nbsp; public function </span><span class="default">className</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="string">"foo\n"</span><span class="keyword">;<br />
&nbsp; }<br />
<br />
};<br />
<br />
class </span><span class="default">Bar </span><span class="keyword">{<br />
&nbsp; public function </span><span class="default">hello</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="string">"bar\n"</span><span class="keyword">;<br />
&nbsp; }<br />
};<br />
<br />
</span><span class="default">$foo </span><span class="keyword">= new </span><span class="default">Foo</span><span class="keyword">();<br />
</span><span class="default">$bar </span><span class="keyword">= </span><span class="default">$foo</span><span class="keyword">-&gt;</span><span class="default">make</span><span class="keyword">();<br />
<br />
echo </span><span class="string">"expecting 'bar': "</span><span class="keyword">;<br />
</span><span class="default">$bar</span><span class="keyword">-&gt;</span><span class="default">hello</span><span class="keyword">();<br />
<br />
echo </span><span class="string">"expecting 'foo': "</span><span class="keyword">;<br />
</span><span class="default">$foo</span><span class="keyword">-&gt;</span><span class="default">callClassName</span><span class="keyword">();<br />
</span><span class="default">?&gt;<br />
</span><br />
even tough $this-&gt;className() is written two times in exactly the same way, the one contained in a new statement gets the className field and the other performs the actual method.</span>
</code></div>
  </div>
 </div>
 <a name="94470"></a>
 <div class="note">
  <strong class='user'>Anonymous</strong>
  <a href="#94470" class="date">06-Nov-2009 06:54</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
It is also simple to get or set a property with a name determined at runtime:<br />
<br />
<span class="default">&lt;?php<br />
$e</span><span class="keyword">=new </span><span class="default">E</span><span class="keyword">();<br />
</span><span class="default">$e</span><span class="keyword">-&gt;{</span><span class="string">"foo"</span><span class="keyword">} = </span><span class="default">1</span><span class="keyword">; </span><span class="comment">// using a runtime name<br />
// is the same as doing:<br />
// $e-&gt;foo = 1;<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="92958"></a>
 <div class="note">
  <strong class='user'>moty66 at gmail dot com</strong>
  <a href="#92958" class="date">16-Aug-2009 07:59</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I hope that this will help to understand how to work with static variables inside a class<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">class </span><span class="default">a </span><span class="keyword">{<br />
<br />
&nbsp;&nbsp;&nbsp; public static </span><span class="default">$foo </span><span class="keyword">= </span><span class="string">'I am foo'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; public </span><span class="default">$bar </span><span class="keyword">= </span><span class="string">'I am bar'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; public static function </span><span class="default">getFoo</span><span class="keyword">() { echo </span><span class="default">self</span><span class="keyword">::</span><span class="default">$foo</span><span class="keyword">;&nbsp; &nbsp; }<br />
&nbsp;&nbsp;&nbsp; public static function </span><span class="default">setFoo</span><span class="keyword">() { </span><span class="default">self</span><span class="keyword">::</span><span class="default">$foo </span><span class="keyword">= </span><span class="string">'I am a new foo'</span><span class="keyword">; }<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">getBar</span><span class="keyword">() { echo </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">bar</span><span class="keyword">;&nbsp; &nbsp; }&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <br />
}<br />
<br />
</span><span class="default">$ob </span><span class="keyword">= new </span><span class="default">a</span><span class="keyword">();<br />
</span><span class="default">a</span><span class="keyword">::</span><span class="default">getFoo</span><span class="keyword">();&nbsp; &nbsp;&nbsp; </span><span class="comment">// output: I am foo&nbsp; &nbsp; <br />
</span><span class="default">$ob</span><span class="keyword">-&gt;</span><span class="default">getFoo</span><span class="keyword">();&nbsp; &nbsp; </span><span class="comment">// output: I am foo<br />
//a::getBar();&nbsp; &nbsp;&nbsp; // fatal error: using $this not in object context<br />
</span><span class="default">$ob</span><span class="keyword">-&gt;</span><span class="default">getBar</span><span class="keyword">();&nbsp; &nbsp; </span><span class="comment">// output: I am bar<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; // If you keep $bar non static this will work<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; // but if bar was static, then var_dump($this-&gt;bar) will output null <br />
<br />
// unset($ob);<br />
</span><span class="default">a</span><span class="keyword">::</span><span class="default">setFoo</span><span class="keyword">();&nbsp; &nbsp; </span><span class="comment">// The same effect as if you called $ob-&gt;setFoo(); because $foo is static<br />
</span><span class="default">$ob </span><span class="keyword">= new </span><span class="default">a</span><span class="keyword">();&nbsp; &nbsp;&nbsp; </span><span class="comment">// This will have no effects on $foo<br />
</span><span class="default">$ob</span><span class="keyword">-&gt;</span><span class="default">getFoo</span><span class="keyword">();&nbsp; &nbsp; </span><span class="comment">// output: I am a new foo <br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
Regards<br />
Motaz Abuthiab</span>
</code></div>
  </div>
 </div>
 <a name="92759"></a>
 <div class="note">
  <strong class='user'>alex c</strong>
  <a href="#92759" class="date">07-Aug-2009 12:17</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
ok this really basic but I always forget this. I always get an error like:<br />
<br />
Fatal error: Call to a member function on a non-object<br />
<br />
when i deal with oops<br />
<br />
if it were me finding the error i'd search the internet for hours and then it would occur to me, I'm putting my class operator inside a function, but i would define the class in global file.<br />
<br />
so like this:<br />
test.php<br />
&lt;?<br />
include(class.php);<br />
$class = new newclassname;<br />
<br />
function function1(){<br />
&nbsp; $class-&gt;dofunc();<br />
}<br />
?&gt;<br />
<br />
you'll get some die errors and try and do this with function1,<br />
<br />
function function1(){<br />
&nbsp; newclassname::dofunc();<br />
}<br />
<br />
but if you're using $this inside your class then you'll get another error on non object<br />
<br />
so basically, all you need to do is:<br />
<br />
function function1(){<br />
&nbsp; $class = new newclassname;<br />
&nbsp; $class-&gt;dofunc();<br />
}<br />
<br />
or<br />
<br />
function function1(){<br />
&nbsp; global $class;<br />
&nbsp; $class-&gt;dofunc();<br />
}<br />
<br />
i know it's simple, but it always gets me!</span>
</code></div>
  </div>
 </div>
 <a name="92204"></a>
 <div class="note">
  <strong class='user'>the_french_cow at hotmail dot com</strong>
  <a href="#92204" class="date">14-Jul-2009 06:54</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
For those of us who are new to inheritance, private functions are not visible in an inherited class. Consider: <br />
<br />
<span class="default">&lt;?php<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">class </span><span class="default">A </span><span class="keyword">{<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; protected function </span><span class="default">func1</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; echo(</span><span class="string">"I'm func1 in A!&lt;br/&gt;"</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; private function </span><span class="default">func2</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; echo(</span><span class="string">"I'm func2 in A!&lt;br/&gt;"</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; class </span><span class="default">B </span><span class="keyword">extends </span><span class="default">A </span><span class="keyword">{<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; public function </span><span class="default">func3</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; echo(</span><span class="string">"I'm func3 in B!&lt;br/&gt;"</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">func1</span><span class="keyword">();<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">func2</span><span class="keyword">();&nbsp; </span><span class="comment">// Call to private function from extended class results in a fatal error<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">}<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$b </span><span class="keyword">= new </span><span class="default">B</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$b</span><span class="keyword">-&gt;</span><span class="default">func3</span><span class="keyword">();&nbsp; </span><span class="comment">// Ends in a fatal error<br />
<br />
// OR<br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$b</span><span class="keyword">-&gt;</span><span class="default">func1</span><span class="keyword">();&nbsp; </span><span class="comment">// Call to protected function from outside world results in a fatal error<br />
</span><span class="default">?&gt;<br />
</span><br />
If you want a function to be accessible in class B but not to the outside world, it must be declared as protected.</span>
</code></div>
  </div>
 </div>
 <a name="92123"></a>
 <div class="note">
  <strong class='user'>Notes on stdClass</strong>
  <a href="#92123" class="date">09-Jul-2009 03:26</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
stdClass is the default PHP object. stdClass has no properties, methods or parent. It does not support magic methods, and implements no interfaces.<br />
<br />
When you cast a scalar or array as Object, you get an instance of stdClass. You can use stdClass whenever you need a generic object instance.<br />
<span class="default">&lt;?php<br />
</span><span class="comment">// ways of creating stdClass instances<br />
</span><span class="default">$x </span><span class="keyword">= new </span><span class="default">stdClass</span><span class="keyword">;<br />
</span><span class="default">$y </span><span class="keyword">= (object) </span><span class="default">null</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp; </span><span class="comment">// same as above<br />
</span><span class="default">$z </span><span class="keyword">= (object) </span><span class="string">'a'</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// creates property 'scalar' = 'a'<br />
</span><span class="default">$a </span><span class="keyword">= (object) array(</span><span class="string">'property1' </span><span class="keyword">=&gt; </span><span class="default">1</span><span class="keyword">, </span><span class="string">'property2' </span><span class="keyword">=&gt; </span><span class="string">'b'</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span><br />
stdClass is NOT a base class! PHP classes do not automatically inherit from any class. All classes are standalone, unless they explicitly extend another class. PHP differs from many object-oriented languages in this respect.<br />
<span class="default">&lt;?php<br />
</span><span class="comment">// CTest does not derive from stdClass<br />
</span><span class="keyword">class </span><span class="default">CTest </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public </span><span class="default">$property1</span><span class="keyword">;<br />
}<br />
</span><span class="default">$t </span><span class="keyword">= new </span><span class="default">CTest</span><span class="keyword">;<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$t </span><span class="keyword">instanceof </span><span class="default">stdClass</span><span class="keyword">);&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="comment">// false<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">is_subclass_of</span><span class="keyword">(</span><span class="default">$t</span><span class="keyword">, </span><span class="string">'stdClass'</span><span class="keyword">));&nbsp; &nbsp; </span><span class="comment">// false<br />
</span><span class="keyword">echo </span><span class="default">get_class</span><span class="keyword">(</span><span class="default">$t</span><span class="keyword">) . </span><span class="string">"\n"</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// 'CTest'<br />
</span><span class="keyword">echo </span><span class="default">get_parent_class</span><span class="keyword">(</span><span class="default">$t</span><span class="keyword">) . </span><span class="string">"\n"</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="comment">// false (no parent)<br />
</span><span class="default">?&gt;<br />
</span><br />
You cannot define a class named 'stdClass' in your code. That name is already used by the system. You can define a class named 'Object'.<br />
<br />
You could define a class that extends stdClass, but you would get no benefit, as stdClass does nothing.<br />
<br />
(tested on PHP 5.2.8)</span>
</code></div>
  </div>
 </div>
 <a name="90667"></a>
 <div class="note">
  <strong class='user'>webmaster at oehoeboeroe dot nl</strong>
  <a href="#90667" class="date">03-May-2009 05:03</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you pass $this by reference and then assign a new value to it, it will not behave as you might expect as illustrated by this example:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">TestClass<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public </span><span class="default">$data</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">setData</span><span class="keyword">(&amp;</span><span class="default">$data</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">data </span><span class="keyword">=&amp; </span><span class="default">$data</span><span class="keyword">; <br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">hasData</span><span class="keyword">(&amp;</span><span class="default">$data</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$saved </span><span class="keyword">= </span><span class="default">$data</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$data </span><span class="keyword">= </span><span class="default">true</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$result </span><span class="keyword">= </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">data </span><span class="keyword">=== </span><span class="default">true</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$data </span><span class="keyword">= </span><span class="default">$saved</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$result</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">isDataOf</span><span class="keyword">(&amp;</span><span class="default">$object</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$object</span><span class="keyword">-&gt;</span><span class="default">hasData</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="default">$o1 </span><span class="keyword">= new </span><span class="default">TestClass</span><span class="keyword">;<br />
</span><span class="default">$o2 </span><span class="keyword">= new </span><span class="default">TestClass</span><span class="keyword">;<br />
</span><span class="default">$o1</span><span class="keyword">-&gt;</span><span class="default">setData</span><span class="keyword">(</span><span class="default">$o2</span><span class="keyword">);<br />
</span><span class="default">$o2</span><span class="keyword">-&gt;</span><span class="default">setData</span><span class="keyword">(</span><span class="default">$o1</span><span class="keyword">);<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$o1</span><span class="keyword">-&gt;</span><span class="default">hasData</span><span class="keyword">(</span><span class="default">$o2</span><span class="keyword">));&nbsp;&nbsp; </span><span class="comment">// true as expected<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$o2</span><span class="keyword">-&gt;</span><span class="default">hasData</span><span class="keyword">(</span><span class="default">$o1</span><span class="keyword">));&nbsp;&nbsp; </span><span class="comment">// true as expected<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$o1</span><span class="keyword">-&gt;</span><span class="default">isDataOf</span><span class="keyword">(</span><span class="default">$o2</span><span class="keyword">));&nbsp; </span><span class="comment">// false even though $o1 is in fact the data of $o2<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$o2</span><span class="keyword">-&gt;</span><span class="default">isDataOf</span><span class="keyword">(</span><span class="default">$o1</span><span class="keyword">));&nbsp; </span><span class="comment">// false even though $o2 is in fact the data of $o1<br />
</span><span class="default">?&gt;<br />
</span><br />
You can make this example work by replacing the hasData method with:<br />
<br />
<span class="default">&lt;?php <br />
</span><span class="keyword">function </span><span class="default">hasData</span><span class="keyword">(&amp;</span><span class="default">$data</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">$data </span><span class="keyword">=== </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">data</span><span class="keyword">;<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
However, although I've not tested this, I've been told that Zend Engine 1, i.e. PHP 4, will choke on the === parameter when comparing recursing objects.</span>
</code></div>
  </div>
 </div>
 <a name="90474"></a>
 <div class="note">
  <strong class='user'>ialsoagree</strong>
  <a href="#90474" class="date">23-Apr-2009 08:03</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Unfortunately, Arpit's solution creates a new class and leaves the old class inaccessible. If you need access to members of the class you are in you'll be unable to get such access. This can be a huge problem.<br />
<br />
However, there is a solution:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">MyClass </span><span class="keyword">{<br />
&nbsp;&nbsp; public </span><span class="default">$message </span><span class="keyword">= </span><span class="string">'Hello'</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp; public function </span><span class="default">MyClassFunction</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp; function </span><span class="default">innerFunction</span><span class="keyword">(&amp;</span><span class="default">$this_thing</span><span class="keyword">, </span><span class="default">$message </span><span class="keyword">= </span><span class="default">null</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this_thing</span><span class="keyword">-&gt;</span><span class="default">message </span><span class="keyword">= (!</span><span class="default">is_null</span><span class="keyword">(</span><span class="default">$message</span><span class="keyword">)) ? </span><span class="default">$message </span><span class="keyword">: </span><span class="default">$this_thing</span><span class="keyword">-&gt;</span><span class="default">message</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this_thing</span><span class="keyword">-&gt;</span><span class="default">echo_something</span><span class="keyword">();<br />
&nbsp;&nbsp; &nbsp; &nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp; </span><span class="default">innerFunction</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">); </span><span class="comment">// echoes 'Hello'<br />
&nbsp;&nbsp; &nbsp; &nbsp; </span><span class="default">innerFunction</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">, </span><span class="string">'&lt;br/&gt;New Message'</span><span class="keyword">); </span><span class="comment">// echoes '&lt;br/&gt;New Message'<br />
&nbsp;&nbsp; </span><span class="keyword">}<br />
&nbsp;&nbsp; <br />
&nbsp;&nbsp; public function </span><span class="default">echo_something</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp; echo </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">message</span><span class="keyword">;<br />
&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="default">$class </span><span class="keyword">= new </span><span class="default">MyClass</span><span class="keyword">;<br />
</span><span class="default">$class</span><span class="keyword">-&gt;</span><span class="default">MyClassFunction</span><span class="keyword">();<br />
</span><span class="default">?&gt;<br />
</span><br />
By passing $this as a variable by reference, you can access members of the class and even update them. If you don't want to be able to update them, you can simply pass $this to the function but not as a reference.</span>
</code></div>
  </div>
 </div>
 <a name="88763"></a>
 <div class="note">
  <strong class='user'>Arpit</strong>
  <a href="#88763" class="date">06-Feb-2009 06:14</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
//try this code if you define a new class inside an object method than we can refer to "$class-&gt;message"<br />
//unset this instance doesn't affected the previous one <br />
//it will not report a fatal error<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">class </span><span class="default">MyClass </span><span class="keyword">{<br />
&nbsp;&nbsp; public </span><span class="default">$message </span><span class="keyword">= </span><span class="string">'Hello'</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp; public function </span><span class="default">MyClassFunction</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; function </span><span class="default">InnerFunction</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$class </span><span class="keyword">= new </span><span class="default">MyClass</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">print_r</span><span class="keyword">(</span><span class="default">$class</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="default">$class</span><span class="keyword">-&gt;</span><span class="default">message</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; unset(</span><span class="default">$class</span><span class="keyword">);</span><span class="comment">//unset this doesn't affected the previous one or we can also use different name $classNew=new MyClass;<br />
&nbsp;&nbsp; &nbsp; &nbsp; </span><span class="keyword">}<br />
&nbsp;&nbsp; &nbsp; &nbsp; </span><span class="default">innerFunction</span><span class="keyword">();<br />
&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="default">$class </span><span class="keyword">= new </span><span class="default">MyClass</span><span class="keyword">;<br />
</span><span class="default">$class</span><span class="keyword">-&gt;</span><span class="default">MyClassFunction</span><span class="keyword">();<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="88665"></a>
 <div class="note">
  <strong class='user'>ialsoagree</strong>
  <a href="#88665" class="date">02-Feb-2009 04:23</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I think it's worth mentioning that if you define a function inside of an object method, that function cannot refer to "$this" - doing so will result in PHP reporting a fatal error:<br />
<br />
Fatal error: Using $this when not in object context<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">MyClass </span><span class="keyword">{<br />
&nbsp;&nbsp; public </span><span class="default">$message </span><span class="keyword">= </span><span class="string">'Hello'</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp; public function </span><span class="default">MyClassFunction</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp; function </span><span class="default">InnerFunction</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">message</span><span class="keyword">; </span><span class="comment">// Reports a fatal error<br />
&nbsp;&nbsp; &nbsp; &nbsp; </span><span class="keyword">}<br />
&nbsp;&nbsp; &nbsp; &nbsp; </span><span class="default">innerFunction</span><span class="keyword">();<br />
&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="default">$class </span><span class="keyword">= new </span><span class="default">MyClass</span><span class="keyword">;<br />
</span><span class="default">$class</span><span class="keyword">-&gt;</span><span class="default">MyClassFunction</span><span class="keyword">();<br />
</span><span class="default">?&gt;<br />
</span><br />
This issue cannot be solved by using the Scope Resolution Operator if you're trying to access a variable:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">MyClass </span><span class="keyword">{<br />
&nbsp;&nbsp; public </span><span class="default">$message </span><span class="keyword">= </span><span class="string">'Hello'</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp; public function </span><span class="default">MyClassFunction</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp; function </span><span class="default">InnerFunction</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="default">MyClass</span><span class="keyword">::</span><span class="default">message</span><span class="keyword">; </span><span class="comment">// Reports a fatal error<br />
&nbsp;&nbsp; &nbsp; &nbsp; </span><span class="keyword">}<br />
&nbsp;&nbsp; &nbsp; &nbsp; </span><span class="default">innerFunction</span><span class="keyword">();<br />
&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="default">$class </span><span class="keyword">= new </span><span class="default">MyClass</span><span class="keyword">;<br />
</span><span class="default">$class</span><span class="keyword">-&gt;</span><span class="default">MyClassFunction</span><span class="keyword">();<br />
</span><span class="default">?&gt;<br />
</span><br />
Additionally, you can NOT create a public function to access that variable:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">MyClass </span><span class="keyword">{<br />
&nbsp;&nbsp; public </span><span class="default">$message </span><span class="keyword">= </span><span class="string">'Hello'</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp; public function </span><span class="default">MyClassFunction</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp; function </span><span class="default">InnerFunction</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">MyClass</span><span class="keyword">::</span><span class="default">echoSomething</span><span class="keyword">();<br />
&nbsp;&nbsp; &nbsp; &nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp; </span><span class="default">innerFunction</span><span class="keyword">();<br />
&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp; public function </span><span class="default">echoSomething</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp;&nbsp; echo </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">message</span><span class="keyword">; </span><span class="comment">// Reports a fatal error<br />
&nbsp;&nbsp; </span><span class="keyword">}<br />
}<br />
<br />
</span><span class="default">$class </span><span class="keyword">= new </span><span class="default">MyClass</span><span class="keyword">;<br />
</span><span class="default">$class</span><span class="keyword">-&gt;</span><span class="default">MyClassFunction</span><span class="keyword">();<br />
</span><span class="default">?&gt;<br />
</span><br />
Note that in this last case, the error is generated on the line below echoSomething function declaration, not at MyClass::echoSomething();<br />
<br />
However, it is worth noting that when called directly, echoSomething works fine:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">MyClass </span><span class="keyword">{<br />
&nbsp;&nbsp; public </span><span class="default">$message </span><span class="keyword">= </span><span class="string">'Hello'</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp; public function </span><span class="default">MyClassFunction</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp; function </span><span class="default">InnerFunction</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">MyClass</span><span class="keyword">::</span><span class="default">echoSomething</span><span class="keyword">();<br />
&nbsp;&nbsp; &nbsp; &nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp; </span><span class="default">innerFunction</span><span class="keyword">();<br />
&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp; public function </span><span class="default">echoSomething</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp;&nbsp; echo </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">message</span><span class="keyword">; </span><span class="comment">// Echoes 'Hello'<br />
&nbsp;&nbsp; </span><span class="keyword">}<br />
}<br />
<br />
</span><span class="default">$class </span><span class="keyword">= new </span><span class="default">MyClass</span><span class="keyword">;<br />
</span><span class="default">$class</span><span class="keyword">-&gt;</span><span class="default">echoSomething</span><span class="keyword">();<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="88249"></a>
 <div class="note">
  <strong class='user'>hugo (@) apres (dot) net</strong>
  <a href="#88249" class="date">16-Jan-2009 08:37</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
A simple approach to Multiple Inheritance<br />
<br />
You can give yourself something approaching multiple inheritance with the following class:<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">class </span><span class="default">inheritance</span><span class="keyword">{<br />
<br />
&nbsp; var </span><span class="default">$bases </span><span class="keyword">= array();<br />
<br />
&nbsp; static function </span><span class="default">error_die</span><span class="keyword">( </span><span class="default">$errno</span><span class="keyword">, </span><span class="default">$errstr</span><span class="keyword">, </span><span class="default">$errfile</span><span class="keyword">, </span><span class="default">$errline </span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$backtrace </span><span class="keyword">= </span><span class="default">debug_backtrace</span><span class="keyword">();<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$detail </span><span class="keyword">= </span><span class="default">$backtrace</span><span class="keyword">[</span><span class="default">4</span><span class="keyword">];<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">var_dump</span><span class="keyword">( </span><span class="default">$backtrace </span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="string">'&lt;b&gt;Fatal Error&lt;/b&gt;: '</span><span class="keyword">.</span><span class="default">$errstr</span><span class="keyword">.</span><span class="string">' of class &lt;b&gt;'</span><span class="keyword">.</span><span class="default">$detail</span><span class="keyword">[</span><span class="string">"class"</span><span class="keyword">].</span><span class="string">'&lt;/b&gt; in &lt;b&gt;'</span><span class="keyword">.</span><span class="default">$detail</span><span class="keyword">[</span><span class="string">"file"</span><span class="keyword">].</span><span class="string">'&lt;/b&gt; on line &lt;b&gt;'</span><span class="keyword">.</span><span class="default">$detail</span><span class="keyword">[</span><span class="string">"line"</span><span class="keyword">].</span><span class="string">'&lt;/b&gt;&lt;br/&gt;'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; die();<br />
&nbsp; }<br />
<br />
&nbsp; private function </span><span class="default">fatal</span><span class="keyword">( </span><span class="default">$text </span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">set_error_handler</span><span class="keyword">( array( </span><span class="string">'inheritance'</span><span class="keyword">, </span><span class="string">'error_die' </span><span class="keyword">) );<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">trigger_error</span><span class="keyword">( </span><span class="default">$text</span><span class="keyword">, </span><span class="default">E_USER_ERROR </span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">restore_error_handler</span><span class="keyword">();<br />
&nbsp; }<br />
<br />
&nbsp; function </span><span class="default">__call</span><span class="keyword">( </span><span class="default">$name</span><span class="keyword">, </span><span class="default">$args </span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; if( </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">bases </span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp;&nbsp; foreach( </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">bases </span><span class="keyword">as </span><span class="default">$base </span><span class="keyword">) <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if( </span><span class="default">method_exists</span><span class="keyword">( </span><span class="default">$base</span><span class="keyword">, </span><span class="default">$name </span><span class="keyword">) )<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$base</span><span class="keyword">-&gt;</span><span class="default">$name</span><span class="keyword">( </span><span class="default">$args </span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">fatal</span><span class="keyword">( </span><span class="string">"Call to undefined method &lt;b&gt;"</span><span class="keyword">.</span><span class="default">$name</span><span class="keyword">.</span><span class="string">"&lt;/b&gt;" </span><span class="keyword">);<br />
&nbsp; }<br />
<br />
&nbsp; function </span><span class="default">__set</span><span class="keyword">( </span><span class="default">$name</span><span class="keyword">, </span><span class="default">$value </span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; if( </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">bases </span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp;&nbsp; foreach( </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">bases </span><span class="keyword">as </span><span class="default">$base </span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if( </span><span class="default">property_exists</span><span class="keyword">( </span><span class="default">$base</span><span class="keyword">, </span><span class="default">$name </span><span class="keyword">) ) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$base</span><span class="keyword">-&gt;</span><span class="default">$name </span><span class="keyword">= </span><span class="default">$value</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp; }<br />
&nbsp;<br />
&nbsp; function </span><span class="default">__get</span><span class="keyword">( </span><span class="default">$name </span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; if( </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">bases </span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp;&nbsp; foreach( </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">bases </span><span class="keyword">as </span><span class="default">$base </span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if( </span><span class="default">property_exists</span><span class="keyword">( </span><span class="default">$base</span><span class="keyword">, </span><span class="default">$name </span><span class="keyword">) )<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$base</span><span class="keyword">-&gt;</span><span class="default">$name</span><span class="keyword">;<br />
&nbsp; }<br />
<br />
&nbsp; function </span><span class="default">__isset</span><span class="keyword">( </span><span class="default">$name </span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; if( </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">bases </span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp;&nbsp; foreach( </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">bases </span><span class="keyword">as </span><span class="default">$base </span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if( </span><span class="default">property_exists</span><span class="keyword">( </span><span class="default">$base</span><span class="keyword">, </span><span class="default">$name </span><span class="keyword">) )<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return isset( </span><span class="default">$base</span><span class="keyword">-&gt;</span><span class="default">$name </span><span class="keyword">);<br />
&nbsp; }<br />
<br />
&nbsp; function </span><span class="default">__unset</span><span class="keyword">( </span><span class="default">$name </span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; if( </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">bases </span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp;&nbsp; foreach( </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">bases </span><span class="keyword">as </span><span class="default">$base </span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if( </span><span class="default">property_exists</span><span class="keyword">( </span><span class="default">$base</span><span class="keyword">, </span><span class="default">$name </span><span class="keyword">) ) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; unset( </span><span class="default">$base</span><span class="keyword">-&gt;</span><span class="default">$name </span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp; }<br />
<br />
&nbsp; function </span><span class="default">inherits</span><span class="keyword">( </span><span class="default">$name</span><span class="keyword">, </span><span class="default">$args </span><span class="keyword">= </span><span class="string">'' </span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">array_unshift</span><span class="keyword">( </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">bases</span><span class="keyword">, new </span><span class="default">$name</span><span class="keyword">( </span><span class="default">$args </span><span class="keyword">) );<br />
&nbsp; }<br />
<br />
}<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
Most of the qualities of multiple inheritance provided by this class are revealed by the following code:<br />
<br />
<span class="default">&lt;?php </span><span class="comment">//test inheritance<br />
<br />
</span><span class="keyword">class </span><span class="default">base0 </span><span class="keyword">{<br />
&nbsp; public </span><span class="default">$base0var</span><span class="keyword">;<br />
&nbsp; public </span><span class="default">$basevar</span><span class="keyword">;<br />
<br />
&nbsp; function </span><span class="default">base0declare</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="string">'I am base 0'</span><span class="keyword">;<br />
&nbsp; }<br />
&nbsp; function </span><span class="default">basedeclare</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">self</span><span class="keyword">::</span><span class="default">base0declare</span><span class="keyword">() {<br />
&nbsp; }<br />
}<br />
<br />
class </span><span class="default">base1 </span><span class="keyword">extends </span><span class="default">base0 </span><span class="keyword">{ </span><span class="comment">// simple linear inheritance here<br />
&nbsp; </span><span class="keyword">public </span><span class="default">$base1var</span><span class="keyword">;<br />
&nbsp; public </span><span class="default">$basevar</span><span class="keyword">;<br />
<br />
&nbsp; function </span><span class="default">based1declare</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="string">'I am base 1'</span><span class="keyword">;<br />
&nbsp; }<br />
&nbsp; function </span><span class="default">basedeclare</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">self</span><span class="keyword">::</span><span class="default">base1declare</span><span class="keyword">()<br />
&nbsp; }<br />
}<br />
<br />
class </span><span class="default">base2<br />
&nbsp; </span><span class="keyword">public </span><span class="default">$base2var</span><span class="keyword">;<br />
&nbsp; public </span><span class="default">$basevar</span><span class="keyword">;<br />
&nbsp; function </span><span class="default">based2declare</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="string">'I am base 2'</span><span class="keyword">;<br />
&nbsp; }<br />
&nbsp; function </span><span class="default">basedeclare</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">self</span><span class="keyword">::</span><span class="default">base2declare<br />
&nbsp; </span><span class="keyword">}<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
Multiple inheritance is achieved by extending the inheritance class, and then in the __construct function placing calls to the "inherits" method of the inheritance class. Each call pushes an instance of the inherited class into an array var which functions as a LIFO stack. Using the magic methods, any failed method call, property set, get, isset or unset is intercepted by the inheritance base class which then attempts to resolve the reference. Object method name conflicts are resolved simply by the later inheritance masking the scope of the earlier inherited method. I recognize there are shortcomings to the approach I offer here, but it works for all my current multiple inheritance needs and offers simplicity and ease of understanding as benefits.<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">base_test </span><span class="keyword">extends </span><span class="default">inheritance </span><span class="keyword">{ </span><span class="comment">// multiple inheritance<br />
<br />
&nbsp; </span><span class="keyword">function </span><span class="default">__construct</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">inherits</span><span class="keyword">( </span><span class="string">'base1' </span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">inherits</span><span class="keyword">( </span><span class="string">'base2' </span><span class="keyword">);<br />
&nbsp; }<br />
<br />
} </span><span class="default">?&gt;<br />
</span><br />
Here are some code fragments you can try out to test things.<br />
<br />
<span class="default">&lt;?php<br />
<br />
$testobj </span><span class="keyword">= new </span><span class="default">base_test</span><span class="keyword">();<br />
</span><span class="default">var_dump</span><span class="keyword">( </span><span class="default">$testobj </span><span class="keyword">);<br />
</span><span class="default">$testobj</span><span class="keyword">-&gt;</span><span class="default">base2declare</span><span class="keyword">();<br />
</span><span class="default">$testobj</span><span class="keyword">-&gt;</span><span class="default">base1declare</span><span class="keyword">();<br />
</span><span class="default">$testobj</span><span class="keyword">-&gt;</span><span class="default">base0declare</span><span class="keyword">();<br />
</span><span class="default">$testobj</span><span class="keyword">-&gt;</span><span class="default">basedeclare</span><span class="keyword">();<br />
</span><span class="default">$testobj</span><span class="keyword">-&gt;</span><span class="default">base2var </span><span class="keyword">= </span><span class="default">27</span><span class="keyword">;<br />
echo </span><span class="default">$testobj</span><span class="keyword">-&gt;</span><span class="default">base2var</span><span class="keyword">;<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
I'd be interested in hearing any comments.</span>
</code></div>
  </div>
 </div>
 <a name="87270"></a>
 <div class="note">
  <strong class='user'>chris (@) xeneco (dot) co (dot) uk</strong>
  <a href="#87270" class="date">27-Nov-2008 03:25</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Regarding object inheritance:<br />
<br />
I hope this helps someone, it should help if you're new to OOPS<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">class </span><span class="default">A </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; public </span><span class="default">$x </span><span class="keyword">= </span><span class="string">'A'</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">foo</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$b </span><span class="keyword">= new </span><span class="default">B</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$b</span><span class="keyword">-&gt;</span><span class="default">bar</span><span class="keyword">();<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">x</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
class </span><span class="default">B </span><span class="keyword">extends </span><span class="default">A </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">bar</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">x </span><span class="keyword">= </span><span class="string">'B'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="default">$a </span><span class="keyword">= new </span><span class="default">A<br />
<br />
</span><span class="keyword">echo </span><span class="default">$a</span><span class="keyword">-&gt;</span><span class="default">foo</span><span class="keyword">();&nbsp; &nbsp; </span><span class="comment">//A<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
I was doing something similar to this (example is greatly simplified to show logic) and spent a long while trying to work out why I would always get 'A' and never get 'B'. Now, after a few weeks, I have revisited the problem and have worked out why:<br />
<br />
The code 'new B' creates a new instance of class B. While class B extends class A, it is a new object and not an extension of the object created by 'new A'<br />
<br />
The value of $x is set to 'B' within the object $b, but not in object $a.<br />
<br />
If within A::foo(), one was to access $b-&gt;x then one would obtain the vale 'B', for example<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">class </span><span class="default">C </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; public </span><span class="default">$x </span><span class="keyword">= </span><span class="string">'C'</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">foo</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$c </span><span class="keyword">= new </span><span class="default">C</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$c</span><span class="keyword">-&gt;</span><span class="default">bar</span><span class="keyword">();<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">x </span><span class="keyword">= </span><span class="default">$c</span><span class="keyword">-&gt;</span><span class="default">$x<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">return </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">x</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
class </span><span class="default">D </span><span class="keyword">extends </span><span class="default">C </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">bar</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">x </span><span class="keyword">= </span><span class="string">'D'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="default">$c </span><span class="keyword">= new </span><span class="default">C<br />
<br />
</span><span class="keyword">echo </span><span class="default">$c</span><span class="keyword">-&gt;</span><span class="default">foo</span><span class="keyword">();&nbsp; &nbsp; </span><span class="comment">//D<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="86235"></a>
 <div class="note">
  <strong class='user'>Jeffrey</strong>
  <a href="#86235" class="date">08-Oct-2008 03:49</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
A PHP Class can be used for several things, but at the most basic level, you'll use classes to "organize and deal with like-minded data". Here's what I mean by "organizing like-minded data". First, start with unorganized data.<br />
<br />
<span class="default">&lt;?php<br />
$customer_name</span><span class="keyword">;<br />
</span><span class="default">$item_name</span><span class="keyword">;<br />
</span><span class="default">$item_price</span><span class="keyword">;<br />
</span><span class="default">$customer_address</span><span class="keyword">;<br />
</span><span class="default">$item_qty</span><span class="keyword">;<br />
</span><span class="default">$item_total</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
Now to organize the data into PHP classes:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">Customer </span><span class="keyword">{<br />
&nbsp; </span><span class="default">$name</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="comment">// same as $customer_name<br />
&nbsp; </span><span class="default">$address</span><span class="keyword">;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// same as $customer_address<br />
</span><span class="keyword">}<br />
<br />
class </span><span class="default">Item </span><span class="keyword">{<br />
&nbsp; </span><span class="default">$name</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="comment">// same as $item_name<br />
&nbsp; </span><span class="default">$price</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// same as $item_price<br />
&nbsp; </span><span class="default">$qty</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// same as $item_qty<br />
&nbsp; </span><span class="default">$total</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// same as $item_total<br />
</span><span class="keyword">}<br />
</span><span class="default">?&gt;<br />
</span><br />
Now here's what I mean by "dealing" with the data. Note: The data is already organized, so that in itself makes writing new functions extremely easy.<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">Customer </span><span class="keyword">{<br />
&nbsp; public </span><span class="default">$name</span><span class="keyword">, </span><span class="default">$address</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// the data for this class...<br />
<br />
&nbsp; // function to deal with user-input / validation<br />
&nbsp; // function to build string for output<br />
&nbsp; // function to write -&gt; database<br />
&nbsp; // function to&nbsp; read &lt;- database<br />
&nbsp; // etc, etc<br />
</span><span class="keyword">}<br />
<br />
class </span><span class="default">Item </span><span class="keyword">{<br />
&nbsp; public </span><span class="default">$name</span><span class="keyword">, </span><span class="default">$price</span><span class="keyword">, </span><span class="default">$qty</span><span class="keyword">, </span><span class="default">$total</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp; </span><span class="comment">// the data for this class...<br />
<br />
&nbsp; // function to calculate total<br />
&nbsp; // function to format numbers<br />
&nbsp; // function to deal with user-input / validation<br />
&nbsp; // function to build string for output<br />
&nbsp; // function to write -&gt; database<br />
&nbsp; // function to&nbsp; read &lt;- database<br />
&nbsp; // etc, etc<br />
</span><span class="keyword">}<br />
</span><span class="default">?&gt;<br />
</span><br />
Imagination that each function you write only calls the bits of data in that class. Some functions may access all the data, while other functions may only access one piece of data. If each function revolves around the data inside, then you have created a good class.</span>
</code></div>
  </div>
 </div>
 <a name="85220"></a>
 <div class="note">
  <strong class='user'>wbcarts at juno dot com</strong>
  <a href="#85220" class="date">20-Aug-2008 06:11</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
CLASSES and OBJECTS that represent the "Ideal World"<br />
<br />
Wouldn't it be great to get the lawn mowed by saying $son-&gt;mowLawn()? Assuming the function mowLawn() is defined, and you have a son that doesn't throw errors, the lawn will be mowed. <br />
<br />
In the following example; let objects of type Line3D measure their own length in 3-dimensional space. Why should I or PHP have to provide another method from outside this class to calculate length, when the class itself holds all the neccessary data and has the education to make the calculation for itself?<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="comment">/*<br />
&nbsp;* Point3D.php<br />
&nbsp;*<br />
&nbsp;* Represents one locaton or position in 3-dimensional space<br />
&nbsp;* using an (x, y, z) coordinate system.<br />
&nbsp;*/<br />
</span><span class="keyword">class </span><span class="default">Point3D<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public </span><span class="default">$x</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; public </span><span class="default">$y</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; public </span><span class="default">$z</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="comment">// the x coordinate of this Point.<br />
<br />
&nbsp;&nbsp;&nbsp; /*<br />
&nbsp;&nbsp; &nbsp; * use the x and y variables inherited from Point.php.<br />
&nbsp;&nbsp; &nbsp; */<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">public function </span><span class="default">__construct</span><span class="keyword">(</span><span class="default">$xCoord</span><span class="keyword">=</span><span class="default">0</span><span class="keyword">, </span><span class="default">$yCoord</span><span class="keyword">=</span><span class="default">0</span><span class="keyword">, </span><span class="default">$zCoord</span><span class="keyword">=</span><span class="default">0</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">x </span><span class="keyword">= </span><span class="default">$xCoord</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">y </span><span class="keyword">= </span><span class="default">$yCoord</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">z </span><span class="keyword">= </span><span class="default">$zCoord</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">/*<br />
&nbsp;&nbsp; &nbsp; * the (String) representation of this Point as "Point3D(x, y, z)".<br />
&nbsp;&nbsp; &nbsp; */<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">public function </span><span class="default">__toString</span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="string">'Point3D(x=' </span><span class="keyword">. </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">x </span><span class="keyword">. </span><span class="string">', y=' </span><span class="keyword">. </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">y </span><span class="keyword">. </span><span class="string">', z=' </span><span class="keyword">. </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">z </span><span class="keyword">. </span><span class="string">')'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="comment">/*<br />
&nbsp;* Line3D.php<br />
&nbsp;*<br />
&nbsp;* Represents one Line in 3-dimensional space using two Point3D objects.<br />
&nbsp;*/<br />
</span><span class="keyword">class </span><span class="default">Line3D<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$start</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$end</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">__construct</span><span class="keyword">(</span><span class="default">$xCoord1</span><span class="keyword">=</span><span class="default">0</span><span class="keyword">, </span><span class="default">$yCoord1</span><span class="keyword">=</span><span class="default">0</span><span class="keyword">, </span><span class="default">$zCoord1</span><span class="keyword">=</span><span class="default">0</span><span class="keyword">, </span><span class="default">$xCoord2</span><span class="keyword">=</span><span class="default">1</span><span class="keyword">, </span><span class="default">$yCoord2</span><span class="keyword">=</span><span class="default">1</span><span class="keyword">, </span><span class="default">$zCoord2</span><span class="keyword">=</span><span class="default">1</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">start </span><span class="keyword">= new </span><span class="default">Point3D</span><span class="keyword">(</span><span class="default">$xCoord1</span><span class="keyword">, </span><span class="default">$yCoord1</span><span class="keyword">, </span><span class="default">$zCoord1</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">end </span><span class="keyword">= new </span><span class="default">Point3D</span><span class="keyword">(</span><span class="default">$xCoord2</span><span class="keyword">, </span><span class="default">$yCoord2</span><span class="keyword">, </span><span class="default">$zCoord2</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">/*<br />
&nbsp;&nbsp; &nbsp; * calculate the length of this Line in 3-dimensional space.<br />
&nbsp;&nbsp; &nbsp; */ <br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">public function </span><span class="default">getLength</span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">sqrt</span><span class="keyword">(<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">pow</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">start</span><span class="keyword">-&gt;</span><span class="default">x </span><span class="keyword">- </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">end</span><span class="keyword">-&gt;</span><span class="default">x</span><span class="keyword">, </span><span class="default">2</span><span class="keyword">) +<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">pow</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">start</span><span class="keyword">-&gt;</span><span class="default">y </span><span class="keyword">- </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">end</span><span class="keyword">-&gt;</span><span class="default">y</span><span class="keyword">, </span><span class="default">2</span><span class="keyword">) +<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">pow</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">start</span><span class="keyword">-&gt;</span><span class="default">z </span><span class="keyword">- </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">end</span><span class="keyword">-&gt;</span><span class="default">z</span><span class="keyword">, </span><span class="default">2</span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; );<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">/*<br />
&nbsp;&nbsp; &nbsp; * The (String) representation of this Line as "Line3D[start, end, length]".<br />
&nbsp;&nbsp; &nbsp; */<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">public function </span><span class="default">__toString</span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="string">'Line3D[start=' </span><span class="keyword">. </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">start </span><span class="keyword">.<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="string">', end=' </span><span class="keyword">. </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">end </span><span class="keyword">.<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="string">', length=' </span><span class="keyword">. </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">getLength</span><span class="keyword">() . </span><span class="string">']'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="comment">/*<br />
&nbsp;* create and display objects of type Line3D.<br />
&nbsp;*/<br />
</span><span class="keyword">echo </span><span class="string">'&lt;p&gt;' </span><span class="keyword">. (new </span><span class="default">Line3D</span><span class="keyword">()) . </span><span class="string">"&lt;/p&gt;\n"</span><span class="keyword">;<br />
echo </span><span class="string">'&lt;p&gt;' </span><span class="keyword">. (new </span><span class="default">Line3D</span><span class="keyword">(</span><span class="default">0</span><span class="keyword">, </span><span class="default">0</span><span class="keyword">, </span><span class="default">0</span><span class="keyword">, </span><span class="default">100</span><span class="keyword">, </span><span class="default">100</span><span class="keyword">, </span><span class="default">0</span><span class="keyword">)) . </span><span class="string">"&lt;/p&gt;\n"</span><span class="keyword">;<br />
echo </span><span class="string">'&lt;p&gt;' </span><span class="keyword">. (new </span><span class="default">Line3D</span><span class="keyword">(</span><span class="default">0</span><span class="keyword">, </span><span class="default">0</span><span class="keyword">, </span><span class="default">0</span><span class="keyword">, </span><span class="default">100</span><span class="keyword">, </span><span class="default">100</span><span class="keyword">, </span><span class="default">100</span><span class="keyword">)) . </span><span class="string">"&lt;/p&gt;\n"</span><span class="keyword">;<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
&nbsp; &lt;--&nbsp; The results look like this&nbsp; --&gt;<br />
<br />
Line3D[start=Point3D(x=0, y=0, z=0), end=Point3D(x=1, y=1, z=1), length=1.73205080757]<br />
<br />
Line3D[start=Point3D(x=0, y=0, z=0), end=Point3D(x=100, y=100, z=0), length=141.421356237]<br />
<br />
Line3D[start=Point3D(x=0, y=0, z=0), end=Point3D(x=100, y=100, z=100), length=173.205080757]<br />
<br />
My absolute favorite thing about OOP is that "good" objects keep themselves in check. I mean really, it's the exact same thing in reality... like, if you hire a plumber to fix your kitchen sink, wouldn't you expect him to figure out the best plan of attack? Wouldn't he dislike the fact that you want to control the whole job? Wouldn't you expect him to not give you additional problems? And for god's sake, it is too much to ask that he cleans up before he leaves?<br />
<br />
I say, design your classes well, so they can do their jobs uninterrupted... who like bad news? And, if your classes and objects are well defined, educated, and have all the necessary data to work on (like the examples above do), you won't have to micro-manage the whole program from outside of the class. In other words... create an object, and LET IT RIP!</span>
</code></div>
  </div>
 </div>
 <a name="83412"></a>
 <div class="note">
  <strong class='user'>ashraf dot samhouri at hotmail dot com</strong>
  <a href="#83412" class="date">24-May-2008 06:35</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
@info -- 20-April<br />
<br />
This is because you requested class "b" before defining it, not because you defined class "b" before "a". It doesn't make a difference which class you define first.</span>
</code></div>
  </div>
 </div>
 <a name="82652"></a>
 <div class="note">
  <strong class='user'>info at youwanttoremovethisvakantiebaas dot nl</strong>
  <a href="#82652" class="date">20-Apr-2008 03:40</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
if you do this<br />
<span class="default">&lt;?php<br />
<br />
$x </span><span class="keyword">= new </span><span class="default">b</span><span class="keyword">();<br />
<br />
class </span><span class="default">b </span><span class="keyword">extends </span><span class="default">a </span><span class="keyword">{}<br />
<br />
class </span><span class="default">a </span><span class="keyword">{ }<br />
<br />
</span><span class="default">?&gt;<br />
</span>PHP will tell you "class b not found", because you've defined class b before a. However, the error tells you something different.... Got me a little confused :)</span>
</code></div>
  </div>
 </div>
 <a name="81141"></a>
 <div class="note">
  <strong class='user'>david dot schueler at tel-billig dot de</strong>
  <a href="#81141" class="date">15-Feb-2008 06:16</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you just want to create a new object that extends another object and you want to copy all variables from the father object, you may use this piece of code:<br />
<span class="default">&lt;?php<br />
$father </span><span class="keyword">=&amp; new </span><span class="default">father</span><span class="keyword">();<br />
</span><span class="default">$father</span><span class="keyword">-&gt;</span><span class="default">a_var </span><span class="keyword">= </span><span class="string">"Hello World."</span><span class="keyword">;<br />
<br />
</span><span class="default">$son </span><span class="keyword">= new </span><span class="default">son</span><span class="keyword">(</span><span class="default">$event</span><span class="keyword">);<br />
<br />
</span><span class="default">$son</span><span class="keyword">-&gt;</span><span class="default">say_hello</span><span class="keyword">();<br />
<br />
class </span><span class="default">father </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public </span><span class="default">$a_var</span><span class="keyword">;<br />
}<br />
<br />
class </span><span class="default">son </span><span class="keyword">extends </span><span class="default">father </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">__construct</span><span class="keyword">(</span><span class="default">$father_class</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; foreach (</span><span class="default">$father_class </span><span class="keyword">as </span><span class="default">$variable</span><span class="keyword">=&gt;</span><span class="default">$value</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">$variable </span><span class="keyword">= </span><span class="default">$value</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">say_hello</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">"Son says: "</span><span class="keyword">.</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">a_var</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
</span><span class="default">?&gt;<br />
</span>This outputs:<br />
<br />
Son says: Hello World.<br />
<br />
So you dont have to clone the entire object to get the contents of the variables from the father object.</span>
</code></div>
  </div>
 </div>
 <a name="79856"></a>
 <div class="note">
  <strong class='user'>aaron at thatone dot com</strong>
  <a href="#79856" class="date">15-Dec-2007 06:46</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I was confused at first about object assignment, because it's not quite the same as normal assignment or assignment by reference. But I think I've figured out what's going on.<br />
<br />
First, think of variables in PHP as data slots. Each one is a name that points to a data slot that can hold a value that is one of the basic data types: a number, a string, a boolean, etc. When you create a reference, you are making a second name that points at the same data slot. When you assign one variable to another, you are copying the contents of one data slot to another data slot.<br />
<br />
Now, the trick is that object instances are not like the basic data types. They cannot be held in the data slots directly. Instead, an object's "handle" goes in the data slot. This is an identifier that points at one particular instance of an obect. So, the object handle, although not directly visible to the programmer, is one of the basic datatypes. <br />
<br />
What makes this tricky is that when you take a variable which holds an object handle, and you assign it to another variable, that other variable gets a copy of the same object handle. This means that both variables can change the state of the same object instance. But they are not references, so if one of the variables is assigned a new value, it does not affect the other variable.<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="comment">// Assignment of an object<br />
</span><span class="keyword">Class </span><span class="default">Object</span><span class="keyword">{<br />
&nbsp;&nbsp; public </span><span class="default">$foo</span><span class="keyword">=</span><span class="string">"bar"</span><span class="keyword">;<br />
};<br />
<br />
</span><span class="default">$objectVar </span><span class="keyword">= new </span><span class="default">Object</span><span class="keyword">();<br />
</span><span class="default">$reference </span><span class="keyword">=&amp; </span><span class="default">$objectVar</span><span class="keyword">;<br />
</span><span class="default">$assignment </span><span class="keyword">= </span><span class="default">$objectVar<br />
<br />
</span><span class="comment">//<br />
// $objectVar ---&gt;+---------+<br />
//&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; |(handle1)----+<br />
// $reference ---&gt;+---------+&nbsp;&nbsp; |<br />
//&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; |<br />
//&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; +---------+&nbsp;&nbsp; |<br />
// $assignment --&gt;|(handle1)----+<br />
//&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; +---------+&nbsp;&nbsp; |<br />
//&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; |<br />
//&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; v<br />
//&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Object(1):foo="bar"<br />
//<br />
</span><span class="default">?&gt;<br />
</span><br />
$assignment has a different data slot from $objectVar, but its data slot holds a handle to the same object. This makes it behave in some ways like a reference. If you use the variable $objectVar to change the state of the Object instance, those changes also show up under $assignment, because it is pointing at that same Object instance.<br />
<br />
<span class="default">&lt;?php<br />
$objectVar</span><span class="keyword">-&gt;</span><span class="default">foo </span><span class="keyword">= </span><span class="string">"qux"</span><span class="keyword">;<br />
</span><span class="default">print_r</span><span class="keyword">( </span><span class="default">$objectVar </span><span class="keyword">);<br />
</span><span class="default">print_r</span><span class="keyword">( </span><span class="default">$reference </span><span class="keyword">);<br />
</span><span class="default">print_r</span><span class="keyword">( </span><span class="default">$assignment </span><span class="keyword">);<br />
<br />
</span><span class="comment">//<br />
// $objectVar ---&gt;+---------+<br />
//&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; |(handle1)----+<br />
// $reference ---&gt;+---------+&nbsp;&nbsp; |<br />
//&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; |<br />
//&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; +---------+&nbsp;&nbsp; |<br />
// $assignment --&gt;|(handle1)----+<br />
//&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; +---------+&nbsp;&nbsp; |<br />
//&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; |<br />
//&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; v<br />
//&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Object(1):foo="qux"<br />
//<br />
</span><span class="default">?&gt;<br />
</span><br />
But it is not exactly the same as a reference. If you null out $objectVar, you replace the handle in its data slot with NULL. This means that $reference, which points at the same data slot, will also be NULL. But $assignment, which is a different data slot, will still hold its copy of the handle to the Object instance, so it will not be NULL.<br />
<br />
<span class="default">&lt;?php<br />
$objectVar </span><span class="keyword">= </span><span class="default">null</span><span class="keyword">;<br />
</span><span class="default">print_r</span><span class="keyword">(</span><span class="default">$objectVar</span><span class="keyword">);<br />
</span><span class="default">print_r</span><span class="keyword">(</span><span class="default">$reference</span><span class="keyword">);<br />
</span><span class="default">print_r</span><span class="keyword">(</span><span class="default">$assignment</span><span class="keyword">);<br />
<br />
</span><span class="comment">//<br />
// $objectVar ---&gt;+---------+<br />
//&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; |&nbsp; NULL&nbsp;&nbsp; | <br />
// $reference ---&gt;+---------+<br />
//&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; <br />
//&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; +---------+<br />
// $assignment --&gt;|(handle1)----+<br />
//&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; +---------+&nbsp;&nbsp; |<br />
//&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; |<br />
//&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; v<br />
//&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Object(1):foo="qux"<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="78389"></a>
 <div class="note">
  <strong class='user'>alan at alan-ng dot net</strong>
  <a href="#78389" class="date">09-Oct-2007 09:41</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The following odd behavior happens in php version 5.1.4 (and presumably some other versions) that does not happen in php version 5.2.1 (and possibly other versions &gt; 5.1.4).<br />
<br />
<span class="default">&lt;?php<br />
<br />
$_SESSION</span><span class="keyword">[</span><span class="string">'instance'</span><span class="keyword">]=...;<br />
<br />
</span><span class="default">$instance</span><span class="keyword">=new </span><span class="default">SomeClass</span><span class="keyword">;<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
The second line will not only create the $instance object successfully, it will also modify the value of $_SESSION['instance']!<br />
<br />
The workaround I arrived at, after trial and error, was to avoid&nbsp; using object names which match a $_SESSION array key.<br />
<br />
This is not intended to be a bug report, since it was apparently fixed by version 5.2.1, so it's just a workaround suggestion.</span>
</code></div>
  </div>
 </div>
 <a name="70770"></a>
 <div class="note">
  <strong class='user'>Dan Dascalescu</strong>
  <a href="#70770" class="date">26-Oct-2006 11:00</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If E_STRICT is enabled, the first example will generate the following error (and a few others akin to it):<br />
<br />
Non-static method A::foo() should not be called statically on line 26<br />
<br />
The example should have explicitly declared the methods foo() and bar() as static:<br />
<br />
class A&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; <br />
{&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; static function foo()&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; { <br />
...</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=language.oop5.basic&amp;redirect=http://www.php.net/manual/en/language.oop5.basic.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.oop5.basic&amp;redirect=http://www.php.net/manual/en/language.oop5.basic.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/language.oop5.basic.php">show source</a> |
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