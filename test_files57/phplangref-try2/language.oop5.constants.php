<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Class Constants - Manual</title>
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
 <link rel="prev" href="language.oop5.properties.php" />
 <link rel="next" href="language.oop5.autoload.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/oop5.constants" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/language.oop5.constants.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="@w{YJG6GKB3}" />
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
 <li class="active"><a href="language.oop5.constants.php">Class Constants</a></li>
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
  <a href="language.oop5.autoload.php">Autoloading Classes<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.oop5.properties.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Properties</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.oop5.constants.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/language.oop5.constants.php">Brazilian Portuguese</option>
    <option value="zh/language.oop5.constants.php">Chinese (Simplified)</option>
    <option value="fr/language.oop5.constants.php">French</option>
    <option value="de/language.oop5.constants.php">German</option>
    <option value="ja/language.oop5.constants.php">Japanese</option>
    <option value="pl/language.oop5.constants.php">Polish</option>
    <option value="ro/language.oop5.constants.php">Romanian</option>
    <option value="ru/language.oop5.constants.php">Russian</option>
    <option value="fa/language.oop5.constants.php">Persian</option>
    <option value="es/language.oop5.constants.php">Spanish</option>
    <option value="tr/language.oop5.constants.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="language.oop5.constants" class="sect1">
  <h2 class="title">Class Constants</h2>
  <p class="para">
   It is possible to define constant values on a per-class basis remaining the
   same and unchangeable. Constants differ from normal variables in that you
   don&#039;t use the <var class="varname"><var class="varname">$</var></var> symbol to declare or use them. 
  </p>
  <p class="para">
   The value must be a constant expression, not (for example) a variable, a
   property, a result of a mathematical operation, or a function call.
  </p>
  <p class="para">
   It&#039;s also possible for interfaces to have <em>constants</em>. Look at 
   the <a href="language.oop5.interfaces.php" class="link">interface documentation</a> for 
   examples.
  </p>
  <p class="para">
   As of PHP 5.3.0, it&#039;s possible to reference the class using a variable.
   The variable&#039;s value can not be a keyword (e.g. <em>self</em>,
   <em>parent</em> and <em>static</em>).
  </p>
  <div class="example" id="example-172">
   <p><strong>Example #1 Defining and using a constant</strong></p>
   <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">class&nbsp;</span><span style="color: #0000BB">MyClass<br /></span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;const&nbsp;</span><span style="color: #0000BB">constant&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'constant&nbsp;value'</span><span style="color: #007700">;<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;function&nbsp;</span><span style="color: #0000BB">showConstant</span><span style="color: #007700">()&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;&nbsp;</span><span style="color: #0000BB">self</span><span style="color: #007700">::</span><span style="color: #0000BB">constant&nbsp;</span><span style="color: #007700">.&nbsp;</span><span style="color: #DD0000">"\n"</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br /><br />echo&nbsp;</span><span style="color: #0000BB">MyClass</span><span style="color: #007700">::</span><span style="color: #0000BB">constant&nbsp;</span><span style="color: #007700">.&nbsp;</span><span style="color: #DD0000">"\n"</span><span style="color: #007700">;<br /><br /></span><span style="color: #0000BB">$classname&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">"MyClass"</span><span style="color: #007700">;<br />echo&nbsp;</span><span style="color: #0000BB">$classname</span><span style="color: #007700">::</span><span style="color: #0000BB">constant&nbsp;</span><span style="color: #007700">.&nbsp;</span><span style="color: #DD0000">"\n"</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;As&nbsp;of&nbsp;PHP&nbsp;5.3.0<br /><br /></span><span style="color: #0000BB">$class&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">MyClass</span><span style="color: #007700">();<br /></span><span style="color: #0000BB">$class</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">showConstant</span><span style="color: #007700">();<br /><br />echo&nbsp;</span><span style="color: #0000BB">$class</span><span style="color: #007700">::</span><span style="color: #0000BB">constant</span><span style="color: #007700">.</span><span style="color: #DD0000">"\n"</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;As&nbsp;of&nbsp;PHP&nbsp;5.3.0<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
   </div>

  </div>
  
  <div class="example" id="example-173">
   <p><strong>Example #2 Static data example</strong></p>
   <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">class&nbsp;</span><span style="color: #0000BB">foo&nbsp;</span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;As&nbsp;of&nbsp;PHP&nbsp;5.3.0<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">const&nbsp;</span><span style="color: #0000BB">bar&nbsp;</span><span style="color: #007700">=&nbsp;&lt;&lt;&lt;'EOT'<br /></span><span style="color: #DD0000">bar<br /></span><span style="color: #007700">EOT;<br />}<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
   </div>

   <div class="example-contents"><p>
    Unlike heredocs, nowdocs can be used in any static data context.
   </p></div>
  </div>
  <blockquote class="note"><p><strong class="note">Note</strong>: 
   <p class="para">
    Nowdoc support was added in PHP 5.3.0.
   </p>
  </p></blockquote>
 </div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="language.oop5.autoload.php">Autoloading Classes<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.oop5.properties.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Properties</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.oop5.constants.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=language.oop5.constants&amp;redirect=@w{YJG6GKB3}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.oop5.constants&amp;redirect=@w{YJG6GKB3}">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Class Constants</strong>
 </div><div id="allnotes">
 <a name="108944"></a>
 <div class="note">
  <strong class='user'>ryan at derokorian dot com</strong>
  <a href="#108944" class="date">07-Jun-2012 03:21</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
