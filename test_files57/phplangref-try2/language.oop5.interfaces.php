<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Object Interfaces - Manual</title>
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
 <link rel="prev" href="language.oop5.abstract.php" />
 <link rel="next" href="language.oop5.traits.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/oop5.interfaces" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/language.oop5.interfaces.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="@w{VQ9ZFBSA}" />
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
 <li class="active"><a href="language.oop5.interfaces.php">Object Interfaces</a></li>
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
  <a href="language.oop5.traits.php">Traits<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.oop5.abstract.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Class Abstraction</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.oop5.interfaces.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/language.oop5.interfaces.php">Brazilian Portuguese</option>
    <option value="zh/language.oop5.interfaces.php">Chinese (Simplified)</option>
    <option value="fr/language.oop5.interfaces.php">French</option>
    <option value="de/language.oop5.interfaces.php">German</option>
    <option value="ja/language.oop5.interfaces.php">Japanese</option>
    <option value="pl/language.oop5.interfaces.php">Polish</option>
    <option value="ro/language.oop5.interfaces.php">Romanian</option>
    <option value="ru/language.oop5.interfaces.php">Russian</option>
    <option value="fa/language.oop5.interfaces.php">Persian</option>
    <option value="es/language.oop5.interfaces.php">Spanish</option>
    <option value="tr/language.oop5.interfaces.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="language.oop5.interfaces" class="sect1">
  <h2 class="title">Object Interfaces</h2>
  <p class="para">
   Object interfaces allow you to create code which specifies which methods a
   class must implement, without having to define how these methods are
   handled.
  </p>
  <p class="para">
   Interfaces are defined using the <em>interface</em> keyword, in the same way as a
   standard class, but without any of the methods having their contents
   defined.
  </p>
  <p class="para">
   All methods declared in an interface must be public, this is the nature of an
   interface.
  </p>
  <div class="sect2" id="language.oop5.interfaces.implements">
   <h3 class="title"><em>implements</em></h3>
   <p class="para">
    To implement an interface, the <em>implements</em> operator is used.
    All methods in the interface must be implemented within a class; failure to do
    so will result in a fatal error. Classes may implement more than one interface
    if desired by separating each interface with a comma.
   </p>
   <blockquote class="note"><p><strong class="note">Note</strong>: 
    <p class="para">
     A class cannot implement two interfaces that share function names, since
     it would cause ambiguity.
    </p>
   </p></blockquote>
   <blockquote class="note"><p><strong class="note">Note</strong>: 
    <p class="para">
     Interfaces can be extended like classes using the <a href="language.oop5.inheritance.php" class="link">extends</a> 
     operator.
    </p>
   </p></blockquote>
   <blockquote class="note"><p><strong class="note">Note</strong>: 
    <p class="para">
     The class implementing the interface must use the exact same method
     signatures as are defined in the interface. Not doing so will result in a
     fatal error.
     </p>
    </p></blockquote>
  </div>
  <div class="sect2" id="language.oop5.interfaces.constants">
   <h3 class="title"><em>Constants</em></h3>
   <p class="para">
    It&#039;s possible for interfaces to have constants. Interface constants works exactly 
    like <a href="language.oop5.constants.php" class="link">class constants</a> except
    they cannot be overridden by a class/interface that inherits them.
   </p>
  </div>
  <div class="sect2" id="language.oop5.interfaces.examples">
   <h3 class="title">Examples</h3>
   <div class="example" id="language.oop5.interfaces.examples.ex1">
    <p><strong>Example #1 Interface example</strong></p>
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /><br /></span><span style="color: #FF8000">//&nbsp;Declare&nbsp;the&nbsp;interface&nbsp;'iTemplate'<br /></span><span style="color: #007700">interface&nbsp;</span><span style="color: #0000BB">iTemplate<br /></span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">setVariable</span><span style="color: #007700">(</span><span style="color: #0000BB">$name</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$var</span><span style="color: #007700">);<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">getHtml</span><span style="color: #007700">(</span><span style="color: #0000BB">$template</span><span style="color: #007700">);<br />}<br /><br /></span><span style="color: #FF8000">//&nbsp;Implement&nbsp;the&nbsp;interface<br />//&nbsp;This&nbsp;will&nbsp;work<br /></span><span style="color: #007700">class&nbsp;</span><span style="color: #0000BB">Template&nbsp;</span><span style="color: #007700">implements&nbsp;</span><span style="color: #0000BB">iTemplate<br /></span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;private&nbsp;</span><span style="color: #0000BB">$vars&nbsp;</span><span style="color: #007700">=&nbsp;array();<br />&nbsp;&nbsp;<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">setVariable</span><span style="color: #007700">(</span><span style="color: #0000BB">$name</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$var</span><span style="color: #007700">)<br />&nbsp;&nbsp;&nbsp;&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">vars</span><span style="color: #007700">[</span><span style="color: #0000BB">$name</span><span style="color: #007700">]&nbsp;=&nbsp;</span><span style="color: #0000BB">$var</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />&nbsp;&nbsp;<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">getHtml</span><span style="color: #007700">(</span><span style="color: #0000BB">$template</span><span style="color: #007700">)<br />&nbsp;&nbsp;&nbsp;&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;foreach(</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">vars&nbsp;</span><span style="color: #007700">as&nbsp;</span><span style="color: #0000BB">$name&nbsp;</span><span style="color: #007700">=&gt;&nbsp;</span><span style="color: #0000BB">$value</span><span style="color: #007700">)&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$template&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">str_replace</span><span style="color: #007700">(</span><span style="color: #DD0000">'{'&nbsp;</span><span style="color: #007700">.&nbsp;</span><span style="color: #0000BB">$name&nbsp;</span><span style="color: #007700">.&nbsp;</span><span style="color: #DD0000">'}'</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$value</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$template</span><span style="color: #007700">);<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}<br />&nbsp;<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;return&nbsp;</span><span style="color: #0000BB">$template</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br /><br /></span><span style="color: #FF8000">//&nbsp;This&nbsp;will&nbsp;not&nbsp;work<br />//&nbsp;Fatal&nbsp;error:&nbsp;Class&nbsp;BadTemplate&nbsp;contains&nbsp;1&nbsp;abstract&nbsp;methods<br />//&nbsp;and&nbsp;must&nbsp;therefore&nbsp;be&nbsp;declared&nbsp;abstract&nbsp;(iTemplate::getHtml)<br /></span><span style="color: #007700">class&nbsp;</span><span style="color: #0000BB">BadTemplate&nbsp;</span><span style="color: #007700">implements&nbsp;</span><span style="color: #0000BB">iTemplate<br /></span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;private&nbsp;</span><span style="color: #0000BB">$vars&nbsp;</span><span style="color: #007700">=&nbsp;array();<br />&nbsp;&nbsp;<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">setVariable</span><span style="color: #007700">(</span><span style="color: #0000BB">$name</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$var</span><span style="color: #007700">)<br />&nbsp;&nbsp;&nbsp;&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">vars</span><span style="color: #007700">[</span><span style="color: #0000BB">$name</span><span style="color: #007700">]&nbsp;=&nbsp;</span><span style="color: #0000BB">$var</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

   </div>
   <div class="example" id="language.oop5.interfaces.examples.ex2">
    <p><strong>Example #2 Extendable Interfaces</strong></p>
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">interface&nbsp;</span><span style="color: #0000BB">a<br /></span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">foo</span><span style="color: #007700">();<br />}<br /><br />interface&nbsp;</span><span style="color: #0000BB">b&nbsp;</span><span style="color: #007700">extends&nbsp;</span><span style="color: #0000BB">a<br /></span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">baz</span><span style="color: #007700">(</span><span style="color: #0000BB">Baz&nbsp;$baz</span><span style="color: #007700">);<br />}<br /><br /></span><span style="color: #FF8000">//&nbsp;This&nbsp;will&nbsp;work<br /></span><span style="color: #007700">class&nbsp;</span><span style="color: #0000BB">c&nbsp;</span><span style="color: #007700">implements&nbsp;</span><span style="color: #0000BB">b<br /></span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">foo</span><span style="color: #007700">()<br />&nbsp;&nbsp;&nbsp;&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">baz</span><span style="color: #007700">(</span><span style="color: #0000BB">Baz&nbsp;$baz</span><span style="color: #007700">)<br />&nbsp;&nbsp;&nbsp;&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br /><br /></span><span style="color: #FF8000">//&nbsp;This&nbsp;will&nbsp;not&nbsp;work&nbsp;and&nbsp;result&nbsp;in&nbsp;a&nbsp;fatal&nbsp;error<br /></span><span style="color: #007700">class&nbsp;</span><span style="color: #0000BB">d&nbsp;</span><span style="color: #007700">implements&nbsp;</span><span style="color: #0000BB">b<br /></span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">foo</span><span style="color: #007700">()<br />&nbsp;&nbsp;&nbsp;&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">baz</span><span style="color: #007700">(</span><span style="color: #0000BB">Foo&nbsp;$foo</span><span style="color: #007700">)<br />&nbsp;&nbsp;&nbsp;&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

   </div>
   <div class="example" id="language.oop5.interfaces.examples.ex3">
    <p><strong>Example #3 Multiple interface inheritance</strong></p>
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">interface&nbsp;</span><span style="color: #0000BB">a<br /></span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">foo</span><span style="color: #007700">();<br />}<br /><br />interface&nbsp;</span><span style="color: #0000BB">b<br /></span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">bar</span><span style="color: #007700">();<br />}<br /><br />interface&nbsp;</span><span style="color: #0000BB">c&nbsp;</span><span style="color: #007700">extends&nbsp;</span><span style="color: #0000BB">a</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">b<br /></span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">baz</span><span style="color: #007700">();<br />}<br /><br />class&nbsp;</span><span style="color: #0000BB">d&nbsp;</span><span style="color: #007700">implements&nbsp;</span><span style="color: #0000BB">c<br /></span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">foo</span><span style="color: #007700">()<br />&nbsp;&nbsp;&nbsp;&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">bar</span><span style="color: #007700">()<br />&nbsp;&nbsp;&nbsp;&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">baz</span><span style="color: #007700">()<br />&nbsp;&nbsp;&nbsp;&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

   </div>
   <div class="example" id="language.oop5.interfaces.examples.ex4">
    <p><strong>Example #4 Interfaces with constants</strong></p>
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">interface&nbsp;</span><span style="color: #0000BB">a<br /></span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;const&nbsp;</span><span style="color: #0000BB">b&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'Interface&nbsp;constant'</span><span style="color: #007700">;<br />}<br /><br /></span><span style="color: #FF8000">//&nbsp;Prints:&nbsp;Interface&nbsp;constant<br /></span><span style="color: #007700">echo&nbsp;</span><span style="color: #0000BB">a</span><span style="color: #007700">::</span><span style="color: #0000BB">b</span><span style="color: #007700">;<br /><br /><br /></span><span style="color: #FF8000">//&nbsp;This&nbsp;will&nbsp;however&nbsp;not&nbsp;work&nbsp;because&nbsp;it's&nbsp;not&nbsp;allowed&nbsp;to&nbsp;<br />//&nbsp;override&nbsp;constants.<br /></span><span style="color: #007700">class&nbsp;</span><span style="color: #0000BB">b&nbsp;</span><span style="color: #007700">implements&nbsp;</span><span style="color: #0000BB">a<br /></span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;const&nbsp;</span><span style="color: #0000BB">b&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'Class&nbsp;constant'</span><span style="color: #007700">;<br />}<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

   </div>
   <p class="para">
     An interface, together with type-hinting, provides a good way to make sure
     that a particular object contains particular methods. See
     <a href="language.operators.type.php" class="link">instanceof</a> operator and
     <a href="language.oop5.typehinting.php" class="link">type hinting</a>.
   </p>
  </div>

 </div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="language.oop5.traits.php">Traits<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.oop5.abstract.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Class Abstraction</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.oop5.interfaces.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=language.oop5.interfaces&amp;redirect=@w{VQ9ZFBSA}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.oop5.interfaces&amp;redirect=@w{VQ9ZFBSA}">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Object Interfaces</strong>
 </div><div id="allnotes">
 <a name="108171"></a>
 <div class="note">
  <strong class='user'>md2perpe</strong>
  <a href="#108171" class="date">04-Apr-2012 03:08</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
