<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: FTP context options - Manual</title>
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
 <link rel="index" href="context.php" />
 <link rel="prev" href="context.http.php" />
 <link rel="next" href="context.ssl.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/context.ftp" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/context.ftp.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/context.ftp.php" />
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
 <li class="header up"><a href="context.php">Context options and parameters</a></li>
 <li><a href="context.socket.php">Socket context options</a></li>
 <li><a href="context.http.php">HTTP context options</a></li>
 <li class="active"><a href="context.ftp.php">FTP context options</a></li>
 <li><a href="context.ssl.php">SSL context options</a></li>
 <li><a href="context.curl.php">CURL context options</a></li>
 <li><a href="context.phar.php">Phar context options</a></li>
 <li><a href="context.params.php">Context parameters</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="context.ssl.php">SSL context options<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="context.http.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />HTTP context options</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/context.ftp.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/context.ftp.php">Brazilian Portuguese</option>
    <option value="zh/context.ftp.php">Chinese (Simplified)</option>
    <option value="fr/context.ftp.php">French</option>
    <option value="de/context.ftp.php">German</option>
    <option value="ja/context.ftp.php">Japanese</option>
    <option value="pl/context.ftp.php">Polish</option>
    <option value="ro/context.ftp.php">Romanian</option>
    <option value="ru/context.ftp.php">Russian</option>
    <option value="fa/context.ftp.php">Persian</option>
    <option value="es/context.ftp.php">Spanish</option>
    <option value="tr/context.ftp.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="context.ftp" class="refentry">
 <div class="refnamediv">
  <h1 class="refname">FTP context options</h1>
  <p class="refpurpose"><span class="refname">FTP context options</span> &mdash; <span class="dc-title">FTP context option listing</span></p>

 </div>
 
 <div class="refsect1 description" id="refsect1-context.ftp-description">
  <h3 class="title">Description</h3>
  <p class="para">
   Context options for <em>ftp://</em> and <em>ftps://</em>
   transports.
  </p>
 </div>


 <div class="refsect1 options" id="refsect1-context.ftp-options">
  <h3 class="title">Options</h3>
  <p class="para">
   <dl>

    <dt id="context.ftp.overwrite">
     <span class="term">
      <em><code class="parameter">overwrite</code></em>
      <span class="type"><a href="language.types.boolean.php" class="type boolean">boolean</a></span>
     </span>
     <dd>

      <p class="para">
       Allow overwriting of already existing files on remote server.
       Applies to write mode (uploading) only.
      </p>
      <p class="para">
       Defaults to <strong><code>FALSE</code></strong>.
      </p>
     </dd>

    </dt>

    <dt id="context.ftp.resume-pos">
     <span class="term">
      <em><code class="parameter">resume_pos</code></em>
      <span class="type"><a href="language.types.integer.php" class="type integer">integer</a></span>
     </span>
     <dd>

      <p class="para">
       File offset at which to begin transfer. Applies to read mode (downloading) only.
      </p>
      <p class="para">
       Defaults to <em>0</em> (Beginning of File).
      </p>
     </dd>

    </dt>

    <dt id="context.ftp.proxy">
     <span class="term">
      <em><code class="parameter">proxy</code></em>
      <span class="type"><a href="language.types.string.php" class="type string">string</a></span>
     </span>
     <dd>

      <p class="para">
       Proxy FTP request via http proxy server. Applies to file read
       operations only. Ex: <em>tcp://squid.example.com:8000</em>.
      </p>
     </dd>

    </dt>

   </dl>

  </p>
 </div>

 
 <div class="refsect1 changelog" id="refsect1-context.ftp-changelog">
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
       <td>5.1.0</td>
       <td>
        Added <em><code class="parameter">proxy</code></em>.
       </td>
      </tr>

      <tr>
       <td>5.0.0</td>
       <td>
        Added <em><code class="parameter">overwrite</code></em> and <em><code class="parameter">resume_pos</code></em>.
       </td>
      </tr>

     </tbody>
    
   </table>

  </p>
 </div>


 <div class="refsect1 notes" id="refsect1-context.ftp-notes">
  <h3 class="title">Notes</h3>
  <blockquote class="note"><p><strong class="note">Note</strong>: 
   <strong>Underlying socket stream context options</strong><br />
   <span class="simpara">
    Additional context options may be supported by the
    <a href="transports.inet.php" class="link">underlying transport</a>
    For <em>ftp://</em> streams, refer to context
    options for the <em>tcp://</em> transport.  For
    <em>ftps://</em> streams, refer to context options
    for the <em>ssl://</em> transport.
   </span>
  </p></blockquote>
 </div>


 <div class="refsect1 seealso" id="refsect1-context.ftp-seealso">
  <h3 class="title">See Also</h3>
  <p class="para">
   <ul class="simplelist">
    <li class="member"><a href="wrappers.ftp.php" class="xref">ftp://</a></li>
    <li class="member"><a href="context.socket.php" class="xref">Socket context options</a></li>
    <li class="member"><a href="context.ssl.php" class="xref">SSL context options</a></li>
   </ul>
  </p>
 </div>


