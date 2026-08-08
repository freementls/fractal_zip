<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: SSL context options - Manual</title>
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
 <link rel="prev" href="context.ftp.php" />
 <link rel="next" href="context.curl.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/context.ssl" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/context.ssl.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/context.ssl.php" />
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
 <li class="active"><a href="context.ssl.php">SSL context options</a></li>
 <li><a href="context.curl.php">CURL context options</a></li>
 <li><a href="context.phar.php">Phar context options</a></li>
 <li><a href="context.params.php">Context parameters</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="context.curl.php">CURL context options<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="context.ftp.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />FTP context options</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/context.ssl.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/context.ssl.php">Brazilian Portuguese</option>
    <option value="zh/context.ssl.php">Chinese (Simplified)</option>
    <option value="fr/context.ssl.php">French</option>
    <option value="de/context.ssl.php">German</option>
    <option value="ja/context.ssl.php">Japanese</option>
    <option value="pl/context.ssl.php">Polish</option>
    <option value="ro/context.ssl.php">Romanian</option>
    <option value="ru/context.ssl.php">Russian</option>
    <option value="fa/context.ssl.php">Persian</option>
    <option value="es/context.ssl.php">Spanish</option>
    <option value="tr/context.ssl.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="context.ssl" class="refentry">
 <div class="refnamediv">
  <h1 class="refname">SSL context options</h1>
  <p class="refpurpose"><span class="refname">SSL context options</span> &mdash; <span class="dc-title">SSL context option listing</span></p>

 </div>
 
 <div class="refsect1 description" id="refsect1-context.ssl-description">
  <h3 class="title">Description</h3>
  <p class="para">
   Context options for <em>ssl://</em> and <em>tls://</em>
   transports.
  </p>
 </div>


 <div class="refsect1 options" id="refsect1-context.ssl-options">
  <h3 class="title">Options</h3>
  <p class="para">
   <dl>

    <dt id="context.ssl.verify-peer">
     <span class="term">
      <em><code class="parameter">verify_peer</code></em>
      <span class="type"><a href="language.types.boolean.php" class="type boolean">boolean</a></span>
     </span>
     <dd>

      <p class="para">
       Require verification of SSL certificate used.
      </p>
      <p class="para">
       Defaults to <strong><code>FALSE</code></strong>.
      </p>
     </dd>

    </dt>

    <dt id="context.ssl.allow-self-signed">
     <span class="term">
      <em><code class="parameter">allow_self_signed</code></em>
      <span class="type"><a href="language.types.boolean.php" class="type boolean">boolean</a></span>
     </span>
     <dd>

      <p class="para">
       Allow self-signed certificates. Requires
       <a href="context.ssl.php#context.ssl.verify-peer" class="link"><em><code class="parameter">verify_peer</code></em></a>.
      </p>
      <p class="para">
       Defaults to <strong><code>FALSE</code></strong>
      </p>
     </dd>

    </dt>

    <dt id="context.ssl.cafile">
     <span class="term">
      <em><code class="parameter">cafile</code></em>
      <span class="type"><a href="language.types.string.php" class="type string">string</a></span>
     </span>
     <dd>

      <p class="para">
       Location of Certificate Authority file on local filesystem
       which should be used with the <em>verify_peer</em>
       context option to authenticate the identity of the remote peer.
      </p>
     </dd>

    </dt>

    <dt id="context.ssl.capath">
     <span class="term">
      <em><code class="parameter">capath</code></em>
      <span class="type"><a href="language.types.string.php" class="type string">string</a></span>
     </span>
     <dd>

      <p class="para">
       If <em>cafile</em> is not specified or if the certificate
       is not found there, the directory pointed to by <em>capath</em> 
       is searched for a suitable certificate.  <em>capath</em>
       must be a correctly hashed certificate directory.
      </p>
     </dd>

    </dt>

    <dt id="context.ssl.local-cert">
     <span class="term">
      <em><code class="parameter">local_cert</code></em>
      <span class="type"><a href="language.types.string.php" class="type string">string</a></span>
     </span>
     <dd>

      <p class="para">
       Path to local certificate file on filesystem.  It must be a PEM
       encoded file which contains your certificate and private key.
       It can optionally contain the certificate chain of issuers.
      </p>
     </dd>

    </dt>

    <dt id="context.ssl.passphrase">
     <span class="term">
      <em><code class="parameter">passphrase</code></em>
      <span class="type"><a href="language.types.string.php" class="type string">string</a></span>
     </span>
     <dd>

      <p class="para">
       Passphrase with which your <em>local_cert</em> file
       was encoded.
      </p>
     </dd>

    </dt>

    <dt id="context.ssl.cn-match">
     <span class="term">
      <em><code class="parameter">CN_match</code></em>
      <span class="type"><a href="language.types.string.php" class="type string">string</a></span>
     </span>
     <dd>

      <p class="para">
       Common Name we are expecting.  PHP will perform limited wildcard
       matching.  If the Common Name does not match this, the connection
       attempt will fail.
      </p>
     </dd>

    </dt>

    <dt id="context.ssl.verify-depth">
     <span class="term">
      <em><code class="parameter">verify_depth</code></em>
      <span class="type"><a href="language.types.integer.php" class="type integer">integer</a></span>
     </span>
     <dd>

      <p class="para">
       Abort if the certificate chain is too deep.
      </p>
      <p class="para">
       Defaults to no verification.
      </p>
     </dd>

    </dt>

    <dt id="context.ssl.ciphers">
     <span class="term">
      <em><code class="parameter">ciphers</code></em>
      <span class="type"><a href="language.types.string.php" class="type string">string</a></span>
     </span>
     <dd>

      <p class="para">
       Sets the list of available ciphers. The format of the string is described
       in <a href="http://www.openssl.org/docs/apps/ciphers.html#CIPHER_LIST_FORMAT" class="link external">&raquo;&nbsp;ciphers(1)</a>.
      </p>
      <p class="para">
       Defaults to <em>DEFAULT</em>.
      </p>
     </dd>

    </dt>

    <dt id="context.ssl.capture-peer-cert">
     <span class="term">
      <em><code class="parameter">capture_peer_cert</code></em>
      <span class="type"><a href="language.types.boolean.php" class="type boolean">boolean</a></span>
     </span>
     <dd>

      <p class="para">
       If set to <strong><code>TRUE</code></strong> a <em>peer_certificate</em> context option
       will be created containing the peer certificate.
      </p>
     </dd>

    </dt>

    <dt id="context.ssl.capture-peer-cert-chain">
     <span class="term">
      <em><code class="parameter">capture_peer_cert_chain</code></em>
      <span class="type"><a href="language.types.boolean.php" class="type boolean">boolean</a></span>
     </span>
     <dd>

      <p class="para">
       If set to <strong><code>TRUE</code></strong> a <em>peer_certificate_chain</em> context
       option will be created containing the certificate chain.
      </p>
     </dd>

    </dt>

    <dt id="context.ssl.sni-enabled">
     <span class="term">
      <em><code class="parameter">SNI_enabled</code></em>
      <span class="type"><a href="language.types.boolean.php" class="type boolean">boolean</a></span>
     </span>
     <dd>

      <p class="para">
       If set to <strong><code>TRUE</code></strong> server name indication will be enabled. Enabling SNI 
       allows multiple certificates on the same IP address.
      </p>
     </dd>

    </dt>

    <dt id="context.ssl.sni-server-name">
     <span class="term">
      <em><code class="parameter">SNI_server_name</code></em>
      <span class="type"><a href="language.types.string.php" class="type string">string</a></span>
     </span>
     <dd>

      <p class="para">
       If set, then this value will be used as server name for server name 
       indication. If this value is not set, then the server name is guessed 
       based on the hostname used when opening the stream.
      </p>
     </dd>

    </dt>

   </dl>

  </p>
 </div>

 
 <div class="refsect1 changelog" id="refsect1-context.ssl-changelog">
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
       <td>5.3.2</td>
       <td>
        Added <em><code class="parameter">SNI_enabled</code></em> and
        <em><code class="parameter">SNI_server_name</code></em>.
       </td>
      </tr>

      <tr>
       <td>5.0.0</td>
       <td>
        Added <em><code class="parameter">capture_peer_cert</code></em>,
        <em><code class="parameter">capture_peer_chain</code></em> and
        <em><code class="parameter">ciphers</code></em>.
       </td>
      </tr>

     </tbody>
    
   </table>

  </p>
 </div>


 <div class="refsect1 notes" id="refsect1-context.ssl-notes">
  <h3 class="title">Notes</h3>
  <blockquote class="note"><p><strong class="note">Note</strong>: 
   <span class="simpara">
    Because <em>ssl://</em> is the underlying transport for the
    <a href="wrappers.http.php" class="link"><em>https://</em></a> and
    <a href="wrappers.ftp.php" class="link"><em>ftps://</em></a> wrappers, 
    any context options which apply to <em>ssl://</em> also apply to
    <em>https://</em> and <em>ftps://</em>.
   </span>
  </p></blockquote>
  <blockquote class="note"><p><strong class="note">Note</strong>: 
   <span class="simpara">
    For SNI (Server Name Indication) to be available, then PHP must be compiled 
    with OpenSSL 0.9.8j or greater. Use the 
    <strong><code>OPENSSL_TLSEXT_SERVER_NAME</code></strong> to determine whether SNI is 
    supported.
   </span>
  </p></blockquote>
 </div>


 <div class="refsect1 seealso" id="refsect1-context.ssl-seealso">
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
  <span class="action"><a href="/manual/add-note.php?sect=context.ssl&amp;redirect=http://www.php.net/manual/en/context.ssl.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=context.ssl&amp;redirect=http://www.php.net/manual/en/context.ssl.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>SSL context options</strong>
 </div><div id="allnotes">
 <a name="96328"></a>
 <div class="note">
  <strong class='user'>Botjan kufca</strong>
  <a href="#96328" class="date">20-Feb-2010 11:11</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
