<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Instruction separation - Manual</title>
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
 <link rel="index" href="language.basic-syntax.php" />
 <link rel="prev" href="language.basic-syntax.phpmode.php" />
 <link rel="next" href="language.basic-syntax.comments.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/basic-syntax.instruction-separation" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/language.basic-syntax.instruction-separation.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="@w{4QHQWJMX}" />
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
 <li class="header up"><a href="language.basic-syntax.php">Basic syntax</a></li>
 <li><a href="language.basic-syntax.phptags.php">PHP tags</a></li>
 <li><a href="language.basic-syntax.phpmode.php">Escaping from HTML</a></li>
 <li class="active"><a href="language.basic-syntax.instruction-separation.php">Instruction separation</a></li>
 <li><a href="language.basic-syntax.comments.php">Comments</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="language.basic-syntax.comments.php">Comments<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.basic-syntax.phpmode.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Escaping from HTML</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.basic-syntax.instruction-separation.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/language.basic-syntax.instruction-separation.php">Brazilian Portuguese</option>
    <option value="zh/language.basic-syntax.instruction-separation.php">Chinese (Simplified)</option>
    <option value="fr/language.basic-syntax.instruction-separation.php">French</option>
    <option value="de/language.basic-syntax.instruction-separation.php">German</option>
    <option value="ja/language.basic-syntax.instruction-separation.php">Japanese</option>
    <option value="pl/language.basic-syntax.instruction-separation.php">Polish</option>
    <option value="ro/language.basic-syntax.instruction-separation.php">Romanian</option>
    <option value="ru/language.basic-syntax.instruction-separation.php">Russian</option>
    <option value="fa/language.basic-syntax.instruction-separation.php">Persian</option>
    <option value="es/language.basic-syntax.instruction-separation.php">Spanish</option>
    <option value="tr/language.basic-syntax.instruction-separation.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="language.basic-syntax.instruction-separation" class="sect1">
   <h2 class="title">Instruction separation</h2>
   <p class="para">
    As in C or Perl, PHP requires instructions to be terminated
    with a semicolon at the end of each statement. The closing tag
    of a block of PHP code automatically implies a semicolon; you
    do not need to have a semicolon terminating the last line of a
    PHP block. The closing tag for the block will include the immediately
    trailing newline if one is present.
    <div class="informalexample">
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">echo&nbsp;</span><span style="color: #DD0000">'This&nbsp;is&nbsp;a&nbsp;test'</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">?&gt;<br /></span><br /><span style="color: #0000BB">&lt;?php&nbsp;</span><span style="color: #007700">echo&nbsp;</span><span style="color: #DD0000">'This&nbsp;is&nbsp;a&nbsp;test'&nbsp;</span><span style="color: #0000BB">?&gt;<br /></span><br /><span style="color: #0000BB">&lt;?php&nbsp;</span><span style="color: #007700">echo&nbsp;</span><span style="color: #DD0000">'We&nbsp;omitted&nbsp;the&nbsp;last&nbsp;closing&nbsp;tag'</span><span style="color: #007700">;</span>
</span>
</code></div>
     </div>

    </div>
    <blockquote class="note"><p><strong class="note">Note</strong>: 
     <p class="para">
      The closing tag of a PHP block at the end of a file is optional,
      and in some cases omitting it is helpful when using  <span class="function"><a href="function.include.php" class="function">include</a></span>
      or  <span class="function"><a href="function.require.php" class="function">require</a></span>, so unwanted whitespace will
      not occur at the end of files, and you will still be able to add
      headers to the response later. It is also handy if you use output
      buffering, and would not like to see added unwanted whitespace
      at the end of the parts generated by the included files.
     </p>
    </p></blockquote>
   </p>
  </div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="language.basic-syntax.comments.php">Comments<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.basic-syntax.phpmode.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Escaping from HTML</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.basic-syntax.instruction-separation.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=language.basic-syntax.instruction-separation&amp;redirect=@w{4QHQWJMX}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.basic-syntax.instruction-separation&amp;redirect=@w{4QHQWJMX}">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Instruction separation</strong>
 </div><div id="allnotes">
 <a name="106551"></a>
 <div class="note">
  <strong class='user'>pbarney</strong>
  <a href="#106551" class="date">17-Nov-2011 04:13</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you want to keep the newline after a closing tag in the output, just add a space after the closing tag, and the newline will not be ignored.</span>
</code></div>
  </div>
 </div>
 <a name="85181"></a>
 <div class="note">
  <strong class='user'>Darabos, Edvrd Konrd</strong>
  <a href="#85181" class="date">19-Aug-2008 03:58</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
One newline character (or sequence) is dropped out by the parser after "?&gt;", so you can add the beloved "final newline" to your file after "?&gt;"<br />
<br />
Example for plain text outputs:<br />
<br />
&lt;? foreach($array as $elem){ ?&gt;<br />
Value: &lt;?=$elem?&gt;<br />
<br />
&lt;? } ?&gt;<br />
<br />
(You have to add an extra enter after &lt;?=$elem?&gt; if you want to see a newline in the output.</span>
</code></div>
  </div>
 </div>
 <a name="82993"></a>
 <div class="note">
  <strong class='user'>james dot d dot noyes at lmco dot com</strong>
  <a href="#82993" class="date">05-May-2008 11:42</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you are embedding this in XML, you had better place the ending '?&gt;' there or the XML parser will puke on you.&nbsp; XML parsers do not like processing instructions without end tags, regardless of what PHP does.<br />
<br />
If you're doing HTML like 90% of the world, or if you are going to process/interpret the PHP before the XML parser ever sees it, then you can likely get away with it, but it's still not best practice for XML.</span>
</code></div>
  </div>
 </div>
 <a name="68959"></a>
 <div class="note">
  <strong class='user'>Krishna Srikanth</strong>
  <a href="#68959" class="date">17-Aug-2006 04:44</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Do not mis interpret<br />
<br />
<span class="default">&lt;?php </span><span class="keyword">echo </span><span class="string">'Ending tag excluded'</span><span class="keyword">; <br />
<br />
</span><span class="default">with<br />
<br />
</span><span class="keyword">&lt;?</span><span class="default">php </span><span class="keyword">echo </span><span class="string">'Ending tag excluded'</span><span class="keyword">;<br />
&lt;</span><span class="default">p</span><span class="keyword">&gt;</span><span class="default">But html is still visible</span><span class="keyword">&lt;/</span><span class="default">p</span><span class="keyword">&gt;<br />
<br />
</span><span class="default">The second one would give error</span><span class="keyword">. </span><span class="default">Exclude ?&gt;</span> if you no more html to write after the code.</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=language.basic-syntax.instruction-separation&amp;redirect=@w{4QHQWJMX}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.basic-syntax.instruction-separation&amp;redirect=@w{4QHQWJMX}">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/language.basic-syntax.instruction-separation.php">show source</a> |
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