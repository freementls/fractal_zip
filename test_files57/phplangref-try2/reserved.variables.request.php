<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: $_REQUEST - Manual</title>
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
 <link rel="prev" href="reserved.variables.files.php" />
 <link rel="next" href="reserved.variables.session.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/reserved.variables.request" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/reserved.variables.request.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="@w{NVHTDYQQ}" />
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
 <li><a href="reserved.variables.server.php">$_SERVER</a></li>
 <li><a href="reserved.variables.get.php">$_GET</a></li>
 <li><a href="reserved.variables.post.php">$_POST</a></li>
 <li><a href="reserved.variables.files.php">$_FILES</a></li>
 <li class="active"><a href="reserved.variables.request.php">$_REQUEST</a></li>
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
  <a href="reserved.variables.session.php">$_SESSION<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="reserved.variables.files.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />$_FILES</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/reserved.variables.request.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/reserved.variables.request.php">Brazilian Portuguese</option>
    <option value="zh/reserved.variables.request.php">Chinese (Simplified)</option>
    <option value="fr/reserved.variables.request.php">French</option>
    <option value="de/reserved.variables.request.php">German</option>
    <option value="ja/reserved.variables.request.php">Japanese</option>
    <option value="pl/reserved.variables.request.php">Polish</option>
    <option value="ro/reserved.variables.request.php">Romanian</option>
    <option value="ru/reserved.variables.request.php">Russian</option>
    <option value="fa/reserved.variables.request.php">Persian</option>
    <option value="es/reserved.variables.request.php">Spanish</option>
    <option value="tr/reserved.variables.request.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="reserved.variables.request" class="refentry">
 <div class="refnamediv">
  <h1 class="refname">$_REQUEST</h1>
  <p class="verinfo">(PHP 4 &gt;= 4.1.0, PHP 5)</p><p class="refpurpose"><span class="refname">$_REQUEST</span> &mdash; <span class="dc-title">HTTP Request variables</span></p>

 </div>
 
 <div class="refsect1 description" id="refsect1-reserved.variables.request-description">
  <h3 class="title">Description</h3>
  <p class="para">
   An associative <span class="type"><a href="language.types.array.php" class="type array">array</a></span> that by default contains the contents of
   <var class="varname"><var class="varname"><a href="reserved.variables.get.php" class="classname">$_GET</a></var></var>,
   <var class="varname"><var class="varname"><a href="reserved.variables.post.php" class="classname">$_POST</a></var></var> and
   <var class="varname"><var class="varname"><a href="reserved.variables.cookies.php" class="classname">$_COOKIE</a></var></var>.
  </p>
 </div>

 

 <div class="refsect1 changelog" id="refsect1-reserved.variables.request-changelog">
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
       <td>5.3.0</td>
       <td>
        Introduced <a href="ini.core.php#ini.request-order" class="link">request_order</a>.
        This directive affects the contents of <var class="varname"><var class="varname">$_REQUEST</var></var>.
       </td>
      </tr>

      <tr>
       <td>4.3.0</td>
       <td>
        <var class="varname"><var class="varname"><a href="reserved.variables.files.php" class="classname">$_FILES</a></var></var> information
        was removed from <var class="varname"><var class="varname">$_REQUEST</var></var>.
       </td>
      </tr>

      <tr>
       <td>4.1.0</td>
       <td>
        Introduced <var class="varname"><var class="varname">$_REQUEST</var></var>.
       </td>
      </tr>

     </tbody>
    
   </table>

  </p>
 </div>

 
 <div class="refsect1 notes" id="refsect1-reserved.variables.request-notes">
  <h3 class="title">Notes</h3>
  <blockquote class="note"><p><strong class="note">Note</strong>: <p class="para">This is a &#039;superglobal&#039;, or
automatic global, variable. This simply means that it is available in
all scopes throughout a script. There is no need to do
<strong class="command">global $variable;</strong> to access it within functions or methods.
</p></p></blockquote>
  <blockquote class="note"><p><strong class="note">Note</strong>: 
   <p class="para">
    When running on the <a href="features.commandline.php" class="link">command line
    </a>, this will <em class="emphasis">not</em> include the 
    <a href="reserved.variables.argv.php" class="link">argv</a> and 
    <a href="reserved.variables.argc.php" class="link">argc</a> entries; these are 
    present in the <var class="varname"><var class="varname"><a href="reserved.variables.server.php" class="classname">$_SERVER</a></var></var>
    <span class="type"><a href="language.types.array.php" class="type array">array</a></span>.
   </p>
  </p></blockquote>
  <blockquote class="note"><p><strong class="note">Note</strong>: 
   <p class="para">
    The variables in <var class="varname"><var class="varname">$_REQUEST</var></var> are provided to the
    script via the GET, POST, and COOKIE input mechanisms and
    therefore could be modified by the remote user and cannot be
    trusted. The presence and order of variables listed in this array
    is defined according to the
    PHP <a href="ini.core.php#ini.variables-order" class="link">variables_order</a>
    configuration directive.
   </p>
  </p></blockquote>
 </div>


 <div class="refsect1 seealso" id="refsect1-reserved.variables.request-seealso">
  <h3 class="title">See Also</h3>
  <ul class="simplelist">
   <li class="member"> <span class="function"><a href="function.import-request-variables.php" class="function" rel="rdfs-seeAlso">import_request_variables()</a> - Import GET/POST/Cookie variables into the global scope</span></li>
   <li class="member"><a href="language.variables.external.php" class="link">Handling external variables</a></li>
   <li class="member"><a href="book.filter.php" class="link">The filter extension</a></li>
  </ul>
 </div>

 