When implementing an interface that extends Traversable, not only you have to supply an implementation of Traversable (i.e. either Iterator or IteratorAggregate) and tell which one you implement.<br />
<br />
It also matters in which order the implemented interfaces are listed.<br />
<br />
Changing "implements Iterator, MyTraversable" to "implements MyTraversable, Iterator" in the following snippet fails as of PHP 5.3.10:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">interface </span><span class="default">MyTraversable </span><span class="keyword">extends </span><span class="default">Traversable </span><span class="keyword">{}<br />
<br />
class </span><span class="default">MyClass </span><span class="keyword">implements </span><span class="default">Iterator</span><span class="keyword">, </span><span class="default">MyTraversable<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">// Implement Iterator<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">public function </span><span class="default">rewind</span><span class="keyword">() {}<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">valid</span><span class="keyword">() {}<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">current</span><span class="keyword">() {}<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">key</span><span class="keyword">() {}<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">next</span><span class="keyword">() {}<br />
}<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="107849"></a>
 <div class="note">
  <strong class='user'>guillaume at metayer dot ca</strong>
  <a href="#107849" class="date">09-Mar-2012 03:11</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I think good OOP design will actually require both what dlovell2001 and anon said. In the examples below, if Database is only an abstract class, you will have limitations. What if one day you have a special Database object that is so different for some specific needs, that it should NOT inherit from the Database abstract class? You would be stuck. In my opinion, the best thing to do would be to have a Database abstract class that implements a DatabaseInterface interface.<br />
