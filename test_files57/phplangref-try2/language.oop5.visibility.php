<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Visibility - Manual</title>
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
 <link rel="prev" href="language.oop5.decon.php" />
 <link rel="next" href="language.oop5.inheritance.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/oop5.visibility" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/language.oop5.visibility.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/language.oop5.visibility.php" />
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
 <li class="active"><a href="language.oop5.visibility.php">Visibility</a></li>
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
  <a href="language.oop5.inheritance.php">Object Inheritance<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.oop5.decon.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Constructors and Destructors</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.oop5.visibility.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/language.oop5.visibility.php">Brazilian Portuguese</option>
    <option value="zh/language.oop5.visibility.php">Chinese (Simplified)</option>
    <option value="fr/language.oop5.visibility.php">French</option>
    <option value="de/language.oop5.visibility.php">German</option>
    <option value="ja/language.oop5.visibility.php">Japanese</option>
    <option value="pl/language.oop5.visibility.php">Polish</option>
    <option value="ro/language.oop5.visibility.php">Romanian</option>
    <option value="ru/language.oop5.visibility.php">Russian</option>
    <option value="fa/language.oop5.visibility.php">Persian</option>
    <option value="es/language.oop5.visibility.php">Spanish</option>
    <option value="tr/language.oop5.visibility.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="language.oop5.visibility" class="sect1">
  <h2 class="title">Visibility</h2>
  <p class="para">
   The visibility of a property or method can be defined by prefixing
   the declaration with the keywords <em class="emphasis">public</em>,
   <em class="emphasis">protected</em> or
   <em class="emphasis">private</em>. Class members declared public can be
   accessed everywhere. Members declared protected can be accessed
   only within the class itself and by inherited and parent
   classes. Members declared as private may only be accessed by the
   class that defines the member.
  </p>

  <div class="sect2" id="language.oop5.visibility-members">
   <h3 class="title">Property Visibility</h3>
   <p class="para">
    Class properties must be defined as public, private, or
    protected. If declared using <em class="emphasis">var</em>,
    the property will be defined as public.
   </p>
   <p class="para">
    <div class="example" id="example-181">
     <p><strong>Example #1 Property declaration</strong></p>
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #FF8000">/**<br />&nbsp;*&nbsp;Define&nbsp;MyClass<br />&nbsp;*/<br /></span><span style="color: #007700">class&nbsp;</span><span style="color: #0000BB">MyClass<br /></span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;</span><span style="color: #0000BB">$public&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'Public'</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;protected&nbsp;</span><span style="color: #0000BB">$protected&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'Protected'</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;private&nbsp;</span><span style="color: #0000BB">$private&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'Private'</span><span style="color: #007700">;<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;function&nbsp;</span><span style="color: #0000BB">printHello</span><span style="color: #007700">()<br />&nbsp;&nbsp;&nbsp;&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">public</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">protected</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">private</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br /><br /></span><span style="color: #0000BB">$obj&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">MyClass</span><span style="color: #007700">();<br />echo&nbsp;</span><span style="color: #0000BB">$obj</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">public</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;Works<br /></span><span style="color: #007700">echo&nbsp;</span><span style="color: #0000BB">$obj</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">protected</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;Fatal&nbsp;Error<br /></span><span style="color: #007700">echo&nbsp;</span><span style="color: #0000BB">$obj</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">private</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;Fatal&nbsp;Error<br /></span><span style="color: #0000BB">$obj</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">printHello</span><span style="color: #007700">();&nbsp;</span><span style="color: #FF8000">//&nbsp;Shows&nbsp;Public,&nbsp;Protected&nbsp;and&nbsp;Private<br /><br /><br />/**<br />&nbsp;*&nbsp;Define&nbsp;MyClass2<br />&nbsp;*/<br /></span><span style="color: #007700">class&nbsp;</span><span style="color: #0000BB">MyClass2&nbsp;</span><span style="color: #007700">extends&nbsp;</span><span style="color: #0000BB">MyClass<br /></span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;We&nbsp;can&nbsp;redeclare&nbsp;the&nbsp;public&nbsp;and&nbsp;protected&nbsp;method,&nbsp;but&nbsp;not&nbsp;private<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">protected&nbsp;</span><span style="color: #0000BB">$protected&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'Protected2'</span><span style="color: #007700">;<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;function&nbsp;</span><span style="color: #0000BB">printHello</span><span style="color: #007700">()<br />&nbsp;&nbsp;&nbsp;&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">public</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">protected</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">private</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br /><br /></span><span style="color: #0000BB">$obj2&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">MyClass2</span><span style="color: #007700">();<br />echo&nbsp;</span><span style="color: #0000BB">$obj2</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">public</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;Works<br /></span><span style="color: #007700">echo&nbsp;</span><span style="color: #0000BB">$obj2</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">private</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;Undefined<br /></span><span style="color: #007700">echo&nbsp;</span><span style="color: #0000BB">$obj2</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">protected</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;Fatal&nbsp;Error<br /></span><span style="color: #0000BB">$obj2</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">printHello</span><span style="color: #007700">();&nbsp;</span><span style="color: #FF8000">//&nbsp;Shows&nbsp;Public,&nbsp;Protected2,&nbsp;Undefined<br /><br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

    </div>
   </p>
   <blockquote class="note"><p><strong class="note">Note</strong>: 
    <span class="simpara">
     The PHP 4 method of declaring a variable with the
     <em class="emphasis">var</em> keyword is still supported for compatibility
     reasons (as a synonym for the public keyword). In PHP 5 before 5.1.3, its
     usage would generate an <strong><code>E_STRICT</code></strong> warning.
    </span>
   </p></blockquote>
  </div>

  <div class="sect2" id="language.oop5.visiblity-methods">
   <h3 class="title">Method Visibility</h3>
   <p class="para">
    Class methods may be defined as public, private, or
    protected. Methods declared without any explicit visibility
    keyword are defined as public.
   </p>
   <p class="para">
    <div class="example" id="example-182">
     <p><strong>Example #2 Method Declaration</strong></p>
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #FF8000">/**<br />&nbsp;*&nbsp;Define&nbsp;MyClass<br />&nbsp;*/<br /></span><span style="color: #007700">class&nbsp;</span><span style="color: #0000BB">MyClass<br /></span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;Declare&nbsp;a&nbsp;public&nbsp;constructor<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">public&nbsp;function&nbsp;</span><span style="color: #0000BB">__construct</span><span style="color: #007700">()&nbsp;{&nbsp;}<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;Declare&nbsp;a&nbsp;public&nbsp;method<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">public&nbsp;function&nbsp;</span><span style="color: #0000BB">MyPublic</span><span style="color: #007700">()&nbsp;{&nbsp;}<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;Declare&nbsp;a&nbsp;protected&nbsp;method<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">protected&nbsp;function&nbsp;</span><span style="color: #0000BB">MyProtected</span><span style="color: #007700">()&nbsp;{&nbsp;}<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;Declare&nbsp;a&nbsp;private&nbsp;method<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">private&nbsp;function&nbsp;</span><span style="color: #0000BB">MyPrivate</span><span style="color: #007700">()&nbsp;{&nbsp;}<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;This&nbsp;is&nbsp;public<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">function&nbsp;</span><span style="color: #0000BB">Foo</span><span style="color: #007700">()<br />&nbsp;&nbsp;&nbsp;&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">MyPublic</span><span style="color: #007700">();<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">MyProtected</span><span style="color: #007700">();<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">MyPrivate</span><span style="color: #007700">();<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br /><br /></span><span style="color: #0000BB">$myclass&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">MyClass</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">$myclass</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">MyPublic</span><span style="color: #007700">();&nbsp;</span><span style="color: #FF8000">//&nbsp;Works<br /></span><span style="color: #0000BB">$myclass</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">MyProtected</span><span style="color: #007700">();&nbsp;</span><span style="color: #FF8000">//&nbsp;Fatal&nbsp;Error<br /></span><span style="color: #0000BB">$myclass</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">MyPrivate</span><span style="color: #007700">();&nbsp;</span><span style="color: #FF8000">//&nbsp;Fatal&nbsp;Error<br /></span><span style="color: #0000BB">$myclass</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">Foo</span><span style="color: #007700">();&nbsp;</span><span style="color: #FF8000">//&nbsp;Public,&nbsp;Protected&nbsp;and&nbsp;Private&nbsp;work<br /><br /><br />/**<br />&nbsp;*&nbsp;Define&nbsp;MyClass2<br />&nbsp;*/<br /></span><span style="color: #007700">class&nbsp;</span><span style="color: #0000BB">MyClass2&nbsp;</span><span style="color: #007700">extends&nbsp;</span><span style="color: #0000BB">MyClass<br /></span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;This&nbsp;is&nbsp;public<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">function&nbsp;</span><span style="color: #0000BB">Foo2</span><span style="color: #007700">()<br />&nbsp;&nbsp;&nbsp;&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">MyPublic</span><span style="color: #007700">();<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">MyProtected</span><span style="color: #007700">();<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">MyPrivate</span><span style="color: #007700">();&nbsp;</span><span style="color: #FF8000">//&nbsp;Fatal&nbsp;Error<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">}<br />}<br /><br /></span><span style="color: #0000BB">$myclass2&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">MyClass2</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">$myclass2</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">MyPublic</span><span style="color: #007700">();&nbsp;</span><span style="color: #FF8000">//&nbsp;Works<br /></span><span style="color: #0000BB">$myclass2</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">Foo2</span><span style="color: #007700">();&nbsp;</span><span style="color: #FF8000">//&nbsp;Public&nbsp;and&nbsp;Protected&nbsp;work,&nbsp;not&nbsp;Private<br /><br /></span><span style="color: #007700">class&nbsp;</span><span style="color: #0000BB">Bar&nbsp;<br /></span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">test</span><span style="color: #007700">()&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">testPrivate</span><span style="color: #007700">();<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">testPublic</span><span style="color: #007700">();<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">testPublic</span><span style="color: #007700">()&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"Bar::testPublic\n"</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />&nbsp;&nbsp;&nbsp;&nbsp;<br />&nbsp;&nbsp;&nbsp;&nbsp;private&nbsp;function&nbsp;</span><span style="color: #0000BB">testPrivate</span><span style="color: #007700">()&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"Bar::testPrivate\n"</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br /><br />class&nbsp;</span><span style="color: #0000BB">Foo&nbsp;</span><span style="color: #007700">extends&nbsp;</span><span style="color: #0000BB">Bar&nbsp;<br /></span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">testPublic</span><span style="color: #007700">()&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"Foo::testPublic\n"</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />&nbsp;&nbsp;&nbsp;&nbsp;<br />&nbsp;&nbsp;&nbsp;&nbsp;private&nbsp;function&nbsp;</span><span style="color: #0000BB">testPrivate</span><span style="color: #007700">()&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"Foo::testPrivate\n"</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br /><br /></span><span style="color: #0000BB">$myFoo&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">foo</span><span style="color: #007700">();<br /></span><span style="color: #0000BB">$myFoo</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">test</span><span style="color: #007700">();&nbsp;</span><span style="color: #FF8000">//&nbsp;Bar::testPrivate&nbsp;<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;//&nbsp;Foo::testPublic<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

    </div>
   </p>
  </div>

  <div class="sect2" id="language.oop5.visibility-other-objects">
   <h3 class="title">Visibility from other objects</h3>
   <p class="para">
    Objects of the same type will have access to each others private and
    protected members even though they are not the same instances. This is
    because the implementation specific details are already known when inside
    those objects.
   </p>
   <div class="example" id="example-183">
    <p><strong>Example #3 Accessing private members of the same object type</strong></p>
    <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">class&nbsp;</span><span style="color: #0000BB">Test<br /></span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;private&nbsp;</span><span style="color: #0000BB">$foo</span><span style="color: #007700">;<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">__construct</span><span style="color: #007700">(</span><span style="color: #0000BB">$foo</span><span style="color: #007700">)<br />&nbsp;&nbsp;&nbsp;&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">foo&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">$foo</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;private&nbsp;function&nbsp;</span><span style="color: #0000BB">bar</span><span style="color: #007700">()<br />&nbsp;&nbsp;&nbsp;&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">'Accessed&nbsp;the&nbsp;private&nbsp;method.'</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">baz</span><span style="color: #007700">(</span><span style="color: #0000BB">Test&nbsp;$other</span><span style="color: #007700">)<br />&nbsp;&nbsp;&nbsp;&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;We&nbsp;can&nbsp;change&nbsp;the&nbsp;private&nbsp;property:<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$other</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">foo&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'hello'</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">var_dump</span><span style="color: #007700">(</span><span style="color: #0000BB">$other</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">foo</span><span style="color: #007700">);<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;We&nbsp;can&nbsp;also&nbsp;call&nbsp;the&nbsp;private&nbsp;method:<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$other</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">bar</span><span style="color: #007700">();<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br /><br /></span><span style="color: #0000BB">$test&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">Test</span><span style="color: #007700">(</span><span style="color: #DD0000">'test'</span><span style="color: #007700">);<br /><br /></span><span style="color: #0000BB">$test</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">baz</span><span style="color: #007700">(new&nbsp;</span><span style="color: #0000BB">Test</span><span style="color: #007700">(</span><span style="color: #DD0000">'other'</span><span style="color: #007700">));<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

    <div class="example-contents"><p>The above example will output:</p></div>
    <div class="example-contents screen">
