<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: zlib:// - Manual</title>
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
 <link rel="prev" href="wrappers.php.php" />
 <link rel="next" href="wrappers.data.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/wrappers.compression" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/wrappers.compression.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/wrappers.compression.php" />
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
 <li class="active"><a href="wrappers.compression.php">zlib://</a></li>
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
  <a href="wrappers.data.php">data://<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="wrappers.php.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />php://</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/wrappers.compression.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/wrappers.compression.php">Brazilian Portuguese</option>
    <option value="zh/wrappers.compression.php">Chinese (Simplified)</option>
    <option value="fr/wrappers.compression.php">French</option>
    <option value="de/wrappers.compression.php">German</option>
    <option value="ja/wrappers.compression.php">Japanese</option>
    <option value="pl/wrappers.compression.php">Polish</option>
    <option value="ro/wrappers.compression.php">Romanian</option>
    <option value="ru/wrappers.compression.php">Russian</option>
    <option value="fa/wrappers.compression.php">Persian</option>
    <option value="es/wrappers.compression.php">Spanish</option>
    <option value="tr/wrappers.compression.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="wrappers.compression" class="refentry">
 <div class="refnamediv">
  <h1 class="refname">zlib://</h1>
  <h1 class="refname">bzip2://</h1>
  <h1 class="refname">zip://</h1>
  <p class="refpurpose"><span class="refname">zlib://</span> -- <span class="refname">bzip2://</span> -- <span class="refname">zip://</span> &mdash; <span class="dc-title">Compression Streams</span></p>

 </div>

 <div class="refsect1 description" id="refsect1-wrappers.compression-description">
  <h3 class="title">Description</h3>
  <p class="simpara"><var class="filename">zlib:</var> PHP 4.0.4 - PHP 4.2.3 (systems with fopencookie only)</p>
  <p class="simpara"><var class="filename">compress.zlib://</var> and <var class="filename">compress.bzip2://</var> PHP 4.3.0 and up</p>

  <p class="simpara">
   <var class="filename">zlib:</var> works like  <span class="function"><a href="function.gzopen.php" class="function">gzopen()</a></span>, except that the
   stream can be used with  <span class="function"><a href="function.fread.php" class="function">fread()</a></span> and the other
   filesystem functions.  This is deprecated as of PHP 4.3.0 due
   to ambiguities with filenames containing &#039;:&#039; characters; use
   <var class="filename">compress.zlib://</var> instead.
  </p>

  <p class="simpara">
   <var class="filename">compress.zlib://</var> and
   <var class="filename">compress.bzip2://</var> are equivalent to
    <span class="function"><a href="function.gzopen.php" class="function">gzopen()</a></span> and  <span class="function"><a href="function.bzopen.php" class="function">bzopen()</a></span>
   respectively, and operate even on systems that do not support
   fopencookie.
  </p>
  <p class="para">
   <a href="book.zip.php" class="link">ZIP extension</a> registers <var class="filename">zip:</var> wrapper.
  </p>
 </div>


 <div class="refsect1 usage" id="refsect1-wrappers.compression-usage"> 
  <h3 class="title">Options</h3>
  <ul class="itemizedlist">
   <li class="listitem"><span class="simpara"><var class="filename">zlib:</var></span></li>
   <li class="listitem"><span class="simpara"><var class="filename">compress.zlib://</var></span></li>
   <li class="listitem"><span class="simpara"><var class="filename">compress.bzip2://</var></span></li>
  </ul>
 </div>
 

 <div class="refsect1 options" id="refsect1-wrappers.compression-options">
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
       <td>Allows Reading</td>
       <td>Yes</td>
      </tr>

      <tr>
       <td>Allows Writing</td>
       <td>Yes (except <em>zip://</em>)</td>
      </tr>

      <tr>
       <td>Allows Appending</td>
       <td>Yes (except <em>zip://</em>)</td>
      </tr>

      <tr>
       <td>Allows Simultaneous Reading and Writing</td>
       <td>No</td>
      </tr>

      <tr>
       <td>Supports  <span class="function"><a href="function.stat.php" class="function">stat()</a></span></td>
       <td>
        No, use the normal <em>file://</em> wrapper
        to stat compressed files.
       </td>
      </tr>

      <tr>
       <td>Supports  <span class="function"><a href="function.unlink.php" class="function">unlink()</a></span></td>
       <td>
        No, use the normal <em>file://</em> wrapper
        to unlink compressed files.
       </td>
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
 

</div><br /><br />
<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=wrappers.compression&amp;redirect=http://www.php.net/manual/en/wrappers.compression.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=wrappers.compression&amp;redirect=http://www.php.net/manual/en/wrappers.compression.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>zlib://</strong>
 </div><div id="allnotes">
 <a name="103425"></a>
 <div class="note">
  <strong class='user'>alvaro at demogracia dot com</strong>
  <a href="#103425" class="date">12-Apr-2011 07:41</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Example on how to read an entry from a ZIP archive (file "bar.txt" inside "./foo.zip"):<br />
<br />
<span class="default">&lt;?php<br />
<br />
$fp </span><span class="keyword">= </span><span class="default">fopen</span><span class="keyword">(</span><span class="string">'zip://./foo.zip#bar.txt'</span><span class="keyword">, </span><span class="string">'r'</span><span class="keyword">);<br />
if( </span><span class="default">$fp </span><span class="keyword">){<br />
&nbsp;&nbsp;&nbsp; while( !</span><span class="default">feof</span><span class="keyword">(</span><span class="default">$fp</span><span class="keyword">) ){<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="default">fread</span><span class="keyword">(</span><span class="default">$fp</span><span class="keyword">, </span><span class="default">8192</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">fclose</span><span class="keyword">(</span><span class="default">$fp</span><span class="keyword">);<br />
}<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
Also, apparently, the "zip:" wrapper does not allow writing as of PHP/5.3.6. You can read <a href="http://php.net/ziparchive-getstream" rel="nofollow" target="_blank">http://php.net/ziparchive-getstream</a> for further reference since the underlying code is probably the same.</span>
</code></div>
  </div>
 </div>
 <a name="77197"></a>
 <div class="note">
  <strong class='user'>joshualross at gmail dot com</strong>
  <a href="#77197" class="date">19-Aug-2007 12:24</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I had a difficult time finding how to use compress.zlib with an http resource so I thought I would post what I found<br />
<span class="default">&lt;?php<br />
$file </span><span class="keyword">= </span><span class="string">'compress.zlib://<a href="http://www.example.com/myarchive.gz" rel="nofollow" target="_blank">http://www.example.com/myarchive.gz</a>'</span><span class="keyword">;<br />
</span><span class="default">$fr </span><span class="keyword">= </span><span class="default">fopen</span><span class="keyword">(</span><span class="default">$file</span><span class="keyword">, </span><span class="string">'rb'</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span><br />
Per the bugreport I found here (<a href="http://bugs.php.net/bug.php?id=29045" rel="nofollow" target="_blank">http://bugs.php.net/bug.php?id=29045</a>)</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=wrappers.compression&amp;redirect=http://www.php.net/manual/en/wrappers.compression.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=wrappers.compression&amp;redirect=http://www.php.net/manual/en/wrappers.compression.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/wrappers.compression.php">show source</a> |
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