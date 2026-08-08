<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Object Cloning - Manual</title>
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
 <link rel="prev" href="language.oop5.final.php" />
 <link rel="next" href="language.oop5.object-comparison.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/oop5.cloning" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/language.oop5.cloning.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/language.oop5.cloning.php" />
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
 <li><a href="language.oop5.final.php">Final Keyword</a></li>
 <li class="active"><a href="language.oop5.cloning.php">Object Cloning</a></li>
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
  <a href="language.oop5.object-comparison.php">Comparing Objects<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.oop5.final.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Final Keyword</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.oop5.cloning.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/language.oop5.cloning.php">Brazilian Portuguese</option>
    <option value="zh/language.oop5.cloning.php">Chinese (Simplified)</option>
    <option value="fr/language.oop5.cloning.php">French</option>
    <option value="de/language.oop5.cloning.php">German</option>
    <option value="ja/language.oop5.cloning.php">Japanese</option>
    <option value="pl/language.oop5.cloning.php">Polish</option>
    <option value="ro/language.oop5.cloning.php">Romanian</option>
    <option value="ru/language.oop5.cloning.php">Russian</option>
    <option value="fa/language.oop5.cloning.php">Persian</option>
    <option value="es/language.oop5.cloning.php">Spanish</option>
    <option value="tr/language.oop5.cloning.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="language.oop5.cloning" class="sect1">
  <h2 class="title">Object Cloning</h2>
  
  <p class="para">
   Creating a copy of an object with fully replicated properties is not
   always the wanted behavior. A good example of the need for copy
   constructors, is if you have an object which represents a GTK window and the
   object holds the resource of this GTK window, when you create a duplicate
   you might want to create a new window with the same properties and have the
   new object hold the resource of the new window. Another example is if your
   object holds a reference to another object which it uses and when you
   replicate the parent object you want to create a new instance of this other
   object so that the replica has its own separate copy.
  </p>

  <p class="para">
   An object copy is created by using the clone keyword (which calls the
   object&#039;s <a href="language.oop5.cloning.php#object.clone" class="link">__clone()</a> method if possible).
   An object&#039;s <a href="language.oop5.cloning.php#object.clone" class="link">__clone()</a> method
   cannot be called directly.
  </p>

  <div class="informalexample">
   <div class="example-contents">
<div class="cdata"><pre>
$copy_of_object = clone $object;
</pre></div>
   </div>

  </div>

  <p class="para">
   When an object is cloned, PHP 5 will perform a shallow copy of all of the
   object&#039;s properties. Any properties that are references to other variables,
   will remain references.
  </p>

  <div class="methodsynopsis dc-description" id="object.clone">
   <span class="type"><span class="type void">void</span></span> <span class="methodname"><strong>__clone</strong></span>
    ( <span class="methodparam">void</span>
   )</div>


  <p class="para">
   Once the cloning is complete, if a <a href="language.oop5.cloning.php#object.clone" class="link">__clone()</a> method is defined, then
   the newly created object&#039;s <a href="language.oop5.cloning.php#object.clone" class="link">__clone()</a> method will be called, to allow any
   necessary properties that need to be changed.
  </p>

  <div class="example" id="example-218">
   <p><strong>Example #1 Cloning an object</strong></p>
   <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">class&nbsp;</span><span style="color: #0000BB">SubObject<br /></span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;static&nbsp;</span><span style="color: #0000BB">$instances&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">0</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;</span><span style="color: #0000BB">$instance</span><span style="color: #007700">;<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">__construct</span><span style="color: #007700">()&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">instance&nbsp;</span><span style="color: #007700">=&nbsp;++</span><span style="color: #0000BB">self</span><span style="color: #007700">::</span><span style="color: #0000BB">$instances</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">__clone</span><span style="color: #007700">()&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">instance&nbsp;</span><span style="color: #007700">=&nbsp;++</span><span style="color: #0000BB">self</span><span style="color: #007700">::</span><span style="color: #0000BB">$instances</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br /><br />class&nbsp;</span><span style="color: #0000BB">MyCloneable<br /></span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;</span><span style="color: #0000BB">$object1</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;</span><span style="color: #0000BB">$object2</span><span style="color: #007700">;<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;function&nbsp;</span><span style="color: #0000BB">__clone</span><span style="color: #007700">()<br />&nbsp;&nbsp;&nbsp;&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;Force&nbsp;a&nbsp;copy&nbsp;of&nbsp;this-&gt;object,&nbsp;otherwise<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;//&nbsp;it&nbsp;will&nbsp;point&nbsp;to&nbsp;same&nbsp;object.<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">object1&nbsp;</span><span style="color: #007700">=&nbsp;clone&nbsp;</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">object1</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br /><br /></span><span style="color: #0000BB">$obj&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">MyCloneable</span><span style="color: #007700">();<br /><br /></span><span style="color: #0000BB">$obj</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">object1&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">SubObject</span><span style="color: #007700">();<br /></span><span style="color: #0000BB">$obj</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">object2&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">SubObject</span><span style="color: #007700">();<br /><br /></span><span style="color: #0000BB">$obj2&nbsp;</span><span style="color: #007700">=&nbsp;clone&nbsp;</span><span style="color: #0000BB">$obj</span><span style="color: #007700">;<br /><br /><br />print(</span><span style="color: #DD0000">"Original&nbsp;Object:\n"</span><span style="color: #007700">);<br /></span><span style="color: #0000BB">print_r</span><span style="color: #007700">(</span><span style="color: #0000BB">$obj</span><span style="color: #007700">);<br /><br />print(</span><span style="color: #DD0000">"Cloned&nbsp;Object:\n"</span><span style="color: #007700">);<br /></span><span style="color: #0000BB">print_r</span><span style="color: #007700">(</span><span style="color: #0000BB">$obj2</span><span style="color: #007700">);<br /><br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
   </div>

   <div class="example-contents"><p>The above example will output:</p></div>
   <div class="example-contents screen">
<div class="cdata"><pre>
Original Object:
MyCloneable Object
(
    [object1] =&gt; SubObject Object
        (
            [instance] =&gt; 1
        )

    [object2] =&gt; SubObject Object
        (
            [instance] =&gt; 2
        )

)
Cloned Object:
MyCloneable Object
(
    [object1] =&gt; SubObject Object
        (
            [instance] =&gt; 3
        )

    [object2] =&gt; SubObject Object
        (
            [instance] =&gt; 2
        )

)
</pre></div>

   </div>

  </div>

 </div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="language.oop5.object-comparison.php">Comparing Objects<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.oop5.final.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Final Keyword</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.oop5.cloning.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=language.oop5.cloning&amp;redirect=http://www.php.net/manual/en/language.oop5.cloning.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.oop5.cloning&amp;redirect=http://www.php.net/manual/en/language.oop5.cloning.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Object Cloning</strong>
 </div><div id="allnotes">
 <a name="108938"></a>
 <div class="note">
  <strong class='user'>seriously at something dot com</strong>
  <a href="#108938" class="date">06-Jun-2012 04:58</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you want a property that gets the same value in every clone if changed, you can do this simple trick:<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">class </span><span class="default">A<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public static </span><span class="default">$name </span><span class="keyword">;<br />
<br />
}<br />
<br />
</span><span class="default">$a </span><span class="keyword">= new </span><span class="default">A</span><span class="keyword">;<br />
</span><span class="default">$a</span><span class="keyword">::</span><span class="default">$name </span><span class="keyword">= </span><span class="string">'George'</span><span class="keyword">;<br />
<br />
</span><span class="default">$b </span><span class="keyword">= clone </span><span class="default">$a</span><span class="keyword">;<br />
</span><span class="default">$b</span><span class="keyword">::</span><span class="default">$name </span><span class="keyword">= </span><span class="string">"Somebody else"</span><span class="keyword">;<br />
<br />
echo </span><span class="string">'a: ' </span><span class="keyword">. </span><span class="default">$a</span><span class="keyword">::</span><span class="default">$name </span><span class="keyword">. </span><span class="string">"\n"</span><span class="keyword">;<br />
echo </span><span class="string">'b: ' </span><span class="keyword">. </span><span class="default">$b</span><span class="keyword">::</span><span class="default">$name </span><span class="keyword">. </span><span class="string">"\n"</span><span class="keyword">;<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
this will output:<br />
<br />
a: Somebody else<br />
b: Somebody else<br />
<br />
You can change any of the clones property and all of the others will change accordingly.</span>
</code></div>
  </div>
 </div>
 <a name="106135"></a>
 <div class="note">
  <strong class='user'>walkman at walkman dot pk</strong>
  <a href="#106135" class="date">13-Oct-2011 05:11</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you want a property that gets the same value in every clone if changed, you can do this simple trick:<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">class </span><span class="default">A<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public </span><span class="default">$name </span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">__construct</span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">name </span><span class="keyword">= &amp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">name</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="default">$a </span><span class="keyword">= new </span><span class="default">A</span><span class="keyword">;<br />
