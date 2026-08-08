<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: http:// - Manual</title>
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
 <link rel="prev" href="wrappers.file.php" />
 <link rel="next" href="wrappers.ftp.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/wrappers.http" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/wrappers.http.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/wrappers.http.php" />
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
 <li class="active"><a href="wrappers.http.php">http://</a></li>
 <li><a href="wrappers.ftp.php">ftp://</a></li>
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
  <a href="wrappers.ftp.php">ftp://<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="wrappers.file.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />file://</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/wrappers.http.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/wrappers.http.php">Brazilian Portuguese</option>
    <option value="zh/wrappers.http.php">Chinese (Simplified)</option>
    <option value="fr/wrappers.http.php">French</option>
    <option value="de/wrappers.http.php">German</option>
    <option value="ja/wrappers.http.php">Japanese</option>
    <option value="pl/wrappers.http.php">Polish</option>
    <option value="ro/wrappers.http.php">Romanian</option>
    <option value="ru/wrappers.http.php">Russian</option>
    <option value="fa/wrappers.http.php">Persian</option>
    <option value="es/wrappers.http.php">Spanish</option>
    <option value="tr/wrappers.http.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="wrappers.http" class="refentry">
 <div class="refnamediv">
  <h1 class="refname">http://</h1>
  <h1 class="refname">https://</h1>
  <p class="refpurpose"><span class="refname">http://</span> -- <span class="refname">https://</span> &mdash; <span class="dc-title">Accessing HTTP(s) URLs</span></p>

 </div>

 <div class="refsect1 description" id="refsect1-wrappers.http-description">
  <h3 class="title">Description</h3>
  <p class="para">
   Allows read-only access to files/resources via HTTP 1.0,
   using the HTTP GET method. A <em>Host:</em> header is sent with the request
   to handle name-based virtual hosts.  If you have configured
   a <a href="filesystem.configuration.php#ini.user-agent" class="link">user_agent</a> string using
   your <var class="filename">php.ini</var> file or the stream context, it will also be included
   in the request.
  </p>
  <p class="simpara">
   The stream allows access to the <em class="emphasis">body</em> of
   the resource; the headers are stored in the
   <var class="varname"><var class="varname"><a href="reserved.variables.httpresponseheader.php" class="classname">$http_response_header</a></var></var> variable.
  </p>
  <p class="simpara">
   If it&#039;s important to know the URL of the resource where
   your document came from (after all redirects have been processed),
   you&#039;ll need to process the series of response headers returned by the
   stream.
  </p>
  <p class="simpara">
   The <a href="filesystem.configuration.php#ini.from" class="link">from</a> directive will be used for the
   <em>From:</em> header if set and not overwritten by the
   <a href="context.php" class="xref">Context options and parameters</a>.
  </p>
 </div>


 <div class="refsect1 usage" id="refsect1-wrappers.http-usage"> 
  <h3 class="title">Options</h3>
  <ul class="itemizedlist">
   <li class="listitem"><span class="simpara"><var class="filename">http://example.com</var></span></li>
   <li class="listitem"><span class="simpara"><var class="filename">http://example.com/file.php?var1=val1&amp;var2=val2</var></span></li>
   <li class="listitem"><span class="simpara"><var class="filename">http://user:password@example.com</var></span></li>
   <li class="listitem"><span class="simpara"><var class="filename">https://example.com</var></span></li>
   <li class="listitem"><span class="simpara"><var class="filename">https://example.com/file.php?var1=val1&amp;var2=val2</var></span></li>
   <li class="listitem"><span class="simpara"><var class="filename">https://user:password@example.com</var></span></li>
  </ul>
 </div>
 

 <div class="refsect1 options" id="refsect1-wrappers.http-options">
  <h3 class="title">Options</h3>
  <p class="para">
   <table class="doctable table">
    <caption><strong>Wrapper Summary</strong></caption>
    
     <thead>
      <tr>
       <th>Attribute</th>
       <th>Supported</th>
      </tr>

     </thead>

     <tbody class="tbody">
      <tr>
       <td>Restricted by <a href="filesystem.configuration.php#ini.allow-url-fopen" class="link">allow_url_fopen</a></td>
       <td>Yes</td>
      </tr>

      <tr>
       <td>Allows Reading</td>
       <td>Yes</td>
      </tr>

      <tr>
       <td>Allows Writing</td>
       <td>No</td>
      </tr>

      <tr>
       <td>Allows Appending</td>
       <td>No</td>
      </tr>

      <tr>
       <td>Allows Simultaneous Reading and Writing</td>
       <td>N/A</td>
      </tr>

      <tr>
       <td>Supports  <span class="function"><a href="function.stat.php" class="function">stat()</a></span></td>
       <td>No</td>
      </tr>

      <tr>
       <td>Supports  <span class="function"><a href="function.unlink.php" class="function">unlink()</a></span></td>
       <td>No</td>
      </tr>

      <tr>
       <td>Supports  <span class="function"><a href="function.rename.php" class="function">rename()</a></span></td>
       <td>No</td>
      </tr>

      <tr>
       <td>Supports  <span class="function"><a href="function.mkdir.php" class="function">mkdir()</a></span></td>
       <td>No</td>
      </tr>

      <tr>
       <td>Supports  <span class="function"><a href="function.rmdir.php" class="function">rmdir()</a></span></td>
       <td>No</td>
      </tr>

     </tbody>
    
   </table>

  </p>
 </div>
 

 <div class="refsect1 changelog" id="refsect1-wrappers.http-changelog">
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
       <td>4.3.7</td>
       <td>
        Detect buggy IIS servers to avoid <em>&quot;SSL: Fatal Protocol Error&quot;</em> errors.
       </td>
      </tr>

      <tr>
       <td>4.3.0</td>
       <td>
        Added <em>https://</em>.
       </td>
      </tr>

      <tr>
       <td>4.0.5</td>
       <td>
        Added support for redirects.
       </td>
      </tr>

     </tbody>
    
   </table>

  </p>
 </div>


 <div class="refsect1 examples" id="refsect1-wrappers.http-examples">
  <h3 class="title">Examples</h3>
  <div class="example" id="wrappers.http.example.basic">
   <p><strong>Example #1 Detecting which URL we ended up on after redirects</strong></p>
   <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br />$url&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'http://www.example.com/redirecting_page.php'</span><span style="color: #007700">;<br /><br /></span><span style="color: #0000BB">$fp&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">fopen</span><span style="color: #007700">(</span><span style="color: #0000BB">$url</span><span style="color: #007700">,&nbsp;</span><span style="color: #DD0000">'r'</span><span style="color: #007700">);<br /><br /></span><span style="color: #0000BB">$meta_data&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">stream_get_meta_data</span><span style="color: #007700">(</span><span style="color: #0000BB">$fp</span><span style="color: #007700">);<br />foreach&nbsp;(</span><span style="color: #0000BB">$meta_data</span><span style="color: #007700">[</span><span style="color: #DD0000">'wrapper_data'</span><span style="color: #007700">]&nbsp;as&nbsp;</span><span style="color: #0000BB">$response</span><span style="color: #007700">)&nbsp;{<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">/*&nbsp;Were&nbsp;we&nbsp;redirected?&nbsp;*/<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">if&nbsp;(</span><span style="color: #0000BB">strtolower</span><span style="color: #007700">(</span><span style="color: #0000BB">substr</span><span style="color: #007700">(</span><span style="color: #0000BB">$response</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">0</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">10</span><span style="color: #007700">))&nbsp;==&nbsp;</span><span style="color: #DD0000">'location:&nbsp;'</span><span style="color: #007700">)&nbsp;{<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">/*&nbsp;update&nbsp;$url&nbsp;with&nbsp;where&nbsp;we&nbsp;were&nbsp;redirected&nbsp;to&nbsp;*/<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$url&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">substr</span><span style="color: #007700">(</span><span style="color: #0000BB">$response</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">10</span><span style="color: #007700">);<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br /><br />}<br /><br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
   </div>

  </div>

  <div class="example" id="wrappers.http.example.custom.headers"> 
   <p><strong>Example #2 Sending custom headers with an HTTP request</strong></p>
   <div class="example-contents"><p>
    Custom headers may be sent using <a href="context.http.php" class="link">context
    options</a>. It is also possible to use this hack:
    Custom headers may be sent with an HTTP request 
    by taking advantage of a side-effect in the
    handling of the <em>user_agent</em> INI setting.
    Set <em>user_agent</em> to any valid string
    (such as the default <em>PHP/version</em> setting)
    followed by a carriage-return/line-feed pair and any
    additional headers.
   </p></div>
   <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br />ini_set</span><span style="color: #007700">(</span><span style="color: #DD0000">'user_agent'</span><span style="color: #007700">,&nbsp;</span><span style="color: #DD0000">"PHP\r\nX-MyCustomHeader:&nbsp;Foo"</span><span style="color: #007700">);<br /><br /></span><span style="color: #0000BB">$fp&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">fopen</span><span style="color: #007700">(</span><span style="color: #DD0000">'http://www.example.com/index.php'</span><span style="color: #007700">,&nbsp;</span><span style="color: #DD0000">'r'</span><span style="color: #007700">);<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
   </div>

   <div class="example-contents"><p>Results in the following request being sent:</p></div>
   <div class="example-contents screen">
