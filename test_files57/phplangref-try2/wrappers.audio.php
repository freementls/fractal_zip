<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: ogg:// - Manual</title>
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
 <link rel="prev" href="wrappers.rar.php" />
 <link rel="next" href="wrappers.expect.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/wrappers.audio" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/wrappers.audio.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/wrappers.audio.php" />
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
 <li><a href="wrappers.data.php">data://</a></li>
 <li><a href="wrappers.glob.php">glob://</a></li>
 <li><a href="wrappers.phar.php">phar://</a></li>
 <li><a href="wrappers.ssh2.php">ssh2://</a></li>
 <li><a href="wrappers.rar.php">rar://</a></li>
 <li class="active"><a href="wrappers.audio.php">ogg://</a></li>
 <li><a href="wrappers.expect.php">expect://</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="wrappers.expect.php">expect://<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="wrappers.rar.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />rar://</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/wrappers.audio.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/wrappers.audio.php">Brazilian Portuguese</option>
    <option value="zh/wrappers.audio.php">Chinese (Simplified)</option>
    <option value="fr/wrappers.audio.php">French</option>
    <option value="de/wrappers.audio.php">German</option>
    <option value="ja/wrappers.audio.php">Japanese</option>
    <option value="pl/wrappers.audio.php">Polish</option>
    <option value="ro/wrappers.audio.php">Romanian</option>
    <option value="ru/wrappers.audio.php">Russian</option>
    <option value="fa/wrappers.audio.php">Persian</option>
    <option value="es/wrappers.audio.php">Spanish</option>
    <option value="tr/wrappers.audio.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="wrappers.audio" class="refentry">
 <div class="refnamediv">
  <h1 class="refname">ogg://</h1>
  <p class="refpurpose"><span class="refname">ogg://</span> &mdash; <span class="dc-title">Audio streams</span></p>

 </div>

 <div class="refsect1 description" id="refsect1-wrappers.audio-description">
  <h3 class="title">Description</h3>
  <p class="para">
   Files opened for reading via the <var class="filename">ogg://</var> wrapper
   are treated as compressed audio encoded using the <em>OGG/Vorbis</em> codec.
   Similarly, files opened for writing or appending via the
   <var class="filename">ogg://</var> wrapper are writen as compressed audio data.
    <span class="function"><a href="function.stream-get-meta-data.php" class="function">stream_get_meta_data()</a></span>, when used on an <em>OGG/Vorbis</em>
   file opened for reading will return various details about the stream
   including the <em><code class="parameter">vendor</code></em> tag, any included
   <em><code class="parameter">comments</code></em>, the number of
   <em><code class="parameter">channels</code></em>, the sampling <em><code class="parameter">rate</code></em>,
   and the encoding rate range described by:
   <em><code class="parameter">bitrate_lower</code></em>, <em><code class="parameter">bitrate_upper</code></em>,
   <em><code class="parameter">bitrate_nominal</code></em>, and <em><code class="parameter">bitrate_window</code></em>.
  </p>

  <p class="simpara"><var class="filename">ogg://</var> PHP 4.3.0 and up (PECL) </p>
  <blockquote class="note"><p><strong class="note">Note</strong>: 
   <strong>This wrapper is not enabled by default</strong><br />
   <span class="simpara">
    In order to use the <var class="filename">ogg://</var> wrapper you must install
    the <a href="http://pecl.php.net/package/oggvorbis" class="link external">&raquo;&nbsp;OGG/Vorbis</a> extension
    available from <a href="http://pecl.php.net/" class="link external">&raquo;&nbsp;PECL</a>.
   </span>
  </p></blockquote>
 </div>


 <div class="refsect1 usage" id="refsect1-wrappers.audio-usage"> 
  <h3 class="title">Options</h3>
  <ul class="itemizedlist">
   <li class="listitem"><span class="simpara"><var class="filename">ogg://soundfile.ogg</var></span></li>
   <li class="listitem"><span class="simpara"><var class="filename">ogg:///path/to/soundfile.ogg</var></span></li>
   <li class="listitem"><span class="simpara"><var class="filename">ogg://http://www.example.com/path/to/soundstream.ogg</var></span></li>
  </ul>
 </div>
 

 <div class="refsect1 options" id="refsect1-wrappers.audio-options">
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
       <td>Yes</td>
      </tr>

      <tr>
       <td>Allows Appending</td>
       <td>Yes</td>
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
  
  <p class="para">
   <table class="doctable table">
    <caption><strong>Context options</strong></caption>
    
     <thead>
      <tr>
       <th>Name</th>
       <th>Usage</th>
       <th>Default</th>
       <th>Mode</th>
      </tr>

     </thead>

     <tbody class="tbody">
      <tr>
       <td><em>pcm_mode</em></td>
       <td>
        PCM encoding to apply while reading, one of:
        <strong><code>OGGVORBIS_PCM_U8</code></strong>, <strong><code>OGGVORBIS_PCM_S8</code></strong>,
        <strong><code>OGGVORBIS_PCM_U16_BE</code></strong>, <strong><code>OGGVORBIS_PCM_S16_BE</code></strong>,
        <strong><code>OGGVORBIS_PCM_U16_LE</code></strong>, and <strong><code>OGGVORBIS_PCM_S16_LE</code></strong>.
        (8 vs 16 bit, signed or unsigned, big or little <em>endian</em>)
       </td>
       <td>OGGVORBIS_PCM_S16_LE</td>
       <td>Read</td>
      </tr>

      <tr>
       <td><em>rate</em></td>
       <td>
        Sampling rate of input data, expressed in Hz
       </td>
       <td>44100</td>
       <td>Write/Append</td>
      </tr>

      <tr>
       <td><em>bitrate</em></td>
       <td>
        When given as an integer, the fixed bitrate at which to encode. (16000 to 131072)
        When given as a float, the variable bitrate quality to use. (-1.0 to 1.0)
       </td>
       <td>128000</td>
       <td>Write/Append</td>
      </tr>

      <tr>
       <td><em>channels</em></td>
       <td>
        The number of audio channels to encode, typically 1 (Mono), or 2 (Stereo).
        May range as high as 16.
       </td>
       <td>2</td>
       <td>Write/Append</td>
      </tr>

      <tr>
       <td><em>comments</em></td>
       <td>
        An array of string values to encode into the track header.
       </td>
       <td class="empty">&nbsp;</td>
       <td>Write/Append</td>
      </tr>

     </tbody>
    
   </table>

  </p>
 </div>
 

 <div class="refsect1 examples" id="refsect1-wrappers.audio-examples">
  <h3 class="title">Examples</h3>
 </div>


</div><br /><br />
<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=wrappers.audio&amp;redirect=http://www.php.net/manual/en/wrappers.audio.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=wrappers.audio&amp;redirect=http://www.php.net/manual/en/wrappers.audio.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>ogg://</strong>
 </div><div id="allnotes">
 <a name="81204"></a>
 <div class="note">
  <strong class='user'>martin dot leese at stanfordalumni dot org</strong>
  <a href="#81204" class="date">18-Feb-2008 10:04</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Note that although "ogg:" is the wrapper name, the stream is restricted to Ogg files containing a single stream encoded using the Vorbis audio codec.&nbsp; An Ogg container can, in fact, contain multiple streams: audio, video, anything.&nbsp; (Also, the audio streams in an Ogg contaner can use codecs such as FLAC and OggPCM.)&nbsp; The wrapper name is therefore misleading.</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=wrappers.audio&amp;redirect=http://www.php.net/manual/en/wrappers.audio.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=wrappers.audio&amp;redirect=http://www.php.net/manual/en/wrappers.audio.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/wrappers.audio.php">show source</a> |
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