</div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="reserved.variables.session.php">$_SESSION<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="reserved.variables.files.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />$_FILES</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/reserved.variables.request.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=reserved.variables.request&amp;redirect=@w{NVHTDYQQ}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=reserved.variables.request&amp;redirect=@w{NVHTDYQQ}">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>$_REQUEST</strong>
 </div><div id="allnotes">
 <a name="107234"></a>
 <div class="note">
  <strong class='user'>rm at km-it dot de</strong>
  <a href="#107234" class="date">23-Jan-2012 03:46</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Note that the default distribution php.ini files does not contain the 'C' for cookies, due to security concerns. <br />
See <a href="http://http://php.net/manual/en/ini.core.php#ini.request-order" rel="nofollow" target="_blank">http://http://php.net/manual/en/ini.core.php#ini.request-order</a></span>
</code></div>
  </div>
 </div>
 <a name="96696"></a>
 <div class="note">
  <strong class='user'>mike o.</strong>
  <a href="#96696" class="date">11-Mar-2010 04:31</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The default php.ini on your system as of in PHP 5.3.0 may exclude cookies from $_REQUEST.&nbsp; The request_order ini directive specifies what goes in the $_REQUEST array; if that does not exist, then the variables_order directive does.&nbsp; Your distribution's php.ini may exclude cookies by default, so beware.</span>
</code></div>
  </div>
 </div>
 <a name="94985"></a>
 <div class="note">
  <strong class='user'>John Galt</strong>
  <a href="#94985" class="date">06-Dec-2009 05:36</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I wrote a function because I found it inconvenient if I needed to change a particular parameter (get) while preserving the others. For example, I want to make a hyperlink on a web page with the URL <a href="http://www.example.com/script.php?id=1&amp;blah=blah+blah&amp;page=1" rel="nofollow" target="_blank">http://www.example.com/script.php?id=1&amp;blah=blah+blah&amp;page=1</a> and change the value of "page" to 2 without getting rid of the other parameters.<br />
