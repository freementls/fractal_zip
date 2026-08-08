<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: ArrayAccess - Manual</title>
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
 <link rel="prev" href="iteratoraggregate.getiterator.php" />
 <link rel="next" href="arrayaccess.offsetexists.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/arrayaccess" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="alternate" href="/manual/en/feeds/class.arrayaccess.atom" type="application/atom+xml" />
 <link rel="canonical" href="http://php.net/manual/en/class.arrayaccess.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/class.arrayaccess.php" />
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
 <li class="active"><a href="class.arrayaccess.php">ArrayAccess</a></li>
 <li><a href="class.serializable.php">Serializable</a></li>
 <li><a href="class.closure.php">Closure</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="arrayaccess.offsetexists.php">ArrayAccess::offsetExists<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="iteratoraggregate.getiterator.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />IteratorAggregate::getIterator</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/class.arrayaccess.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/class.arrayaccess.php">Brazilian Portuguese</option>
    <option value="zh/class.arrayaccess.php">Chinese (Simplified)</option>
    <option value="fr/class.arrayaccess.php">French</option>
    <option value="de/class.arrayaccess.php">German</option>
    <option value="ja/class.arrayaccess.php">Japanese</option>
    <option value="pl/class.arrayaccess.php">Polish</option>
    <option value="ro/class.arrayaccess.php">Romanian</option>
    <option value="ru/class.arrayaccess.php">Russian</option>
    <option value="fa/class.arrayaccess.php">Persian</option>
    <option value="es/class.arrayaccess.php">Spanish</option>
    <option value="tr/class.arrayaccess.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="class.arrayaccess" class="reference">

 <h1 class="title">The ArrayAccess interface</h1>
 

 <div class="partintro"><p class="verinfo">(PHP 5 &gt;= 5.0.0)</p>


  <div class="section" id="arrayaccess.intro">
   <h2 class="title">Introduction</h2>
   <p class="para">
    Interface to provide accessing objects as arrays.
   </p>
  </div>


  <div class="section" id="arrayaccess.synopsis">
   <h2 class="title">Interface synopsis</h2>


   <div class="classsynopsis">
    <div class="ooclass"></div>


    <div class="classsynopsisinfo">
     <span class="ooclass">
      <strong class="classname">ArrayAccess</strong>
     </span>
     {</div>

    
    <div class="classsynopsisinfo classsynopsisinfo_comment">/* Methods */</div>
    <div class="methodsynopsis dc-description">
   <span class="modifier">abstract</span> <span class="modifier">public</span> <span class="type">boolean</span> <span class="methodname"><a href="arrayaccess.offsetexists.php" class="methodname">offsetExists</a></span>
    ( <span class="methodparam"><span class="type"><a href="language.pseudo-types.php#language.types.mixed" class="type mixed">mixed</a></span> <code class="parameter">$offset</code></span>
   )</div>
<div class="methodsynopsis dc-description">
   <span class="modifier">abstract</span> <span class="modifier">public</span> <span class="type">mixed</span> <span class="methodname"><a href="arrayaccess.offsetget.php" class="methodname">offsetGet</a></span>
    ( <span class="methodparam"><span class="type"><a href="language.pseudo-types.php#language.types.mixed" class="type mixed">mixed</a></span> <code class="parameter">$offset</code></span>
   )</div>
<div class="methodsynopsis dc-description">
   <span class="modifier">abstract</span> <span class="modifier">public</span> <span class="type">void</span> <span class="methodname"><a href="arrayaccess.offsetset.php" class="methodname">offsetSet</a></span>
    ( <span class="methodparam"><span class="type"><a href="language.pseudo-types.php#language.types.mixed" class="type mixed">mixed</a></span> <code class="parameter">$offset</code></span>
   , <span class="methodparam"><span class="type"><a href="language.pseudo-types.php#language.types.mixed" class="type mixed">mixed</a></span> <code class="parameter">$value</code></span>
   )</div>
<div class="methodsynopsis dc-description">
   <span class="modifier">abstract</span> <span class="modifier">public</span> <span class="type">void</span> <span class="methodname"><a href="arrayaccess.offsetunset.php" class="methodname">offsetUnset</a></span>
    ( <span class="methodparam"><span class="type"><a href="language.pseudo-types.php#language.types.mixed" class="type mixed">mixed</a></span> <code class="parameter">$offset</code></span>
   )</div>

   }</div>


  </div>

  <div class="section" id="arrayaccess.examples">
   <div class="example" id="arrayaccess.example.basic">
    <p><strong>Example #1 Basic usage</strong></p>
    <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">class&nbsp;</span><span style="color: #0000BB">obj&nbsp;</span><span style="color: #007700">implements&nbsp;</span><span style="color: #0000BB">arrayaccess&nbsp;</span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;private&nbsp;</span><span style="color: #0000BB">$container&nbsp;</span><span style="color: #007700">=&nbsp;array();<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">__construct</span><span style="color: #007700">()&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">container&nbsp;</span><span style="color: #007700">=&nbsp;array(<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #DD0000">"one"&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">=&gt;&nbsp;</span><span style="color: #0000BB">1</span><span style="color: #007700">,<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #DD0000">"two"&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">=&gt;&nbsp;</span><span style="color: #0000BB">2</span><span style="color: #007700">,<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #DD0000">"three"&nbsp;</span><span style="color: #007700">=&gt;&nbsp;</span><span style="color: #0000BB">3</span><span style="color: #007700">,<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;);<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">offsetSet</span><span style="color: #007700">(</span><span style="color: #0000BB">$offset</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$value</span><span style="color: #007700">)&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;if&nbsp;(</span><span style="color: #0000BB">is_null</span><span style="color: #007700">(</span><span style="color: #0000BB">$offset</span><span style="color: #007700">))&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">container</span><span style="color: #007700">[]&nbsp;=&nbsp;</span><span style="color: #0000BB">$value</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}&nbsp;else&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">container</span><span style="color: #007700">[</span><span style="color: #0000BB">$offset</span><span style="color: #007700">]&nbsp;=&nbsp;</span><span style="color: #0000BB">$value</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">offsetExists</span><span style="color: #007700">(</span><span style="color: #0000BB">$offset</span><span style="color: #007700">)&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;return&nbsp;isset(</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">container</span><span style="color: #007700">[</span><span style="color: #0000BB">$offset</span><span style="color: #007700">]);<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">offsetUnset</span><span style="color: #007700">(</span><span style="color: #0000BB">$offset</span><span style="color: #007700">)&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;unset(</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">container</span><span style="color: #007700">[</span><span style="color: #0000BB">$offset</span><span style="color: #007700">]);<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">offsetGet</span><span style="color: #007700">(</span><span style="color: #0000BB">$offset</span><span style="color: #007700">)&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;return&nbsp;isset(</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">container</span><span style="color: #007700">[</span><span style="color: #0000BB">$offset</span><span style="color: #007700">])&nbsp;?&nbsp;</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">container</span><span style="color: #007700">[</span><span style="color: #0000BB">$offset</span><span style="color: #007700">]&nbsp;:&nbsp;</span><span style="color: #0000BB">null</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br /><br /></span><span style="color: #0000BB">$obj&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">obj</span><span style="color: #007700">;<br /><br /></span><span style="color: #0000BB">var_dump</span><span style="color: #007700">(isset(</span><span style="color: #0000BB">$obj</span><span style="color: #007700">[</span><span style="color: #DD0000">"two"</span><span style="color: #007700">]));<br /></span><span style="color: #0000BB">var_dump</span><span style="color: #007700">(</span><span style="color: #0000BB">$obj</span><span style="color: #007700">[</span><span style="color: #DD0000">"two"</span><span style="color: #007700">]);<br />unset(</span><span style="color: #0000BB">$obj</span><span style="color: #007700">[</span><span style="color: #DD0000">"two"</span><span style="color: #007700">]);<br /></span><span style="color: #0000BB">var_dump</span><span style="color: #007700">(isset(</span><span style="color: #0000BB">$obj</span><span style="color: #007700">[</span><span style="color: #DD0000">"two"</span><span style="color: #007700">]));<br /></span><span style="color: #0000BB">$obj</span><span style="color: #007700">[</span><span style="color: #DD0000">"two"</span><span style="color: #007700">]&nbsp;=&nbsp;</span><span style="color: #DD0000">"A&nbsp;value"</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">var_dump</span><span style="color: #007700">(</span><span style="color: #0000BB">$obj</span><span style="color: #007700">[</span><span style="color: #DD0000">"two"</span><span style="color: #007700">]);<br /></span><span style="color: #0000BB">$obj</span><span style="color: #007700">[]&nbsp;=&nbsp;</span><span style="color: #DD0000">'Append&nbsp;1'</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">$obj</span><span style="color: #007700">[]&nbsp;=&nbsp;</span><span style="color: #DD0000">'Append&nbsp;2'</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">$obj</span><span style="color: #007700">[]&nbsp;=&nbsp;</span><span style="color: #DD0000">'Append&nbsp;3'</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">print_r</span><span style="color: #007700">(</span><span style="color: #0000BB">$obj</span><span style="color: #007700">);<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

    <div class="example-contents"><p>The above example will output