<div class="cdata"><pre>
string(5) &quot;hello&quot;
Accessed the private method.
</pre></div>
    </div>
   </div>
  </div>
 </div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="language.oop5.inheritance.php">Object Inheritance<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.oop5.decon.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Constructors and Destructors</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.oop5.visibility.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=language.oop5.visibility&amp;redirect=http://www.php.net/manual/en/language.oop5.visibility.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.oop5.visibility&amp;redirect=http://www.php.net/manual/en/language.oop5.visibility.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Visibility</strong>
 </div><div id="allnotes">
 <a name="109324"></a>
 <div class="note">
  <strong class='user'>omega at 2093 dot es</strong>
  <a href="#109324" class="date">06-Jul-2012 12:04</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
This has already been noted here, but there was no clear example. Methods defined in a parent class can NOT access private methods defined in a class which inherits from them. They can access protected, though.<br />
<br />
Example:<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">class </span><span class="default">ParentClass </span><span class="keyword">{<br />
<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">execute</span><span class="keyword">(</span><span class="default">$method</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">$method</span><span class="keyword">();<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
}<br />
<br />
class </span><span class="default">ChildClass </span><span class="keyword">extends </span><span class="default">ParentClass </span><span class="keyword">{<br />
<br />
&nbsp;&nbsp;&nbsp; private function </span><span class="default">privateMethod</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">"hi, i'm private"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; protected function </span><span class="default">protectedMethod</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">"hi, i'm protected"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
}<br />
<br />
</span><span class="default">$object </span><span class="keyword">= new </span><span class="default">ChildClass</span><span class="keyword">();<br />
<br />
</span><span class="default">$object</span><span class="keyword">-&gt;</span><span class="default">execute</span><span class="keyword">(</span><span class="string">'protectedMethod'</span><span class="keyword">);<br />
<br />
</span><span class="default">$object</span><span class="keyword">-&gt;</span><span class="default">execute</span><span class="keyword">(</span><span class="string">'privateMethod'</span><span class="keyword">);<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
Output:<br />
<br />
hi, i'm protected<br />
Fatal error: Call to private method ChildClass::privateMethod() from context 'ParentClass' in index.php on line 6<br />
<br />
In an early approach this may seem unwanted behaviour but it actually makes sense. Private can only be accessed by the class which defines, neither parent nor children classes.</span>
</code></div>
  </div>
 </div>
 <a name="109110"></a>
 <div class="note">
  <strong class='user'>jc dot flash at gmail dot com</strong>
  <a href="#109110" class="date">21-Jun-2012 02:31</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