<br />
<span class="default">&lt;?php<br />
&nbsp;</span><span class="keyword">function </span><span class="default">add_or_change_parameter</span><span class="keyword">(</span><span class="default">$parameter</span><span class="keyword">, </span><span class="default">$value</span><span class="keyword">)<br />
&nbsp;{<br />
&nbsp; </span><span class="default">$params </span><span class="keyword">= array();<br />
&nbsp; </span><span class="default">$output </span><span class="keyword">= </span><span class="string">"?"</span><span class="keyword">;<br />
&nbsp; </span><span class="default">$firstRun </span><span class="keyword">= </span><span class="default">true</span><span class="keyword">;<br />
&nbsp; foreach(</span><span class="default">$_GET </span><span class="keyword">as </span><span class="default">$key</span><span class="keyword">=&gt;</span><span class="default">$val</span><span class="keyword">)<br />
&nbsp; {<br />
&nbsp;&nbsp; if(</span><span class="default">$key </span><span class="keyword">!= </span><span class="default">$parameter</span><span class="keyword">)<br />
&nbsp;&nbsp; {<br />
&nbsp;&nbsp;&nbsp; if(!</span><span class="default">$firstRun</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; </span><span class="default">$output </span><span class="keyword">.= </span><span class="string">"&amp;"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; else<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; </span><span class="default">$firstRun </span><span class="keyword">= </span><span class="default">false</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$output </span><span class="keyword">.= </span><span class="default">$key</span><span class="keyword">.</span><span class="string">"="</span><span class="keyword">.</span><span class="default">urlencode</span><span class="keyword">(</span><span class="default">$val</span><span class="keyword">);<br />
&nbsp;&nbsp; }<br />
&nbsp; }<br />
&nbsp; if(!</span><span class="default">$firstRun</span><span class="keyword">)<br />
&nbsp;&nbsp; </span><span class="default">$output </span><span class="keyword">.= </span><span class="string">"&amp;"</span><span class="keyword">;<br />
&nbsp; </span><span class="default">$output </span><span class="keyword">.= </span><span class="default">$parameter</span><span class="keyword">.</span><span class="string">"="</span><span class="keyword">.</span><span class="default">urlencode</span><span class="keyword">(</span><span class="default">$value</span><span class="keyword">);<br />
&nbsp; return </span><span class="default">htmlentities</span><span class="keyword">(</span><span class="default">$output</span><span class="keyword">);<br />
&nbsp;}<br />
</span><span class="default">?&gt;<br />
</span><br />
Now, I can add a hyperlink to the page (<a href="http://www.example.com/script.php?id=1&amp;blah=blah+blah&amp;page=1" rel="nofollow" target="_blank">http://www.example.com/script.php?id=1&amp;blah=blah+blah&amp;page=1</a>) like this:<br />
&lt;a href="<span class="default">&lt;?php </span><span class="keyword">echo </span><span class="default">add_or_change_parameter</span><span class="keyword">(</span><span class="string">"page"</span><span class="keyword">, </span><span class="string">"2"</span><span class="keyword">); </span><span class="default">?&gt;</span>"&gt;Click to go to page 2&lt;/a&gt;<br />
<br />
The above code will output<br />
&lt;a href="?id=1&amp;amp;blah=blah+blah&amp;amp;page=2"&gt;Click to go to page 2&lt;/a&gt;<br />
<br />
Also, if I was setting "page" to a string rather than just "2", the value would be urlencode()'d.<br />
&lt;a href="<span class="default">&lt;?php </span><span class="keyword">echo </span><span class="default">add_or_change_parameter</span><span class="keyword">(</span><span class="string">"page"</span><span class="keyword">, </span><span class="string">"banana+split!"</span><span class="keyword">); </span><span class="default">?&gt;</span>"&gt;Click to go to page banana split!&lt;/a&gt;<br />
would become<br />
&lt;a href="?id=1&amp;amp;blah=blah+blah&amp;amp;page=banana+split%21"&gt;Click to go to page banana split!&lt;/a&gt;<br />
<br />
[EDIT BY danbrown AT php DOT net: Contains a bugfix provided by (theogony AT gmail DOT com), which adds missing `echo` instructions to the HREF tags.]</span>
</code></div>
  </div>
 </div>
 <a name="92891"></a>
 <div class="note">
  <strong class='user'>smjg at iname dot com</strong>
  <a href="#92891" class="date">13-Aug-2009 03:56</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Selecting $_GET or $_POST depending on the request method isn't a general solution, since it's possible for an HTTP request to have both posted content and a query string in the URI.<br />
<br />
If you want to allow for this possibility, you can use<br />
<span class="default">&lt;?php<br />
$req </span><span class="keyword">= </span><span class="default">array_merge</span><span class="keyword">(</span><span class="default">$_GET</span><span class="keyword">, </span><span class="default">$_POST</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span>or vice versa, depending on which you want to be used in the event of a clash between them.</span>
</code></div>
  </div>
 </div>
 <a name="84527"></a>
 <div class="note">
  <strong class='user'>strata_ranger at hotmail dot com</strong>
  <a href="#84527" class="date">17-Jul-2008 08:04</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Don't forget, because $_REQUEST is a different variable than $_GET and $_POST, it is treated as such in PHP -- modifying $_GET or $_POST elements at runtime will not affect the ellements in $_REQUEST, nor vice versa.<br />
<br />
e.g:<br />
<br />
<span class="default">&lt;?php<br />
<br />
$_GET</span><span class="keyword">[</span><span class="string">'foo'</span><span class="keyword">] = </span><span class="string">'a'</span><span class="keyword">;<br />
</span><span class="default">$_POST</span><span class="keyword">[</span><span class="string">'bar'</span><span class="keyword">] = </span><span class="string">'b'</span><span class="keyword">;<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$_GET</span><span class="keyword">); </span><span class="comment">// Element 'foo' is string(1) "a"<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$_POST</span><span class="keyword">); </span><span class="comment">// Element 'bar' is string(1) "b"<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$_REQUEST</span><span class="keyword">); </span><span class="comment">// Does not contain elements 'foo' or 'bar'<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
If you want to evaluate $_GET and $_POST variables by a single token without including $_COOKIE in the mix, use&nbsp; $_SERVER['REQUEST_METHOD'] to identify the method used and set up a switch block accordingly, e.g:<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">switch(</span><span class="default">$_SERVER</span><span class="keyword">[</span><span class="string">'REQUEST_METHOD'</span><span class="keyword">])<br />
{<br />
case </span><span class="string">'GET'</span><span class="keyword">: </span><span class="default">$the_request </span><span class="keyword">= &amp;</span><span class="default">$_GET</span><span class="keyword">; break;<br />
case </span><span class="string">'POST'</span><span class="keyword">: </span><span class="default">$the_request </span><span class="keyword">= &amp;</span><span class="default">$_POST</span><span class="keyword">; break;<br />
.<br />
. </span><span class="comment">// Etc.<br />
</span><span class="keyword">.<br />
default:<br />
}<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=reserved.variables.request&amp;redirect=@w{NVHTDYQQ}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=reserved.variables.request&amp;redirect=@w{NVHTDYQQ}">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/reserved.variables.request.php">show source</a> |
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