</span><span class="default">$a</span><span class="keyword">-&gt;</span><span class="default">name </span><span class="keyword">= </span><span class="string">"George"</span><span class="keyword">;<br />
<br />
</span><span class="default">$b </span><span class="keyword">= clone </span><span class="default">$a</span><span class="keyword">;<br />
</span><span class="default">$b</span><span class="keyword">-&gt;</span><span class="default">name </span><span class="keyword">= </span><span class="string">"Somebody else"</span><span class="keyword">;<br />
<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$a</span><span class="keyword">);<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$b</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span><br />
this will output:<br />
<br />
object(A)#1 (1) {<br />
&nbsp; ["name"]=&gt;<br />
&nbsp; &amp;string(13) "Somebody else"<br />
}<br />
object(A)#2 (1) {<br />
&nbsp; ["name"]=&gt;<br />
&nbsp; &amp;string(13) "Somebody else"<br />
}<br />
<br />
You can change any of the clones property and all of the others will change accordingly.</span>
</code></div>
  </div>
 </div>
 <a name="103217"></a>
 <div class="note">
  <strong class='user'>user at somethiong dot com</strong>
  <a href="#103217" class="date">01-Apr-2011 04:35</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Creating a Deep Copy<br />
<br />
<span class="default">&lt;?php<br />
&nbsp;</span><span class="keyword">protected function </span><span class="default">deepCopy</span><span class="keyword">(</span><span class="default">$object</span><span class="keyword">){<br />
&nbsp;&nbsp; &nbsp; &nbsp; return </span><span class="default">unserialize</span><span class="keyword">(</span><span class="default">serialize</span><span class="keyword">(</span><span class="default">$object</span><span class="keyword">));<br />
&nbsp;&nbsp; }</span><span class="comment">// End Function<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="100844"></a>
 <div class="note">
  <strong class='user'>gratcypalma at gmail dot com</strong>
  <a href="#100844" class="date">10-Nov-2010 08:12</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