<br />
When working on a Database object, you would first make sure it implements the DatabaseInterface object (dlovell2001, this is something very important that's missing in your example). You can still use any object inheriting the Database class (because this class implements the DatabaseInterface interface) but you are NOT limited to using an object inheriting from Database. Any object implementing the DatabaseInterface will do. This way, you are not limited to a specific type.<br />
<br />
Interfaces are a good way to have a 'contract' with the component using a certain object, without being limited to using a specific type for that object. Also remember to use the 'instanceof' or similar when working on such object. One goal of interfaces is to actually enforce the signature of an object before actually working with it.</span>
</code></div>
  </div>
 </div>
 <a name="107364"></a>
 <div class="note">
  <strong class='user'>dlovell2001 at yahoo dot com</strong>
  <a href="#107364" class="date">03-Feb-2012 10:40</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
It seems like many contributors are missing the point of using an INTERFACE. An INTERFACE is not specifically provided for abstraction. That's what a CLASS is used for. Most examples in this article of interfaces could be achieved just as easily using just classes alone. <br />
<br />
An INTERFACE is provided so you can describe a set of functions and then hide the final implementation of those functions in an implementing class. This allows you to change the IMPLEMENTATION of those functions without changing how you use it. <br />
<br />
For example: I have a database. I want to write a class that accesses the data in my database. I define an interface like this:<br />
<br />
interface Database {<br />
function listOrders();<br />
function addOrder();<br />
function removeOrder();<br />
...<br />
}<br />
<br />
Then let's say we start out using a MySQL database. So we write a class to access the MySQL database:<br />
<br />
class MySqlDatabase implements Database {<br />
function listOrders() {...<br />
}<br />
we write these methods as needed to get to the MySQL database tables. Then you can write your controller to use the interface as such:<br />
<br />
$database = new MySqlDatabase();<br />
foreach ($database-&gt;listOrders() as $order) {<br />
<br />
Then let's say we decide to migrate to an Oracle database. We could write another class to get to the Oracle database as such:<br />
<br />
class OracleDatabase implements Database {<br />
public function listOrders() {...<br />
}<br />
<br />
Then - to switch our application to use the Oracle database instead of the MySQL database we only have to change ONE LINE of code:<br />
<br />
$database = new OracleDatabase();<br />
<br />
all other lines of code, such as:<br />
<br />
foreach ($database-&gt;listOrders() as $order) {<br />
<br />
will remain unchanged. The point is - the INTERFACE describes the methods that we need to access our database. It does NOT describe in any way HOW we achieve that. That's what the IMPLEMENTing class does. We can IMPLEMENT this interface as many times as we need in as many different ways as we need. We can then switch between implementations of the interface without impact to our code because the interface defines how we will use it regardless of how it actually works.</span>
</code></div>
  </div>
 </div>
 <a name="106855"></a>
 <div class="note">
  <strong class='user'>julien arobase fastre point info</strong>
  <a href="#106855" class="date">12-Dec-2011 11:12</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you use namespaces and autoloading, do not forget to mention the use statement for each class used in yours arguments, before your class: <br />
<br />
<span class="default">&lt;?php <br />
</span><span class="comment">#file : fruit/squeezable.php<br />
</span><span class="default">namespace fruit<br />
</span><span class="keyword">use </span><span class="default">BarFoo</span><span class="keyword">;<br />
<br />
interface </span><span class="default">squeezable </span><span class="keyword">{<br />
&nbsp; public function </span><span class="default">squeeze </span><span class="keyword">(</span><span class="default">Foo $foo</span><span class="keyword">);<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
<span class="default">&lt;?php <br />
</span><span class="comment">#file: orange <br />
<br />
</span><span class="default">namespace fruitcitrus</span><span class="keyword">;<br />
<br />
class </span><span class="default">orange </span><span class="keyword">{<br />
&nbsp; public function </span><span class="default">squeeze</span><span class="keyword">(</span><span class="default">Foo $foo</span><span class="keyword">);<br />
}<br />
</span><span class="comment">#Will throw an exception Fatal error: Declaration of "fruit\citrus\orange::squeeze must be compatible with that of fruit\squeezable() in fruit/squeezable.php<br />
</span><span class="default">?&gt;<br />
</span><br />
<span class="default">&lt;?php <br />
</span><span class="comment">#file: orange <br />
<br />
</span><span class="default">namespace fruitcitrus</span><span class="keyword">;<br />
use </span><span class="default">BarFoo</span><span class="keyword">; </span><span class="comment">#DO NOT FORGET THIS!<br />
<br />
</span><span class="keyword">class </span><span class="default">orange </span><span class="keyword">{<br />
&nbsp; public function </span><span class="default">squeeze </span><span class="keyword">(</span><span class="default">Foo $foo</span><span class="keyword">);<br />
}<br />
</span><span class="comment">#Will be correct<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="106290"></a>
 <div class="note">
  <strong class='user'>FX Laviron</strong>
  <a href="#106290" class="date">25-Oct-2011 09:12</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The error "Can't inherit abstract function IB::f() (previously declared abstract in IA)" in the following code<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">interface </span><span class="default">IA </span><span class="keyword">{&nbsp; &nbsp;&nbsp; public function </span><span class="default">f</span><span class="keyword">(); }<br />
<br />
interface </span><span class="default">IB </span><span class="keyword">{&nbsp; &nbsp;&nbsp; public function </span><span class="default">f</span><span class="keyword">(); }<br />
<br />
class </span><span class="default">Test </span><span class="keyword">implements </span><span class="default">IA</span><span class="keyword">, </span><span class="default">IB </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public&nbsp; function </span><span class="default">f</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">"f"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
</span><span class="default">$o </span><span class="keyword">= new </span><span class="default">Test</span><span class="keyword">();<br />
</span><span class="default">$o</span><span class="keyword">-&gt;</span><span class="default">f</span><span class="keyword">();<br />
</span><span class="default">?&gt;<br />
</span><br />
can be avoided (if appropriate) by adding a ancestor interface to IA and IB, and moving the common method to it:<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">interface </span><span class="default">IAncestor </span><span class="keyword">{&nbsp; &nbsp;&nbsp; public function </span><span class="default">f</span><span class="keyword">(); }<br />
<br />
interface </span><span class="default">IA </span><span class="keyword">{&nbsp;&nbsp; }<br />
<br />
interface </span><span class="default">IB </span><span class="keyword">extends </span><span class="default">IAncestor </span><span class="keyword">{&nbsp;&nbsp; }<br />
<br />
class </span><span class="default">Test </span><span class="keyword">implements </span><span class="default">IA</span><span class="keyword">, </span><span class="default">IB </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public&nbsp; function </span><span class="default">f</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">"f"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
</span><span class="default">$o </span><span class="keyword">= new </span><span class="default">Test</span><span class="keyword">();<br />
</span><span class="default">$o</span><span class="keyword">-&gt;</span><span class="default">f</span><span class="keyword">();<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="102755"></a>
 <div class="note">
  <strong class='user'>thanhn2001 at gmail dot com</strong>
  <a href="#102755" class="date">03-Mar-2011 01:37</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
PHP prevents interface a contant to be overridden by a class/interface that DIRECTLY inherits it.&nbsp; However, further inheritance allows it.&nbsp; That means that interface constants are not final as mentioned in a previous comment.&nbsp; Is this a bug or a feature?<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">interface </span><span class="default">a<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; const </span><span class="default">b </span><span class="keyword">= </span><span class="string">'Interface constant'</span><span class="keyword">;<br />
}<br />
<br />
</span><span class="comment">// Prints: Interface constant<br />
</span><span class="keyword">echo </span><span class="default">a</span><span class="keyword">::</span><span class="default">b</span><span class="keyword">;<br />
<br />
class </span><span class="default">b </span><span class="keyword">implements </span><span class="default">a<br />
</span><span class="keyword">{<br />
}<br />
<br />
</span><span class="comment">// This works!!!<br />
</span><span class="keyword">class </span><span class="default">c </span><span class="keyword">extends </span><span class="default">b<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; const </span><span class="default">b </span><span class="keyword">= </span><span class="string">'Class constant'</span><span class="keyword">;<br />
}<br />
<br />
echo </span><span class="default">c</span><span class="keyword">::</span><span class="default">b</span><span class="keyword">;<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="101723"></a>
 <div class="note">
  <strong class='user'>jballard at natoga dot com</strong>
  <a href="#101723" class="date">06-Jan-2011 01:33</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If it isn't already obvious, you can create an object of a class above the class declaration if it does NOT implement an interface. However, when a class DOES implement an interface, PHP will throw a "class not found" error unless the instantiation declaration is _below_ the class definition.<br />
<br />
<span class="default">&lt;?php<br />
$bar </span><span class="keyword">= new </span><span class="default">foo</span><span class="keyword">(); </span><span class="comment">// Valid<br />
<br />
</span><span class="keyword">class </span><span class="default">foo<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; <br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
<span class="default">&lt;?php<br />
$bar </span><span class="keyword">= new </span><span class="default">foo</span><span class="keyword">(); </span><span class="comment">// Invalid - throws fatal error<br />
<br />
</span><span class="keyword">interface </span><span class="default">foo2<br />
</span><span class="keyword">{<br />
<br />
}<br />
<br />
class </span><span class="default">bar </span><span class="keyword">implements </span><span class="default">foo2<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; <br />
}<br />
<br />
</span><span class="default">$bar </span><span class="keyword">= new </span><span class="default">foo</span><span class="keyword">(); </span><span class="comment">// Valid, since it is below the class declaration<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
Also, @Jeffrey -- Lol? What? Is that a joke? PHP interfaces have nothing to do with connecting to "peripheral devices" or "cameras", etc. Not even in the least sense. This is a very common miscommunication with the word "interface", as interfaces in programming are not at all like interfaces in electronics or drivers, etc.</span>
</code></div>
  </div>
 </div>
 <a name="101008"></a>
 <div class="note">
  <strong class='user'>gratcypalma at gmail dot com</strong>
  <a href="#101008" class="date">19-Nov-2010 11:07</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
here is my simply method muliple inheritence with __construct function..<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">foo </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; protected </span><span class="default">$mam </span><span class="keyword">= </span><span class="string">'mamam'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">__construct</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">'foo'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
class </span><span class="default">bar </span><span class="keyword">extends </span><span class="default">foo </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">__construct</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">parent</span><span class="keyword">:: </span><span class="default">__construct</span><span class="keyword">();<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">'bar'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
class </span><span class="default">foobar </span><span class="keyword">extends </span><span class="default">bar </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">__construct</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">parent</span><span class="keyword">:: </span><span class="default">__construct</span><span class="keyword">();<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">'foobar'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="default">$foo </span><span class="keyword">= new </span><span class="default">foobar</span><span class="keyword">();<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="99381"></a>
 <div class="note">
  <strong class='user'>Anonymous</strong>
  <a href="#99381" class="date">13-Aug-2010 12:44</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you want to ensure implementation classes are correctly initialised (i.e. due to trickery one needs to do to work around lack of multiple inheritance), simply add&nbsp; __construct() to your interface, so risk of init being forgotten is reduced.</span>
</code></div>
  </div>
 </div>
 <a name="96368"></a>
 <div class="note">
  <strong class='user'>drieick at hotmail dot com</strong>
  <a href="#96368" class="date">23-Feb-2010 09:29</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I was wondering if implementing interfaces will take into account inheritance. That is, can inherited methods be used to follow an interface's structure?<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">interface </span><span class="default">Auxiliary_Platform </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">Weapon</span><span class="keyword">();<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">Health</span><span class="keyword">();<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">Shields</span><span class="keyword">();<br />
}<br />
<br />
class </span><span class="default">T805 </span><span class="keyword">implements </span><span class="default">Auxiliary_Platform </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">Weapon</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">__CLASS__</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">Health</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">__CLASS__ </span><span class="keyword">. </span><span class="string">"::" </span><span class="keyword">. </span><span class="default">__FUNCTION__</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">Shields</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">__CLASS__ </span><span class="keyword">. </span><span class="string">"-&gt;" </span><span class="keyword">. </span><span class="default">__FUNCTION__</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
class </span><span class="default">T806 </span><span class="keyword">extends </span><span class="default">T805 </span><span class="keyword">implements </span><span class="default">Auxiliary_Platform </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">Weapon</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">__CLASS__</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">Shields</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">__CLASS__ </span><span class="keyword">. </span><span class="string">"-&gt;" </span><span class="keyword">. </span><span class="default">__FUNCTION__</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="default">$T805 </span><span class="keyword">= new </span><span class="default">T805</span><span class="keyword">();<br />
</span><span class="default">$T805</span><span class="keyword">-&gt;</span><span class="default">Weapon</span><span class="keyword">();<br />
</span><span class="default">$T805</span><span class="keyword">-&gt;</span><span class="default">Health</span><span class="keyword">();<br />
</span><span class="default">$T805</span><span class="keyword">-&gt;</span><span class="default">Shields</span><span class="keyword">();<br />
<br />
echo </span><span class="string">"&lt;hr /&gt;"</span><span class="keyword">;<br />
<br />
</span><span class="default">$T806 </span><span class="keyword">= new </span><span class="default">T806</span><span class="keyword">();<br />
</span><span class="default">$T806</span><span class="keyword">-&gt;</span><span class="default">Weapon</span><span class="keyword">();<br />
</span><span class="default">$T806</span><span class="keyword">-&gt;</span><span class="default">Health</span><span class="keyword">();<br />
</span><span class="default">$T806</span><span class="keyword">-&gt;</span><span class="default">Shields</span><span class="keyword">();<br />
<br />
</span><span class="comment">/* Output:<br />
string(4) "T805"<br />
string(12) "T805::Health"<br />
string(13) "T805-&gt;Shields"<br />
&lt;hr /&gt;string(4) "T806"<br />
string(12) "T805::Health"<br />
string(13) "T806-&gt;Shields"<br />
*/<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
Class T805 implements the interface Auxiliary_Platform. T806 does the same thing, but the method Health() is inherited from T805 (not the exact case, but you get the idea). PHP seems to be fine with this and everything still works fine. Do note that the rules for class inheritance doesn't change in this scenario.<br />
<br />
If the code were to be the same, but instead T805 (or T806) DOES NOT implement Auxiliary_Platform, then it'll still work. Since T805 already follows the interface, everything that inherits T805 will also be valid. I would be careful about that. Personally, I don't consider this a bug.<br />
<br />
This seems to work in PHP5.2.9-2, PHP5.3 and PHP5.3.1 (my current versions).<br />
<br />
We could also do the opposite:<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">class </span><span class="default">T805 </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">Weapon</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">__CLASS__</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
class </span><span class="default">T806 </span><span class="keyword">extends </span><span class="default">T805 </span><span class="keyword">implements </span><span class="default">Auxiliary_Platform </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">Health</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">__CLASS__ </span><span class="keyword">. </span><span class="string">"::" </span><span class="keyword">. </span><span class="default">__FUNCTION__</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">Shields</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">__CLASS__ </span><span class="keyword">. </span><span class="string">"-&gt;" </span><span class="keyword">. </span><span class="default">__FUNCTION__</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="default">$T805 </span><span class="keyword">= new </span><span class="default">T805</span><span class="keyword">();<br />
</span><span class="default">$T805</span><span class="keyword">-&gt;</span><span class="default">Weapon</span><span class="keyword">();<br />
<br />
echo </span><span class="string">"&lt;hr /&gt;"</span><span class="keyword">;<br />
<br />
</span><span class="default">$T806 </span><span class="keyword">= new </span><span class="default">T806</span><span class="keyword">();<br />
</span><span class="default">$T806</span><span class="keyword">-&gt;</span><span class="default">Weapon</span><span class="keyword">();<br />
</span><span class="default">$T806</span><span class="keyword">-&gt;</span><span class="default">Health</span><span class="keyword">();<br />
</span><span class="default">$T806</span><span class="keyword">-&gt;</span><span class="default">Shields</span><span class="keyword">();<br />
<br />
</span><span class="comment">/* Output:<br />
string(4) "T805"<br />
&lt;hr /&gt;string(4) "T805"<br />
string(12) "T806::Health"<br />
string(13) "T806-&gt;Shields"<br />
*/<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
This works as well, but the output is different. I'd be careful with this.</span>
</code></div>
  </div>
 </div>
 <a name="96137"></a>
 <div class="note">
  <strong class='user'>uramihsayibok, gmail, com</strong>
  <a href="#96137" class="date">10-Feb-2010 11:25</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Interfaces can define static methods, but note that this won't make sense as you'll be using the class name and not polymorphism.<br />
<br />
...Unless you have PHP 5.3 which supports late static binding:<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">interface </span><span class="default">IDoSomething </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public static function </span><span class="default">doSomething</span><span class="keyword">();<br />
}<br />
<br />
class </span><span class="default">One </span><span class="keyword">implements </span><span class="default">IDoSomething </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public static function </span><span class="default">doSomething</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">"One is doing something\n"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
class </span><span class="default">Two </span><span class="keyword">extends </span><span class="default">One </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public static function </span><span class="default">doSomething</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">"Two is doing something\n"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
function </span><span class="default">example</span><span class="keyword">(</span><span class="default">IDoSomething $doer</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$doer</span><span class="keyword">::</span><span class="default">doSomething</span><span class="keyword">(); </span><span class="comment">// "unexpected ::" in PHP 5.2<br />
</span><span class="keyword">}<br />
<br />
</span><span class="default">example</span><span class="keyword">(new </span><span class="default">One</span><span class="keyword">()); </span><span class="comment">// One is doing something<br />
</span><span class="default">example</span><span class="keyword">(new </span><span class="default">Two</span><span class="keyword">()); </span><span class="comment">// Two is doing something<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
If you have PHP 5.2 you can still declare static methods in interfaces. While you won't be able to call them via LSB, the "implements IDoSomething" can serve as a hint/reminder to other developers by saying "this class has a ::doSomething() method".<br />
Besides, you'll be upgrading to 5.3 soon, right? Right?<br />
<br />
(Heh. I just realized: "I do something". Unintentional, I swear!)</span>
</code></div>
  </div>
 </div>
 <a name="95587"></a>
 <div class="note">
  <strong class='user'>btjakachira at gmail dot com</strong>
  <a href="#95587" class="date">11-Jan-2010 01:52</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I'm going to give a very simple explanation between interface and any abstract. I'm not going to repeat basic stuff mentioned above e.g you can not instantiate an interface or an abstract. Any class with an abstract method should be declared as abstract.. etc.<br />
<br />
Example.<br />
I will show you where and when to use interfaces. Normally people will use interface when absctracting becomes a problem. Lets say we start with the following objects<br />
<br />
<span class="default">&lt;?php<br />
<br />
Person<br />
<br />
Employee<br />
Employer<br />
Criminal<br />
Rapist<br />
President<br />
Student<br />
?&gt;<br />
</span><br />
You will easily see that Employee is a Person, Criminal is a person etc.. Therefore we can have Person as a PARENT class. For person to be abstract, you decide within your application if a person object makes sense... if it doesn't make the person an abstract so that you wont have alien objects.<br />
<br />
Now, interfaces are used when, in a group of objects, you have two or more objects that share similar behaviour. E.g President and Employer will makePolicy() while Criminal and Rapist will commitCrime(). Having said that, normally people would put these methods in their respective classes (but defeating the OO designs). If you put makePolicy() in President and Employer class there will be duplication of code. Other people can be tempted to put thet makePolicy() and commitCrime() in Person as abstract methods so that the 6 objects will see the methods. Its not a good idea as Student or Employee will not normally makePolicy() or commitCrime(). In fact, it means Employee object can makePolicy() of increasind salary :-) . Therefore in this case we use INTERFACES<br />
<br />
<span class="default">&lt;?php<br />
&nbsp;</span><span class="keyword">interface </span><span class="default">HighOffice </span><span class="keyword">{<br />
&nbsp;&nbsp; public function </span><span class="default">makePolicy</span><span class="keyword">();<br />
&nbsp;&nbsp; public function </span><span class="default">declareEmergency</span><span class="keyword">();<br />
}<br />
<br />
&nbsp;interface </span><span class="default">jailable </span><span class="keyword">{<br />
&nbsp;&nbsp; public function </span><span class="default">commitCrime</span><span class="keyword">();<br />
&nbsp;&nbsp; public function </span><span class="default">appeal</span><span class="keyword">();<br />
}<br />
<br />
abstract class </span><span class="default">Person </span><span class="keyword">{<br />
&nbsp; public function </span><span class="default">getAge</span><span class="keyword">()<br />
&nbsp; {<br />
&nbsp;&nbsp;&nbsp; return </span><span class="string">"28years"</span><span class="keyword">;<br />
&nbsp; }<br />
}<br />
<br />
public class </span><span class="default">Employee extend Person </span><span class="keyword">{<br />
&nbsp;</span><span class="comment">//do stuff for employee<br />
</span><span class="keyword">}<br />
<br />
public class </span><span class="default">Criminal extend Person impliments jailable</span><span class="keyword">{<br />
&nbsp;</span><span class="comment">//do stuff for a criminal.<br />
&nbsp;<br />
&nbsp;//these must be present.<br />
&nbsp; </span><span class="keyword">public function </span><span class="default">commitCrime</span><span class="keyword">()<br />
&nbsp; {<br />
<br />
&nbsp; }<br />
&nbsp; public function </span><span class="default">appeal</span><span class="keyword">()<br />
&nbsp; {<br />
&nbsp; }<br />
<br />
public class </span><span class="default">President extend Person impliments HighOffice<br />
</span><span class="keyword">{<br />
<br />
&nbsp; </span><span class="comment">//do other stuff for President<br />
<br />
&nbsp; //then a President should make policies and declare emergency<br />
&nbsp;&nbsp; </span><span class="keyword">public function </span><span class="default">makePolicy</span><span class="keyword">()<br />
&nbsp;&nbsp; {<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp; public function </span><span class="default">declareEmergency</span><span class="keyword">()<br />
&nbsp; {<br />
&nbsp; }<br />
}<br />
<br />
}<br />
&nbsp;<br />
</span><span class="default">?&gt;<br />
</span><br />
NB: forgive me for errors syntax. I in the middle of doing Java.<br />
<br />
You have noticed that relevant behavious have been added to the relevant objects..</span>
</code></div>
  </div>
 </div>
 <a name="91265"></a>
 <div class="note">
  <strong class='user'>rskret at ranphilit dot com</strong>
  <a href="#91265" class="date">02-Jun-2009 02:47</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
This may help understand EX.2. Below are modifiers and additions to<br />
the code. Refer to EX.2 to make a complete code block(this saves comment<br />
space!).<br />
I found the function definition baz(Baz $baz) baffling. Lucky was <br />
able to sus it out fast. Seems method baz requires just one arg and <br />
that must be an instance of the class Baz. Here is a way to know how to <br />
deal with that sort of arg...<br />
<span class="default">&lt;?php<br />
</span><span class="comment"># modify iface b...adding $num to get better understanding<br />
</span><span class="keyword">interface </span><span class="default">b </span><span class="keyword">extends </span><span class="default">a<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">baz</span><span class="keyword">(</span><span class="default">Baz $baz</span><span class="keyword">,</span><span class="default">$num</span><span class="keyword">);<br />
}<br />
</span><span class="comment"># mod claas c <br />
</span><span class="keyword">class </span><span class="default">c </span><span class="keyword">implements </span><span class="default">b<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">foo</span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo</span><span class="string">'foo from class c'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">baz</span><span class="keyword">(</span><span class="default">Baz $baz</span><span class="keyword">,</span><span class="default">$num</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">var_dump </span><span class="keyword">(</span><span class="default">$baz</span><span class="keyword">);</span><span class="comment"># object(Baz)#2 (1) { ["bb"]=&gt;&nbsp; string(3) "hot" }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">echo </span><span class="string">'&lt;br&gt;'</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="default">$baz</span><span class="keyword">-&gt;</span><span class="default">bb</span><span class="keyword">.</span><span class="string">" $num"</span><span class="keyword">;echo </span><span class="string">'&lt;br&gt;'</span><span class="keyword">;</span><span class="comment"># hot 6<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">}<br />
}<br />
</span><span class="comment"># add a class Baz...<br />
</span><span class="keyword">class </span><span class="default">Baz<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public </span><span class="default">$bb</span><span class="keyword">=</span><span class="string">'hot'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">ebaz</span><span class="keyword">(){&nbsp; &nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo</span><span class="string">'this is BAZ'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
</span><span class="comment"># set instance of Baz and get some output...<br />
</span><span class="default">$bazI</span><span class="keyword">=new </span><span class="default">Baz</span><span class="keyword">;<br />
</span><span class="default">baz</span><span class="keyword">::</span><span class="default">ebaz</span><span class="keyword">();echo </span><span class="string">'&lt;br&gt;'</span><span class="keyword">;</span><span class="comment"># this is BAZ<br />
</span><span class="default">c</span><span class="keyword">::</span><span class="default">baz</span><span class="keyword">(</span><span class="default">$bazI</span><span class="keyword">,</span><span class="default">6</span><span class="keyword">);<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="85859"></a>
 <div class="note">
  <strong class='user'>cretz</strong>
  <a href="#85859" class="date">21-Sep-2008 03:49</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
