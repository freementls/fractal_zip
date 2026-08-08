<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: data:// - Manual</title>
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
 <link rel="prev" href="wrappers.compression.php" />
 <link rel="next" href="wrappers.glob.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/wrappers.data" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/wrappers.data.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/wrappers.data.php" />
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
 <li class="active"><a href="wrappers.data.php">data://</a></li>
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
  <a href="wrappers.glob.php">glob://<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="wrappers.compression.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />zlib://</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/wrappers.data.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/wrappers.data.php">Brazilian Portuguese</option>
    <option value="zh/wrappers.data.php">Chinese (Simplified)</option>
    <option value="fr/wrappers.data.php">French</option>
    <option value="de/wrappers.data.php">German</option>
    <option value="ja/wrappers.data.php">Japanese</option>
    <option value="pl/wrappers.data.php">Polish</option>
    <option value="ro/wrappers.data.php">Romanian</option>
    <option value="ru/wrappers.data.php">Russian</option>
    <option value="fa/wrappers.data.php">Persian</option>
    <option value="es/wrappers.data.php">Spanish</option>
    <option value="tr/wrappers.data.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="wrappers.data" class="refentry">
 <div class="refnamediv">
  <h1 class="refname">data://</h1>
  <p class="refpurpose"><span class="refname">data://</span> &mdash; <span class="dc-title">Data (RFC 2397)</span></p>

 </div>

 <div class="refsect1 description" id="refsect1-wrappers.data-description">
  <h3 class="title">Description</h3>
  <p class="para">
   The <var class="filename">data:</var> (<a href="http://www.faqs.org/rfcs/rfc2397" class="link external">&raquo;&nbsp;RFC
   2397</a>) stream wrapper is available since PHP 5.2.0.
  </p>
 </div>


 <div class="refsect1 usage" id="refsect1-wrappers.data-usage"> 
  <h3 class="title">Options</h3>
  <ul class="itemizedlist">
   <li class="listitem"><span class="simpara"><var class="filename">data://text/plain;base64,</var></span></li>
  </ul>
 </div>
 

 <div class="refsect1 options" id="refsect1-wrappers.data-options">
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
       <td>No</td>
      </tr>

      <tr>
       <td>Restricted by <a href="filesystem.configuration.php#ini.allow-url-include" class="link">allow_url_include</a></td>
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
       <td>No</td>
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
 

 <div class="refsect1 examples" id="refsect1-wrappers.data-examples">
  <h3 class="title">Examples</h3>
  <div class="example" id="example-306">
   <p><strong>Example #1 Print data:// contents</strong></p>
   <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #FF8000">//&nbsp;prints&nbsp;"I&nbsp;love&nbsp;PHP"<br /></span><span style="color: #007700">echo&nbsp;</span><span style="color: #0000BB">file_get_contents</span><span style="color: #007700">(</span><span style="color: #DD0000">'data://text/plain;base64,SSBsb3ZlIFBIUAo='</span><span style="color: #007700">);<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
   </div>

  </div>

  <div class="example" id="example-307">
   <p><strong>Example #2 Fetch the media type</strong></p>
   <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br />$fp&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">fopen</span><span style="color: #007700">(</span><span style="color: #DD0000">'data://text/plain;base64,'</span><span style="color: #007700">,&nbsp;</span><span style="color: #DD0000">'r'</span><span style="color: #007700">);<br /></span><span style="color: #0000BB">$meta&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">stream_get_meta_data</span><span style="color: #007700">(</span><span style="color: #0000BB">$fp</span><span style="color: #007700">);<br /><br /></span><span style="color: #FF8000">//&nbsp;prints&nbsp;"text/plain"<br /></span><span style="color: #007700">echo&nbsp;</span><span style="color: #0000BB">$meta</span><span style="color: #007700">[</span><span style="color: #DD0000">'mediatype'</span><span style="color: #007700">];<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
   </div>

  </div>
 </div>


</div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="wrappers.glob.php">glob://<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="wrappers.compression.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />zlib://</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/wrappers.data.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=wrappers.data&amp;redirect=http://www.php.net/manual/en/wrappers.data.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=wrappers.data&amp;redirect=http://www.php.net/manual/en/wrappers.data.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>data://</strong>
 </div><div id="allnotes">
 <a name="106021"></a>
 <div class="note">
  <strong class='user'>senica at gmail dot com</strong>
  <a href="#106021" class="date">03-Oct-2011 01:20</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
You can use data:// to evaluate php that is stored in mySQL<br />
<br />
For example:<br />
<br />
If you have code stored that looks like this:<br />
$content = ' //Content from your database or variable<br />
&nbsp; <span class="default">&lt;?php </span><span class="keyword">if(</span><span class="default">$var </span><span class="keyword">== </span><span class="string">"something"</span><span class="keyword">): </span><span class="default">?&gt;<br />
</span>&nbsp; &nbsp; print this html<br />
&nbsp; <span class="default">&lt;?php </span><span class="keyword">endif; </span><span class="default">?&gt;<br />
</span>';<br />
<br />
Trying to evaluate this can prove cumbersome<br />
<br />
But you can do something like:<br />
<br />
include "data://text/plain;base64,".base64_encode($content);<br />
<br />
and it will parse it with no problems.</span>
</code></div>
  </div>
 </div>
 <a name="100979"></a>
 <div class="note">
  <strong class='user'>from dot php dot net at brainbox dot cz</strong>
  <a href="#100979" class="date">18-Nov-2010 04:37</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
