<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Context options and parameters - Manual</title>
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
 <link rel="prev" href="closure.bindto.php" />
 <link rel="next" href="context.socket.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/context" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="alternate" href="/manual/en/feeds/context.atom" type="application/atom+xml" />
 <link rel="canonical" href="http://php.net/manual/en/context.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/context.php" />
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
 <li><a href="language.namespaces.php">Namespaces</a></li>
 <li><a href="language.exceptions.php">Exceptions</a></li>
 <li><a href="language.references.php">References Explained</a></li>
 <li><a href="reserved.variables.php">Predefined Variables</a></li>
 <li><a href="reserved.exceptions.php">Predefined Exceptions</a></li>
 <li><a href="reserved.interfaces.php">Predefined Interfaces</a></li>
 <li class="active"><a href="context.php">Context options and parameters</a></li>
 <li><a href="wrappers.php">Supported Protocols and Wrappers</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="context.socket.php">Socket context options<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="closure.bindto.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Closure::bindTo</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/context.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/context.php">Brazilian Portuguese</option>
    <option value="zh/context.php">Chinese (Simplified)</option>
    <option value="fr/context.php">French</option>
    <option value="de/context.php">German</option>
    <option value="ja/context.php">Japanese</option>
    <option value="pl/context.php">Polish</option>
    <option value="ro/context.php">Romanian</option>
    <option value="ru/context.php">Russian</option>
    <option value="fa/context.php">Persian</option>
    <option value="es/context.php">Spanish</option>
    <option value="tr/context.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="context" class="reference">
 <h1 class="title">Context options and parameters</h1>
 
 <div class="partintro">
  <p class="para">
   PHP offers various context options and parameters which can be used with all
   filesystem and stream wrappers. The context is created with
    <span class="function"><a href="function.stream-context-create.php" class="function">stream_context_create()</a></span>. Options are set with
    <span class="function"><a href="function.stream-context-set-option.php" class="function">stream_context_set_option()</a></span> and parameters with
    <span class="function"><a href="function.stream-context-set-params.php" class="function">stream_context_set_params()</a></span>.
  </p>
 </div>
 
 



 



 



 



 



 



 



 



 



 



 



 



 



 



 
<h2>Table of Contents</h2><ul class="chunklist chunklist_reference"><li><a href="context.socket.php">Socket context options</a> — Socket context option listing</li><li><a href="context.http.php">HTTP context options</a> — HTTP context option listing</li><li><a href="context.ftp.php">FTP context options</a> — FTP context option listing</li><li><a href="context.ssl.php">SSL context options</a> — SSL context option listing</li><li><a href="context.curl.php">CURL context options</a> — CURL context option listing</li><li><a href="context.phar.php">Phar context options</a> — Phar context option listing</li><li><a href="context.params.php">Context parameters</a> — Context parameter listing</li></ul>
</div>
<br /><br />
<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=context&amp;redirect=http://www.php.net/manual/en/context.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=context&amp;redirect=http://www.php.net/manual/en/context.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Context options and parameters</strong>
 </div>
 <div class="note">There are no user contributed notes for this page.</div></div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/context.php">show source</a> |
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