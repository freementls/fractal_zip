<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Features - Manual</title>
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
 <link rel="index" href="index.php" />
 <link rel="prev" href="security.current.php" />
 <link rel="next" href="features.http-auth.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="alternate" href="/manual/en/feeds/features.atom" type="application/atom+xml" />
 <link rel="canonical" href="http://php.net/manual/en/features.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/features.php" />
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
 <li><a href="copyright.php">Copyright</a></li>
 <li><a href="manual.php">PHP Manual</a></li>
 <li><a href="getting-started.php">Getting Started</a></li>
 <li><a href="install.php">Installation and Configuration</a></li>
 <li><a href="langref.php">Language Reference</a></li>
 <li><a href="security.php">Security</a></li>
 <li class="active"><a href="features.php">Features</a></li>
 <li><a href="funcref.php">Function Reference</a></li>
 <li><a href="internals2.php">PHP at the Core: A Hacker's Guide to the Zend Engine</a></li>
 <li><a href="faq.php">FAQ</a></li>
 <li><a href="appendices.php">Appendices</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="features.http-auth.php">HTTP authentication with PHP<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="security.current.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Keeping Current</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/features.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/features.php">Brazilian Portuguese</option>
    <option value="zh/features.php">Chinese (Simplified)</option>
    <option value="fr/features.php">French</option>
    <option value="de/features.php">German</option>
    <option value="ja/features.php">Japanese</option>
    <option value="pl/features.php">Polish</option>
    <option value="ro/features.php">Romanian</option>
    <option value="ru/features.php">Russian</option>
    <option value="fa/features.php">Persian</option>
    <option value="es/features.php">Spanish</option>
    <option value="tr/features.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="features" class="book">
  <h1 class="title">Features</h1>
  

 



  

 



  

 



  





  

 



  

 



  

 



  

 



  

 



  





  

 



 <ul class="chunklist chunklist_book"><li><a href="features.http-auth.php">HTTP authentication with PHP</a></li><li><a href="features.cookies.php">Cookies</a></li><li><a href="features.sessions.php">Sessions</a></li><li><a href="features.xforms.php">Dealing with XForms</a></li><li><a href="features.file-upload.php">Handling file uploads</a><ul class="chunklist chunklist_book chunklist_children"><li><a href="features.file-upload.post-method.php">POST method uploads</a></li><li><a href="features.file-upload.errors.php">Error Messages Explained</a></li><li><a href="features.file-upload.common-pitfalls.php">Common Pitfalls</a></li><li><a href="features.file-upload.multiple.php">Uploading multiple files</a></li><li><a href="features.file-upload.put-method.php">PUT method support</a></li></ul></li><li><a href="features.remote-files.php">Using remote files</a></li><li><a href="features.connection-handling.php">Connection handling</a></li><li><a href="features.persistent-connections.php">Persistent Database Connections</a></li><li><a href="features.safe-mode.php">Safe Mode</a><ul class="chunklist chunklist_book chunklist_children"><li><a href="ini.sect.safe-mode.php">Security and Safe Mode</a></li><li><a href="features.safe-mode.functions.php">Functions restricted/disabled by safe mode</a></li></ul></li><li><a href="features.commandline.php">Command line usage</a> — Using PHP from the command line<ul class="chunklist chunklist_book chunklist_children"><li><a href="features.commandline.introduction.php">Introduction</a></li><li><a href="features.commandline.differences.php">Differences to other SAPIs</a></li><li><a href="features.commandline.options.php">Options</a> — Command line options</li><li><a href="features.commandline.usage.php">Usage</a> — Executing PHP files</li><li><a href="features.commandline.io-streams.php">I/O streams</a> — Input/output streams</li><li><a href="features.commandline.interactive.php">Interactive shell</a></li><li><a href="features.commandline.webserver.php">Built-in web server</a></li><li><a href="features.commandline.ini.php">INI settings</a></li></ul></li><li><a href="features.gc.php">Garbage Collection</a><ul class="chunklist chunklist_book chunklist_children"><li><a href="features.gc.refcounting-basics.php">Reference Counting Basics</a></li><li><a href="features.gc.collecting-cycles.php">Collecting Cycles</a></li><li><a href="features.gc.performance-considerations.php">Performance Considerations</a></li></ul></li></ul></div><br /><br />
<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=features&amp;redirect=http://www.php.net/manual/en/features.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=features&amp;redirect=http://www.php.net/manual/en/features.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Features</strong>
 </div><div id="allnotes">
 <a name="12447"></a>
 <div class="note">
  <strong class='user'>yohgaki at hotmail dot com</strong>
  <a href="#12447" class="date">14-Apr-2001 02:48</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
PHP manages freeing all resources. Users does not required to free file handle resource, database resources, memory, etc, unless programmer need to free resource during script execution.<br />
(All resources are released after script execution)<br />
<br />
PHP4 also have reference count feature. For example, memory for variables is shared when it assigned to other variable. If contents has been changed, PHP4 allocate new memory for it. <br />
<br />
For example, programmer does not have to use pass by reference for large parameters for better performance with PHP4.<br />
<br />
It would be a nice section for new PHP users, if there is "Resource Handling" section or like. Explanation about reference count feature in PHP4 would be very helpful to write better PHP4 scripts also.</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=features&amp;redirect=http://www.php.net/manual/en/features.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=features&amp;redirect=http://www.php.net/manual/en/features.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/features.php">show source</a> |
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