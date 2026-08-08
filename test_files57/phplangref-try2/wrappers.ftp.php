<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: ftp:// - Manual</title>
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
 <link rel="prev" href="wrappers.http.php" />
 <link rel="next" href="wrappers.php.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/wrappers.ftp" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/wrappers.ftp.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/wrappers.ftp.php" />
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
 <li class="active"><a href="wrappers.ftp.php">ftp://</a></li>
 <li><a href="wrappers.php.php">php://</a></li>
 <li><a href="wrappers.compression.php">zlib://</a></li>
 <li><a href="wrappers.data.php">data://</a></li>
 <li><a href="wrappers.glob.php">glob://</a></li>
 <li><a href="wrappers.phar.php">phar://</a></li>
 <li><a href="wrappers.ssh2.php">ssh2://</a></li>
 <li><a href="wrappers.rar.php">rar://</a></li>
 <li><a href="wrappers.audio.php">ogg://</a></li>
 <li><a href="wrappers.expect.php">expect://</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="wrappers.php.php">php://<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="wrappers.http.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />http://</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/wrappers.ftp.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/wrappers.ftp.php">Brazilian Portuguese</option>
    <option value="zh/wrappers.ftp.php">Chinese (Simplified)</option>
    <option value="fr/wrappers.ftp.php">French</option>
    <option value="de/wrappers.ftp.php">German</option>
    <option value="ja/wrappers.ftp.php">Japanese</option>
    <option value="pl/wrappers.ftp.php">Polish</option>
    <option value="ro/wrappers.ftp.php">Romanian</option>
    <option value="ru/wrappers.ftp.php">Russian</option>
    <option value="fa/wrappers.ftp.php">Persian</option>
    <option value="es/wrappers.ftp.php">Spanish</option>
    <option value="tr/wrappers.ftp.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="wrappers.ftp" class="refentry">
 <div class="refnamediv">
  <h1 class="refname">ftp://</h1>
  <h1 class="refname">ftps://</h1>
  <p class="refpurpose"><span class="refname">ftp://</span> -- <span class="refname">ftps://</span> &mdash; <span class="dc-title">Accessing FTP(s) URLs</span></p>

 </div>

 <div class="refsect1 description" id="refsect1-wrappers.ftp-description">
  <h3 class="title">Description</h3>
  <p class="para">
   Allows read access to existing files and creation of new files
   via FTP.  If the server does not support passive mode ftp, the
   connection will fail.
  </p>
  <p class="simpara">
   You can open files for either reading or writing, but not both
   simultaneously.  If the remote file already exists on the ftp
   server and you attempt to open it for writing but have not specified
   the context option <em>overwrite</em>, the connection
   will fail.  If you need to overwrite existing files over ftp,
   specify the <em>overwrite</em> option in the context
   and open the file for writing.  Alternatively, you can
   use the <a href="ref.ftp.php" class="link">FTP extension</a>.
  </p>
  <p class="simpara">
   If you have set the <a href="filesystem.configuration.php#ini.from" class="link">from</a> directive
   in <var class="filename">php.ini</var>, then this value will be sent as the anonymous FTP
   password.
  </p>
 </div>


 <div class="refsect1 usage" id="refsect1-wrappers.ftp-usage"> 
  <h3 class="title">Options</h3>
  <ul class="itemizedlist">
   <li class="listitem"><span class="simpara"><var class="filename">ftp://example.com/pub/file.txt</var></span></li>
   <li class="listitem"><span class="simpara"><var class="filename">ftp://user:password@example.com/pub/file.txt</var></span></li>
   <li class="listitem"><span class="simpara"><var class="filename">ftps://example.com/pub/file.txt</var></span></li>
   <li class="listitem"><span class="simpara"><var class="filename">ftps://user:password@example.com/pub/file.txt</var></span></li>
  </ul>
 </div>
 

 <div class="refsect1 options" id="refsect1-wrappers.ftp-options">
  <h3 class="title">Options</h3>
  <p class="para">
   <table class="doctable table">
    <caption><strong>Wrapper Summary</strong></caption>
    
     <thead>
      <tr>
       <th>Attribute</th>
       <th>PHP 4</th>
       <th>PHP 5</th>
      </tr>

     </thead>

     <tbody class="tbody">
      <tr>
       <td>Restricted by <a href="filesystem.configuration.php#ini.allow-url-fopen" class="link">allow_url_fopen</a></td>
       <td>Yes</td>
       <td>Yes</td>
      </tr>

      <tr>
       <td>Allows Reading</td>
       <td>Yes</td>
       <td>Yes</td>
      </tr>

      <tr>
       <td>Allows Writing</td>
       <td>Yes (new files only)</td>
       <td>Yes (new files/existing files with <em><code class="parameter">overwrite</code></em>)</td>
      </tr>

      <tr>
       <td>Allows Appending</td>
       <td>No</td>
       <td>Yes</td>
      </tr>

      <tr>
       <td>Allows Simultaneous Reading and Writing</td>
       <td>No</td>
       <td>No</td>
      </tr>

      <tr>
       <td>Supports  <span class="function"><a href="function.stat.php" class="function">stat()</a></span></td>
       <td>No</td>
       <td>
        As of PHP 5.0.0:  <span class="function"><a href="function.filesize.php" class="function">filesize()</a></span>,
         <span class="function"><a href="function.filetype.php" class="function">filetype()</a></span>,  <span class="function"><a href="function.file-exists.php" class="function">file_exists()</a></span>,
         <span class="function"><a href="function.is-file.php" class="function">is_file()</a></span>, and  <span class="function"><a href="function.is-dir.php" class="function">is_dir()</a></span> elements only.
        As of PHP 5.1.0:  <span class="function"><a href="function.filemtime.php" class="function">filemtime()</a></span>.
       </td>
      </tr>

      <tr>
       <td>Supports  <span class="function"><a href="function.unlink.php" class="function">unlink()</a></span></td>
       <td>No</td>
       <td>Yes</td>
      </tr>

      <tr>
       <td>Supports  <span class="function"><a href="function.rename.php" class="function">rename()</a></span></td>
       <td>No</td>
       <td>Yes</td>
      </tr>

      <tr>
       <td>Supports  <span class="function"><a href="function.mkdir.php" class="function">mkdir()</a></span></td>
       <td>No</td>
       <td>Yes</td>
      </tr>

      <tr>
       <td>Supports  <span class="function"><a href="function.rmdir.php" class="function">rmdir()</a></span></td>
       <td>No</td>
       <td>Yes</td>
      </tr>

     </tbody>
    
   </table>

  </p>
 </div>
 

 <div class="refsect1 changelog" id="refsect1-wrappers.ftp-changelog">
  <h3 class="title">Changelog</h3>
  <p class="para">
   <table class="doctable informaltable">
    
     <thead>
      <tr>
       <th>Version</th>
       <th>Description</th>
      </tr>

     </thead>

     <tbody class="tbody">
      <tr>
       <td>4.3.0</td>
       <td>
        Added <em>ftps://</em>.
       </td>
      </tr>

     </tbody>
    
   </table>

  </p>
 </div>


 <div class="refsect1 notes" id="refsect1-wrappers.ftp-notes">
  <h3 class="title">Notes</h3>
  <blockquote class="note"><p><strong class="note">Note</strong>: 
   <p class="para">
    FTPS is only supported when the <a href="book.openssl.php" class="link">openssl</a>
    extension is enabled.
   </p>
   <span class="simpara">
    If the server does not support SSL, then the connection falls back
    to regular unencrypted ftp.
   </span>
  </p></blockquote>
  <blockquote class="note"><p><strong class="note">Note</strong>: 
   <strong>Appending</strong><br />
   <span class="simpara">
    As of PHP 5.0.0 files may be appended via the
    <em>ftp://</em> URL wrapper.  In prior versions, attempting
    to append to a file via <em>ftp://</em> will result in failure.
   </span>
  </p></blockquote>
 </div>


 <div class="refsect1 seealso" id="refsect1-wrappers.ftp-seealso">
  <h3 class="title">See Also</h3>
  <ul class="simplelist">
   <li class="member"><a href="context.ftp.php" class="xref">FTP context options</a></li>
  </ul>
 </div>