if not overwritten, self::$foo in a subclass actually refers to parent's self::$foo <br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">one<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; protected static </span><span class="default">$foo </span><span class="keyword">= </span><span class="string">"bar"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">change_foo</span><span class="keyword">(</span><span class="default">$value</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">self</span><span class="keyword">::</span><span class="default">$foo </span><span class="keyword">= </span><span class="default">$value</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
class </span><span class="default">two </span><span class="keyword">extends </span><span class="default">one<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">tell_me</span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="default">self</span><span class="keyword">::</span><span class="default">$foo</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
</span><span class="default">$first </span><span class="keyword">= new </span><span class="default">one</span><span class="keyword">;<br />
</span><span class="default">$second </span><span class="keyword">= new </span><span class="default">two</span><span class="keyword">;<br />
<br />
</span><span class="default">$second</span><span class="keyword">-&gt;</span><span class="default">tell_me</span><span class="keyword">(); </span><span class="comment">// bar<br />
</span><span class="default">$first</span><span class="keyword">-&gt;</span><span class="default">change_foo</span><span class="keyword">(</span><span class="string">"restaurant"</span><span class="keyword">);<br />
</span><span class="default">$second</span><span class="keyword">-&gt;</span><span class="default">tell_me</span><span class="keyword">(); </span><span class="comment">// restaurant<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="108852"></a>
 <div class="note">
  <strong class='user'>IgelHaut</strong>
  <a href="#108852" class="date">30-May-2012 01:48</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">test </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public </span><span class="default">$public </span><span class="keyword">= </span><span class="string">'Public var'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; protected </span><span class="default">$protected </span><span class="keyword">= </span><span class="string">'protected var'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; private </span><span class="default">$private </span><span class="keyword">= </span><span class="string">'Private var'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; public static </span><span class="default">$static_public </span><span class="keyword">= </span><span class="string">'Public static var'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; protected static </span><span class="default">$static_protected </span><span class="keyword">= </span><span class="string">'protected static var'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; private static </span><span class="default">$static_private </span><span class="keyword">= </span><span class="string">'Private static var'</span><span class="keyword">;<br />
}<br />
<br />
</span><span class="default">$class </span><span class="keyword">= new </span><span class="default">test</span><span class="keyword">;<br />
</span><span class="default">print_r</span><span class="keyword">(</span><span class="default">$class</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span><br />
The code prints<br />
test Object ( [public] =&gt; Public var [protected:protected] =&gt; protected var [private:test:private] =&gt; Private var )<br />
<br />
Functions like print_r(), var_dump() and var_export() prints public, protected and private variables, but not the static variables.</span>
</code></div>
  </div>
 </div>
 <a name="108711"></a>
 <div class="note">
  <strong class='user'>wbcarts at juno dot com</strong>
  <a href="#108711" class="date">19-May-2012 08:27</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
INSIDE CODE and OUTSIDE CODE<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">class </span><span class="default">Item<br />
</span><span class="keyword">{<br />
&nbsp; </span><span class="comment">/**<br />
&nbsp;&nbsp; * This is INSIDE CODE because it is written INSIDE the class.<br />
&nbsp;&nbsp; */<br />
&nbsp; </span><span class="keyword">public </span><span class="default">$label</span><span class="keyword">;<br />
&nbsp; public </span><span class="default">$price</span><span class="keyword">;<br />
}<br />
<br />
</span><span class="comment">/**<br />
&nbsp;* This is OUTSIDE CODE because it is written OUTSIDE the class.<br />
&nbsp;*/<br />
</span><span class="default">$item </span><span class="keyword">= new </span><span class="default">Item</span><span class="keyword">();<br />
</span><span class="default">$item</span><span class="keyword">-&gt;</span><span class="default">label </span><span class="keyword">= </span><span class="string">'Ink-Jet Tatoo Gun'</span><span class="keyword">;<br />
</span><span class="default">$item</span><span class="keyword">-&gt;</span><span class="default">price </span><span class="keyword">= </span><span class="default">49.99</span><span class="keyword">;<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
Ok, that's simple enough... I got it inside and out. The big problem with this is that the Item class is COMPLETELY IGNORANT in the following ways:<br />
* It REQUIRES OUTSIDE CODE to do all the work AND to know what and how to do it -- huge mistake.<br />
* OUTSIDE CODE can cast Item properties to any other PHP types (booleans, integers, floats, strings, arrays, and objects etc.) -- another huge mistake.<br />
<br />
Note: we did it correctly above, but what if someone made an array for $price? FYI: PHP has no clue what we mean by an Item, especially by the terms of our class definition above. To PHP, our Item is something with two properties (mutable in every way) and that's it. As far as PHP is concerned, we can pack the entire set of Britannica Encyclopedias into the price slot. When that happens, we no longer have what we expect an Item to be.<br />
<br />
INSIDE CODE should keep the integrity of the object. For example, our class definition should keep $label a string and $price a float -- which means only strings can come IN and OUT of the class for label, and only floats can come IN and OUT of the class for price.<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">class </span><span class="default">Item<br />
</span><span class="keyword">{<br />
&nbsp; </span><span class="comment">/**<br />
&nbsp;&nbsp; * Here's the new INSIDE CODE and the Rules to follow:<br />
&nbsp;&nbsp; *<br />
&nbsp;&nbsp; * 1. STOP ACCESS to properties via $item-&gt;label and $item-&gt;price,<br />
&nbsp;&nbsp; *&nbsp; &nbsp; by using the protected keyword.<br />
&nbsp;&nbsp; * 2. FORCE the use of public functions.<br />
&nbsp;&nbsp; * 3. ONLY strings are allowed IN &amp; OUT of this class for $label<br />
&nbsp;&nbsp; *&nbsp; &nbsp; via the getLabel and setLabel functions.<br />
&nbsp;&nbsp; * 4. ONLY floats are allowed IN &amp; OUT of this class for $price<br />
&nbsp;&nbsp; *&nbsp; &nbsp; via the getPrice and setPrice functions.<br />
&nbsp;&nbsp; */<br />
<br />
&nbsp; </span><span class="keyword">protected </span><span class="default">$label </span><span class="keyword">= </span><span class="string">'Unknown Item'</span><span class="keyword">; </span><span class="comment">// Rule 1 - protected.<br />
&nbsp; </span><span class="keyword">protected </span><span class="default">$price </span><span class="keyword">= </span><span class="default">0.0</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="comment">// Rule 1 - protected.<br />
<br />
&nbsp; </span><span class="keyword">public function </span><span class="default">getLabel</span><span class="keyword">() {&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// Rule 2 - public function.<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">return </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">label</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// Rule 3 - string OUT for $label.<br />
&nbsp; </span><span class="keyword">}<br />
<br />
&nbsp; public function </span><span class="default">getPrice</span><span class="keyword">() {&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// Rule 2 - public function.&nbsp; &nbsp; <br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">return </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">price</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// Rule 4 - float OUT for $price.<br />
&nbsp; </span><span class="keyword">}<br />
<br />
&nbsp; public function </span><span class="default">setLabel</span><span class="keyword">(</span><span class="default">$label</span><span class="keyword">)&nbsp;&nbsp; </span><span class="comment">// Rule 2 - public function.<br />
&nbsp; </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">/**<br />
&nbsp;&nbsp; &nbsp; * Make sure $label is a PHP string that can be used in a SORTING<br />
&nbsp;&nbsp; &nbsp; * alogorithm, NOT a boolean, number, array, or object that can't<br />
&nbsp;&nbsp; &nbsp; * properly sort -- AND to make sure that the getLabel() function<br />
&nbsp;&nbsp; &nbsp; * ALWAYS returns a genuine PHP string.<br />
&nbsp;&nbsp; &nbsp; *<br />
&nbsp;&nbsp; &nbsp; * Using a RegExp would improve this function, however, the main<br />
&nbsp;&nbsp; &nbsp; * point is the one made above.<br />
&nbsp;&nbsp; &nbsp; */<br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">if(</span><span class="default">is_string</span><span class="keyword">(</span><span class="default">$label</span><span class="keyword">))<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">label </span><span class="keyword">= (string)</span><span class="default">$label</span><span class="keyword">; </span><span class="comment">// Rule 3 - string IN for $label.<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">}<br />
&nbsp; }<br />
<br />
&nbsp; public function </span><span class="default">setPrice</span><span class="keyword">(</span><span class="default">$price</span><span class="keyword">)&nbsp;&nbsp; </span><span class="comment">// Rule 2 - public function.<br />
&nbsp; </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">/**<br />
&nbsp;&nbsp; &nbsp; * Make sure $price is a PHP float so that it can be used in a<br />
&nbsp;&nbsp; &nbsp; * NUMERICAL CALCULATION. Do not accept boolean, string, array or<br />
&nbsp;&nbsp; &nbsp; * some other object that can't be included in a simple calculation.<br />
&nbsp;&nbsp; &nbsp; * This will ensure that the getPrice() function ALWAYS returns an<br />
&nbsp;&nbsp; &nbsp; * authentic, genuine, full-flavored PHP number and nothing but.<br />
&nbsp;&nbsp; &nbsp; *<br />
&nbsp;&nbsp; &nbsp; * Checking for positive values may improve this function,<br />
&nbsp;&nbsp; &nbsp; * however, the main point is the one made above.<br />
&nbsp;&nbsp; &nbsp; */<br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">if(</span><span class="default">is_numeric</span><span class="keyword">(</span><span class="default">$price</span><span class="keyword">))<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">price </span><span class="keyword">= (float)</span><span class="default">$price</span><span class="keyword">; </span><span class="comment">// Rule 4 - float IN for $price.<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">}<br />
&nbsp; }<br />
}<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
Now there is nothing OUTSIDE CODE can do to obscure the INSIDES of an Item. In other words, every instance of Item will always look and behave like any other Item complete with a label and a price, AND you can group them together and they will interact without disruption. Even though there is room for improvement, the basics are there, and PHP will not hassle you... which means you can keep your hair!</span>
</code></div>
  </div>
 </div>
 <a name="106460"></a>
 <div class="note">
  <strong class='user'>briank at kappacs dot com</strong>
  <a href="#106460" class="date">10-Nov-2011 12:20</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