something similar to:</p></div>
    <div class="example-contents screen">
<div class="cdata"><pre>
bool(true)
int(2)
bool(false)
string(7) &quot;A value&quot;
obj Object
(
    [container:obj:private] =&gt; Array
        (
            [one] =&gt; 1
            [three] =&gt; 3
            [two] =&gt; A value
            [0] =&gt; Append 1
            [1] =&gt; Append 2
            [2] =&gt; Append 3
        )

)
</pre></div>
    </div>
   </div>
  </div>

 </div>

 






 






 






 







<h2>Table of Contents</h2><ul class="chunklist chunklist_reference"><li><a href="arrayaccess.offsetexists.php">ArrayAccess::offsetExists</a> — Whether a offset exists</li><li><a href="arrayaccess.offsetget.php">ArrayAccess::offsetGet</a> — Offset to retrieve</li><li><a href="arrayaccess.offsetset.php">ArrayAccess::offsetSet</a> — Offset to set</li><li><a href="arrayaccess.offsetunset.php">ArrayAccess::offsetUnset</a> — Offset to unset</li></ul>
</div>
<br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="arrayaccess.offsetexists.php">ArrayAccess::offsetExists<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="iteratoraggregate.getiterator.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />IteratorAggregate::getIterator</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/class.arrayaccess.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=class.arrayaccess&amp;redirect=http://www.php.net/manual/en/class.arrayaccess.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=class.arrayaccess&amp;redirect=http://www.php.net/manual/en/class.arrayaccess.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>ArrayAccess</strong>
 </div><div id="allnotes">
 <a name="104061"></a>
 <div class="note">
  <strong class='user'>Per</strong>
  <a href="#104061" class="date">20-May-2011 02:16</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
