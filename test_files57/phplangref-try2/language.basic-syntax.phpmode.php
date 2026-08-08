<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Escaping from HTML - Manual</title>
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
 <link rel="prev" href="language.basic-syntax.phptags.php" />
 <link rel="next" href="language.basic-syntax.instruction-separation.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/basic-syntax.phpmode" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/language.basic-syntax.phpmode.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="@w{AQDA8F6J}" />
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
 <li class="active"><a href="language.basic-syntax.phpmode.php">Escaping from HTML</a></li>
 <li><a href="language.basic-syntax.instruction-separation.php">Instruction separation</a></li>
 <li><a href="language.basic-syntax.comments.php">Comments</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="language.basic-syntax.instruction-separation.php">Instruction separation<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.basic-syntax.phptags.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />PHP tags</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.basic-syntax.phpmode.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/language.basic-syntax.phpmode.php">Brazilian Portuguese</option>
    <option value="zh/language.basic-syntax.phpmode.php">Chinese (Simplified)</option>
    <option value="fr/language.basic-syntax.phpmode.php">French</option>
    <option value="de/language.basic-syntax.phpmode.php">German</option>
    <option value="ja/language.basic-syntax.phpmode.php">Japanese</option>
    <option value="pl/language.basic-syntax.phpmode.php">Polish</option>
    <option value="ro/language.basic-syntax.phpmode.php">Romanian</option>
    <option value="ru/language.basic-syntax.phpmode.php">Russian</option>
    <option value="fa/language.basic-syntax.phpmode.php">Persian</option>
    <option value="es/language.basic-syntax.phpmode.php">Spanish</option>
    <option value="tr/language.basic-syntax.phpmode.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="language.basic-syntax.phpmode" class="sect1">
   <h2 class="title">Escaping from HTML</h2>
   <p class="para">
    Everything outside of a pair of opening and closing tags is ignored by the
    PHP parser which allows PHP files to have mixed content.  This allows PHP
    to be embedded in HTML documents, for example to create templates.
    <div class="example" id="example-64">
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
&lt;p&gt;This&nbsp;is&nbsp;going&nbsp;to&nbsp;be&nbsp;ignored&nbsp;by&nbsp;PHP&nbsp;and&nbsp;displayed&nbsp;by&nbsp;the&nbsp;browser.&lt;/p&gt;<br /><span style="color: #0000BB">&lt;?php&nbsp;</span><span style="color: #007700">echo&nbsp;</span><span style="color: #DD0000">'While&nbsp;this&nbsp;is&nbsp;going&nbsp;to&nbsp;be&nbsp;parsed.'</span><span style="color: #007700">;&nbsp;</span><span style="color: #0000BB">?&gt;<br /></span>&lt;p&gt;This&nbsp;will&nbsp;also&nbsp;be&nbsp;ignored&nbsp;by&nbsp;PHP&nbsp;and&nbsp;displayed&nbsp;by&nbsp;the&nbsp;browser.&lt;/p&gt;</span>
</code></div>
     </div>

    </div>
    This works as expected, because when the PHP interpreter hits the ?&gt; closing
    tags, it simply starts outputting whatever it finds (except for an
    immediately following newline - see
    <a href="language.basic-syntax.instruction-separation.php" class="link">instruction separation</a>)
    until it hits another opening tag unless in the middle of a conditional
    statement in which case the interpreter will determine the outcome of the
    conditional before making a decision of what which to skip over.
    See the next example.
   </p>
   <p class="para">
    Using structures with conditions
    <div class="example" id="example-65">
     <p><strong>Example #1 Advanced escaping using conditions</strong></p>
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php&nbsp;</span><span style="color: #007700">if&nbsp;(</span><span style="color: #0000BB">$expression&nbsp;</span><span style="color: #007700">==&nbsp;</span><span style="color: #0000BB">true</span><span style="color: #007700">):&nbsp;</span><span style="color: #0000BB">?&gt;<br /></span>&nbsp;&nbsp;This&nbsp;will&nbsp;show&nbsp;if&nbsp;the&nbsp;expression&nbsp;is&nbsp;true.<br /><span style="color: #0000BB">&lt;?php&nbsp;</span><span style="color: #007700">else:&nbsp;</span><span style="color: #0000BB">?&gt;<br /></span>&nbsp;&nbsp;Otherwise&nbsp;this&nbsp;will&nbsp;show.<br /><span style="color: #0000BB">&lt;?php&nbsp;</span><span style="color: #007700">endif;&nbsp;</span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

    </div>
    In this example PHP will skip the blocks where the condition is not met, even
    though they are outside of the PHP open/close tags, PHP skips them according
    to the condition since the PHP interpreter will jump over blocks contained
    within a condition what is not met.
   </p>
   <p class="para">
    For outputting large blocks of text, dropping out of PHP parsing mode is
    generally more efficient than sending all of the text through
     <span class="function"><a href="function.echo.php" class="function">echo</a></span> or  <span class="function"><a href="function.print.php" class="function">print</a></span>.
   </p>
   <p class="para">
    There are four different pairs of opening and closing tags
    which can be used in PHP. Two of those, &lt;?php ?&gt; and
    &lt;script language=&quot;php&quot;&gt; &lt;/script&gt;, are always available.
    The other two are short tags and <span class="productname">ASP</span>
    style tags, and can be turned on and off from the <var class="filename">php.ini</var>
    configuration file. As such, while some people find short tags
    and <span class="productname">ASP</span> style tags convenient, they
    are less portable, and generally not recommended.
    <blockquote class="note"><p><strong class="note">Note</strong>: 
     <p class="para">
      Also note that if you are embedding PHP within XML or XHTML
      you will need to use the &lt;?php ?&gt; tags to remain
      compliant with standards.
     </p>
    </p></blockquote>
   </p>
   <p class="para">
    <div class="example" id="example-66">
     <p><strong>Example #2 PHP Opening and Closing Tags</strong></p>
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
1.&nbsp;&nbsp;<span style="color: #0000BB">&lt;?php&nbsp;</span><span style="color: #007700">echo&nbsp;</span><span style="color: #DD0000">'if&nbsp;you&nbsp;want&nbsp;to&nbsp;serve&nbsp;XHTML&nbsp;or&nbsp;XML&nbsp;documents,&nbsp;do&nbsp;it&nbsp;like&nbsp;this'</span><span style="color: #007700">;&nbsp;</span><span style="color: #0000BB">?&gt;<br /></span><br />2.&nbsp;&nbsp;<span style="color: #0000BB">&lt;script&nbsp;language="php"&gt;<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">echo&nbsp;</span><span style="color: #DD0000">'some&nbsp;editors&nbsp;(like&nbsp;FrontPage)&nbsp;don\'t<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;like&nbsp;processing&nbsp;instructions'</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">&lt;/script&gt;<br /></span><br />3.&nbsp;&nbsp;<span style="color: #0000BB">&lt;?&nbsp;</span><span style="color: #007700">echo&nbsp;</span><span style="color: #DD0000">'this&nbsp;is&nbsp;the&nbsp;simplest,&nbsp;an&nbsp;SGML&nbsp;processing&nbsp;instruction'</span><span style="color: #007700">;&nbsp;</span><span style="color: #0000BB">?&gt;<br /></span>&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: #0000BB">&lt;?=&nbsp;expression&nbsp;?&gt;</span>&nbsp;This&nbsp;is&nbsp;a&nbsp;shortcut&nbsp;for&nbsp;"<span style="color: #0000BB">&lt;?&nbsp;</span><span style="color: #007700">echo&nbsp;</span><span style="color: #0000BB">expression&nbsp;?&gt;</span>"<br /><br />4.&nbsp;&nbsp;&lt;%&nbsp;echo&nbsp;'You&nbsp;may&nbsp;optionally&nbsp;use&nbsp;ASP-style&nbsp;tags';&nbsp;%&gt;<br />&nbsp;&nbsp;&nbsp;&nbsp;&lt;%=&nbsp;$variable;&nbsp;#&nbsp;This&nbsp;is&nbsp;a&nbsp;shortcut&nbsp;for&nbsp;"&lt;%&nbsp;echo&nbsp;.&nbsp;.&nbsp;."&nbsp;%&gt;</span>
</code></div>
     </div>

    </div>
   </p>
   <p class="para">
    While the tags seen in examples one and two are both
    always available, example one is the most commonly
    used, and recommended, of the two.
   </p>
   <p class="para">
    Short tags (example three) are only available when they are
    enabled via the <a href="ini.core.php#ini.short-open-tag" class="link">short_open_tag</a>
    <var class="filename">php.ini</var> configuration file directive, or if PHP was configured
    with the <strong class="option unknown">--enable-short-tags</strong>
 option.
   </p>
   <p class="para">
    <span class="productname">ASP</span> style tags (example four) are only available when
    they are enabled via the <a href="ini.core.php#ini.asp-tags" class="link">asp_tags</a> <var class="filename">php.ini</var>
    configuration file directive.
   </p>
   <p class="para">
    <blockquote class="note"><p><strong class="note">Note</strong>: 
     <p class="para">
      Using short tags should be avoided when developing applications
      or libraries that are meant for redistribution, or deployment on
      PHP servers which are not under your control, because short tags
      may not be supported on the target server.  For portable,
      redistributable code, be sure not to use short tags.
     </p>
    </p></blockquote>
    <blockquote class="note"><p><strong class="note">Note</strong>: 
     <p class="para">
      In PHP 5.2 and earlier, the parser does not allow the
      <em>&lt;?php</em> opening tag to be the only thing in a file.
      This is allowed as of PHP 5.3 provided there are one or more whitespace
      characters after the opening tag.
     </p>
    </p></blockquote>
    <blockquote class="note"><p><strong class="note">Note</strong>: 
     <p class="para">
      Starting with PHP 5.4, short echo tag <em>&lt;?=</em> is always recognized and
      valid, regardless of the <a href="ini.core.php#ini.short-open-tag" class="link">short_open_tag</a> setting.
     </p>
    </p></blockquote>
   </p>
  </div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="language.basic-syntax.instruction-separation.php">Instruction separation<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.basic-syntax.phptags.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />PHP tags</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.basic-syntax.phpmode.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=language.basic-syntax.phpmode&amp;redirect=@w{AQDA8F6J}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.basic-syntax.phpmode&amp;redirect=@w{AQDA8F6J}">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Escaping from HTML</strong>
 </div><div id="allnotes">
 <a name="101166"></a>
 <div class="note">
  <strong class='user'>mike at clove dot com</strong>
  <a href="#101166" class="date">30-Nov-2010 10:30</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