To make (some) object members read-only outside of the class (revisited using PHP 5 magic __get):<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">class </span><span class="default">ReadOnlyMembers </span><span class="keyword">{<br />
<br />
&nbsp;&nbsp;&nbsp; private </span><span class="default">$reallyPrivate</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; private </span><span class="default">$justReadOnly</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">__construct </span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">reallyPrivate </span><span class="keyword">= </span><span class="string">'secret'</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">justReadOnly </span><span class="keyword">= </span><span class="string">'read only'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">__get </span><span class="keyword">(</span><span class="default">$what</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; switch (</span><span class="default">$what</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; case </span><span class="string">'justReadOnly'</span><span class="keyword">:<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">$what</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; default:<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment"># Generate an error, throw an exception, or ...<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">return </span><span class="default">null</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">__isset </span><span class="keyword">(</span><span class="default">$what</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$val </span><span class="keyword">= </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">__get</span><span class="keyword">(</span><span class="default">$what</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return isset(</span><span class="default">$val</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
}<br />
<br />
</span><span class="default">$rom </span><span class="keyword">= new </span><span class="default">ReadOnlyMembers</span><span class="keyword">();<br />
<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$rom</span><span class="keyword">-&gt;</span><span class="default">justReadOnly</span><span class="keyword">);&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// string(9) "read only"<br />
<br />
</span><span class="default">$rom</span><span class="keyword">-&gt;</span><span class="default">justReadOnly </span><span class="keyword">= </span><span class="string">'new value'</span><span class="keyword">;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// Fatal error<br />
<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$rom</span><span class="keyword">-&gt;</span><span class="default">reallyPrivate</span><span class="keyword">);&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="comment">// Fatal error<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="104843"></a>
 <div class="note">
  <strong class='user'>php at stage-slash-solutions dot com</strong>
  <a href="#104843" class="date">10-Jul-2011 03:21</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
access a protected property:<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="comment">//Some library I am not allowed to change:<br />
<br />
</span><span class="keyword">abstract class </span><span class="default">a<br />
</span><span class="keyword">{<br />
&nbsp; protected </span><span class="default">$foo</span><span class="keyword">;<br />
}<br />
<br />
class </span><span class="default">aa </span><span class="keyword">extends </span><span class="default">a<br />
</span><span class="keyword">{<br />
&nbsp; function </span><span class="default">setFoo</span><span class="keyword">(</span><span class="default">$afoo</span><span class="keyword">)<br />
&nbsp; {<br />
&nbsp;&nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">foo </span><span class="keyword">= </span><span class="default">$afoo</span><span class="keyword">;<br />
&nbsp; }<br />
}<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
if you get an instance of aa and need access to $foo:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">b </span><span class="keyword">extends </span><span class="default">a<br />
</span><span class="keyword">{<br />
&nbsp; function </span><span class="default">getFoo</span><span class="keyword">(</span><span class="default">$ainstance</span><span class="keyword">)<br />
&nbsp; {<br />
&nbsp;&nbsp; &nbsp;&nbsp; return </span><span class="default">$ainstance</span><span class="keyword">-&gt;</span><span class="default">foo</span><span class="keyword">;<br />
&nbsp; }<br />
}<br />
<br />
</span><span class="default">$aainstance</span><span class="keyword">=</span><span class="default">someexternalfunction</span><span class="keyword">();<br />
</span><span class="default">$binstance</span><span class="keyword">=new </span><span class="default">b</span><span class="keyword">;<br />
</span><span class="default">$aafoo</span><span class="keyword">=</span><span class="default">$binstance</span><span class="keyword">-&gt;</span><span class="default">getFoo</span><span class="keyword">(</span><span class="default">$aainstance</span><span class="keyword">);<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="93743"></a>
 <div class="note">
  <strong class='user'>Marce!</strong>
  <a href="#93743" class="date">25-Sep-2009 03:04</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Please note that protected methods are also available from sibling classes as long as the method is declared in the common parent. This may also be an abstract method.<br />
&nbsp;<br />
In the below example Bar knows about the existence of _test() in Foo because they inherited this method from the same parent. It does not matter that it was abstract in the parent.<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">abstract class </span><span class="default">Base </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; abstract protected function </span><span class="default">_test</span><span class="keyword">();<br />
}<br />
&nbsp;<br />
class </span><span class="default">Bar </span><span class="keyword">extends </span><span class="default">Base </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; protected function </span><span class="default">_test</span><span class="keyword">() { }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">TestFoo</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$c </span><span class="keyword">= new </span><span class="default">Foo</span><span class="keyword">();<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$c</span><span class="keyword">-&gt;</span><span class="default">_test</span><span class="keyword">();<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
&nbsp;<br />
class </span><span class="default">Foo </span><span class="keyword">extends </span><span class="default">Base </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; protected function </span><span class="default">_test</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">'Foo'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
&nbsp;<br />
</span><span class="default">$bar </span><span class="keyword">= new </span><span class="default">Bar</span><span class="keyword">();<br />
</span><span class="default">$bar</span><span class="keyword">-&gt;</span><span class="default">TestFoo</span><span class="keyword">(); </span><span class="comment">// result: Foo<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="92995"></a>
 <div class="note">
  <strong class='user'>imran at phptrack dot com</strong>
  <a href="#92995" class="date">18-Aug-2009 02:06</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Some Method Overriding rules :<br />
<br />
1. In the overriding, the method names and arguments (arg’s) must be same.<br />
<br />
Example:<br />
class p { public function getName(){} }<br />
class c extends P{ public function getName(){} }<br />
<br />
2. final methods can’t be overridden.<br />
<br />
3. private methods never participate in the in the overriding because these methods are not visible in the child classes.<br />
<br />
Example:<br />
class a {<br />
private&nbsp; function my(){&nbsp; &nbsp; <br />
&nbsp;&nbsp;&nbsp; print "parent:my";<br />
}<br />
public function getmy(){<br />
$this-&gt;my();<br />
}<br />
}<br />
class b extends a{<br />
&nbsp;&nbsp;&nbsp; private&nbsp; function my(){<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; print "base:my";&nbsp; &nbsp; &nbsp; &nbsp; <br />
}<br />
}<br />
$x = new b();<br />
$x-&gt;getmy(); // parent:my<br />
<br />
4. While overriding decreasing access specifier is not allowed<br />
<br />
class a {<br />
public&nbsp; function my(){&nbsp; &nbsp; <br />
&nbsp;&nbsp;&nbsp; print "parent:my";<br />
}<br />
<br />
}<br />
class b extends a{<br />
&nbsp;&nbsp;&nbsp; private&nbsp; function my(){<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; print "base:my";&nbsp; &nbsp; &nbsp; &nbsp; <br />
}<br />
}<br />
//Fatal error:&nbsp; Access level to b::my() must be public (as in class a)</span>
</code></div>
  </div>
 </div>
 <a name="91850"></a>
 <div class="note">
  <strong class='user'>a dot schaffhirt at sedna-soft dot de</strong>
  <a href="#91850" class="date">29-Jun-2009 12:27</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you miss the "package" keyword in PHP in order to allow access between certain classes without their members being public, you can utilize the fact, that in PHP the protected keyword allows access to both subclasses and superclasses.<br />
