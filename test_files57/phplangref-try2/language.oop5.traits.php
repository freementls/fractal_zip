<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Traits - Manual</title>
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
 <link rel="prev" href="language.oop5.interfaces.php" />
 <link rel="next" href="language.oop5.overloading.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/oop5.traits" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/language.oop5.traits.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/language.oop5.traits.php" />
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
 <li class="active"><a href="language.oop5.traits.php">Traits</a></li>
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
  <a href="language.oop5.overloading.php">Overloading<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.oop5.interfaces.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Object Interfaces</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.oop5.traits.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/language.oop5.traits.php">Brazilian Portuguese</option>
    <option value="zh/language.oop5.traits.php">Chinese (Simplified)</option>
    <option value="fr/language.oop5.traits.php">French</option>
    <option value="de/language.oop5.traits.php">German</option>
    <option value="ja/language.oop5.traits.php">Japanese</option>
    <option value="pl/language.oop5.traits.php">Polish</option>
    <option value="ro/language.oop5.traits.php">Romanian</option>
    <option value="ru/language.oop5.traits.php">Russian</option>
    <option value="fa/language.oop5.traits.php">Persian</option>
    <option value="es/language.oop5.traits.php">Spanish</option>
    <option value="tr/language.oop5.traits.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="language.oop5.traits" class="sect1">
  <h2 class="title">Traits</h2>
  <p class="para">
   As of PHP 5.4.0, PHP implements a method of code reuse called Traits.
  </p>
  <p class="para">
   Traits is a mechanism for code reuse in single inheritance languages such as
   PHP. A Trait is intended to reduce some limitations of single inheritance by
   enabling a developer to reuse sets of methods freely in several independent
   classes living in different class hierarchies. The semantics of the combination
   of Traits and classes is defined in a way which reduces complexity, and avoids
   the typical problems associated with multiple inheritance and Mixins.
  </p>
  <p class="para">
   A Trait is similar to a class, but only intended to group functionality in a
   fine-grained and consistent way. It is not possible to instantiate a Trait on
   its own. It is an addition to traditional inheritance and enables horizontal
   composition of behavior; that is, the application of class members without
   requiring inheritance.
  </p>
  
  <div class="example" id="language.oop5.traits.basicexample">
    <p><strong>Example #1 Trait example</strong></p>
    <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br />trait&nbsp;ezcReflectionReturnInfo&nbsp;</span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;function&nbsp;</span><span style="color: #0000BB">getReturnType</span><span style="color: #007700">()&nbsp;{&nbsp;</span><span style="color: #FF8000">/*1*/&nbsp;</span><span style="color: #007700">}<br />&nbsp;&nbsp;&nbsp;&nbsp;function&nbsp;</span><span style="color: #0000BB">getReturnDescription</span><span style="color: #007700">()&nbsp;{&nbsp;</span><span style="color: #FF8000">/*2*/&nbsp;</span><span style="color: #007700">}<br />}<br /><br />class&nbsp;</span><span style="color: #0000BB">ezcReflectionMethod&nbsp;</span><span style="color: #007700">extends&nbsp;</span><span style="color: #0000BB">ReflectionMethod&nbsp;</span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;use&nbsp;</span><span style="color: #0000BB">ezcReflectionReturnInfo</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">/*&nbsp;...&nbsp;*/<br /></span><span style="color: #007700">}<br /><br />class&nbsp;</span><span style="color: #0000BB">ezcReflectionFunction&nbsp;</span><span style="color: #007700">extends&nbsp;</span><span style="color: #0000BB">ReflectionFunction&nbsp;</span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;use&nbsp;</span><span style="color: #0000BB">ezcReflectionReturnInfo</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">/*&nbsp;...&nbsp;*/<br /></span><span style="color: #007700">}<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

   </div>
  
  <div class="sect2" id="language.oop5.traits.precedence">
   <h3 class="title">Precedence</h3>
   <p class="para">
    An inherited member from a base class is overridden by a member inserted
    by a Trait. The precedence order is that members from the current class
    override Trait methods, which in return override inherited methods.
   </p>
   <div class="example" id="language.oop5.traits.precedence.examples.ex1">
    <p><strong>Example #2 Precedence Order Example</strong></p>
    <div class="example-contents"><p>
     An inherited method from a base class is overridden by the
     method inserted into MyHelloWorld from the SayWorld Trait. The behavior is
     the same for methods defined in the MyHelloWorld class. The precedence order
     is that methods from the current class override Trait methods, which in
     turn override methods from the base class.
    </p></div>
    <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">class&nbsp;</span><span style="color: #0000BB">Base&nbsp;</span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">sayHello</span><span style="color: #007700">()&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">'Hello&nbsp;'</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br /><br /></span><span style="color: #0000BB">trait&nbsp;SayWorld&nbsp;</span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">sayHello</span><span style="color: #007700">()&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">parent</span><span style="color: #007700">::</span><span style="color: #0000BB">sayHello</span><span style="color: #007700">();<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">'World!'</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br /><br />class&nbsp;</span><span style="color: #0000BB">MyHelloWorld&nbsp;</span><span style="color: #007700">extends&nbsp;</span><span style="color: #0000BB">Base&nbsp;</span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;use&nbsp;</span><span style="color: #0000BB">SayWorld</span><span style="color: #007700">;<br />}<br /><br /></span><span style="color: #0000BB">$o&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">MyHelloWorld</span><span style="color: #007700">();<br /></span><span style="color: #0000BB">$o</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">sayHello</span><span style="color: #007700">();<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

    <div class="example-contents"><p>The above example will output:</p></div>
    <div class="example-contents screen">
