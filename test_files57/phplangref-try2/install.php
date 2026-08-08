<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Installation and Configuration - Manual</title>
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
 <link rel="prev" href="tutorial.whatsnext.php" />
 <link rel="next" href="install.general.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/install" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="alternate" href="/manual/en/feeds/install.atom" type="application/atom+xml" />
 <link rel="canonical" href="http://php.net/manual/en/install.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/install.php" />
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
 <li class="active"><a href="install.php">Installation and Configuration</a></li>
 <li><a href="langref.php">Language Reference</a></li>
 <li><a href="security.php">Security</a></li>
 <li><a href="features.php">Features</a></li>
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
  <a href="install.general.php">General Installation Considerations<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="tutorial.whatsnext.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />What's next?</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/install.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/install.php">Brazilian Portuguese</option>
    <option value="zh/install.php">Chinese (Simplified)</option>
    <option value="fr/install.php">French</option>
    <option value="de/install.php">German</option>
    <option value="ja/install.php">Japanese</option>
    <option value="pl/install.php">Polish</option>
    <option value="ro/install.php">Romanian</option>
    <option value="ru/install.php">Russian</option>
    <option value="fa/install.php">Persian</option>
    <option value="es/install.php">Spanish</option>
    <option value="tr/install.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="install" class="book">
  <h1 class="title">Installation and Configuration</h1>
  

  



  

  



  





  

  



  





  

  



  


   



  


  



  





 <ul class="chunklist chunklist_book"><li><a href="install.general.php">General Installation Considerations</a></li><li><a href="install.unix.php">Installation on Unix systems</a><ul class="chunklist chunklist_book chunklist_children"><li><a href="install.unix.apache.php">Apache 1.3.x on Unix systems</a></li><li><a href="install.unix.apache2.php">Apache 2.x on Unix systems</a></li><li><a href="install.unix.lighttpd-14.php">Lighttpd 1.4 on Unix systems</a></li><li><a href="install.unix.sun.php">Sun, iPlanet and Netscape servers on Sun Solaris</a></li><li><a href="install.unix.commandline.php">CGI and command line setups</a></li><li><a href="install.unix.hpux.php">HP-UX specific installation notes</a></li><li><a href="install.unix.openbsd.php">OpenBSD installation notes</a></li><li><a href="install.unix.solaris.php">Solaris specific installation tips</a></li><li><a href="install.unix.debian.php">Debian GNU/Linux installation notes</a></li></ul></li><li><a href="install.macosx.php">Installation on Mac OS X</a><ul class="chunklist chunklist_book chunklist_children"><li><a href="install.macosx.packages.php">Using Packages</a></li><li><a href="install.macosx.bundled.php">Using the bundled PHP</a></li><li><a href="install.macosx.compile.php">Compiling PHP on Mac OS X</a></li></ul></li><li><a href="install.windows.php">Installation on Windows systems</a><ul class="chunklist chunklist_book chunklist_children"><li><a href="install.windows.installer.php">Windows Installer (PHP 5.1.0 and earlier)</a></li><li><a href="install.windows.installer.msi.php">Windows Installer (PHP 5.2 and later)</a></li><li><a href="install.windows.manual.php">Manual Installation Steps</a></li><li><a href="install.windows.iis.php">Microsoft IIS</a></li><li><a href="install.windows.iis6.php">Microsoft IIS 5.1 and IIS 6.0</a></li><li><a href="install.windows.iis7.php">Microsoft IIS 7.0 and later</a></li><li><a href="install.windows.apache1.php">Apache 1.3.x on Microsoft Windows</a></li><li><a href="install.windows.apache2.php">Apache 2.x on Microsoft Windows</a></li><li><a href="install.windows.sun.php">Sun, iPlanet and Netscape servers on Microsoft Windows</a></li><li><a href="install.windows.sambar.php">Sambar Server on Microsoft Windows</a></li><li><a href="install.windows.xitami.php">Xitami on Microsoft Windows</a></li><li><a href="install.windows.building.php">Building from source</a></li><li><a href="install.windows.extensions.php">Installation of extensions on Windows</a></li><li><a href="install.windows.commandline.php">Command Line PHP on Microsoft Windows</a></li></ul></li><li><a href="install.cloud.php">Installation on Cloud Computing platforms</a><ul class="chunklist chunklist_book chunklist_children"><li><a href="install.cloud.azure.php">Microsoft Azure</a></li><li><a href="install.cloud.ec2.php">Amazon EC2</a></li></ul></li><li><a href="install.fpm.php">FastCGI Process Manager (FPM)</a><ul class="chunklist chunklist_book chunklist_children"><li><a href="install.fpm.install.php">Installation</a></li><li><a href="install.fpm.configuration.php">Configuration</a></li></ul></li><li><a href="install.pecl.php">Installation of PECL extensions</a><ul class="chunklist chunklist_book chunklist_children"><li><a href="install.pecl.intro.php">Introduction to PECL Installations</a></li><li><a href="install.pecl.downloads.php">Downloading PECL extensions</a></li><li><a href="install.pecl.windows.php">Installing a PHP extension on Windows</a></li><li><a href="install.pecl.pear.php">Compiling shared PECL extensions with the pecl command</a></li><li><a href="install.pecl.phpize.php">Compiling shared PECL extensions with phpize</a></li><li><a href="install.pecl.php-config.php">php-config</a></li><li><a href="install.pecl.static.php">Compiling PECL extensions statically into PHP</a></li></ul></li><li><a href="install.problems.php">Problems?</a><ul class="chunklist chunklist_book chunklist_children"><li><a href="install.problems.faq.php">Read the FAQ</a></li><li><a href="install.problems.support.php">Other problems</a></li><li><a href="install.problems.bugs.php">Bug reports</a></li></ul></li><li><a href="configuration.php">Runtime Configuration</a><ul class="chunklist chunklist_book chunklist_children"><li><a href="configuration.file.php">The configuration file</a></li><li><a href="configuration.file.per-user.php">.user.ini files</a></li><li><a href="configuration.changes.modes.php">Where a configuration setting may be set</a></li><li><a href="configuration.changes.php">How to change configuration settings</a></li></ul></li></ul></div><br /><br />
<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=install&amp;redirect=http://www.php.net/manual/en/install.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=install&amp;redirect=http://www.php.net/manual/en/install.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Installation and Configuration</strong>
 </div><div id="allnotes">
 <a name="60199"></a>
 <div class="note">
  <strong class='user'>nullplan</strong>
  <a href="#60199" class="date">29-Dec-2005 10:23</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
For German users there is a very great tutorial <br />
in setting up a local test environment on windows on <br />
www.wintotal.de/Artikel/lokaletestumgebung/lokaletestumgebung.php .</span>
</code></div>
  </div>
 </div>
 <a name="18270"></a>
 <div class="note">
  <strong class='user'>luke at 4d dot com</strong>
  <a href="#18270" class="date">16-Jan-2002 01:31</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
4D WebSTAR V for Mac OS X supports the default CGI compile of PHP.<br />
<br />
Note that most compile instructions for PHP on Mac OS X are specific to the Apache server.</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=install&amp;redirect=http://www.php.net/manual/en/install.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=install&amp;redirect=http://www.php.net/manual/en/install.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/install.php">show source</a> |
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