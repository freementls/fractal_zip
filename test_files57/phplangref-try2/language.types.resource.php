<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Resources - Manual</title>
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
 <link rel="index" href="language.types.php" />
 <link rel="prev" href="language.types.object.php" />
 <link rel="next" href="language.types.null.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/types.resource" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/language.types.resource.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/language.types.resource.php" />
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
 <li class="header up"><a href="language.types.php">Types</a></li>
 <li><a href="language.types.intro.php">Introduction</a></li>
 <li><a href="language.types.boolean.php">Booleans</a></li>
 <li><a href="language.types.integer.php">Integers</a></li>
 <li><a href="language.types.float.php">Floating point numbers</a></li>
 <li><a href="language.types.string.php">Strings</a></li>
 <li><a href="language.types.array.php">Arrays</a></li>
 <li><a href="language.types.object.php">Objects</a></li>
 <li class="active"><a href="language.types.resource.php">Resources</a></li>
 <li><a href="language.types.null.php">NULL</a></li>
 <li><a href="language.types.callable.php">Callbacks</a></li>
 <li><a href="language.pseudo-types.php">Pseudo-types and variables used in this documentation</a></li>
 <li><a href="language.types.type-juggling.php">Type Juggling</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="language.types.null.php">NULL<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.types.object.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Objects</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.types.resource.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/language.types.resource.php">Brazilian Portuguese</option>
    <option value="zh/language.types.resource.php">Chinese (Simplified)</option>
    <option value="fr/language.types.resource.php">French</option>
    <option value="de/language.types.resource.php">German</option>
    <option value="ja/language.types.resource.php">Japanese</option>
    <option value="pl/language.types.resource.php">Polish</option>
    <option value="ro/language.types.resource.php">Romanian</option>
    <option value="ru/language.types.resource.php">Russian</option>
    <option value="fa/language.types.resource.php">Persian</option>
    <option value="es/language.types.resource.php">Spanish</option>
    <option value="tr/language.types.resource.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="language.types.resource" class="sect1">
 <h2 class="title">Resources</h2>
  
 <p class="para">
  A <span class="type"><a href="language.types.resource.php" class="type resource">resource</a></span> is a special variable, holding a reference to an
  external resource. Resources are created and used by special functions. See
  the <a href="resource.php" class="link">appendix</a> for a listing of all these
  functions and the corresponding <span class="type"><a href="language.types.resource.php" class="type resource">resource</a></span> types.
 </p>

 <p class="para">
  See also the  <span class="function"><a href="function.get-resource-type.php" class="function">get_resource_type()</a></span> function.
 </p>

 <div class="sect2" id="language.types.resource.casting">
  <h3 class="title">Converting to resource</h3>
  
  <p class="para">
   As <span class="type"><a href="language.types.resource.php" class="type resource">resource</a></span> variables hold special handlers to opened files,
   database connections, image canvas areas and the like, converting to a
   <span class="type"><a href="language.types.resource.php" class="type resource">resource</a></span> makes no sense.
  </p>
 </div>

 <div class="sect2" id="language.types.resource.self-destruct">
  <h3 class="title">Freeing resources</h3>
  
  <p class="para">
   Thanks to the reference-counting system introduced with PHP 4&#039;s Zend Engine,
   a <span class="type"><a href="language.types.resource.php" class="type resource">resource</a></span> with no more references to it is detected
   automatically, and it is freed by the garbage collector. For this reason, it
   is rarely necessary to free the memory manually.
  </p>

  <blockquote class="note"><p><strong class="note">Note</strong>: 
   <span class="simpara">
    Persistent database links are an exception to this rule. They are
    <em class="emphasis">not</em> destroyed by the garbage collector. See the
    <a href="features.persistent-connections.php" class="link">persistent
    connections</a> section for more information.
   </span>
  </p></blockquote>
  
 </div>
</div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="language.types.null.php">NULL<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.types.object.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Objects</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.types.resource.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=language.types.resource&amp;redirect=http://www.php.net/manual/en/language.types.resource.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.types.resource&amp;redirect=http://www.php.net/manual/en/language.types.resource.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Resources</strong>
 </div><div id="allnotes">
 <a name="97565"></a>
 <div class="note">
  <strong class='user'>Soos Gergely</strong>
  <a href="#97565" class="date">26-Apr-2010 05:24</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
