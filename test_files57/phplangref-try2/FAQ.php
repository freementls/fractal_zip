<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: FAQ - Manual</title>
 <style type="text/css" media="all">
  @import url("/styles/site.css");
  @import url("/styles/mirror.css");
  
 </style>
 <!--[if IE]><![if gte IE 6]><![endif]-->
  <style type="text/css" media="print">
   @import url("/styles/print.css");
  </style>
 <!--[if IE]><![endif]><![endif]-->
 <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
 <link rel="shortcut icon" href="/favicon.ico" />
 <link rel="contents" href="index.php" />
 <link rel="index" href="index.php" />
 <link rel="prev" href="internals2.ze1.tsrm.php" />
 <link rel="next" href="faq.general.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="alternate" href="/manual/en/feeds/faq.atom" type="application/atom+xml" />
 <link rel="canonical" href="http://php.net/manual/en/faq.php" />
 <script type="text/javascript" src="/userprefs.js"></script>
 <base href="http://ru2.php.net/manual/en/faq.php" />
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
 <a href="/" rel="home"><img src="/images/php.gif"
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
          src="/images/small_submit_white.gif"
          class="submit" alt="search" />
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
 <li><a href="features.php">Features</a></li>
 <li><a href="funcref.php">Function Reference</a></li>
 <li><a href="internals2.php">PHP at the Core: A Hacker's Guide to the Zend Engine</a></li>
 <li class="active"><a href="faq.php">FAQ</a></li>
 <li><a href="appendices.php">Appendices</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="faq.general.php">General Information<img src="/images/caret-r.gif" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="internals2.ze1.tsrm.php"><img src="/images/caret-l.gif" alt="&lt;" width="11" height="7" />TSRM API</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/faq.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/faq.php">Brazilian Portuguese</option>
    <option value="zh/faq.php">Chinese (Simplified)</option>
    <option value="fr/faq.php">French</option>
    <option value="de/faq.php">German</option>
    <option value="ja/faq.php">Japanese</option>
    <option value="pl/faq.php">Polish</option>
    <option value="ro/faq.php">Romanian</option>
    <option value="ru/faq.php">Russian</option>
    <option value="fa/faq.php">Persian</option>
    <option value="es/faq.php">Spanish</option>
    <option value="tr/faq.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="/images/small_submit.gif" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="faq" class="book">
  <h1 class="title">FAQ: Frequently Asked Questions</h1>
  
  





  

 



  





  

 



  

 



  





  





  

 



  

 



  

 



  

 



  

 



  

 



 <ul class="chunklist chunklist_book"><li><a href="faq.general.php">General Information</a></li><li><a href="faq.mailinglist.php">Mailing lists</a></li><li><a href="faq.obtaining.php">Obtaining PHP</a></li><li><a href="faq.databases.php">Database issues</a></li><li><a href="faq.installation.php">Installation</a></li><li><a href="faq.build.php">Build Problems</a></li><li><a href="faq.using.php">Using PHP</a></li><li><a href="faq.passwords.php">Password Hashing</a> — Safe Password Hashing</li><li><a href="faq.html.php">PHP and HTML</a></li><li><a href="faq.com.php">PHP and COM</a></li><li><a href="faq.languages.php">PHP and other languages</a></li><li><a href="faq.migration5.php">Migrating from PHP 4 to PHP 5</a></li><li><a href="faq.misc.php">Miscellaneous Questions</a></li></ul></div><br /><br />
<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=faq&amp;redirect=http://ru2.php.net/manual/en/faq.php"><img src="/images/notes-add.gif" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=faq&amp;redirect=http://ru2.php.net/manual/en/faq.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>FAQ</strong>
 </div>
 <div class="note">There are no user contributed notes for this page.</div></div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/faq.php">show source</a> |
 <a href="/credits.php">credits</a> |
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
  <a href="http://isp.rinet.ru/">Cronyx Plus LLC</a><br />
  Last updated: Tue Jul 31 23:41:08 2012 MSK
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