this is my simple method, use class_alias function<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">Y </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">__construct</span><span class="keyword">(</span><span class="default">$a</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this </span><span class="keyword">-&gt; </span><span class="default">a </span><span class="keyword">= </span><span class="default">$a</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">__destruct</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="default">$this </span><span class="keyword">-&gt; </span><span class="default">a</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="default">$mam </span><span class="keyword">= new </span><span class="default">Y</span><span class="keyword">(</span><span class="string">'Foo'</span><span class="keyword">);<br />
<br />
</span><span class="default">class_alias</span><span class="keyword">(</span><span class="string">'Y'</span><span class="keyword">, </span><span class="string">'bar'</span><span class="keyword">); </span><span class="comment">// clone class use class_alias function<br />
<br />
</span><span class="default">$m </span><span class="keyword">= new </span><span class="default">bar</span><span class="keyword">(</span><span class="string">'Bar'</span><span class="keyword">);<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="98297"></a>
 <div class="note">
  <strong class='user'>jojor at gmx dot net</strong>
  <a href="#98297" class="date">07-Jun-2010 01:47</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Here is test script i wrote to test the behaviour of clone when i have arrays with primitive values in my class - as an additonal test of the note below by jeffrey at whinger dot nl<br />
<br />
&lt;pre&gt;<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">class </span><span class="default">MyClass </span><span class="keyword">{<br />
<br />
&nbsp;&nbsp;&nbsp; private </span><span class="default">$myArray </span><span class="keyword">= array();<br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">pushSomethingToArray</span><span class="keyword">(</span><span class="default">$var</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">array_push</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">myArray</span><span class="keyword">, </span><span class="default">$var</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">getArray</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">myArray</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
}<br />
<br />
</span><span class="comment">//push some values to the myArray of Mainclass<br />
</span><span class="default">$myObj </span><span class="keyword">= new </span><span class="default">MyClass</span><span class="keyword">();<br />
</span><span class="default">$myObj</span><span class="keyword">-&gt;</span><span class="default">pushSomethingToArray</span><span class="keyword">(</span><span class="string">'blue'</span><span class="keyword">);<br />
</span><span class="default">$myObj</span><span class="keyword">-&gt;</span><span class="default">pushSomethingToArray</span><span class="keyword">(</span><span class="string">'orange'</span><span class="keyword">);<br />
</span><span class="default">$myObjClone </span><span class="keyword">= clone </span><span class="default">$myObj</span><span class="keyword">;<br />
</span><span class="default">$myObj</span><span class="keyword">-&gt;</span><span class="default">pushSomethingToArray</span><span class="keyword">(</span><span class="string">'pink'</span><span class="keyword">);<br />
<br />
</span><span class="comment">//testing<br />
</span><span class="default">print_r</span><span class="keyword">(</span><span class="default">$myObj</span><span class="keyword">-&gt;</span><span class="default">getArray</span><span class="keyword">());&nbsp; &nbsp;&nbsp; </span><span class="comment">//Array([0] =&gt; blue,[1] =&gt; orange,[2] =&gt; pink)<br />
</span><span class="default">print_r</span><span class="keyword">(</span><span class="default">$myObjClone</span><span class="keyword">-&gt;</span><span class="default">getArray</span><span class="keyword">());</span><span class="comment">//Array([0] =&gt; blue,[1] =&gt; orange)<br />
//so array&nbsp; cloned <br />
<br />
</span><span class="default">?&gt;<br />
</span>&lt;/pre&gt;</span>
</code></div>
  </div>
 </div>
 <a name="97348"></a>
 <div class="note">
  <strong class='user'>jeffrey at whinger dot nl</strong>
  <a href="#97348" class="date">15-Apr-2010 05:41</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
For me it wasn't very clear to how this cloning of objects really worked so I made this little bit of code:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">foo<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public </span><span class="default">$test</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">test</span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">'give us a '</span><span class="keyword">.</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">test</span><span class="keyword">.</span><span class="string">"&lt;br&gt;\n"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
class </span><span class="default">bar<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public </span><span class="default">$foo</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">insertFoo</span><span class="keyword">(</span><span class="default">$foo</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">foo </span><span class="keyword">= </span><span class="default">$foo</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="default">$foo </span><span class="keyword">= new </span><span class="default">foo</span><span class="keyword">();<br />
<br />
</span><span class="default">$foo</span><span class="keyword">-&gt;</span><span class="default">test </span><span class="keyword">= </span><span class="string">'foo'</span><span class="keyword">;<br />
<br />
</span><span class="default">$bar </span><span class="keyword">= new </span><span class="default">bar</span><span class="keyword">();<br />
<br />
</span><span class="default">$bar</span><span class="keyword">-&gt;</span><span class="default">insertFoo</span><span class="keyword">(</span><span class="default">$foo</span><span class="keyword">);<br />
<br />
</span><span class="default">$foo</span><span class="keyword">-&gt;</span><span class="default">test</span><span class="keyword">();<br />
<br />
</span><span class="default">$bar</span><span class="keyword">-&gt;</span><span class="default">foo</span><span class="keyword">-&gt;</span><span class="default">test</span><span class="keyword">();<br />
<br />
</span><span class="default">$foo</span><span class="keyword">-&gt;</span><span class="default">test </span><span class="keyword">= </span><span class="string">'bar'</span><span class="keyword">;<br />
<br />
</span><span class="default">$foo</span><span class="keyword">-&gt;</span><span class="default">test</span><span class="keyword">();<br />
<br />
</span><span class="default">$bar</span><span class="keyword">-&gt;</span><span class="default">foo</span><span class="keyword">-&gt;</span><span class="default">test</span><span class="keyword">();<br />
<br />
</span><span class="default">$bar</span><span class="keyword">-&gt;</span><span class="default">foo </span><span class="keyword">= clone </span><span class="default">$foo</span><span class="keyword">;<br />
<br />
</span><span class="default">$bar</span><span class="keyword">-&gt;</span><span class="default">foo</span><span class="keyword">-&gt;</span><span class="default">test </span><span class="keyword">= </span><span class="string">'woop woop'</span><span class="keyword">;<br />
<br />
</span><span class="default">$foo</span><span class="keyword">-&gt;</span><span class="default">test</span><span class="keyword">();<br />
<br />
</span><span class="default">$bar</span><span class="keyword">-&gt;</span><span class="default">foo</span><span class="keyword">-&gt;</span><span class="default">test</span><span class="keyword">();<br />
<br />
</span><span class="comment">// result:<br />
// give us a foo<br />
// give us a foo<br />
// give us a bar<br />
// give us a bar<br />
// give us a bar<br />
// give us a woop woop<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="97249"></a>
 <div class="note">
  <strong class='user'>henke at henke37 dot cjb dot net</strong>
  <a href="#97249" class="date">10-Apr-2010 03:31</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Arrays are shallow cloned on assignment, so don't use the clone keyword on them, just assign it to a new variable. That would lead to an error instead.</span>