It may seem obvious, but class constants are always publicly visible. They cannot be made private or protected. I do not see it state that in the docs anywhere.</span>
</code></div>
  </div>
 </div>
 <a name="104260"></a>
 <div class="note">
  <strong class='user'>tmp dot 4 dot longoria at gmail dot com</strong>
  <a href="#104260" class="date">04-Jun-2011 01:52</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
it's possible to declare constant in base class, and override it in child, and access to correct value of the const from the static method is possible by 'get_called_class' method:<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">abstract class </span><span class="default">dbObject<br />
</span><span class="keyword">{&nbsp; &nbsp; <br />
&nbsp;&nbsp;&nbsp; const </span><span class="default">TABLE_NAME</span><span class="keyword">=</span><span class="string">'undefined'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; public static function </span><span class="default">GetAll</span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$c </span><span class="keyword">= </span><span class="default">get_called_class</span><span class="keyword">();<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="string">"SELECT * FROM `"</span><span class="keyword">.</span><span class="default">$c</span><span class="keyword">::</span><span class="default">TABLE_NAME</span><span class="keyword">.</span><span class="string">"`"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }&nbsp; &nbsp; <br />
}<br />
<br />
class </span><span class="default">dbPerson </span><span class="keyword">extends </span><span class="default">dbObject<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; const </span><span class="default">TABLE_NAME</span><span class="keyword">=</span><span class="string">'persons'</span><span class="keyword">;<br />
}<br />
<br />
class </span><span class="default">dbAdmin </span><span class="keyword">extends </span><span class="default">dbPerson<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; const </span><span class="default">TABLE_NAME</span><span class="keyword">=</span><span class="string">'admins'</span><span class="keyword">;<br />
}<br />
<br />
echo </span><span class="default">dbPerson</span><span class="keyword">::</span><span class="default">GetAll</span><span class="keyword">().</span><span class="string">"&lt;br&gt;"</span><span class="keyword">;</span><span class="comment">//output: "SELECT * FROM `persons`"<br />
</span><span class="keyword">echo </span><span class="default">dbAdmin</span><span class="keyword">::</span><span class="default">GetAll</span><span class="keyword">().</span><span class="string">"&lt;br&gt;"</span><span class="keyword">;</span><span class="comment">//output: "SELECT * FROM `admins`"<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="102199"></a>
 <div class="note">
  <strong class='user'>dexen dot devries at gmail dot com</strong>
  <a href="#102199" class="date">02-Feb-2011 01:42</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Summary: use ReflectionObject to access class const in PHP older than 5.3.<br />
<br />
In versions &lt;= 5.2 you can't use the $object::constMember syntax. Accessing via class name or the `self' operator doesn't resolve the right class in case of inheritance (for the C++ folks, it behaves like a NON-virtual method). The only working (if ugly) solution to access seems to be to use the Reflection extension:<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">class </span><span class="default">Foo </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; const </span><span class="default">a </span><span class="keyword">= </span><span class="default">7</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; const </span><span class="default">x </span><span class="keyword">= </span><span class="default">99</span><span class="keyword">;<br />
}<br />
<br />
class </span><span class="default">Bar </span><span class="keyword">extends </span><span class="default">Foo </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; const </span><span class="default">a </span><span class="keyword">= </span><span class="default">42</span><span class="keyword">; </span><span class="comment">/* overrides the `a = 7' in base class */<br />
</span><span class="keyword">}<br />
<br />
</span><span class="default">$b </span><span class="keyword">= new </span><span class="default">Bar</span><span class="keyword">();<br />
</span><span class="default">$r </span><span class="keyword">= new </span><span class="default">ReflectionObject</span><span class="keyword">(</span><span class="default">$b</span><span class="keyword">);<br />
echo </span><span class="default">$r</span><span class="keyword">-&gt;</span><span class="default">getConstant</span><span class="keyword">(</span><span class="string">'a'</span><span class="keyword">);&nbsp; </span><span class="comment"># prints `42' from the Bar class<br />
</span><span class="keyword">echo </span><span class="string">"\n"</span><span class="keyword">;<br />
echo </span><span class="default">$r</span><span class="keyword">-&gt;</span><span class="default">getConstant</span><span class="keyword">(</span><span class="string">'x'</span><span class="keyword">);&nbsp; </span><span class="comment"># prints `99' inherited from the Foo class<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="100909"></a>
 <div class="note">
  <strong class='user'>jakub dot lopuszanski at nasza-klasa dot pl</strong>
  <a href="#100909" class="date">14-Nov-2010 11:20</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Suprisingly consts are lazy bound even though you use self instead of static:<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">A</span><span class="keyword">{<br />
