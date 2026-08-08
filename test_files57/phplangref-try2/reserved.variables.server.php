<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: $_SERVER - Manual</title>
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
 <link rel="index" href="reserved.variables.php" />
 <link rel="prev" href="reserved.variables.globals.php" />
 <link rel="next" href="reserved.variables.get.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/reserved.variables.server" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/reserved.variables.server.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/reserved.variables.server.php" />
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
 <li class="header up"><a href="reserved.variables.php">Predefined Variables</a></li>
 <li><a href="language.variables.superglobals.php">Superglobals</a></li>
 <li><a href="reserved.variables.globals.php">$GLOBALS</a></li>
 <li class="active"><a href="reserved.variables.server.php">$_SERVER</a></li>
 <li><a href="reserved.variables.get.php">$_GET</a></li>
 <li><a href="reserved.variables.post.php">$_POST</a></li>
 <li><a href="reserved.variables.files.php">$_FILES</a></li>
 <li><a href="reserved.variables.request.php">$_REQUEST</a></li>
 <li><a href="reserved.variables.session.php">$_SESSION</a></li>
 <li><a href="reserved.variables.environment.php">$_ENV</a></li>
 <li><a href="reserved.variables.cookies.php">$_COOKIE</a></li>
 <li><a href="reserved.variables.phperrormsg.php">$php_errormsg</a></li>
 <li><a href="reserved.variables.httprawpostdata.php">$HTTP_RAW_POST_DATA</a></li>
 <li><a href="reserved.variables.httpresponseheader.php">$http_response_header</a></li>
 <li><a href="reserved.variables.argc.php">$argc</a></li>
 <li><a href="reserved.variables.argv.php">$argv</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="reserved.variables.get.php">$_GET<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="reserved.variables.globals.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />$GLOBALS</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/reserved.variables.server.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/reserved.variables.server.php">Brazilian Portuguese</option>
    <option value="zh/reserved.variables.server.php">Chinese (Simplified)</option>
    <option value="fr/reserved.variables.server.php">French</option>
    <option value="de/reserved.variables.server.php">German</option>
    <option value="ja/reserved.variables.server.php">Japanese</option>
    <option value="pl/reserved.variables.server.php">Polish</option>
    <option value="ro/reserved.variables.server.php">Romanian</option>
    <option value="ru/reserved.variables.server.php">Russian</option>
    <option value="fa/reserved.variables.server.php">Persian</option>
    <option value="es/reserved.variables.server.php">Spanish</option>
    <option value="tr/reserved.variables.server.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="reserved.variables.server" class="refentry">
 <div class="refnamediv">
  <h1 class="refname">$_SERVER</h1>
  <h1 class="refname">$HTTP_SERVER_VARS [deprecated]</h1>
  <p class="verinfo">(PHP 4 &gt;= 4.1.0, PHP 5)</p><p class="refpurpose"><span class="refname">$_SERVER</span> -- <span class="refname">$HTTP_SERVER_VARS [deprecated]</span> &mdash; <span class="dc-title">Server and execution environment information</span></p>

 </div>
 
 <div class="refsect1 description" id="refsect1-reserved.variables.server-description">
  <h3 class="title">Description</h3>
  <p class="para">
   <var class="varname"><var class="varname">$_SERVER</var></var> is an array containing information
   such as headers, paths, and script locations. The entries in this
   array are created by the web server. There is no guarantee that
   every web server will provide any of these; servers may omit some,
   or provide others not listed here. That said, a large number of
   these variables are accounted for in the <a href="http://www.faqs.org/rfcs/rfc3875" class="link external">&raquo;&nbsp;CGI/1.1 specification</a>, so you should
   be able to expect those.
  </p>

  <p class="simpara">
   <var class="varname"><var class="varname">$HTTP_SERVER_VARS</var></var> contains the same initial
   information, but is not a <a href="language.variables.superglobals.php" class="link">superglobal</a>.
   (Note that <var class="varname"><var class="varname">$HTTP_SERVER_VARS</var></var> and <var class="varname"><var class="varname">$_SERVER</var></var>
   are different variables and that PHP handles them as such)
  </p>
 </div>


 <div class="refsect1 indices" id="refsect1-reserved.variables.server-indices">
  <h3 class="title">Indices</h3>

  <p class="simpara">
   You may or may not find any of the following elements in
   <var class="varname"><var class="varname">$_SERVER</var></var>. Note that few, if any, of these will be
   available (or indeed have any meaning) if running PHP on the
   <a href="features.commandline.php" class="link">command line</a>.
  </p>

  <p class="para">
   <dl>

    <dt>

     <span class="term">&#039;<var class="varname"><var class="varname">PHP_SELF</var></var>&#039;</span>
     <dd>

      <span class="simpara">
       The filename of the currently executing script, relative to
       the document root. For instance,
       <var class="varname"><var class="varname">$_SERVER['PHP_SELF']</var></var> in a script at the
       address <var class="filename">http://example.com/test.php/foo.bar</var>
       would be <var class="filename">/test.php/foo.bar</var>.
       The <a href="language.constants.predefined.php" class="link">__FILE__</a>
       constant contains the full path and filename of the current (i.e.
       included) file.
      </span>
      <span class="simpara">
       If PHP is running as a command-line processor this variable contains
       the script name since PHP 4.3.0. Previously it was not available.
      </span>
     </dd>

    </dt>


    <dt>

     <span class="term">&#039;<a href="reserved.variables.argv.php" class="link">argv</a>&#039;</span>
     <dd>

      <span class="simpara">
       Array of arguments passed to the script. When the script is
       run on the command line, this gives C-style access to the
       command line parameters. When called via the GET method, this
       will contain the query string.
      </span>
     </dd>

    </dt>


    <dt>

     <span class="term">&#039;<a href="reserved.variables.argc.php" class="link">argc</a>&#039;</span>
     <dd>

      <span class="simpara">
       Contains the number of command line parameters passed to the
       script (if run on the command line).
      </span>
     </dd>

    </dt>


    <dt>

     <span class="term">&#039;<var class="varname"><var class="varname">GATEWAY_INTERFACE</var></var>&#039;</span>
     <dd>

      <span class="simpara">
       What revision of the CGI specification the server is using;
       i.e. &#039;<em>CGI/1.1</em>&#039;.
      </span>
     </dd>

    </dt>


    <dt>

     <span class="term">&#039;<var class="varname"><var class="varname">SERVER_ADDR</var></var>&#039;</span>
     <dd>

      <span class="simpara">
       The IP address of the server under which the current script is
       executing.
      </span>
     </dd>

    </dt>


    <dt>

     <span class="term">&#039;<var class="varname"><var class="varname">SERVER_NAME</var></var>&#039;</span>
     <dd>

      <span class="simpara">
       The name of the server host under which the current script is
       executing. If the script is running on a virtual host, this
       will be the value defined for that virtual host.
      </span>
     </dd>

    </dt>


    <dt>

     <span class="term">&#039;<var class="varname"><var class="varname">SERVER_SOFTWARE</var></var>&#039;</span>
     <dd>

      <span class="simpara">
       Server identification string, given in the headers when
       responding to requests.
      </span>
     </dd>

    </dt>


    <dt>

     <span class="term">&#039;<var class="varname"><var class="varname">SERVER_PROTOCOL</var></var>&#039;</span>
     <dd>

      <span class="simpara">
       Name and revision of the information protocol via which the
       page was requested; i.e. &#039;<em>HTTP/1.0</em>&#039;;
      </span>
     </dd>

    </dt>

    
    <dt>

     <span class="term">&#039;<var class="varname"><var class="varname">REQUEST_METHOD</var></var>&#039;</span>
     <dd>

      <span class="simpara">
       Which request method was used to access the page; i.e. &#039;<em>GET</em>&#039;,
       &#039;<em>HEAD</em>&#039;, &#039;<em>POST</em>&#039;, &#039;<em>PUT</em>&#039;.
      </span>
      <blockquote class="note"><p><strong class="note">Note</strong>: 
       <p class="para">
        PHP script is terminated after sending headers (it means after
        producing any output without output buffering) if the request method
        was <em>HEAD</em>.
       </p>
      </p></blockquote>
     </dd>

    </dt>


    <dt>

     <span class="term">&#039;<var class="varname"><var class="varname">REQUEST_TIME</var></var>&#039;</span>
     <dd>

      <span class="simpara">
       The timestamp of the start of the request. Available since PHP 5.1.0.
      </span>
     </dd>

    </dt>

    
    <dt>

     <span class="term">&#039;<var class="varname"><var class="varname">REQUEST_TIME_FLOAT</var></var>&#039;</span>
     <dd>

      <span class="simpara">
       The timestamp of the start of the request, with microsecond precision.
       Available since PHP 5.4.0.
      </span>
     </dd>

    </dt>


    <dt>

     <span class="term">&#039;<var class="varname"><var class="varname">QUERY_STRING</var></var>&#039;</span>
     <dd>

      <span class="simpara">
       The query string, if any, via which the page was accessed.
      </span>
     </dd>

    </dt>


    <dt>

     <span class="term">&#039;<var class="varname"><var class="varname">DOCUMENT_ROOT</var></var>&#039;</span>
     <dd>

      <span class="simpara">
       The document root directory under which the current script is
       executing, as defined in the server&#039;s configuration file.
      </span>
     </dd>

    </dt>


    <dt>

     <span class="term">&#039;<var class="varname"><var class="varname">HTTP_ACCEPT</var></var>&#039;</span>
     <dd>

      <span class="simpara">
       Contents of the <em>Accept:</em> header from the
       current request, if there is one.
      </span>
     </dd>

    </dt>


    <dt>

     <span class="term">&#039;<var class="varname"><var class="varname">HTTP_ACCEPT_CHARSET</var></var>&#039;</span>
     <dd>

      <span class="simpara">
       Contents of the <em>Accept-Charset:</em> header
       from the current request, if there is one. Example:
       &#039;<em>iso-8859-1,*,utf-8</em>&#039;.
      </span>
     </dd>

    </dt>


    <dt>

     <span class="term">&#039;<var class="varname"><var class="varname">HTTP_ACCEPT_ENCODING</var></var>&#039;</span>
     <dd>

      <span class="simpara">
       Contents of the <em>Accept-Encoding:</em> header
       from the current request, if there is one. Example: &#039;<em>gzip</em>&#039;.
      </span>
     </dd>

    </dt>

    
    <dt>

     <span class="term">&#039;<var class="varname"><var class="varname">HTTP_ACCEPT_LANGUAGE</var></var>&#039;</span>
     <dd>

      <span class="simpara">
       Contents of the <em>Accept-Language:</em> header
       from the current request, if there is one. Example: &#039;<em>en</em>&#039;.
      </span>
     </dd>

    </dt>

    
    <dt>

     <span class="term">&#039;<var class="varname"><var class="varname">HTTP_CONNECTION</var></var>&#039;</span>
     <dd>

      <span class="simpara">
       Contents of the <em>Connection:</em> header from
       the current request, if there is one. Example: &#039;<em>Keep-Alive</em>&#039;.
      </span>
     </dd>

    </dt>


    <dt>

     <span class="term">&#039;<var class="varname"><var class="varname">HTTP_HOST</var></var>&#039;</span>
     <dd>

      <span class="simpara">
       Contents of the <em>Host:</em> header from the
       current request, if there is one.
      </span>
     </dd>

    </dt>


    <dt>

     <span class="term">&#039;<var class="varname"><var class="varname">HTTP_REFERER</var></var>&#039;</span>
     <dd>

      <span class="simpara">
       The address of the page (if any) which referred the user
       agent to the current page. This is set by the user agent. Not
       all user agents will set this, and some provide the ability
       to modify <var class="varname"><var class="varname">HTTP_REFERER</var></var> as a feature. In
       short, it cannot really be trusted.
      </span>
     </dd>

    </dt>


    <dt>

     <span class="term">&#039;<var class="varname"><var class="varname">HTTP_USER_AGENT</var></var>&#039;</span>
     <dd>

      <span class="simpara">
       Contents of the <em>User-Agent:</em> header from
       the current request, if there is one. This is a string
       denoting the user agent being which is accessing the page. A
       typical example is: <span class="computeroutput">Mozilla/4.5 [en] (X11; U;
       Linux 2.2.9 i586)</span>. Among other things, you
       can use this value with  <span class="function"><a href="function.get-browser.php" class="function">get_browser()</a></span> to
       tailor your page&#039;s output to the capabilities of the user
       agent.
      </span>
     </dd>

    </dt>


    <dt>

     <span class="term">&#039;<var class="varname"><var class="varname">HTTPS</var></var>&#039;</span>
     <dd>

      <span class="simpara">
       Set to a non-empty value if the script was queried through the HTTPS
       protocol.
      </span>
      <blockquote class="note"><p><strong class="note">Note</strong>: 
       <span class="simpara">
        Note that when using ISAPI with IIS, the value will be 
        <em>off</em> if the request was not made through the HTTPS
        protocol.
       </span>
      </p></blockquote>
     </dd>

    </dt>


    <dt>

     <span class="term">&#039;<var class="varname"><var class="varname">REMOTE_ADDR</var></var>&#039;</span>
     <dd>

      <span class="simpara">
       The IP address from which the user is viewing the current
       page.
      </span>
     </dd>

    </dt>


    <dt>

     <span class="term">&#039;<var class="varname"><var class="varname">REMOTE_HOST</var></var>&#039;</span>
     <dd>

      <span class="simpara">
       The Host name from which the user is viewing the current
       page.  The reverse dns lookup is based off the 
       <var class="varname"><var class="varname">REMOTE_ADDR</var></var> of the user.
      </span>
      <blockquote class="note"><p><strong class="note">Note</strong>: 
       <span class="simpara">
        Your web server must be configured to create this variable. For
        example in Apache you&#039;ll need <em>HostnameLookups On</em>
        inside <var class="filename">httpd.conf</var> for it to exist.  See also
         <span class="function"><a href="function.gethostbyaddr.php" class="function">gethostbyaddr()</a></span>.
       </span>
      </p></blockquote>
     </dd>

    </dt>


    <dt>

     <span class="term">&#039;<var class="varname"><var class="varname">REMOTE_PORT</var></var>&#039;</span>
     <dd>

      <span class="simpara">
       The port being used on the user&#039;s machine to communicate with
       the web server.
      </span>
     </dd>

    </dt>


    <dt>

     <span class="term">&#039;<var class="varname"><var class="varname">REMOTE_USER</var></var>&#039;</span>
     <dd>

      <span class="simpara">
        The authenticated user.
      </span>
     </dd>

    </dt>


    <dt>

     <span class="term">&#039;<var class="varname"><var class="varname">REDIRECT_REMOTE_USER</var></var>&#039;</span>
     <dd>

      <span class="simpara">
        The authenticated user if the request is internally redirected.
      </span>
     </dd>

    </dt>


    <dt>

     <span class="term">&#039;<var class="varname"><var class="varname">SCRIPT_FILENAME</var></var>&#039;</span>
     <dd>

      <p class="para">
       The absolute pathname of the currently executing script.
       <blockquote class="note"><p><strong class="note">Note</strong>: 
        <p class="para">
         If a script is executed with the CLI, as a relative path,
         such as <var class="filename">file.php</var> or 
         <var class="filename">../file.php</var>, 
         <var class="varname"><var class="varname">$_SERVER['SCRIPT_FILENAME']</var></var> will 
         contain the relative path specified by the user.
        </p>
       </p></blockquote>
      </p>
     </dd>

    </dt>


    <dt>

     <span class="term">&#039;<var class="varname"><var class="varname">SERVER_ADMIN</var></var>&#039;</span>
     <dd>

      <span class="simpara">
       The value given to the SERVER_ADMIN (for Apache) directive in
       the web server configuration file. If the script is running
       on a virtual host, this will be the value defined for that
       virtual host.
      </span>
     </dd>

    </dt>

    
    <dt>

     <span class="term">&#039;<var class="varname"><var class="varname">SERVER_PORT</var></var>&#039;</span>
     <dd>

      <span class="simpara">
       The port on the server machine being used by the web server
       for communication. For default setups, this will be &#039;<em>80</em>&#039;;
       using SSL, for instance, will change this to whatever your
       defined secure HTTP port is.
      </span>
     </dd>

    </dt>


    <dt>

     <span class="term">&#039;<var class="varname"><var class="varname">SERVER_SIGNATURE</var></var>&#039;</span>
     <dd>

      <span class="simpara">
       String containing the server version and virtual host name
       which are added to server-generated pages, if enabled.
      </span>
     </dd>

    </dt>


    <dt>

     <span class="term">&#039;<var class="varname"><var class="varname">PATH_TRANSLATED</var></var>&#039;</span>
     <dd>

      <span class="simpara">
       Filesystem- (not document root-) based path to the current
       script, after the server has done any virtual-to-real
       mapping.
      </span>
      <blockquote class="note"><p><strong class="note">Note</strong>: 
       <span class="simpara">
        As of PHP 4.3.2, <span class="envar">PATH_TRANSLATED</span> is no longer set 
        implicitly under the Apache 2 <acronym title="Server Application Programming Interface">SAPI</acronym> in contrast 
        to the situation in Apache 1, where it&#039;s set to the same value as 
        the <span class="envar">SCRIPT_FILENAME</span> server variable when it&#039;s not 
        populated by Apache.  This change was made to comply with the 
        <acronym title="Common Gateway Interface">CGI</acronym> specification that 
        <span class="envar">PATH_TRANSLATED</span> should only exist if 
        <span class="envar">PATH_INFO</span> is defined.
       </span>
       <span class="simpara">
        Apache 2 users may use <em>AcceptPathInfo = On</em> inside
        <var class="filename">httpd.conf</var> to define <span class="envar">PATH_INFO</span>.
       </span>
      </p></blockquote>
     </dd>

    </dt>


    <dt>

     <span class="term">&#039;<var class="varname"><var class="varname">SCRIPT_NAME</var></var>&#039;</span>
     <dd>

      <span class="simpara">
       Contains the current script&#039;s path. This is useful for pages
       which need to point to themselves.
       The <a href="language.constants.predefined.php" class="link">__FILE__</a>
       constant contains the full path and filename of the current (i.e.
       included) file.
      </span>
     </dd>

    </dt>


    <dt>

     <span class="term">&#039;<var class="varname"><var class="varname">REQUEST_URI</var></var>&#039;</span>
     <dd>

      <span class="simpara">
       The URI which was given in order to access this page; for
       instance, &#039;<em>/index.html</em>&#039;.
      </span>
     </dd>

    </dt>


    <dt>

     <span class="term">&#039;<var class="varname"><var class="varname">PHP_AUTH_DIGEST</var></var>&#039;</span>
     <dd>

      <span class="simpara">
       When doing Digest HTTP authentication this variable is set 
       to the &#039;Authorization&#039; header sent by the client (which you 
       should then use to make the appropriate validation).
      </span>
     </dd>

    </dt>


    <dt>

     <span class="term">&#039;<var class="varname"><var class="varname">PHP_AUTH_USER</var></var>&#039;</span>
     <dd>

      <span class="simpara">
       When doing HTTP authentication this variable is set to the 
       username provided by the user.
      </span>
     </dd>

    </dt>


    <dt>

     <span class="term">&#039;<var class="varname"><var class="varname">PHP_AUTH_PW</var></var>&#039;</span>
     <dd>

      <span class="simpara">
       When doing HTTP authentication this variable is set to the 
       password provided by the user.
      </span>
     </dd>

    </dt>


    <dt>

     <span class="term">&#039;<var class="varname"><var class="varname">AUTH_TYPE</var></var>&#039;</span>
     <dd>

      <span class="simpara">
       When doing HTTP authenticated this variable is set to the 
       authentication type.
      </span>
     </dd>

    </dt>


    <dt>

     <span class="term">&#039;<var class="varname"><var class="varname">PATH_INFO</var></var>&#039;</span>
     <dd>

      <span class="simpara">
       Contains any client-provided pathname information trailing the
       actual script filename but preceding the query string, if
       available. For instance, if the current script was accessed via
       the
       URL <var class="filename">http://www.example.com/php/path_info.php/some/stuff?foo=bar</var>,
       then <var class="varname"><var class="varname">$_SERVER['PATH_INFO']</var></var> would
       contain <em>/some/stuff</em>.
      </span>
     </dd>

    </dt>


    <dt>

     <span class="term">&#039;<var class="varname"><var class="varname">ORIG_PATH_INFO</var></var>&#039;</span>
     <dd>

      <span class="simpara">
       Original version of &#039;<var class="varname"><var class="varname">PATH_INFO</var></var>&#039; before processed by
       PHP.
      </span>
     </dd>

    </dt>


   </dl>

  </p>
 </div>

 
 <div class="refsect1 changelog" id="refsect1-reserved.variables.server-changelog">
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
       <td>4.1.0</td>
       <td>
        Introduced <var class="varname"><var class="varname">$_SERVER</var></var> that deprecated
        <var class="varname"><var class="varname">$HTTP_SERVER_VARS</var></var>.
       </td>
      </tr>

     </tbody>
    
   </table>

  </p>
 </div>

 
 <div class="refsect1 examples" id="refsect1-reserved.variables.server-examples">
  <h3 class="title">Examples</h3>
  <p class="para">
   <div class="example" id="variable.server.basic">
    <p><strong>Example #1 <var class="varname"><var class="varname">$_SERVER</var></var> example</strong></p>
    <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">echo&nbsp;</span><span style="color: #0000BB">$_SERVER</span><span style="color: #007700">[</span><span style="color: #DD0000">'SERVER_NAME'</span><span style="color: #007700">];<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

    <div class="example-contents"><p>The above example will output
