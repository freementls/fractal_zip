<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Classes and Objects - Manual</title>
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
 <link rel="index" href="langref.php" />
 <link rel="prev" href="functions.anonymous.php" />
 <link rel="next" href="oop5.intro.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/oop5" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="alternate" href="/manual/en/feeds/language.oop5.atom" type="application/atom+xml" />
 <link rel="canonical" href="http://php.net/manual/en/language.oop5.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="@w{6EHQVFYQ}" />
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
 <li><a href="language.basic-syntax.php">Basic syntax</a></li>
 <li><a href="language.types.php">Types</a></li>
 <li><a href="language.variables.php">Variables</a></li>
 <li><a href="language.constants.php">Constants</a></li>
 <li><a href="language.expressions.php">Expressions</a></li>
 <li><a href="language.operators.php">Operators</a></li>
 <li><a href="language.control-structures.php">Control Structures</a></li>
 <li><a href="language.functions.php">Functions</a></li>
 <li class="active"><a href="language.oop5.php">Classes and Objects</a></li>
 <li><a href="language.namespaces.php">Namespaces</a></li>
 <li><a href="language.exceptions.php">Exceptions</a></li>
 <li><a href="language.references.php">References Explained</a></li>
 <li><a href="reserved.variables.php">Predefined Variables</a></li>
 <li><a href="reserved.exceptions.php">Predefined Exceptions</a></li>
 <li><a href="reserved.interfaces.php">Predefined Interfaces</a></li>
 <li><a href="context.php">Context options and parameters</a></li>
 <li><a href="wrappers.php">Supported Protocols and Wrappers</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="oop5.intro.php">Introduction<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="functions.anonymous.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Anonymous functions</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.oop5.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/language.oop5.php">Brazilian Portuguese</option>
    <option value="zh/language.oop5.php">Chinese (Simplified)</option>
    <option value="fr/language.oop5.php">French</option>
    <option value="de/language.oop5.php">German</option>
    <option value="ja/language.oop5.php">Japanese</option>
    <option value="pl/language.oop5.php">Polish</option>
    <option value="ro/language.oop5.php">Romanian</option>
    <option value="ru/language.oop5.php">Russian</option>
    <option value="fa/language.oop5.php">Persian</option>
    <option value="es/language.oop5.php">Spanish</option>
    <option value="tr/language.oop5.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="language.oop5" class="chapter">
  <h1>Classes and Objects</h1>
<h2>Table of Contents</h2><ul class="chunklist chunklist_chapter"><li><a href="oop5.intro.php">Introduction</a></li><li><a href="language.oop5.basic.php">The Basics</a></li><li><a href="language.oop5.properties.php">Properties</a></li><li><a href="language.oop5.constants.php">Class Constants</a></li><li><a href="language.oop5.autoload.php">Autoloading Classes</a></li><li><a href="language.oop5.decon.php">Constructors and Destructors</a></li><li><a href="language.oop5.visibility.php">Visibility</a></li><li><a href="language.oop5.inheritance.php">Object Inheritance</a></li><li><a href="language.oop5.paamayim-nekudotayim.php">Scope Resolution Operator (::)</a></li><li><a href="language.oop5.static.php">Static Keyword</a></li><li><a href="language.oop5.abstract.php">Class Abstraction</a></li><li><a href="language.oop5.interfaces.php">Object Interfaces</a></li><li><a href="language.oop5.traits.php">Traits</a></li><li><a href="language.oop5.overloading.php">Overloading</a></li><li><a href="language.oop5.iterations.php">Object Iteration</a></li><li><a href="language.oop5.magic.php">Magic Methods</a></li><li><a href="language.oop5.final.php">Final Keyword</a></li><li><a href="language.oop5.cloning.php">Object Cloning</a></li><li><a href="language.oop5.object-comparison.php">Comparing Objects</a></li><li><a href="language.oop5.typehinting.php">Type Hinting</a></li><li><a href="language.oop5.late-static-bindings.php">Late Static Bindings</a></li><li><a href="language.oop5.references.php">Objects and references</a></li><li><a href="language.oop5.serialization.php">Object Serialization</a></li><li><a href="language.oop5.changelog.php">OOP Changelog</a></li></ul>


  

  


 



  

 
 


  

 
 


  

 
 


  

 



  

 
 


  

 
 


  





  

 
 


  

 



  

 
 


  

 
 


  

 



  

 
 


  

 
 


  

 
 


  

 
 


  

  
 


  

 
 


  

 



  

 


  

 
 


  


 