<div class="cdata"><pre>
Hello World!
</pre></div>
    </div>
   </div>
   <div class="example" id="language.oop5.traits.precedence.examples.ex2">
    <p><strong>Example #3 Alternate Precedence Order Example</strong></p>
    <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br />trait&nbsp;HelloWorld&nbsp;</span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">sayHello</span><span style="color: #007700">()&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">'Hello&nbsp;World!'</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br /><br />class&nbsp;</span><span style="color: #0000BB">TheWorldIsNotEnough&nbsp;</span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;use&nbsp;</span><span style="color: #0000BB">HelloWorld</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">sayHello</span><span style="color: #007700">()&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">'Hello&nbsp;Universe!'</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br /><br /></span><span style="color: #0000BB">$o&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">TheWorldIsNotEnough</span><span style="color: #007700">();<br /></span><span style="color: #0000BB">$o</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">sayHello</span><span style="color: #007700">();<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

    <div class="example-contents"><p>The above example will output:</p></div>
    <div class="example-contents screen">
<div class="cdata"><pre>
Hello Universe!
</pre></div>
    </div>
   </div>
  </div>
  
  <div class="sect2" id="language.oop5.traits.multiple">
   <h3 class="title">Multiple Traits</h3>
   <p class="para">
    Multiple Traits can be inserted into a class by listing them in the use
    statement, separated by commas.
   </p>
   <div class="example" id="language.oop5.traits.multiple.ex1">
    <p><strong>Example #4 Multiple Traits Usage</strong></p>
    <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br />trait&nbsp;Hello&nbsp;</span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">sayHello</span><span style="color: #007700">()&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">'Hello&nbsp;'</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br /><br /></span><span style="color: #0000BB">trait&nbsp;World&nbsp;</span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">sayWorld</span><span style="color: #007700">()&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">'World'</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br /><br />class&nbsp;</span><span style="color: #0000BB">MyHelloWorld&nbsp;</span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;use&nbsp;</span><span style="color: #0000BB">Hello</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">World</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">sayExclamationMark</span><span style="color: #007700">()&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">'!'</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br /><br /></span><span style="color: #0000BB">$o&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">MyHelloWorld</span><span style="color: #007700">();<br /></span><span style="color: #0000BB">$o</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">sayHello</span><span style="color: #007700">();<br /></span><span style="color: #0000BB">$o</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">sayWorld</span><span style="color: #007700">();<br /></span><span style="color: #0000BB">$o</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">sayExclamationMark</span><span style="color: #007700">();<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

    <div class="example-contents"><p>The above example will output:</p></div>
    <div class="example-contents screen">