</code></div>
  </div>
 </div>
 <a name="96852"></a>
 <div class="note">
  <strong class='user'>olivier dot pons at goo dot without dot oo dot mail dot com</strong>
  <a href="#96852" class="date">18-Mar-2010 10:14</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you think "clone" will create a new instance, thus calling "__constructor", you're wrong. clone seems to only allocate memory for the object cloned, and simply copies the variables memory from the original to the new one (imagine something alike memcpy() in C). Nothing more. Keep in mind you'll have to do all the rest by yourself.</span>
</code></div>
  </div>
 </div>
 <a name="96493"></a>
 <div class="note">
  <strong class='user'>emile at webflow dot nl</strong>
  <a href="#96493" class="date">02-Mar-2010 01:27</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Another gotcha I encountered: like __construct and __desctruct, you must call parent::__clone() yourself from inside a child's __clone() function. The manual kind of got me on the wrong foot here: "An object's __clone() method cannot be called directly."</span>
</code></div>
  </div>
 </div>
 <a name="91323"></a>
 <div class="note">
  <strong class='user'>ben at last dot fm</strong>
  <a href="#91323" class="date">05-Jun-2009 10:33</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Here are some cloning and reference gotchas we came up against at Last.fm.<br />
<br />
1. PHP treats variables as either 'values types' or 'reference types', where the difference is supposed to be transparent. Object cloning is one of the few times when it can make a big difference. I know of no programmatic way to tell if a variable is intrinsically a value or reference type. There IS however a non-programmatic ways to tell if an object property is value or reference type:<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">class </span><span class="default">A </span><span class="keyword">{ var </span><span class="default">$p</span><span class="keyword">; }<br />
<br />
</span><span class="default">$a </span><span class="keyword">= new </span><span class="default">A</span><span class="keyword">;<br />
</span><span class="default">$a</span><span class="keyword">-&gt;</span><span class="default">p </span><span class="keyword">= </span><span class="string">'Hello'</span><span class="keyword">; </span><span class="comment">// $a-&gt;p is a value type<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$a</span><span class="keyword">);<br />
<br />
</span><span class="comment">/*<br />
object(A)#1 (1) {<br />
&nbsp; ["p"]=&gt;<br />
&nbsp; string(5) "Hello" // &lt;-- no &amp;<br />
}<br />
*/<br />
<br />
</span><span class="default">$ref </span><span class="keyword">=&amp; </span><span class="default">$a</span><span class="keyword">-&gt;</span><span class="default">p</span><span class="keyword">; </span><span class="comment">// note that this CONVERTS $a-&gt;p into a reference type!!<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$a</span><span class="keyword">);<br />
<br />
</span><span class="comment">/*<br />
object(A)#1 (1) {<br />
&nbsp; ["p"]=&gt;<br />
&nbsp; &amp;string(5) "Hello" // &lt;-- note the &amp;, this indicates it's a reference.<br />
}<br />
*/<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
2. unsetting all-but-one of the references will convert the remaining reference back into a value. Continuing from the previous example:<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">unset(</span><span class="default">$ref</span><span class="keyword">);<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$a</span><span class="keyword">);<br />
<br />
</span><span class="comment">/*<br />
object(A)#1 (1) {<br />
&nbsp; ["p"]=&gt;<br />
&nbsp; string(5) "Hello"<br />
}<br />
*/<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
I interpret this as the reference-count jumping from 2 straight to 0. However...<br />
<br />
2. It IS possible to create a reference with a reference count of 1 - i.e. to convert an property from value type to reference type, without any extra references. All you have to do is declare that it refers to itself. This is HIGHLY idiosyncratic, but nevertheless it works. This leads to the observation that although the manual states that 'Any properties that are references to other variables, will remain references,' this is not strictly true. Any variables that are references, even to *themselves* (not necessarily to other variables), will be copied by reference rather than by value. <br />
<br />
Here's an example to demonstrate:<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">class </span><span class="default">ByVal<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; var </span><span class="default">$prop</span><span class="keyword">;<br />
}<br />
<br />
class </span><span class="default">ByRef<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; var </span><span class="default">$prop</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">__construct</span><span class="keyword">() { </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">prop </span><span class="keyword">=&amp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">prop</span><span class="keyword">; }<br />
}<br />
<br />
</span><span class="default">$a </span><span class="keyword">= new </span><span class="default">ByVal</span><span class="keyword">;<br />
</span><span class="default">$a</span><span class="keyword">-&gt;</span><span class="default">prop </span><span class="keyword">= </span><span class="default">1</span><span class="keyword">;<br />
</span><span class="default">$b </span><span class="keyword">= clone </span><span class="default">$a</span><span class="keyword">;<br />
</span><span class="default">$b</span><span class="keyword">-&gt;</span><span class="default">prop </span><span class="keyword">= </span><span class="default">2</span><span class="keyword">; </span><span class="comment">// $a-&gt;prop remains at 1<br />
<br />
</span><span class="default">$a </span><span class="keyword">= new </span><span class="default">ByRef</span><span class="keyword">;<br />
</span><span class="default">$a</span><span class="keyword">-&gt;</span><span class="default">prop </span><span class="keyword">= </span><span class="default">1</span><span class="keyword">;<br />
</span><span class="default">$b </span><span class="keyword">= clone </span><span class="default">$a</span><span class="keyword">;<br />
</span><span class="default">$b</span><span class="keyword">-&gt;</span><span class="default">prop </span><span class="keyword">= </span><span class="default">2</span><span class="keyword">; </span><span class="comment">// $a-&gt;prop is now 2<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="89321"></a>
 <div class="note">
  <strong class='user'>koyama</strong>
  <a href="#89321" class="date">03-Mar-2009 07:28</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The __clone() method for deep cloning by cheetah at tanabi dot org also works when the object to be cloned contains references to itself. This is not the case for any variation of the __clone() method in edit by danbrown at php dot net.<br />