something similar to:</p></div>
    <div class="example-contents screen">
<div class="cdata"><pre>
www.example.com
</pre></div>
    </div>
   </div>
  </p>
 </div>

 
 <div class="refsect1 notes" id="refsect1-reserved.variables.server-notes">
  <h3 class="title">Notes</h3>
  <blockquote class="note"><p><strong class="note">Note</strong>: <p class="para">This is a &#039;superglobal&#039;, or
automatic global, variable. This simply means that it is available in
all scopes throughout a script. There is no need to do
<strong class="command">global $variable;</strong> to access it within functions or methods.
</p></p></blockquote>
 </div>


 <div class="refsect1 seealso" id="refsect1-reserved.variables.server-seealso">
  <h3 class="title">See Also</h3>
  <p class="para">
   <ul class="simplelist">
    <li class="member"><a href="book.filter.php" class="link">The filter extension</a></li>
   </ul>
  </p>
 </div>


</div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="reserved.variables.get.php">$_GET<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="reserved.variables.globals.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />$GLOBALS</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/reserved.variables.server.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=reserved.variables.server&amp;redirect=http://www.php.net/manual/en/reserved.variables.server.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=reserved.variables.server&amp;redirect=http://www.php.net/manual/en/reserved.variables.server.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>$_SERVER</strong>
 </div><div id="allnotes">
 <a name="108329"></a>
 <div class="note">
  <strong class='user'>dii3g0</strong>
  <a href="#108329" class="date">17-Apr-2012 04:43</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Proccess path_info<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">get_path_info</span><span class="keyword">()<br />
{<br />
&nbsp;&nbsp;&nbsp; if( ! </span><span class="default">array_key_exists</span><span class="keyword">(</span><span class="string">'PATH_INFO'</span><span class="keyword">, </span><span class="default">$_SERVER</span><span class="keyword">) )<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$pos </span><span class="keyword">= </span><span class="default">strpos</span><span class="keyword">(</span><span class="default">$_SERVER</span><span class="keyword">[</span><span class="string">'REQUEST_URI'</span><span class="keyword">], </span><span class="default">$_SERVER</span><span class="keyword">[</span><span class="string">'QUERY_STRING'</span><span class="keyword">]);<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$asd </span><span class="keyword">= </span><span class="default">substr</span><span class="keyword">(</span><span class="default">$_SERVER</span><span class="keyword">[</span><span class="string">'REQUEST_URI'</span><span class="keyword">], </span><span class="default">0</span><span class="keyword">, </span><span class="default">$pos </span><span class="keyword">- </span><span class="default">2</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$asd </span><span class="keyword">= </span><span class="default">substr</span><span class="keyword">(</span><span class="default">$asd</span><span class="keyword">, </span><span class="default">strlen</span><span class="keyword">(</span><span class="default">$_SERVER</span><span class="keyword">[</span><span class="string">'SCRIPT_NAME'</span><span class="keyword">]) + </span><span class="default">1</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$asd</span><span class="keyword">;&nbsp; &nbsp; <br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; else<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">trim</span><span class="keyword">(</span><span class="default">$_SERVER</span><span class="keyword">[</span><span class="string">'PATH_INFO'</span><span class="keyword">], </span><span class="string">'/'</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
}</span>
</span>
</code></div>
  </div>
 </div>
 <a name="108186"></a>
 <div class="note">
  <strong class='user'>LOL</strong>
  <a href="#108186" class="date">05-Apr-2012 01:26</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
For an hosting that use windows I have used this script to make REQUEST_URI to be correctly setted on IIS<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">request_URI</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; if(!isset(</span><span class="default">$_SERVER</span><span class="keyword">[</span><span class="string">'REQUEST_URI'</span><span class="keyword">])) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$_SERVER</span><span class="keyword">[</span><span class="string">'REQUEST_URI'</span><span class="keyword">] = </span><span class="default">$_SERVER</span><span class="keyword">[</span><span class="string">'SCRIPT_NAME'</span><span class="keyword">];<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if(</span><span class="default">$_SERVER</span><span class="keyword">[</span><span class="string">'QUERY_STRING'</span><span class="keyword">]) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$_SERVER</span><span class="keyword">[</span><span class="string">'REQUEST_URI'</span><span class="keyword">] .= </span><span class="string">'?' </span><span class="keyword">. </span><span class="default">$_SERVER</span><span class="keyword">[</span><span class="string">'QUERY_STRING'</span><span class="keyword">];<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">$_SERVER</span><span class="keyword">[</span><span class="string">'REQUEST_URI'</span><span class="keyword">];<br />
}<br />
</span><span class="default">$_SERVER</span><span class="keyword">[</span><span class="string">'REQUEST_URI'</span><span class="keyword">] = </span><span class="default">request_URI</span><span class="keyword">();<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="106859"></a>
 <div class="note">
  <strong class='user'>mdlamar at gmail dot com</strong>
  <a href="#106859" class="date">12-Dec-2011 03:04</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
$_SERVER['SERVER_ADDR'] contains my LAN IP rather than the public IP. I used the function gethostbyname() to get my public IP rather than the router assigned local IP.</span>
</code></div>
  </div>
 </div>
 <a name="106142"></a>
 <div class="note">
  <strong class='user'>picov at e-link dot it</strong>
  <a href="#106142" class="date">13-Oct-2011 10:59</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
A simple function to detect if the current page address was rewritten by mod_rewrite:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">public function </span><span class="default">urlWasRewritten</span><span class="keyword">() {<br />
&nbsp; </span><span class="default">$realScriptName</span><span class="keyword">=</span><span class="default">$_SERVER</span><span class="keyword">[</span><span class="string">'SCRIPT_NAME'</span><span class="keyword">];<br />
&nbsp; </span><span class="default">$virtualScriptName</span><span class="keyword">=</span><span class="default">reset</span><span class="keyword">(</span><span class="default">explode</span><span class="keyword">(</span><span class="string">"?"</span><span class="keyword">, </span><span class="default">$_SERVER</span><span class="keyword">[</span><span class="string">'REQUEST_URI'</span><span class="keyword">]));<br />
&nbsp; return !(</span><span class="default">$realScriptName</span><span class="keyword">==</span><span class="default">$virtualScriptName</span><span class="keyword">);<br />
}<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="105602"></a>
 <div class="note">
  <strong class='user'>MarkAgius at markagius dot co dot uk</strong>
  <a href="#105602" class="date">31-Aug-2011 02:18</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