<div class="cdata"><pre>
Hello World!
</pre></div>
    </div>
   </div>
  </div>
  
  <div class="sect2" id="language.oop5.traits.conflict">
   <h3 class="title">Conflict Resolution</h3>
   <p class="para">
    If two Traits insert a method with the same name, a fatal error is produced,
    if the conflict is not explicitly resolved.
   </p>
   <p class="para">
    To resolve naming conflicts between Traits used in the same class,
    the <em>insteadof</em> operator needs to be used to chose exactly
    one of the conflicting methods.
   </p>
   <p class="para">
    Since this only allows one to exclude methods, the <em>as</em>
    operator can be used to allow the inclusion of one of the conflicting
    methods under another name. 
   </p>
   <div class="example" id="language.oop5.traits.conflict.ex1">
    <p><strong>Example #5 Conflict Resolution</strong></p>
    <div class="example-contents"><p>
      In this example, Talker uses the traits A and B.
      Since A and B have conflicting methods, it defines to use
      the variant of smallTalk from trait B, and the variant of bigTalk from
      trait A.
    </p></div>
    <div class="example-contents"><p>
      The Aliased_Talker makes use of the <em>as</em> operator
      to be able to use B&#039;s bigTalk implementation under an additional alias
      <em>talk</em>.
    </p></div> 
    <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br />trait&nbsp;A&nbsp;</span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">smallTalk</span><span style="color: #007700">()&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">'a'</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">bigTalk</span><span style="color: #007700">()&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">'A'</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br /><br /></span><span style="color: #0000BB">trait&nbsp;B&nbsp;</span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">smallTalk</span><span style="color: #007700">()&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">'b'</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">bigTalk</span><span style="color: #007700">()&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">'B'</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br /><br />class&nbsp;</span><span style="color: #0000BB">Talker&nbsp;</span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;use&nbsp;</span><span style="color: #0000BB">A</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">B&nbsp;</span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">B</span><span style="color: #007700">::</span><span style="color: #0000BB">smallTalk&nbsp;insteadof&nbsp;A</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">A</span><span style="color: #007700">::</span><span style="color: #0000BB">bigTalk&nbsp;insteadof&nbsp;B</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br /><br />class&nbsp;</span><span style="color: #0000BB">Aliased_Talker&nbsp;</span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;use&nbsp;</span><span style="color: #0000BB">A</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">B&nbsp;</span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">B</span><span style="color: #007700">::</span><span style="color: #0000BB">smallTalk&nbsp;insteadof&nbsp;A</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">A</span><span style="color: #007700">::</span><span style="color: #0000BB">bigTalk&nbsp;insteadof&nbsp;B</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">B</span><span style="color: #007700">::</span><span style="color: #0000BB">bigTalk&nbsp;</span><span style="color: #007700">as&nbsp;</span><span style="color: #0000BB">talk</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

   </div>
  </div>
  
  <div class="sect2" id="language.oop5.traits.visibility">
   <h3 class="title">Changing Method Visibility</h3>
   <p class="para">
    Using the <em>as</em> syntax, one can also adjust the visibility
    of the method in the exhibiting class.
   </p>
   <div class="example" id="language.oop5.traits.visibility.ex1">
    <p><strong>Example #6 Changing Method Visibility</strong></p>
    <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br />trait&nbsp;HelloWorld&nbsp;</span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">sayHello</span><span style="color: #007700">()&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">'Hello&nbsp;World!'</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br /><br /></span><span style="color: #FF8000">//&nbsp;Change&nbsp;visibility&nbsp;of&nbsp;sayHello<br /></span><span style="color: #007700">class&nbsp;</span><span style="color: #0000BB">MyClass1&nbsp;</span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;use&nbsp;</span><span style="color: #0000BB">HelloWorld&nbsp;</span><span style="color: #007700">{&nbsp;</span><span style="color: #0000BB">sayHello&nbsp;</span><span style="color: #007700">as&nbsp;protected;&nbsp;}<br />}<br /><br /></span><span style="color: #FF8000">//&nbsp;Alias&nbsp;method&nbsp;with&nbsp;changed&nbsp;visibility<br />//&nbsp;sayHello&nbsp;visibility&nbsp;not&nbsp;changed<br /></span><span style="color: #007700">class&nbsp;</span><span style="color: #0000BB">MyClass2&nbsp;</span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;use&nbsp;</span><span style="color: #0000BB">HelloWorld&nbsp;</span><span style="color: #007700">{&nbsp;</span><span style="color: #0000BB">sayHello&nbsp;</span><span style="color: #007700">as&nbsp;private&nbsp;</span><span style="color: #0000BB">myPrivateHello</span><span style="color: #007700">;&nbsp;}<br />}<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

   </div>
  </div>
  
  <div class="sect2" id="language.oop5.traits.composition">
   <h3 class="title">Traits Composed from Traits</h3>
   <p class="para">
    Just as classes can make use of traits, so can other traits. By using one
    or more traits in a trait definition, it can be composed partially or
    entirely of the members defined in those other traits.
   </p>
   <div class="example" id="language.oop5.traits.composition.ex1">
    <p><strong>Example #7 Traits Composed from Traits</strong></p>
    <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br />trait&nbsp;Hello&nbsp;</span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">sayHello</span><span style="color: #007700">()&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">'Hello&nbsp;'</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br /><br /></span><span style="color: #0000BB">trait&nbsp;World&nbsp;</span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">sayWorld</span><span style="color: #007700">()&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">'World!'</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br /><br /></span><span style="color: #0000BB">trait&nbsp;HelloWorld&nbsp;</span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;use&nbsp;</span><span style="color: #0000BB">Hello</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">World</span><span style="color: #007700">;<br />}<br /><br />class&nbsp;</span><span style="color: #0000BB">MyHelloWorld&nbsp;</span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;use&nbsp;</span><span style="color: #0000BB">HelloWorld</span><span style="color: #007700">;<br />}<br /><br /></span><span style="color: #0000BB">$o&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">MyHelloWorld</span><span style="color: #007700">();<br /></span><span style="color: #0000BB">$o</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">sayHello</span><span style="color: #007700">();<br /></span><span style="color: #0000BB">$o</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">sayWorld</span><span style="color: #007700">();<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

    <div class="example-contents"><p>The above example will output:</p></div>
    <div class="example-contents screen">
