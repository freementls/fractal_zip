<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Function Reference - Manual</title>
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
 <link rel="prev" href="features.gc.performance-considerations.php" />
 <link rel="next" href="refs.basic.php.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/funcref" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="alternate" href="/manual/en/feeds/funcref.atom" type="application/atom+xml" />
 <link rel="canonical" href="http://php.net/manual/en/funcref.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/funcref.php" />
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
 <li class="active"><a href="funcref.php">Function Reference</a></li>
 <li><a href="internals2.php">PHP at the Core: A Hacker's Guide to the Zend Engine</a></li>
 <li><a href="faq.php">FAQ</a></li>
 <li><a href="appendices.php">Appendices</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="refs.basic.php.php">Affecting PHP's Behaviour<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="features.gc.performance-considerations.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Performance Considerations</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/funcref.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/funcref.php">Brazilian Portuguese</option>
    <option value="zh/funcref.php">Chinese (Simplified)</option>
    <option value="fr/funcref.php">French</option>
    <option value="de/funcref.php">German</option>
    <option value="ja/funcref.php">Japanese</option>
    <option value="pl/funcref.php">Polish</option>
    <option value="ro/funcref.php">Romanian</option>
    <option value="ru/funcref.php">Russian</option>
    <option value="fa/funcref.php">Persian</option>
    <option value="es/funcref.php">Spanish</option>
    <option value="tr/funcref.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="funcref" class="set">
  <h1 class="title">Function Reference</h1>
  <div class="info">
   <div class="abstract">
    <p class="para">
     <div class="tip"><strong class="tip">Tip</strong>
      <p class="simpara">
       See also <a href="extensions.php" class="xref">Extension List/Categorization</a>.
      </p>
     </div>
    </p>
   </div>
  </div>

  

  

  

  

  

  

  

  

  

  

  

  

  

  

  

  

  

  

  

  

  

  

  

  

  

  
 <ul class="chunklist chunklist_set"><li><a href="refs.basic.php.php">Affecting PHP's Behaviour</a><ul class="chunklist chunklist_set chunklist_children"><li><a href="book.apc.php">APC</a> — Alternative PHP Cache</li><li><a href="book.apd.php">APD</a> — Advanced PHP debugger</li><li><a href="book.bcompiler.php">bcompiler</a> — PHP bytecode Compiler</li><li><a href="book.errorfunc.php">Error Handling</a> — Error Handling and Logging</li><li><a href="book.htscanner.php">htscanner</a> — htaccess-like support for all SAPIs</li><li><a href="book.inclued.php">inclued</a> — Inclusion hierarchy viewer</li><li><a href="book.memtrack.php">Memtrack</a></li><li><a href="book.outcontrol.php">Output Control</a> — Output Buffering Control</li><li><a href="book.info.php">PHP Options/Info</a> — PHP Options and Information</li><li><a href="book.runkit.php">runkit</a></li><li><a href="book.scream.php">scream</a> — Break the silence operator</li><li><a href="book.weakref.php">Weakref</a> — Weak References</li><li><a href="book.wincache.php">WinCache</a> — Windows Cache for PHP</li><li><a href="book.xhprof.php">Xhprof</a> — Hierarchical Profiler</li></ul></li><li><a href="refs.utilspec.audio.php">Audio Formats Manipulation</a><ul class="chunklist chunklist_set chunklist_children"><li><a href="book.id3.php">ID3</a> — ID3 Tags</li><li><a href="book.ktaglib.php">KTaglib</a></li><li><a href="book.oggvorbis.php">oggvorbis</a> — OGG/Vorbis</li><li><a href="book.openal.php">OpenAL</a> — OpenAL Audio Bindings</li></ul></li><li><a href="refs.remote.auth.php">Authentication Services</a><ul class="chunklist chunklist_set chunklist_children"><li><a href="book.kadm5.php">KADM5</a> — Kerberos V</li><li><a href="book.radius.php">Radius</a></li></ul></li><li><a href="refs.calendar.php">Date and Time Related Extensions</a><ul class="chunklist chunklist_set chunklist_children"><li><a href="book.calendar.php">Calendar</a></li><li><a href="book.datetime.php">Date/Time</a> — Date and Time</li></ul></li><li><a href="refs.utilspec.cmdline.php">Command Line Specific Extensions</a><ul class="chunklist chunklist_set chunklist_children"><li><a href="book.ncurses.php">Ncurses</a> — Ncurses Terminal Screen Control</li><li><a href="book.newt.php">Newt</a></li><li><a href="book.readline.php">Readline</a> — GNU Readline</li></ul></li><li><a href="refs.compression.php">Compression and Archive Extensions</a><ul class="chunklist chunklist_set chunklist_children"><li><a href="book.bzip2.php">Bzip2</a></li><li><a href="book.lzf.php">LZF</a></li><li><a href="book.phar.php">Phar</a></li><li><a href="book.rar.php">Rar</a> — Rar Archiving</li><li><a href="book.zip.php">Zip</a></li><li><a href="book.zlib.php">Zlib</a> — Zlib Compression</li></ul></li><li><a href="refs.creditcard.php">Credit Card Processing</a><ul class="chunklist chunklist_set chunklist_children"><li><a href="book.mcve.php">MCVE</a> — MCVE (Monetra) Payment</li><li><a href="book.spplus.php">SPPLUS</a> — SPPLUS Payment System</li></ul></li><li><a href="refs.crypto.php">Cryptography Extensions</a><ul class="chunklist chunklist_set chunklist_children"><li><a href="book.crack.php">Crack</a> — Cracklib</li><li><a href="book.hash.php">Hash</a> — HASH Message Digest Framework</li><li><a href="book.mcrypt.php">Mcrypt</a></li><li><a href="book.mhash.php">Mhash</a></li><li><a href="book.openssl.php">OpenSSL</a></li></ul></li><li><a href="refs.database.php">Database Extensions</a><ul class="chunklist chunklist_set chunklist_children"><li><a href="refs.database.abstract.php">Abstraction Layers</a></li><li><a href="refs.database.vendors.php">Vendor Specific Database Extensions</a></li></ul></li><li><a href="refs.fileprocess.file.php">File System Related Extensions</a><ul class="chunklist chunklist_set chunklist_children"><li><a href="book.dio.php">Direct IO</a></li><li><a href="book.dir.php">Directories</a></li><li><a href="book.fileinfo.php">Fileinfo</a> — File Information</li><li><a href="book.filesystem.php">Filesystem</a></li><li><a href="book.inotify.php">Inotify</a></li><li><a href="book.mime-magic.php">Mimetype</a></li><li><a href="book.proctitle.php">Proctitle</a></li><li><a href="book.xattr.php">xattr</a></li><li><a href="book.xdiff.php">xdiff</a></li></ul></li><li><a href="refs.international.php">Human Language and Character Encoding Support</a><ul class="chunklist chunklist_set chunklist_children"><li><a href="book.enchant.php">Enchant</a> — Enchant spelling library</li><li><a href="book.fribidi.php">FriBiDi</a></li><li><a href="book.gettext.php">Gettext</a></li><li><a href="book.iconv.php">iconv</a></li><li><a href="book.intl.php">intl</a> — Internationalization Functions</li><li><a href="book.mbstring.php">Multibyte String</a></li><li><a href="book.pspell.php">Pspell</a></li><li><a href="book.recode.php">Recode</a> — GNU Recode</li></ul></li><li><a href="refs.utilspec.image.php">Image Processing and Generation</a><ul class="chunklist chunklist_set chunklist_children"><li><a href="book.cairo.php">Cairo</a></li><li><a href="book.exif.php">Exif</a> — Exchangeable image information</li><li><a href="book.image.php">GD</a> — Image Processing and GD</li><li><a href="book.gmagick.php">Gmagick</a></li><li><a href="book.imagick.php">ImageMagick</a> — Image Processing (ImageMagick)</li></ul></li><li><a href="refs.remote.mail.php">Mail Related Extensions</a><ul class="chunklist chunklist_set chunklist_children"><li><a href="book.cyrus.php">Cyrus</a> — Cyrus IMAP administration</li><li><a href="book.imap.php">IMAP</a> — IMAP, POP3 and NNTP</li><li><a href="book.mail.php">Mail</a></li><li><a href="book.mailparse.php">Mailparse</a></li><li><a href="book.vpopmail.php">vpopmail</a></li></ul></li><li><a href="refs.math.php">Mathematical Extensions</a><ul class="chunklist chunklist_set chunklist_children"><li><a href="book.bc.php">BC Math</a> — BCMath Arbitrary Precision Mathematics</li><li><a href="book.gmp.php">GMP</a> — GNU Multiple Precision</li><li><a href="book.lapack.php">Lapack</a></li><li><a href="book.math.php">Math</a> — Mathematical Functions</li><li><a href="book.stats.php">Statistics</a></li><li><a href="book.trader.php">Trader</a> — Technical Analysis for traders</li></ul></li><li><a href="refs.utilspec.nontext.php">Non-Text MIME Output</a><ul class="chunklist chunklist_set chunklist_children"><li><a href="book.fdf.php">FDF</a> — Forms Data Format</li><li><a href="book.gnupg.php">GnuPG</a> — GNU Privacy Guard</li><li><a href="book.haru.php">haru</a> — Haru PDF</li><li><a href="book.ming.php">Ming</a> — Ming (flash)</li><li><a href="book.pdf.php">PDF</a></li><li><a href="book.ps.php">PS</a> — PostScript document creation</li><li><a href="book.rpmreader.php">RPM Reader</a> — RPM Header Reading</li><li><a href="book.swf.php">SWF</a> — Shockwave Flash</li></ul></li><li><a href="refs.fileprocess.process.php">Process Control Extensions</a><ul class="chunklist chunklist_set chunklist_children"><li><a href="book.eio.php">Eio</a></li><li><a href="book.expect.php">Expect</a></li><li><a href="book.libevent.php">Libevent</a></li><li><a href="book.pcntl.php">PCNTL</a> — Process Control</li><li><a href="book.posix.php">POSIX</a></li><li><a href="book.exec.php">Program execution</a> — System program execution</li><li><a href="book.sem.php">Semaphore</a> — Semaphore, Shared Memory and IPC</li><li><a href="book.shmop.php">Shared Memory</a></li></ul></li><li><a href="refs.basic.other.php">Other Basic Extensions</a><ul class="chunklist chunklist_set chunklist_children"><li><a href="book.geoip.php">GeoIP</a> — Geo IP Location</li><li><a href="book.json.php">JSON</a> — JavaScript Object Notation</li><li><a href="book.judy.php">Judy</a> — Judy Arrays</li><li><a href="book.lua.php">Lua</a></li><li><a href="book.misc.php">Misc.</a> — Miscellaneous Functions</li><li><a href="book.parsekit.php">Parsekit</a></li><li><a href="book.spl.php">SPL</a> — Standard PHP Library (SPL)</li><li><a href="book.spl-types.php">SPL Types</a> — SPL Type Handling</li><li><a href="book.stream.php">Streams</a></li><li><a href="book.tidy.php">Tidy</a></li><li><a href="book.tokenizer.php">Tokenizer</a></li><li><a href="book.url.php">URLs</a></li><li><a href="book.v8js.php">V8js</a> — V8 Javascript Engine Integration</li><li><a href="book.yaml.php">Yaml</a> — YAML Data Serialization</li><li><a href="book.yaf.php">Yaf</a></li><li><a href="book.taint.php">Taint</a></li></ul></li><li><a href="refs.remote.other.php">Other Services</a><ul class="chunklist chunklist_set chunklist_children"><li><a href="book.amqp.php">AMQP</a></li><li><a href="book.chdb.php">chdb</a> — Constant hash database</li><li><a href="book.curl.php">cURL</a> — Client URL Library</li><li><a href="book.fam.php">FAM</a> — File Alteration Monitor</li><li><a href="book.ftp.php">FTP</a></li><li><a href="book.gearman.php">Gearman</a></li><li><a href="book.net-gopher.php">Gopher</a> — Net Gopher</li><li><a href="book.gupnp.php">Gupnp</a></li><li><a href="book.http.php">HTTP</a></li><li><a href="book.hw.php">Hyperwave</a></li><li><a href="book.hwapi.php">Hyperwave API</a></li><li><a href="book.java.php">Java</a> — PHP / Java Integration</li><li><a href="book.ldap.php">LDAP</a> — Lightweight Directory Access Protocol</li><li><a href="book.notes.php">Lotus Notes</a></li><li><a href="book.memcache.php">Memcache</a></li><li><a href="book.memcached.php">Memcached</a></li><li><a href="book.mqseries.php">mqseries</a></li><li><a href="book.network.php">Network</a></li><li><a href="book.rrd.php">RRD</a> — RRDtool</li><li><a href="book.sam.php">SAM</a> — Simple Asynchronous Messaging</li><li><a href="book.snmp.php">SNMP</a></li><li><a href="book.sockets.php">Sockets</a></li><li><a href="book.ssh2.php">SSH2</a> — Secure Shell2</li><li><a href="book.stomp.php">Stomp</a> — Stomp Client</li><li><a href="book.svm.php">SVM</a> — Support Vector Machine</li><li><a href="book.svn.php">SVN</a> — Subversion</li><li><a href="book.tcpwrap.php">TCP</a> — TCP Wrappers</li><li><a href="book.varnish.php">Varnish</a></li><li><a href="book.yaz.php">YAZ</a></li><li><a href="book.nis.php">YP/NIS</a></li></ul></li><li><a href="refs.search.php">Search Engine Extensions</a><ul class="chunklist chunklist_set chunklist_children"><li><a href="book.mnogosearch.php">mnoGoSearch</a></li><li><a href="book.solr.php">Solr</a> — Apache Solr</li><li><a href="book.sphinx.php">Sphinx</a> — Sphinx Client</li><li><a href="book.swish.php">Swish</a> — Swish Indexing</li></ul></li><li><a href="refs.utilspec.server.php">Server Specific Extensions</a><ul class="chunklist chunklist_set chunklist_children"><li><a href="book.apache.php">Apache</a></li><li><a href="book.iisfunc.php">IIS</a> — IIS Administration</li><li><a href="book.nsapi.php">NSAPI</a></li></ul></li><li><a href="refs.basic.session.php">Session Extensions</a><ul class="chunklist chunklist_set chunklist_children"><li><a href="book.msession.php">Msession</a> — Mohawk Software Session Handler Functions</li><li><a href="book.session.php">Sessions</a> — Session Handling</li><li><a href="book.session-pgsql.php">Session PgSQL</a> — PostgreSQL Session Save Handler</li></ul></li><li><a href="refs.basic.text.php">Text Processing</a><ul class="chunklist chunklist_set chunklist_children"><li><a href="book.bbcode.php">BBCode</a> — Bulletin Board Code</li><li><a href="book.pcre.php">PCRE</a> — Regular Expressions (Perl-Compatible)</li><li><a href="book.regex.php">POSIX Regex</a> — Regular Expression (POSIX Extended)</li><li><a href="book.ssdeep.php">ssdeep</a> — ssdeep Fuzzy Hashing</li><li><a href="book.strings.php">Strings</a></li></ul></li><li><a href="refs.basic.vartype.php">Variable and Type Related Extensions</a><ul class="chunklist chunklist_set chunklist_children"><li><a href="book.array.php">Arrays</a></li><li><a href="book.classobj.php">Classes/Objects</a> — Class/Object Information</li><li><a href="book.classkit.php">Classkit</a></li><li><a href="book.ctype.php">Ctype</a> — Character type checking</li><li><a href="book.filter.php">Filter</a> — Data Filtering</li><li><a href="book.funchand.php">Function Handling</a></li><li><a href="book.objaggregation.php">Object Aggregation</a> — Object Aggregation/Composition [PHP 4]</li><li><a href="book.quickhash.php">Quickhash</a></li><li><a href="book.reflection.php">Reflection</a></li><li><a href="book.var.php">Variable handling</a></li></ul></li><li><a href="refs.webservice.php">Web Services</a><ul class="chunklist chunklist_set chunklist_children"><li><a href="book.oauth.php">OAuth</a></li><li><a href="book.sca.php">SCA</a></li><li><a href="book.soap.php">SOAP</a></li><li><a href="book.xmlrpc.php">XML-RPC</a></li></ul></li><li><a href="refs.utilspec.windows.php">Windows Only Extensions</a><ul class="chunklist chunklist_set chunklist_children"><li><a href="book.dotnet.php">.NET</a></li><li><a href="book.com.php">COM</a> — COM and .Net (Windows)</li><li><a href="book.printer.php">Printer</a></li><li><a href="book.w32api.php">W32api</a></li><li><a href="book.win32ps.php">win32ps</a></li><li><a href="book.win32service.php">win32service</a></li></ul></li><li><a href="refs.xml.php">XML Manipulation</a><ul class="chunklist chunklist_set chunklist_children"><li><a href="book.dom.php">DOM</a> — Document Object Model</li><li><a href="book.libxml.php">libxml</a></li><li><a href="book.qtdom.php">qtdom</a></li><li><a href="book.sdo.php">SDO</a> — Service Data Objects</li><li><a href="book.sdodasrel.php">SDO-DAS-Relational</a> — SDO Relational Data Access Service</li><li><a href="book.sdo-das-xml.php">SDO DAS XML</a> — SDO XML Data Access Service</li><li><a href="book.simplexml.php">SimpleXML</a></li><li><a href="book.wddx.php">WDDX</a></li><li><a href="book.xml.php">XML Parser</a></li><li><a href="book.xmlreader.php">XMLReader</a></li><li><a href="book.xmlwriter.php">XMLWriter</a></li><li><a href="book.xsl.php">XSL</a></li><li><a href="book.xslt.php">XSLT (PHP 4)</a></li></ul></li></ul></div><br /><br />
<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=funcref&amp;redirect=http://www.php.net/manual/en/funcref.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=funcref&amp;redirect=http://www.php.net/manual/en/funcref.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Function Reference</strong>
 </div>
 <div class="note">There are no user contributed notes for this page.</div></div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/funcref.php">show source</a> |
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