When passing plain string without base64 encoding, do not forget to pass the string through URLENCODE(), because PHP automatically urldecodes all entities inside passed string (and therefore all + get lost, all % entities will be converted to the corresponding characters).<br />
<br />
In this case, PHP is strictly compilant with the RFC 2397. Section 3 states that passes data should be either in base64 encoding or urlencoded.<br />
<br />
VALID USAGE:<br />
<span class="default">&lt;?php<br />
$fp </span><span class="keyword">= </span><span class="default">fopen</span><span class="keyword">(</span><span class="string">'data:text/plain,'</span><span class="keyword">.</span><span class="default">urlencode</span><span class="keyword">(</span><span class="default">$data</span><span class="keyword">), </span><span class="string">'rb'</span><span class="keyword">); </span><span class="comment">// urlencoded data<br />
</span><span class="default">$fp </span><span class="keyword">= </span><span class="default">fopen</span><span class="keyword">(</span><span class="string">'data:text/plain;base64,'</span><span class="keyword">.</span><span class="default">base64_encode</span><span class="keyword">(</span><span class="default">$data</span><span class="keyword">), </span><span class="string">'rb'</span><span class="keyword">); </span><span class="comment">// base64 encoded data<br />
</span><span class="default">?&gt;<br />
</span><br />
Demonstration of invalid usage:<br />
<span class="default">&lt;?php<br />
$data </span><span class="keyword">= </span><span class="string">'Günther says: 1+1 is 2, 10%40 is 20.'</span><span class="keyword">;<br />
<br />
</span><span class="default">$fp </span><span class="keyword">= </span><span class="default">fopen</span><span class="keyword">(</span><span class="string">'data:text/plain,'</span><span class="keyword">.</span><span class="default">$data</span><span class="keyword">, </span><span class="string">'rb'</span><span class="keyword">); </span><span class="comment">// INVALID, never do this<br />
</span><span class="keyword">echo </span><span class="default">stream_get_contents</span><span class="keyword">(</span><span class="default">$fp</span><span class="keyword">);<br />
</span><span class="comment">// Günther says: 1 1 is 2, 10@ is 20. // ERROR<br />
<br />
</span><span class="default">$fp </span><span class="keyword">= </span><span class="default">fopen</span><span class="keyword">(</span><span class="string">'data:text/plain,'</span><span class="keyword">.</span><span class="default">urlencode</span><span class="keyword">(</span><span class="default">$data</span><span class="keyword">), </span><span class="string">'rb'</span><span class="keyword">); </span><span class="comment">// urlencoded data<br />
</span><span class="keyword">echo </span><span class="default">stream_get_contents</span><span class="keyword">(</span><span class="default">$fp</span><span class="keyword">);<br />
</span><span class="comment">// Günther says: 1+1 is 2, 10%40 is 20. // OK<br />
<br />
// Valid option 1: base64 encoded data<br />
</span><span class="default">$fp </span><span class="keyword">= </span><span class="default">fopen</span><span class="keyword">(</span><span class="string">'data:text/plain;base64,'</span><span class="keyword">.</span><span class="default">base64_encode</span><span class="keyword">(</span><span class="default">$data</span><span class="keyword">), </span><span class="string">'rb'</span><span class="keyword">); </span><span class="comment">// base64 encoded data<br />
</span><span class="keyword">echo </span><span class="default">stream_get_contents</span><span class="keyword">(</span><span class="default">$fp</span><span class="keyword">);<br />
</span><span class="comment">// Günther says: 1+1 is 2, 10%40 is 20. // OK<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="97679"></a>
 <div class="note">
  <strong class='user'>admin deskbitz net</strong>
  <a href="#97679" class="date">02-May-2010 12:54</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you want to create a gd-image directly out of a sql-database-field you might want to use:<br />
<br />
<span class="default">&lt;?php<br />
$jpegimage </span><span class="keyword">= </span><span class="default">imagecreatefromjpeg</span><span class="keyword">(</span><span class="string">"data://image/jpeg;base64," </span><span class="keyword">. </span><span class="default">base64_encode</span><span class="keyword">(</span><span class="default">$sql_result_array</span><span class="keyword">[</span><span class="string">'imagedata'</span><span class="keyword">]));<br />
</span><span class="default">?&gt;<br />
</span><br />
this goes also for gif, png, etc using the correct "imagecreatefrom$$$"-function and mime-type.</span>
</code></div>
  </div>
 </div>
 <a name="85581"></a>
 <div class="note">
  <strong class='user'>sandaimespaceman at gmail dot com</strong>
  <a href="#85581" class="date">06-Sep-2008 06:30</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Now PHP supports data: protocol w/out "//" like data:text/plain, not data://text/plain,<br />
<br />
I tried it.</span>
</code></div>
  </div>
 </div>
 <a name="82375"></a>
 <div class="note">
  <strong class='user'>togos00 at gmail dot com</strong>
  <a href="#82375" class="date">08-Apr-2008 12:56</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Note that the official data URI scheme does not include a double slash after the colon - that you must include it when making calls to PHP is an artifact of the designers' misunderstanding of URL syntax.<br />
<br />
To automatically convert proper data URIs to ones understood by PHP, you can use code such as the following:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">convertUriForPhp</span><span class="keyword">( </span><span class="default">$uri </span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; if( </span><span class="default">preg_match</span><span class="keyword">(</span><span class="string">'/^data:(?!\\/\\/)(.*)$/'</span><span class="keyword">,</span><span class="default">$uri</span><span class="keyword">,</span><span class="default">$bif</span><span class="keyword">) ) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="string">'data://' </span><span class="keyword">. </span><span class="default">$bif</span><span class="keyword">[</span><span class="default">1</span><span class="keyword">]; <br />
&nbsp;&nbsp;&nbsp; } else {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$uri</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; } <br />
}<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=wrappers.data&amp;redirect=http://www.php.net/manual/en/wrappers.data.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=wrappers.data&amp;redirect=http://www.php.net/manual/en/wrappers.data.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/wrappers.data.php">show source</a> |
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