<div class="cdata"><pre>
Hello World!
</pre></div>
    </div>
   </div>
  </div>
  
  <div class="sect2" id="language.oop5.traits.abstract">
   <h3 class="title">Abstract Trait Members</h3>
   <p class="para">
    Traits support the use of abstract methods in order to impose requirements
    upon the exhibiting class.
   </p>
   <div class="example" id="language.oop5.traits.abstract.ex1">
    <p><strong>Example #8 Express Requirements by Abstract Methods</strong></p>
    <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br />trait&nbsp;Hello&nbsp;</span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">sayHelloWorld</span><span style="color: #007700">()&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">'Hello'</span><span style="color: #007700">.</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">getWorld</span><span style="color: #007700">();<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />&nbsp;&nbsp;&nbsp;&nbsp;abstract&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">getWorld</span><span style="color: #007700">();<br />}<br /><br />class&nbsp;</span><span style="color: #0000BB">MyHelloWorld&nbsp;</span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;private&nbsp;</span><span style="color: #0000BB">$world</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;use&nbsp;</span><span style="color: #0000BB">Hello</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">getWorld</span><span style="color: #007700">()&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;return&nbsp;</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">world</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">setWorld</span><span style="color: #007700">(</span><span style="color: #0000BB">$val</span><span style="color: #007700">)&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">world&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">$val</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

   </div>
  </div>
  
  <div class="sect2" id="language.oop5.traits.static">
   <h3 class="title">Static Trait Members</h3>
   <p class="para">
    Static variables can be referred to in trait methods, but cannot be
    defined by the trait. Traits can, however, define static methods for
    the exhibiting class.
   </p>
   <div class="example" id="language.oop5.traits.static.ex1">
    <p><strong>Example #9 Static Variables</strong></p>
    <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br />trait&nbsp;Counter&nbsp;</span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">inc</span><span style="color: #007700">()&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;static&nbsp;</span><span style="color: #0000BB">$c&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">0</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$c&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">$c&nbsp;</span><span style="color: #007700">+&nbsp;</span><span style="color: #0000BB">1</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"</span><span style="color: #0000BB">$c</span><span style="color: #DD0000">\n"</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br /><br />class&nbsp;</span><span style="color: #0000BB">C1&nbsp;</span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;use&nbsp;</span><span style="color: #0000BB">Counter</span><span style="color: #007700">;<br />}<br /><br />class&nbsp;</span><span style="color: #0000BB">C2&nbsp;</span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;use&nbsp;</span><span style="color: #0000BB">Counter</span><span style="color: #007700">;<br />}<br /><br /></span><span style="color: #0000BB">$o&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">C1</span><span style="color: #007700">();&nbsp;</span><span style="color: #0000BB">$o</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">inc</span><span style="color: #007700">();&nbsp;</span><span style="color: #FF8000">//&nbsp;echo&nbsp;1<br /></span><span style="color: #0000BB">$p&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">C2</span><span style="color: #007700">();&nbsp;</span><span style="color: #0000BB">$p</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">inc</span><span style="color: #007700">();&nbsp;</span><span style="color: #FF8000">//&nbsp;echo&nbsp;1<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

   </div>
   <div class="example" id="language.oop5.traits.static.ex2">
    <p><strong>Example #10 Static Methods</strong></p>
    <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br />trait&nbsp;StaticExample&nbsp;</span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;static&nbsp;function&nbsp;</span><span style="color: #0000BB">doSomething</span><span style="color: #007700">()&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;return&nbsp;</span><span style="color: #DD0000">'Doing&nbsp;something'</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br /><br />class&nbsp;</span><span style="color: #0000BB">Example&nbsp;</span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;use&nbsp;</span><span style="color: #0000BB">StaticExample</span><span style="color: #007700">;<br />}<br /><br /></span><span style="color: #0000BB">Example</span><span style="color: #007700">::</span><span style="color: #0000BB">doSomething</span><span style="color: #007700">();<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

   </div>
  </div>
  
  <div class="sect2" id="language.oop5.traits.properties">
   <h3 class="title">Properties</h3>
   <p class="para">
    Traits can also define properties.
   </p>
   <div class="example" id="language.oop5.traits.properties.example">
    <p><strong>Example #11 Defining Properties</strong></p>
    <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br />trait&nbsp;PropertiesTrait&nbsp;</span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;</span><span style="color: #0000BB">$x&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">1</span><span style="color: #007700">;<br />}<br /><br />class&nbsp;</span><span style="color: #0000BB">PropertiesExample&nbsp;</span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;use&nbsp;</span><span style="color: #0000BB">PropertiesTrait</span><span style="color: #007700">;<br />}<br /><br /></span><span style="color: #0000BB">$example&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">PropertiesExample</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">$example</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">x</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

   </div>
   <p class="para">
    If a trait defines a property then a class can not define a property with
    the same name, otherwise an error is issued. It is an
    <strong><code>E_STRICT</code></strong> if the class definition is compatible (same
    visibility and initial value) or fatal error otherwise.
   </p>
   <div class="example" id="language.oop5.traits.properties.conflicts">
    <p><strong>Example #12 Conflict Resolution</strong></p>
    <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br />trait&nbsp;PropertiesTrait&nbsp;</span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;</span><span style="color: #0000BB">$same&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">true</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;</span><span style="color: #0000BB">$different&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">false</span><span style="color: #007700">;<br />}<br /><br />class&nbsp;</span><span style="color: #0000BB">PropertiesExample&nbsp;</span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;use&nbsp;</span><span style="color: #0000BB">PropertiesTrait</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;</span><span style="color: #0000BB">$same&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">true</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;Strict&nbsp;Standards<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">public&nbsp;</span><span style="color: #0000BB">$different&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">true</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;Fatal&nbsp;error<br /></span><span style="color: #007700">}<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

   </div>
  </div>

 </div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="language.oop5.overloading.php">Overloading<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.oop5.interfaces.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Object Interfaces</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.oop5.traits.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=language.oop5.traits&amp;redirect=http://www.php.net/manual/en/language.oop5.traits.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.oop5.traits&amp;redirect=http://www.php.net/manual/en/language.oop5.traits.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Traits</strong>
 </div><div id="allnotes">
 <a name="109508"></a>
 <div class="note">
  <strong class='user'>t8 at  AT pobox dot com</strong>
  <a href="#109508" class="date">23-Jul-2012 08:17</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Another difference with traits vs inheritance is that methods defined in traits can access methods and properties of the class they're used in, including private ones.<br />