<br />
So you can use this simple pattern:<br />
<br />
<span class="default">&lt;?php<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">abstract class </span><span class="default">Dispatcher </span><span class="keyword">{<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; protected function &amp;</span><span class="default">accessProperty </span><span class="keyword">(</span><span class="default">self $pObj</span><span class="keyword">, </span><span class="default">$pName</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$pObj</span><span class="keyword">-&gt;</span><span class="default">$pName</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; protected function </span><span class="default">invokeMethod </span><span class="keyword">(</span><span class="default">$pObj</span><span class="keyword">, </span><span class="default">$pName</span><span class="keyword">, </span><span class="default">$pArgs</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">call_user_func_array</span><span class="keyword">(array(</span><span class="default">$pObj</span><span class="keyword">, </span><span class="default">$pName</span><span class="keyword">), </span><span class="default">$pArgs</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; }<br />
</span><span class="default">?&gt;<br />
</span><br />
The classes that should be privileged to each other simply extend this dispatcher:<br />
<br />
<span class="default">&lt;?php<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">class </span><span class="default">Person </span><span class="keyword">extends </span><span class="default">Dispatcher </span><span class="keyword">{<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; private </span><span class="default">$name</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; protected </span><span class="default">$phoneNumbers</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; public function </span><span class="default">__construct </span><span class="keyword">(</span><span class="default">$pName</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">name </span><span class="keyword">= </span><span class="default">$pName</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">phoneNumbers </span><span class="keyword">= array();<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; public function </span><span class="default">addNumber </span><span class="keyword">(</span><span class="default">PhoneNumber $pNumber</span><span class="keyword">, </span><span class="default">$pLabel</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">phoneNumbers</span><span class="keyword">[</span><span class="default">$pLabel</span><span class="keyword">] = </span><span class="default">$pNumber</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// this does not work, because "owner" is protected:<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; // $pNumber-&gt;owner = $this;<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; // instead, we get a reference from the dispatcher:<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$p </span><span class="keyword">=&amp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">accessProperty</span><span class="keyword">(</span><span class="default">$pNumber</span><span class="keyword">, </span><span class="string">"owner"</span><span class="keyword">);<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// ... and change that:<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$p </span><span class="keyword">= </span><span class="default">$this</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; public function </span><span class="default">call </span><span class="keyword">(</span><span class="default">$pLabel</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// this does not work since "call" is protected:<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; // $this-&gt;phoneNumbers[$pLabel]-&gt;call();<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; // instead, we dispatch the call request:<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">invokeMethod</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">phoneNumbers</span><span class="keyword">[</span><span class="default">$pLabel</span><span class="keyword">], </span><span class="string">"call"</span><span class="keyword">, array());<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; class </span><span class="default">PhoneNumber </span><span class="keyword">extends </span><span class="default">Dispatcher </span><span class="keyword">{<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; private </span><span class="default">$countryCode</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; private </span><span class="default">$areaCode</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; private </span><span class="default">$number</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; protected </span><span class="default">$owner</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; public function </span><span class="default">__construct </span><span class="keyword">(</span><span class="default">$pCountryCode</span><span class="keyword">, </span><span class="default">$pAreaCode</span><span class="keyword">, </span><span class="default">$pNumber</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">countryCode </span><span class="keyword">= </span><span class="default">$pCountryCode</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">areaCode </span><span class="keyword">= </span><span class="default">$pAreaCode</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">number </span><span class="keyword">= </span><span class="default">$pNumber</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; protected function </span><span class="default">call </span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; echo(</span><span class="string">"calling " </span><span class="keyword">. </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">countryCode </span><span class="keyword">. </span><span class="string">"-" </span><span class="keyword">. </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">areaCode </span><span class="keyword">. </span><span class="string">"-" </span><span class="keyword">. </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">number </span><span class="keyword">. </span><span class="string">"\n"</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$person </span><span class="keyword">= new </span><span class="default">Person</span><span class="keyword">(</span><span class="string">"John Doe"</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$number1 </span><span class="keyword">= new </span><span class="default">PhoneNumber</span><span class="keyword">(</span><span class="default">12</span><span class="keyword">, </span><span class="default">345</span><span class="keyword">, </span><span class="default">67890</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$number2 </span><span class="keyword">= new </span><span class="default">PhoneNumber</span><span class="keyword">(</span><span class="default">34</span><span class="keyword">, </span><span class="default">5678</span><span class="keyword">, </span><span class="default">90123</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$person</span><span class="keyword">-&gt;</span><span class="default">addNumber</span><span class="keyword">(</span><span class="default">$number1</span><span class="keyword">, </span><span class="string">"home"</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$person</span><span class="keyword">-&gt;</span><span class="default">addNumber</span><span class="keyword">(</span><span class="default">$number2</span><span class="keyword">, </span><span class="string">"office"</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$person</span><span class="keyword">-&gt;</span><span class="default">call</span><span class="keyword">(</span><span class="string">"home"</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span><br />
Without this pattern you would have to make $owner and call() public in PhoneNumber.<br />
<br />
Best regards,</span>
</code></div>
  </div>
 </div>
 <a name="87413"></a>
 <div class="note">
  <strong class='user'>what at ever dot com</strong>
  <a href="#87413" class="date">04-Dec-2008 05:15</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you have problems with overriding private methods in extended classes, read this:)<br />
<br />
The manual says that "Private limits visibility only to the class that defines the item". That means extended children classes do not see the private methods of parent class and vice versa also. <br />
<br />
As a result, parents and children can have different implementations of the "same" private methods, depending on where you call them (e.g. parent or child class instance). Why? Because private methods are visible only for the class that defines them and the child class does not see the parent's private methods. If the child doesn't see the parent's private methods, the child can't override them. Scopes are different. In other words -- each class has a private set of private variables that no-one else has access to. <br />
<br />
A sample demonstrating the percularities of private methods when extending classes:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">abstract class </span><span class="default">base </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">inherited</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">overridden</span><span class="keyword">();<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; private function </span><span class="default">overridden</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">'base'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
class </span><span class="default">child </span><span class="keyword">extends </span><span class="default">base </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; private function </span><span class="default">overridden</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">'child'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="default">$test </span><span class="keyword">= new </span><span class="default">child</span><span class="keyword">();<br />
</span><span class="default">$test</span><span class="keyword">-&gt;</span><span class="default">inherited</span><span class="keyword">();<br />
</span><span class="default">?&gt;<br />
</span><br />
Output will be "base".<br />
<br />
If you want the inherited methods to use overridden functionality in extended classes but public sounds too loose, use protected. That's what it is for:)<br />
<br />
A sample that works as intended:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">abstract class </span><span class="default">base </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">inherited</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">overridden</span><span class="keyword">();<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; protected function </span><span class="default">overridden</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">'base'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
class </span><span class="default">child </span><span class="keyword">extends </span><span class="default">base </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; protected function </span><span class="default">overridden</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">'child'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="default">$test </span><span class="keyword">= new </span><span class="default">child</span><span class="keyword">();<br />
</span><span class="default">$test</span><span class="keyword">-&gt;</span><span class="default">inherited</span><span class="keyword">();<br />
</span><span class="default">?&gt;<br />
</span>Output will be "child".</span>
</code></div>
  </div>
 </div>
 <a name="86266"></a>
 <div class="note">
  <strong class='user'>Jeffrey</strong>
  <a href="#86266" class="date">09-Oct-2008 05:48</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
