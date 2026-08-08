<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Serializable - Manual</title>
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
 <link rel="index" href="reserved.interfaces.php" />
 <link rel="prev" href="arrayaccess.offsetunset.php" />
 <link rel="next" href="serializable.serialize.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/serializable" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="alternate" href="/manual/en/feeds/class.serializable.atom" type="application/atom+xml" />
 <link rel="canonical" href="http://php.net/manual/en/class.serializable.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/class.serializable.php" />
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
 <li class="header up"><a href="reserved.interfaces.php">Predefined Interfaces</a></li>
 <li><a href="class.traversable.php">Traversable</a></li>
 <li><a href="class.iterator.php">Iterator</a></li>
 <li><a href="class.iteratoraggregate.php">IteratorAggregate</a></li>
 <li><a href="class.arrayaccess.php">ArrayAccess</a></li>
 <li class="active"><a href="class.serializable.php">Serializable</a></li>
 <li><a href="class.closure.php">Closure</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="serializable.serialize.php">Serializable::serialize<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="arrayaccess.offsetunset.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />ArrayAccess::offsetUnset</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/class.serializable.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/class.serializable.php">Brazilian Portuguese</option>
    <option value="zh/class.serializable.php">Chinese (Simplified)</option>
    <option value="fr/class.serializable.php">French</option>
    <option value="de/class.serializable.php">German</option>
    <option value="ja/class.serializable.php">Japanese</option>
    <option value="pl/class.serializable.php">Polish</option>
    <option value="ro/class.serializable.php">Romanian</option>
    <option value="ru/class.serializable.php">Russian</option>
    <option value="fa/class.serializable.php">Persian</option>
    <option value="es/class.serializable.php">Spanish</option>
    <option value="tr/class.serializable.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="class.serializable" class="reference">

 <h1 class="title">The Serializable interface</h1>
 

 <div class="partintro"><p class="verinfo">(PHP 5 &gt;= 5.1.0)</p>


  <div class="section" id="serializable.intro">
   <h2 class="title">Introduction</h2>
   <p class="para">
    Interface for customized serializing.
   </p>

   <p class="para">
    Classes that implement this interface no longer support
    <a href="language.oop5.magic.php#object.sleep" class="link">__sleep()</a> and
    <a href="language.oop5.magic.php#object.wakeup" class="link">__wakeup()</a>. The method serialize is
    called whenever an instance needs to be serialized. This does not invoke __destruct()
    or has any other side effect unless programmed inside the method. When the data is
    unserialized the class is known and the appropriate unserialize() method is called as
    a constructor instead of calling __construct(). If you need to execute the standard
    constructor you may do so in the method.
   </p>
  </div>


  <div class="section" id="serializable.synopsis">
   <h2 class="title">Interface synopsis</h2>


   <div class="classsynopsis">
    <div class="ooclass"></div>


    <div class="classsynopsisinfo">
     <span class="ooclass">
      <strong class="classname">Serializable</strong>
     </span>
     {</div>

    
    <div class="classsynopsisinfo classsynopsisinfo_comment">/* Methods */</div>
    <div class="methodsynopsis dc-description">
   <span class="modifier">abstract</span> <span class="modifier">public</span> <span class="type">string</span> <span class="methodname"><a href="serializable.serialize.php" class="methodname">serialize</a></span>
    ( <span class="methodparam">void</span>
   )</div>
<div class="methodsynopsis dc-description">
   <span class="modifier">abstract</span> <span class="modifier">public</span> <span class="type">void</span> <span class="methodname"><a href="serializable.unserialize.php" class="methodname">unserialize</a></span>
    ( <span class="methodparam"><span class="type">string</span> <code class="parameter">$serialized</code></span>
   )</div>

   }</div>


  </div>

  <div class="section" id="serializable.examples">
   <div class="example" id="serializable.example.basic">
    <p><strong>Example #1 Basic usage</strong></p>
    <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">class&nbsp;</span><span style="color: #0000BB">obj&nbsp;</span><span style="color: #007700">implements&nbsp;</span><span style="color: #0000BB">Serializable&nbsp;</span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;private&nbsp;</span><span style="color: #0000BB">$data</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">__construct</span><span style="color: #007700">()&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">data&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">"My&nbsp;private&nbsp;data"</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">serialize</span><span style="color: #007700">()&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;return&nbsp;</span><span style="color: #0000BB">serialize</span><span style="color: #007700">(</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">data</span><span style="color: #007700">);<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">unserialize</span><span style="color: #007700">(</span><span style="color: #0000BB">$data</span><span style="color: #007700">)&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">data&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">unserialize</span><span style="color: #007700">(</span><span style="color: #0000BB">$data</span><span style="color: #007700">);<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">getData</span><span style="color: #007700">()&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;return&nbsp;</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">data</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br /><br /></span><span style="color: #0000BB">$obj&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">obj</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">$ser&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">serialize</span><span style="color: #007700">(</span><span style="color: #0000BB">$obj</span><span style="color: #007700">);<br /><br /></span><span style="color: #0000BB">var_dump</span><span style="color: #007700">(</span><span style="color: #0000BB">$ser</span><span style="color: #007700">);<br /><br /></span><span style="color: #0000BB">$newobj&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">unserialize</span><span style="color: #007700">(</span><span style="color: #0000BB">$ser</span><span style="color: #007700">);<br /><br /></span><span style="color: #0000BB">var_dump</span><span style="color: #007700">(</span><span style="color: #0000BB">$newobj</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">getData</span><span style="color: #007700">());<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

    <div class="example-contents"><p>The above example will output