<br />
We are taking advantage of the fact that one can serialize an object that references itself.<br />
<br />
Example:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">Foo<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">__construct</span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">_myself </span><span class="keyword">= </span><span class="default">$this</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">__clone</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; foreach (</span><span class="default">$this </span><span class="keyword">as </span><span class="default">$key </span><span class="keyword">=&gt; </span><span class="default">$val</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; if (</span><span class="default">is_object</span><span class="keyword">(</span><span class="default">$val</span><span class="keyword">) || (</span><span class="default">is_array</span><span class="keyword">(</span><span class="default">$val</span><span class="keyword">))) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;{</span><span class="default">$key</span><span class="keyword">} = </span><span class="default">unserialize</span><span class="keyword">(</span><span class="default">serialize</span><span class="keyword">(</span><span class="default">$val</span><span class="keyword">));<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="comment">// this object references itself<br />
</span><span class="default">$foo </span><span class="keyword">= new </span><span class="default">Foo</span><span class="keyword">();<br />
<br />
</span><span class="comment">// create a deep clone<br />
</span><span class="default">$bar </span><span class="keyword">= clone </span><span class="default">$foo</span><span class="keyword">;<br />
<br />
</span><span class="comment">// check if we reach this point<br />
</span><span class="keyword">echo </span><span class="string">'Finished cloning!'</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
Replacing the __clone() method with the one shown in edit by danbrown at php dot net we run into an infinite loop, and we never get message 'Finished cloning!'.</span>
</code></div>
  </div>
 </div>
 <a name="87066"></a>
 <div class="note">
  <strong class='user'>cheetah at tanabi dot org</strong>
  <a href="#87066" class="date">18-Nov-2008 05:15</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Want deep cloning without too much hassle?<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">__clone</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; foreach(</span><span class="default">$this </span><span class="keyword">as </span><span class="default">$key </span><span class="keyword">=&gt; </span><span class="default">$val</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if(</span><span class="default">is_object</span><span class="keyword">(</span><span class="default">$val</span><span class="keyword">)||(</span><span class="default">is_array</span><span class="keyword">(</span><span class="default">$val</span><span class="keyword">))){<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;{</span><span class="default">$key</span><span class="keyword">} = </span><span class="default">unserialize</span><span class="keyword">(</span><span class="default">serialize</span><span class="keyword">(</span><span class="default">$val</span><span class="keyword">));<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
That will insure any object, or array that may potentially contain objects, will get cloned without using recursion or other support methods.<br />
<br />
<br />
<br />
[EDIT BY danbrown AT php DOT net: An almost exact function was contributed on 02-DEC-2008-10:18 by (david ashe AT metabin):<br />
<br />
<span class="default">&lt;?php<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">function </span><span class="default">__clone</span><span class="keyword">(){<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; foreach(</span><span class="default">$this </span><span class="keyword">as </span><span class="default">$name </span><span class="keyword">=&gt; </span><span class="default">$value</span><span class="keyword">){<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; if(</span><span class="default">gettype</span><span class="keyword">(</span><span class="default">$value</span><span class="keyword">)==</span><span class="string">'object'</span><span class="keyword">){<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">$name</span><span class="keyword">= clone(</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">$name</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; }<br />
</span><span class="default">?&gt;<br />
</span><br />
Giving credit where it's due.&nbsp; ~DPB]</span>
</code></div>
  </div>
 </div>
 <a name="86093"></a>
 <div class="note">
  <strong class='user'>wbcarts at juno dot com</strong>
  <a href="#86093" class="date">02-Oct-2008 11:41</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
CLONED ARMIES? USE STATIC DATA<br />
<br />
When I think of cloning, I always think of Star Wars "Cloned Army"... where the number of clones are in the hundreds of thousands. So far, I have only seen examples of one or two clones with either shallow, deep, or recursive references. My fix is to use the static keyword. With static, you choose the properties your objects share... and makes scaling up the number of so-called "clones" much easier.<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">class </span><span class="default">Soldier </span><span class="keyword">{<br />
&nbsp; public static </span><span class="default">$status</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// this is the property I'm trying to clone<br />
<br />
&nbsp; </span><span class="keyword">protected static </span><span class="default">$idCount </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">;&nbsp;&nbsp; </span><span class="comment">// used to increment ID numbers<br />
&nbsp; </span><span class="keyword">protected </span><span class="default">$id</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// each Soldier will have a unique ID<br />
<br />
&nbsp; </span><span class="keyword">public function </span><span class="default">__construct</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">id </span><span class="keyword">= ++</span><span class="default">self</span><span class="keyword">::</span><span class="default">$idCount</span><span class="keyword">;<br />
&nbsp; }&nbsp; <br />
<br />
&nbsp; public function </span><span class="default">issueCommand</span><span class="keyword">(</span><span class="default">$task</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; switch(</span><span class="default">$task</span><span class="keyword">){<br />
&nbsp;&nbsp; &nbsp;&nbsp; case </span><span class="string">'Deploy Troops'</span><span class="keyword">: </span><span class="default">self</span><span class="keyword">::</span><span class="default">$status </span><span class="keyword">= </span><span class="string">'deploying'</span><span class="keyword">; break;<br />
&nbsp;&nbsp; &nbsp;&nbsp; case </span><span class="string">'March Forward'</span><span class="keyword">: </span><span class="default">self</span><span class="keyword">::</span><span class="default">$status </span><span class="keyword">= </span><span class="string">'marching forward'</span><span class="keyword">; break;<br />
&nbsp;&nbsp; &nbsp;&nbsp; case </span><span class="string">'Fire!'</span><span class="keyword">: </span><span class="default">self</span><span class="keyword">::</span><span class="default">$status </span><span class="keyword">= </span><span class="string">'shot fired'</span><span class="keyword">; break;<br />
&nbsp;&nbsp; &nbsp;&nbsp; case </span><span class="string">'Retreat!'</span><span class="keyword">: </span><span class="default">self</span><span class="keyword">::</span><span class="default">$status </span><span class="keyword">= </span><span class="string">'course reversed'</span><span class="keyword">; break;<br />
&nbsp;&nbsp; &nbsp;&nbsp; default: </span><span class="default">self</span><span class="keyword">::</span><span class="default">$status </span><span class="keyword">= </span><span class="string">'at ease'</span><span class="keyword">; break;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="string">'COMMAND ISSUED: ' </span><span class="keyword">. </span><span class="default">$task </span><span class="keyword">. </span><span class="string">'&lt;br&gt;'</span><span class="keyword">;<br />
&nbsp; }<br />
<br />
&nbsp; public function </span><span class="default">__toString</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; return </span><span class="string">"Soldier[id=$this-&gt;id, status=" </span><span class="keyword">. </span><span class="default">self</span><span class="keyword">::</span><span class="default">$status </span><span class="keyword">. </span><span class="string">']'</span><span class="keyword">;<br />
&nbsp; }<br />
}<br />
<br />
</span><span class="comment"># create the General and the Cloned Army<br />
</span><span class="default">$general </span><span class="keyword">= new </span><span class="default">Soldier</span><span class="keyword">();<br />
</span><span class="default">$platoon </span><span class="keyword">= array();<br />
&nbsp; for(</span><span class="default">$i </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">; </span><span class="default">$i </span><span class="keyword">&lt; </span><span class="default">250</span><span class="keyword">; </span><span class="default">$i</span><span class="keyword">++) </span><span class="default">$platoon</span><span class="keyword">[] = new </span><span class="default">Soldier</span><span class="keyword">();<br />
<br />
</span><span class="comment"># issue commands, then check what soldiers are doing<br />
</span><span class="default">$general</span><span class="keyword">-&gt;</span><span class="default">issueCommand</span><span class="keyword">(</span><span class="string">'Deploy Troops'</span><span class="keyword">);<br />
echo </span><span class="default">$general </span><span class="keyword">. </span><span class="string">'&lt;br&gt;'</span><span class="keyword">;<br />
echo </span><span class="default">$platoon</span><span class="keyword">[</span><span class="default">223</span><span class="keyword">] . </span><span class="string">'&lt;br&gt;'</span><span class="keyword">;<br />
echo </span><span class="default">$platoon</span><span class="keyword">[</span><span class="default">12</span><span class="keyword">] . </span><span class="string">'&lt;br&gt;'</span><span class="keyword">;<br />
<br />
</span><span class="default">$general</span><span class="keyword">-&gt;</span><span class="default">issueCommand</span><span class="keyword">(</span><span class="string">'March Forward'</span><span class="keyword">);<br />
echo </span><span class="default">$platoon</span><span class="keyword">[</span><span class="default">47</span><span class="keyword">] . </span><span class="string">'&lt;br&gt;'</span><span class="keyword">;<br />
echo </span><span class="default">$platoon</span><span class="keyword">[</span><span class="default">163</span><span class="keyword">] . </span><span class="string">'&lt;br&gt;'</span><span class="keyword">;<br />
<br />
</span><span class="default">$general</span><span class="keyword">-&gt;</span><span class="default">issueCommand</span><span class="keyword">(</span><span class="string">'Fire!'</span><span class="keyword">);<br />
echo </span><span class="default">$platoon</span><span class="keyword">[</span><span class="default">248</span><span class="keyword">] . </span><span class="string">'&lt;br&gt;'</span><span class="keyword">;<br />
echo </span><span class="default">$platoon</span><span class="keyword">[</span><span class="default">68</span><span class="keyword">] . </span><span class="string">'&lt;br&gt;'</span><span class="keyword">;<br />
<br />
</span><span class="default">$general</span><span class="keyword">-&gt;</span><span class="default">issueCommand</span><span class="keyword">(</span><span class="string">'Retreat!'</span><span class="keyword">);<br />
echo </span><span class="default">$platoon</span><span class="keyword">[</span><span class="default">26</span><span class="keyword">] . </span><span class="string">'&lt;br&gt;'</span><span class="keyword">;<br />
echo </span><span class="default">$platoon</span><span class="keyword">[</span><span class="default">197</span><span class="keyword">] . </span><span class="string">'&lt;br&gt;'</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
COMMAND ISSUED: Deploy Troops<br />
&nbsp; Soldier[id=1, status=deploying]<br />
&nbsp; Soldier[id=225, status=deploying]<br />
&nbsp; Soldier[id=14, status=deploying]<br />
<br />
COMMAND ISSUED: March Forward<br />
&nbsp; Soldier[id=49, status=marching forward]<br />
&nbsp; Soldier[id=165, status=marching forward]<br />
<br />
COMMAND ISSUED: Fire!<br />
&nbsp; Soldier[id=250, status=shot fired]<br />
&nbsp; Soldier[id=70, status=shot fired]<br />
<br />
COMMAND ISSUED: Retreat!<br />
&nbsp; Soldier[id=28, status=course reversed]<br />
&nbsp; Soldier[id=199, status=course reversed]</span>
</code></div>
  </div>
 </div>
 <a name="84575"></a>
 <div class="note">
  <strong class='user'>Jim Brown</strong>
  <a href="#84575" class="date">19-Jul-2008 03:34</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Regarding the generic deep __clone() example provided by david ashe at metabin:<br />
<br />
If your object has a variable that stores an array of objects, that particular __clone() example will NOT perform a deep copy on your array of objects.</span>
</code></div>
  </div>
 </div>
 <a name="83286"></a>
 <div class="note">
  <strong class='user'>alex dot offshore at gmail dot com</strong>
  <a href="#83286" class="date">19-May-2008 03:23</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Remember that in PHP 5 ALL objects are assigned BY REFERENCE.<br />
<br />
<span class="default">&lt;?php<br />
<br />
&nbsp; </span><span class="keyword">function </span><span class="default">foo</span><span class="keyword">(</span><span class="default">$a</span><span class="keyword">) </span><span class="comment">// notice that '&amp;' near $a is missing<br />
&nbsp; </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$a</span><span class="keyword">[</span><span class="string">'bar'</span><span class="keyword">] = </span><span class="default">10</span><span class="keyword">;<br />
&nbsp; }<br />
<br />
&nbsp; </span><span class="default">$x </span><span class="keyword">= array(</span><span class="string">'bar' </span><span class="keyword">=&gt; </span><span class="default">0</span><span class="keyword">); </span><span class="comment">// built-in array() is not an object<br />
&nbsp; </span><span class="default">$y </span><span class="keyword">= new </span><span class="default">ArrayObject</span><span class="keyword">(array(</span><span class="string">'bar' </span><span class="keyword">=&gt; </span><span class="default">0</span><span class="keyword">));<br />
<br />
&nbsp; echo </span><span class="string">"\$x['bar'] == ${x['bar']};\n\$y['bar'] == ${y['bar']};\n\n"</span><span class="keyword">;<br />
<br />
&nbsp; </span><span class="default">foo</span><span class="keyword">(</span><span class="default">$x</span><span class="keyword">);<br />
&nbsp; </span><span class="default">foo</span><span class="keyword">(</span><span class="default">$y</span><span class="keyword">);<br />
<br />
&nbsp; echo </span><span class="string">"\$x['bar'] == ${x['bar']};\n\$y['bar'] == ${y['bar']};\n"</span><span class="keyword">;<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
Output:<br />
$x['bar'] == 0;<br />
$y['bar'] == 0;<br />
<br />
$x['bar'] == 0;<br />
$y['bar'] == 10;<br />
<br />
Hope this will be useful.<br />
<br />
By the way, to determine whether the variable is compatible with ArrayAccess/ArrayObject see <a href="http://php.net/manual/en/function.is-array.php#48083" rel="nofollow" target="_blank">http://php.net/manual/en/function.is-array.php#48083</a></span>
</code></div>
  </div>
 </div>
 <a name="81774"></a>
 <div class="note">
  <strong class='user'>crrodriguez at suse dot de</strong>
  <a href="#81774" class="date">12-Mar-2008 09:52</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Keep in mind that since PHP 5.2.5, trying to clone a non-object correctly results in a fatal error, this differs from previous versions where only a Warning was thrown.</span>
</code></div>
  </div>
 </div>
 <a name="79886"></a>
 <div class="note">
  <strong class='user'>Hayley Watson</strong>
  <a href="#79886" class="date">17-Dec-2007 03:51</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
It should go without saying that if you have circular references, where a property of object A refers to object B while a property of B refers to A (or more indirect loops than that), then you'll be glad that clone does NOT automatically make a deep copy!<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">class </span><span class="default">Foo<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; var </span><span class="default">$that</span><span class="keyword">;<br />
<br />
function </span><span class="default">__clone</span><span class="keyword">()<br />
{<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">that </span><span class="keyword">= clone </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">that</span><span class="keyword">;<br />
}<br />
<br />
}<br />
<br />
</span><span class="default">$a </span><span class="keyword">= new </span><span class="default">Foo</span><span class="keyword">;<br />
</span><span class="default">$b </span><span class="keyword">= new </span><span class="default">Foo</span><span class="keyword">;<br />
</span><span class="default">$a</span><span class="keyword">-&gt;</span><span class="default">that </span><span class="keyword">= </span><span class="default">$b</span><span class="keyword">;<br />
</span><span class="default">$b</span><span class="keyword">-&gt;</span><span class="default">that </span><span class="keyword">= </span><span class="default">$a</span><span class="keyword">;<br />
<br />
</span><span class="default">$c </span><span class="keyword">= clone </span><span class="default">$a</span><span class="keyword">;<br />
echo </span><span class="string">'What happened?'</span><span class="keyword">;<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$c</span><span class="keyword">);</span>
</span>
</code></div>
  </div>
 </div>
 <a name="79137"></a>
 <div class="note">
  <strong class='user'>tomi at cumulo dot fi</strong>
  <a href="#79137" class="date">13-Nov-2007 03:57</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