WHEN do I use public, protected or private keyword? Here's the default behavior.<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">Example<br />
</span><span class="keyword">{<br />
&nbsp; </span><span class="comment">/* use PUBLIC on variables and functions when:<br />
&nbsp;&nbsp; *&nbsp; 1. outside-code SHOULD access this property or function.<br />
&nbsp;&nbsp; *&nbsp; 2. extending classes SHOULD inherit this property or function.<br />
&nbsp;&nbsp; */&nbsp; <br />
&nbsp; </span><span class="keyword">public </span><span class="default">$var1</span><span class="keyword">;<br />
&nbsp; public function </span><span class="default">someFunction_1</span><span class="keyword">() { }<br />
<br />
&nbsp; </span><span class="comment">/* use PROTECTED on variables and functions when:<br />
&nbsp;&nbsp; *&nbsp; 1. outside-code SHOULD NOT access this property or function.<br />
&nbsp;&nbsp; *&nbsp; 2. extending classes SHOULD inherit this property or function.<br />
&nbsp;&nbsp; */<br />
&nbsp; </span><span class="keyword">protected </span><span class="default">$var2</span><span class="keyword">;<br />
&nbsp; protected function </span><span class="default">someFunction_2</span><span class="keyword">() { }<br />
<br />
&nbsp; </span><span class="comment">/* use PRIVATE on variables and functions when:<br />
&nbsp;&nbsp; *&nbsp; 1. outside-code SHOULD NOT access this property or function.<br />
&nbsp;&nbsp; *&nbsp; 2. extending classes SHOULD NOT inherit this property or function.<br />
&nbsp;&nbsp; */<br />
&nbsp; </span><span class="keyword">private </span><span class="default">$var3</span><span class="keyword">;<br />
&nbsp; private function </span><span class="default">someFunction_3</span><span class="keyword">() { }<br />
}<br />
<br />
</span><span class="comment"># these are the only valid calls outside-code can make on Example objects:<br />
&nbsp;</span><span class="default">$obj1 </span><span class="keyword">= new </span><span class="default">Example</span><span class="keyword">();&nbsp; &nbsp; &nbsp; </span><span class="comment">// instantiate<br />
&nbsp;</span><span class="default">$var1 </span><span class="keyword">= </span><span class="default">$obj1</span><span class="keyword">-&gt;</span><span class="default">var1</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp; </span><span class="comment">//&nbsp; get public data<br />
&nbsp;</span><span class="default">$obj1</span><span class="keyword">-&gt;</span><span class="default">var1 </span><span class="keyword">= </span><span class="default">35</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">//&nbsp; set public data<br />
&nbsp;</span><span class="default">$obj1</span><span class="keyword">-&gt;</span><span class="default">someFunction_1</span><span class="keyword">();&nbsp; &nbsp; </span><span class="comment">// call public function<br />
</span><span class="default">?&gt;<br />
</span><br />
Now try extending the class...<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">Example_2 </span><span class="keyword">extends </span><span class="default">Example<br />
</span><span class="keyword">{<br />
&nbsp; </span><span class="comment">// this class inherits the following properties and functions.<br />
&nbsp; </span><span class="keyword">public </span><span class="default">$var1</span><span class="keyword">;<br />
&nbsp; public function </span><span class="default">someFunction_1</span><span class="keyword">() { }<br />
&nbsp; protected </span><span class="default">$var2</span><span class="keyword">;<br />
&nbsp; protected function </span><span class="default">someFunction_2</span><span class="keyword">() { }<br />
}<br />
<br />
</span><span class="comment"># these are the only valid calls outside-code can make on Example_2 objects:<br />
&nbsp;</span><span class="default">$obj2 </span><span class="keyword">= new </span><span class="default">Example_2</span><span class="keyword">();&nbsp; </span><span class="comment">// instantiate<br />
&nbsp;</span><span class="default">$var2 </span><span class="keyword">= </span><span class="default">$obj2</span><span class="keyword">-&gt;</span><span class="default">var1</span><span class="keyword">;&nbsp; &nbsp; &nbsp; </span><span class="comment">//&nbsp; get public data<br />
&nbsp;</span><span class="default">$obj2</span><span class="keyword">-&gt;</span><span class="default">var1 </span><span class="keyword">= </span><span class="default">45</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">//&nbsp; set public data<br />
&nbsp;</span><span class="default">$obj2</span><span class="keyword">-&gt;</span><span class="default">someFunction_1</span><span class="keyword">();&nbsp; </span><span class="comment">// call public function<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="85656"></a>
 <div class="note">
  <strong class='user'>wbcarts at juno dot com</strong>
  <a href="#85656" class="date">10-Sep-2008 02:21</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
UNDERSTANDING PHP's OBJECT VISIBILITY<br />
<br />
Sometimes it's good to see a list of many extended classes, one right after the other - just for the sole purpose of looking at their inherited members. Maybe this will help:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">MyClass_1</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public </span><span class="default">$pubVal </span><span class="keyword">= </span><span class="string">'Hello World!'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; protected </span><span class="default">$proVal </span><span class="keyword">= </span><span class="string">'Hello FROM MyClass_1'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; private </span><span class="default">$priVal </span><span class="keyword">= </span><span class="string">'Hello TO MyClass_1'</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">__toString</span><span class="keyword">(){<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="string">"MyClass_1[public=$this-&gt;pubVal, protected=$this-&gt;proVal, private=$this-&gt;priVal]"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
class </span><span class="default">MyClass_2 </span><span class="keyword">extends </span><span class="default">MyClass_1</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">__toString</span><span class="keyword">(){<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="string">"MyClass_2[public=$this-&gt;pubVal, protected=$this-&gt;proVal, private=$this-&gt;priVal]"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
class </span><span class="default">MyClass_3 </span><span class="keyword">extends </span><span class="default">MyClass_2</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; private </span><span class="default">$priVal </span><span class="keyword">= </span><span class="string">'Hello TO MyClass_3'</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">__toString</span><span class="keyword">(){<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="string">"MyClass_3[public=$this-&gt;pubVal, protected=$this-&gt;proVal, private=$this-&gt;priVal]"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
class </span><span class="default">MyClass_4 </span><span class="keyword">extends </span><span class="default">MyClass_3</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; protected </span><span class="default">$proVal </span><span class="keyword">= </span><span class="string">'Hello FROM MyClass_4'</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">__toString</span><span class="keyword">(){<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="string">"MyClass_4[public=$this-&gt;pubVal, protected=$this-&gt;proVal, private=$this-&gt;priVal]"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
class </span><span class="default">MyClass_5 </span><span class="keyword">extends </span><span class="default">MyClass_4</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">__toString</span><span class="keyword">(){<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="string">"MyClass_5[public=$this-&gt;pubVal, protected=$this-&gt;proVal, private=$this-&gt;priVal]"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
class </span><span class="default">MyClass_6 </span><span class="keyword">extends </span><span class="default">MyClass_5</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; private </span><span class="default">$priVal </span><span class="keyword">= </span><span class="string">'Hello TO MyClass_6'</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">__toString</span><span class="keyword">(){<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="string">"MyClass_6[public=$this-&gt;pubVal, protected=$this-&gt;proVal, private=$this-&gt;priVal]"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
echo (new </span><span class="default">MyClass_1</span><span class="keyword">()) . </span><span class="string">'&lt;br&gt;'</span><span class="keyword">;<br />
echo (new </span><span class="default">MyClass_2</span><span class="keyword">()) . </span><span class="string">'&lt;br&gt;'</span><span class="keyword">;<br />
echo (new </span><span class="default">MyClass_3</span><span class="keyword">()) . </span><span class="string">'&lt;br&gt;'</span><span class="keyword">;<br />
echo (new </span><span class="default">MyClass_4</span><span class="keyword">()) . </span><span class="string">'&lt;br&gt;'</span><span class="keyword">;<br />
echo (new </span><span class="default">MyClass_5</span><span class="keyword">()) . </span><span class="string">'&lt;br&gt;'</span><span class="keyword">;<br />
echo (new </span><span class="default">MyClass_6</span><span class="keyword">()) . </span><span class="string">'&lt;br&gt;'</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
The list of extended objects:<br />
<br />
&nbsp;MyClass_1[public=Hello World!, protected=Hello FROM MyClass_1, private=Hello TO MyClass_1]<br />
&nbsp;MyClass_2[public=Hello World!, protected=Hello FROM MyClass_1, private=]<br />
&nbsp;MyClass_3[public=Hello World!, protected=Hello FROM MyClass_1, private=Hello TO MyClass_3]<br />
&nbsp;MyClass_4[public=Hello World!, protected=Hello FROM MyClass_4, private=]<br />
&nbsp;MyClass_5[public=Hello World!, protected=Hello FROM MyClass_4, private=]<br />
&nbsp;MyClass_6[public=Hello World!, protected=Hello FROM MyClass_4, private=Hello TO MyClass_6]<br />
<br />
Notice in the class definitions, I made absolutly no attempt to change protected members to private, etc. - that gets too confusing and is not necessary - though I did redeclare a few members with the same var name and visibility strength. One other noteworthy: the output for MyClass_2, there seems to be a private property with value of empty string. Here, $priVal was not inherited from MyClass_1 because it is private in MyClass_1, instead, because of PHP's relaxed syntax, it was actually created right there in MyClass2::__toString() method... not only that, it is not a private member either. Watch out for this kind of thing, as PHP can sometimes give confusing impressions.</span>
</code></div>
  </div>
 </div>
 <a name="79807"></a>
 <div class="note">
  <strong class='user'>omnibus at omnibus dot edu dot pl</strong>
  <a href="#79807" class="date">13-Dec-2007 06:34</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Please note that if a class has a protected variable, a subclass cannot have the same variable redefined private (must be protected or weaker). It seemed to be logical for me as a subsubclass would not know if it could see it or not but even if you declare a subclass to be final the restriction remains.</span>