CN_match works contrary to intuitive thinking. I came across this when I was developing SSL server implemented in PHP. I stated (in code): <br />
<br />
- do not allow self signed certs (works)<br />
- verify peer certs against CA cert (works)<br />
- verify the client's CN against CN_match (does not work), like this:<br />
<br />
stream_context_set_option($context, 'ssl', 'CN_match', '*.example.org');<br />
<br />
I presumed this would match any client with CN below .example.org domain.<br />
Unfortunately this is NOT the case. The option above does not do that.<br />
<br />
What it really does is this:<br />
- it takes client's CN and compares it to CN_match<br />
- IF CLIENT's CN CONTAINS AN ASTERISK like *.example.org, then it is matched against CN_match in wildcard matching fashion<br />
<br />
Examples to illustrate behaviour:<br />
(CNM = server's CN_match)<br />
(CCN = client's CN)<br />
<br />
- CNM=host.example.org, CCN=host.example.org ---&gt; OK<br />
- CNM=host.example.org, CCN=*.example.org ---&gt; OK<br />
- CNM=.example.org, CCN=*.example.org ---&gt; OK<br />
- CNM=example.org, CCN=*.example.org ---&gt; ERROR<br />
<br />
- CNM=*.example.org, CCN=host.example.org ---&gt; ERROR<br />
- CNM=*.example.org, CCN=*.example.org ---&gt; OK<br />
<br />
According to PHP sources I believe that the same applies if you are trying to act as Client and the server contains a wildcard certificate. If you set CN_match to myserver.example.org and server presents itself with *.example.org, the connection is allowed.<br />
<br />
Everything above applies to PHP version 5.2.12.<br />
I will supply a patch to support CN_match starting with asterisk.</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=context.ssl&amp;redirect=http://www.php.net/manual/en/context.ssl.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=context.ssl&amp;redirect=http://www.php.net/manual/en/context.ssl.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/context.ssl.php">show source</a> |
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