It's possible to write code to create php escapes which can be processed later by substituting \x3f for '?' - as in echo "&lt;\x3fphp echo 'foo'; \x3f&gt;";<br />
<br />
This is useful for creating a template parser which later is rendered by PHP.</span>
</code></div>
  </div>
 </div>
 <a name="99083"></a>
 <div class="note">
  <strong class='user'>quickfur at quickfur dot ath dot cx</strong>
  <a href="#99083" class="date">26-Jul-2010 02:40</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
When the documentation says that the PHP parser ignores everything outside the <span class="default">&lt;?php </span><span class="keyword">... </span><span class="default">?&gt;</span> tags, it means literally EVERYTHING. Including things you normally wouldn't consider "valid", such as the following:<br />
<br />
&lt;html&gt;&lt;body&gt;<br />
&lt;p<span class="default">&lt;?php </span><span class="keyword">if (</span><span class="default">$highlight</span><span class="keyword">): </span><span class="default">?&gt;</span> class="highlight"<span class="default">&lt;?php </span><span class="keyword">endif;</span><span class="default">?&gt;</span>&gt;This is a paragraph.&lt;/p&gt;<br />
&lt;/body&gt;&lt;/html&gt;<br />
<br />
Notice how the PHP code is embedded in the middle of an HTML opening tag. The PHP parser doesn't care that it's in the middle of an opening tag, and doesn't require that it be closed. It also doesn't care that after the closing ?&gt; tag is the end of the HTML opening tag. So, if $highlight is true, then the output will be:<br />
<br />
&lt;html&gt;&lt;body&gt;<br />
&lt;p class="highlight"&gt;This is a paragraph.&lt;/p&gt;<br />
&lt;/body&gt;&lt;/html&gt;<br />
<br />
Otherwise, it will be:<br />
<br />
&lt;html&gt;&lt;body&gt;<br />
&lt;p&gt;This is a paragraph.&lt;/p&gt;<br />
&lt;/body&gt;&lt;/html&gt;<br />
<br />
Using this method, you can have HTML tags with optional attributes, depending on some PHP condition. Extremely flexible and useful!</span>
</code></div>
  </div>
 </div>
 <a name="97113"></a>
 <div class="note">
  <strong class='user'>snor_007 at hotmail dot com</strong>
  <a href="#97113" class="date">01-Apr-2010 04:28</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Playing around with different open and close tags I discovered you can actually mix different style open/close tags<br />