It bit me today, so putting it here in the hope it will help others:<br />
If you call array_key_exists() on an object of a class that implements ArrayAccess, ArrayAccess::offsetExists() wil NOT be called.</span>
</code></div>
  </div>
 </div>
 <a name="99236"></a>
 <div class="note">
  <strong class='user'>uramihsayibok, gmail, com</strong>
  <a href="#99236" class="date">05-Aug-2010 03:34</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
EDITOR NOTE: You can return by reference in offsetGet as of PHP 5.3.4.<br />
<br />
If you find yourself facing the dreaded "Indirect modification of overloaded element of $class has no effect" then don't worry too much: there's a clever solution. While you *CANNOT* return by-reference with offsetGet, you can return objects which _also_ implement ArrayAccess...<br />
<br />
Example:<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="comment">// sanity and error checking omitted for brevity<br />
// note: it's a good idea to implement arrayaccess + countable + an<br />
// iterator interface (like iteratoraggregate) as a triplet<br />
<br />
</span><span class="keyword">class </span><span class="default">RecursiveArrayAccess </span><span class="keyword">implements </span><span class="default">ArrayAccess </span><span class="keyword">{<br />
<br />
&nbsp;&nbsp;&nbsp; private </span><span class="default">$data </span><span class="keyword">= array();<br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">// necessary for deep copies<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">public function </span><span class="default">__clone</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; foreach (</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">data </span><span class="keyword">as </span><span class="default">$key </span><span class="keyword">=&gt; </span><span class="default">$value</span><span class="keyword">) if (</span><span class="default">$value </span><span class="keyword">instanceof </span><span class="default">self</span><span class="keyword">) </span><span class="default">$this</span><span class="keyword">[</span><span class="default">$key</span><span class="keyword">] = clone </span><span class="default">$value</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">__construct</span><span class="keyword">(array </span><span class="default">$data </span><span class="keyword">= array()) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; foreach (</span><span class="default">$data </span><span class="keyword">as </span><span class="default">$key </span><span class="keyword">=&gt; </span><span class="default">$value</span><span class="keyword">) </span><span class="default">$this</span><span class="keyword">[</span><span class="default">$key</span><span class="keyword">] = </span><span class="default">$value</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">offsetSet</span><span class="keyword">(</span><span class="default">$offset</span><span class="keyword">, </span><span class="default">$data</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if (</span><span class="default">is_array</span><span class="keyword">(</span><span class="default">$data</span><span class="keyword">)) </span><span class="default">$data </span><span class="keyword">= new </span><span class="default">self</span><span class="keyword">(</span><span class="default">$data</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if (</span><span class="default">$offset </span><span class="keyword">=== </span><span class="default">null</span><span class="keyword">) { </span><span class="comment">// don't forget this!<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">data</span><span class="keyword">[] = </span><span class="default">$data</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; } else {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">data</span><span class="keyword">[</span><span class="default">$offset</span><span class="keyword">] = </span><span class="default">$data</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">toArray</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$data </span><span class="keyword">= </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">data</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; foreach (</span><span class="default">$data </span><span class="keyword">as </span><span class="default">$key </span><span class="keyword">=&gt; </span><span class="default">$value</span><span class="keyword">) if (</span><span class="default">$value </span><span class="keyword">instanceof </span><span class="default">self</span><span class="keyword">) </span><span class="default">$data</span><span class="keyword">[</span><span class="default">$key</span><span class="keyword">] = </span><span class="default">$value</span><span class="keyword">-&gt;</span><span class="default">toArray</span><span class="keyword">();<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$data</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">// as normal<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">public function </span><span class="default">offsetGet</span><span class="keyword">(</span><span class="default">$offset</span><span class="keyword">) { return </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">data</span><span class="keyword">[</span><span class="default">$offset</span><span class="keyword">]; }<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">offsetExists</span><span class="keyword">(</span><span class="default">$offset</span><span class="keyword">) { return isset(</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">data</span><span class="keyword">[</span><span class="default">$offset</span><span class="keyword">]); }<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">offsetUnset</span><span class="keyword">(</span><span class="default">$offset</span><span class="keyword">) { unset(</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">data</span><span class="keyword">); }<br />
<br />
}<br />
<br />
</span><span class="default">$a </span><span class="keyword">= new </span><span class="default">RecursiveArrayAccess</span><span class="keyword">();<br />
</span><span class="default">$a</span><span class="keyword">[</span><span class="default">0</span><span class="keyword">] = array(</span><span class="default">1</span><span class="keyword">=&gt;</span><span class="string">"foo"</span><span class="keyword">, </span><span class="default">2</span><span class="keyword">=&gt;array(</span><span class="default">3</span><span class="keyword">=&gt;</span><span class="string">"bar"</span><span class="keyword">, </span><span class="default">4</span><span class="keyword">=&gt;array(</span><span class="default">5</span><span class="keyword">=&gt;</span><span class="string">"bz"</span><span class="keyword">)));<br />
</span><span class="comment">// oops. typo<br />
</span><span class="default">$a</span><span class="keyword">[</span><span class="default">0</span><span class="keyword">][</span><span class="default">2</span><span class="keyword">][</span><span class="default">4</span><span class="keyword">][</span><span class="default">5</span><span class="keyword">] = </span><span class="string">"baz"</span><span class="keyword">;<br />
<br />
</span><span class="comment">//var_dump($a);<br />
//var_dump($a-&gt;toArray());<br />
<br />
// isset and unset work too<br />
//var_dump(isset($a[0][2][4][5])); // equivalent to $a[0][2][4]-&gt;offsetExists(5)<br />
//unset($a[0][2][4][5]); // equivalent to $a[0][2][4]-&gt;offsetUnset(5);<br />
<br />
// if __clone wasn't implemented then cloning would produce a shallow copy, and<br />
</span><span class="default">$b </span><span class="keyword">= clone </span><span class="default">$a</span><span class="keyword">;<br />
</span><span class="default">$b</span><span class="keyword">[</span><span class="default">0</span><span class="keyword">][</span><span class="default">2</span><span class="keyword">][</span><span class="default">4</span><span class="keyword">][</span><span class="default">5</span><span class="keyword">] = </span><span class="string">"xyzzy"</span><span class="keyword">;<br />
</span><span class="comment">// would affect $a's data too<br />
//echo $a[0][2][4][5]; // still "baz"<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="97163"></a>
 <div class="note">
  <strong class='user'>max at flashdroid dot com</strong>
  <a href="#97163" class="date">05-Apr-2010 06:49</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Objects implementing ArrayAccess may return objects by references in PHP 5.3.0.<br />
<br />
You can implement your ArrayAccess object like this:<br />
<br />
&nbsp;&nbsp;&nbsp; class Reflectable implements ArrayAccess {<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; public function set($name, $value) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; $this-&gt;{$name} = $value;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; public function &amp;get($name) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return $this-&gt;{$name};<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; public function offsetGet($offset) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return $this-&gt;get($offset);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; public function offsetSet($offset, $value) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; $this-&gt;set($offset, $value);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; ...<br />
<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
This base class allows you to get / set your object properties using the [] operator just like in Javascript:<br />
<br />
&nbsp;&nbsp;&nbsp; class Boo extends Reflectable {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; public $name;<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; $obj = new Boo();<br />
&nbsp;&nbsp;&nbsp; $obj['name'] = "boo";<br />
&nbsp;&nbsp;&nbsp; echo $obj['name']; // prints boo</span>
</code></div>
  </div>
 </div>
 <a name="94336"></a>
 <div class="note">
  <strong class='user'>Cintix</strong>
  <a href="#94336" class="date">29-Oct-2009 01:32</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
To take full advantages of all array features with ArrayAccess, then you would need to implements Countable and Iterator<br />
<br />
Like this.<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">class </span><span class="default">ArrayOfColorModel </span><span class="keyword">implements </span><span class="default">ArrayAccess</span><span class="keyword">, </span><span class="default">Iterator</span><span class="keyword">, </span><span class="default">Countable </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; private </span><span class="default">$container </span><span class="keyword">= array();<br />
<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">__construct</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">offsetSet</span><span class="keyword">(</span><span class="default">$offset</span><span class="keyword">,</span><span class="default">$value</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; if (</span><span class="default">$value </span><span class="keyword">instanceof </span><span class="default">ColorModel</span><span class="keyword">){<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; if (</span><span class="default">$offset </span><span class="keyword">== </span><span class="string">""</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">container</span><span class="keyword">[] = </span><span class="default">$value</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; }else {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">container</span><span class="keyword">[</span><span class="default">$offset</span><span class="keyword">] = </span><span class="default">$value</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; } else {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; throw new </span><span class="default">Exception</span><span class="keyword">(</span><span class="string">"Value have to be a instance of the Model ColorModel"</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">offsetExists</span><span class="keyword">(</span><span class="default">$offset</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; return isset(</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">container</span><span class="keyword">[</span><span class="default">$offset</span><span class="keyword">]);<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">offsetUnset</span><span class="keyword">(</span><span class="default">$offset</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; unset(</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">container</span><span class="keyword">[</span><span class="default">$offset</span><span class="keyword">]);<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">offsetGet</span><span class="keyword">(</span><span class="default">$offset</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return isset(</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">container</span><span class="keyword">[</span><span class="default">$offset</span><span class="keyword">]) ? </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">container</span><span class="keyword">[</span><span class="default">$offset</span><span class="keyword">] : </span><span class="default">null</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">rewind</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">reset</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">container</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">current</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">current</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">container</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">key</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">key</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">container</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">next</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">next</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">container</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">valid</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">current</span><span class="keyword">() !== </span><span class="default">false</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }&nbsp; &nbsp; <br />
<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">count</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; return </span><span class="default">count</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">container</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
}<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
Now you can using it like any other array.<br />
<br />
<span class="default">&lt;?php<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp; $array </span><span class="keyword">= new </span><span class="default">ArrayOfColorModel</span><span class="keyword">();<br />
&nbsp;&nbsp; foreach (</span><span class="default">$array </span><span class="keyword">as </span><span class="default">$model</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">var_export</span><span class="keyword">(</span><span class="default">$model</span><span class="keyword">);<br />
&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp; </span><span class="comment">// OR<br />
<br />
&nbsp;&nbsp; </span><span class="keyword">for(</span><span class="default">$i</span><span class="keyword">=</span><span class="default">0</span><span class="keyword">;</span><span class="default">$i</span><span class="keyword">&lt;</span><span class="default">count</span><span class="keyword">(</span><span class="default">$array</span><span class="keyword">);</span><span class="default">$i</span><span class="keyword">++){<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">var_export</span><span class="keyword">(</span><span class="default">$array</span><span class="keyword">[</span><span class="default">$i</span><span class="keyword">]);<br />
&nbsp;&nbsp; }<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="88079"></a>
 <div class="note">
  <strong class='user'>AryehGregor+php-comment at gmail dot com</strong>
  <a href="#88079" class="date">08-Jan-2009 05:33</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Note that at least in PHP 5.1, objects implementing ArrayAccess cannot return objects by reference.&nbsp; See <a href="http://bugs.php.net/bug.php?id=34783" rel="nofollow" target="_blank">http://bugs.php.net/bug.php?id=34783</a> .&nbsp; If you have code like <br />
<br />
<span class="default">&lt;?php<br />
$x </span><span class="keyword">= &amp;</span><span class="default">$y</span><span class="keyword">[</span><span class="default">0</span><span class="keyword">];<br />
</span><span class="default">?&gt;<br />
</span><br />
then this will (as far as I can tell) *always* fail unless $y is a real array -- it cannot work if $y is an object implementing ArrayAccess.&nbsp; If your offsetGet() function returns by reference, you get the fatal error "Declaration of MyClass::offsetGet() must be compatible with that of ArrayAccess::offsetGet()".&nbsp; If you try to have it return by value, however, you get the (contradictory) fatal error "Objects used as arrays in post/pre increment/decrement must return values by reference", at least in my version of PHP.<br />
<br />
It is therefore not possible to take arbitrary code dealing with arrays and try to substitute an object of your own for an array, even if all of the normal array functions didn't fail as well (which they do, or at least some of them).</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=class.arrayaccess&amp;redirect=http://www.php.net/manual/en/class.arrayaccess.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=class.arrayaccess&amp;redirect=http://www.php.net/manual/en/class.arrayaccess.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/class.arrayaccess.php">show source</a> |
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