<div class="cdata"><pre>
GET /index.php HTTP/1.0
Host: www.example.com
User-Agent: PHP
X-MyCustomHeader: Foo
</pre></div>
   </div>
  </div>
 </div>


 <div class="refsect1 notes" id="refsect1-wrappers.http-notes">
  <h3 class="title">Notes</h3>
  <blockquote class="note"><p><strong class="note">Note</strong>: 
   <span class="simpara">
    HTTPS is only supported when the <a href="book.openssl.php" class="link">openssl</a>
    extension is enabled.
   </span>
  </p></blockquote>
  <p class="simpara">
   HTTP connections are read-only; writing data or copying
   files to an HTTP resource is not supported.
  </p>
  <p class="simpara">
   Sending <em class="emphasis">POST</em> and <em class="emphasis">PUT</em> requests, for example,
   can be done with the help of <a href="context.http.php" class="link">HTTP Contexts</a>.
  </p>
 </div>


 <div class="refsect1 seealso" id="refsect1-wrappers.http-seealso">
  <h3 class="title">See Also</h3>
  <ul class="simplelist">
   <li class="member"><a href="context.http.php" class="xref">HTTP context options</a></li>
   <li class="member"><var class="varname"><var class="varname"><a href="reserved.variables.httpresponseheader.php" class="classname">$http_response_header</a></var></var></li>
   <li class="member"> <span class="function"><a href="function.stream-get-meta-data.php" class="function" rel="rdfs-seeAlso">stream_get_meta_data()</a> - Retrieves header/meta data from streams/file pointers</span></li>
  </ul>
 </div>