</code></div>
  </div>
 </div>
 <a name="78432"></a>
 <div class="note">
  <strong class='user'>phpdoc at povaddict dot com dot ar</strong>
  <a href="#78432" class="date">11-Oct-2007 12:52</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Re: ference at super_delete_brose dot co dot uk<br />
<br />
"If eval() is the answer, you’re almost certainly asking the wrong question."<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">eval(</span><span class="string">'$result = $this-&gt;'</span><span class="keyword">.</span><span class="default">$var</span><span class="keyword">.</span><span class="string">';'</span><span class="keyword">); </span><span class="comment">//wrong<br />
</span><span class="default">$result </span><span class="keyword">= </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">$var</span><span class="keyword">; </span><span class="comment">//right way<br />
<br />
</span><span class="default">$var </span><span class="keyword">= </span><span class="string">"foo"</span><span class="keyword">;<br />
</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">var </span><span class="keyword">= </span><span class="string">"this will assign to member called 'var'."</span><span class="keyword">;<br />
</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">$var </span><span class="keyword">= </span><span class="string">"this will assign to member called 'foo'."</span><span class="keyword">;<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="75426"></a>
 <div class="note">
  <strong class='user'>Joshua Watt</strong>
  <a href="#75426" class="date">29-May-2007 12:09</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I couldn't find this documented anywhere, but you can access protected and private member varaibles in different instance of the same class, just as you would expect<br />
<br />
i.e.<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">A<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; protected </span><span class="default">$prot</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; private </span><span class="default">$priv</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">__construct</span><span class="keyword">(</span><span class="default">$a</span><span class="keyword">, </span><span class="default">$b</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">prot </span><span class="keyword">= </span><span class="default">$a</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">priv </span><span class="keyword">= </span><span class="default">$b</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">print_other</span><span class="keyword">(</span><span class="default">A $other</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="default">$other</span><span class="keyword">-&gt;</span><span class="default">prot</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="default">$other</span><span class="keyword">-&gt;</span><span class="default">priv</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
class </span><span class="default">B </span><span class="keyword">extends </span><span class="default">A<br />
</span><span class="keyword">{<br />
}<br />
<br />
</span><span class="default">$a </span><span class="keyword">= new </span><span class="default">A</span><span class="keyword">(</span><span class="string">"a_protected"</span><span class="keyword">, </span><span class="string">"a_private"</span><span class="keyword">);<br />
</span><span class="default">$other_a </span><span class="keyword">= new </span><span class="default">A</span><span class="keyword">(</span><span class="string">"other_a_protected"</span><span class="keyword">, </span><span class="string">"other_a_private"</span><span class="keyword">);<br />
<br />
</span><span class="default">$b </span><span class="keyword">= new </span><span class="default">B</span><span class="keyword">(</span><span class="string">"b_protected"</span><span class="keyword">, </span><span class="string">"ba_private"</span><span class="keyword">);<br />
<br />
</span><span class="default">$other_a</span><span class="keyword">-&gt;</span><span class="default">print_other</span><span class="keyword">(</span><span class="default">$a</span><span class="keyword">); </span><span class="comment">//echoes a_protected and a_private<br />
</span><span class="default">$other_a</span><span class="keyword">-&gt;</span><span class="default">print_other</span><span class="keyword">(</span><span class="default">$b</span><span class="keyword">); </span><span class="comment">//echoes b_protected and ba_private<br />
<br />
</span><span class="default">$b</span><span class="keyword">-&gt;</span><span class="default">print_other</span><span class="keyword">(</span><span class="default">$a</span><span class="keyword">); </span><span class="comment">//echoes a_protected and a_private<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="75290"></a>
 <div class="note">
  <strong class='user'>ference at super_delete_brose dot co dot uk</strong>
  <a href="#75290" class="date">22-May-2007 10:10</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Sometimes you may wish to have all members of a class visible to other classes, but not editable - effectively read-only.<br />
<br />
In this case defining them as public or protected is no good, but defining them as private is too strict and by convention requires you to write accessor functions.<br />
<br />
Here is the lazy way, using one get function for accessing any of the variables:<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">class </span><span class="default">Foo </span><span class="keyword">{<br />
<br />
&nbsp; private </span><span class="default">$a</span><span class="keyword">;<br />
&nbsp; private </span><span class="default">$b</span><span class="keyword">;<br />
&nbsp; private </span><span class="default">$c</span><span class="keyword">;<br />
&nbsp; private </span><span class="default">$d</span><span class="keyword">;<br />
&nbsp; private </span><span class="default">$e</span><span class="keyword">;<br />
&nbsp; private </span><span class="default">$f</span><span class="keyword">;<br />
<br />
&nbsp; public function </span><span class="default">__construct</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">a </span><span class="keyword">= </span><span class="string">'Value of $a'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">b </span><span class="keyword">= </span><span class="string">'Value of $b'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">c </span><span class="keyword">= </span><span class="string">'Value of $c'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">d </span><span class="keyword">= </span><span class="string">'Value of $d'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">e </span><span class="keyword">= </span><span class="string">'Value of $e'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">f </span><span class="keyword">= </span><span class="string">'Value of $f'</span><span class="keyword">;<br />
&nbsp; }<br />
<br />
&nbsp; </span><span class="comment">/* Accessor for all class variables. */<br />
&nbsp; </span><span class="keyword">public function </span><span class="default">get</span><span class="keyword">(</span><span class="default">$what</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$result </span><span class="keyword">= </span><span class="default">FALSE</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$vars </span><span class="keyword">= </span><span class="default">array_keys</span><span class="keyword">(</span><span class="default">get_class_vars</span><span class="keyword">(</span><span class="string">'Foo'</span><span class="keyword">));<br />
<br />
&nbsp;&nbsp;&nbsp; foreach (</span><span class="default">$vars </span><span class="keyword">as </span><span class="default">$var</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp;&nbsp; if (</span><span class="default">$what </span><span class="keyword">== </span><span class="default">$var</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; eval(</span><span class="string">'$result = $this-&gt;'</span><span class="keyword">.</span><span class="default">$var</span><span class="keyword">.</span><span class="string">';'</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$result</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">$result</span><span class="keyword">;<br />
&nbsp; }<br />
}<br />
<br />
class </span><span class="default">Bar </span><span class="keyword">{<br />
&nbsp; private </span><span class="default">$a</span><span class="keyword">;<br />
<br />
&nbsp; public function </span><span class="default">__construct</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$foo </span><span class="keyword">= new </span><span class="default">Foo</span><span class="keyword">();<br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$foo</span><span class="keyword">-&gt;</span><span class="default">get</span><span class="keyword">(</span><span class="string">'a'</span><span class="keyword">));&nbsp; &nbsp;&nbsp; </span><span class="comment">// results in: string(11) "Value of $a"<br />
&nbsp; </span><span class="keyword">}<br />
}<br />
<br />
</span><span class="default">$bar </span><span class="keyword">= new </span><span class="default">Bar</span><span class="keyword">();<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="74376"></a>
 <div class="note">
  <strong class='user'>nanocaiordo at gmail dot com</strong>
  <a href="#74376" class="date">08-Apr-2007 06:49</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you always thought how can you use a private method in php4 classes then try the following within your class.<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">private_func</span><span class="keyword">(</span><span class="default">$func</span><span class="keyword">)<br />
{<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">file </span><span class="keyword">= </span><span class="default">__FILE__</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; if (</span><span class="default">PHPVERS </span><span class="keyword">&gt;= </span><span class="default">43</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$tmp </span><span class="keyword">= </span><span class="default">debug_backtrace</span><span class="keyword">();<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; for (</span><span class="default">$i</span><span class="keyword">=</span><span class="default">0</span><span class="keyword">; </span><span class="default">$i</span><span class="keyword">&lt;</span><span class="default">count</span><span class="keyword">(</span><span class="default">$tmp</span><span class="keyword">); ++</span><span class="default">$i</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; if (isset(</span><span class="default">$tmp</span><span class="keyword">[</span><span class="default">$i</span><span class="keyword">][</span><span class="string">'function'</span><span class="keyword">][</span><span class="default">$func</span><span class="keyword">])) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; if (</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">file </span><span class="keyword">!= </span><span class="default">$tmp</span><span class="keyword">[</span><span class="default">$i</span><span class="keyword">][</span><span class="string">'file'</span><span class="keyword">]) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">trigger_error</span><span class="keyword">(</span><span class="string">'Call to a private method '</span><span class="keyword">.</span><span class="default">__CLASS__</span><span class="keyword">.</span><span class="string">'::'</span><span class="keyword">.</span><span class="default">$func</span><span class="keyword">.</span><span class="string">' in '</span><span class="keyword">.</span><span class="default">$tmp</span><span class="keyword">[</span><span class="default">$i</span><span class="keyword">][</span><span class="string">'file'</span><span class="keyword">], </span><span class="default">E_USER_ERROR</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
Then inside the private function add:<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">foo</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">private_func</span><span class="keyword">(</span><span class="default">__FUNCTION__</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment"># your staff goes here<br />
</span><span class="keyword">}<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="69104"></a>
 <div class="note">
  <strong class='user'>stephane at harobed dot org</strong>
  <a href="#69104" class="date">23-Aug-2006 02:22</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