<br />
some examples<br />
<br />
&lt;%<br />
//your php code here<br />
?&gt;<br />
<br />
or<br />
<br />
<span class="default">&lt;script language="php"&gt;<br />
</span><span class="comment">//php code here<br />
</span><span class="keyword">%&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="92643"></a>
 <div class="note">
  <strong class='user'>ravenswd at gmail dot com</strong>
  <a href="#92643" class="date">01-Aug-2009 05:08</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
One aspect of PHP that you need to be careful of, is that ?&gt; will drop you out of PHP code and into HTML even if it appears inside a // comment. (This does not apply to /* */ comments.) This can lead to unexpected results. For example, take this line:<br />
<br />
<span class="default">&lt;?php<br />
&nbsp; $file_contents&nbsp; </span><span class="keyword">= </span><span class="string">'&lt;?php die(); ?&gt;' </span><span class="keyword">. </span><span class="string">"\n"</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
If you try to remove it by turning it into a comment, you get this:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="comment">//&nbsp; $file_contents&nbsp; = '&lt;?php die(); </span><span class="default">?&gt;</span>' . "\n";<br />
?&gt;<br />
<br />
Which results in ' . "\n"; (and whatever is in the lines following it) to be output to your HTML page.<br />
<br />
The cure is to either comment it out using /* */ tags, or re-write the line as:<br />
<br />
<span class="default">&lt;?php<br />
&nbsp; $file_contents&nbsp; </span><span class="keyword">= </span><span class="string">'&lt;' </span><span class="keyword">. </span><span class="string">'?php die(); ?' </span><span class="keyword">. </span><span class="string">'&gt;' </span><span class="keyword">. </span><span class="string">"\n"</span><span class="keyword">;<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="91931"></a>
 <div class="note">
  <strong class='user'>eksith at live dot com</strong>
  <a href="#91931" class="date">01-Jul-2009 11:56</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Even if it's pretty simple to insert echo lines to your PHP, I would storngly advise against it.<br />