It should be noticed that __clone() does not allow you to return a value. Basically the idea is that you implement this magic method only when you want to execute operations inside the cloned object, immediately prior to the cloning. In this way __clone() is similar to the default destructor (__destruct()), in that it executes code right before the object is destroyed.</span>
</code></div>
  </div>
 </div>
 <a name="78364"></a>
 <div class="note">
  <strong class='user'>muratyaman at gmail dot com</strong>
  <a href="#78364" class="date">08-Oct-2007 07:43</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I think this is a bit awkward:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">A</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public </span><span class="default">$aaa</span><span class="keyword">;<br />
}<br />
<br />
class </span><span class="default">B</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public </span><span class="default">$a</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; public </span><span class="default">$bbb</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">__clone</span><span class="keyword">(){<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">a </span><span class="keyword">= clone </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">a</span><span class="keyword">;</span><span class="comment">//clone MANUALLY!!!<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">}<br />
}<br />
<br />
</span><span class="default">$b1 </span><span class="keyword">= new </span><span class="default">B</span><span class="keyword">();<br />
</span><span class="default">$b1</span><span class="keyword">-&gt;</span><span class="default">a </span><span class="keyword">= new </span><span class="default">A</span><span class="keyword">();<br />
</span><span class="default">$b1</span><span class="keyword">-&gt;</span><span class="default">a</span><span class="keyword">-&gt;</span><span class="default">aaa </span><span class="keyword">= </span><span class="default">111</span><span class="keyword">;<br />
</span><span class="default">$b1</span><span class="keyword">-&gt;</span><span class="default">bbb </span><span class="keyword">= </span><span class="default">1</span><span class="keyword">;<br />
<br />
</span><span class="default">$b2 </span><span class="keyword">= clone </span><span class="default">$b1</span><span class="keyword">;<br />
</span><span class="default">$b2</span><span class="keyword">-&gt;</span><span class="default">a</span><span class="keyword">-&gt;</span><span class="default">aaa </span><span class="keyword">= </span><span class="default">222</span><span class="keyword">;</span><span class="comment">//BEWARE!!<br />
</span><span class="default">$b2</span><span class="keyword">-&gt;</span><span class="default">bbb </span><span class="keyword">= </span><span class="default">2</span><span class="keyword">;</span><span class="comment">//no problem on basic types<br />
<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$b1</span><span class="keyword">); echo </span><span class="string">'&lt;br /&gt;'</span><span class="keyword">;<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$b2</span><span class="keyword">);<br />
</span><span class="comment">/*<br />
OUTPUT BEFORE implementing the function __clone()<br />
object(B)#2 (3) { ["a"]=&gt;&nbsp; object(A)#3 (1) { ["aaa"]=&gt;&nbsp; int(222) } ["bbb"]=&gt;&nbsp; int(1)&nbsp; }<br />
object(B)#4 (3) { ["a"]=&gt;&nbsp; object(A)#3 (1) { ["aaa"]=&gt;&nbsp; int(222) } ["bbb"]=&gt;&nbsp; int(2)&nbsp; }<br />
<br />
OUTPUT AFTER implementing the function __clone()<br />
object(B)#1 (3) { ["a"]=&gt;&nbsp; object(A)#2 (1) { ["aaa"]=&gt;&nbsp; int(111) } ["bbb"]=&gt;&nbsp; int(1)&nbsp; }<br />
object(B)#3 (3) { ["a"]=&gt;&nbsp; object(A)#4 (1) { ["aaa"]=&gt;&nbsp; int(222) } ["bbb"]=&gt;&nbsp; int(2)&nbsp; }<br />
*/<br />
</span><span class="default">?&gt;<br />
</span><br />
Whenever we use another class inside, we must clone it manually. If you have 10s of classes related, this is rather tedious. I don't want to even think about classes dynamically populated with other objects. Be careful when designing your classes! You should look after your objects all the time! This major change on PHP5 vs PHP4 regarding "references" definitely has very good performance improvements but comes with very dangerous side effects as well..</span>
</code></div>
  </div>
 </div>
 <a name="73081"></a>
 <div class="note">
  <strong class='user'>Alexey</strong>
  <a href="#73081" class="date">08-Feb-2007 07:18</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
