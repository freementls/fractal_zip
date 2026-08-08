<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Final Keyword - Manual</title>
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
 <link rel="prev" href="language.oop5.magic.php" />
 <link rel="next" href="language.oop5.cloning.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/oop5.final" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/language.oop5.final.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/language.oop5.final.php" />
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
 <li class="active"><a href="language.oop5.final.php">Final Keyword</a></li>
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
  <a href="language.oop5.cloning.php">Object Cloning<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.oop5.magic.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Magic Methods</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.oop5.final.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/language.oop5.final.php">Brazilian Portuguese</option>
    <option value="zh/language.oop5.final.php">Chinese (Simplified)</option>
    <option value="fr/language.oop5.final.php">French</option>
    <option value="de/language.oop5.final.php">German</option>
    <option value="ja/language.oop5.final.php">Japanese</option>
    <option value="pl/language.oop5.final.php">Polish</option>
    <option value="ro/language.oop5.final.php">Romanian</option>
    <option value="ru/language.oop5.final.php">Russian</option>
    <option value="fa/language.oop5.final.php">Persian</option>
    <option value="es/language.oop5.final.php">Spanish</option>
    <option value="tr/language.oop5.final.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="language.oop5.final" class="sect1">
  <h2 class="title">Final Keyword</h2>
  <p class="para">
   PHP 5 introduces the final keyword, which prevents child classes from
   overriding a method by prefixing the definition with final. If the class
   itself is being defined final then it cannot be extended.
  </p>
  <p class="para">
   <div class="example" id="example-216">
    <p><strong>Example #1 Final methods example</strong></p>
    <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">class&nbsp;</span><span style="color: #0000BB">BaseClass&nbsp;</span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">test</span><span style="color: #007700">()&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"BaseClass::test()&nbsp;called\n"</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;}<br />&nbsp;&nbsp;&nbsp;<br />&nbsp;&nbsp;&nbsp;final&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">moreTesting</span><span style="color: #007700">()&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"BaseClass::moreTesting()&nbsp;called\n"</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;}<br />}<br /><br />class&nbsp;</span><span style="color: #0000BB">ChildClass&nbsp;</span><span style="color: #007700">extends&nbsp;</span><span style="color: #0000BB">BaseClass&nbsp;</span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">moreTesting</span><span style="color: #007700">()&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"ChildClass::moreTesting()&nbsp;called\n"</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;}<br />}<br /></span><span style="color: #FF8000">//&nbsp;Results&nbsp;in&nbsp;Fatal&nbsp;error:&nbsp;Cannot&nbsp;override&nbsp;final&nbsp;method&nbsp;BaseClass::moreTesting()<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
   </div>

  </div>
  </p>
  <p class="para">
   <div class="example" id="example-217">
    <p><strong>Example #2 Final class example</strong></p>
    <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">final&nbsp;class&nbsp;</span><span style="color: #0000BB">BaseClass&nbsp;</span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">test</span><span style="color: #007700">()&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"BaseClass::test()&nbsp;called\n"</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;}<br /><br />&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;Here&nbsp;it&nbsp;doesn't&nbsp;matter&nbsp;if&nbsp;you&nbsp;specify&nbsp;the&nbsp;function&nbsp;as&nbsp;final&nbsp;or&nbsp;not<br />&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">final&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">moreTesting</span><span style="color: #007700">()&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"BaseClass::moreTesting()&nbsp;called\n"</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;}<br />}<br /><br />class&nbsp;</span><span style="color: #0000BB">ChildClass&nbsp;</span><span style="color: #007700">extends&nbsp;</span><span style="color: #0000BB">BaseClass&nbsp;</span><span style="color: #007700">{<br />}<br /></span><span style="color: #FF8000">//&nbsp;Results&nbsp;in&nbsp;Fatal&nbsp;error:&nbsp;Class&nbsp;ChildClass&nbsp;may&nbsp;not&nbsp;inherit&nbsp;from&nbsp;final&nbsp;class&nbsp;(BaseClass)<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

   </div>
  </p>
  <blockquote class="note"><p><strong class="note">Note</strong>: 
   <span class="simpara">
    Properties cannot be declared final, only classes and methods 
    may be declared as final.
   </span>
  </p></blockquote>
 </div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="language.oop5.cloning.php">Object Cloning<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.oop5.magic.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Magic Methods</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.oop5.final.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=language.oop5.final&amp;redirect=http://www.php.net/manual/en/language.oop5.final.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.oop5.final&amp;redirect=http://www.php.net/manual/en/language.oop5.final.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Final Keyword</strong>
 </div><div id="allnotes">
 <a name="100149"></a>
 <div class="note">
  <strong class='user'>Anonymous</strong>
  <a href="#100149" class="date">28-Sep-2010 07:06</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The behaviour of FINAL is not as serious as you may think. A little explample:<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">A </span><span class="keyword">{<br />