FYI, interfaces can define constructors, destructors, and magic methods. This can be very helpful especially in the case of constructors when instantiating an implementing class via reflection in some sort of factory. Of course, it is not recommended to do such a thing since it goes against the nature of a true interface.</span>
</code></div>
  </div>
 </div>
 <a name="85738"></a>
 <div class="note">
  <strong class='user'>lazybones_senior</strong>
  <a href="#85738" class="date">15-Sep-2008 10:41</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
WHOA! KEEP IT SIMPLE...<br />
<br />
With the code below, you already get a feel at how much ground this app might cover.<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">interface </span><span class="default">ElectricalDevice</span><span class="keyword">{<br />
&nbsp; public function </span><span class="default">power_on</span><span class="keyword">();<br />
&nbsp; public function </span><span class="default">power_off</span><span class="keyword">();<br />
}<br />
<br />
interface </span><span class="default">FrequencyTuner</span><span class="keyword">{<br />
&nbsp; public function </span><span class="default">get_frequencey</span><span class="keyword">();<br />
&nbsp; public function </span><span class="default">set_frequency</span><span class="keyword">(</span><span class="default">$f</span><span class="keyword">);<br />
}<br />
<br />
class </span><span class="default">ElectricFan </span><span class="keyword">implements </span><span class="default">ElectricalDevice</span><span class="keyword">{<br />
&nbsp; </span><span class="comment">// define ElectricalDevice...<br />
</span><span class="keyword">}<br />
<br />
class </span><span class="default">MicrowaveOven </span><span class="keyword">implements </span><span class="default">ElectricalDevice</span><span class="keyword">{<br />
&nbsp; </span><span class="comment">// define ElectricalDevice...<br />
</span><span class="keyword">}<br />
<br />
class </span><span class="default">StereoReceiver </span><span class="keyword">implements </span><span class="default">ElectricalDevice</span><span class="keyword">, </span><span class="default">FrequencyTuner</span><span class="keyword">{<br />
&nbsp; </span><span class="comment">// define ElectricalDevice...<br />
&nbsp; // define FrequencyTuner...<br />
</span><span class="keyword">}<br />
<br />
class </span><span class="default">CellPhone </span><span class="keyword">implements </span><span class="default">ElectricalDevice</span><span class="keyword">, </span><span class="default">FrequencyTuner</span><span class="keyword">{<br />
&nbsp; </span><span class="comment">// define ElectricalDevice...<br />
&nbsp; // define FrequencyTuner...<br />
</span><span class="keyword">}<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
Even those who lack imagination can fill in the blanks from here.</span>
</code></div>
  </div>
 </div>
 <a name="85728"></a>
 <div class="note">
  <strong class='user'>secure_admin</strong>
  <a href="#85728" class="date">14-Sep-2008 02:28</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