&nbsp; const </span><span class="default">X</span><span class="keyword">=</span><span class="default">1</span><span class="keyword">;<br />
&nbsp; const </span><span class="default">Y</span><span class="keyword">=</span><span class="default">self</span><span class="keyword">::</span><span class="default">X</span><span class="keyword">;<br />
}<br />
class </span><span class="default">B </span><span class="keyword">extends </span><span class="default">A</span><span class="keyword">{<br />
&nbsp; const </span><span class="default">X</span><span class="keyword">=</span><span class="default">1.0</span><span class="keyword">;<br />
}<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">B</span><span class="keyword">::</span><span class="default">Y</span><span class="keyword">); </span><span class="comment">// float(1.0)<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="100142"></a>
 <div class="note">
  <strong class='user'>anonymous</strong>
  <a href="#100142" class="date">27-Sep-2010 06:32</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Most people miss the point in declaring constants and confuse then things by trying to declare things like functions or arrays as constants. What happens next is to try things that are more complicated then necessary and sometimes lead to bad coding practices. Let me explain...<br />
<br />
A constant is a name for a value (but it's NOT a variable), that usually will be replaced in the code while it gets COMPILED and NOT at runtime. <br />
<br />
So returned values from functions can't be used, because they will return a value only at runtime. <br />
<br />
Arrays can't be used, because they are data structures that exist at runtime. <br />
<br />
One main purpose of declaring a constant is usually using a value in your code, that you can replace easily in one place without looking for all the occurences. Another is, to avoid mistakes. <br />
<br />
Think about some examples written by some before me: <br />
<br />
1. const MY_ARR = "return array(\"A\", \"B\", \"C\", \"D\");";<br />
It was said, this would declare an array that can be used with eval. WRONG! This is just a string as constant, NOT an array. Does it make sense if it would be possible to declare an array as constant? Probably not. Instead declare the values of the array as constants and make an array variable. <br />
<br />
2. const magic_quotes = (bool)get_magic_quotes_gpc();<br />
This can't work, of course. And it doesn't make sense either. The function already returns the value, there is no purpose in declaring a constant for the same thing. <br />
<br />
3. Someone spoke about "dynamic" assignments to constants. What? There are no dynamic assignments to constants, runtime assignments work _only_ with variables. Let's take the proposed example: <br />
<br />
<span class="default">&lt;?php<br />
</span><span class="comment">/**<br />
&nbsp;* Constants that deal only with the database<br />
&nbsp;*/<br />
</span><span class="keyword">class </span><span class="default">DbConstant </span><span class="keyword">extends </span><span class="default">aClassConstant </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; protected </span><span class="default">$host </span><span class="keyword">= </span><span class="string">'localhost'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; protected </span><span class="default">$user </span><span class="keyword">= </span><span class="string">'user'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; protected </span><span class="default">$password </span><span class="keyword">= </span><span class="string">'pass'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; protected </span><span class="default">$database </span><span class="keyword">= </span><span class="string">'db'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; protected </span><span class="default">$time</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">__construct</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">time </span><span class="keyword">= </span><span class="default">time</span><span class="keyword">() + </span><span class="default">1</span><span class="keyword">; </span><span class="comment">// dynamic assignment<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">}<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
Those aren't constants, those are properties of the class. Something like "this-&gt;time = time()" would even totally defy the purpose of a constant. Constants are supposed to be just that, constant values, on every execution. They are not supposed to change every time a script runs or a class is instantiated. <br />
<br />
Conclusion: Don't try to reinvent constants as variables. If constants don't work, just use variables. Then you don't need to reinvent methods to achieve things for what is already there.</span>
</code></div>
  </div>
 </div>
 <a name="94604"></a>
 <div class="note">
  <strong class='user'>Anonymous</strong>
  <a href="#94604" class="date">13-Nov-2009 12:03</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Note that since constants are tied to the class definition, they are static by definition and cannot be accessed using the -&gt; operator.<br />
<br />
A side effect of this is that it's entirely possible for a class constant to have the same name as a property (static or object):<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">Foo<br />
</span><span class="keyword">{<br />
&nbsp; const </span><span class="default">foo </span><span class="keyword">= </span><span class="string">'bar'</span><span class="keyword">;<br />
&nbsp; public </span><span class="default">$foo </span><span class="keyword">= </span><span class="string">'foobar'</span><span class="keyword">;<br />
<br />
&nbsp; const </span><span class="default">bar </span><span class="keyword">= </span><span class="string">'foo'</span><span class="keyword">;<br />
&nbsp; static </span><span class="default">$bar </span><span class="keyword">= </span><span class="string">'foobar'</span><span class="keyword">;<br />
}<br />
<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">foo</span><span class="keyword">::</span><span class="default">$bar</span><span class="keyword">); </span><span class="comment">// static property<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">foo</span><span class="keyword">::</span><span class="default">bar</span><span class="keyword">);&nbsp; </span><span class="comment">// class constant<br />
<br />
</span><span class="default">$bar </span><span class="keyword">= new </span><span class="default">Foo</span><span class="keyword">();<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$bar</span><span class="keyword">-&gt;</span><span class="default">foo</span><span class="keyword">); </span><span class="comment">// object property<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">bar</span><span class="keyword">::</span><span class="default">foo</span><span class="keyword">); </span><span class="comment">// class constant<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="86179"></a>
 <div class="note">
  <strong class='user'>cwreace at yahoo dot com</strong>
  <a href="#86179" class="date">06-Oct-2008 06:22</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
