<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: PHP at the Core: A Hacker's Guide to the Zend Engine - Manual</title>
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
 <link rel="prev" href="function.xslt-setopt.php" />
 <link rel="next" href="internals2.preface.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/internals2" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="alternate" href="/manual/en/feeds/internals2.atom" type="application/atom+xml" />
 <link rel="canonical" href="http://php.net/manual/en/internals2.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/internals2.php" />
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
 <li><a href="features.php">Features</a></li>
 <li><a href="funcref.php">Function Reference</a></li>
 <li class="active"><a href="internals2.php">PHP at the Core: A Hacker's Guide to the Zend Engine</a></li>
 <li><a href="faq.php">FAQ</a></li>
 <li><a href="appendices.php">Appendices</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="internals2.preface.php">Preface<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="function.xslt-setopt.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />xslt_setopt</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/internals2.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/internals2.php">Brazilian Portuguese</option>
    <option value="zh/internals2.php">Chinese (Simplified)</option>
    <option value="fr/internals2.php">French</option>
    <option value="de/internals2.php">German</option>
    <option value="ja/internals2.php">Japanese</option>
    <option value="pl/internals2.php">Polish</option>
    <option value="ro/internals2.php">Romanian</option>
    <option value="ru/internals2.php">Russian</option>
    <option value="fa/internals2.php">Persian</option>
    <option value="es/internals2.php">Spanish</option>
    <option value="tr/internals2.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="internals2" class="book">
  <h1 class="title">PHP at the Core: A Hacker&#039;s Guide to the Zend Engine</h1>
  




 



  

 



  

 



  

 



  

 



  

 
 
 

  

 



  

 



  

 



  

 



  

 



  







  

 



  

 



  

 

  

 



 <ul class="chunklist chunklist_book"><li><a href="internals2.preface.php">Preface</a></li><li><a href="internals2.counter.php">The &quot;counter&quot; Extension - A Continuing Example</a><ul class="chunklist chunklist_book chunklist_children"><li><a href="internals2.counter.setup.php">Installing/Configuring</a></li><li><a href="internals2.counter.constants.php">Predefined Constants</a></li><li><a href="internals2.counter.examples.php">Examples</a></li><li><a href="internals2.counter.counter-class.php">Counter</a> — The Counter class</li><li><a href="internals2.counter.basic-interface.php">Basic</a> — The basic interface</li><li><a href="internals2.counter.extended-interface.php">Extended</a> — The extended interface</li></ul></li><li><a href="internals2.buildsys.php">The PHP 5 build system</a><ul class="chunklist chunklist_book chunklist_children"><li><a href="internals2.buildsys.environment.php">Building PHP for extension development</a></li><li><a href="internals2.buildsys.skeleton.php">The ext_skel script</a></li><li><a href="internals2.buildsys.configunix.php">Talking to the UNIX build system: config.m4</a></li><li><a href="internals2.buildsys.configwin.php">Talking to the Windows build system: config.w32</a></li></ul></li><li><a href="internals2.structure.php">Extension structure</a><ul class="chunklist chunklist_book chunklist_children"><li><a href="internals2.structure.files.php">Files which make up an extension</a></li><li><a href="internals2.structure.basics.php">Basic constructs</a></li><li><a href="internals2.structure.modstruct.php">The zend_module structure</a></li><li><a href="internals2.structure.globals.php">Extension globals</a></li><li><a href="internals2.structure.lifecycle.php">Life cycle of an extension</a></li><li><a href="internals2.structure.tests.php">Testing an extension</a></li></ul></li><li><a href="internals2.memory.php">Memory management</a><ul class="chunklist chunklist_book chunklist_children"><li><a href="internals2.memory.management.php">Basic memory management</a></li><li><a href="internals2.memory.persistence.php">Data persistence</a></li><li><a href="internals2.memory.tsrm.php">Thread-Safe Resource Manager</a></li></ul></li><li><a href="internals2.variables.php">Working with variables</a><ul class="chunklist chunklist_book chunklist_children"><li><a href="internals2.variables.intro.php">Intro</a></li><li><a href="internals2.variables.creating.php">Creating variables and setting values</a></li></ul></li><li><a href="internals2.funcs.php">Writing functions</a></li><li><a href="internals2.objects.php">Working with classes and objects</a></li><li><a href="internals2.resources.php">Working with resources</a></li><li><a href="internals2.ini.php">Working with INI settings</a></li><li><a href="internals2.streams.php">Working with streams</a></li><li><a href="internals2.pdo.php">PDO Driver How-To</a><ul class="chunklist chunklist_book chunklist_children"><li><a href="internals2.pdo.prerequisites.php">Prerequisites</a></li><li><a href="internals2.pdo.preparation.php">Preparation and Housekeeping</a></li><li><a href="internals2.pdo.implementing.php">Fleshing out your skeleton</a></li><li><a href="internals2.pdo.building.php">Building</a></li><li><a href="internals2.pdo.testing.php">Testing</a></li><li><a href="internals2.pdo.packaging.php">Packaging and distribution</a></li><li><a href="internals2.pdo.pdo-dbh-t.php">pdo_dbh_t definition</a></li><li><a href="internals2.pdo.pdo-stmt-t.php">pdo_stmt_t definition</a></li><li><a href="internals2.pdo.constants.php">Constants</a></li><li><a href="internals2.pdo.error-handling.php">Error handling</a></li></ul></li><li><a href="internals2.faq.php">Extension FAQs</a></li><li><a href="internals2.apiref.php">Zend Engine 2 API reference</a></li><li><a href="internals2.opcodes.php">Zend Engine 2 Opcodes</a><ul class="chunklist chunklist_book chunklist_children"><li><a href="internals2.opcodes.list.php">Opcode Descriptions and Examples</a></li></ul></li><li><a href="internals2.ze1.php">Zend Engine 1</a><ul class="chunklist chunklist_book chunklist_children"><li><a href="internals2.ze1.intro.php">Old introduction</a></li><li><a href="internals2.ze1.streams.php">Streams API for PHP Extension Authors</a></li><li><a href="internals2.ze1.zendapi.php">Zend API: Hacking the Core of PHP</a></li><li><a href="internals2.ze1.tsrm.php">TSRM API</a></li></ul></li></ul></div><br /><br />
<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=internals2&amp;redirect=http://www.php.net/manual/en/internals2.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=internals2&amp;redirect=http://www.php.net/manual/en/internals2.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>PHP at the Core: A Hacker's Guide to the Zend Engine</strong>
 </div>
 <div class="note">There are no user contributed notes for this page.</div></div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/internals2.php">show source</a> |
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