You have missed 'REDIRECT_STATUS'<br />
<br />
Very useful if you point all your error pages to the same file.<br />
<br />
File; .htaccess<br />
# .htaccess file.<br />
<br />
ErrorDocument 404 /error-msg.php<br />
ErrorDocument 500 /error-msg.php<br />
ErrorDocument 400 /error-msg.php<br />
ErrorDocument 401 /error-msg.php<br />
ErrorDocument 403 /error-msg.php<br />
# End of file.<br />
<br />
File; error-msg.php<br />
<span class="default">&lt;?php<br />
&nbsp; $HttpStatus </span><span class="keyword">= </span><span class="default">$_SERVER</span><span class="keyword">[</span><span class="string">"REDIRECT_STATUS"</span><span class="keyword">] ;<br />
&nbsp; if(</span><span class="default">$HttpStatus</span><span class="keyword">==</span><span class="default">200</span><span class="keyword">) {print </span><span class="string">"Document has been processed and sent to you."</span><span class="keyword">;}<br />
&nbsp; if(</span><span class="default">$HttpStatus</span><span class="keyword">==</span><span class="default">400</span><span class="keyword">) {print </span><span class="string">"Bad HTTP request "</span><span class="keyword">;}<br />
&nbsp; if(</span><span class="default">$HttpStatus</span><span class="keyword">==</span><span class="default">401</span><span class="keyword">) {print </span><span class="string">"Unauthorized - Iinvalid password"</span><span class="keyword">;}<br />
&nbsp; if(</span><span class="default">$HttpStatus</span><span class="keyword">==</span><span class="default">403</span><span class="keyword">) {print </span><span class="string">"Forbidden"</span><span class="keyword">;}<br />
&nbsp; if(</span><span class="default">$HttpStatus</span><span class="keyword">==</span><span class="default">500</span><span class="keyword">) {print </span><span class="string">"Internal Server Error"</span><span class="keyword">;}<br />
&nbsp; if(</span><span class="default">$HttpStatus</span><span class="keyword">==</span><span class="default">418</span><span class="keyword">) {print </span><span class="string">"I'm a teapot! - This is a real value, defined in 1998"</span><span class="keyword">;}<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="102734"></a>
 <div class="note">
  <strong class='user'>Jamie</strong>
  <a href="#102734" class="date">02-Mar-2011 01:18</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Note that on real paths, aliases are not resolved<br />