&nbsp;&nbsp; &nbsp; final private function </span><span class="default">method</span><span class="keyword">(){}&nbsp; &nbsp;&nbsp; <br />
}<br />
<br />
class </span><span class="default">B </span><span class="keyword">extends </span><span class="default">A </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; private function </span><span class="default">method</span><span class="keyword">(){}<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
Normally you would expect some of the following will happen:<br />
- An error that final and private keyword cannot be used together<br />
- No error as the private visibility says, that a method/var/etc. is only visible within the same class<br />
<br />
But what happens is PHP is a little curios: "Cannot override final method A::method()"<br />
<br />
So its possible to deny method names in subclasses! Don't know if this is&nbsp; a good behavior, but maybe its useful for your purpose.</span>
</code></div>
  </div>
 </div>
 <a name="99705"></a>
 <div class="note">
  <strong class='user'>Baldurien</strong>
  <a href="#99705" class="date">31-Aug-2010 01:37</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
"Note for Java developers: the 'final' keyword is not used for class constants in PHP. We use the keyword 'const'."<br />
<br />
<a href="http://php.net/manual/en/language.oop5.constants.php" rel="nofollow" target="_blank">http://php.net/manual/en/language.oop5.constants.php</a><br />
<br />
This is more or less true, regardless of the fact that constant (being defined at class level or not) in PHP are only scalar (int, string, etc) while in Java they may be pure object (ex: java.awat.Color.BLACK). The only possible solution of having such kind of constant is :<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">Bar </span><span class="keyword">{...}<br />
class </span><span class="default">Foo </span><span class="keyword">{<br />
&nbsp; public static </span><span class="default">$FOOBAR</span><span class="keyword">;<br />
<br />
&nbsp; static function </span><span class="default">__init</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; static </span><span class="default">$init </span><span class="keyword">= </span><span class="default">false</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; if (</span><span class="default">$init</span><span class="keyword">) throw new </span><span class="default">Exception</span><span class="keyword">(</span><span class="string">'Constants were already initialized'</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">self</span><span class="keyword">::</span><span class="default">$FOOBAR </span><span class="keyword">= new </span><span class="default">Bar</span><span class="keyword">();<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$init </span><span class="keyword">= </span><span class="default">true</span><span class="keyword">;<br />
&nbsp; }<br />
}<br />
</span><span class="default">Foo</span><span class="keyword">::</span><span class="default">__init</span><span class="keyword">();<br />
</span><span class="default">?&gt;<br />
</span>That said, perhaps it is useless unless PHP automatically calls the __init() method.<br />
<br />
However, one alternative that could be done in certain case is this : <br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">__autoload</span><span class="keyword">(</span><span class="default">$className</span><span class="keyword">) { <br />
&nbsp; ... require </span><span class="default">the file where the </span><span class="keyword">class </span><span class="default">is </span><span class="keyword">...<br />
&nbsp; if (</span><span class="default">interface_exists</span><span class="keyword">(</span><span class="default">$className</span><span class="keyword">, </span><span class="default">false</span><span class="keyword">)) return;<br />
&nbsp; if (</span><span class="default">class_exists</span><span class="keyword">(</span><span class="default">$className</span><span class="keyword">, </span><span class="default">false</span><span class="keyword">)) {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$rc </span><span class="keyword">= new </span><span class="default">ReflectionClass</span><span class="keyword">(</span><span class="default">$className</span><span class="keyword">); <br />
&nbsp;&nbsp;&nbsp; if (!</span><span class="default">$rc</span><span class="keyword">-&gt;</span><span class="default">hasMethod</span><span class="keyword">(</span><span class="string">'__init'</span><span class="keyword">)) return;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$m </span><span class="keyword">= </span><span class="default">$rc</span><span class="keyword">-&gt;</span><span class="default">getMethod</span><span class="keyword">(</span><span class="string">'__init'</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; if (!(</span><span class="default">$m</span><span class="keyword">-&gt;</span><span class="default">isStatic</span><span class="keyword">() &amp;&amp; </span><span class="default">$m</span><span class="keyword">-&gt;</span><span class="default">isPrivate</span><span class="keyword">())) {<br />
&nbsp;&nbsp; &nbsp;&nbsp; throw new </span><span class="default">Exception</span><span class="keyword">(</span><span class="default">$className </span><span class="keyword">. </span><span class="string">' __init() method must be private and static !'</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$m</span><span class="keyword">-&gt;</span><span class="default">invoke</span><span class="keyword">(</span><span class="default">null</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; return;<br />
&nbsp; }<br />
&nbsp; throw new </span><span class="default">Exception</span><span class="keyword">(</span><span class="string">'Class or interface not found ' </span><span class="keyword">. </span><span class="default">$className</span><span class="keyword">);<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
This can only work when one class is defined per file, since we are assured that __autoload() will be called to load the file containing the class.<br />
<br />
eg: <br />
<br />
test2.php:<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">B </span><span class="keyword">{<br />
&nbsp;public static </span><span class="default">$X</span><span class="keyword">;<br />
&nbsp;private static function </span><span class="default">__init</span><span class="keyword">() {<br />
&nbsp;&nbsp; echo </span><span class="string">'B'</span><span class="keyword">, </span><span class="string">"\n"</span><span class="keyword">; <br />
&nbsp;&nbsp; </span><span class="default">self</span><span class="keyword">::</span><span class="default">$X </span><span class="keyword">= array(</span><span class="default">1</span><span class="keyword">, </span><span class="default">2</span><span class="keyword">);<br />
&nbsp;}<br />
}<br />
class </span><span class="default">A </span><span class="keyword">{<br />
&nbsp; public static </span><span class="default">$Y</span><span class="keyword">;<br />
&nbsp; private static function </span><span class="default">__init</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="string">'A'</span><span class="keyword">, </span><span class="string">"\n"</span><span class="keyword">; <br />
&nbsp;&nbsp;&nbsp; </span><span class="default">self</span><span class="keyword">::</span><span class="default">$Y </span><span class="keyword">= array(</span><span class="default">3</span><span class="keyword">, </span><span class="default">4</span><span class="keyword">);<br />
&nbsp; }<br />
}<br />
</span><span class="default">?&gt;<br />
</span>test.php:<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">__autoload</span><span class="keyword">(</span><span class="default">$n</span><span class="keyword">) {<br />
&nbsp; if (</span><span class="default">$n </span><span class="keyword">== </span><span class="string">'A' </span><span class="keyword">|| </span><span class="default">$n </span><span class="keyword">== </span><span class="string">'B'</span><span class="keyword">) require </span><span class="string">'test2.php'</span><span class="keyword">;<br />
&nbsp; ... do </span><span class="default">our __init</span><span class="keyword">() </span><span class="default">trick </span><span class="keyword">...<br />
}<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">B</span><span class="keyword">::</span><span class="default">$X</span><span class="keyword">); </span><span class="comment">// shows B, then array(2) (1, 2)<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">A</span><span class="keyword">::</span><span class="default">$Y</span><span class="keyword">); </span><span class="comment">// shows NULL.<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="92628"></a>
 <div class="note">
  <strong class='user'>Kiam</strong>
  <a href="#92628" class="date">31-Jul-2009 04:49</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The final keyword doesn't change the visibility of a property / method, which is public by default.</span>