</div>
<br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="oop5.intro.php">Introduction<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="functions.anonymous.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Anonymous functions</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.oop5.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=language.oop5&amp;redirect=@w{6EHQVFYQ}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.oop5&amp;redirect=@w{6EHQVFYQ}">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Classes and Objects</strong>
 </div><div id="allnotes">
 <a name="101929"></a>
 <div class="note">
  <strong class='user'>dances_with_peons at live dot com</strong>
  <a href="#101929" class="date">18-Jan-2011 02:36</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
As of PHP 5.3, $className::funcName() works fine.<br />
<br />
<span class="default">&lt;?php<br />
<br />
&nbsp; </span><span class="keyword">class </span><span class="default">test<br />
&nbsp; </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public static function </span><span class="default">run</span><span class="keyword">() { print </span><span class="string">"Works\n"</span><span class="keyword">; }<br />
&nbsp; }<br />
<br />
&nbsp; </span><span class="default">$className </span><span class="keyword">= </span><span class="string">'test'</span><span class="keyword">;<br />
&nbsp; </span><span class="default">$className</span><span class="keyword">::</span><span class="default">run</span><span class="keyword">();<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
on my system, prints "Works".&nbsp; May work with earlier versions of PHP as well.&nbsp; Even if it doesn't, there's always<br />
<br />
<span class="default">&lt;?php<br />
<br />
&nbsp; $className </span><span class="keyword">= </span><span class="string">'test'</span><span class="keyword">;<br />
&nbsp; </span><span class="default">call_user_func</span><span class="keyword">(array(</span><span class="default">$className</span><span class="keyword">, </span><span class="string">'run'</span><span class="keyword">));<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
The point is, there's no need for eval.</span>
</code></div>
  </div>
 </div>
 <a name="101928"></a>
 <div class="note">
  <strong class='user'>dances_with_peons at live dot com</strong>
  <a href="#101928" class="date">18-Jan-2011 02:30</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
As of PHP 5.3, $className::funcName() works fine.<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">test<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public static function </span><span class="default">run</span><span class="keyword">() { print </span><span class="string">"Works\n"</span><span class="keyword">; }<br />
}<br />
<br />
</span><span class="default">$className </span><span class="keyword">= </span><span class="string">'test'</span><span class="keyword">;<br />
</span><span class="default">$className</span><span class="keyword">::</span><span class="default">run</span><span class="keyword">();<br />
</span><span class="default">?&gt;<br />
</span><br />
on my system, prints "Works".&nbsp; It may work with earlier versions of PHP as well.</span>
</code></div>
  </div>
 </div>
 <a name="101112"></a>
 <div class="note">
  <strong class='user'>corpus-deus at softhome dot net</strong>
  <a href="#101112" class="date">26-Nov-2010 08:27</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
With regards to Singleton patterns (and variable class names) - try:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">MyClass </span><span class="keyword">{<br />
<br />
&nbsp; </span><span class="comment">// singleton instance<br />
&nbsp; </span><span class="keyword">private static </span><span class="default">$instance</span><span class="keyword">;<br />
<br />
&nbsp; </span><span class="comment">// private constructor function<br />
&nbsp; // to prevent external instantiation<br />
&nbsp; </span><span class="keyword">private </span><span class="default">__construct</span><span class="keyword">() { }<br />
<br />
&nbsp; </span><span class="comment">// getInstance method<br />
&nbsp; </span><span class="keyword">public static function </span><span class="default">getInstance</span><span class="keyword">() {<br />
<br />
&nbsp;&nbsp;&nbsp; if(!</span><span class="default">self</span><span class="keyword">::</span><span class="default">$instance</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp;&nbsp; </span><span class="default">self</span><span class="keyword">::</span><span class="default">$instance </span><span class="keyword">= new </span><span class="default">self</span><span class="keyword">();<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">self</span><span class="keyword">::</span><span class="default">$instance</span><span class="keyword">;<br />
<br />
&nbsp; }<br />
<br />
&nbsp; </span><span class="comment">//...<br />
<br />
</span><span class="keyword">}<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="100591"></a>
 <div class="note">
  <strong class='user'>suleman dot saleh at gmail dot com</strong>
  <a href="#100591" class="date">25-Oct-2010 11:41</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Using php abstract classes we must have to implement all their functions in child classes other wise it will make automatically child class as a concrete</span>
</code></div>
  </div>
 </div>
 <a name="100209"></a>
 <div class="note">
  <strong class='user'>DavMe</strong>
  <a href="#100209" class="date">01-Oct-2010 12:03</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