<br />
$_SERVER["DOCUMENT_ROOT"] =&gt; /var/services/web/mysite<br />
$_SERVER["SCRIPT_FILENAME"] =&gt; /var/services/web/mysite/admin/products.php<br />
<br />
(but __FILE__ =&gt; /volume1/web/mysite/admin/inc/includeFile.inc.php)<br />
Use realpath to resolve the $_SERVER value.<br />
<br />
Virtual paths also have some differences:<br />
$_SERVER["SCRIPT_NAME"] =&gt; /admin/products.php (virtual path)<br />
$_SERVER["PHP_SELF"] =&gt; /admin/products.php/someExtraStuff (virtual path)<br />
<br />
SCRIPT_NAME is defined in the CGI 1.1 specification, PHP_SELF is created by PHP itself. See <a href="http://php.about.com/od/learnphp/qt/_SERVER_PHP.htm" rel="nofollow" target="_blank">http://php.about.com/od/learnphp/qt/_SERVER_PHP.htm</a> for tests.</span>
</code></div>
  </div>
 </div>
 <a name="102643"></a>
 <div class="note">
  <strong class='user'>sainthyoga2003 at gmail dot com</strong>
  <a href="#102643" class="date">25-Feb-2011 12:37</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
$_SERVER["SCRIPT_FILENAME"] returns the path including the filename, like __DIR__</span>
</code></div>
  </div>
 </div>
 <a name="101490"></a>
 <div class="note">
  <strong class='user'>Josh Fremer</strong>
  <a href="#101490" class="date">20-Dec-2010 10:47</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
HTTPS<br />
<br />
Set to a non-empty value if the script was queried through the HTTPS protocol.<br />
<br />
Note: Note that when using ISAPI with IIS, the value will be off if the request was not made through the HTTPS protocol.<br />
<br />
=-=-=<br />
<br />
To clarify this, the value is the string "off", so a specific non-empty value rather than an empty value as in Apache.</span>
</code></div>
  </div>
 </div>
 <a name="100964"></a>
 <div class="note">
  <strong class='user'>rulerof at gmail dot com</strong>
  <a href="#100964" class="date">17-Nov-2010 10:12</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I needed to get the full base directory of my script local to my webserver, IIS 7 on Windows 2008.<br />
<br />
I ended up using this:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">GetBasePath</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">substr</span><span class="keyword">(</span><span class="default">$_SERVER</span><span class="keyword">[</span><span class="string">'SCRIPT_FILENAME'</span><span class="keyword">], </span><span class="default">0</span><span class="keyword">, </span><span class="default">strlen</span><span class="keyword">(</span><span class="default">$_SERVER</span><span class="keyword">[</span><span class="string">'SCRIPT_FILENAME'</span><span class="keyword">]) - </span><span class="default">strlen</span><span class="keyword">(</span><span class="default">strrchr</span><span class="keyword">(</span><span class="default">$_SERVER</span><span class="keyword">[</span><span class="string">'SCRIPT_FILENAME'</span><span class="keyword">], </span><span class="string">"\\"</span><span class="keyword">)));<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
And it returned C:\inetpub\wwwroot\&lt;applicationfolder&gt; as I had hoped.</span>
</code></div>
  </div>
 </div>
 <a name="100881"></a>
 <div class="note">
  <strong class='user'>Stefano (info at sarchittu dot org)</strong>
  <a href="#100881" class="date">12-Nov-2010 06:07</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
A way to get the absolute path of your page, independent from the site position (so works both on local machine and on server without setting anything) and from the server OS (works both on Unix systems and Windows systems).<br />
<br />
The only parameter it requires is the folder in which you place this script<br />
So, for istance, I'll place this into my SCRIPT folder, and I'll write SCRIPT word length in $conflen<br />
<br />
<span class="default">&lt;?php<br />
$conflen</span><span class="keyword">=</span><span class="default">strlen</span><span class="keyword">(</span><span class="string">'SCRIPT'</span><span class="keyword">);<br />
</span><span class="default">$B</span><span class="keyword">=</span><span class="default">substr</span><span class="keyword">(</span><span class="default">__FILE__</span><span class="keyword">,</span><span class="default">0</span><span class="keyword">,</span><span class="default">strrpos</span><span class="keyword">(</span><span class="default">__FILE__</span><span class="keyword">,</span><span class="string">'/'</span><span class="keyword">));<br />
</span><span class="default">$A</span><span class="keyword">=</span><span class="default">substr</span><span class="keyword">(</span><span class="default">$_SERVER</span><span class="keyword">[</span><span class="string">'DOCUMENT_ROOT'</span><span class="keyword">], </span><span class="default">strrpos</span><span class="keyword">(</span><span class="default">$_SERVER</span><span class="keyword">[</span><span class="string">'DOCUMENT_ROOT'</span><span class="keyword">], </span><span class="default">$_SERVER</span><span class="keyword">[</span><span class="string">'PHP_SELF'</span><span class="keyword">]));<br />
</span><span class="default">$C</span><span class="keyword">=</span><span class="default">substr</span><span class="keyword">(</span><span class="default">$B</span><span class="keyword">,</span><span class="default">strlen</span><span class="keyword">(</span><span class="default">$A</span><span class="keyword">));<br />
</span><span class="default">$posconf</span><span class="keyword">=</span><span class="default">strlen</span><span class="keyword">(</span><span class="default">$C</span><span class="keyword">)-</span><span class="default">$conflen</span><span class="keyword">-</span><span class="default">1</span><span class="keyword">;<br />
</span><span class="default">$D</span><span class="keyword">=</span><span class="default">substr</span><span class="keyword">(</span><span class="default">$C</span><span class="keyword">,</span><span class="default">1</span><span class="keyword">,</span><span class="default">$posconf</span><span class="keyword">);<br />
</span><span class="default">$host</span><span class="keyword">=</span><span class="string">'<a href="http://" rel="nofollow" target="_blank">http://</a>'</span><span class="keyword">.</span><span class="default">$_SERVER</span><span class="keyword">[</span><span class="string">'SERVER_NAME'</span><span class="keyword">].</span><span class="string">'/'</span><span class="keyword">.</span><span class="default">$D</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
$host will finally contain the absolute path.</span>
</code></div>
  </div>
 </div>
 <a name="100877"></a>
 <div class="note">
  <strong class='user'>Anonymous</strong>
  <a href="#100877" class="date">12-Nov-2010 04:22</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Use Strict-Transport-Security (STS) to force the use of SSL.<br />