A useful technique I've found is to use interfaces for package- or application-wide constants, making it easy to incorporate them into any classes that need access to them:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">interface </span><span class="default">AppConstants<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp; const </span><span class="default">FOOBAR </span><span class="keyword">= </span><span class="string">'Hello, World.'</span><span class="keyword">;<br />
}<br />
<br />
class </span><span class="default">Example </span><span class="keyword">implements </span><span class="default">AppConstants<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp; public function </span><span class="default">test</span><span class="keyword">()<br />
&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp;&nbsp; echo </span><span class="default">self</span><span class="keyword">::</span><span class="default">FOOBAR</span><span class="keyword">;<br />
&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="default">$obj </span><span class="keyword">= new </span><span class="default">Example</span><span class="keyword">();<br />
</span><span class="default">$obj</span><span class="keyword">-&gt;</span><span class="default">test</span><span class="keyword">();&nbsp; </span><span class="comment">// outputs "Hello, world."<br />
</span><span class="default">?&gt;<br />
</span><br />
I realize the same could be done simply by defining the constant in a class and accessing it via "class_name::const_name", but I find this a little nicer in that the class declaration makes it immediately obvious that you accessing values from the implemented interface.</span>
</code></div>
  </div>
 </div>
 <a name="85703"></a>
 <div class="note">
  <strong class='user'>wbcarts at juno dot com</strong>
  <a href="#85703" class="date">12-Sep-2008 12:12</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Use CONST to set UPPER and LOWER LIMITS<br />
<br />
If you have code that accepts user input or you just need to make sure input is acceptable, you can use constants to set upper and lower limits. Note: a static function that enforces your limits is highly recommended... sniff the clamp() function below for a taste.<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">class </span><span class="default">Dimension<br />
</span><span class="keyword">{<br />
&nbsp; const </span><span class="default">MIN </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">, </span><span class="default">MAX </span><span class="keyword">= </span><span class="default">800</span><span class="keyword">;<br />
<br />
&nbsp; public </span><span class="default">$width</span><span class="keyword">, </span><span class="default">$height</span><span class="keyword">;<br />
<br />
&nbsp; public function </span><span class="default">__construct</span><span class="keyword">(</span><span class="default">$w </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">, </span><span class="default">$h </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">){<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">width&nbsp; </span><span class="keyword">= </span><span class="default">self</span><span class="keyword">::</span><span class="default">clamp</span><span class="keyword">(</span><span class="default">$w</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">height </span><span class="keyword">= </span><span class="default">self</span><span class="keyword">::</span><span class="default">clamp</span><span class="keyword">(</span><span class="default">$h</span><span class="keyword">);<br />
&nbsp; }<br />
<br />
&nbsp; public function </span><span class="default">__toString</span><span class="keyword">(){<br />
&nbsp;&nbsp;&nbsp; return </span><span class="string">"Dimension [width=$this-&gt;width, height=$this-&gt;height]"</span><span class="keyword">;<br />
&nbsp; }<br />
<br />
&nbsp; protected static function </span><span class="default">clamp</span><span class="keyword">(</span><span class="default">$value</span><span class="keyword">){<br />
&nbsp;&nbsp;&nbsp; if(</span><span class="default">$value </span><span class="keyword">&lt; </span><span class="default">self</span><span class="keyword">::</span><span class="default">MIN</span><span class="keyword">) </span><span class="default">$value </span><span class="keyword">= </span><span class="default">self</span><span class="keyword">::</span><span class="default">MIN</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; if(</span><span class="default">$value </span><span class="keyword">&gt; </span><span class="default">self</span><span class="keyword">::</span><span class="default">MAX</span><span class="keyword">) </span><span class="default">$value </span><span class="keyword">= </span><span class="default">self</span><span class="keyword">::</span><span class="default">MAX</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">$value</span><span class="keyword">;<br />
&nbsp; }<br />
}<br />
<br />
echo (new </span><span class="default">Dimension</span><span class="keyword">()) . </span><span class="string">'&lt;br&gt;'</span><span class="keyword">;<br />
echo (new </span><span class="default">Dimension</span><span class="keyword">(</span><span class="default">1500</span><span class="keyword">, </span><span class="default">97</span><span class="keyword">)) . </span><span class="string">'&lt;br&gt;'</span><span class="keyword">;<br />
echo (new </span><span class="default">Dimension</span><span class="keyword">(</span><span class="default">14</span><span class="keyword">, -</span><span class="default">20</span><span class="keyword">)) . </span><span class="string">'&lt;br&gt;'</span><span class="keyword">;<br />
echo (new </span><span class="default">Dimension</span><span class="keyword">(</span><span class="default">240</span><span class="keyword">, </span><span class="default">80</span><span class="keyword">)) . </span><span class="string">'&lt;br&gt;'</span><span class="keyword">;<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
- - - - - - - -<br />
&nbsp;Dimension [width=0, height=0] - default size<br />
&nbsp;Dimension [width=800, height=97] - width has been clamped to MAX<br />
&nbsp;Dimension [width=14, height=0] - height has been clamped to MIN<br />
&nbsp;Dimension [width=240, height=80] - width and height unchanged<br />
- - - - - - - -<br />
<br />
Setting upper and lower limits on your classes also help your objects make sense. For example, it is not possible for the width or height of a Dimension to be negative. It is up to you to keep phoney input from corrupting your objects, and to avoid potential errors and exceptions in other parts of your code.</span>
</code></div>
  </div>
 </div>
 <a name="84182"></a>
 <div class="note">
  <strong class='user'>elmar huebschmann</strong>
  <a href="#84182" class="date">02-Jul-2008 06:12</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The major problem of constants is for me, you cant use them for binary flags. <br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">constant </span><span class="keyword">{<br />
