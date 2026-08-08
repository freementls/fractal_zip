<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Introduction - Manual</title>
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
 <link rel="prev" href="language.types.php" />
 <link rel="next" href="language.types.boolean.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/types.intro" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/language.types.intro.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/language.types.intro.php" />
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
 <li class="active"><a href="language.types.intro.php">Introduction</a></li>
 <li><a href="language.types.boolean.php">Booleans</a></li>
 <li><a href="language.types.integer.php">Integers</a></li>
 <li><a href="language.types.float.php">Floating point numbers</a></li>
 <li><a href="language.types.string.php">Strings</a></li>
 <li><a href="language.types.array.php">Arrays</a></li>
 <li><a href="language.types.object.php">Objects</a></li>
 <li><a href="language.types.resource.php">Resources</a></li>
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
  <a href="language.types.boolean.php">Booleans<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.types.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Types</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.types.intro.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/language.types.intro.php">Brazilian Portuguese</option>
    <option value="zh/language.types.intro.php">Chinese (Simplified)</option>
    <option value="fr/language.types.intro.php">French</option>
    <option value="de/language.types.intro.php">German</option>
    <option value="ja/language.types.intro.php">Japanese</option>
    <option value="pl/language.types.intro.php">Polish</option>
    <option value="ro/language.types.intro.php">Romanian</option>
    <option value="ru/language.types.intro.php">Russian</option>
    <option value="fa/language.types.intro.php">Persian</option>
    <option value="es/language.types.intro.php">Spanish</option>
    <option value="tr/language.types.intro.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="language.types.intro" class="sect1">
  <h2 class="title">Introduction</h2>
  
  <p class="simpara">
   PHP supports eight primitive types.
  </p>
  
  <p class="para">
   Four scalar types:
  </p>

  <ul class="itemizedlist">

   <li class="listitem">
    <span class="simpara">
     <span class="type"><a href="language.types.boolean.php" class="type boolean">boolean</a></span>
    </span>
   </li>

   <li class="listitem">
    <span class="simpara">
     <span class="type"><a href="language.types.integer.php" class="type integer">integer</a></span>
    </span>
   </li>

   <li class="listitem">
    <span class="simpara">
     <span class="type"><a href="language.types.float.php" class="type float">float</a></span> (floating-point number, aka <span class="type"><a href="language.types.float.php" class="type double">double</a></span>)
    </span>
   </li>

   <li class="listitem">
    <span class="simpara">
     <span class="type"><a href="language.types.string.php" class="type string">string</a></span>
    </span>
   </li>

  </ul>

  <p class="para">
   Two compound types:
  </p>

  <ul class="itemizedlist">

   <li class="listitem">
    <span class="simpara">
     <span class="type"><a href="language.types.array.php" class="type array">array</a></span>
    </span>
   </li>

   <li class="listitem">
    <span class="simpara">
     <span class="type"><a href="language.types.object.php" class="type object">object</a></span>
    </span>
   </li>

  </ul>

  <p class="para">
   And finally three special types:
  </p>

  <ul class="itemizedlist">

   <li class="listitem">
    <span class="simpara">
     <span class="type"><a href="language.types.resource.php" class="type resource">resource</a></span>
    </span>
   </li>

   <li class="listitem">
    <span class="simpara">
     <span class="type"><a href="language.types.null.php" class="type NULL">NULL</a></span>
    </span>
   </li>

   <li class="listitem">
    <span class="simpara">
     <span class="type"><a href="language.types.callable.php" class="type callable">callable</a></span>
    </span>
   </li>

  </ul>

  <p class="para">
   This manual also introduces some
   <a href="language.pseudo-types.php" class="link">pseudo-types</a> for readability
   reasons:
  </p>

  <ul class="itemizedlist">
 
   <li class="listitem">
    <span class="simpara">
     <span class="type"><a href="language.pseudo-types.php#language.types.mixed" class="type mixed">mixed</a></span>
    </span>
   </li>
 
   <li class="listitem">
    <span class="simpara">
     <span class="type"><a href="language.pseudo-types.php#language.types.number" class="type number">number</a></span>
    </span>
   </li>
 
   <li class="listitem">
    <span class="simpara">
     <span class="type"><a href="language.pseudo-types.php#language.types.callback" class="type callback">callback</a></span>
    </span>
   </li>

  </ul>
  
  <p class="para">
   And the pseudo-variable <em><code class="parameter">$...</code></em>.
  </p>

  <p class="simpara">
   Some references to the type &quot;double&quot; may remain in the manual. Consider
   double the same as float; the two names exist only for historic reasons.
  </p>
  
  <p class="simpara">
   The type of a variable is not usually set by the programmer; rather, it is
   decided at runtime by PHP depending on the context in which that variable is
   used.
  </p>

  <blockquote class="note"><p><strong class="note">Note</strong>: 
   <span class="simpara">
    To check the type and value of an
    <a href="language.expressions.php" class="link">expression</a>, use the
     <span class="function"><a href="function.var-dump.php" class="function">var_dump()</a></span> function.
   </span>

   <p class="para">
    To get a human-readable representation of a type for debugging, use the
     <span class="function"><a href="function.gettype.php" class="function">gettype()</a></span> function. To check for a certain type, do
    <em class="emphasis">not</em> use  <span class="function"><a href="function.gettype.php" class="function">gettype()</a></span>, but rather the
    <em>is_<span class="replaceable">type</span></em> functions. Some
    examples:
   </p>
   
   <div class="informalexample">
    <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br />$a_bool&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">TRUE</span><span style="color: #007700">;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;a&nbsp;boolean<br /></span><span style="color: #0000BB">$a_str&nbsp;&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">"foo"</span><span style="color: #007700">;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;a&nbsp;string<br /></span><span style="color: #0000BB">$a_str2&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'foo'</span><span style="color: #007700">;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;a&nbsp;string<br /></span><span style="color: #0000BB">$an_int&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">12</span><span style="color: #007700">;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;an&nbsp;integer<br /><br /></span><span style="color: #007700">echo&nbsp;</span><span style="color: #0000BB">gettype</span><span style="color: #007700">(</span><span style="color: #0000BB">$a_bool</span><span style="color: #007700">);&nbsp;</span><span style="color: #FF8000">//&nbsp;prints&nbsp;out:&nbsp;&nbsp;boolean<br /></span><span style="color: #007700">echo&nbsp;</span><span style="color: #0000BB">gettype</span><span style="color: #007700">(</span><span style="color: #0000BB">$a_str</span><span style="color: #007700">);&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;prints&nbsp;out:&nbsp;&nbsp;string<br /><br />//&nbsp;If&nbsp;this&nbsp;is&nbsp;an&nbsp;integer,&nbsp;increment&nbsp;it&nbsp;by&nbsp;four<br /></span><span style="color: #007700">if&nbsp;(</span><span style="color: #0000BB">is_int</span><span style="color: #007700">(</span><span style="color: #0000BB">$an_int</span><span style="color: #007700">))&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$an_int&nbsp;</span><span style="color: #007700">+=&nbsp;</span><span style="color: #0000BB">4</span><span style="color: #007700">;<br />}<br /><br /></span><span style="color: #FF8000">//&nbsp;If&nbsp;$a_bool&nbsp;is&nbsp;a&nbsp;string,&nbsp;print&nbsp;it&nbsp;out<br />//&nbsp;(does&nbsp;not&nbsp;print&nbsp;out&nbsp;anything)<br /></span><span style="color: #007700">if&nbsp;(</span><span style="color: #0000BB">is_string</span><span style="color: #007700">(</span><span style="color: #0000BB">$a_bool</span><span style="color: #007700">))&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"String:&nbsp;</span><span style="color: #0000BB">$a_bool</span><span style="color: #DD0000">"</span><span style="color: #007700">;<br />}<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

   </div>
  </p></blockquote>

  <p class="simpara">
   To forcibly convert a variable to a certain type, either
   <a href="language.types.type-juggling.php#language.types.typecasting" class="link">cast</a> the variable or use
   the  <span class="function"><a href="function.settype.php" class="function">settype()</a></span> function on it.
  </p>

  <p class="simpara">
   Note that a variable may be evaluated with different values in certain
   situations, depending on what type it is at the time. For more information,
   see the section on <a href="language.types.type-juggling.php" class="link">Type
   Juggling</a>. <a href="types.comparisons.php" class="link">The type comparison
   tables</a> may also be useful, as they show examples of various
   type-related comparisons.
  </p>
 </div><br /><br />
<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=language.types.intro&amp;redirect=http://www.php.net/manual/en/language.types.intro.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.types.intro&amp;redirect=http://www.php.net/manual/en/language.types.intro.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Introduction</strong>
 </div>
 <div class="note">There are no user contributed notes for this page.</div></div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/language.types.intro.php">show source</a> |
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