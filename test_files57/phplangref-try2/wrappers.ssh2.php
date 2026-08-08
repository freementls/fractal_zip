<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: ssh2:// - Manual</title>
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
 <link rel="index" href="wrappers.php" />
 <link rel="prev" href="wrappers.phar.php" />
 <link rel="next" href="wrappers.rar.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/wrappers.ssh2" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/wrappers.ssh2.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/wrappers.ssh2.php" />
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
 <li class="header up"><a href="wrappers.php">Supported Protocols and Wrappers</a></li>
 <li><a href="wrappers.file.php">file://</a></li>
 <li><a href="wrappers.http.php">http://</a></li>
 <li><a href="wrappers.ftp.php">ftp://</a></li>
 <li><a href="wrappers.php.php">php://</a></li>
 <li><a href="wrappers.compression.php">zlib://</a></li>
 <li><a href="wrappers.data.php">data://</a></li>
 <li><a href="wrappers.glob.php">glob://</a></li>
 <li><a href="wrappers.phar.php">phar://</a></li>
 <li class="active"><a href="wrappers.ssh2.php">ssh2://</a></li>
 <li><a href="wrappers.rar.php">rar://</a></li>
 <li><a href="wrappers.audio.php">ogg://</a></li>
 <li><a href="wrappers.expect.php">expect://</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="wrappers.rar.php">rar://<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="wrappers.phar.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />phar://</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/wrappers.ssh2.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/wrappers.ssh2.php">Brazilian Portuguese</option>
    <option value="zh/wrappers.ssh2.php">Chinese (Simplified)</option>
    <option value="fr/wrappers.ssh2.php">French</option>
    <option value="de/wrappers.ssh2.php">German</option>
    <option value="ja/wrappers.ssh2.php">Japanese</option>
    <option value="pl/wrappers.ssh2.php">Polish</option>
    <option value="ro/wrappers.ssh2.php">Romanian</option>
    <option value="ru/wrappers.ssh2.php">Russian</option>
    <option value="fa/wrappers.ssh2.php">Persian</option>
    <option value="es/wrappers.ssh2.php">Spanish</option>
    <option value="tr/wrappers.ssh2.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="wrappers.ssh2" class="refentry">
 <div class="refnamediv">
  <h1 class="refname">ssh2://</h1>
  <p class="refpurpose"><span class="refname">ssh2://</span> &mdash; <span class="dc-title">Secure Shell 2</span></p>

 </div>

 <div class="refsect1 description" id="refsect1-wrappers.ssh2-description">
  <h3 class="title">Description</h3>
  <p class="para">
   <var class="filename">ssh2.shell://</var>
   <var class="filename">ssh2.exec://</var>
   <var class="filename">ssh2.tunnel://</var>
   <var class="filename">ssh2.sftp://</var>
   <var class="filename">ssh2.scp://</var>
   PHP 4.3.0 and up (PECL)
  </p>

  <blockquote class="note"><p><strong class="note">Note</strong>: 
   <strong>This wrapper is not enabled by default</strong><br />
   <span class="simpara">
    In order to use the <var class="filename">ssh2.*://</var> wrappers you must install
    the <a href="http://pecl.php.net/package/ssh2" class="link external">&raquo;&nbsp;SSH2</a> extension
    available from <a href="http://pecl.php.net/" class="link external">&raquo;&nbsp;PECL</a>.
   </span>
  </p></blockquote>

  <p class="simpara">
   In addition to accepting traditional URI login details, the ssh2 wrappers
   will also reuse open connections by passing the connection resource in the
   host portion of the URL.
  </p>
 </div>


 <div class="refsect1 usage" id="refsect1-wrappers.ssh2-usage"> 
  <h3 class="title">Options</h3>
  <ul class="itemizedlist">
   <li class="listitem"><span class="simpara"><var class="filename">ssh2.shell://user:pass@example.com:22/xterm</var></span></li>
   <li class="listitem"><span class="simpara"><var class="filename">ssh2.exec://user:pass@example.com:22/usr/local/bin/somecmd</var></span></li>
   <li class="listitem"><span class="simpara"><var class="filename">ssh2.tunnel://user:pass@example.com:22/192.168.0.1:14</var></span></li>
   <li class="listitem"><span class="simpara"><var class="filename">ssh2.sftp://user:pass@example.com:22/path/to/filename</var></span></li>
  </ul>
 </div>
 

 <div class="refsect1 options" id="refsect1-wrappers.ssh2-options">
  <h3 class="title">Options</h3>
  <p class="para">
   <table class="doctable table">
    <caption><strong>Wrapper Summary</strong></caption>
    
     <thead>
      <tr>
       <th>Attribute</th>
       <th>ssh2.shell</th>
       <th>ssh2.exec</th>
       <th>ssh2.tunnel</th>
       <th>ssh2.sftp</th>
       <th>ssh2.scp</th>
      </tr>

     </thead>

     <tbody class="tbody">
      <tr>
       <td>Restricted by <a href="filesystem.configuration.php#ini.allow-url-fopen" class="link">allow_url_fopen</a></td>
       <td>Yes</td>
       <td>Yes</td>
       <td>Yes</td>
       <td>Yes</td>
       <td>Yes</td>
      </tr>

      <tr>
       <td>Allows Reading</td>
       <td>Yes</td>
       <td>Yes</td>
       <td>Yes</td>
       <td>Yes</td>
       <td>Yes</td>
      </tr>

      <tr>
       <td>Allows Writing</td>
       <td>Yes</td>
       <td>Yes</td>
       <td>Yes</td>
       <td>Yes</td>
       <td>No</td>
      </tr>

      <tr>
       <td>Allows Appending</td>
       <td>No</td>
       <td>No</td>
       <td>No</td>
       <td>Yes (When supported by server)</td>
       <td>No</td>
      </tr>

      <tr>
       <td>Allows Simultaneous Reading and Writing</td>
       <td>Yes</td>
       <td>Yes</td>
       <td>Yes</td>
       <td>Yes</td>
       <td>No</td>
      </tr>

      <tr>
       <td>Supports  <span class="function"><a href="function.stat.php" class="function">stat()</a></span></td>
       <td>No</td>
       <td>No</td>
       <td>No</td>
       <td>Yes</td>
       <td>No</td>
      </tr>

      <tr>
       <td>Supports  <span class="function"><a href="function.unlink.php" class="function">unlink()</a></span></td>
       <td>No</td>
       <td>No</td>
       <td>No</td>
       <td>Yes</td>
       <td>No</td>
      </tr>

      <tr>
       <td>Supports  <span class="function"><a href="function.rename.php" class="function">rename()</a></span></td>
       <td>No</td>
       <td>No</td>
       <td>No</td>
       <td>Yes</td>
       <td>No</td>
      </tr>

      <tr>
       <td>Supports  <span class="function"><a href="function.mkdir.php" class="function">mkdir()</a></span></td>
       <td>No</td>
       <td>No</td>
       <td>No</td>
       <td>Yes</td>
       <td>No</td>
      </tr>

      <tr>
       <td>Supports  <span class="function"><a href="function.rmdir.php" class="function">rmdir()</a></span></td>
       <td>No</td>
       <td>No</td>
       <td>No</td>
       <td>Yes</td>
       <td>No</td>
      </tr>

     </tbody>
    
   </table>

  </p>


  
  <p class="para">
   <table class="doctable table">
    <caption><strong>Context options</strong></caption>
    
     <thead>
      <tr>
       <th>Name</th>
       <th>Usage</th>
       <th>Default</th>
      </tr>

     </thead>

     <tbody class="tbody">
      <tr>
       <td><em>session</em></td>
       <td>Preconnected ssh2 resource to be reused</td>
       <td class="empty">&nbsp;</td>
      </tr>

      <tr>
       <td><em>sftp</em></td>
       <td>Preallocated sftp resource to be reused</td>
       <td class="empty">&nbsp;</td>
      </tr>

      <tr>
       <td><em>methods</em></td>
       <td>Key exchange, hostkey, cipher, compression, and MAC methods to use</td>
       <td class="empty">&nbsp;</td>
      </tr>

      <tr>
       <td><em>callbacks</em></td>
       <td class="empty">&nbsp;</td>
       <td class="empty">&nbsp;</td>
      </tr>

      <tr>
       <td><em>username</em></td>
       <td>Username to connect as</td>
       <td class="empty">&nbsp;</td>
      </tr>

      <tr>
       <td><em>password</em></td>
       <td>Password to use with password authentication</td>
       <td class="empty">&nbsp;</td>
      </tr>

      <tr>
       <td><em>pubkey_file</em></td>
       <td>Name of public key file to use for authentication</td>
       <td class="empty">&nbsp;</td>
      </tr>

      <tr>
       <td><em>privkey_file</em></td>
       <td>Name of private key file to use for authentication</td>
       <td class="empty">&nbsp;</td>
      </tr>

      <tr>
       <td><em>env</em></td>
       <td>Associate array of environment variables to set</td>
       <td class="empty">&nbsp;</td>
      </tr>

      <tr>
       <td><em>term</em></td>
       <td>Terminal emulation type to request when allocating a pty</td>
       <td class="empty">&nbsp;</td>
      </tr>

      <tr>
       <td><em>term_width</em></td>
       <td>Width of terminal requested when allocating a pty</td>
       <td class="empty">&nbsp;</td>
      </tr>

      <tr>
       <td><em>term_height</em></td>
       <td>Height of terminal requested when allocating a pty</td>
       <td class="empty">&nbsp;</td>
      </tr>

      <tr>
       <td><em>term_units</em></td>
       <td>Units to use with term_width and term_height</td>
       <td><strong><code>SSH2_TERM_UNIT_CHARS</code></strong></td>
      </tr>

     </tbody>
    
   </table>

  </p>
 </div>
 

 <div class="refsect1 examples" id="refsect1-wrappers.ssh2-examples">
  <h3 class="title">Examples</h3>
  <div class="example" id="example-309">
   <p><strong>Example #1 Opening a stream from an active connection</strong></p>
   <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br />$session&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">ssh2_connect</span><span style="color: #007700">(</span><span style="color: #DD0000">'example.com'</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">22</span><span style="color: #007700">);<br /></span><span style="color: #0000BB">ssh2_auth_pubkey_file</span><span style="color: #007700">(</span><span style="color: #0000BB">$session</span><span style="color: #007700">,&nbsp;</span><span style="color: #DD0000">'username'</span><span style="color: #007700">,&nbsp;</span><span style="color: #DD0000">'/home/username/.ssh/id_rsa.pub'</span><span style="color: #007700">,<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #DD0000">'/home/username/.ssh/id_rsa'</span><span style="color: #007700">,&nbsp;</span><span style="color: #DD0000">'secret'</span><span style="color: #007700">);<br /></span><span style="color: #0000BB">$stream&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">fopen</span><span style="color: #007700">(</span><span style="color: #DD0000">"ssh2.tunnel://</span><span style="color: #0000BB">$session</span><span style="color: #DD0000">/remote.example.com:1234"</span><span style="color: #007700">,&nbsp;</span><span style="color: #DD0000">'r'</span><span style="color: #007700">);<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
   </div>

  </div>

 </div>


</div><br /><br />
<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=wrappers.ssh2&amp;redirect=http://www.php.net/manual/en/wrappers.ssh2.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=wrappers.ssh2&amp;redirect=http://www.php.net/manual/en/wrappers.ssh2.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>ssh2://</strong>
 </div><div id="allnotes">
 <a name="105756"></a>
 <div class="note">
  <strong class='user'>stm555 at hotmail dot com</strong>
  <a href="#105756" class="date">12-Sep-2011 12:41</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Note that using the resource inline in the uri does work, (ie, ssh2.s<a href="ftp://$session/path/to/file" rel="nofollow" target="_blank">ftp://$session/path/to/file</a>) but will (at least in PHP 5.2.5) throw a "supplied resource is not a valid SSH2 SFTP resource in .." warning on things like file_get_contents.</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=wrappers.ssh2&amp;redirect=http://www.php.net/manual/en/wrappers.ssh2.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=wrappers.ssh2&amp;redirect=http://www.php.net/manual/en/wrappers.ssh2.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/wrappers.ssh2.php">show source</a> |
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