<br />
&nbsp;&nbsp;&nbsp; const </span><span class="default">MODE_FLAG_1 </span><span class="keyword">= </span><span class="default">1</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; const </span><span class="default">MODE_FLAG_2 </span><span class="keyword">= </span><span class="default">2</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; const </span><span class="default">MODE_FLAG_3 </span><span class="keyword">= </span><span class="default">4</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp;&nbsp; const </span><span class="default">DEFAULT_MODE </span><span class="keyword">= </span><span class="default">self</span><span class="keyword">::</span><span class="default">FLAG_1 </span><span class="keyword">| </span><span class="default">self</span><span class="keyword">::</span><span class="default">FLAG_2<br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">private function </span><span class="default">foo </span><span class="keyword">(</span><span class="default">$mode</span><span class="keyword">=</span><span class="default">self</span><span class="keyword">::</span><span class="default">DEFAULT_MODE</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// some operations<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">}<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
This code will not work because constants can't be an calculation result. You could use<br />
<br />
<span class="default">&lt;?php<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">const </span><span class="default">DEFAULT_MODE </span><span class="keyword">= </span><span class="default">3</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
instead, but we use flags to be value indipendent. So you would miss target with it. Only way is to use defines like ever before.</span>
</code></div>
  </div>
 </div>
 <a name="81072"></a>
 <div class="note">
  <strong class='user'>riku at helloit dot fi</strong>
  <a href="#81072" class="date">13-Feb-2008 03:24</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
pre 5.3 can refer a class using variable and get constants with:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">get_class_const</span><span class="keyword">(</span><span class="default">$class</span><span class="keyword">, </span><span class="default">$const</span><span class="keyword">){<br />
&nbsp; return </span><span class="default">constant</span><span class="keyword">(</span><span class="default">sprintf</span><span class="keyword">(</span><span class="string">'%s::%s'</span><span class="keyword">, </span><span class="default">$class</span><span class="keyword">, </span><span class="default">$const</span><span class="keyword">));<br />
}<br />
<br />
class </span><span class="default">Foo</span><span class="keyword">{<br />
&nbsp; const </span><span class="default">BAR </span><span class="keyword">= </span><span class="string">'foobar'</span><span class="keyword">;<br />
}<br />
<br />
</span><span class="default">$class </span><span class="keyword">= </span><span class="string">'Foo'</span><span class="keyword">;<br />
<br />
echo </span><span class="default">get_class_const</span><span class="keyword">(</span><span class="default">$class</span><span class="keyword">, </span><span class="string">'BAR'</span><span class="keyword">);<br />
</span><span class="comment">//'foobar'<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="78292"></a>
 <div class="note">
  <strong class='user'>nrg1981 {AT} hotmail {DOT} com</strong>
  <a href="#78292" class="date">05-Oct-2007 06:19</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you have a class which defines a constant which may be overridden in child definitions, here are two methods how the parent can access that constant:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">Weather<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; const </span><span class="default">danger </span><span class="keyword">= </span><span class="string">'parent'</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp;&nbsp; static function </span><span class="default">getDanger</span><span class="keyword">(</span><span class="default">$class</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// Code to return the danger field from the given class name<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">}<br />
<br />
}<br />
<br />
class </span><span class="default">Rain </span><span class="keyword">extends </span><span class="default">Weather<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; const </span><span class="default">danger </span><span class="keyword">= </span><span class="string">'child'</span><span class="keyword">;<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
The two options to place in the parent accessor are:<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; eval('$danger = ' . $class . '::danger;');<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
or:<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; $danger = constant($class . '::danger');<br />
<br />
I prefer the last option, but they both seem to work.<br />
<br />
So, why might this be useful?&nbsp;&nbsp; Well, in my case I have a page class which contains various common functions for all pages and specific page classes extend this parent class.&nbsp;&nbsp; The parent class has a static method which takes an argument (class name) and returns a new instantiation of the class.&nbsp;&nbsp; <br />
<br />
Each child class has a constant which defines the access level the user must have in order to view the page.&nbsp;&nbsp; The parent must check this variable before creating and returning an instance of the child - the problem is that the class name is a variable and $class::danger will treat $class as an object.</span>
</code></div>
  </div>
 </div>
 <a name="71621"></a>
 <div class="note">
  <strong class='user'>michikono at symbol gmail dot com</strong>
  <a href="#71621" class="date">07-Dec-2006 08:58</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