<br />
For example:<br />
<span class="default">&lt;?php<br />
trait MyTrait<br />
</span><span class="keyword">{<br />
&nbsp; protected function </span><span class="default">accessVar</span><span class="keyword">()<br />
&nbsp; {<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">var</span><span class="keyword">;<br />
&nbsp; }<br />
<br />
}<br />
<br />
class </span><span class="default">TraitUser<br />
</span><span class="keyword">{<br />
&nbsp; use </span><span class="default">MyTrait</span><span class="keyword">;<br />
<br />
&nbsp; private </span><span class="default">$var </span><span class="keyword">= </span><span class="string">'var'</span><span class="keyword">;<br />
<br />
&nbsp; public function </span><span class="default">getVar</span><span class="keyword">()<br />
&nbsp; {<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">accessVar</span><span class="keyword">();<br />
&nbsp; }<br />
}<br />
<br />
</span><span class="default">$t </span><span class="keyword">= new </span><span class="default">TraitUser</span><span class="keyword">();<br />
echo </span><span class="default">$t</span><span class="keyword">-&gt;</span><span class="default">getVar</span><span class="keyword">(); </span><span class="comment">// -&gt; 'var'&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="109448"></a>
 <div class="note">
  <strong class='user'>karolis at iwsolutions dot ie</strong>
  <a href="#109448" class="date">18-Jul-2012 09:16</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Not very obvious but trait methods can be called as if they were defined as static methods in a regular class<br />
<br />
<span class="default">&lt;?php<br />
trait Foo </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">bar</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="string">'baz'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
echo </span><span class="default">Foo</span><span class="keyword">::</span><span class="default">bar</span><span class="keyword">(),</span><span class="string">"\\n"</span><span class="keyword">;<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="108604"></a>
 <div class="note">
  <strong class='user'>ryanhanekamp at yahoo dot com</strong>
  <a href="#108604" class="date">10-May-2012 12:14</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Using AS on a __construct method (and maybe other magic methods) is really, really bad. The problem is that is doesn't throw any errors, at least in 5.4.0. It just sporadically resets the connection. And when I say "sporadically," I mean that arbitrary changes in the preceding code can cause the browser connection to reset or not reset *consistently*, so that subsequent page refreshes will continue to hang, crash, or display perfectly in the same fashion as the first load of the page after a change in the preceding code, but the slightest change in the code can change this state. (I believe it is related to precise memory usage.)<br />
<br />
I've spent a good part of the day chasing down this one, and weeping every time commenting or even moving a completely arbitrary section of code would cause the connection to reset. It was just by luck that I decided to comment the<br />
<br />
"__construct as primitiveObjectConstruct"<br />
<br />
line and then the crashes went away entirely.<br />
<br />
My parent trait constructor was very simple, so my fix this time was to copy the functionality into the child __construct. I'm not sure how I'll approach a more complicated parent trait constructor.</span>
</code></div>
  </div>
 </div>
 <a name="108293"></a>
 <div class="note">
  <strong class='user'>ryan at derokorian dot com</strong>
  <a href="#108293" class="date">15-Apr-2012 02:03</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Simple singleton trait.<br />