</div><br /><br />
<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=context.ftp&amp;redirect=http://www.php.net/manual/en/context.ftp.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=context.ftp&amp;redirect=http://www.php.net/manual/en/context.ftp.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>FTP context options</strong>
 </div><div id="allnotes">
 <a name="90313"></a>
 <div class="note">
  <strong class='user'>php dot net at misterchucker dot com</strong>
  <a href="#90313" class="date">15-Apr-2009 06:19</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
This is an example of how to allow fopen() to overwrite a file on an FTP site. If the stream context is not modified, an error will occur: "...failed to open stream: Remote file already exists and overwrite context option not specified...".<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="comment">// The path to the FTP file, including login arguments<br />
</span><span class="default">$ftp_path </span><span class="keyword">= </span><span class="string">'<a href="ftp://username:password@example.com/example.txt" rel="nofollow" target="_blank">ftp://username:password@example.com/example.txt</a>'</span><span class="keyword">;<br />
<br />
</span><span class="comment">// Allows overwriting of existing files on the remote FTP server<br />
</span><span class="default">$stream_options </span><span class="keyword">= array(</span><span class="string">'ftp' </span><span class="keyword">=&gt; array(</span><span class="string">'overwrite' </span><span class="keyword">=&gt; </span><span class="default">true</span><span class="keyword">));<br />
<br />
</span><span class="comment">// Creates a stream context resource with the defined options<br />
</span><span class="default">$stream_context </span><span class="keyword">= </span><span class="default">stream_context_create</span><span class="keyword">(</span><span class="default">$stream_options</span><span class="keyword">);<br />
<br />
</span><span class="comment">// Opens the file for writing and truncates it to zero length <br />
</span><span class="keyword">if (</span><span class="default">$fh </span><span class="keyword">= </span><span class="default">fopen</span><span class="keyword">(</span><span class="default">$ftp_path</span><span class="keyword">, </span><span class="string">'w'</span><span class="keyword">, </span><span class="default">0</span><span class="keyword">, </span><span class="default">$stream_context</span><span class="keyword">))<br />
{<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">// Writes contents to the file<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">fputs</span><span class="keyword">(</span><span class="default">$fh</span><span class="keyword">, </span><span class="string">'example contents'</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">// Closes the file handle<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">fclose</span><span class="keyword">(</span><span class="default">$fh</span><span class="keyword">);<br />
}<br />
else<br />
{<br />
&nbsp;&nbsp;&nbsp; die(</span><span class="string">'Could not open file.'</span><span class="keyword">);<br />
}<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=context.ftp&amp;redirect=http://www.php.net/manual/en/context.ftp.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=context.ftp&amp;redirect=http://www.php.net/manual/en/context.ftp.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/context.ftp.php">show source</a> |
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