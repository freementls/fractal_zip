<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Socket context options - Manual</title>
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
 <link rel="prev" href="context.php" />
 <link rel="next" href="context.http.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/context.socket" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/context.socket.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/context.socket.php" />
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
 <li class="active"><a href="context.socket.php">Socket context options</a></li>
 <li><a href="context.http.php">HTTP context options</a></li>
 <li><a href="context.ftp.php">FTP context options</a></li>
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
  <a href="context.http.php">HTTP context options<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="context.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Context options and parameters</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/context.socket.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/context.socket.php">Brazilian Portuguese</option>
    <option value="zh/context.socket.php">Chinese (Simplified)</option>
    <option value="fr/context.socket.php">French</option>
    <option value="de/context.socket.php">German</option>
    <option value="ja/context.socket.php">Japanese</option>
    <option value="pl/context.socket.php">Polish</option>
    <option value="ro/context.socket.php">Romanian</option>
    <option value="ru/context.socket.php">Russian</option>
    <option value="fa/context.socket.php">Persian</option>
    <option value="es/context.socket.php">Spanish</option>
    <option value="tr/context.socket.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="context.socket" class="refentry">
 <div class="refnamediv">
  <h1 class="refname">Socket context options</h1>
  <p class="refpurpose"><span class="refname">Socket context options</span> &mdash; <span class="dc-title">Socket context option listing</span></p>

 </div>
 
 <div class="refsect1 description" id="refsect1-context.socket-description">
  <h3 class="title">Description</h3>
  <p class="para">
   Socket context options are available for all wrappers that work over
   sockets, like <em>tcp</em>, <em>http</em> and
   <em>ftp</em>.
  </p>
 </div>

 
 <div class="refsect1 options" id="refsect1-context.socket-options">
  <h3 class="title">Options</h3>
  <p class="para">
   <dl>

    <dt id="context.socket.bindto">
     <span class="term"><em><code class="parameter">bindto</code></em></span>
     <dd>

      <p class="para">
       Used to specify the IP address (either IPv4 or IPv6) and/or the
       port number that PHP will use to access the network. The syntax
       is <em>ip:port</em>.
       Setting the IP or the port to <em>0</em> will let the system
       choose the IP and/or port.
      </p>
      <blockquote class="note"><p><strong class="note">Note</strong>: 
       <p class="para">
        As FTP creates two socket connections during normal operation,
        the port number cannot be specified using this option.
       </p>
      </p></blockquote>
     </dd>

    </dt>

    <dt id="context.socket.backlog">
     <span class="term"><em><code class="parameter">backlog</code></em></span>
     <dd>

      <p class="para">
       Used to  limit  the  number  of  outstanding  connections  in  the
       socket&#039;s  listen  queue.
      </p>
      <blockquote class="note"><p><strong class="note">Note</strong>: 
       <p class="para">
        This is only applicable to  <span class="function"><a href="function.stream-socket-server.php" class="function">stream_socket_server()</a></span>.
       </p>
      </p></blockquote>
     </dd>

    </dt>

   </dl>

  </p>
 </div>

 
 <div class="refsect1 changelog" id="refsect1-context.socket-changelog">
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
        Added <em>bindto</em>.
       </td>
      </tr>

      <tr>
       <td>5.3.3</td>
       <td>
        Added <em>backlog</em>.
       </td>
      </tr>

     </tbody>
    
   </table>

  </p>
 </div>

 
 <div class="refsect1 examples" id="refsect1-context.socket-examples">
  <h3 class="title">Examples</h3>
  <p class="para">
   <div class="example" id="context.socket.example-bindto">
    <p><strong>Example #1 Basic <em><code class="parameter">bindto</code></em> usage example</strong></p>
    <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #FF8000">//&nbsp;connect&nbsp;to&nbsp;the&nbsp;internet&nbsp;using&nbsp;the&nbsp;'192.168.0.100'&nbsp;IP<br /></span><span style="color: #0000BB">$opts&nbsp;</span><span style="color: #007700">=&nbsp;array(<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #DD0000">'socket'&nbsp;</span><span style="color: #007700">=&gt;&nbsp;array(<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #DD0000">'bindto'&nbsp;</span><span style="color: #007700">=&gt;&nbsp;</span><span style="color: #DD0000">'192.168.0.100:0'</span><span style="color: #007700">,<br />&nbsp;&nbsp;&nbsp;&nbsp;),<br />);<br /><br /><br /></span><span style="color: #FF8000">//&nbsp;connect&nbsp;to&nbsp;the&nbsp;internet&nbsp;using&nbsp;the&nbsp;'192.168.0.100'&nbsp;IP&nbsp;and&nbsp;port&nbsp;'7000'<br /></span><span style="color: #0000BB">$opts&nbsp;</span><span style="color: #007700">=&nbsp;array(<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #DD0000">'socket'&nbsp;</span><span style="color: #007700">=&gt;&nbsp;array(<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #DD0000">'bindto'&nbsp;</span><span style="color: #007700">=&gt;&nbsp;</span><span style="color: #DD0000">'192.168.0.100:7000'</span><span style="color: #007700">,<br />&nbsp;&nbsp;&nbsp;&nbsp;),<br />);<br /><br /><br /></span><span style="color: #FF8000">//&nbsp;connect&nbsp;to&nbsp;the&nbsp;internet&nbsp;using&nbsp;port&nbsp;'7000'<br /></span><span style="color: #0000BB">$opts&nbsp;</span><span style="color: #007700">=&nbsp;array(<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #DD0000">'socket'&nbsp;</span><span style="color: #007700">=&gt;&nbsp;array(<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #DD0000">'bindto'&nbsp;</span><span style="color: #007700">=&gt;&nbsp;</span><span style="color: #DD0000">'0:7000'</span><span style="color: #007700">,<br />&nbsp;&nbsp;&nbsp;&nbsp;),<br />);<br /><br /><br /></span><span style="color: #FF8000">//&nbsp;create&nbsp;the&nbsp;context...<br /></span><span style="color: #0000BB">$context&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">stream_context_create</span><span style="color: #007700">(</span><span style="color: #0000BB">$opts</span><span style="color: #007700">);<br /><br /></span><span style="color: #FF8000">//&nbsp;...and&nbsp;use&nbsp;it&nbsp;to&nbsp;fetch&nbsp;the&nbsp;data<br /></span><span style="color: #007700">echo&nbsp;</span><span style="color: #0000BB">file_get_contents</span><span style="color: #007700">(</span><span style="color: #DD0000">'http://www.example.com'</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">false</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$context</span><span style="color: #007700">);<br /><br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

   </div>
  </p>
 </div>

 
</div><br /><br />
<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=context.socket&amp;redirect=http://www.php.net/manual/en/context.socket.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=context.socket&amp;redirect=http://www.php.net/manual/en/context.socket.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Socket context options</strong>
 </div>
 <div class="note">There are no user contributed notes for this page.</div></div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/context.socket.php">show source</a> |
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