<br />
<span class="default">&lt;?php<br />
<br />
trait singleton </span><span class="keyword">{&nbsp; &nbsp; <br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">/**<br />
&nbsp;&nbsp; &nbsp; * private construct, generally defined by using class<br />
&nbsp;&nbsp; &nbsp; */<br />
&nbsp;&nbsp;&nbsp; //private function __construct() {}<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">public static function </span><span class="default">getInstance</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; static </span><span class="default">$_instance </span><span class="keyword">= </span><span class="default">NULL</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$class </span><span class="keyword">= </span><span class="default">__CLASS__</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$_instance </span><span class="keyword">?: </span><span class="default">$_instance </span><span class="keyword">= new </span><span class="default">$class</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">__clone</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">trigger_error</span><span class="keyword">(</span><span class="string">'Cloning '</span><span class="keyword">.</span><span class="default">__CLASS__</span><span class="keyword">.</span><span class="string">' is not allowed.'</span><span class="keyword">,</span><span class="default">E_USER_ERROR</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">__wakeup</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">trigger_error</span><span class="keyword">(</span><span class="string">'Unserializing '</span><span class="keyword">.</span><span class="default">__CLASS__</span><span class="keyword">.</span><span class="string">' is not allowed.'</span><span class="keyword">,</span><span class="default">E_USER_ERROR</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="comment">/**<br />
&nbsp;* Example Usage<br />
&nbsp;*/<br />
<br />
</span><span class="keyword">class </span><span class="default">foo </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; use </span><span class="default">singleton</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; private function </span><span class="default">__construct</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">name </span><span class="keyword">= </span><span class="string">'foo'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
class </span><span class="default">bar </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; use </span><span class="default">singleton</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; private function </span><span class="default">__construct</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">name </span><span class="keyword">= </span><span class="string">'bar'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="default">$foo </span><span class="keyword">= </span><span class="default">foo</span><span class="keyword">::</span><span class="default">getInstance</span><span class="keyword">();<br />
echo </span><span class="default">$foo</span><span class="keyword">-&gt;</span><span class="default">name</span><span class="keyword">;<br />
<br />
</span><span class="default">$bar </span><span class="keyword">= </span><span class="default">bar</span><span class="keyword">::</span><span class="default">getInstance</span><span class="keyword">();<br />
echo </span><span class="default">$bar</span><span class="keyword">-&gt;</span><span class="default">name</span><span class="keyword">;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="108086"></a>
 <div class="note">
  <strong class='user'>Anonymous</strong>
  <a href="#108086" class="date">27-Mar-2012 09:30</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Traits can not implement interfaces.<br />
(should be obvious, but tested is tested)</span>
</code></div>
  </div>
 </div>
 <a name="107965"></a>
 <div class="note">
  <strong class='user'>Safak Ozpinar / safakozpinar at gmail</strong>
  <a href="#107965" class="date">18-Mar-2012 03:03</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Unlike inheritance; if a trait has static properties, each class using that trait has independent instances of those properties.<br />
<br />
Example using parent class:<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">TestClass </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public static </span><span class="default">$_bar</span><span class="keyword">;<br />
}<br />
class </span><span class="default">Foo1 </span><span class="keyword">extends </span><span class="default">TestClass </span><span class="keyword">{ }<br />
class </span><span class="default">Foo2 </span><span class="keyword">extends </span><span class="default">TestClass </span><span class="keyword">{ }<br />
</span><span class="default">Foo1</span><span class="keyword">::</span><span class="default">$_bar </span><span class="keyword">= </span><span class="string">'Hello'</span><span class="keyword">;<br />
</span><span class="default">Foo2</span><span class="keyword">::</span><span class="default">$_bar </span><span class="keyword">= </span><span class="string">'World'</span><span class="keyword">;<br />
echo </span><span class="default">Foo1</span><span class="keyword">::</span><span class="default">$_bar </span><span class="keyword">. </span><span class="string">' ' </span><span class="keyword">. </span><span class="default">Foo2</span><span class="keyword">::</span><span class="default">$_bar</span><span class="keyword">; </span><span class="comment">// Prints: World World<br />
</span><span class="default">?&gt;<br />
</span><br />
Example using trait:<br />
<span class="default">&lt;?php<br />
trait TestTrait </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public static </span><span class="default">$_bar</span><span class="keyword">;<br />
}<br />
class </span><span class="default">Foo1 </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; use </span><span class="default">TestTrait</span><span class="keyword">;<br />
}<br />
class </span><span class="default">Foo2 </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; use </span><span class="default">TestTrait</span><span class="keyword">;<br />
}<br />
</span><span class="default">Foo1</span><span class="keyword">::</span><span class="default">$_bar </span><span class="keyword">= </span><span class="string">'Hello'</span><span class="keyword">;<br />
</span><span class="default">Foo2</span><span class="keyword">::</span><span class="default">$_bar </span><span class="keyword">= </span><span class="string">'World'</span><span class="keyword">;<br />
echo </span><span class="default">Foo1</span><span class="keyword">::</span><span class="default">$_bar </span><span class="keyword">. </span><span class="string">' ' </span><span class="keyword">. </span><span class="default">Foo2</span><span class="keyword">::</span><span class="default">$_bar</span><span class="keyword">; </span><span class="comment">// Prints: Hello World<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="107913"></a>
 <div class="note">
  <strong class='user'>Jason dot Hofer dot deletify dot this dot part at gmail dot com</strong>
  <a href="#107913" class="date">14-Mar-2012 04:39</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