In response to harryjry and mehea concerning your Weather Model. The problem is that you don't need all the things you think you need. In OOP, good class definitions get to the point rather quickly.<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">Weather</span><span class="keyword">{<br />
&nbsp; public </span><span class="default">$time</span><span class="keyword">, </span><span class="default">$temperature</span><span class="keyword">, </span><span class="default">$humidity</span><span class="keyword">;<br />
<br />
&nbsp; public function </span><span class="default">__construct</span><span class="keyword">(</span><span class="default">$tm</span><span class="keyword">, </span><span class="default">$t</span><span class="keyword">, </span><span class="default">$h</span><span class="keyword">){<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">time </span><span class="keyword">= </span><span class="default">$tm</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">temperature </span><span class="keyword">= </span><span class="default">$t</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">humidity </span><span class="keyword">= </span><span class="default">$h</span><span class="keyword">;<br />
&nbsp; }<br />
<br />
&nbsp; public function </span><span class="default">__toString</span><span class="keyword">(){<br />
&nbsp;&nbsp;&nbsp; return </span><span class="string">"Time: $this-&gt;time, <br />
&nbsp;&nbsp; &nbsp;&nbsp; Temperature: $this-&gt;temperature&amp;deg;, <br />
&nbsp;&nbsp; &nbsp;&nbsp; Humidity: $this-&gt;humidity%"</span><span class="keyword">;<br />
&nbsp; }<br />
}<br />
<br />
</span><span class="default">$forecasts </span><span class="keyword">= array(<br />
&nbsp; new </span><span class="default">Weather</span><span class="keyword">(</span><span class="string">"1:00 pm"</span><span class="keyword">, </span><span class="default">65</span><span class="keyword">, </span><span class="default">42</span><span class="keyword">),<br />
&nbsp; new </span><span class="default">Weather</span><span class="keyword">(</span><span class="string">"2:00 pm"</span><span class="keyword">, </span><span class="default">66</span><span class="keyword">, </span><span class="default">40</span><span class="keyword">),<br />
&nbsp; new </span><span class="default">Weather</span><span class="keyword">(</span><span class="string">"3:00 pm"</span><span class="keyword">, </span><span class="default">68</span><span class="keyword">, </span><span class="default">39</span><span class="keyword">)<br />
&nbsp; </span><span class="comment">// add more weather reports as desired...<br />
</span><span class="keyword">);<br />
echo </span><span class="string">"Forecast for Chicago, IL:&lt;br&gt;"</span><span class="keyword">;<br />
foreach(</span><span class="default">$forecasts </span><span class="keyword">as </span><span class="default">$forecast</span><span class="keyword">) echo </span><span class="string">' - ' </span><span class="keyword">. </span><span class="default">$forecast </span><span class="string">'&lt;br&gt;'</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span>Forecast for Chicago, IL:<br />
- Time: 1:00 pm, Temperature: 65°, Humidity: 42%<br />
- Time: 2:00 pm, Temperature: 66°, Humidity: 40%<br />
- Time: 3:00 pm, Temperature: 68°, Humidity: 39%<br />
<br />
Note: MySQL can store data like this already, but if you included constants, more variables, and other functions in the Weather class, then maybe, just maybe it could be of use.</span>
</code></div>
  </div>
 </div>
 <a name="84804"></a>
 <div class="note">
  <strong class='user'>mehea</strong>
  <a href="#84804" class="date">30-Jul-2008 11:55</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
While a subclass may implement an interface by extending an abstract class that implements the interface, I question whether it is good design to to do so.&nbsp; Here's what I would suggest while taking the liberty of modifying the above weather/wet model:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">interface </span><span class="default">water<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">makeItWet</span><span class="keyword">();<br />
}<br />
<br />
&nbsp;<br />
</span><span class="comment">/**<br />
&nbsp;&nbsp; * abstract class implements water but defines makeItWet<br />
&nbsp;&nbsp; * in the most general way to allow child class to <br />
&nbsp;&nbsp; * provide specificity<br />
**/<br />
</span><span class="keyword">abstract class </span><span class="default">weather </span><span class="keyword">implements </span><span class="default">water&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <br />
</span><span class="keyword">{<br />
&nbsp;&nbsp; private </span><span class="default">$cloudy</span><span class="keyword">;<br />
&nbsp;&nbsp; public function </span><span class="default">makeItWet</span><span class="keyword">(){}<br />
&nbsp;&nbsp; abstract public function </span><span class="default">start</span><span class="keyword">();<br />
&nbsp;&nbsp; abstract public function </span><span class="default">getCloudy</span><span class="keyword">();<br />
&nbsp;&nbsp; abstract public function </span><span class="default">setCloudy</span><span class="keyword">();<br />
}<br />
<br />
class </span><span class="default">rain </span><span class="keyword">extends </span><span class="default">weather&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; private </span><span class="default">$cloudy</span><span class="keyword">;&nbsp; &nbsp; <br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">start</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="string">"Here's some weather. "</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">makeItWet</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="string">'it is raining cats and dogs today.'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">getCloudy</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">cloudy</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">setCloudy</span><span class="keyword">(</span><span class="default">$bln</span><span class="keyword">=</span><span class="default">false</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">cloudy </span><span class="keyword">= </span><span class="default">$bln</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="default">$a </span><span class="keyword">= new </span><span class="default">rain</span><span class="keyword">();<br />
echo </span><span class="default">$a</span><span class="keyword">-&gt;</span><span class="default">start</span><span class="keyword">();<br />
</span><span class="default">$a</span><span class="keyword">-&gt;</span><span class="default">setCloudy</span><span class="keyword">(</span><span class="default">true</span><span class="keyword">);<br />
if (</span><span class="default">$a</span><span class="keyword">-&gt;</span><span class="default">getCloudy</span><span class="keyword">()) {<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="string">'It is a cloudy day and '</span><span class="keyword">;<br />
}<br />
echo </span><span class="default">$a</span><span class="keyword">-&gt;</span><span class="default">makeItWet</span><span class="keyword">();<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="84537"></a>
 <div class="note">
  <strong class='user'>logik at centrum dot cz</strong>
  <a href="#84537" class="date">17-Jul-2008 02:17</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Makes them useles a bit. I give an example:<br />
I have a class that enumerate (so implements iterator) a interface that has method key() that returns key for the enumerated object.<br />
I cannot implement iterator, that enumerates the objects by itself (so current() returns this), because of collision of method key(). But it's not collision - the key in the iterator and the key in the enumerated object has the same meaning and allways returns same values. <br />
(Common example of this iterator is iterator, that reads from database - make a special object for each row is waste of time).<br />
<br />
Yes - there are workarounds - e.g. rewrite the code so current don't return this - but it's in some cases waste of processor time. <br />
Or I can rename the method key in enumerated object - but why should I wrote the same method twice? It's either waste of time (if the function key is simply duplicated) or waste of time (if the renamed key calls original key).<br />
Well, the right, clear way there would be to redefine interface iterator -- move the method key to the ancestor of iterator, and makes the ancestor ancestor of enumerated interface too. But it's (with built-in interfaces) impossible too.</span>
</code></div>
  </div>
 </div>
 <a name="83766"></a>
 <div class="note">
  <strong class='user'>harryjry at yahoo dot com</strong>
  <a href="#83766" class="date">10-Jun-2008 02:29</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The structure I am working with has a lot of inheritance going on, but not all methods are specified in one place. I needed a way to make sure an interface would be used, but that the method(s) defined in the interface are defined somewhere.<br />
<br />
As such, I learned that the parent can define the interface's methods, and then the children can override that method at will without having to worry about the interface.<br />
<br />
To expand on nrg1981's example, the following is possible:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">interface </span><span class="default">water<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">makeItWet</span><span class="keyword">();<br />
}<br />
<br />
class </span><span class="default">weather<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">makeItWet</span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="string">'it may or may not be wet'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">start</span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="string">'Here is some weather'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
class </span><span class="default">rain </span><span class="keyword">extends </span><span class="default">weather </span><span class="keyword">implements </span><span class="default">water<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">makeItWet</span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="string">'It is wet'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
class </span><span class="default">thunder </span><span class="keyword">extends </span><span class="default">weather </span><span class="keyword">implements </span><span class="default">water<br />
</span><span class="keyword">{<br />
<br />
}<br />
<br />
</span><span class="default">$a </span><span class="keyword">= new </span><span class="default">rain</span><span class="keyword">();<br />
echo </span><span class="default">$a</span><span class="keyword">-&gt;</span><span class="default">start</span><span class="keyword">() . </span><span class="string">"\n"</span><span class="keyword">;<br />
echo </span><span class="default">$a</span><span class="keyword">-&gt;</span><span class="default">makeItWet</span><span class="keyword">() . </span><span class="string">"\n"</span><span class="keyword">;<br />
<br />
</span><span class="default">$a </span><span class="keyword">= new </span><span class="default">thunder</span><span class="keyword">();<br />
echo </span><span class="default">$a</span><span class="keyword">-&gt;</span><span class="default">start</span><span class="keyword">() . </span><span class="string">"\n"</span><span class="keyword">;<br />
echo </span><span class="default">$a</span><span class="keyword">-&gt;</span><span class="default">makeItWet</span><span class="keyword">() . </span><span class="string">"\n"</span><span class="keyword">;<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="82346"></a>
 <div class="note">
  <strong class='user'>kaisershahid at gmail dot com</strong>
  <a href="#82346" class="date">07-Apr-2008 05:41</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