<span class="default">&lt;?php<br />
$use_sts </span><span class="keyword">= </span><span class="default">TRUE</span><span class="keyword">;<br />
<br />
if (</span><span class="default">$use_sts </span><span class="keyword">&amp;&amp; isset(</span><span class="default">$_SERVER</span><span class="keyword">[</span><span class="string">'HTTPS'</span><span class="keyword">]) {<br />
&nbsp; </span><span class="default">header</span><span class="keyword">(</span><span class="string">'Strict-Transport-Security: max-age=500'</span><span class="keyword">);<br />
} elseif (</span><span class="default">$use_sts </span><span class="keyword">&amp;&amp; !isset(</span><span class="default">$_SERVER</span><span class="keyword">[</span><span class="string">'HTTPS'</span><span class="keyword">]) {<br />
&nbsp; </span><span class="default">header</span><span class="keyword">(</span><span class="string">'Status-Code: 301'</span><span class="keyword">);<br />
&nbsp; </span><span class="default">header</span><span class="keyword">(</span><span class="string">'Location: https://'</span><span class="keyword">.</span><span class="default">$_SERVER</span><span class="keyword">[</span><span class="string">"HTTP_HOST"</span><span class="keyword">].</span><span class="default">$_SERVER</span><span class="keyword">[</span><span class="string">'REQUEST_URI'</span><span class="keyword">]);<br />
}<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="99395"></a>
 <div class="note">
  <strong class='user'>dtomasiewicz at gmail dot com</strong>
  <a href="#99395" class="date">14-Aug-2010 08:03</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
To get an associative array of HTTP request headers formatted similarly to get_headers(), this will do the trick:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="comment">/**<br />
&nbsp;* Transforms $_SERVER HTTP headers into a nice associative array. For example:<br />
&nbsp;*&nbsp;&nbsp; array(<br />
&nbsp;*&nbsp; &nbsp; &nbsp;&nbsp; 'Referer' =&gt; 'example.com',<br />
&nbsp;*&nbsp; &nbsp; &nbsp;&nbsp; 'X-Requested-With' =&gt; 'XMLHttpRequest'<br />
&nbsp;*&nbsp;&nbsp; )<br />
&nbsp;*/<br />
</span><span class="keyword">function </span><span class="default">get_request_headers</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$headers </span><span class="keyword">= array();<br />
&nbsp;&nbsp;&nbsp; foreach(</span><span class="default">$_SERVER </span><span class="keyword">as </span><span class="default">$key </span><span class="keyword">=&gt; </span><span class="default">$value</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if(</span><span class="default">strpos</span><span class="keyword">(</span><span class="default">$key</span><span class="keyword">, </span><span class="string">'HTTP_'</span><span class="keyword">) === </span><span class="default">0</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$headers</span><span class="keyword">[</span><span class="default">str_replace</span><span class="keyword">(</span><span class="string">' '</span><span class="keyword">, </span><span class="string">'-'</span><span class="keyword">, </span><span class="default">ucwords</span><span class="keyword">(</span><span class="default">str_replace</span><span class="keyword">(</span><span class="string">'_'</span><span class="keyword">, </span><span class="string">' '</span><span class="keyword">, </span><span class="default">strtolower</span><span class="keyword">(</span><span class="default">substr</span><span class="keyword">(</span><span class="default">$key</span><span class="keyword">, </span><span class="default">5</span><span class="keyword">)))))] = </span><span class="default">$value</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">$headers</span><span class="keyword">;<br />
}<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="98408"></a>
 <div class="note">
  <strong class='user'>wbeaumo1 at gmail dot com</strong>
  <a href="#98408" class="date">13-Jun-2010 08:43</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Don't forget $_SERVER['HTTP_COOKIE']. It contains the raw value of the 'Cookie' header sent by the user agent.</span>
</code></div>
  </div>
 </div>
 <a name="97351"></a>
 <div class="note">
  <strong class='user'>kamazee at gmail dot com</strong>
  <a href="#97351" class="date">15-Apr-2010 06:41</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
$_SERVER['DOCUMENT_ROOT'] in different environments may has trailing slash or not, so be careful when including files from $_SERVER['DOCUMENT_ROOT']:<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">include(</span><span class="default">dirname</span><span class="keyword">(</span><span class="default">$_SERVER</span><span class="keyword">[</span><span class="string">'DOCUMENT_ROOT'</span><span class="keyword">]) . </span><span class="default">DIRECTORY_SEPARATOR </span><span class="keyword">. </span><span class="string">'file.php'</span><span class="keyword">)<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="97106"></a>
 <div class="note">
  <strong class='user'>php at isnoop dot net</strong>
  <a href="#97106" class="date">01-Apr-2010 09:38</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Use the apache SetEnv directive to set arbitrary $_SERVER variables in your vhost or apache config.<br />
<br />
SetEnv varname "variable value"</span>
</code></div>
  </div>
 </div>
 <a name="96832"></a>
 <div class="note">
  <strong class='user'>piana at pyrohawk dot com</strong>
  <a href="#96832" class="date">17-Mar-2010 09:26</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
There are two different variables that I find very useful in Caching and similar.<br />
<br />
$_SERVER['REQUEST_URI'] and $_SERVER['REQUEST_URL']<br />
<br />
URI provides the entire request path (/directory/file.ext?query=string)<br />
URL provides the request path, without the query string (/directory/file.ext)<br />
It also differs from __FILE__ in that it's not the file name.&nbsp; So, if you go to /directory/anotherfile.ext and get silently redirected to file.ext, these variables are anotherfile.ext, while __FILE__ is still file.ext.</span>
</code></div>
  </div>
 </div>
 <a name="96356"></a>
 <div class="note">
  <strong class='user'>Megan Mickelson</strong>
  <a href="#96356" class="date">22-Feb-2010 03:36</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
It makes sense to want to paste the $_SERVER['REQUEST_URI'] on to a page (like on a footer), but be sure to clean it up first with htmlspecialchars() otherwise it poses a cross-site scripting vulnerability.<br />
<br />
htmlspecialchars($_SERVER['REQUEST_URI']);<br />
<br />
e.g.<br />
<a href="http://www.example.com/foo?&lt;script&gt;..." rel="nofollow" target="_blank">http://www.example.com/foo?&lt;script&gt;...</a><br />
<br />
becomes<br />
<a href="http://www.example.com/foo?&amp;lt;script&amp;gt;..." rel="nofollow" target="_blank">http://www.example.com/foo?&amp;lt;script&amp;gt;...</a></span>
</code></div>
  </div>
 </div>
 <a name="95672"></a>
 <div class="note">
  <strong class='user'>admin at NOSpAM dot sinfocol dot org</strong>
  <a href="#95672" class="date">14-Jan-2010 10:31</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I was testing with the $_SERVER variable and some request method, and I found that with apache I can put an arbitrary method.<br />
<br />
For example, I have an script called "server.php" in my example webpage with the next code:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">echo </span><span class="default">$_SERVER</span><span class="keyword">[</span><span class="string">'REQUEST_METHOD'</span><span class="keyword">];<br />
</span><span class="default">?&gt;<br />
</span><br />
And I made this request:<br />
c:\&gt;nc -vv www.example.com 80<br />
example.com [x.x.x.x] 80 (http) open<br />
ArbitratyMethod /server.php HTTP/1.1<br />
Host: wow.sinfocol.org<br />
Connection: Close<br />
<br />
The response of the server is the next:<br />
HTTP/1.1 200 OK<br />
Date: Fri, 15 Jan 2010 05:14:09 GMT<br />
Server: Apache<br />
Connection: close<br />
Transfer-Encoding: chunked<br />
Content-Type: text/html<br />
<br />
ArbitratyMethod<br />
<br />
So, be carefully when include the $_SERVER['REQUEST_METHOD'] in any script, this kind of "bug" is old and could be dangerous.</span>
</code></div>
  </div>
 </div>
 <a name="94237"></a>
 <div class="note">
  <strong class='user'>mirko dot steiner at slashdevslashnull dot de</strong>
  <a href="#94237" class="date">24-Oct-2009 02:43</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
<span class="default">&lt;?php<br />
<br />
</span><span class="comment">// RFC 2616 compatible Accept Language Parser<br />
// <a href="http://www.ietf.org/rfc/rfc2616.txt," rel="nofollow" target="_blank">http://www.ietf.org/rfc/rfc2616.txt,</a> 14.4 Accept-Language, Page 104<br />
// Hypertext Transfer Protocol -- HTTP/1.1<br />
<br />
</span><span class="keyword">foreach (</span><span class="default">explode</span><span class="keyword">(</span><span class="string">','</span><span class="keyword">, </span><span class="default">$_SERVER</span><span class="keyword">[</span><span class="string">'HTTP_ACCEPT_LANGUAGE'</span><span class="keyword">]) as </span><span class="default">$lang</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$pattern </span><span class="keyword">= </span><span class="string">'/^(?P&lt;primarytag&gt;[a-zA-Z]{2,8})'</span><span class="keyword">.<br />
&nbsp;&nbsp;&nbsp; </span><span class="string">'(?:-(?P&lt;subtag&gt;[a-zA-Z]{2,8}))?(?:(?:;q=)'</span><span class="keyword">.<br />
&nbsp;&nbsp;&nbsp; </span><span class="string">'(?P&lt;quantifier&gt;\d\.\d))?$/'</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$splits </span><span class="keyword">= array();<br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">printf</span><span class="keyword">(</span><span class="string">"Lang:,,%s''\n"</span><span class="keyword">, </span><span class="default">$lang</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; if (</span><span class="default">preg_match</span><span class="keyword">(</span><span class="default">$pattern</span><span class="keyword">, </span><span class="default">$lang</span><span class="keyword">, </span><span class="default">$splits</span><span class="keyword">)) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">print_r</span><span class="keyword">(</span><span class="default">$splits</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; } else {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">"\nno match\n"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
example output:<br />
<br />
Google Chrome 3.0.195.27 Windows xp<br />
<br />
Lang:,,de-DE''<br />
Array<br />
(<br />
&nbsp;&nbsp;&nbsp; [0] =&gt; de-DE<br />
&nbsp;&nbsp;&nbsp; [primarytag] =&gt; de<br />
&nbsp;&nbsp;&nbsp; [1] =&gt; de<br />
&nbsp;&nbsp;&nbsp; [subtag] =&gt; DE<br />
&nbsp;&nbsp;&nbsp; [2] =&gt; DE<br />
)<br />
Lang:,,de;q=0.8''<br />
Array<br />
(<br />
&nbsp;&nbsp;&nbsp; [0] =&gt; de;q=0.8<br />
&nbsp;&nbsp;&nbsp; [primarytag] =&gt; de<br />
&nbsp;&nbsp;&nbsp; [1] =&gt; de<br />
&nbsp;&nbsp;&nbsp; [subtag] =&gt; <br />
&nbsp;&nbsp;&nbsp; [2] =&gt; <br />
&nbsp;&nbsp;&nbsp; [quantifier] =&gt; 0.8<br />
&nbsp;&nbsp;&nbsp; [3] =&gt; 0.8<br />
)<br />
Lang:,,en-US;q=0.6''<br />
Array<br />
(<br />
&nbsp;&nbsp;&nbsp; [0] =&gt; en-US;q=0.6<br />
&nbsp;&nbsp;&nbsp; [primarytag] =&gt; en<br />
&nbsp;&nbsp;&nbsp; [1] =&gt; en<br />
&nbsp;&nbsp;&nbsp; [subtag] =&gt; US<br />
&nbsp;&nbsp;&nbsp; [2] =&gt; US<br />
&nbsp;&nbsp;&nbsp; [quantifier] =&gt; 0.6<br />
&nbsp;&nbsp;&nbsp; [3] =&gt; 0.6<br />
)<br />
Lang:,,en;q=0.4''<br />
Array<br />
(<br />
&nbsp;&nbsp;&nbsp; [0] =&gt; en;q=0.4<br />
&nbsp;&nbsp;&nbsp; [primarytag] =&gt; en<br />
&nbsp;&nbsp;&nbsp; [1] =&gt; en<br />
&nbsp;&nbsp;&nbsp; [subtag] =&gt; <br />
&nbsp;&nbsp;&nbsp; [2] =&gt; <br />
&nbsp;&nbsp;&nbsp; [quantifier] =&gt; 0.4<br />
&nbsp;&nbsp;&nbsp; [3] =&gt; 0.4<br />
)</span>
</code></div>
  </div>
 </div>
 <a name="94070"></a>
 <div class="note">
  <strong class='user'>Lord Mac</strong>
  <a href="#94070" class="date">14-Oct-2009 07:56</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
An even *more* improved version...<br />
<br />
<span class="default">&lt;?php<br />
phpinfo</span><span class="keyword">(</span><span class="default">32</span><span class="keyword">);<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="93599"></a>
 <div class="note">
  <strong class='user'>steve at sc-fa dot com</strong>
  <a href="#93599" class="date">17-Sep-2009 12:20</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you are serving from behind a proxy server, you will almost certainly save time by looking at what these $_SERVER variables do on your machine behind the proxy.&nbsp;&nbsp; <br />
<br />
$_SERVER['HTTP_X_FORWARDED_FOR'] in place of $_SERVER['REMOTE_ADDR']<br />
<br />
$_SERVER['HTTP_X_FORWARDED_HOST'] and <br />
$_SERVER['HTTP_X_FORWARDED_SERVER'] in place of (at least in our case,) $_SERVER['SERVER_NAME']</span>
</code></div>
  </div>
 </div>
 <a name="93046"></a>
 <div class="note">
  <strong class='user'>cupy at email dot cz</strong>
  <a href="#93046" class="date">20-Aug-2009 08:24</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Tech note:<br />
$_SERVER['argc'] and $_SERVER['argv'][] has some funny behaviour,<br />
used from linux (bash) commandline, when called like <br />
"php ./script_name.php 0x020B" <br />
there is everything correct, but <br />
"./script_name.php 0x020B"<br />
is not correct - "0" is passed instead of "0x020B" as $_SERVER['argv'][1] - see the script below.<br />
Looks like the parameter is not passed well from bash to PHP.<br />
(but, inspected on the level of bash, 0x020B is understood well as $1)<br />
<br />
try this example:<br />
<br />
-------------&gt;8------------------<br />
cat ./script_name.php<br />
#! /usr/bin/php<br />
<br />
if( $_SERVER['argc'] == 2)<br />
&nbsp; {<br />
&nbsp;&nbsp;&nbsp; // funny... we have to do this trick to pass e.g. 0x020B from parameters<br />
&nbsp;&nbsp;&nbsp; // ignore this: "PHP Notice:&nbsp; Undefined offset:&nbsp; 2 in ..."<br />
&nbsp;&nbsp;&nbsp; $EID = $_SERVER['argv'][1] + $_SERVER['argv'][2] + $_SERVER['argv'][3];<br />
&nbsp; }<br />
&nbsp;else<br />
&nbsp;&nbsp; {&nbsp; &nbsp; &nbsp; &nbsp; // default<br />
&nbsp;&nbsp; &nbsp; $EID = 0x0210; // PPS failure<br />
&nbsp;&nbsp; }</span>
</code></div>
  </div>
 </div>
 <a name="92829"></a>
 <div class="note">
  <strong class='user'>jarrod at squarecrow dot com</strong>
  <a href="#92829" class="date">10-Aug-2009 08:31</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
$_SERVER['DOCUMENT_ROOT'] is incredibly useful especially when working in your development environment. If you're working on large projects you'll likely be including a large number of files into your pages. For example:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="comment">//Defines constants to use for "include" URLS - helps keep our paths clean<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">define</span><span class="keyword">(</span><span class="string">"REGISTRY_CLASSES"</span><span class="keyword">,&nbsp; </span><span class="default">$_SERVER</span><span class="keyword">[</span><span class="string">'DOCUMENT_ROOT'</span><span class="keyword">].</span><span class="string">"/SOAP/classes/"</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">define</span><span class="keyword">(</span><span class="string">"REGISTRY_CONTROLS"</span><span class="keyword">, </span><span class="default">$_SERVER</span><span class="keyword">[</span><span class="string">'DOCUMENT_ROOT'</span><span class="keyword">].</span><span class="string">"/SOAP/controls/"</span><span class="keyword">);<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">define</span><span class="keyword">(</span><span class="string">"STRING_BUILDER"</span><span class="keyword">,&nbsp; &nbsp;&nbsp; </span><span class="default">REGISTRY_CLASSES</span><span class="keyword">. </span><span class="string">"stringbuilder.php"</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">define</span><span class="keyword">(</span><span class="string">"SESSION_MANAGER"</span><span class="keyword">,&nbsp; &nbsp;&nbsp; </span><span class="default">REGISTRY_CLASSES</span><span class="keyword">. </span><span class="string">"sessionmanager.php"</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">define</span><span class="keyword">(</span><span class="string">"STANDARD_CONTROLS"</span><span class="keyword">,&nbsp; &nbsp; </span><span class="default">REGISTRY_CONTROLS</span><span class="keyword">.</span><span class="string">"standardcontrols.php"</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span><br />
In development environments, you're rarely working with your root folder, especially if you're running PHP locally on your box and using DOCUMENT_ROOT is a great way to maintain URL conformity. This will save you hours of work preparing your application for deployment from your box to a production server (not to mention save you the headache of include path failures).</span>
</code></div>
  </div>
 </div>
 <a name="92121"></a>
 <div class="note">
  <strong class='user'>Richard York</strong>
  <a href="#92121" class="date">09-Jul-2009 11:19</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Not documented here is the fact that $_SERVER is populated with some pretty useful information when accessing PHP via the shell.<br />
<br />
&nbsp;["_SERVER"]=&gt;<br />
&nbsp; array(24) {<br />
&nbsp;&nbsp;&nbsp; ["MANPATH"]=&gt;<br />
&nbsp;&nbsp;&nbsp; string(48) "/usr/share/man:/usr/local/share/man:/usr/X11/man"<br />
&nbsp;&nbsp;&nbsp; ["TERM"]=&gt;<br />
&nbsp;&nbsp;&nbsp; string(11) "xterm-color"<br />
&nbsp;&nbsp;&nbsp; ["SHELL"]=&gt;<br />
&nbsp;&nbsp;&nbsp; string(9) "/bin/bash"<br />
&nbsp;&nbsp;&nbsp; ["SSH_CLIENT"]=&gt;<br />
&nbsp;&nbsp;&nbsp; string(20) "127.0.0.1 41242 22"<br />
&nbsp;&nbsp;&nbsp; ["OLDPWD"]=&gt;<br />
&nbsp;&nbsp;&nbsp; string(60) "/Library/WebServer/Domains/www.example.com/private"<br />
&nbsp;&nbsp;&nbsp; ["SSH_TTY"]=&gt;<br />
&nbsp;&nbsp;&nbsp; string(12) "/dev/ttys000"<br />
&nbsp;&nbsp;&nbsp; ["USER"]=&gt;<br />
&nbsp;&nbsp;&nbsp; string(5) "username"<br />
&nbsp;&nbsp;&nbsp; ["MAIL"]=&gt;<br />
&nbsp;&nbsp;&nbsp; string(15) "/var/mail/username"<br />
&nbsp;&nbsp;&nbsp; ["PATH"]=&gt;<br />
&nbsp;&nbsp;&nbsp; string(57) "/usr/bin:/bin:/usr/sbin:/sbin:/usr/local/bin:/usr/X11/bin"<br />
&nbsp;&nbsp;&nbsp; ["PWD"]=&gt;<br />
&nbsp;&nbsp;&nbsp; string(56) "/Library/WebServer/Domains/www.example.com/www"<br />
&nbsp;&nbsp;&nbsp; ["SHLVL"]=&gt;<br />
&nbsp;&nbsp;&nbsp; string(1) "1"<br />
&nbsp;&nbsp;&nbsp; ["HOME"]=&gt;<br />
&nbsp;&nbsp;&nbsp; string(12) "/Users/username"<br />
&nbsp;&nbsp;&nbsp; ["LOGNAME"]=&gt;<br />
&nbsp;&nbsp;&nbsp; string(5) "username"<br />
&nbsp;&nbsp;&nbsp; ["SSH_CONNECTION"]=&gt;<br />
&nbsp;&nbsp;&nbsp; string(31) "127.0.0.1 41242 10.0.0.1 22"<br />
&nbsp;&nbsp;&nbsp; ["_"]=&gt;<br />
&nbsp;&nbsp;&nbsp; string(12) "/usr/bin/php"<br />
&nbsp;&nbsp;&nbsp; ["__CF_USER_TEXT_ENCODING"]=&gt;<br />
&nbsp;&nbsp;&nbsp; string(9) "0x1F5:0:0"<br />
&nbsp;&nbsp;&nbsp; ["PHP_SELF"]=&gt;<br />
&nbsp;&nbsp;&nbsp; string(10) "Shell.php"<br />
&nbsp;&nbsp;&nbsp; ["SCRIPT_NAME"]=&gt;<br />
&nbsp;&nbsp;&nbsp; string(10) "Shell.php"<br />
&nbsp;&nbsp;&nbsp; ["SCRIPT_FILENAME"]=&gt;<br />
&nbsp;&nbsp;&nbsp; string(10) "Shell.php"<br />
&nbsp;&nbsp;&nbsp; ["PATH_TRANSLATED"]=&gt;<br />
&nbsp;&nbsp;&nbsp; string(10) "Shell.php"<br />
&nbsp;&nbsp;&nbsp; ["DOCUMENT_ROOT"]=&gt;<br />
&nbsp;&nbsp;&nbsp; string(0) ""<br />
&nbsp;&nbsp;&nbsp; ["REQUEST_TIME"]=&gt;<br />
&nbsp;&nbsp;&nbsp; int(1247162183)<br />
&nbsp;&nbsp;&nbsp; ["argv"]=&gt;<br />
&nbsp;&nbsp;&nbsp; array(1) {<br />
&nbsp;&nbsp; &nbsp;&nbsp; [0]=&gt;<br />
&nbsp;&nbsp; &nbsp;&nbsp; string(10) "Shell.php"<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; ["argc"]=&gt;<br />
&nbsp;&nbsp;&nbsp; int(1)<br />
&nbsp; }</span>
</code></div>
  </div>
 </div>
 <a name="91964"></a>
 <div class="note">
  <strong class='user'>chris</strong>
  <a href="#91964" class="date">02-Jul-2009 04:01</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
A table of everything in the $_SERVER array can be found near the bottom of the output of phpinfo();</span>
</code></div>
  </div>
 </div>
 <a name="90656"></a>
 <div class="note">
  <strong class='user'>pudding06 at gmail dot com</strong>
  <a href="#90656" class="date">02-May-2009 02:44</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Here's a simple, quick but effective way to block unwanted external visitors to your local server:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="comment">// only local requests<br />
</span><span class="keyword">if (</span><span class="default">$_SERVER</span><span class="keyword">[</span><span class="string">'REMOTE_ADDR'</span><span class="keyword">] !== </span><span class="string">'127.0.0.1'</span><span class="keyword">) die(</span><span class="default">header</span><span class="keyword">(</span><span class="string">"Location: /"</span><span class="keyword">));<br />
</span><span class="default">?&gt;<br />
</span><br />
This will direct all external traffic to your home page. Of course you could send a 404 or other custom error. Best practice is not to stay on the page with a custom error message as you acknowledge that the page does exist. That's why I redirect unwanted calls to (for example) phpmyadmin.</span>
</code></div>
  </div>
 </div>
 <a name="90592"></a>
 <div class="note">
  <strong class='user'>dragon[dot]dionysius[at]gmail[dot]com</strong>
  <a href="#90592" class="date">29-Apr-2009 10:53</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I've updated the function of my previous poster and putted it into my class.<br />
<br />
<span class="default">&lt;?php<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">/**<br />
&nbsp;&nbsp; &nbsp; * Checking HTTP-Header for language<br />
&nbsp;&nbsp; &nbsp; * needed for various system classes<br />
&nbsp;&nbsp; &nbsp; * <br />
&nbsp;&nbsp; &nbsp; * @return&nbsp; &nbsp; boolean&nbsp; &nbsp; true/false <br />
&nbsp;&nbsp; &nbsp; */<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">private function </span><span class="default">_checkClientLanguage</span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {&nbsp; &nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$langcode </span><span class="keyword">= (!empty(</span><span class="default">$_SERVER</span><span class="keyword">[</span><span class="string">'HTTP_ACCEPT_LANGUAGE'</span><span class="keyword">])) ? </span><span class="default">$_SERVER</span><span class="keyword">[</span><span class="string">'HTTP_ACCEPT_LANGUAGE'</span><span class="keyword">] : </span><span class="string">''</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$langcode </span><span class="keyword">= (!empty(</span><span class="default">$langcode</span><span class="keyword">)) ? </span><span class="default">explode</span><span class="keyword">(</span><span class="string">";"</span><span class="keyword">, </span><span class="default">$langcode</span><span class="keyword">) : </span><span class="default">$langcode</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$langcode </span><span class="keyword">= (!empty(</span><span class="default">$langcode</span><span class="keyword">[</span><span class="string">'0'</span><span class="keyword">])) ? </span><span class="default">explode</span><span class="keyword">(</span><span class="string">","</span><span class="keyword">, </span><span class="default">$langcode</span><span class="keyword">[</span><span class="string">'0'</span><span class="keyword">]) : </span><span class="default">$langcode</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$langcode </span><span class="keyword">= (!empty(</span><span class="default">$langcode</span><span class="keyword">[</span><span class="string">'0'</span><span class="keyword">])) ? </span><span class="default">explode</span><span class="keyword">(</span><span class="string">"-"</span><span class="keyword">, </span><span class="default">$langcode</span><span class="keyword">[</span><span class="string">'0'</span><span class="keyword">]) : </span><span class="default">$langcode</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$langcode</span><span class="keyword">[</span><span class="string">'0'</span><span class="keyword">];<br />
&nbsp;&nbsp;&nbsp; }<br />
</span><span class="default">?&gt;<br />
</span><br />
Please note, you have to check additional the result! Because the header may be missing or another possible thing, it is malformed. So check the result with a list with languages you support and perhaps you have to load a default language.<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="comment">// if result isn't one of my defined languages<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">if(!</span><span class="default">in_array</span><span class="keyword">(</span><span class="default">$lang</span><span class="keyword">, </span><span class="default">$language_list</span><span class="keyword">)) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$lang </span><span class="keyword">= </span><span class="default">$language_default</span><span class="keyword">; </span><span class="comment">// load default<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
My HTTP_ACCEPT_LANGUAGE string:<br />
FF3: de-de,de;q=0.8,en-us;q=0.5,en;q=0.3 <br />
IE7: de-ch<br />
<br />
So, take care of it!</span>
</code></div>
  </div>
 </div>
 <a name="90293"></a>
 <div class="note">
  <strong class='user'>dalys at chokladboll dot se</strong>
  <a href="#90293" class="date">15-Apr-2009 02:03</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you want en, sv-SE, da, es etc. to be returned from $_SERVER['HTTP_ACCEPT_LANGUAGE'] you can use this function:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">detectlanguage</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$langcode </span><span class="keyword">= </span><span class="default">explode</span><span class="keyword">(</span><span class="string">";"</span><span class="keyword">, </span><span class="default">$_SERVER</span><span class="keyword">[</span><span class="string">'HTTP_ACCEPT_LANGUAGE'</span><span class="keyword">]);<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$langcode </span><span class="keyword">= </span><span class="default">explode</span><span class="keyword">(</span><span class="string">","</span><span class="keyword">, </span><span class="default">$langcode</span><span class="keyword">[</span><span class="string">'0'</span><span class="keyword">]);<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">$langcode</span><span class="keyword">[</span><span class="string">'0'</span><span class="keyword">];<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
</span><span class="default">$language </span><span class="keyword">= </span><span class="default">detectlanguage</span><span class="keyword">();<br />
<br />
echo </span><span class="string">"You have chosen $language as your language in your web browser."</span><span class="keyword">;<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="89567"></a>
 <div class="note">
  <strong class='user'>Vladimir Kornea</strong>
  <a href="#89567" class="date">13-Mar-2009 05:06</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
1. All elements of the $_SERVER array whose keys begin with 'HTTP_' come from HTTP request headers and are not to be trusted.<br />
<br />
2. All HTTP headers sent to the script are made available through the $_SERVER array, with names prefixed by 'HTTP_'.<br />
<br />
3. $_SERVER['PHP_SELF'] is dangerous if misused. If login.php/nearly_arbitrary_string is requested, $_SERVER['PHP_SELF'] will contain not just login.php, but the entire login.php/nearly_arbitrary_string. If you've printed $_SERVER['PHP_SELF'] as the value of the action attribute of your form tag without performing HTML encoding, an attacker can perform XSS attacks by offering users a link to your site such as this:<br />
<br />
&lt;a href='<a href="http://www.example.com/login.php/" rel="nofollow" target="_blank">http://www.example.com/login.php/</a>"&gt;&lt;script type="text/javascript"&gt;...&lt;/script&gt;&lt;span a="'&gt;Example.com&lt;/a&gt;<br />
<br />
The javascript block would define an event handler function and bind it to the form's submit event. This event handler would load via an &lt;img&gt; tag an external file, with the submitted username and password as parameters.<br />
<br />
Use $_SERVER['SCRIPT_NAME'] instead of $_SERVER['PHP_SELF']. HTML encode every string sent to the browser that should not be interpreted as HTML, unless you are absolutely certain that it cannot contain anything that the browser can interpret as HTML.</span>
</code></div>
  </div>
 </div>
 <a name="88418"></a>
 <div class="note">
  <strong class='user'>info at mtprod dot com</strong>
  <a href="#88418" class="date">23-Jan-2009 02:13</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
On Windows IIS 7 you must use $_SERVER['LOCAL_ADDR'] rather than $_SERVER['SERVER_ADDR'] to get the server's IP address.</span>
</code></div>
  </div>
 </div>
 <a name="87195"></a>
 <div class="note">
  <strong class='user'>jonbarnett at gmail dot com</strong>
  <a href="#87195" class="date">23-Nov-2008 09:13</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
It's worth noting that $_SERVER variables get created for any HTTP request headers, including those you might invent:<br />
<br />
If the browser sends an HTTP request header of:<br />
X-Debug-Custom: some string<br />
<br />
Then:<br />
<br />
<span class="default">&lt;?php<br />
$_SERVER</span><span class="keyword">[</span><span class="string">'HTTP_X_DEBUG_CUSTOM'</span><span class="keyword">]; </span><span class="comment">// "some string"<br />
</span><span class="default">?&gt;<br />
</span><br />
There are better ways to identify the HTTP request headers sent by the browser, but this is convenient if you know what to expect from, for example, an AJAX script with custom headers.<br />
<br />
Works in PHP5 on Apache with mod_php.&nbsp; Don't know if this is true from other environments.</span>
</code></div>
  </div>
 </div>
 <a name="86740"></a>
 <div class="note">
  <strong class='user'>jette at nerdgirl dot dk</strong>
  <a href="#86740" class="date">01-Nov-2008 11:43</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Windows running IIS v6 does not include $_SERVER['SERVER_ADDR']<br />
<br />
If you need to get the IP addresse, use this instead:<br />
<br />
<span class="default">&lt;?php<br />
$ipAddress </span><span class="keyword">= </span><span class="default">gethostbyname</span><span class="keyword">(</span><span class="default">$_SERVER</span><span class="keyword">[</span><span class="string">'SERVER_NAME'</span><span class="keyword">]);<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="86609"></a>
 <div class="note">
  <strong class='user'>geoffrey dot hoffman at gmail dot com</strong>
  <a href="#86609" class="date">25-Oct-2008 05:13</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you are looking at $_SERVER['HTTP_USER_AGENT'] to determine whether your user is on a mobile device, you may want to visit these resources:<br />
<br />
<a href="http://wurfl.sourceforge.net/" rel="nofollow" target="_blank">http://wurfl.sourceforge.net/</a><br />
<br />
<a href="http://www.zytrax.com/tech/web/mobile_ids.html" rel="nofollow" target="_blank">http://www.zytrax.com/tech/web/mobile_ids.html</a></span>
</code></div>
  </div>
 </div>
 <a name="86495"></a>
 <div class="note">
  <strong class='user'>Thomas Urban</strong>
  <a href="#86495" class="date">22-Oct-2008 01:19</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Maybe you're missing information on $_SERVER['CONTENT_TYPE'] or $_SERVER['CONTENT_LENGTH'] as I did. On POST-requests these are available in addition to those listed above.</span>
</code></div>
  </div>
 </div>
 <a name="86309"></a>
 <div class="note">
  <strong class='user'>Taomyn</strong>
  <a href="#86309" class="date">12-Oct-2008 07:21</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
'HTTPS'<br />
&nbsp;&nbsp;&nbsp; Set to a non-empty value if the script was queried through the HTTPS protocol. Note that when using ISAPI with IIS, the value will be off if the request was not made through the HTTPS protocol. <br />
<br />
Does the same for IIS7 running PHP as a Fast-CGI application.</span>
</code></div>
  </div>
 </div>
 <a name="85759"></a>
 <div class="note">
  <strong class='user'>Tonin</strong>
  <a href="#85759" class="date">16-Sep-2008 10:43</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
When using the $_SERVER['SERVER_NAME'] variable in an apache virtual host setup with a ServerAlias directive, be sure to check the UseCanonicalName apache directive.&nbsp; If it is On, this variable will always have the apache ServerName value.&nbsp; If it is Off, it will have the value given by the headers sent by the browser.<br />
<br />
Depending on what you want to do the content of this variable, put in On or Off.</span>
</code></div>
  </div>
 </div>
 <a name="85611"></a>
 <div class="note">
  <strong class='user'>Andrew B</strong>
  <a href="#85611" class="date">08-Sep-2008 04:26</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Please note on Windows/IIS - the variable 'USER_AUTH' will return the username/identity of the user accessing the page, i.e. if anonymous access is off, you would normally get back "$domain\$username".</span>
</code></div>
  </div>
 </div>
 <a name="85045"></a>
 <div class="note">
  <strong class='user'>jeff at example dot com</strong>
  <a href="#85045" class="date">12-Aug-2008 11:24</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Note that, in Apache 2, the server settings will affect the variables available in $_SERVER. For example, if you are using SSL, the following directive will dump SSL-related status information, along with the server certificate and client certificate (if present) into the $_SERVER variables:<br />
<br />
SSLOptions +StdEnvVars +ExportCertData</span>
</code></div>
  </div>
 </div>
 <a name="84919"></a>
 <div class="note">
  <strong class='user'>silverquick at gmail dot com</strong>
  <a href="#84919" class="date">05-Aug-2008 05:55</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I think the HTTPS element will only be present under Apache 2.x. It's not in the list of "special" variables here:<br />
<a href="http://httpd.apache.org/docs/1.3/mod/mod_rewrite.html#RewriteCond" rel="nofollow" target="_blank">http://httpd.apache.org/docs/1.3/mod/mod_rewrite.html#RewriteCond</a><br />
But it is here:<br />
<a href="http://httpd.apache.org/docs/2.0/mod/mod_rewrite.html#rewritecond" rel="nofollow" target="_blank">http://httpd.apache.org/docs/2.0/mod/mod_rewrite.html#rewritecond</a></span>
</code></div>
  </div>
 </div>
 <a name="84826"></a>
 <div class="note">
  <strong class='user'>danny at orionrobots dot co dot uk</strong>
  <a href="#84826" class="date">31-Jul-2008 02:25</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
It is worth noting here that if you use $_SERVER['REQUEST_URI'] with a rewrite rule, the original, not rewritten URI will be presented.</span>
</code></div>
  </div>
 </div>
 <a name="83439"></a>
 <div class="note">
  <strong class='user'>emailfire at gmail dot com</strong>
  <a href="#83439" class="date">26-May-2008 07:49</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
REQUEST_URI is useful, but if you want to get just the file name use:<br />
<br />
<span class="default">&lt;?php<br />
$this_page </span><span class="keyword">= </span><span class="default">basename</span><span class="keyword">(</span><span class="default">$_SERVER</span><span class="keyword">[</span><span class="string">'REQUEST_URI'</span><span class="keyword">]);<br />
if (</span><span class="default">strpos</span><span class="keyword">(</span><span class="default">$this_page</span><span class="keyword">, </span><span class="string">"?"</span><span class="keyword">) !== </span><span class="default">false</span><span class="keyword">) </span><span class="default">$this_page </span><span class="keyword">= </span><span class="default">reset</span><span class="keyword">(</span><span class="default">explode</span><span class="keyword">(</span><span class="string">"?"</span><span class="keyword">, </span><span class="default">$this_page</span><span class="keyword">));<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=reserved.variables.server&amp;redirect=http://www.php.net/manual/en/reserved.variables.server.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=reserved.variables.server&amp;redirect=http://www.php.net/manual/en/reserved.variables.server.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/reserved.variables.server.php">show source</a> |
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