A (somewhat) practical example of trait usage.<br />
<br />
Without traits:<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">class </span><span class="default">Controller </span><span class="keyword">{<br />
&nbsp; </span><span class="comment">/* Controller-specific methods defined here. */<br />
</span><span class="keyword">}<br />
<br />
class </span><span class="default">AdminController </span><span class="keyword">extends </span><span class="default">Controller </span><span class="keyword">{<br />
&nbsp; </span><span class="comment">/* Controller-specific methods inherited from Controller. */<br />
&nbsp; /* Admin-specific methods defined here. */<br />
</span><span class="keyword">}<br />
<br />
class </span><span class="default">CrudController </span><span class="keyword">extends </span><span class="default">Controller </span><span class="keyword">{<br />
&nbsp; </span><span class="comment">/* Controller-specific methods inherited from Controller. */<br />
&nbsp; /* CRUD-specific methods defined here. */<br />
</span><span class="keyword">}<br />
<br />
class </span><span class="default">AdminCrudController </span><span class="keyword">extends </span><span class="default">CrudController </span><span class="keyword">{<br />
&nbsp; </span><span class="comment">/* Controller-specific methods inherited from Controller. */<br />
&nbsp; /* CRUD-specific methods inherited from CrudController. */<br />
&nbsp; /* (!!!) Admin-specific methods copied and pasted from AdminController. */<br />
</span><span class="keyword">}<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
With traits:<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">class </span><span class="default">Controller </span><span class="keyword">{<br />
&nbsp; </span><span class="comment">/* Controller-specific methods defined here. */<br />
</span><span class="keyword">}<br />
<br />
class </span><span class="default">AdminController </span><span class="keyword">extends </span><span class="default">Controller </span><span class="keyword">{<br />
&nbsp; </span><span class="comment">/* Controller-specific methods inherited from Controller. */<br />
&nbsp; /* Admin-specific methods defined here. */<br />
</span><span class="keyword">}<br />
<br />
</span><span class="default">trait CrudControllerTrait </span><span class="keyword">{<br />
&nbsp; </span><span class="comment">/* CRUD-specific methods defined here. */<br />
</span><span class="keyword">}<br />
<br />
class </span><span class="default">AdminCrudController </span><span class="keyword">extends </span><span class="default">AdminController </span><span class="keyword">{<br />
&nbsp; use </span><span class="default">CrudControllerTrait</span><span class="keyword">;<br />
&nbsp; </span><span class="comment">/* Controller-specific methods inherited from Controller. */<br />
&nbsp; /* Admin-specific methods inherited from AdminController. */<br />
&nbsp; /* CRUD-specific methods defined by CrudControllerTrait. */<br />
</span><span class="keyword">}<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="107852"></a>
 <div class="note">
  <strong class='user'>farhad dot peb at gmail dot com</strong>
  <a href="#107852" class="date">09-Mar-2012 07:29</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
<span class="default">&lt;?php<br />
trait first_trait<br />
&nbsp; </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">first_function</span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp;&nbsp; echo </span><span class="string">"From First Trait"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp; }<br />
&nbsp;<br />
&nbsp; </span><span class="default">trait second_trait<br />
&nbsp; </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">first_function</span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp;&nbsp; echo </span><span class="string">"From Second Trait"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp; }<br />
&nbsp;<br />
&nbsp; class </span><span class="default">first_class<br />
&nbsp; </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; use </span><span class="default">first_trait</span><span class="keyword">, </span><span class="default">second_trait<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">{<br />
&nbsp;&nbsp; &nbsp;&nbsp; </span><span class="comment">// This class will now call the method<br />
&nbsp;&nbsp; &nbsp;&nbsp; // first function from first_trait only<br />
&nbsp;&nbsp; &nbsp;&nbsp; </span><span class="default">first_trait</span><span class="keyword">::</span><span class="default">first_function insteadof second_trait</span><span class="keyword">;<br />
&nbsp;<br />
&nbsp;&nbsp; &nbsp;&nbsp; </span><span class="comment">// first_function of second_traits can be<br />
&nbsp;&nbsp; &nbsp;&nbsp; // accessed with second_function<br />
&nbsp;&nbsp; &nbsp;&nbsp; </span><span class="default">second_trait</span><span class="keyword">::</span><span class="default">first_function </span><span class="keyword">as </span><span class="default">second_function</span><span class="keyword">;<br />
&nbsp;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp; }<br />
&nbsp;<br />
&nbsp; </span><span class="default">$obj </span><span class="keyword">= new </span><span class="default">first_class</span><span class="keyword">();<br />
&nbsp; </span><span class="comment">// Output: From First Trait<br />
&nbsp; </span><span class="default">$obj</span><span class="keyword">-&gt;</span><span class="default">first_function</span><span class="keyword">();<br />
&nbsp;<br />
&nbsp; </span><span class="comment">// Output: From Second Trait<br />
&nbsp; </span><span class="default">$obj</span><span class="keyword">-&gt;</span><span class="default">second_function</span><span class="keyword">();<br />
</span><span class="default">?&gt;<br />
</span><br />
the iranian php programmer<br />
<br />
writer: farhad zand<br />
<br />
farhad.peb@gmail.com<br />
php_engineer_bk@yahoo.com</span>
</code></div>
  </div>
 </div>
 <a name="107769"></a>
 <div class="note">
  <strong class='user'>anthony bishopric</strong>
  <a href="#107769" class="date">03-Mar-2012 12:11</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The magic method __call works as expected using traits.<br />
