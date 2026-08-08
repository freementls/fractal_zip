<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: IteratorAggregate - Manual</title>
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
 <link rel="prev" href="iterator.valid.php" />
 <link rel="next" href="iteratoraggregate.getiterator.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/iteratoraggregate" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="alternate" href="/manual/en/feeds/class.iteratoraggregate.atom" type="application/atom+xml" />
 <link rel="canonical" href="http://php.net/manual/en/class.iteratoraggregate.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/class.iteratoraggregate.php" />
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
 <li class="active"><a href="class.iteratoraggregate.php">IteratorAggregate</a></li>
 <li><a href="class.arrayaccess.php">ArrayAccess</a></li>
 <li><a href="class.serializable.php">Serializable</a></li>
 <li><a href="class.closure.php">Closure</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="iteratoraggregate.getiterator.php">IteratorAggregate::getIterator<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="iterator.valid.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Iterator::valid</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/class.iteratoraggregate.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/class.iteratoraggregate.php">Brazilian Portuguese</option>
    <option value="zh/class.iteratoraggregate.php">Chinese (Simplified)</option>
    <option value="fr/class.iteratoraggregate.php">French</option>
    <option value="de/class.iteratoraggregate.php">German</option>
    <option value="ja/class.iteratoraggregate.php">Japanese</option>
    <option value="pl/class.iteratoraggregate.php">Polish</option>
    <option value="ro/class.iteratoraggregate.php">Romanian</option>
    <option value="ru/class.iteratoraggregate.php">Russian</option>
    <option value="fa/class.iteratoraggregate.php">Persian</option>
    <option value="es/class.iteratoraggregate.php">Spanish</option>
    <option value="tr/class.iteratoraggregate.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="class.iteratoraggregate" class="reference">

 <h1 class="title">The IteratorAggregate interface</h1>
 

 <div class="partintro"><p class="verinfo">(PHP 5 &gt;= 5.0.0)</p>


  <div class="section" id="iteratoraggregate.intro">
   <h2 class="title">Introduction</h2>
   <p class="para">
    Interface to create an external Iterator.
   </p>
  </div>


  <div class="section" id="iteratoraggregate.synopsis">
   <h2 class="title">Interface synopsis</h2>


   <div class="classsynopsis">
    <div class="ooclass"></div>


    <div class="classsynopsisinfo">
     <span class="ooclass">
      <strong class="classname">IteratorAggregate</strong>
     </span>
     
     <span class="ooclass">
      <span class="modifier">extends</span>
      <a href="class.traversable.php" class="classname">Traversable</a>
     </span>
     {</div>

    
    <div class="classsynopsisinfo classsynopsisinfo_comment">/* Methods */</div>
    <div class="methodsynopsis dc-description">
   <span class="modifier">abstract</span> <span class="modifier">public</span> <span class="type">Traversable</span> <span class="methodname"><a href="iteratoraggregate.getiterator.php" class="methodname">getIterator</a></span>
    ( <span class="methodparam">void</span>
   )</div>

   }</div>


  </div>

  <div class="section" id="iteratoraggregate.examples">
   <div class="example" id="iteratoraggregate.example.basic">
    <p><strong>Example #1 Basic usage</strong></p>
    <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">class&nbsp;</span><span style="color: #0000BB">myData&nbsp;</span><span style="color: #007700">implements&nbsp;</span><span style="color: #0000BB">IteratorAggregate&nbsp;</span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;</span><span style="color: #0000BB">$property1&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">"Public&nbsp;property&nbsp;one"</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;</span><span style="color: #0000BB">$property2&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">"Public&nbsp;property&nbsp;two"</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;</span><span style="color: #0000BB">$property3&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">"Public&nbsp;property&nbsp;three"</span><span style="color: #007700">;<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">__construct</span><span style="color: #007700">()&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">property4&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">"last&nbsp;property"</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">getIterator</span><span style="color: #007700">()&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;return&nbsp;new&nbsp;</span><span style="color: #0000BB">ArrayIterator</span><span style="color: #007700">(</span><span style="color: #0000BB">$this</span><span style="color: #007700">);<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br /><br /></span><span style="color: #0000BB">$obj&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">myData</span><span style="color: #007700">;<br /><br />foreach(</span><span style="color: #0000BB">$obj&nbsp;</span><span style="color: #007700">as&nbsp;</span><span style="color: #0000BB">$key&nbsp;</span><span style="color: #007700">=&gt;&nbsp;</span><span style="color: #0000BB">$value</span><span style="color: #007700">)&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">var_dump</span><span style="color: #007700">(</span><span style="color: #0000BB">$key</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$value</span><span style="color: #007700">);<br />&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"\n"</span><span style="color: #007700">;<br />}<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

    <div class="example-contents"><p>The above example will output
something similar to:</p></div>
    <div class="example-contents screen">
<div class="cdata"><pre>
string(9) &quot;property1&quot;
string(19) &quot;Public property one&quot;

string(9) &quot;property2&quot;
string(19) &quot;Public property two&quot;

string(9) &quot;property3&quot;
string(21) &quot;Public property three&quot;

string(9) &quot;property4&quot;
string(13) &quot;last property&quot;

</pre></div>
    </div>
   </div>
  </div>


 </div>

 







<h2>Table of Contents</h2><ul class="chunklist chunklist_reference"><li><a href="iteratoraggregate.getiterator.php">IteratorAggregate::getIterator</a> — Retrieve an external iterator</li></ul>
</div>
<br /><br />
<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=class.iteratoraggregate&amp;redirect=http://www.php.net/manual/en/class.iteratoraggregate.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=class.iteratoraggregate&amp;redirect=http://www.php.net/manual/en/class.iteratoraggregate.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>IteratorAggregate</strong>
 </div><div id="allnotes">
 <a name="108476"></a>
 <div class="note">
  <strong class='user'>Tab Atkins</strong>
  <a href="#108476" class="date">29-Apr-2012 02:11</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Note that, at least as of 5.3, you still aren't allowed to return a normal Array from getIterator().<br />
<br />
In some places, the docs wrap the array into an ArrayObject and return that.&nbsp; DON'T DO IT.&nbsp; ArrayObject drops any empty-string keys on the floor when you iterate over it (again, at least as of 5.3).<br />
<br />
Use ArrayIterator instead.&nbsp; I wouldn't be surprised if it didn't have its own set of wonderful bugs, but at the very least it works correctly when you use it with this method.</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=class.iteratoraggregate&amp;redirect=http://www.php.net/manual/en/class.iteratoraggregate.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=class.iteratoraggregate&amp;redirect=http://www.php.net/manual/en/class.iteratoraggregate.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/class.iteratoraggregate.php">show source</a> |
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