php at wallbash dot com's comment of "It's important to note this because it is very unexpected behavior and renders many common Interface completly useless" doesn't make sense.<br />
<br />
the idea of the interface is to force objects that aren't related to be reused in a common way. without them, to force that requirement, all objects that need those methods implemented would have to be descended from a base class that's known to have those methods. that's clearly not a smart idea if these objects aren't actually related.<br />
<br />
one example (that i'm currently working on) is a background service that pulls information down from different content providers. i have a transport and i have an import. for both, what actually happens in the background is different from provider to provider, but since i'm implementing a transport &amp; import interface, i only need to write code once, because i know exactly the what methods will be implemented to get the job done. then, i just have a config file that loads the class dynamically. i don't need something like<br />
<br />
if ( $provider == "some company" )<br />
{<br />
&nbsp;&nbsp; // use this set of code<br />
}<br />
elseif ( $provider == "another company" )<br />
{<br />
&nbsp;&nbsp; // use this other set of code<br />
}<br />
<br />
instead, i can do:<br />
<br />
foreach ( $providers as $provider =&gt; $info )<br />
{<br />
&nbsp;&nbsp;&nbsp; $_transport = $info['transportObject'];<br />
&nbsp;&nbsp;&nbsp; $transport = new $_transport();<br />
&nbsp;&nbsp;&nbsp; $_import = $info['importObject'];<br />
&nbsp;&nbsp;&nbsp; $import = new $_import();<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; $transport-&gt;setImporter( $import );<br />
&nbsp;&nbsp;&nbsp; $transport-&gt;retrieve();<br />
}<br />
<br />
it is expected behavior that when a class implements two interfaces that share one or more method names, an error is thrown, because interfaces don't relate to each other. if you want that sort of inferred behavior (i.e. A and B are different except for these shared methods), stick to [abstract] classes.<br />
<br />
it sucks that interface methods might collide for some common types of tasks (get(), set(), etc.), so knowing that, design your interfaces with more unique method names.</span>
</code></div>
  </div>
 </div>
 <a name="80202"></a>
 <div class="note">
  <strong class='user'>michael dot martinek at gmail dot com</strong>
  <a href="#80202" class="date">03-Jan-2008 08:18</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
In regards to what Hayley Watson is writing:<br />
<br />
The "interface" is a method of enforcing that anyone who implements it must include all the functions declared in the interface. This is an abstraction method, since you cannot just declare a base class and do something like "public abstract function myTest();" and later on extend that class.<br />
<br />
If you don't override the default value in a parameter list, it's assumed that the default value was received by time you have any control to read or relay the value on again. There should be no problem in having all or none of your parameters in an interface having a default value, as the value is "auto-filled" if not explicitly provided. <br />
<br />
I just came across interfaces in PHP.. but I use them quite a bit in Java and Delphi. Currently building different DB wrappers, but all must enforce common access using a base class.. and also enforce that all of specific routines are implemented.</span>
</code></div>
  </div>
 </div>
 <a name="79110"></a>
 <div class="note">
  <strong class='user'>Docey</strong>
  <a href="#79110" class="date">11-Nov-2007 03:23</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Another note about default values in interfaces is that an class must implement at least the arguments as in the interface. that is: an implementation may have more arguments but not less if these additional arguments have an default value and thus can be called as declared in the interface.<br />
<br />
an litte example:<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">interface </span><span class="default">myInterface</span><span class="keyword">{<br />
&nbsp; public function </span><span class="default">setStuff</span><span class="keyword">(</span><span class="default">$id</span><span class="keyword">, </span><span class="default">$name</span><span class="keyword">);<br />
}<br />
<br />
class </span><span class="default">MyFirstClass </span><span class="keyword">implements </span><span class="default">myInterface</span><span class="keyword">{<br />
&nbsp; public function </span><span class="default">setStuff</span><span class="keyword">(</span><span class="default">$id</span><span class="keyword">, </span><span class="default">$name</span><span class="keyword">);<br />
}<br />
<br />
class </span><span class="default">MySecondClass </span><span class="keyword">implements </span><span class="default">myInterface</span><span class="keyword">{<br />
&nbsp;public function </span><span class="default">setStuff</span><span class="keyword">(</span><span class="default">$id</span><span class="keyword">, </span><span class="default">$name</span><span class="keyword">, </span><span class="default">$type</span><span class="keyword">); <br />
}<br />
<br />
class </span><span class="default">myThirdClass </span><span class="keyword">implements </span><span class="default">myInterface</span><span class="keyword">{<br />
&nbsp;public function </span><span class="default">setStuff</span><span class="keyword">(</span><span class="default">$id</span><span class="keyword">, </span><span class="default">$name</span><span class="keyword">, </span><span class="default">$type</span><span class="keyword">=</span><span class="default">0</span><span class="keyword">);<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
Here mySecondClass will print an fatal error while myThirdClass is just fine because myThirdClass::setStuff($id, $name); is valid and thus fullfills the interface requirements. an interface declares as set of requirement on how methods can be called and any class implementing an interface thus agrees that is will provide these methods and that they can be called as in the interface. adding additional arguments with default values is thus allowed because it does not violate the agreement that the method can be called as in the interface.</span>
</code></div>
  </div>
 </div>
 <a name="78293"></a>
 <div class="note">
  <strong class='user'>nrg1981 {AT} hotmail {DOT} com</strong>
  <a href="#78293" class="date">05-Oct-2007 06:48</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
In case you would want to, a child class can implement an interface:<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">interface </span><span class="default">water<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">makeItWet</span><span class="keyword">();<br />
}<br />
<br />
class </span><span class="default">weather<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">start</span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="string">'Here is some weather'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
class </span><span class="default">rain </span><span class="keyword">extends </span><span class="default">weather </span><span class="keyword">implements </span><span class="default">water <br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">makeItWet</span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="string">'It is wet'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="default">$a </span><span class="keyword">= new </span><span class="default">rain</span><span class="keyword">();<br />
echo </span><span class="default">$a</span><span class="keyword">-&gt;</span><span class="default">start</span><span class="keyword">();<br />
echo </span><span class="default">$a</span><span class="keyword">-&gt;</span><span class="default">makeItWet</span><span class="keyword">();<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="77954"></a>
 <div class="note">
  <strong class='user'>Hayley Watson</strong>
  <a href="#77954" class="date">21-Sep-2007 03:42</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If it's not already obvious, it's worth noticing that the parameters in the interface's method declaration do not have to have the same names as those in any of its implementations.<br />
<br />
More significantly, default argument values may be supplied for interface method parameters, and they have to be if you want to use default argument values in the implemented classes:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">interface </span><span class="default">isStuffable<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">getStuffed</span><span class="keyword">(</span><span class="default">$ratio</span><span class="keyword">=</span><span class="default">0.5</span><span class="keyword">);<br />
}<br />
<br />
class </span><span class="default">Turkey </span><span class="keyword">implements </span><span class="default">isStuffable<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">getStuffed</span><span class="keyword">(</span><span class="default">$stuffing</span><span class="keyword">=</span><span class="default">1</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// ....<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">}<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
Note that not only do the parameters have different names ($ratio and $stuffing), but their default values are free to be different as well. There doesn't seem to be any purpose to the interface's default argument value except as a dummy placeholder to show that there is a default (a class implementing isStuffable will not be able to implement methods with the signatures getStuffed(), getStuffed($a), or getStuffed($a,$b)).</span>
</code></div>
  </div>
 </div>
 <a name="77950"></a>
 <div class="note">
  <strong class='user'>Hayley Watson</strong>
  <a href="#77950" class="date">20-Sep-2007 10:34</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
On an incidental note, it is not necessary for the implementation of an interface method to use the same variable names for its parameters that were used in the interface declaration.<br />
<br />
More significantly, your interface method declarations can include default argument values. If you do, you must specify their implementations with default arguments, too. Just like the parameter names, the default argument values do not need to be the same. In fact, there doesn't seem to be any functionality to the one in the interface declaration at all beyond the fact that it is there.<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">interface </span><span class="default">isStuffed </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">getStuff</span><span class="keyword">(</span><span class="default">$something</span><span class="keyword">=</span><span class="default">17</span><span class="keyword">);<br />
}<br />
<br />
class </span><span class="default">oof </span><span class="keyword">implements </span><span class="default">isStuffed </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">getStuff</span><span class="keyword">(</span><span class="default">$a</span><span class="keyword">=</span><span class="default">42</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$a</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="default">$oof </span><span class="keyword">= new </span><span class="default">oof</span><span class="keyword">;<br />
<br />
echo </span><span class="default">$oof</span><span class="keyword">-&gt;</span><span class="default">getStuff</span><span class="keyword">();<br />
</span><span class="default">?&gt;<br />
</span><br />
Implementations that try to declare the method as getStuff(), getStuff($a), or getStuff($a,$b) will all trigger a fatal error.</span>
</code></div>
  </div>
 </div>
 <a name="77600"></a>
 <div class="note">
  <strong class='user'>php at wallbash dot com</strong>
  <a href="#77600" class="date">05-Sep-2007 07:39</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Please note that the sentence "Note: A class cannot implement two interfaces that share function names, since it would cause ambiguity." _really_ means that it is not possible to do something like:<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">interface </span><span class="default">IA </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">a</span><span class="keyword">();<br />
}<br />
<br />
interface </span><span class="default">IB </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">a</span><span class="keyword">();<br />
}<br />
<br />
class </span><span class="default">Test </span><span class="keyword">implements </span><span class="default">IA</span><span class="keyword">, </span><span class="default">IB </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public&nbsp; function </span><span class="default">a</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">"a"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
</span><span class="default">$o </span><span class="keyword">= new </span><span class="default">Test</span><span class="keyword">();<br />
</span><span class="default">$o</span><span class="keyword">-&gt;</span><span class="default">a</span><span class="keyword">();<br />
</span><span class="default">?&gt;<br />
</span><br />
lead to: <br />
PHP Fatal error:&nbsp; Can't inherit abstract function IB::a() (previously declared abstract in IA) <br />
<br />
It's important to note this because it is very unexpected behavior and renders many common Interface completly useless.</span>
</code></div>
  </div>
 </div>
 <a name="76206"></a>
 <div class="note">
  <strong class='user'>zedd at fadingtwilight dot net</strong>
  <a href="#76206" class="date">04-Jul-2007 09:42</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Regarding my previous note (04-Jul-2007 9:01):<br />