<br />
<span class="default">&lt;?php <br />
trait Call_Helper</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">__call</span><span class="keyword">(</span><span class="default">$name</span><span class="keyword">, </span><span class="default">$args</span><span class="keyword">){<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">count</span><span class="keyword">(</span><span class="default">$args</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
class </span><span class="default">Foo</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; use </span><span class="default">Call_Helper</span><span class="keyword">;<br />
}<br />
<br />
</span><span class="default">$foo </span><span class="keyword">= new </span><span class="default">Foo</span><span class="keyword">();<br />
echo </span><span class="default">$foo</span><span class="keyword">-&gt;</span><span class="default">go</span><span class="keyword">(</span><span class="default">1</span><span class="keyword">,</span><span class="default">2</span><span class="keyword">,</span><span class="default">3</span><span class="keyword">,</span><span class="default">4</span><span class="keyword">); </span><span class="comment">// echoes 4</span>
</span>
</code></div>
  </div>
 </div>
 <a name="107735"></a>
 <div class="note">
  <strong class='user'>Edward</strong>
  <a href="#107735" class="date">01-Mar-2012 03:29</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The difference between Traits and multiple inheritance is in the inheritance part.&nbsp;&nbsp; A trait is not inherited from, but rather included or mixed-in, thus becoming part of "this class".&nbsp;&nbsp; Traits also provide a more controlled means of resolving conflicts that inevitably arise when using multiple inheritance in the few languages that support them (C++).&nbsp; Most modern languages are going the approach of a "traits" or "mixin" style system as opposed to multiple-inheritance, largely due to the ability to control ambiguities if a method is declared in multiple "mixed-in" classes.<br />
<br />
Also, one can not "inherit" static member functions in multiple-inheritance.</span>
</code></div>
  </div>
 </div>
 <a name="107548"></a>
 <div class="note">
  <strong class='user'>greywire at gmail dot com</strong>
  <a href="#107548" class="date">16-Feb-2012 07:12</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The best way to understand what traits are and how to use them is to look at them for what they essentially are:&nbsp; language assisted copy and paste.<br />
<br />
If you can copy and paste the code from one class to another (and we've all done this, even though we try not to because its code duplication) then you have a candidate for a trait.</span>
</code></div>
  </div>
 </div>
 <a name="106958"></a>
 <div class="note">
  <strong class='user'>chris dot rutledge at gmail dot com</strong>
  <a href="#106958" class="date">21-Dec-2011 12:42</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
It may be worth noting here that the magic constant __CLASS__ becomes even more magical - __CLASS__ will return the name of the class in which the trait is being used.<br />
<br />
for example<br />
<br />
<span class="default">&lt;?php<br />
trait sayWhere </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">whereAmI</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="default">__CLASS__</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
class </span><span class="default">Hello </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; use </span><span class="default">sayWHere</span><span class="keyword">;<br />
}<br />
<br />
class </span><span class="default">World </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; use </span><span class="default">sayWHere</span><span class="keyword">;<br />
}<br />
<br />
</span><span class="default">$a </span><span class="keyword">= new </span><span class="default">Hello</span><span class="keyword">;<br />
</span><span class="default">$a</span><span class="keyword">-&gt;</span><span class="default">whereAmI</span><span class="keyword">(); </span><span class="comment">//Hello<br />
<br />
</span><span class="default">$b </span><span class="keyword">= new </span><span class="default">World</span><span class="keyword">;<br />
</span><span class="default">$b</span><span class="keyword">-&gt;</span><span class="default">whereAmI</span><span class="keyword">(); </span><span class="comment">//World<br />
</span><span class="default">?&gt;<br />
</span><br />
The magic constant __TRAIT__ will giev you the name of the trait</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=language.oop5.traits&amp;redirect=http://www.php.net/manual/en/language.oop5.traits.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.oop5.traits&amp;redirect=http://www.php.net/manual/en/language.oop5.traits.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/language.oop5.traits.php">show source</a> |
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