When you have a class name in a variable and want to create a new instance of that class, you can simply use:<br />
<span class="default">&lt;?php<br />
$className </span><span class="keyword">= </span><span class="string">"ClassName"</span><span class="keyword">;<br />
</span><span class="default">$instance </span><span class="keyword">= new </span><span class="default">$className</span><span class="keyword">();<br />
</span><span class="default">?&gt;<br />
</span><br />
If, however, you have a class that is part of a singleton pattern where you cannot create it with new and need to use:<br />
<span class="default">&lt;?php<br />
$instance </span><span class="keyword">= </span><span class="default">ClassName</span><span class="keyword">::</span><span class="default">GetInstance</span><span class="keyword">();<br />
</span><span class="default">?&gt;<br />
</span><br />
...you quickly discover that it fails miserably with a variable.<br />
Fail Example:<br />
<span class="default">&lt;?php<br />
$className </span><span class="keyword">= </span><span class="string">"ClassName"</span><span class="keyword">;<br />
</span><span class="default">$instance </span><span class="keyword">= </span><span class="default">$className</span><span class="keyword">::</span><span class="default">GetInstance</span><span class="keyword">();<br />
</span><span class="default">?&gt;<br />
</span><br />
After a few days of head pounding, I finally put together this workaround:<br />
<span class="default">&lt;?php<br />
$className </span><span class="keyword">= </span><span class="string">"ClassName"</span><span class="keyword">;<br />
eval(</span><span class="string">'$instance = '</span><span class="keyword">.</span><span class="default">$className</span><span class="keyword">.</span><span class="string">'::GetInstance();'</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span><br />
I hope this saves you some effort and if anyone knows of a non-eval method to accomplish this, please share!<br />
<br />
Thanks!</span>
</code></div>
  </div>
 </div>
 <a name="89296"></a>
 <div class="note">
  <strong class='user'>midir</strong>
  <a href="#89296" class="date">02-Mar-2009 07:40</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
There are a couple of tricks you can do with PHP's classes that programmers from C++, etc., will find very peculiar, but which can be useful.<br />
<br />
You can create instances of classes without knowing the class name in advance, when it's in a variable:<br />
<br />
<span class="default">&lt;?php<br />
<br />
$type </span><span class="keyword">= </span><span class="string">'cc'</span><span class="keyword">;<br />
</span><span class="default">$obj </span><span class="keyword">= new </span><span class="default">$type</span><span class="keyword">; </span><span class="comment">// outputs "hi!"<br />
<br />
</span><span class="keyword">class </span><span class="default">cc </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">__construct</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">'hi!'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
You can also conditionally define them by wrapping them in if/else blocks etc, like so:<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">if (</span><span class="default">expr</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; class </span><span class="default">cc </span><span class="keyword">{<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// version 1<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">}<br />
} else {<br />
&nbsp;&nbsp;&nbsp; class </span><span class="default">cc </span><span class="keyword">{<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// version 2<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">}<br />
}<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
It makes up for PHP's lack of preprocessor directives. The caveat is that the if/else code body must have been executed before you can use the class, so you need to pay attention to the order of the code, and not use things before they're defined.</span>
</code></div>
  </div>
 </div>
 <a name="87942"></a>
 <div class="note">
  <strong class='user'>redrik at gmail dot com</strong>
  <a href="#87942" class="date">31-Dec-2008 01:08</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Maybe someone will find these classes, which simulate enumeration, useful.<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">Enum </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; protected </span><span class="default">$self </span><span class="keyword">= array();<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">__construct</span><span class="keyword">( </span><span class="comment">/*...*/ </span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$args </span><span class="keyword">= </span><span class="default">func_get_args</span><span class="keyword">();<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; for( </span><span class="default">$i</span><span class="keyword">=</span><span class="default">0</span><span class="keyword">, </span><span class="default">$n</span><span class="keyword">=</span><span class="default">count</span><span class="keyword">(</span><span class="default">$args</span><span class="keyword">); </span><span class="default">$i</span><span class="keyword">&lt;</span><span class="default">$n</span><span class="keyword">; </span><span class="default">$i</span><span class="keyword">++ )<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">add</span><span class="keyword">(</span><span class="default">$args</span><span class="keyword">[</span><span class="default">$i</span><span class="keyword">]);<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">__get</span><span class="keyword">( </span><span class="comment">/*string*/ </span><span class="default">$name </span><span class="keyword">= </span><span class="default">null </span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">self</span><span class="keyword">[</span><span class="default">$name</span><span class="keyword">];<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">add</span><span class="keyword">( </span><span class="comment">/*string*/ </span><span class="default">$name </span><span class="keyword">= </span><span class="default">null</span><span class="keyword">, </span><span class="comment">/*int*/ </span><span class="default">$enum </span><span class="keyword">= </span><span class="default">null </span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if( isset(</span><span class="default">$enum</span><span class="keyword">) )<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">self</span><span class="keyword">[</span><span class="default">$name</span><span class="keyword">] = </span><span class="default">$enum</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; else<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">self</span><span class="keyword">[</span><span class="default">$name</span><span class="keyword">] = </span><span class="default">end</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">self</span><span class="keyword">) + </span><span class="default">1</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
class </span><span class="default">DefinedEnum </span><span class="keyword">extends </span><span class="default">Enum </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">__construct</span><span class="keyword">( </span><span class="comment">/*array*/ </span><span class="default">$itms </span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; foreach( </span><span class="default">$itms </span><span class="keyword">as </span><span class="default">$name </span><span class="keyword">=&gt; </span><span class="default">$enum </span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">add</span><span class="keyword">(</span><span class="default">$name</span><span class="keyword">, </span><span class="default">$enum</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
class </span><span class="default">FlagsEnum </span><span class="keyword">extends </span><span class="default">Enum </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">__construct</span><span class="keyword">( </span><span class="comment">/*...*/ </span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$args </span><span class="keyword">= </span><span class="default">func_get_args</span><span class="keyword">();<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; for( </span><span class="default">$i</span><span class="keyword">=</span><span class="default">0</span><span class="keyword">, </span><span class="default">$n</span><span class="keyword">=</span><span class="default">count</span><span class="keyword">(</span><span class="default">$args</span><span class="keyword">), </span><span class="default">$f</span><span class="keyword">=</span><span class="default">0x1</span><span class="keyword">; </span><span class="default">$i</span><span class="keyword">&lt;</span><span class="default">$n</span><span class="keyword">; </span><span class="default">$i</span><span class="keyword">++, </span><span class="default">$f </span><span class="keyword">*= </span><span class="default">0x2 </span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">add</span><span class="keyword">(</span><span class="default">$args</span><span class="keyword">[</span><span class="default">$i</span><span class="keyword">], </span><span class="default">$f</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
</span><span class="default">?&gt;<br />
</span>Example usage:<br />
<span class="default">&lt;?php<br />
$eFruits </span><span class="keyword">= new </span><span class="default">Enum</span><span class="keyword">(</span><span class="string">"APPLE"</span><span class="keyword">, </span><span class="string">"ORANGE"</span><span class="keyword">, </span><span class="string">"PEACH"</span><span class="keyword">);<br />
echo </span><span class="default">$eFruits</span><span class="keyword">-&gt;</span><span class="default">APPLE </span><span class="keyword">. </span><span class="string">","</span><span class="keyword">;<br />
echo </span><span class="default">$eFruits</span><span class="keyword">-&gt;</span><span class="default">ORANGE </span><span class="keyword">. </span><span class="string">","</span><span class="keyword">;<br />
echo </span><span class="default">$eFruits</span><span class="keyword">-&gt;</span><span class="default">PEACH </span><span class="keyword">. </span><span class="string">"\n"</span><span class="keyword">;<br />
<br />
</span><span class="default">$eBeers </span><span class="keyword">= new </span><span class="default">DefinedEnum</span><span class="keyword">(</span><span class="string">"GUINESS" </span><span class="keyword">=&gt; </span><span class="default">25</span><span class="keyword">, </span><span class="string">"MIRROR_POND" </span><span class="keyword">=&gt; </span><span class="default">49</span><span class="keyword">);<br />
echo </span><span class="default">$eBeers</span><span class="keyword">-&gt;</span><span class="default">GUINESS </span><span class="keyword">. </span><span class="string">","</span><span class="keyword">;<br />
echo </span><span class="default">$eBeers</span><span class="keyword">-&gt;</span><span class="default">MIRROR_POND </span><span class="keyword">. </span><span class="string">"\n"</span><span class="keyword">;<br />
<br />
</span><span class="default">$eFlags </span><span class="keyword">= new </span><span class="default">FlagsEnum</span><span class="keyword">(</span><span class="string">"HAS_ADMIN"</span><span class="keyword">, </span><span class="string">"HAS_SUPER"</span><span class="keyword">, </span><span class="string">"HAS_POWER"</span><span class="keyword">, </span><span class="string">"HAS_GUEST"</span><span class="keyword">);<br />
echo </span><span class="default">$eFlags</span><span class="keyword">-&gt;</span><span class="default">HAS_ADMIN </span><span class="keyword">. </span><span class="string">","</span><span class="keyword">;<br />
echo </span><span class="default">$eFlags</span><span class="keyword">-&gt;</span><span class="default">HAS_SUPER </span><span class="keyword">. </span><span class="string">","</span><span class="keyword">;<br />
echo </span><span class="default">$eFlags</span><span class="keyword">-&gt;</span><span class="default">HAS_POWER </span><span class="keyword">. </span><span class="string">","</span><span class="keyword">;<br />
echo </span><span class="default">$eFlags</span><span class="keyword">-&gt;</span><span class="default">HAS_GUEST </span><span class="keyword">. </span><span class="string">"\n"</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span>Will output: <br />
1, 2, 3<br />
25, 49<br />
1,2,4,8 (or 1, 10, 100, 1000 in binary)</span>
</code></div>
  </div>
 </div>
 <a name="86229"></a>
 <div class="note">
  <strong class='user'>Jeffrey</strong>
  <a href="#86229" class="date">08-Oct-2008 11:51</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Why should anyone learn what classes and objects are? The short answer is to clarify and simplify code. Take this regular script:<br />
<br />
<span class="default">&lt;?php<br />
$item_name </span><span class="keyword">= </span><span class="string">'Widget 22'</span><span class="keyword">;<br />
</span><span class="default">$item_price </span><span class="keyword">= </span><span class="default">4.90</span><span class="keyword">;<br />
</span><span class="default">$item_qty </span><span class="keyword">= </span><span class="default">2</span><span class="keyword">;<br />
</span><span class="default">$item_total </span><span class="keyword">= (</span><span class="default">$item_price </span><span class="keyword">* </span><span class="default">$item_qty</span><span class="keyword">);<br />
echo </span><span class="string">"You ordered $item_qty $item_name @ \$$item_price for a total of: \$$item_total."</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
You ordered 2 Widget 22 @ $4.9 for a total of: $9.8.<br />
<br />
You can see clearly that you have to "define and set" the data, "perform a calculation", and explicitly "write" the results - for a total of 5 written statements. But the more you look at it, the more it needs fixin'. If you attempt to do that, your code can get really ugly, really fast - and remember, this is just a simple script! Here's the same program in OOP with all the fixin's:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">Item </span><span class="keyword">{<br />
&nbsp; protected </span><span class="default">$name</span><span class="keyword">, </span><span class="default">$price</span><span class="keyword">, </span><span class="default">$qty</span><span class="keyword">, </span><span class="default">$total</span><span class="keyword">;<br />
<br />
&nbsp; public function </span><span class="default">__construct</span><span class="keyword">(</span><span class="default">$iName</span><span class="keyword">, </span><span class="default">$iPrice</span><span class="keyword">, </span><span class="default">$iQty</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">name </span><span class="keyword">= </span><span class="default">$iName</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">price </span><span class="keyword">= </span><span class="default">$iPrice</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">qty </span><span class="keyword">= </span><span class="default">$iQty</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">calculate</span><span class="keyword">();<br />
&nbsp; }<br />
<br />
&nbsp; protected function </span><span class="default">calculate</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">price </span><span class="keyword">= </span><span class="default">number_format</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">price</span><span class="keyword">, </span><span class="default">2</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">total </span><span class="keyword">= </span><span class="default">number_format</span><span class="keyword">((</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">price </span><span class="keyword">* </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">qty</span><span class="keyword">), </span><span class="default">2</span><span class="keyword">);<br />
&nbsp; }<br />
<br />
&nbsp; public function </span><span class="default">__toString</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; return </span><span class="string">"You ordered ($this-&gt;qty) '$this-&gt;name'" </span><span class="keyword">. (</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">qty </span><span class="keyword">== </span><span class="default">1 </span><span class="keyword">? </span><span class="string">"" </span><span class="keyword">: </span><span class="string">"s"</span><span class="keyword">) .<br />
&nbsp;&nbsp;&nbsp; </span><span class="string">" at \$$this-&gt;price, for a total of: \$$this-&gt;total."</span><span class="keyword">;<br />
&nbsp; }<br />
}<br />
<br />
echo (new </span><span class="default">Item</span><span class="keyword">(</span><span class="string">"Widget 22"</span><span class="keyword">, </span><span class="default">4.90</span><span class="keyword">, </span><span class="default">2</span><span class="keyword">));<br />
</span><span class="default">?&gt;<br />
</span><br />
You ordered (2) 'Widget 22's at $4.90, for a total of: $9.80.<br />
<br />
By loading class Item (which houses all the improvements we made over the first script) into PHP first, we went from having to write 5 statements in the first script, to writing only 1 statement "echo new Item" in the second.</span>
</code></div>
  </div>
 </div>
 <a name="84292"></a>
 <div class="note">
  <strong class='user'>Jason</strong>
  <a href="#84292" class="date">07-Jul-2008 10:34</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
For real quick and dirty one-liner anonymous objects, just cast an associative array:<br />
<br />
<span class="default">&lt;?php<br />
<br />
$obj </span><span class="keyword">= (object) array(</span><span class="string">'foo' </span><span class="keyword">=&gt; </span><span class="string">'bar'</span><span class="keyword">, </span><span class="string">'property' </span><span class="keyword">=&gt; </span><span class="string">'value'</span><span class="keyword">);<br />
<br />
echo </span><span class="default">$obj</span><span class="keyword">-&gt;</span><span class="default">foo</span><span class="keyword">; </span><span class="comment">// prints 'bar'<br />
</span><span class="keyword">echo </span><span class="default">$obj</span><span class="keyword">-&gt;</span><span class="default">property</span><span class="keyword">; </span><span class="comment">// prints 'value'<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
... no need to create a new class or function to accomplish it.</span>
</code></div>
  </div>
 </div>
 <a name="82177"></a>
 <div class="note">
  <strong class='user'>ranema at ubuntu dot polarhome dot com</strong>
  <a href="#82177" class="date">30-Mar-2008 07:49</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Sometimes you just forget to close handles, links, etc and sometimes you are just lazy to do that. PHP 5 OOP can do it automatically by using destructors:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">MySqlDriver </span><span class="keyword">{<br />
&nbsp;&nbsp; private </span><span class="default">$_Link</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp; public function </span><span class="default">__construct</span><span class="keyword">( &lt;...&gt; ) {<br />
&nbsp;&nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">_Link </span><span class="keyword">= </span><span class="default">mysql_connect</span><span class="keyword">( &lt;...&gt; );<br />
&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp; </span><span class="comment">// this will be called automatically at the end of scope<br />
&nbsp;&nbsp; </span><span class="keyword">public function </span><span class="default">__destruct</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp;&nbsp; </span><span class="default">mysql_close</span><span class="keyword">( </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">_Link </span><span class="keyword">);<br />
&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="default">$_gLink </span><span class="keyword">= new </span><span class="default">MySqlDriver</span><span class="keyword">( &lt;...&gt; );<br />
</span><span class="comment">// and you don't need to close the link manually<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="70224"></a>
 <div class="note">
  <strong class='user'>osculabond at gmail dot com</strong>
  <a href="#70224" class="date">06-Oct-2006 11:20</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
A better way to simulate an enum in php5:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">final class </span><span class="default">Days </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; const </span><span class="default">Sunday&nbsp; &nbsp;&nbsp; </span><span class="keyword">= </span><span class="default">0x00000001</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; const </span><span class="default">Monday&nbsp; &nbsp;&nbsp; </span><span class="keyword">= </span><span class="default">0x00000010</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; const </span><span class="default">Tuesday&nbsp; &nbsp; </span><span class="keyword">= </span><span class="default">0x00000100</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; const </span><span class="default">Wednesday </span><span class="keyword">= </span><span class="default">0x00001000</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; const </span><span class="default">Thursday&nbsp; </span><span class="keyword">= </span><span class="default">0x00010000</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; const </span><span class="default">Friday&nbsp; &nbsp; </span><span class="keyword">= </span><span class="default">0x00100000</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; const </span><span class="default">Saturday&nbsp; </span><span class="keyword">= </span><span class="default">0x01000000</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; const </span><span class="default">Unknown&nbsp; &nbsp; </span><span class="keyword">= </span><span class="default">0x00000000</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">// ensures that this class acts like an enum<br />
&nbsp;&nbsp;&nbsp; // and that it cannot be instantiated<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">private function </span><span class="default">__construct</span><span class="keyword">(){}<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
This will allow you to do things like:<br />
<br />
<span class="default">&lt;?php<br />
$day_to_email </span><span class="keyword">= </span><span class="default">Days</span><span class="keyword">::</span><span class="default">Thursday</span><span class="keyword">;<br />
<br />
if(</span><span class="default">$day_to_email </span><span class="keyword">== </span><span class="default">Days</span><span class="keyword">::</span><span class="default">Wednesday</span><span class="keyword">) echo </span><span class="string">"Wednesday&lt;br /&gt;"</span><span class="keyword">;<br />
if(</span><span class="default">$day_to_email </span><span class="keyword">== </span><span class="default">Days</span><span class="keyword">::</span><span class="default">Thursday</span><span class="keyword">) echo </span><span class="string">"Thursday&lt;br /&gt;"</span><span class="keyword">;<br />
if(</span><span class="default">$day_to_email </span><span class="keyword">== </span><span class="default">Days</span><span class="keyword">::</span><span class="default">Friday</span><span class="keyword">) echo </span><span class="string">"Friday&lt;br /&gt;"</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
Which would output:<br />
Thursday<br />
<br />
Or if you wanted to get a little fancier you could also do the following:<br />
<br />
<span class="default">&lt;?php<br />
$days_to_email </span><span class="keyword">= </span><span class="default">Days</span><span class="keyword">::</span><span class="default">Monday </span><span class="keyword">| </span><span class="default">Days</span><span class="keyword">::</span><span class="default">Wednesday </span><span class="keyword">| </span><span class="default">Days</span><span class="keyword">::</span><span class="default">Friday</span><span class="keyword">;<br />
<br />
if(</span><span class="default">$days_to_email </span><span class="keyword">&amp; </span><span class="default">Days</span><span class="keyword">::</span><span class="default">Monday</span><span class="keyword">) echo </span><span class="string">"Monday&lt;br /&gt;"</span><span class="keyword">;<br />
if(</span><span class="default">$days_to_email </span><span class="keyword">&amp; </span><span class="default">Days</span><span class="keyword">::</span><span class="default">Tuesday</span><span class="keyword">) echo </span><span class="string">"Tuesday&lt;br /&gt;"</span><span class="keyword">;<br />
if(</span><span class="default">$days_to_email </span><span class="keyword">&amp; </span><span class="default">Days</span><span class="keyword">::</span><span class="default">Wednesday</span><span class="keyword">) echo </span><span class="string">"Wednesday&lt;br /&gt;"</span><span class="keyword">;<br />
if(</span><span class="default">$days_to_email </span><span class="keyword">&amp; </span><span class="default">Days</span><span class="keyword">::</span><span class="default">Thursday</span><span class="keyword">) echo </span><span class="string">"Thursday&lt;br /&gt;"</span><span class="keyword">;<br />
if(</span><span class="default">$days_to_email </span><span class="keyword">&amp; </span><span class="default">Days</span><span class="keyword">::</span><span class="default">Friday</span><span class="keyword">) echo </span><span class="string">"Friday&lt;br /&gt;"</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
Which would output:<br />
Monday<br />
Wednesday<br />
Friday</span>
</code></div>
  </div>
 </div>
 <a name="53282"></a>
 <div class="note">
  <strong class='user'>S�b.</strong>
  <a href="#53282" class="date">27-May-2005 09:50</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
We can't create easily anonymous objects like in JavaScript.<br />
JS example :<br />
<br />
&nbsp;&nbsp;&nbsp; var o = {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; aProperty : "value",<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; anotherProperty : [ "element 1", "element 2" ] } ;<br />
&nbsp;&nbsp;&nbsp; alert(o.anotherProperty[1]) ; // "element 2"<br />
<br />
So I have created a class Object :<br />
<br />
<span class="default">&lt;?php<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">class </span><span class="default">Object </span><span class="keyword">{<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; function </span><span class="default">__construct</span><span class="keyword">( ) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$n </span><span class="keyword">= </span><span class="default">func_num_args</span><span class="keyword">( ) ;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; for ( </span><span class="default">$i </span><span class="keyword">= </span><span class="default">0 </span><span class="keyword">; </span><span class="default">$i </span><span class="keyword">&lt; </span><span class="default">$n </span><span class="keyword">; </span><span class="default">$i </span><span class="keyword">+= </span><span class="default">2 </span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;{</span><span class="default">func_get_arg</span><span class="keyword">(</span><span class="default">$i</span><span class="keyword">)} = </span><span class="default">func_get_arg</span><span class="keyword">(</span><span class="default">$i </span><span class="keyword">+ </span><span class="default">1</span><span class="keyword">) ;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$o </span><span class="keyword">= new </span><span class="default">Object</span><span class="keyword">(<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="string">'aProperty'</span><span class="keyword">, </span><span class="string">'value'</span><span class="keyword">,<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="string">'anotherProperty'</span><span class="keyword">, array(</span><span class="string">'element 1'</span><span class="keyword">, </span><span class="string">'element 2'</span><span class="keyword">)) ;<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="default">$o</span><span class="keyword">-&gt;</span><span class="default">anotherProperty</span><span class="keyword">[</span><span class="default">1</span><span class="keyword">] ; </span><span class="comment">// "element 2"<br />
</span><span class="default">?&gt;<br />
</span><br />
You must feel free to make it better :)</span>
</code></div>
  </div>
 </div>
 <a name="51123"></a>
 <div class="note">
  <strong class='user'>spam at afoyi dot com</strong>
  <a href="#51123" class="date">20-Mar-2005 05:18</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
You can call a function defined in an inherited class from the parent class. This works in both PHP 4.3.6 and 5.0.0:<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">class </span><span class="default">p </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">p</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; print </span><span class="string">"Parent's constructor\n"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">p_test</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; print </span><span class="string">"p_test()\n"</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">c_test</span><span class="keyword">();<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
class </span><span class="default">c </span><span class="keyword">extends </span><span class="default">p </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">c</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; print </span><span class="string">"Child's constructor\n"</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">parent</span><span class="keyword">::</span><span class="default">p</span><span class="keyword">();<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">c_test</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; print </span><span class="string">"c_test()\n"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="default">$obj </span><span class="keyword">= new </span><span class="default">c</span><span class="keyword">;<br />
</span><span class="default">$obj</span><span class="keyword">-&gt;</span><span class="default">p_test</span><span class="keyword">();<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
Outputs:<br />
<br />
Child's constructor<br />
Parent's constructor<br />
p_test()<br />
c_test()</span>
</code></div>
  </div>
 </div>
 <a name="46290"></a>
 <div class="note">
  <strong class='user'>farzan at ifarzan dot com</strong>
  <a href="#46290" class="date">05-Oct-2004 04:04</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
PHP 5 is very very flexible in accessing member variables and member functions. These access methods maybe look unusual and unnecessary at first glance; but they are very useful sometimes; specially when you work with SimpleXML classes and objects. I have posted a similar comment in SimpleXML function reference section, but this one is more comprehensive.<br />
<br />
I use the following class as reference for all examples:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">Foo </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public </span><span class="default">$aMemberVar </span><span class="keyword">= </span><span class="string">'aMemberVar Member Variable'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; public </span><span class="default">$aFuncName </span><span class="keyword">= </span><span class="string">'aMemberFunc'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">aMemberFunc</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; print </span><span class="string">'Inside `aMemberFunc()`'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="default">$foo </span><span class="keyword">= new </span><span class="default">Foo</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
You can access member variables in an object using another variable as name:<br />
<br />
<span class="default">&lt;?php<br />
$element </span><span class="keyword">= </span><span class="string">'aMemberVar'</span><span class="keyword">;<br />
print </span><span class="default">$foo</span><span class="keyword">-&gt;</span><span class="default">$element</span><span class="keyword">; </span><span class="comment">// prints "aMemberVar Member Variable"<br />
</span><span class="default">?&gt;<br />
</span><br />
or use functions:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">getVarName</span><span class="keyword">()<br />
{ return </span><span class="string">'aMemberVar'</span><span class="keyword">; }<br />
<br />
print </span><span class="default">$foo</span><span class="keyword">-&gt;{</span><span class="default">getVarName</span><span class="keyword">()}; </span><span class="comment">// prints "aMemberVar Member Variable"<br />
</span><span class="default">?&gt;<br />
</span><br />
Important Note: You must surround function name with { and } or PHP would think you are calling a member function of object "foo".<br />
<br />
you can use a constant or literal as well:<br />
<br />
<span class="default">&lt;?php<br />
define</span><span class="keyword">(</span><span class="default">MY_CONSTANT</span><span class="keyword">, </span><span class="string">'aMemberVar'</span><span class="keyword">);<br />
print </span><span class="default">$foo</span><span class="keyword">-&gt;{</span><span class="default">MY_CONSTANT</span><span class="keyword">}; </span><span class="comment">// Prints "aMemberVar Member Variable"<br />
</span><span class="keyword">print </span><span class="default">$foo</span><span class="keyword">-&gt;{</span><span class="string">'aMemberVar'</span><span class="keyword">}; </span><span class="comment">// Prints "aMemberVar Member Variable"<br />
</span><span class="default">?&gt;<br />
</span><br />
You can use members of other objects as well:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">print </span><span class="default">$foo</span><span class="keyword">-&gt;{</span><span class="default">$otherObj</span><span class="keyword">-&gt;</span><span class="default">var</span><span class="keyword">};<br />
print </span><span class="default">$foo</span><span class="keyword">-&gt;{</span><span class="default">$otherObj</span><span class="keyword">-&gt;</span><span class="default">func</span><span class="keyword">()};<br />
</span><span class="default">?&gt;<br />
</span><br />
You can use mathods above to access member functions as well:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">print </span><span class="default">$foo</span><span class="keyword">-&gt;{</span><span class="string">'aMemberFunc'</span><span class="keyword">}(); </span><span class="comment">// Prints "Inside `aMemberFunc()`"<br />
</span><span class="keyword">print </span><span class="default">$foo</span><span class="keyword">-&gt;{</span><span class="default">$foo</span><span class="keyword">-&gt;</span><span class="default">aFuncName</span><span class="keyword">}(); </span><span class="comment">// Prints "Inside `aMemberFunc()`"<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=language.oop5&amp;redirect=@w{6EHQVFYQ}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.oop5&amp;redirect=@w{6EHQVFYQ}">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/language.oop5.php">show source</a> |
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