</code></div>
  </div>
 </div>
 <a name="92295"></a>
 <div class="note">
  <strong class='user'>jriddy at gmail dot com</strong>
  <a href="#92295" class="date">17-Jul-2009 02:20</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Note for Java developers: the 'final' keyword is not used for class constants in PHP. We use the keyword 'const'.<br />
<br />
<a href="http://php.net/manual/en/language.oop5.constants.php" rel="nofollow" target="_blank">http://php.net/manual/en/language.oop5.constants.php</a></span>
</code></div>
  </div>
 </div>
 <a name="89678"></a>
 <div class="note">
  <strong class='user'>sunil dot boodram at gmail dot com</strong>
  <a href="#89678" class="date">18-Mar-2009 09:54</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Using the scope resolution operator, we can easily access constants, static variables, public functions and final functions from Final Classes<br />
<br />
Note: even by omitting the "public" keyword from the "nightNews()" method, we can see that the function defaults to public even if prefixed with the keyword final.<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">final class </span><span class="default">news</span><span class="keyword">{<br />
&nbsp; <br />
&nbsp; const </span><span class="default">micCheck </span><span class="keyword">= </span><span class="string">"test1"</span><span class="keyword">; </span><span class="comment">// will work<br />
&nbsp; </span><span class="keyword">static public </span><span class="default">$promptCheck </span><span class="keyword">= </span><span class="string">"test2"</span><span class="keyword">; </span><span class="comment">//will work<br />
&nbsp; <br />
&nbsp; //will work<br />
&nbsp; </span><span class="keyword">public function </span><span class="default">morningNews</span><span class="keyword">(){<br />
&nbsp;&nbsp;&nbsp; print </span><span class="string">'good morning world!'</span><span class="keyword">;<br />
&nbsp; }<br />
<br />
&nbsp; </span><span class="comment">//will work<br />
&nbsp; </span><span class="keyword">final public function </span><span class="default">eveningNews</span><span class="keyword">(){<br />
&nbsp;&nbsp;&nbsp; print </span><span class="string">'good evening world!'</span><span class="keyword">;<br />
&nbsp; }<br />
<br />
&nbsp; </span><span class="comment">//will work<br />
&nbsp; </span><span class="keyword">final function </span><span class="default">nightNews</span><span class="keyword">(){<br />
&nbsp;&nbsp;&nbsp; print </span><span class="string">'good night world!'</span><span class="keyword">;<br />
&nbsp; }<br />
<br />
}<br />
<br />
print </span><span class="default">news</span><span class="keyword">::</span><span class="default">micCheck</span><span class="keyword">; </span><span class="comment">// output: test1<br />
</span><span class="keyword">print </span><span class="string">'&lt;br/&gt;'</span><span class="keyword">;<br />
print </span><span class="default">news</span><span class="keyword">::</span><span class="default">$promptCheck</span><span class="keyword">; </span><span class="comment">// output: test2<br />
</span><span class="keyword">print </span><span class="string">'&lt;br/&gt;'</span><span class="keyword">;<br />
print </span><span class="default">news</span><span class="keyword">::</span><span class="default">morningNews</span><span class="keyword">(); </span><span class="comment">// output: good morning world!<br />
</span><span class="keyword">print </span><span class="string">'&lt;br/&gt;'</span><span class="keyword">;<br />
print </span><span class="default">news</span><span class="keyword">::</span><span class="default">eveningNews</span><span class="keyword">(); </span><span class="comment">// output: good evening world!<br />
</span><span class="keyword">print </span><span class="string">'&lt;br/&gt;'</span><span class="keyword">;<br />
print </span><span class="default">news</span><span class="keyword">::</span><span class="default">nightNews</span><span class="keyword">(); </span><span class="comment">// output: good night world!<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="87444"></a>
 <div class="note">
  <strong class='user'>santoshjoshi2003 at yahoo dot co dot in</strong>
  <a href="#87444" class="date">05-Dec-2008 02:45</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The use of final keyword is just like that occurs in Java<br />