<br />
The safest way to output&nbsp; HTML content which may have special chraracters is to remove the HTML from your core code.<br />
<br />
Put them in heredocs instead.<br />
<br />
See the heredoc documentation and comments for more examples.<br />
<br />
If you can remove as much of the HTML as you can from the rest of the PHP code (in terms of printf and echo lines), please do.<br />
<br />
Try to keep your core logic and presentation separate.<br />
<br />
<span class="default">&lt;?php<br />
$html </span><span class="keyword">=&lt;&lt;&lt;HTML<br />
</span><span class="default">&lt;?xml version="1.0" encoding="UTF-8" ?&gt;<br />
<br />
... The rest of your HTML...<br />
<br />
And a PHP </span><span class="keyword">{</span><span class="default">$variable</span><span class="keyword">}</span><span class="default"> here and an array </span><span class="keyword">{</span><span class="default">$arr</span><span class="keyword">[</span><span class="string">'value'</span><span class="keyword">]}</span><span class="default"> there.<br />
<br />
HTML; // End of heredoc<br />
<br />
// Print this HTML<br />
echo $html<br />
?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="90062"></a>
 <div class="note">
  <strong class='user'>Richard Neill</strong>
  <a href="#90062" class="date">03-Apr-2009 07:26</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
WARNING: there is a potentially *nasty* gotcha here. Consider the following:<br />
<br />
&lt;html&gt;&lt;body&gt;&lt;pre&gt;<br />
First line&nbsp; &lt;?/* Comment, inside PHP */?&gt;<br />
Second line<br />
&lt;/pre&gt;&lt;/body&gt;&lt;/html&gt; <br />
<br />
If the comment is immediately followed by newline (and most editors will trim spaces at the ends of lines anyway), then you will NOT get what you expect. <br />
<br />
Expect:<br />
&nbsp; First line<br />
&nbsp; Second Line<br />
<br />
Actually get:<br />
&nbsp; First line&nbsp; Second line&nbsp; <br />
<br />
Now, if you are relying on that newline, for example to terminate a line of Javascript, where the trailing semicolon is optional, watch out!</span>
</code></div>
  </div>
 </div>
 <a name="89863"></a>
 <div class="note">
  <strong class='user'>david dot jarry at gmail dot com</strong>
  <a href="#89863" class="date">26-Mar-2009 03:40</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Shorts tags and ASP tags are unportables and should be avoided.<br />
