<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Namespaces - Manual</title>
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
 <link rel="prev" href="language.oop5.changelog.php" />
 <link rel="next" href="language.namespaces.rationale.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/namespaces" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="alternate" href="/manual/en/feeds/language.namespaces.atom" type="application/atom+xml" />
 <link rel="canonical" href="http://php.net/manual/en/language.namespaces.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/language.namespaces.php" />
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
 <li><a href="language.oop5.php">Classes and Objects</a></li>
 <li class="active"><a href="language.namespaces.php">Namespaces</a></li>
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
  <a href="language.namespaces.rationale.php">Namespaces overview<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.oop5.changelog.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />OOP Changelog</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.namespaces.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/language.namespaces.php">Brazilian Portuguese</option>
    <option value="zh/language.namespaces.php">Chinese (Simplified)</option>
    <option value="fr/language.namespaces.php">French</option>
    <option value="de/language.namespaces.php">German</option>
    <option value="ja/language.namespaces.php">Japanese</option>
    <option value="pl/language.namespaces.php">Polish</option>
    <option value="ro/language.namespaces.php">Romanian</option>
    <option value="ru/language.namespaces.php">Russian</option>
    <option value="fa/language.namespaces.php">Persian</option>
    <option value="es/language.namespaces.php">Spanish</option>
    <option value="tr/language.namespaces.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="language.namespaces" class="chapter">
 <h1>Namespaces</h1>
<h2>Table of Contents</h2><ul class="chunklist chunklist_chapter"><li><a href="language.namespaces.rationale.php">Namespaces overview</a></li><li><a href="language.namespaces.definition.php">Defining namespaces</a></li><li><a href="language.namespaces.nested.php">Declaring sub-namespaces</a></li><li><a href="language.namespaces.definitionmultiple.php">Defining multiple namespaces in the same file</a></li><li><a href="language.namespaces.basics.php">Using namespaces: Basics</a></li><li><a href="language.namespaces.dynamic.php">Namespaces and dynamic language features</a></li><li><a href="language.namespaces.nsconstants.php">namespace keyword and __NAMESPACE__ constant</a></li><li><a href="language.namespaces.importing.php">Using namespaces: Aliasing/Importing</a></li><li><a href="language.namespaces.global.php">Global space</a></li><li><a href="language.namespaces.fallback.php">Using namespaces: fallback to global function/constant</a></li><li><a href="language.namespaces.rules.php">Name resolution rules</a></li><li><a href="language.namespaces.faq.php">FAQ: things you need to know about namespaces</a></li></ul>


 

 
 
 
 
 
 
 
 
 

 
 
</div>
<br /><br />
<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=language.namespaces&amp;redirect=http://www.php.net/manual/en/language.namespaces.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.namespaces&amp;redirect=http://www.php.net/manual/en/language.namespaces.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Namespaces</strong>
 </div><div id="allnotes">
 <a name="104136"></a>
 <div class="note">
  <strong class='user'>Anonymous</strong>
  <a href="#104136" class="date">25-May-2011 11:06</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The keyword 'use' has two different applications, but the reserved word table links to here.<br />