<br />
I noticed a minor but critical mistake in my explanation. After the link to the PHP manual page on class abstraction, I stated:<br />
<br />
"So by definition, you may only overload non-abstract methods."<br />
<br />
This is incorrect. This should read:<br />
<br />
"So by definition, you may only override non-abstract methods."<br />
<br />
Sorry for any confusion.</span>
</code></div>
  </div>
 </div>
 <a name="76205"></a>
 <div class="note">
  <strong class='user'>zedd at fadingtwilight dot net</strong>
  <a href="#76205" class="date">04-Jul-2007 09:01</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
prometheus at php-sparcle:<br />
<br />
Your code fails because you're effectively trying to do this:<br />
<br />
<span class="default">&lt;?php<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">abstract class </span><span class="default">IFoo<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">{<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; abstract public function </span><span class="default">Foo</span><span class="keyword">();<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; abstract class </span><span class="default">IBar </span><span class="keyword">extends </span><span class="default">IFoo<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">{<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// Fails; abstract method IFoo::Foo() must be defined in child and must match parent's definition<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">abstract public function </span><span class="default">Foo</span><span class="keyword">(</span><span class="default">$bar</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
</span><span class="default">?&gt;<br />
</span><br />
By definition, all methods in an interface are abstract. So the above code segment is equivalent to your interface definitions and results in the same error. Why? Let's have a look at the PHP manual. From the second paragraph on class abstraction:<br />
<br />
"When inheriting from an abstract class, all methods marked abstract in the parent's class declaration must be defined by the child;"<br />
<br />
<a href="@w{X7R9XFHY}" rel="nofollow" target="_blank">@w{X7R9XFHY}</a><br />
<br />
So by definition, you may only overload non-abstract methods.<br />
<br />
For example:<br />
<br />
<span class="default">&lt;?php<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">abstract class </span><span class="default">IFoo<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">{<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; public function </span><span class="default">Foo</span><span class="keyword">()<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// do something...<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">}<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; abstract class </span><span class="default">IBar </span><span class="keyword">extends </span><span class="default">IFoo<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">{&nbsp; &nbsp; &nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; public function </span><span class="default">Foo</span><span class="keyword">(</span><span class="default">$bar</span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// do something else...<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">}<br />
&nbsp;&nbsp;&nbsp; }<br />
</span><span class="default">?&gt;<br />
</span><br />
This can't be directly replicated with interfaces since you can't implement methods inside of an interface. They can only be implemented in a class or an abstract class.<br />
<br />
If you must use interfaces, the following accomplishes the same thing, but with two separate method names:<br />
<br />
<span class="default">&lt;?php<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">interface </span><span class="default">IFoo<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">{<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; public function </span><span class="default">Foo</span><span class="keyword">();<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; interface </span><span class="default">IBar </span><span class="keyword">extends </span><span class="default">IFoo<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">{<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; public function </span><span class="default">Bar</span><span class="keyword">(</span><span class="default">$bar</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; class </span><span class="default">FooBar </span><span class="keyword">implements </span><span class="default">IBar<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">{<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; public function </span><span class="default">Foo</span><span class="keyword">()<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// do something...<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">}<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; public function </span><span class="default">Bar</span><span class="keyword">(</span><span class="default">$bar</span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// do something else...<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">}<br />
&nbsp;&nbsp;&nbsp; }<br />
</span><span class="default">?&gt;<br />
</span><br />
If both methods need the same name, then you'll have to use non-abstract methods. In this case, interfaces aren't the right tool for the job. You'll want to use abstract classes (or just regular classes).</span>
</code></div>
  </div>
 </div>
 <a name="72312"></a>
 <div class="note">
  <strong class='user'>Maikel</strong>
  <a href="#72312" class="date">12-Jan-2007 03:07</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
if you want to implement an interface and in addition to use inheritance, first it uses “extends” and then “implements” example:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">MyChildClass </span><span class="keyword">extends </span><span class="default">MyParentClass </span><span class="keyword">implements </span><span class="default">MyInterface<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp; </span><span class="comment">// definition<br />
</span><span class="keyword">}<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="71608"></a>
 <div class="note">
  <strong class='user'>Chris AT w3style DOT co.uk</strong>
  <a href="#71608" class="date">07-Dec-2006 05:25</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Note that you can extend interfaces with other interfaces since under-the-hood they are just abstract classes:<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">interface </span><span class="default">Foo </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">doFoo</span><span class="keyword">();<br />
}<br />
<br />
interface </span><span class="default">Bar </span><span class="keyword">extends </span><span class="default">Foo </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">doBar</span><span class="keyword">();<br />
}<br />
<br />
class </span><span class="default">Zip </span><span class="keyword">implements </span><span class="default">Bar </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">doFoo</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">"Foo"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">doBar</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">"Bar"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="default">$zip </span><span class="keyword">= new </span><span class="default">Zip</span><span class="keyword">();<br />
</span><span class="default">$zip</span><span class="keyword">-&gt;</span><span class="default">doFoo</span><span class="keyword">();<br />
</span><span class="default">$zip</span><span class="keyword">-&gt;</span><span class="default">doBar</span><span class="keyword">();<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
This is quite useful when you're using interfaces for identity more than the rigidity it places upon an API.&nbsp; You can get the same result by implementing multiple interfaces.<br />
<br />
An example of where I've used this in the past is with EventListener objects ala Java's Swing UI.&nbsp; Some listeners are effectively the same thing but happen at different times therefore we can keep the same API but change the naming for clarity.</span>
</code></div>
  </div>
 </div>
 <a name="69467"></a>
 <div class="note">
  <strong class='user'>marasek AT telton POINT de</strong>
  <a href="#69467" class="date">06-Sep-2006 06:01</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
What is not mentioned in the manual is that you can use "self" to force object hinting on a method of the implementing class:<br />
<br />
Consider the following interface:<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">interface </span><span class="default">Comparable<br />
</span><span class="keyword">{function </span><span class="default">compare</span><span class="keyword">(</span><span class="default">self $compare</span><span class="keyword">);}<br />
</span><span class="default">?&gt;<br />
</span><br />
Which is then implemented:<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">class </span><span class="default">String </span><span class="keyword">implements </span><span class="default">Comparable<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; private </span><span class="default">$string</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">__construct</span><span class="keyword">(</span><span class="default">$string</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">string </span><span class="keyword">= </span><span class="default">$string</span><span class="keyword">;}<br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">compare</span><span class="keyword">(</span><span class="default">self $compare</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {return </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">string </span><span class="keyword">== </span><span class="default">$compare</span><span class="keyword">-&gt;</span><span class="default">string</span><span class="keyword">;}<br />
}<br />
<br />
class </span><span class="default">Integer </span><span class="keyword">implements </span><span class="default">Comparable<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; private </span><span class="default">$integer</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">__construct</span><span class="keyword">(</span><span class="default">$int</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">integer </span><span class="keyword">= </span><span class="default">$int</span><span class="keyword">;}<br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">compare</span><span class="keyword">(</span><span class="default">self $compare</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {return </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">integer </span><span class="keyword">== </span><span class="default">$compare</span><span class="keyword">-&gt;</span><span class="default">integer</span><span class="keyword">;}<br />
}<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
Comparing Integer with String will result in a fatal error, as it is not an instance of the same class:<br />
<br />
<span class="default">&lt;?php<br />
$first_int </span><span class="keyword">= new </span><span class="default">Integer</span><span class="keyword">(</span><span class="default">3</span><span class="keyword">);<br />
</span><span class="default">$second_int </span><span class="keyword">= new </span><span class="default">Integer</span><span class="keyword">(</span><span class="default">3</span><span class="keyword">);<br />
</span><span class="default">$first_string </span><span class="keyword">= new </span><span class="default">String</span><span class="keyword">(</span><span class="string">"foo"</span><span class="keyword">);<br />
</span><span class="default">$second_string </span><span class="keyword">= new </span><span class="default">String</span><span class="keyword">(</span><span class="string">"bar"</span><span class="keyword">);<br />
<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$first_int</span><span class="keyword">-&gt;</span><span class="default">compare</span><span class="keyword">(</span><span class="default">$second_int</span><span class="keyword">)); </span><span class="comment">// bool(true)<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$first_string</span><span class="keyword">-&gt;</span><span class="default">compare</span><span class="keyword">(</span><span class="default">$second_string</span><span class="keyword">)); </span><span class="comment">// bool(false)<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$first_string</span><span class="keyword">-&gt;</span><span class="default">compare</span><span class="keyword">(</span><span class="default">$second_int</span><span class="keyword">)); </span><span class="comment">// Fatal Error<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="68773"></a>
 <div class="note">
  <strong class='user'>vbolshov at rbc dot ru</strong>
  <a href="#68773" class="date">10-Aug-2006 02:34</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Consider the following:<br />