In realizing it is impossible to create dynamic constants, I opted for a "read only" constants class. <br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">abstract class </span><span class="default">aClassConstant </span><span class="keyword">{&nbsp; &nbsp; <br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">/**<br />
&nbsp;&nbsp; &nbsp; * Setting is not permitted.<br />
&nbsp;&nbsp; &nbsp; *<br />
&nbsp;&nbsp; &nbsp; * @param&nbsp; &nbsp; string&nbsp; &nbsp; constant name<br />
&nbsp;&nbsp; &nbsp; * @param&nbsp; &nbsp; mixed&nbsp; &nbsp; new value<br />
&nbsp;&nbsp; &nbsp; * @return&nbsp; &nbsp; void<br />
&nbsp;&nbsp; &nbsp; * @throws&nbsp; &nbsp; Exception<br />
&nbsp;&nbsp; &nbsp; */<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">final function </span><span class="default">__set</span><span class="keyword">(</span><span class="default">$member</span><span class="keyword">, </span><span class="default">$value</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; throw new </span><span class="default">Exception</span><span class="keyword">(</span><span class="string">'You cannot set a constant.'</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">/**<br />
&nbsp;&nbsp; &nbsp; * Get the value of the constant<br />
&nbsp;&nbsp; &nbsp; *<br />
&nbsp;&nbsp; &nbsp; * @param&nbsp; &nbsp; string&nbsp; &nbsp; constant name<br />
&nbsp;&nbsp; &nbsp; * @return&nbsp; &nbsp; void<br />
&nbsp;&nbsp; &nbsp; */<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">final function </span><span class="default">__get</span><span class="keyword">(</span><span class="default">$member</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">$member</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
The class would be extended by another class that would compartmentalize the purpose of the constants. Thus, for example, you would extend the class with a DbConstant class for managing database related constants, that might look like this:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="comment">/**<br />
&nbsp;* Constants that deal only with the database<br />
&nbsp;*/<br />
</span><span class="keyword">class </span><span class="default">DbConstant </span><span class="keyword">extends </span><span class="default">aClassConstant </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; protected </span><span class="default">$host </span><span class="keyword">= </span><span class="string">'localhost'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; protected </span><span class="default">$user </span><span class="keyword">= </span><span class="string">'user'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; protected </span><span class="default">$password </span><span class="keyword">= </span><span class="string">'pass'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; protected </span><span class="default">$database </span><span class="keyword">= </span><span class="string">'db'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; protected </span><span class="default">$time</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">/**<br />
&nbsp;&nbsp; &nbsp; * Constructor. This is so fully dynamic values can be set. This can be skipped and the values can be directly assigned for non dynamic values as shown above.<br />
&nbsp;&nbsp; &nbsp; *<br />
&nbsp;&nbsp; &nbsp; * @return&nbsp; &nbsp; void<br />
&nbsp;&nbsp; &nbsp; */<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">function </span><span class="default">__construct</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">time </span><span class="keyword">= </span><span class="default">time</span><span class="keyword">() + </span><span class="default">1</span><span class="keyword">; </span><span class="comment">// dynamic assignment<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">}<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
You would use the class like thus:<br />
<br />
<span class="default">&lt;?php<br />
$dbConstant </span><span class="keyword">= new </span><span class="default">DbConstant</span><span class="keyword">();<br />
echo </span><span class="default">$dbConstant</span><span class="keyword">-&gt;</span><span class="default">host</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
The following would cause an exception:<br />
<br />
<span class="default">&lt;?php<br />
$dbConstant </span><span class="keyword">= new </span><span class="default">DbConstant</span><span class="keyword">();<br />
</span><span class="default">$dbConstant</span><span class="keyword">-&gt;</span><span class="default">host </span><span class="keyword">= </span><span class="string">'127.0.0.1'</span><span class="keyword">; </span><span class="comment">// EXCEPTION<br />
</span><span class="default">?&gt;<br />
</span><br />
It's not pretty, nor ideal, but at least you don't pollute the global name space with long winded global names and it is relatively elegant.<br />
<br />
Variables must be *protected*, not public. Public variables will bypass the __get and __set methods!! This class is, by design, not meant to be extended much further than one level, as it is really meant to only contain constants. By keeping the constant definition class seperate from the rest of your classes (if you are calling this from a class), you minimize the possibility of accidental variable assignment. <br />
<br />
Managing this instance may be a slight pain that requires either caching a copy of the instance in a class variable, or using the factory pattern. Unfortunately, static methods can't detect the correct class name when the parent name is used during the call (e.g., DbConstant::instance()). Thus there is no elegant, inheriting solution to that problem. Thus, it is easier to simply manage a single instance that is declared using conventional notation (e.g., new DbConstant...).<br />
<br />
- Michi Kono</span>
</code></div>
  </div>
 </div>
 <a name="69866"></a>
 <div class="note">
  <strong class='user'>webmaster at chaosonline dot de</strong>
  <a href="#69866" class="date">24-Sep-2006 04:57</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Since constants of a child class are not accessible from the parent class via self::CONST and there is no special keyword to access the constant (like this::CONST), i use private static variables and these two methods to make them read-only accessible from object's parent/child classes as well as statically from outside:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">b </span><span class="keyword">extends </span><span class="default">a </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; private static </span><span class="default">$CONST </span><span class="keyword">= </span><span class="string">'any value'</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp;&nbsp; public static function </span><span class="default">getConstFromOutside</span><span class="keyword">(</span><span class="default">$const</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">self</span><span class="keyword">::$</span><span class="default">$const</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; protected function </span><span class="default">getConst</span><span class="keyword">(</span><span class="default">$const</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">self</span><span class="keyword">::$</span><span class="default">$const</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