</div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="wrappers.ftp.php">ftp://<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="wrappers.file.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />file://</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/wrappers.http.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=wrappers.http&amp;redirect=http://www.php.net/manual/en/wrappers.http.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=wrappers.http&amp;redirect=http://www.php.net/manual/en/wrappers.http.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>http://</strong>
 </div><div id="allnotes">
 <a name="108895"></a>
 <div class="note">
  <strong class='user'>gaver at telenet dot be</strong>
  <a href="#108895" class="date">02-Jun-2012 11:38</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you want to communicate between your server and a https server<br />
<br />
<span class="default">&lt;?php<br />
$variable </span><span class="keyword">=</span><span class="default">$_GET</span><span class="keyword">[</span><span class="string">'variable'</span><span class="keyword">];<br />
</span><span class="default">$host </span><span class="keyword">= </span><span class="string">"https://user:password@secure site"</span><span class="keyword">; <br />
</span><span class="default">$path </span><span class="keyword">= </span><span class="string">"securescript.php"</span><span class="keyword">; <br />
</span><span class="default">$url</span><span class="keyword">=</span><span class="default">$host</span><span class="keyword">.</span><span class="default">$path</span><span class="keyword">;<br />
<br />
</span><span class="default">$formdata </span><span class="keyword">= array ( </span><span class="string">"variable1" </span><span class="keyword">=&gt; </span><span class="string">"10000"</span><span class="keyword">,</span><span class="string">"variable2" </span><span class="keyword">=&gt; </span><span class="string">"02" </span><span class="keyword">, </span><span class="string">"variable3" </span><span class="keyword">=&gt; </span><span class="string">"03" </span><span class="keyword">); <br />
<br />
</span><span class="comment">// get form data in a string<br />
&nbsp; </span><span class="keyword">foreach(</span><span class="default">$formdata </span><span class="keyword">AS </span><span class="default">$key </span><span class="keyword">=&gt; </span><span class="default">$val</span><span class="keyword">){ <br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$poststring </span><span class="keyword">.= </span><span class="default">urlencode</span><span class="keyword">(</span><span class="default">$key</span><span class="keyword">) . </span><span class="string">"=" </span><span class="keyword">. </span><span class="default">urlencode</span><span class="keyword">(</span><span class="default">$val</span><span class="keyword">) . </span><span class="string">"&amp;"</span><span class="keyword">; <br />
&nbsp; } <br />
</span><span class="comment">// strip off trailing ampersand <br />
</span><span class="default">$poststring </span><span class="keyword">= </span><span class="default">substr</span><span class="keyword">(</span><span class="default">$poststring</span><span class="keyword">, </span><span class="default">0</span><span class="keyword">, -</span><span class="default">1</span><span class="keyword">); <br />
</span><span class="comment">// create a complete nice string<br />
</span><span class="default">$urlcom</span><span class="keyword">= </span><span class="default">$host</span><span class="keyword">.</span><span class="default">$path</span><span class="keyword">.</span><span class="string">"?"</span><span class="keyword">.</span><span class="default">$poststring</span><span class="keyword">;<br />
<br />
</span><span class="comment">// echo $urlcom; // if you want to debug your string<br />
<br />
//and you can read out the data in one command... from a secure https server handling login and everything you need<br />
&nbsp;</span><span class="default">$postdataget</span><span class="keyword">=</span><span class="default">file_get_contents</span><span class="keyword">(</span><span class="default">$urlcom</span><span class="keyword">);<br />
</span><span class="comment">// and yes you can debug the responseheader to view if you have authorization<br />
//&nbsp; var_dump($http_response_header);<br />
<br />
//and if you want to read the data, this is the response from the server<br />
//&nbsp; var_dump($postdataget);<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="84067"></a>
 <div class="note">
  <strong class='user'>Nick Lewis</strong>
  <a href="#84067" class="date">26-Jun-2008 04:17</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