In java final has three uses<br />
&nbsp;&nbsp;&nbsp; 1) prevent class Inheritance<br />
&nbsp;&nbsp;&nbsp; 2) prevent method overriding or redifination of <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; method in subclass <br />
&nbsp;&nbsp;&nbsp; 3) and to declare constants <br />
&nbsp;But the third point seems to be missing from the PHP<br />
&nbsp;I guess, as i am a java developer Currently gaining competence in PHP</span>
</code></div>
  </div>
 </div>
 <a name="78862"></a>
 <div class="note">
  <strong class='user'>slorenzo at clug dot org dot ve</strong>
  <a href="#78862" class="date">30-Oct-2007 11:13</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">parentClass </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">someMethod</span><span class="keyword">() { }<br />
}<br />
class </span><span class="default">childClass </span><span class="keyword">extends </span><span class="default">parentClass </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public final function </span><span class="default">someMethod</span><span class="keyword">() { } </span><span class="comment">//override parent function<br />
</span><span class="keyword">}<br />
<br />
</span><span class="default">$class </span><span class="keyword">= new </span><span class="default">childClass</span><span class="keyword">;<br />
</span><span class="default">$class</span><span class="keyword">-&gt;</span><span class="default">someMethod</span><span class="keyword">(); </span><span class="comment">//call the override function in chield class<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="74058"></a>
 <div class="note">
  <strong class='user'>penartur at yandex dot ru</strong>
  <a href="#74058" class="date">22-Mar-2007 01:39</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Note that you cannot ovverride final methods even if they are defined as private in parent class.<br />
Thus, the following example:<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">parentClass </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; final private function </span><span class="default">someMethod</span><span class="keyword">() { }<br />
}<br />
class </span><span class="default">childClass </span><span class="keyword">extends </span><span class="default">parentClass </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; private function </span><span class="default">someMethod</span><span class="keyword">() { }<br />
}<br />
</span><span class="default">?&gt;<br />
</span>dies with error "Fatal error: Cannot override final method parentClass::someMethod() in ***.php on line 7"<br />
<br />
Such behaviour looks slight unexpected because in child class we cannot know, which private methods exists in a parent class and vice versa.<br />
<br />
So, remember that if you defined a private final method, you cannot place method with the same name in child class.</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=language.oop5.final&amp;redirect=http://www.php.net/manual/en/language.oop5.final.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.oop5.final&amp;redirect=http://www.php.net/manual/en/language.oop5.final.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/language.oop5.final.php">show source</a> |
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