<br />
&lt;script /&gt; tags are a waste of time and simply inefficient in some simple cases :<br />
&lt;body&gt;<br />
&nbsp; &lt;p style="color: <span class="default">&lt;script language="php"&gt; </span><span class="keyword">echo </span><span class="default">$text_color &lt;/script&gt;</span>;"&gt;<br />
&nbsp; (...) VERY long text (...)<br />
&nbsp; &lt;/p&gt;<br />
&lt;/body&gt;<br />
To render this example in a basic XHTML editor, you need to "echo()" all the content or break the XML rules.<br />
<br />
The solution seems obvious to me : Why not add the shortcut "&lt;?php= ?&gt;" to be used within XML and XHTML documents ?<br />
&lt;?php='example1'?&gt;<br />
&lt;?php=$example2?&gt;</span>
</code></div>
  </div>
 </div>
 <a name="88096"></a>
 <div class="note">
  <strong class='user'>phpcoder at cyberpimp dot awmail dot org</strong>
  <a href="#88096" class="date">09-Jan-2009 11:14</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Some graphical HTML editors (and most web browsers) don't explicitly recognize the <span class="default">&lt;?php ?&gt;</span> tags.&nbsp; When opening a PHP file with a graphical HTML editor to design the page layout, chunks of PHP code can appear as literal text if the PHP code contains a greater-than symbol (&gt;).<br />