With those methods in the child class, you are now able to read the variables from the parent or child class:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">a </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; private function </span><span class="default">readConst</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">getConst</span><span class="keyword">(</span><span class="string">'CONST'</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; abstract public static function </span><span class="default">getConstFromOutside</span><span class="keyword">(</span><span class="default">$const</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; abstract protected function </span><span class="default">getConst</span><span class="keyword">(</span><span class="default">$const</span><span class="keyword">);<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
From outside of the object:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">echo </span><span class="default">b</span><span class="keyword">::</span><span class="default">getConstFromOutside</span><span class="keyword">(</span><span class="string">'CONST'</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span><br />
You maybe want to put the methods into an interface.<br />
<br />
However, class b's attribute $CONST is not a constant, so it is changeable by methods inside of class b, but it works for me and in my opinion, it is better than using real constants and accessing them by calling with eval:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">protected function </span><span class="default">getConst</span><span class="keyword">(</span><span class="default">$const</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; eval(</span><span class="string">'$value = '</span><span class="keyword">.</span><span class="default">get_class</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">).</span><span class="string">'::'</span><span class="keyword">.</span><span class="default">$const</span><span class="keyword">.</span><span class="string">';'</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">$value</span><span class="keyword">;<br />
}<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="67816"></a>
 <div class="note">
  <strong class='user'>sw at knip dot pol dot lublin dot pl</strong>
  <a href="#67816" class="date">05-Jul-2006 12:14</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
It might be obvious,<br />
but I noticed that you can't define an array as a class constant.<br />
<br />
Insteed you can define AND initialize an static array variable:<br />
<br />
<span class="default">&lt;?php <br />
<br />
</span><span class="keyword">class </span><span class="default">AClass </span><span class="keyword">{<br />
<br />
&nbsp;&nbsp;&nbsp; const </span><span class="default">an_array </span><span class="keyword">= Array (</span><span class="default">1</span><span class="keyword">,</span><span class="default">2</span><span class="keyword">,</span><span class="default">3</span><span class="keyword">,</span><span class="default">4</span><span class="keyword">);&nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp;&nbsp; </span><span class="comment">//this WILL NOT work<br />
&nbsp;&nbsp; &nbsp;&nbsp; // and will throw Fatal Error:<br />
&nbsp;&nbsp; &nbsp;&nbsp; //Fatal error: Arrays are not allowed in class constants in... <br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">public static </span><span class="default">$an_array </span><span class="keyword">= Array (</span><span class="default">1</span><span class="keyword">,</span><span class="default">2</span><span class="keyword">,</span><span class="default">3</span><span class="keyword">,</span><span class="default">4</span><span class="keyword">); <br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">//this WILL work<br />
&nbsp;&nbsp;&nbsp; //however, you have no guarantee that it will not be modified outside your class<br />
<br />
</span><span class="keyword">}<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="53935"></a>
 <div class="note">
  <a href="#53935" class="date">17-Jun-2005 09:29</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
It's important to note that constants cannot be overridden by an extended class, if you with to use them in virtual functions.&nbsp; For example : <br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">abc<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; const </span><span class="default">avar </span><span class="keyword">= </span><span class="string">"abc's"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">show</span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="default">self</span><span class="keyword">::</span><span class="default">avar </span><span class="keyword">. </span><span class="string">"\r\n"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
};<br />
<br />
class </span><span class="default">def </span><span class="keyword">extends </span><span class="default">abc<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; const </span><span class="default">avar </span><span class="keyword">= </span><span class="string">"def's"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">showmore </span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="default">self</span><span class="keyword">::</span><span class="default">avar </span><span class="keyword">. </span><span class="string">"\r\n"</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">show</span><span class="keyword">();<br />
&nbsp;&nbsp;&nbsp; }<br />
};<br />
<br />
</span><span class="default">$bob </span><span class="keyword">= new </span><span class="default">def</span><span class="keyword">();<br />
</span><span class="default">$bob</span><span class="keyword">-&gt;</span><span class="default">showmore</span><span class="keyword">();<br />
</span><span class="default">?&gt;<br />
</span><br />
Will display:<br />
def's<br />
abc's<br />
<br />
However, if you use variables instead the output is different, such as:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">abc<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; protected </span><span class="default">$avar </span><span class="keyword">= </span><span class="string">"abc's"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">show</span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">avar </span><span class="keyword">. </span><span class="string">"\r\n"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
};<br />
<br />
class </span><span class="default">def </span><span class="keyword">extends </span><span class="default">abc<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; protected </span><span class="default">$avar </span><span class="keyword">= </span><span class="string">"def's"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">showmore </span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">avar </span><span class="keyword">. </span><span class="string">"\r\n"</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">show</span><span class="keyword">();<br />
&nbsp;&nbsp;&nbsp; }<br />
};<br />
<br />
</span><span class="default">$bob </span><span class="keyword">= new </span><span class="default">def</span><span class="keyword">();<br />
</span><span class="default">$bob</span><span class="keyword">-&gt;</span><span class="default">showmore</span><span class="keyword">();<br />
</span><span class="default">?&gt;<br />
</span><br />
Will output:<br />
def's<br />
def's</span>
</code></div>
  </div>
 </div>
 <a name="48224"></a>
 <div class="note">
  <strong class='user'>caliban at darklock dot com</strong>
  <a href="#48224" class="date">15-Dec-2004 10:55</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Lest anyone think this is somehow an omission in PHP, there is simply no point to having a protected or private constant. Access specifiers identify who has the right to *change* members, not who has the right to read them:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="comment">// define a test class<br />