something similar to:</p></div>
    <div class="example-contents screen">
<div class="cdata"><pre>
string(38) &quot;C:3:&quot;obj&quot;:23:{s:15:&quot;My private data&quot;;}&quot;
string(15) &quot;My private data&quot;
</pre></div>
    </div>
   </div>
  </div>


 </div>

 






 







<h2>Table of Contents</h2><ul class="chunklist chunklist_reference"><li><a href="serializable.serialize.php">Serializable::serialize</a> — String representation of object</li><li><a href="serializable.unserialize.php">Serializable::unserialize</a> — Constructs the object</li></ul>
</div>
<br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="serializable.serialize.php">Serializable::serialize<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="arrayaccess.offsetunset.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />ArrayAccess::offsetUnset</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/class.serializable.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=class.serializable&amp;redirect=http://www.php.net/manual/en/class.serializable.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=class.serializable&amp;redirect=http://www.php.net/manual/en/class.serializable.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Serializable</strong>
 </div><div id="allnotes">
 <a name="107194"></a>
 <div class="note">
  <strong class='user'>marcos dot gottardi at folha dot REM0VE-THIS dot com dot br</strong>
  <a href="#107194" class="date">13-Jan-2012 09:05</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Serializing child and parent classes:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">MyClass </span><span class="keyword">implements </span><span class="default">Serializable </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; private </span><span class="default">$data</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">__construct</span><span class="keyword">(</span><span class="default">$data</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">data </span><span class="keyword">= </span><span class="default">$data</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">getData</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">data</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">serialize</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">"Serializing MyClass...\n"</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">serialize</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">data</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">unserialize</span><span class="keyword">(</span><span class="default">$data</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">"Unserializing MyClass...\n"</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">data </span><span class="keyword">= </span><span class="default">unserialize</span><span class="keyword">(</span><span class="default">$data</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
class </span><span class="default">MyChildClass </span><span class="keyword">extends </span><span class="default">MyClass </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; private </span><span class="default">$id</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; private </span><span class="default">$name</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">__construct</span><span class="keyword">(</span><span class="default">$id</span><span class="keyword">, </span><span class="default">$name</span><span class="keyword">, </span><span class="default">$data</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">parent</span><span class="keyword">::</span><span class="default">__construct</span><span class="keyword">(</span><span class="default">$data</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">id </span><span class="keyword">= </span><span class="default">$id</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">name </span><span class="keyword">= </span><span class="default">$name</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">serialize</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">"Serializing MyChildClass...\n"</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">serialize</span><span class="keyword">(<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; array(<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="string">'id' </span><span class="keyword">=&gt; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">id</span><span class="keyword">,<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="string">'name' </span><span class="keyword">=&gt; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">name</span><span class="keyword">,<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="string">'parentData' </span><span class="keyword">=&gt; </span><span class="default">parent</span><span class="keyword">::</span><span class="default">serialize</span><span class="keyword">()<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; )<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; );<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">unserialize</span><span class="keyword">(</span><span class="default">$data</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">"Unserializing MyChildClass...\n"</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$data </span><span class="keyword">= </span><span class="default">unserialize</span><span class="keyword">(</span><span class="default">$data</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">id </span><span class="keyword">= </span><span class="default">$data</span><span class="keyword">[</span><span class="string">'id'</span><span class="keyword">];<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">name </span><span class="keyword">= </span><span class="default">$data</span><span class="keyword">[</span><span class="string">'name'</span><span class="keyword">];<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">parent</span><span class="keyword">::</span><span class="default">unserialize</span><span class="keyword">(</span><span class="default">$data</span><span class="keyword">[</span><span class="string">'parentData'</span><span class="keyword">]);<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">getId</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">id</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">getName</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">name</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="default">$obj </span><span class="keyword">= new </span><span class="default">MyChildClass</span><span class="keyword">(</span><span class="default">15</span><span class="keyword">, </span><span class="string">'My class name'</span><span class="keyword">, </span><span class="string">'My data'</span><span class="keyword">);<br />
<br />
</span><span class="default">$serial </span><span class="keyword">= </span><span class="default">serialize</span><span class="keyword">(</span><span class="default">$obj</span><span class="keyword">);<br />
</span><span class="default">$newObject </span><span class="keyword">= </span><span class="default">unserialize</span><span class="keyword">(</span><span class="default">$serial</span><span class="keyword">);<br />
<br />
echo </span><span class="default">$newObject</span><span class="keyword">-&gt;</span><span class="default">getId</span><span class="keyword">() . </span><span class="default">PHP_EOL</span><span class="keyword">;<br />
echo </span><span class="default">$newObject</span><span class="keyword">-&gt;</span><span class="default">getName</span><span class="keyword">() . </span><span class="default">PHP_EOL</span><span class="keyword">;<br />
echo </span><span class="default">$newObject</span><span class="keyword">-&gt;</span><span class="default">getData</span><span class="keyword">() . </span><span class="default">PHP_EOL</span><span class="keyword">;<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
This will output:<br />
<br />
Serializing MyChildClass...<br />
Serializing MyClass...<br />
Unserializing MyChildClass...<br />
Unserializing MyClass...<br />
15<br />
My class name<br />
My data</span>
</code></div>
  </div>
 </div>
 <a name="104747"></a>
 <div class="note">
  <strong class='user'>Anonymous</strong>
  <a href="#104747" class="date">05-Jul-2011 07:01</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
You cannot throw an exception inside the serialize() method.&nbsp; This will cause PHP to complain that you are not returning a string or NULL.<br />
<br />
The best way to prevent the serialization of an object is to throw an Exception in the __sleep() method:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">Obj </span><span class="keyword">{<br />
&nbsp; public function </span><span class="default">__sleep</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; throw new </span><span class="default">BadMethodCallException</span><span class="keyword">(</span><span class="string">'You cannot serialize this object.'</span><span class="keyword">);<br />
&nbsp; }<br />
}<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="103936"></a>
 <div class="note">
  <strong class='user'>Anonymous</strong>
  <a href="#103936" class="date">12-May-2011 06:09</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
You can prevent an object getting unserialized by returning NULL. Instead of a serialized object, PHP will return the serialized form of NULL:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">testNull </span><span class="keyword">implements </span><span class="default">Serializable </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">serialize</span><span class="keyword">() {&nbsp; &nbsp; &nbsp; &nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">NULL</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">unserialize</span><span class="keyword">(</span><span class="default">$data</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="default">$obj </span><span class="keyword">= new </span><span class="default">testNull</span><span class="keyword">;<br />
</span><span class="default">$string </span><span class="keyword">= </span><span class="default">serialize</span><span class="keyword">(</span><span class="default">$obj</span><span class="keyword">);<br />
echo </span><span class="default">$string</span><span class="keyword">; </span><span class="comment">// "N;"<br />
</span><span class="default">?&gt;<br />
</span><br />
That's perhaps better than throwing exceptions inside of the serialize function if you want to prevent serialization of certain objects.</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=class.serializable&amp;redirect=http://www.php.net/manual/en/class.serializable.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=class.serializable&amp;redirect=http://www.php.net/manual/en/class.serializable.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/class.serializable.php">show source</a> |
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