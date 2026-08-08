<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Traversable - Manual</title>
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
 <link rel="prev" href="reserved.interfaces.php" />
 <link rel="next" href="class.iterator.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/traversable" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/class.traversable.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/class.traversable.php" />
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
 <li class="active"><a href="class.traversable.php">Traversable</a></li>
 <li><a href="class.iterator.php">Iterator</a></li>
 <li><a href="class.iteratoraggregate.php">IteratorAggregate</a></li>
 <li><a href="class.arrayaccess.php">ArrayAccess</a></li>
 <li><a href="class.serializable.php">Serializable</a></li>
 <li><a href="class.closure.php">Closure</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="class.iterator.php">Iterator<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="reserved.interfaces.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Predefined Interfaces</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/class.traversable.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/class.traversable.php">Brazilian Portuguese</option>
    <option value="zh/class.traversable.php">Chinese (Simplified)</option>
    <option value="fr/class.traversable.php">French</option>
    <option value="de/class.traversable.php">German</option>
    <option value="ja/class.traversable.php">Japanese</option>
    <option value="pl/class.traversable.php">Polish</option>
    <option value="ro/class.traversable.php">Romanian</option>
    <option value="ru/class.traversable.php">Russian</option>
    <option value="fa/class.traversable.php">Persian</option>
    <option value="es/class.traversable.php">Spanish</option>
    <option value="tr/class.traversable.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="class.traversable" class="reference">

 <h1 class="title">The Traversable interface</h1>
 

 <div class="partintro"><p class="verinfo">(No version information available, might only be in SVN)</p>


  <div class="section" id="traversable.intro">
   <h2 class="title">Introduction</h2>
   <p class="para">
    Interface to detect if a class is traversable using <a href="control-structures.foreach.php" class="link">foreach</a>.
   </p>
   <p class="para">
    Abstract base interface that cannot be implemented alone. Instead it must
    be implemented by either <a href="class.iteratoraggregate.php" class="classname">IteratorAggregate</a> or
    <a href="class.iterator.php" class="classname">Iterator</a>.
   </p>
   <blockquote class="note"><p><strong class="note">Note</strong>: 
    <p class="para">
     Internal (built-in) classes that implement this interface can be used in
     a <a href="control-structures.foreach.php" class="link">foreach</a> construct and do not need to implement
     <a href="class.iteratoraggregate.php" class="classname">IteratorAggregate</a> or
     <a href="class.iterator.php" class="classname">Iterator</a>.
    </p>
   </p></blockquote>
   <blockquote class="note"><p><strong class="note">Note</strong>: 
    <p class="para">
     This is an internal engine interface which cannot be implemented in PHP
     scripts. Either <a href="class.iteratoraggregate.php" class="classname">IteratorAggregate</a> or
     <a href="class.iterator.php" class="classname">Iterator</a> must be used instead.
    </p>
   </p></blockquote>
  </div>


  <div class="section" id="traversable.synopsis">
   <h2 class="title">Interface synopsis</h2>


   <div class="classsynopsis">
    <div class="ooclass"></div>


    <div class="classsynopsisinfo">
     <span class="ooclass">
      <strong class="classname">Traversable</strong>
     </span>
     {</div>


   }</div>


   <p class="para">
    This interface has no methods, its only purpose is to be the base
    interface for all traversable classes.
   </p>

  </div>

 </div>

</div>
<br /><br />
<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=class.traversable&amp;redirect=http://www.php.net/manual/en/class.traversable.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=class.traversable&amp;redirect=http://www.php.net/manual/en/class.traversable.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Traversable</strong>
 </div><div id="allnotes">
 <a name="99195"></a>
 <div class="note">
  <strong class='user'>kevinpeno at gmail dot com</strong>
  <a href="#99195" class="date">02-Aug-2010 10:06</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
While you cannot implement this interface, you can use it in your checks to determine if something is usable in for each. Here is what I use if I'm expecting something that must be iterable via foreach.<br />
<br />
<span class="default">&lt;?php<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">if( !</span><span class="default">is_array</span><span class="keyword">( </span><span class="default">$items </span><span class="keyword">) &amp;&amp; !</span><span class="default">$items </span><span class="keyword">instanceof </span><span class="default">Traversable </span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">//Throw exception here<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=class.traversable&amp;redirect=http://www.php.net/manual/en/class.traversable.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=class.traversable&amp;redirect=http://www.php.net/manual/en/class.traversable.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/class.traversable.php">show source</a> |
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