</span><span class="keyword">class </span><span class="default">Test <br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public static </span><span class="default">$open</span><span class="keyword">=</span><span class="default">2</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; protected static </span><span class="default">$var</span><span class="keyword">=</span><span class="default">1</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; private static </span><span class="default">$secret</span><span class="keyword">=</span><span class="default">3</span><span class="keyword">;<br />
}<br />
<br />
</span><span class="default">$classname</span><span class="keyword">=</span><span class="string">"Test"</span><span class="keyword">;<br />
<br />
</span><span class="comment">// reflect class information<br />
</span><span class="default">$x</span><span class="keyword">=new </span><span class="default">ReflectionClass</span><span class="keyword">(</span><span class="default">$classname</span><span class="keyword">);<br />
</span><span class="default">$y</span><span class="keyword">=array();<br />
foreach(</span><span class="default">$x</span><span class="keyword">-&gt;</span><span class="default">GetStaticProperties</span><span class="keyword">() as </span><span class="default">$k</span><span class="keyword">=&gt;</span><span class="default">$v</span><span class="keyword">) <br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$y</span><span class="keyword">[</span><span class="default">str_replace</span><span class="keyword">(</span><span class="default">chr</span><span class="keyword">(</span><span class="default">0</span><span class="keyword">),</span><span class="string">"@"</span><span class="keyword">,</span><span class="default">$k</span><span class="keyword">)]=</span><span class="default">$v</span><span class="keyword">;<br />
<br />
</span><span class="comment">// define the variables to search for<br />
</span><span class="default">$a</span><span class="keyword">=array(</span><span class="string">"open"</span><span class="keyword">,</span><span class="string">"var"</span><span class="keyword">,</span><span class="string">"secret"</span><span class="keyword">,</span><span class="string">"nothing"</span><span class="keyword">);<br />
foreach(</span><span class="default">$a </span><span class="keyword">as </span><span class="default">$b</span><span class="keyword">)<br />
{<br />
&nbsp;&nbsp;&nbsp; if(isset(</span><span class="default">$y</span><span class="keyword">[</span><span class="string">"$b"</span><span class="keyword">])) <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">"\"$b\" is public: {$y["</span><span class="default">$b</span><span class="string">"]}&lt;br/&gt;"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; elseif(isset(</span><span class="default">$y</span><span class="keyword">[</span><span class="string">"@*@$b"</span><span class="keyword">])) <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">"\"$b\" is protected: {$y["</span><span class="default">@*@$b</span><span class="string">"]}&lt;br/&gt;"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; elseif(isset(</span><span class="default">$y</span><span class="keyword">[</span><span class="string">"@$classname@$b"</span><span class="keyword">])) <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">"\"$b\" is private: {$y["</span><span class="default">@$classname@$b</span><span class="string">"]}&lt;br/&gt;"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; else <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">"\"$b\" is not a static member of $classname&lt;br/&gt;"</span><span class="keyword">;<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
As you can see from the results of this code, the protected and private static members of Test are still visible if you know where to look. The protection and privacy are applicable only on writing, not reading -- and since nobody can write to a constant at all, assigning an access specifier to it is just redundant.</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=language.oop5.constants&amp;redirect=@w{YJG6GKB3}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.oop5.constants&amp;redirect=@w{YJG6GKB3}">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/language.oop5.constants.php">show source</a> |
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