A note on how to deal with Cookies<br />
<br />
To receive a cookie:<br />
<br />
$httphandle = fopen($url,"r");<br />
$meta = stream_get_meta_data($httphandle);<br />
for ($j = 0; isset($meta['wrapper_data'][$j]); $j++) {<br />
&nbsp;&nbsp; $httpline = $meta['wrapper_data'][$j];<br />
&nbsp;&nbsp; @list($header,$parameters) = explode(";",$httpline,2);<br />
&nbsp;&nbsp; @list($attr,$value) = explode(":",$header,2);<br />
&nbsp;&nbsp; if (strtolower(trim($attr)) == "set-cookie") {<br />
&nbsp;&nbsp; &nbsp;&nbsp; $cookie = trim($value);<br />
&nbsp;&nbsp; &nbsp;&nbsp; break;<br />
&nbsp;&nbsp; }<br />
}<br />
fclose($httphandle);<br />
echo $cookie;<br />
<br />
To send a cookie:<br />
<br />
$user_agent = ini_get("user_agent");<br />
ini_set("user_agent",$user_agent . "\r\nCookie: " . $cookie);<br />
$httphandle = fopen($url,"r");<br />
fclose($httphandle);<br />
ini_set("user_agent",$user_agent);</span>
</code></div>
  </div>
 </div>
 <a name="78703"></a>
 <div class="note">
  <strong class='user'>spazdaq</strong>
  <a href="#78703" class="date">24-Oct-2007 03:27</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