[vbolshov@localhost tmp]$ cat t.php<br />
<span class="default">&lt;?php<br />
<br />
error_reporting</span><span class="keyword">(</span><span class="default">E_ALL </span><span class="keyword">| </span><span class="default">E_STRICT</span><span class="keyword">);<br />
<br />
interface </span><span class="default">i </span><span class="keyword">{<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; function </span><span class="default">f</span><span class="keyword">(</span><span class="default">$arg</span><span class="keyword">);<br />
}<br />
class </span><span class="default">c </span><span class="keyword">implements </span><span class="default">i </span><span class="keyword">{<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; function </span><span class="default">f</span><span class="keyword">(</span><span class="default">$arg</span><span class="keyword">, </span><span class="default">$arg2 </span><span class="keyword">= </span><span class="default">null</span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
}<br />
</span><span class="default">?&gt;<br />
</span>[vbolshov@localhost tmp]$ php t.php<br />
[vbolshov@localhost tmp]$<br />
<br />
PHP doesn't generate a Fatal Error in this case, although the method declaration in the class differs from that in the interface. This situation doesn't seem good to me: I'd prefer classes being strictly bound to their interfaces.</span>
</code></div>
  </div>
 </div>
 <a name="58147"></a>
 <div class="note">
  <strong class='user'>spiritus.canis at gmail dot com</strong>
  <a href="#58147" class="date">25-Oct-2005 10:45</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Regarding the example by cyrille.berliat:<br />
<br />
This is not a problem and is consistent with other languages.&nbsp; You'd just want to use inheritance like so:<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">class </span><span class="default">AbstractClass </span><span class="keyword">{<br />
&nbsp;&nbsp; public function </span><span class="default">__ToString </span><span class="keyword">( ) { return </span><span class="string">'Here I am'</span><span class="keyword">; }<br />
}<br />
<br />
class </span><span class="default">DescendantClass </span><span class="keyword">extends </span><span class="default">AbstractClass </span><span class="keyword">{}<br />
<br />
interface </span><span class="default">MyInterface </span><span class="keyword">{<br />
&nbsp;&nbsp; public function </span><span class="default">Hello </span><span class="keyword">( </span><span class="default">AbstractClass $obj </span><span class="keyword">);<br />
}<br />
<br />
class </span><span class="default">MyClassOne </span><span class="keyword">implements </span><span class="default">MyInterface </span><span class="keyword">{<br />
<br />
&nbsp;&nbsp; public function </span><span class="default">Hello </span><span class="keyword">( </span><span class="default">AbstractClass $obj </span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; echo </span><span class="default">$obj</span><span class="keyword">;<br />
&nbsp;&nbsp; }<br />
} </span><span class="comment">// Will work as Interface Satisfied<br />
<br />
</span><span class="default">$myDC </span><span class="keyword">= new </span><span class="default">DescendantClass</span><span class="keyword">() ;<br />
</span><span class="default">MyClassOne</span><span class="keyword">::</span><span class="default">Hello</span><span class="keyword">( </span><span class="default">$myDC </span><span class="keyword">) ;<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="57866"></a>
 <div class="note">
  <strong class='user'>cyrille.berliat[no spam]free.fr</strong>
  <a href="#57866" class="date">17-Oct-2005 02:29</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Interfaces and Type Hinting can be used but not with Inherintance in the same time :<br />
<br />
&lt;?<br />
<br />
class AbstractClass<br />
{<br />
&nbsp;&nbsp;&nbsp; public function __ToString ( ) { return 'Here I\'m I'; }<br />
}<br />
<br />
class DescendantClass extends AbstractClass<br />
{<br />
<br />
}<br />
<br />
interface MyI<br />
{<br />
&nbsp;&nbsp;&nbsp; public function Hello ( AbstractClass $obj );<br />
}<br />
<br />
class MyClassOne implements MyI<br />
{<br />
&nbsp;&nbsp;&nbsp; public function Hello ( AbstractClass $obj ) <br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo $obj;<br />
&nbsp;&nbsp;&nbsp; }<br />
} // Will work as Interface Satisfied<br />
<br />
class MyClassTwo implements MyI<br />
{<br />
&nbsp;&nbsp;&nbsp; public function Hello ( DescendantClass $obj ) <br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo $obj;<br />
&nbsp;&nbsp;&nbsp; }<br />
} // Will output a fatal error because Interfaces don't support Inherintance in TypeHinting<br />
<br />
//Fatal error: Declaration of MyClassTwo::hello() must be compatible with that of MyI::hello()<br />
<br />
?&gt;<br />
<br />
Something a little bit bad in PHP 5.0.4 :)</span>
</code></div>
  </div>
 </div>
 <a name="57389"></a>
 <div class="note">
  <strong class='user'>darealremco at msn dot com</strong>
  <a href="#57389" class="date">02-Oct-2005 12:53</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
To two notes below: There is one situation where classes and interfaces can be used interchangeably. In function definitions you can define parameter types to be classes or interfaces. If this was not so then there would not be much use for interfaces at all.</span>
</code></div>
  </div>
 </div>
 <a name="55721"></a>
 <div class="note">
  <strong class='user'>warhog at warhog dot net</strong>
  <a href="#55721" class="date">11-Aug-2005 08:35</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
on the post below:<br />
<br />
An interface is in fact the same like an abstract class containing abstract methods, that's why interfaces share the same namespace as classes and why therefore "real" classes cannot have the same name as interfaces.</span>
</code></div>
  </div>
 </div>
 <a name="55237"></a>
 <div class="note">
  <strong class='user'>marcus at synchromedia dot co dot uk</strong>
  <a href="#55237" class="date">28-Jul-2005 04:11</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Classes and interface names share a common name space, so you can't have a class and an interface with the same name, even though the two can never be used ambiguously (i.e. there are no circumstances in which a class and an interface can be used interchangeably). e.g. this will not work:<br />
<br />
interface foo {<br />
public function bling();<br />
}<br />
<br />
class foo implements foo {<br />
public function bling() {<br />
}<br />
}<br />
<br />
You will get a 'Cannot redeclare class' error, even though it's only been declared as a class once.</span>
</code></div>
  </div>
 </div>
 <a name="52531"></a>
 <div class="note">
  <strong class='user'>tobias_demuth at web dot de</strong>
  <a href="#52531" class="date">04-May-2005 02:21</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The statement, that you have to implement _all_ methods of an interface has not to be taken that seriously, at least if you declare an abstract class and want to force the inheriting subclasses to implement the interface.<br />
Just leave out all methods that should be implemented by the subclasses. But never write something like this:<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">interface </span><span class="default">Foo </span><span class="keyword">{<br />
<br />
&nbsp;&nbsp; &nbsp;&nbsp; function </span><span class="default">bar</span><span class="keyword">();<br />
<br />
}<br />
<br />
abstract class </span><span class="default">FooBar </span><span class="keyword">implements </span><span class="default">Foo </span><span class="keyword">{<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; abstract function </span><span class="default">bar</span><span class="keyword">(); </span><span class="comment">// just for making clear, that this<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; // method has to be implemented<br />
<br />
</span><span class="keyword">}<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
This will end up with the following error-message:<br />
<br />
Fatal error: Can't inherit abstract function Foo::bar() (previously declared abstract in FooBar) in path/to/file on line anylinenumber</span>
</code></div>
  </div>
 </div>
 <a name="50362"></a>
 <div class="note">
  <strong class='user'>erik dot zoltan at msn dot com</strong>
  <a href="#50362" class="date">25-Feb-2005 10:43</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
When should you use interfaces?&nbsp; What are they good for? <br />
Here are two examples.&nbsp; <br />
<br />
1. Interfaces are an excellent way to implement reusability.&nbsp; <br />
You can create a general interface for a number of situations <br />
(such as a save to/load from disk interface.)&nbsp; You can then <br />
implement the interface in a variety of different ways (e.g. for <br />
formats such as tab delimited ASCII, XML and a database.)&nbsp; <br />
You can write code that asks the object to "save itself to <br />
disk" without having to worry what that means for the object <br />
in question.&nbsp; One object might save itself to the database, <br />
another to an XML and you can change this behavior over <br />
time without having to rewrite the calling code.&nbsp; <br />
<br />
This allows you to write reusable calling code that can work <br />
for any number of different objects -- you don't need to know <br />
what kind of object it is, as long as it obeys the common <br />
interface.&nbsp; <br />
<br />
2. Interfaces can also promote gradual evolution.&nbsp; On a <br />
recent project I had some very complicated work to do and I <br />
didn't know how to implement it.&nbsp; I could think of a "basic" <br />
implementation but I knew I would have to change it later.&nbsp; <br />
So I created interfaces in each of these cases, and created <br />
at least one "basic" implementation of the interface that <br />
was "good enough for now" even though I knew it would have <br />
to change later.&nbsp; <br />
<br />
When I came back to make the changes, I was able to create <br />
some new implementations of these interfaces that added the <br />
extra features I needed.&nbsp; Some of my classes still used <br />
the "basic" implementations, but others needed the <br />
specialized ones.&nbsp; I was able to add the new features to the <br />
objects themselves without rewriting the calling code in most <br />
cases.&nbsp; It was easy to evolve my code in this way because <br />
the changes were mostly isolated -- they didn't spread all <br />
over the place like you might expect.</span>
</code></div>
  </div>
 </div>
 <a name="49218"></a>
 <div class="note">
  <strong class='user'>mat.wilmots (at) wanadoo (dot) fr</strong>
  <a href="#49218" class="date">20-Jan-2005 06:22</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
interfaces support multiple inheritance<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">interface </span><span class="default">SQL_Result </span><span class="keyword">extends </span><span class="default">SeekableIterator</span><span class="keyword">, </span><span class="default">Countable<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">// new stuff<br />
</span><span class="keyword">}<br />
<br />
abstract class </span><span class="default">SQL_Result_Common<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">// just because that's what one would do in reality, generic implementation<br />
</span><span class="keyword">}<br />
<br />
class </span><span class="default">SQL_Result_mysql </span><span class="keyword">extends </span><span class="default">SQL_Result_Common </span><span class="keyword">implements </span><span class="default">SQL_Result<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp; </span><span class="comment">// actual implementation<br />
</span><span class="keyword">}<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
This code raises a fatal error because SQL_Result_mysql doesn't implement the abstract methods of SeekableIterator (6) + Countable (1)</span>
</code></div>
  </div>
 </div>
 <a name="47759"></a>
 <div class="note">
  <strong class='user'>russ dot collier at gmail dot com</strong>
  <a href="#47759" class="date">28-Nov-2004 10:24</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
You can also specify class constants in interfaces as well (similar to specifying 'public static final' fields in Java interfaces):<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">interface </span><span class="default">FooBar<br />
</span><span class="keyword">{<br />
<br />
&nbsp;&nbsp;&nbsp; const </span><span class="default">SOME_CONSTANT </span><span class="keyword">= </span><span class="string">'I am an interface constant'</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">doStuff</span><span class="keyword">();<br />
<br />
}<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
Then you can access the constant by referring to the interface name, or an implementing class, (again similar to Java) e.g.:<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">class </span><span class="default">Baz </span><span class="keyword">implements </span><span class="default">FooBar<br />
</span><span class="keyword">{<br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">//....<br />
<br />
</span><span class="keyword">}<br />
<br />
print </span><span class="default">Baz</span><span class="keyword">::</span><span class="default">SOME_CONSTANT</span><span class="keyword">;<br />
print </span><span class="default">FooBar</span><span class="keyword">::</span><span class="default">SOME_CONSTANT</span><span class="keyword">;<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
Both of the last print statements will output the same thing: the value of FooBar::SOME_CONSTANT</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=language.oop5.interfaces&amp;redirect=@w{VQ9ZFBSA}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.oop5.interfaces&amp;redirect=@w{VQ9ZFBSA}">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/language.oop5.interfaces.php">show source</a> |
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