<br />
It can apply to namespace constucts:<br />
<br />
file1:<br />
<span class="default">&lt;?php namespace foo</span><span class="keyword">;<br />
&nbsp; class </span><span class="default">Cat </span><span class="keyword">{ <br />
&nbsp;&nbsp;&nbsp; static function </span><span class="default">says</span><span class="keyword">() {echo </span><span class="string">'meoow'</span><span class="keyword">;}&nbsp; } </span><span class="default">?&gt;<br />
</span><br />
file2:<br />
<span class="default">&lt;?php namespace bar</span><span class="keyword">;<br />
&nbsp; class </span><span class="default">Dog </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; static function </span><span class="default">says</span><span class="keyword">() {echo </span><span class="string">'ruff'</span><span class="keyword">;}&nbsp; } </span><span class="default">?&gt;<br />
</span><br />
file3:<br />
<span class="default">&lt;?php namespace animate</span><span class="keyword">;<br />
&nbsp; class </span><span class="default">Animal </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; static function </span><span class="default">breathes</span><span class="keyword">() {echo </span><span class="string">'air'</span><span class="keyword">;}&nbsp; } </span><span class="default">?&gt;<br />
</span><br />
file4:<br />
<span class="default">&lt;?php namespace fub</span><span class="keyword">;<br />
&nbsp; include </span><span class="string">'file1.php'</span><span class="keyword">;<br />
&nbsp; include </span><span class="string">'file2.php'</span><span class="keyword">;<br />
&nbsp; include </span><span class="string">'file3.php'</span><span class="keyword">;<br />
&nbsp; use </span><span class="default">foo </span><span class="keyword">as </span><span class="default">feline</span><span class="keyword">;<br />
&nbsp; use </span><span class="default">bar </span><span class="keyword">as </span><span class="default">canine</span><span class="keyword">;<br />
&nbsp; use </span><span class="default">animate</span><span class="keyword">;<br />
&nbsp; echo </span><span class="default">felineCat</span><span class="keyword">::</span><span class="default">says</span><span class="keyword">(), </span><span class="string">"&lt;br /&gt;\n"</span><span class="keyword">;<br />
&nbsp; echo </span><span class="default">canineDog</span><span class="keyword">::</span><span class="default">says</span><span class="keyword">(), </span><span class="string">"&lt;br /&gt;\n"</span><span class="keyword">;<br />
&nbsp; echo </span><span class="default">animateAnimal</span><span class="keyword">::</span><span class="default">breathes</span><span class="keyword">(), </span><span class="string">"&lt;br /&gt;\n"</span><span class="keyword">;&nbsp; </span><span class="default">?&gt;<br />
</span><br />
Note that <br />
felineCat::says()<br />
should be<br />
\feline\Cat::says()<br />
(and similar for the others)<br />
but this comment form deletes the backslash (why???) <br />
<br />
The 'use' keyword also applies to closure constructs:<br />
<br />
<span class="default">&lt;?php </span><span class="keyword">function </span><span class="default">getTotal</span><span class="keyword">(</span><span class="default">$products_costs</span><span class="keyword">, </span><span class="default">$tax</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$total </span><span class="keyword">= </span><span class="default">0.00</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$callback </span><span class="keyword">=<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; function (</span><span class="default">$pricePerItem</span><span class="keyword">) use (</span><span class="default">$tax</span><span class="keyword">, &amp;</span><span class="default">$total</span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$total </span><span class="keyword">+= </span><span class="default">$pricePerItem </span><span class="keyword">* (</span><span class="default">$tax </span><span class="keyword">+ </span><span class="default">1.0</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; };<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">array_walk</span><span class="keyword">(</span><span class="default">$products_costs</span><span class="keyword">, </span><span class="default">$callback</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">round</span><span class="keyword">(</span><span class="default">$total</span><span class="keyword">, </span><span class="default">2</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="103606"></a>
 <div class="note">
  <strong class='user'>netmosfera at gmail dot com</strong>
  <a href="#103606" class="date">21-Apr-2011 11:09</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
for example, if you use a lot of .php files (once per class) you need to know that require() a lot of files will slow down consistently the page load<br />
<br />
so you can use namespaces + autoload to implement package-specific initialization handlers<br />
<br />
i used this to put an entire package (lot of classes) in one php file<br />
<br />
!!! please note you can't use this as-is but you need to adapt it to your project<br />
<br />
the file __init.php is placed inside every namespace/folder you want to load<br />
there you can startup your namespace, check for environment, debugging, or as i do, you can merge all the package in one php file and require it to load other classes too.<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">class </span><span class="default">Loader<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">// here we store the already-initialized namespaces<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">private static </span><span class="default">$loadedNamespaces </span><span class="keyword">= array();<br />
&nbsp; <br />
&nbsp;&nbsp;&nbsp; static function </span><span class="default">loadClass</span><span class="keyword">(</span><span class="default">$className</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// we assume the class AAA\BBB\CCC is placed in /AAA/BBB/CCC.php<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$className </span><span class="keyword">= </span><span class="default">str_replace</span><span class="keyword">(array(</span><span class="string">'/'</span><span class="keyword">, </span><span class="string">'\\'</span><span class="keyword">), </span><span class="default">DIRECTORY_SEPARATOR</span><span class="keyword">, </span><span class="default">$className</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// we get the namespace parts<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$namespaces </span><span class="keyword">= </span><span class="default">explode</span><span class="keyword">(</span><span class="default">DIRECTORY_SEPARATOR</span><span class="keyword">, </span><span class="default">$className</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; unset(</span><span class="default">$namespaces</span><span class="keyword">[</span><span class="default">sizeof</span><span class="keyword">(</span><span class="default">$namespaces</span><span class="keyword">)-</span><span class="default">1</span><span class="keyword">]); </span><span class="comment">// the last item is the classname<br />
&nbsp;&nbsp; &nbsp; &nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; // now we loops over namespaces<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$current</span><span class="keyword">=</span><span class="string">""</span><span class="keyword">; foreach(</span><span class="default">$namespaces </span><span class="keyword">as </span><span class="default">$namepart</span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// we chain $namepart to parent namespace string<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$current</span><span class="keyword">.=</span><span class="string">'\\' </span><span class="keyword">. </span><span class="default">$namepart</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// skip if the namespace is already initialized<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">if(</span><span class="default">in_array</span><span class="keyword">(</span><span class="default">$current</span><span class="keyword">, </span><span class="default">self</span><span class="keyword">::</span><span class="default">$loadedNamespaces</span><span class="keyword">)) continue;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// wow, we got a namespace to load, so:<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$fnload </span><span class="keyword">= </span><span class="default">$current </span><span class="keyword">. </span><span class="default">DIRECTORY_SEPARATOR </span><span class="keyword">. </span><span class="string">"__init.php"</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; if(</span><span class="default">file_exists</span><span class="keyword">(</span><span class="default">$fnload</span><span class="keyword">)) require(</span><span class="default">$fnload</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// then we flag the namespace as already-loaded<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">self</span><span class="keyword">::</span><span class="default">$loadedNamespaces</span><span class="keyword">[] = </span><span class="default">$current</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// we build the filename to require<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$load </span><span class="keyword">= </span><span class="default">$className </span><span class="keyword">. </span><span class="string">".php"</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// check for file existence<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">!</span><span class="default">file_exists</span><span class="keyword">(</span><span class="default">$load</span><span class="keyword">) ?: require(</span><span class="default">$load</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// return true if class is loaded<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">return </span><span class="default">class_exists</span><span class="keyword">(</span><span class="default">$className</span><span class="keyword">, </span><span class="default">false</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; static function </span><span class="default">register</span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">spl_autoload_register</span><span class="keyword">(</span><span class="string">"Loader::loadClass"</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; static function </span><span class="default">unregister</span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">spl_autoload_unregister</span><span class="keyword">(</span><span class="string">"Loader::loadClass"</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="default">Loader</span><span class="keyword">::</span><span class="default">register</span><span class="keyword">();<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=language.namespaces&amp;redirect=http://www.php.net/manual/en/language.namespaces.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.namespaces&amp;redirect=http://www.php.net/manual/en/language.namespaces.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/language.namespaces.php">show source</a> |
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