It is always wrong to assume that some operation, like casting to a resource makes no sense. People will always find some extreme case where it would be useful; like the previous example with mysql. My problem was that I wanted to start daemons from a web interface but the apache filehandles were inherited which caused that apache was unable to restart. If I could only typecast a number to a filehandle and then close it... Instead I had to write a small C program that closes every filehandle and then starts my program. I surely miss Apache2::SubProcess from perl. (Also, in perl you can reopen a file and then close it using IO::Handle module's fdopen. I'm just saying.)</span>
</code></div>
  </div>
 </div>
 <a name="87666"></a>
 <div class="note">
  <strong class='user'>wetmonkey__ at  at __gmail dot com</strong>
  <a href="#87666" class="date">15-Dec-2008 04:17</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Resources are commonly used to iterate through a mysql or file handle.<br />
example<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">while(</span><span class="default">$row </span><span class="keyword">= </span><span class="default">mysql_fetch_row</span><span class="keyword">(</span><span class="default">$resource</span><span class="keyword">)){<br />
&nbsp; echo </span><span class="default">$row</span><span class="keyword">[</span><span class="default">0</span><span class="keyword">] ;<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
It's possible to fake this treatment. <br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">fakewhile</span><span class="keyword">{<br />
<br />
public </span><span class="default">$arrayCount</span><span class="keyword">;<br />
public </span><span class="default">$arrayCounter</span><span class="keyword">;<br />
<br />
function </span><span class="default">setArrValues</span><span class="keyword">(){<br />
&nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">arrValues </span><span class="keyword">= array(</span><span class="default">0 </span><span class="keyword">=&gt;array(</span><span class="string">"apple"</span><span class="keyword">,</span><span class="string">"artichoke"</span><span class="keyword">,</span><span class="string">"apricot"</span><span class="keyword">),</span><span class="default">1 </span><span class="keyword">=&gt; array(</span><span class="string">"bears"</span><span class="keyword">,</span><span class="string">"dogs"</span><span class="keyword">,</span><span class="string">"cats"</span><span class="keyword">));<br />
&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">arrayCounter </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">;<br />
&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">arrayCount </span><span class="keyword">= </span><span class="default">count</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">arrValues</span><span class="keyword">);<br />
}<br />
<br />
function </span><span class="default">outputValues</span><span class="keyword">(){<br />
</span><span class="comment">/*<br />
&nbsp;* Anything until the if statement is evaluted one more<br />
&nbsp;* time then the array count value<br />
&nbsp;*/<br />
&nbsp;</span><span class="default">$arrayInfo </span><span class="keyword">= </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">arrValues</span><span class="keyword">;<br />
&nbsp;</span><span class="default">$arrCounter </span><span class="keyword">= </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">arrayCounter</span><span class="keyword">;<br />
&nbsp;if(</span><span class="default">$arrCounter </span><span class="keyword">&gt; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">arrayCount</span><span class="keyword">){<br />
&nbsp; return </span><span class="default">false</span><span class="keyword">;<br />
&nbsp;}<br />
&nbsp;</span><span class="default">$endCounter </span><span class="keyword">= </span><span class="default">$arrCounter</span><span class="keyword">+</span><span class="default">1</span><span class="keyword">;<br />
&nbsp;</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">arrayCounter </span><span class="keyword">= </span><span class="default">$endCounter</span><span class="keyword">;<br />
&nbsp;return </span><span class="default">$arrayInfo</span><span class="keyword">[</span><span class="default">$arrCounter</span><span class="keyword">];<br />
}<br />
<br />
}<br />
<br />
</span><span class="default">$fw </span><span class="keyword">= new </span><span class="default">fakewhile</span><span class="keyword">();<br />
</span><span class="default">$fw</span><span class="keyword">-&gt;</span><span class="default">setArrValues</span><span class="keyword">();<br />
while(</span><span class="default">$row </span><span class="keyword">= </span><span class="default">$fw</span><span class="keyword">-&gt;</span><span class="default">outputValues</span><span class="keyword">()){<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">print_r</span><span class="keyword">(</span><span class="default">$row</span><span class="keyword">);<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
Hopefully will get someone started on completing a complete application.</span>
</code></div>
  </div>
 </div>
 <a name="84276"></a>
 <div class="note">
  <strong class='user'>adrian dot dziubek at gmail dot com</strong>
  <a href="#84276" class="date">07-Jul-2008 08:55</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I spent an hour trying to create mock setup for testing SQL queries. The explanation here, that a resource contains file handlers and therefore there is no sense in trying to create one is lame. Being unable to redefine functions, creating a fake resource was the second thing I tried to put test in place, but looking at the search results, I see I'm the first one to try... For me it looks like security by obscurity.</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=language.types.resource&amp;redirect=http://www.php.net/manual/en/language.types.resource.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.types.resource&amp;redirect=http://www.php.net/manual/en/language.types.resource.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/language.types.resource.php">show source</a> |
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