<br />
Example:<br />
<br />
&lt;html&gt;<br />
&lt;body&gt;<br />
Unsafe-<span class="default">&lt;?php<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">if (</span><span class="default">4</span><span class="keyword">&gt;</span><span class="default">3</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">"PHP-"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
</span><span class="default">?&gt;</span>embedding<br />
&lt;/body&gt;<br />
&lt;/html&gt;<br />
<br />
When executed, it should display this:<br />
<br />
Unsafe-PHP-embedding<br />
<br />
However, when opened with an HTML editor, the on-screen result might look like this:<br />
<br />
Unsafe-3) { echo "PHP-"; } ?&gt;embedding<br />
<br />
...and further, the PHP code after the great-than operator (&gt;) is at risk of being corrupted by the HTML editor's text formatting algorithms.<br />
<br />
PHP code with greater-than symbols can be safely embedded into HTML by surrounding it with a pair of HTML-style comment delimiters + fake HTML end &amp; start stags, as PHP-style comments.<br />
<br />
Example:<br />
<br />
&lt;html&gt;<br />
&lt;body&gt;<br />
Safe-<span class="default">&lt;?php<br />
</span><span class="comment">/*&gt;&lt;!--*/<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">if (</span><span class="default">4</span><span class="keyword">&gt;</span><span class="default">3</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">"PHP-"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
</span><span class="comment">/*--&gt;&lt;?*/<br />
</span><span class="default">?&gt;</span>embedding<br />
&lt;/body&gt;<br />
&lt;/html&gt;<br />
<br />
When executed, it should display this:<br />
<br />
Safe-PHP-embedding<br />
<br />
And when opened with an HTML editor (or even opened directly with a web browser), it should display this:<br />
<br />
Safe-embedding<br />
<br />
An HTML editor will see the surrounded PHP code as an HTML comment, and (hopefully) leave it as-is.<br />
<br />
Finally, any PHP code with a hard-coded string containing the HTML end-of-comment delimiter (--&gt;) should be reconstructed to be syntactically identical, while avoiding the literal "--&gt;" sequence in the PHP code.<br />
<br />
For example, this:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="comment">/*&gt;&lt;!--*/<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$a </span><span class="keyword">= </span><span class="string">"--&gt;"</span><span class="keyword">;<br />
</span><span class="comment">/*--&gt;&lt;?*/<br />
</span><span class="default">?&gt;<br />
</span><br />
...can safely be changed to any of these:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="comment">/*&gt;&lt;!--*/<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$a </span><span class="keyword">= </span><span class="string">"\55-&gt;"</span><span class="keyword">;<br />
</span><span class="comment">/*--&gt;&lt;?*/<br />
</span><span class="default">?&gt;<br />
</span><br />
<span class="default">&lt;?php<br />
</span><span class="comment">/*&gt;&lt;!--*/<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$a </span><span class="keyword">= </span><span class="string">"--\76"</span><span class="keyword">;<br />
</span><span class="comment">/*--&gt;&lt;?*/<br />
</span><span class="default">?&gt;<br />
</span><br />
<span class="default">&lt;?php<br />
</span><span class="comment">/*&gt;&lt;!--*/<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$a </span><span class="keyword">= </span><span class="string">'--'</span><span class="keyword">.</span><span class="string">'&gt;'</span><span class="keyword">;<br />
</span><span class="comment">/*--&gt;&lt;?*/<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="87955"></a>
 <div class="note">
  <strong class='user'>admin at furutsuzeru dot net</strong>
  <a href="#87955" class="date">02-Jan-2009 08:50</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
These methods are just messy. Short-opening tags and ASP-styled tags are not always enabled on servers. The <span class="default">&lt;script language="php"&gt;&lt;/script&gt;</span> alternative is just out there. You should just use the traditional tag opening:<br />
<br />
&lt;?php?&gt;<br />
<br />
Coding islands, for example:<br />
<br />
<span class="default">&lt;?php<br />
$me </span><span class="keyword">=&nbsp; </span><span class="string">'Pyornide'</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span>&lt;?=$me;?&gt; is happy.<br />
<span class="default">&lt;?php<br />
$me </span><span class="keyword">= </span><span class="default">strtoupper</span><span class="keyword">(</span><span class="default">$me</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span>&lt;?=$me;?&gt; is happier.<br />
<br />
Lead to something along the lines of messy code. Writing your application like this can just prove to be more of an <br />
inconvenience when it comes to maintenance.<br />
<br />
If you have to deal chunks of HTML, then consider having a templating system do the job for you. It is a poor idea to rely on the coding islands method as a template system in any way, and for reasons listed above.</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=language.basic-syntax.phpmode&amp;redirect=@w{AQDA8F6J}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.basic-syntax.phpmode&amp;redirect=@w{AQDA8F6J}">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/language.basic-syntax.phpmode.php">show source</a> |
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