</div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="wrappers.php.php">php://<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="wrappers.http.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />http://</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/wrappers.ftp.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=wrappers.ftp&amp;redirect=http://www.php.net/manual/en/wrappers.ftp.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=wrappers.ftp&amp;redirect=http://www.php.net/manual/en/wrappers.ftp.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>ftp://</strong>
 </div><div id="allnotes">
 <a name="82794"></a>
 <div class="note">
  <strong class='user'>fazil dot stormhammer dot nospam at gmail dot com</strong>
  <a href="#82794" class="date">25-Apr-2008 01:41</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Document says "Allows read access to existing files and creation of new files via FTP. If the server does not support passive mode ftp, the connection will fail. "<br />
<br />
As of version 5.2.5 at least fopen("<a href="ftp://..." rel="nofollow" target="_blank">ftp://...</a>") uses an ACTIVE mode connection by default (it issues an FTP PORT command but not a PASV command).&nbsp; To force passive mode:<br />
<br />
$f = fopen("<a href="ftp://..." rel="nofollow" target="_blank">ftp://...</a>");<br />
ftp_pasv($f, true);</span>
</code></div>
  </div>
 </div>
 <a name="70269"></a>
 <div class="note">
  <strong class='user'>wlangdon at essex dot ac dot uk</strong>
  <a href="#70269" class="date">09-Oct-2006 09:32</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
old fashioned FTP servers may not be compatible with ftp_connect().</span>
</code></div>
  </div>
 </div>
 <a name="57168"></a>
 <div class="note">
  <a href="#57168" class="date">25-Sep-2005 08:33</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
&lt;?<br />
$str ="replace all contenents";<br />
$filew="<a href="ftp://gufo:gufo@192.168.1.55:21/jj.php" rel="nofollow" target="_blank">ftp://gufo:gufo@192.168.1.55:21/jj.php</a>";<br />
$opts = array('ftp' =&gt; array('overwrite' =&gt; true));<br />
$context = stream_context_create($opts);<br />
$strwri = file_put_contents($filew,$str,LOCK_EX,$context);<br />
?&gt;</span>
</code></div>
  </div>
 </div>
 <a name="43797"></a>
 <div class="note">
  <strong class='user'>php at f00n dot com</strong>
  <a href="#43797" class="date">04-Jul-2004 12:39</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
For Intranet purposes I found I preferred to move my file via ftp functions to match the session user's ftp account and put the file in a holding bay so I knew who it was from.<br />
<br />
The FTP wrapper method will NOT do this if your ftp server does NOT support passive mode.<br />
<br />
eg.&nbsp; an ftp server behind NAT/routing</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=wrappers.ftp&amp;redirect=http://www.php.net/manual/en/wrappers.ftp.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=wrappers.ftp&amp;redirect=http://www.php.net/manual/en/wrappers.ftp.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/wrappers.ftp.php">show source</a> |
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