To implement __clone() method in complex classes I use this simple function:<br />
<br />
function clone_($some)<br />
{<br />
&nbsp;&nbsp; return (is_object($some)) ? clone $some : $some;<br />
}<br />
<br />
In this way I don't need to care about type of my class properties.</span>
</code></div>
  </div>
 </div>
 <a name="72502"></a>
 <div class="note">
  <strong class='user'>MakariVerslund at gmail dot com</strong>
  <a href="#72502" class="date">21-Jan-2007 04:30</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I ran into the same problem of an array of objects inside of an object that I wanted to clone all pointing to the same objects. However, I agreed that serializing the data was not the answer. It was relatively simple, really:<br />
<br />
public function __clone() {<br />
&nbsp;&nbsp;&nbsp; foreach ($this-&gt;varName as &amp;$a) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; foreach ($a as &amp;$b) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; $b = clone $b;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
Note, that I was working with a multi-dimensional array and I was not using the Key=&gt;Value pair system, but basically, the point is that if you use foreach, you need to specify that the copied data is to be accessed by reference.</span>
</code></div>
  </div>
 </div>
 <a name="51439"></a>
 <div class="note">
  <strong class='user'>jorge dot villalobos at gmail dot com</strong>
  <a href="#51439" class="date">30-Mar-2005 03:29</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I think it's relevant to note that __clone is NOT an override. As the example shows, the normal cloning process always occurs, and it's the responsibility of the __clone method to "mend" any "wrong" action performed by it.</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=language.oop5.cloning&amp;redirect=http://www.php.net/manual/en/language.oop5.cloning.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.oop5.cloning&amp;redirect=http://www.php.net/manual/en/language.oop5.cloning.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/language.oop5.cloning.php">show source</a> |
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