A class A static public function can access to class A private function :<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">A </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; private function </span><span class="default">foo</span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; print(</span><span class="string">"bar"</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; static public function </span><span class="default">bar</span><span class="keyword">(</span><span class="default">$a</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$a</span><span class="keyword">-&gt;</span><span class="default">foo</span><span class="keyword">();<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="default">$a </span><span class="keyword">= new </span><span class="default">A</span><span class="keyword">();<br />
<br />
</span><span class="default">A</span><span class="keyword">::</span><span class="default">bar</span><span class="keyword">(</span><span class="default">$a</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span><br />
It's working.</span>
</code></div>
  </div>
 </div>
 <a name="68101"></a>
 <div class="note">
  <strong class='user'>kakugo at kakugo dot com</strong>
  <a href="#68101" class="date">13-Jul-2006 03:19</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
This refers to previous notes on protected members being manipulated externally:<br />
<br />
It is obvious that if you were to allow methods the option of replacing protected variables with external ones it will be possible, but there is no reason not to simply use a protected method to define these, or not to write the code to allow it. Just because it is possible doesn't mean it's a problem, it simply does not allow you to be lax on the security of the class.</span>
</code></div>
  </div>
 </div>
 <a name="60382"></a>
 <div class="note">
  <strong class='user'>r dot wilczek at web-appz dot de</strong>
  <a href="#60382" class="date">05-Jan-2006 05:11</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Beware: Visibility works on a per-class-base and does not prevent instances of the same class accessing each others properties!<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">Foo<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; private </span><span class="default">$bar</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">debugBar</span><span class="keyword">(</span><span class="default">Foo $object</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// this does NOT violate visibility although $bar is private<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">echo </span><span class="default">$object</span><span class="keyword">-&gt;</span><span class="default">bar</span><span class="keyword">, </span><span class="string">"\n"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">setBar</span><span class="keyword">(</span><span class="default">$value</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// Neccessary method, for $bar is invisible outside the class<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">bar </span><span class="keyword">= </span><span class="default">$value</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">setForeignBar</span><span class="keyword">(</span><span class="default">Foo $object</span><span class="keyword">, </span><span class="default">$value</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// this does NOT violate visibility!<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$object</span><span class="keyword">-&gt;</span><span class="default">bar </span><span class="keyword">= </span><span class="default">$value</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="default">$a </span><span class="keyword">= new </span><span class="default">Foo</span><span class="keyword">();<br />
</span><span class="default">$b </span><span class="keyword">= new </span><span class="default">Foo</span><span class="keyword">();<br />
</span><span class="default">$a</span><span class="keyword">-&gt;</span><span class="default">setBar</span><span class="keyword">(</span><span class="default">1</span><span class="keyword">);<br />
</span><span class="default">$b</span><span class="keyword">-&gt;</span><span class="default">setBar</span><span class="keyword">(</span><span class="default">2</span><span class="keyword">);<br />
</span><span class="default">$a</span><span class="keyword">-&gt;</span><span class="default">debugBar</span><span class="keyword">(</span><span class="default">$b</span><span class="keyword">);&nbsp; &nbsp; &nbsp; &nbsp; </span><span class="comment">// 2<br />
</span><span class="default">$b</span><span class="keyword">-&gt;</span><span class="default">debugBar</span><span class="keyword">(</span><span class="default">$a</span><span class="keyword">);&nbsp; &nbsp; &nbsp; &nbsp; </span><span class="comment">// 1<br />
</span><span class="default">$a</span><span class="keyword">-&gt;</span><span class="default">setForeignBar</span><span class="keyword">(</span><span class="default">$b</span><span class="keyword">, </span><span class="default">3</span><span class="keyword">);<br />
</span><span class="default">$b</span><span class="keyword">-&gt;</span><span class="default">setForeignBar</span><span class="keyword">(</span><span class="default">$a</span><span class="keyword">, </span><span class="default">4</span><span class="keyword">);<br />
</span><span class="default">$a</span><span class="keyword">-&gt;</span><span class="default">debugBar</span><span class="keyword">(</span><span class="default">$b</span><span class="keyword">);&nbsp; &nbsp; &nbsp; &nbsp; </span><span class="comment">// 3<br />
</span><span class="default">$b</span><span class="keyword">-&gt;</span><span class="default">debugBar</span><span class="keyword">(</span><span class="default">$a</span><span class="keyword">);&nbsp; &nbsp; &nbsp; &nbsp; </span><span class="comment">// 4<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="56432"></a>
 <div class="note">
  <strong class='user'>gugglegum at gmail dot com</strong>
  <a href="#56432" class="date">02-Sep-2005 03:14</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Private visibility actually force members to be not inherited instead of limit its visibility. There is a small nuance that allows you to redeclare private member in child classes.<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">class </span><span class="default">A<br />
</span><span class="keyword">{<br />
private </span><span class="default">$prop </span><span class="keyword">= </span><span class="string">'I am property of A!'</span><span class="keyword">;<br />
}<br />
<br />
class </span><span class="default">B </span><span class="keyword">extends </span><span class="default">A<br />
</span><span class="keyword">{<br />
public </span><span class="default">$prop </span><span class="keyword">= </span><span class="string">'I am property of B!'</span><span class="keyword">;<br />
}<br />
<br />
</span><span class="default">$b </span><span class="keyword">= new </span><span class="default">B</span><span class="keyword">();<br />
echo </span><span class="default">$b</span><span class="keyword">-&gt;</span><span class="default">prop</span><span class="keyword">; </span><span class="comment">// "I am property of B!"<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="54986"></a>
 <div class="note">
  <strong class='user'>Miguel &lt;miguel at lugopolis dot net&gt;</strong>
  <a href="#54986" class="date">21-Jul-2005 08:10</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
A note about private members, the doc says "Private limits visibility only to the class that defines the item" this says that the following code works as espected:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">A </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; private </span><span class="default">$_myPrivate</span><span class="keyword">=</span><span class="string">"private"</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">showPrivate</span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">_myPrivate</span><span class="keyword">.</span><span class="string">"\n"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
class </span><span class="default">B </span><span class="keyword">extends </span><span class="default">A </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">show</span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">showPrivate</span><span class="keyword">();<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="default">$obj</span><span class="keyword">=new </span><span class="default">B</span><span class="keyword">();<br />
</span><span class="default">$obj</span><span class="keyword">-&gt;</span><span class="default">show</span><span class="keyword">(); </span><span class="comment">// shows "private\n";<br />
</span><span class="default">?&gt;<br />
</span><br />
this works cause A::showPrivate() is defined in the same class as $_myPrivate and has access to it.</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=language.oop5.visibility&amp;redirect=http://www.php.net/manual/en/language.oop5.visibility.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.oop5.visibility&amp;redirect=http://www.php.net/manual/en/language.oop5.visibility.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/language.oop5.visibility.php">show source</a> |
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