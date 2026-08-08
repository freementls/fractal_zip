<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: CURL context options - Manual</title>
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
 <link rel="prev" href="context.ssl.php" />
 <link rel="next" href="context.phar.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/context.curl" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/context.curl.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/context.curl.php" />
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
 <li><a href="context.ftp.php">FTP context options</a></li>
 <li><a href="context.ssl.php">SSL context options</a></li>
 <li class="active"><a href="context.curl.php">CURL context options</a></li>
 <li><a href="context.phar.php">Phar context options</a></li>
 <li><a href="context.params.php">Context parameters</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="context.phar.php">Phar context options<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="context.ssl.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />SSL context options</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/context.curl.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/context.curl.php">Brazilian Portuguese</option>
    <option value="zh/context.curl.php">Chinese (Simplified)</option>
    <option value="fr/context.curl.php">French</option>
    <option value="de/context.curl.php">German</option>
    <option value="ja/context.curl.php">Japanese</option>
    <option value="pl/context.curl.php">Polish</option>
    <option value="ro/context.curl.php">Romanian</option>
    <option value="ru/context.curl.php">Russian</option>
    <option value="fa/context.curl.php">Persian</option>
    <option value="es/context.curl.php">Spanish</option>
    <option value="tr/context.curl.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="context.curl" class="refentry">
 <div class="refnamediv">
  <h1 class="refname">CURL context options</h1>
  <p class="refpurpose"><span class="refname">CURL context options</span> &mdash; <span class="dc-title">CURL context option listing</span></p>

 </div>
 
 <div class="refsect1 description" id="refsect1-context.curl-description">
  <h3 class="title">Description</h3>
  <p class="para">
   CURL context options are available when the
   <a href="intro.curl.php" class="link">CURL</a> extension was compiled using the
   <strong class="option unknown">--with-curlwrappers</strong>
 configure option.
  </p>
 </div>


 <div class="refsect1 options" id="refsect1-context.curl-options">
  <h3 class="title">Options</h3>
  <p class="para">
   <dl>

    <dt id="context.curl.method">
     <span class="term">
      <em><code class="parameter">method</code></em>
      <span class="type"><a href="language.types.string.php" class="type string">string</a></span>
     </span>
     <dd>

      <p class="para">
       <strong><code>GET</code></strong>, <strong><code>POST</code></strong>, or
       any other HTTP method supported by the remote server.
      </p>
      <p class="para">
       Defaults to <strong><code>GET</code></strong>.
      </p>
     </dd>

    </dt>

    <dt id="context.curl.header">
     <span class="term">
      <em><code class="parameter">header</code></em>
      <span class="type"><a href="language.types.string.php" class="type string">string</a></span>
     </span>
     <dd>

      <p class="para">
       Additional headers to be sent during request. Values
       in this option will override other values (such as
       <em>User-agent:</em>, <em>Host:</em>,
       and <em>Authentication:</em>).
      </p>
     </dd>

    </dt>

    <dt id="context.curl.user-agent">
     <span class="term">
      <em><code class="parameter">user_agent</code></em>
      <span class="type"><a href="language.types.string.php" class="type string">string</a></span>
     </span>
     <dd>

      <p class="para">
       Value to send with User-Agent: header.
      </p>
      <p class="para">
       By default the
       <a href="filesystem.configuration.php#ini.user-agent" class="link">user_agent</a>
       <var class="filename">php.ini</var> setting is used.
      </p>
     </dd>

    </dt>

    <dt id="context.curl.content">
     <span class="term">
      <em><code class="parameter">content</code></em>
      <span class="type"><a href="language.types.string.php" class="type string">string</a></span>
     </span>
     <dd>

      <p class="para">
       Additional data to be sent after the headers. This option is not used
       for <strong><code>GET</code></strong> or <strong><code>HEAD</code></strong> requests.
      </p>
     </dd>

    </dt>

    <dt id="context.curl.proxy">
     <span class="term">
      <em><code class="parameter">proxy</code></em>
      <span class="type"><a href="language.types.string.php" class="type string">string</a></span>
     </span>
     <dd>

      <p class="para">
       URI specifying address of proxy server. (e.g.
       <em>tcp://proxy.example.com:5100</em>).
      </p>
     </dd>

    </dt>

    <dt id="context.curl.max-redirects">
     <span class="term">
      <em><code class="parameter">max_redirects</code></em>
      <span class="type"><a href="language.types.integer.php" class="type integer">integer</a></span>
     </span>
     <dd>

      <p class="para">
       The max number of redirects to follow. Value <em>1</em> or
       less means that no redirects are followed.
      </p>
      <p class="para">
       Defaults to <em>20</em>.
      </p>
     </dd>

    </dt>

    <dt id="context.curl.curl-verify-ssl-host">
     <span class="term">
      <em><code class="parameter">curl_verify_ssl_host</code></em>
      <span class="type"><a href="language.types.boolean.php" class="type boolean">boolean</a></span>
     </span>
     <dd>

      <p class="para">
       Verify the host.
      </p>
      <p class="para">
       Defaults to <strong><code>FALSE</code></strong>
      </p>
      <blockquote class="note"><p><strong class="note">Note</strong>: 
       <p class="para">
        This option is available for both the http and ftp protocol wrappers.
       </p>
      </p></blockquote>
     </dd>

    </dt>

    <dt id="context.curl.curl-verify-ssl-peer">
     <span class="term">
      <em><code class="parameter">curl_verify_ssl_peer</code></em>
      <span class="type"><a href="language.types.boolean.php" class="type boolean">boolean</a></span>
     </span>
     <dd>

      <p class="para">
       Require verification of SSL certificate used.
      </p>
      <p class="para">
       Defaults to <strong><code>FALSE</code></strong>
      </p>
      <blockquote class="note"><p><strong class="note">Note</strong>: 
       <p class="para">
        This option is available for both the http and ftp protocol wrappers.
       </p>
      </p></blockquote>
     </dd>

    </dt>

   </dl>

  </p>
 </div>

 
 <div class="refsect1 examples" id="refsect1-context.curl-examples">
  <h3 class="title">Examples</h3>
  <p class="para">
   <div class="example" id="context.curl.example-post">
    <p><strong>Example #1 Fetch a page and send POST data</strong></p>
    <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /><br />$postdata&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">http_build_query</span><span style="color: #007700">(<br />&nbsp;&nbsp;&nbsp;&nbsp;array(<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #DD0000">'var1'&nbsp;</span><span style="color: #007700">=&gt;&nbsp;</span><span style="color: #DD0000">'some&nbsp;content'</span><span style="color: #007700">,<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #DD0000">'var2'&nbsp;</span><span style="color: #007700">=&gt;&nbsp;</span><span style="color: #DD0000">'doh'<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">)<br />);<br /><br /></span><span style="color: #0000BB">$opts&nbsp;</span><span style="color: #007700">=&nbsp;array(</span><span style="color: #DD0000">'http'&nbsp;</span><span style="color: #007700">=&gt;<br />&nbsp;&nbsp;&nbsp;&nbsp;array(<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #DD0000">'method'&nbsp;&nbsp;</span><span style="color: #007700">=&gt;&nbsp;</span><span style="color: #DD0000">'POST'</span><span style="color: #007700">,<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #DD0000">'header'&nbsp;&nbsp;</span><span style="color: #007700">=&gt;&nbsp;</span><span style="color: #DD0000">'Content-type:&nbsp;application/x-www-form-urlencoded'</span><span style="color: #007700">,<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #DD0000">'content'&nbsp;</span><span style="color: #007700">=&gt;&nbsp;</span><span style="color: #0000BB">$postdata<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">)<br />);<br /><br /></span><span style="color: #0000BB">$context&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">stream_context_create</span><span style="color: #007700">(</span><span style="color: #0000BB">$opts</span><span style="color: #007700">);<br /><br /></span><span style="color: #0000BB">$result&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">file_get_contents</span><span style="color: #007700">(</span><span style="color: #DD0000">'http://example.com/submit.php'</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">false</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$context</span><span style="color: #007700">);<br /><br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

   </div>
  </p>
 </div>


 <div class="refsect1 seealso" id="refsect1-context.curl-seealso">
  <h3 class="title">See Also</h3>
  <p class="para">
   <ul class="simplelist">
    <li class="member"><a href="context.socket.php" class="xref">Socket context options</a></li>
   </ul>
  </p>
 </div>


</div><br /><br />
<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=context.curl&amp;redirect=http://www.php.net/manual/en/context.curl.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=context.curl&amp;redirect=http://www.php.net/manual/en/context.curl.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>CURL context options</strong>
 </div><div id="allnotes">
 <a name="85551"></a>
 <div class="note">
  <strong class='user'>patrick dot allaert at gmail dot com</strong>
  <a href="#85551" class="date">05-Sep-2008 04:48</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
In PHP 5.2.6, the header option requires an array and not a string if PHP is built with --with-curlwrappers<br />
<br />
Patrick Allaert<br />
<a href="http://patrickallaert.blogspot.com/" rel="nofollow" target="_blank">http://patrickallaert.blogspot.com/</a><br />
<br />
EDIT: In PHP 5.2.10 both string or an array are accepted. // Jani</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=context.curl&amp;redirect=http://www.php.net/manual/en/context.curl.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=context.curl&amp;redirect=http://www.php.net/manual/en/context.curl.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/context.curl.php">show source</a> |
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