just an FYI about digest authentication.<br />
<br />
While one of the above http examples has the username and password info supplied with the url, this must only be for basic authentication. it does not appear to work for digest authentication. you have to handle the digest followup request on your own.</span>
</code></div>
  </div>
 </div>
 <a name="76760"></a>
 <div class="note">
  <strong class='user'>NEA at AraTaraBul dot com</strong>
  <a href="#76760" class="date">29-Jul-2007 04:06</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
HTTP post function;<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">post_it</span><span class="keyword">(</span><span class="default">$datastream</span><span class="keyword">, </span><span class="default">$url</span><span class="keyword">) { <br />
<br />
</span><span class="default">$url </span><span class="keyword">= </span><span class="default">preg_replace</span><span class="keyword">(</span><span class="string">"@^<a href="http://@i" rel="nofollow" target="_blank">http://@i</a>"</span><span class="keyword">, </span><span class="string">""</span><span class="keyword">, </span><span class="default">$url</span><span class="keyword">);<br />
</span><span class="default">$host </span><span class="keyword">= </span><span class="default">substr</span><span class="keyword">(</span><span class="default">$url</span><span class="keyword">, </span><span class="default">0</span><span class="keyword">, </span><span class="default">strpos</span><span class="keyword">(</span><span class="default">$url</span><span class="keyword">, </span><span class="string">"/"</span><span class="keyword">));<br />
</span><span class="default">$uri </span><span class="keyword">= </span><span class="default">strstr</span><span class="keyword">(</span><span class="default">$url</span><span class="keyword">, </span><span class="string">"/"</span><span class="keyword">); <br />
<br />
&nbsp;&nbsp; &nbsp;&nbsp; </span><span class="default">$reqbody </span><span class="keyword">= </span><span class="string">""</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp;&nbsp; foreach(</span><span class="default">$datastream </span><span class="keyword">as </span><span class="default">$key</span><span class="keyword">=&gt;</span><span class="default">$val</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; if (!empty(</span><span class="default">$reqbody</span><span class="keyword">)) </span><span class="default">$reqbody</span><span class="keyword">.= </span><span class="string">"&amp;"</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp;&nbsp; </span><span class="default">$reqbody</span><span class="keyword">.= </span><span class="default">$key</span><span class="keyword">.</span><span class="string">"="</span><span class="keyword">.</span><span class="default">urlencode</span><span class="keyword">(</span><span class="default">$val</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp;&nbsp; } <br />
<br />
</span><span class="default">$contentlength </span><span class="keyword">= </span><span class="default">strlen</span><span class="keyword">(</span><span class="default">$reqbody</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; </span><span class="default">$reqheader </span><span class="keyword">=&nbsp; </span><span class="string">"POST $uri HTTP/1.1\r\n"</span><span class="keyword">.<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="string">"Host: $host\n"</span><span class="keyword">. </span><span class="string">"User-Agent: PostIt\r\n"</span><span class="keyword">.<br />
&nbsp;&nbsp; &nbsp; </span><span class="string">"Content-Type: application/x-www-form-urlencoded\r\n"</span><span class="keyword">.<br />
&nbsp;&nbsp; &nbsp; </span><span class="string">"Content-Length: $contentlength\r\n\r\n"</span><span class="keyword">.<br />
&nbsp;&nbsp; &nbsp; </span><span class="string">"$reqbody\r\n"</span><span class="keyword">; <br />
<br />
</span><span class="default">$socket </span><span class="keyword">= </span><span class="default">fsockopen</span><span class="keyword">(</span><span class="default">$host</span><span class="keyword">, </span><span class="default">80</span><span class="keyword">, </span><span class="default">$errno</span><span class="keyword">, </span><span class="default">$errstr</span><span class="keyword">);<br />
<br />
if (!</span><span class="default">$socket</span><span class="keyword">) {<br />
&nbsp;&nbsp; </span><span class="default">$result</span><span class="keyword">[</span><span class="string">"errno"</span><span class="keyword">] = </span><span class="default">$errno</span><span class="keyword">;<br />
&nbsp;&nbsp; </span><span class="default">$result</span><span class="keyword">[</span><span class="string">"errstr"</span><span class="keyword">] = </span><span class="default">$errstr</span><span class="keyword">;<br />
&nbsp;&nbsp; return </span><span class="default">$result</span><span class="keyword">;<br />
}<br />
<br />
</span><span class="default">fputs</span><span class="keyword">(</span><span class="default">$socket</span><span class="keyword">, </span><span class="default">$reqheader</span><span class="keyword">);<br />
<br />
while (!</span><span class="default">feof</span><span class="keyword">(</span><span class="default">$socket</span><span class="keyword">)) {<br />
&nbsp;&nbsp; </span><span class="default">$result</span><span class="keyword">[] = </span><span class="default">fgets</span><span class="keyword">(</span><span class="default">$socket</span><span class="keyword">, </span><span class="default">4096</span><span class="keyword">);<br />
}<br />
<br />
</span><span class="default">fclose</span><span class="keyword">(</span><span class="default">$socket</span><span class="keyword">);<br />
<br />
return </span><span class="default">$result</span><span class="keyword">;<br />
}<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="76058"></a>
 <div class="note">
  <strong class='user'>Sinured</strong>
  <a href="#76058" class="date">28-Jun-2007 03:24</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you want to send more than one custom header, just make header an array:<br />
<br />
<span class="default">&lt;?php<br />
$default_opts </span><span class="keyword">= array(<br />
&nbsp;&nbsp;&nbsp; </span><span class="string">'http' </span><span class="keyword">=&gt; array(<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="string">'user_agent' </span><span class="keyword">=&gt; </span><span class="string">'Foobar'</span><span class="keyword">,<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="string">'header' </span><span class="keyword">=&gt; array(<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="string">'X-Foo: Bar'</span><span class="keyword">,<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="string">'X-Bar: Baz'<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; )<br />
);<br />
</span><span class="default">stream_context_get_default</span><span class="keyword">(</span><span class="default">$default_opts</span><span class="keyword">);<br />
</span><span class="default">readfile</span><span class="keyword">(</span><span class="string">'<a href="http://www.xhaus.com/headers" rel="nofollow" target="_blank">http://www.xhaus.com/headers</a>'</span><span class="keyword">);<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="71226"></a>
 <div class="note">
  <strong class='user'>dwalton at acm dot org</strong>
  <a href="#71226" class="date">17-Nov-2006 12:18</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
As it says on this page:<br />
<br />
"The stream allows access to the body of the resource; the headers are stored in the $http_response_header variable. Since PHP 4.3.0, the headers are available using stream_get_meta_data()."<br />
<br />
This one sentence is the only documentation I have found on the mysterious $http_response_header variable, and I'm afraid it's misleading.&nbsp; It implies that from 4.3.0 onward, stream_get_meta_data() ought to be used in favor of $http_response_header.&nbsp; <br />
<br />
Don't be fooled!&nbsp; stream_get_meta_data() requires a stream reference, which makes it ONLY useful with fopen() and related functions.&nbsp; However, $http_response_header can be used to get the headers from the much simpler file_get_contents() and related functions, which makes it still very useful in 5.x.<br />
<br />
Also note that even when file_get_contents() and friends fail due to a 4xx or 5xx error and return false, the headers are still available in $http_response_header.</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=wrappers.http&amp;redirect=http://www.php.net/manual/en/wrappers.http.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=wrappers.http&amp;redirect=http://www.php.net/manual/en/wrappers.http.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/wrappers.http.php">show source</a> |
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