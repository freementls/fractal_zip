<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Comments - Manual</title>
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
 <link rel="prev" href="language.basic-syntax.instruction-separation.php" />
 <link rel="next" href="language.types.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/basic-syntax.comments" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/language.basic-syntax.comments.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="@w{MS5MCSDC}" />
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
 <li><a href="language.basic-syntax.instruction-separation.php">Instruction separation</a></li>
 <li class="active"><a href="language.basic-syntax.comments.php">Comments</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="language.types.php">Types<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.basic-syntax.instruction-separation.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Instruction separation</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.basic-syntax.comments.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/language.basic-syntax.comments.php">Brazilian Portuguese</option>
    <option value="zh/language.basic-syntax.comments.php">Chinese (Simplified)</option>
    <option value="fr/language.basic-syntax.comments.php">French</option>
    <option value="de/language.basic-syntax.comments.php">German</option>
    <option value="ja/language.basic-syntax.comments.php">Japanese</option>
    <option value="pl/language.basic-syntax.comments.php">Polish</option>
    <option value="ro/language.basic-syntax.comments.php">Romanian</option>
    <option value="ru/language.basic-syntax.comments.php">Russian</option>
    <option value="fa/language.basic-syntax.comments.php">Persian</option>
    <option value="es/language.basic-syntax.comments.php">Spanish</option>
    <option value="tr/language.basic-syntax.comments.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="language.basic-syntax.comments" class="sect1">
   <h2 class="title">Comments</h2>
   <p class="para">
    PHP supports &#039;C&#039;, &#039;C++&#039; and Unix shell-style (Perl style) comments. For example:

    <div class="informalexample">
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">echo&nbsp;</span><span style="color: #DD0000">'This&nbsp;is&nbsp;a&nbsp;test'</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;This&nbsp;is&nbsp;a&nbsp;one-line&nbsp;c++&nbsp;style&nbsp;comment<br />&nbsp;&nbsp;&nbsp;&nbsp;/*&nbsp;This&nbsp;is&nbsp;a&nbsp;multi&nbsp;line&nbsp;comment<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;yet&nbsp;another&nbsp;line&nbsp;of&nbsp;comment&nbsp;*/<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">echo&nbsp;</span><span style="color: #DD0000">'This&nbsp;is&nbsp;yet&nbsp;another&nbsp;test'</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">'One&nbsp;Final&nbsp;Test'</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">#&nbsp;This&nbsp;is&nbsp;a&nbsp;one-line&nbsp;shell-style&nbsp;comment<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

    </div>
   </p>
   <p class="simpara">
    The &quot;one-line&quot; comment styles only comment to the end of
    the line or the current block of PHP code, whichever comes first.
    This means that HTML code after <em>// ... ?&gt;</em>
    or <em># ...  ?&gt;</em> WILL be printed:
    ?&gt; breaks out of PHP mode and returns to HTML mode, and
    <em>//</em> or <em>#</em> cannot influence that.
    If the <a href="ini.core.php#ini.asp-tags" class="link">asp_tags</a> configuration directive
    is enabled, it behaves the same with <em>// %&gt;</em> and
    <em># %&gt;</em>.
    However, the <em>&lt;/script&gt;</em> tag doesn&#039;t break out of PHP mode in
    a one-line comment.
   </p>
   <p class="para">
    <div class="informalexample">
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
&lt;h1&gt;This&nbsp;is&nbsp;an&nbsp;<span style="color: #0000BB">&lt;?php&nbsp;</span><span style="color: #FF8000">#&nbsp;echo&nbsp;'simple';</span><span style="color: #0000BB">?&gt;</span>&nbsp;example&lt;/h1&gt;<br />&lt;p&gt;The&nbsp;header&nbsp;above&nbsp;will&nbsp;say&nbsp;'This&nbsp;is&nbsp;an&nbsp;&nbsp;example'.&lt;/p&gt;</span>
</code></div>
     </div>

    </div>
   </p>
   <p class="simpara">
    &#039;C&#039; style comments end at the first <em>*/</em> encountered.
    Make sure you don&#039;t nest &#039;C&#039; style comments.  It is easy to make this
    mistake if you are trying to comment out a large block of code.
   </p>
   <p class="para">
    <div class="informalexample">
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br />&nbsp;</span><span style="color: #FF8000">/*<br />&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;'This&nbsp;is&nbsp;a&nbsp;test';&nbsp;/*&nbsp;This&nbsp;comment&nbsp;will&nbsp;cause&nbsp;a&nbsp;problem&nbsp;*/<br />&nbsp;</span><span style="color: #007700">*/<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

    </div>
   </p>
  </div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="language.types.php">Types<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.basic-syntax.instruction-separation.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Instruction separation</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.basic-syntax.comments.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=language.basic-syntax.comments&amp;redirect=@w{MS5MCSDC}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.basic-syntax.comments&amp;redirect=@w{MS5MCSDC}">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Comments</strong>
 </div><div id="allnotes">
 <a name="105775"></a>
 <div class="note">
  <strong class='user'>team at researchbib dot com</strong>
  <a href="#105775" class="date">13-Sep-2011 07:25</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
when the comment string contains '?&gt;', you should be careful.<br />
<br />
e.g. output code 1= code 2 is different with code 3<br />
1. with //<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="comment">// echo '&lt;?php </span><span class="default">?&gt;</span>';<br />
<br />
?&gt;<br />
<br />
2. with #<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="comment">// echo '&lt;?php </span><span class="default">?&gt;</span>';<br />
<br />
?&gt;<br />
<br />
3. with /* */<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="comment">/* echo '&lt;?php ?&gt;';*/<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="102775"></a>
 <div class="note">
  <strong class='user'>philip-php at dago dot yourweb dot de</strong>
  <a href="#102775" class="date">05-Mar-2011 10:29</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
It's true, comments do not take up PROCESSING time, but they do take some PARSING time in case you are not using a compile cache of some kind.</span>
</code></div>
  </div>
 </div>
 <a name="101424"></a>
 <div class="note">
  <strong class='user'>jballard at natoga dot com</strong>
  <a href="#101424" class="date">15-Dec-2010 02:28</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Comments do NOT take up processing power.<br />
<br />
So, for all the people who argue that comments are undesired because they take up processing power now have no reason to comment ;)<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="comment">// Control<br />
</span><span class="keyword">echo </span><span class="default">microtime</span><span class="keyword">(), </span><span class="string">"&lt;br /&gt;"</span><span class="keyword">; </span><span class="comment">// 0.25163600 1292450508<br />
</span><span class="keyword">echo </span><span class="default">microtime</span><span class="keyword">(), </span><span class="string">"&lt;br /&gt;"</span><span class="keyword">; </span><span class="comment">// 0.25186000 1292450508<br />
<br />
// Test<br />
</span><span class="keyword">echo </span><span class="default">microtime</span><span class="keyword">(), </span><span class="string">"&lt;br /&gt;"</span><span class="keyword">; </span><span class="comment">// 0.25189700 1292450508<br />
# TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST TEST<br />
# .. Above comment repeated 18809 times ..<br />
</span><span class="keyword">echo </span><span class="default">microtime</span><span class="keyword">(), </span><span class="string">"&lt;br /&gt;"</span><span class="keyword">; </span><span class="comment">// 0.25192100 1292450508<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
They take up about the same amount of time (about meaning on a repeated testing, sometimes the difference between the control and the test was negative and sometimes positive).</span>
</code></div>
  </div>
 </div>
 <a name="101289"></a>
 <div class="note">
  <strong class='user'>benny at bennyborn dot de</strong>
  <a href="#101289" class="date">08-Dec-2010 03:16</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
This regex should do the job when trying to parse comments<br />
<br />
(\/\*(.*?)\*\/)|(^|\s+)\/\/(.*?)(\n|$)|(^|\s+)#(.*?)(\n|$)</span>
</code></div>
  </div>
 </div>
 <a name="97866"></a>
 <div class="note">
  <strong class='user'>Wolfsbay at ya dot ru</strong>
  <a href="#97866" class="date">12-May-2010 02:10</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you are using editor with code highlight, it’s much easier to notice error like /* */ */.</span>
</code></div>
  </div>
 </div>
 <a name="77415"></a>
 <div class="note">
  <strong class='user'>theblazingangel at aol dot com</strong>
  <a href="#77415" class="date">28-Aug-2007 03:55</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
it's perhaps not obvious to some, but the following code will cause a parse error! the ?&gt; in //?&gt; is not treated as commented text, this is a result of having to handle code on one line such as <span class="default">&lt;?php </span><span class="keyword">echo </span><span class="string">'something'</span><span class="keyword">; </span><span class="comment">//comment </span><span class="default">?&gt;<br />
</span><br />
<span class="default">&lt;?php<br />
</span><span class="keyword">if(</span><span class="default">1</span><span class="keyword">==</span><span class="default">1</span><span class="keyword">)<br />
{<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">//</span><span class="default">?&gt;<br />
</span>}<br />
?&gt;<br />
<br />
i discovered this "anomally" when i commented out a line of code containing a regex which itself contained ?&gt;, with the // style comment.<br />
e.g. //preg_match('/^(?&gt;c|b)at$/', 'cat', $matches);<br />
will cause an error while commented! using /**/ style comments provides a solution. i don't know about # style comments, i don't ever personally use them.</span>
</code></div>
  </div>
 </div>
 <a name="68128"></a>
 <div class="note">
  <strong class='user'>fun at nybbles dot com</strong>
  <a href="#68128" class="date">13-Jul-2006 10:28</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
a trick I have used in all languages to temporarily block out large sections (usually for test/debug/new-feature purposes), is to set (or define) a var at the top, and use that to conditionally comment the blocks; an added benefit over if(0) (samuli's comment from nov'05) is that u can have several versions or tests running at once, and u dont require cleanup later if u want to keep the blocks in:&nbsp; just reset the var.<br />
<br />
personally, I use this more to conditionally include code for new feature testing, than to block it out,,,, but hey, to each their own :)<br />
<br />
this is also the only safe way I know of to easily nest comments in any language, and great for multi-file use, if the conditional variables are placed in an include :)<br />
<br />
for example, placed at top of file:<br />
<br />
<span class="default">&lt;?php $ver3 </span><span class="keyword">= </span><span class="default">TRUE</span><span class="keyword">;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp; </span><span class="default">$debug2 </span><span class="keyword">= </span><span class="default">FALSE</span><span class="keyword">; <br />
</span><span class="default">?&gt;</span> <br />
<br />
and then deeper inside the file: <br />
<br />
<span class="default">&lt;?php </span><span class="keyword">if (</span><span class="default">$ver3</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; print(</span><span class="string">"This code is included since we are testing version 3"</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; }<br />
</span><span class="default">?&gt;<br />
</span><br />
<span class="default">&lt;?php </span><span class="keyword">if (</span><span class="default">$debug2</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; print(</span><span class="string">"This code is 'commented' out"</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; }<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="67189"></a>
 <div class="note">
  <strong class='user'>mst_NO_SPAM_TO_ME at mstsoft dot com</strong>
  <a href="#67189" class="date">05-Jun-2006 05:38</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
This "comment ends on line break or end of PHP Block" thing can be confusing. I discovered this by accident when working with XML Output from PHP...<br />
<br />
<span class="default">&lt;?PHP<br />
<br />
header</span><span class="keyword">(</span><span class="string">"Content-type: text/xml"</span><span class="keyword">);<br />
<br />
</span><span class="comment">/*<br />
echo "&lt;?xml version=\"1.0\"?&gt;";<br />
echo "&lt;page&gt;multi-line comments work as expected.&lt;/page&gt;";<br />
*/<br />
<br />
//echo "&lt;?xml version=\"1.0\"</span><span class="default">?&gt;</span>";<br />
//echo "&lt;page&gt;single-line comments end php mode and output your code.&lt;/page&gt;";<br />
<br />
?&gt;<br />
<br />
I would expect the comment to work, but there is no parsing in comments so the String suddenly becomes a PHP&nbsp; end-block tag, which is correct reading this documentation.<br />
<br />
cheers,<br />
martin<br />
PS: You even see the behavior in the Syntax highlighting :-)</span>
</code></div>
  </div>
 </div>
 <a name="66693"></a>
 <div class="note">
  <strong class='user'>J Lee</strong>
  <a href="#66693" class="date">25-May-2006 11:39</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
MSpreij (8-May-2005) says&nbsp; /* .. */ overrides //&nbsp; <br />
Anonymous (26-Jan-2006) says // overrides /* .. */<br />
<br />
Actually, both are correct. Once a comment is opened, *everything* is ignored until the end of the comment (or the end of the php block) is reached.<br />
<br />
Thus, if a comment is opened with: <br />
&nbsp;&nbsp; //&nbsp; then /* and */ are "overridden" until after end-of-line <br />
&nbsp;&nbsp; /*&nbsp; then // is "overridden" until after */</span>
</code></div>
  </div>
 </div>
 <a name="60960"></a>
 <div class="note">
  <a href="#60960" class="date">21-Jan-2006 01:46</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
M Spreij wrote, 08-May-2005 08:15...<br />
A nice way to toggle the commenting of blocks of code can be done by mixing the two comment styles:<br />
...<br />
This works because a /* .. */ overrides //. <br />
<br />
The final sentence should be the other way round, i.e.<br />
<br />
This works because a // overrides /* .. */. <br />
(If it didn't the /* .. */ would comment out the code regardless of whether an additional '/' is prefixed to the first line).</span>
</code></div>
  </div>
 </div>
 <a name="58679"></a>
 <div class="note">
  <strong class='user'>samuli dot karevaara at lamk dot fi</strong>
  <a href="#58679" class="date">11-Nov-2005 08:30</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you want to comment out large sections of code (temporarily, usually and hopefully), consider using<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">if (</span><span class="default">0</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; print(</span><span class="string">"This code is 'commented' out"</span><span class="keyword">);<br />
}<br />
</span><span class="default">?&gt;<br />
</span>instead of /* comment block */. Otherwise, as noted here, you will have parse errors if the block that you commented out contains */ somewhere, like in regexp or in another comment.</span>
</code></div>
  </div>
 </div>
 <a name="54296"></a>
 <div class="note">
  <strong class='user'>hcderaad at wanadoo dot nl</strong>
  <a href="#54296" class="date">29-Jun-2005 01:51</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Comments in PHP can be used for several purposes, a very interesting one being that you can generate API documentation directly from them by using PHPDocumentor (<a href="http://www.phpdoc.org/" rel="nofollow" target="_blank">http://www.phpdoc.org/</a>).<br />
<br />
Therefor one has to use a JavaDoc-like comment syntax (conforms to the DocBook DTD), example:<br />
<span class="default">&lt;?php<br />
</span><span class="comment">/**<br />
* The second * here opens the DocBook commentblock, which could later on&lt;br&gt;<br />
* in your development cycle save you a lot of time by preventing you having to rewrite&lt;br&gt;<br />
* major documentation parts to generate some usable form of documentation.<br />
*/<br />
</span><span class="default">?&gt;<br />
</span>Some basic html-like formatting is supported with this (ie &lt;br&gt; tags) to create something of a layout.</span>
</code></div>
  </div>
 </div>
 <a name="52651"></a>
 <div class="note">
  <strong class='user'>M Spreij</strong>
  <a href="#52651" class="date">08-May-2005 12:15</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
A nice way to toggle the commenting of blocks of code can be done by mixing the two comment styles:<br />
<span class="default">&lt;?php<br />
</span><span class="comment">//*<br />
</span><span class="keyword">if (</span><span class="default">$foo</span><span class="keyword">) {<br />
&nbsp; echo </span><span class="default">$bar</span><span class="keyword">;<br />
}<br />
</span><span class="comment">// */<br />
</span><span class="default">sort</span><span class="keyword">(</span><span class="default">$morecode</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span><br />
Now by taking out one / on the first line..<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="comment">/*<br />
if ($foo) {<br />
&nbsp; echo $bar;<br />
}<br />
// */<br />
</span><span class="default">sort</span><span class="keyword">(</span><span class="default">$morecode</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span>..the block is suddenly commented out.<br />
This works because a /* .. */ overrides //. You can even "flip" two blocks, like this:<br />
<span class="default">&lt;?php<br />
</span><span class="comment">//*<br />
</span><span class="keyword">if (</span><span class="default">$foo</span><span class="keyword">) {<br />
&nbsp; echo </span><span class="default">$bar</span><span class="keyword">;<br />
}<br />
</span><span class="comment">/*/<br />
if ($bar) {<br />
&nbsp; echo $foo;<br />
}<br />
// */<br />
</span><span class="default">?&gt;<br />
</span>vs<br />
<span class="default">&lt;?php<br />
</span><span class="comment">/*<br />
if ($foo) {<br />
&nbsp; echo $bar;<br />
}<br />
/*/<br />
</span><span class="keyword">if (</span><span class="default">$bar</span><span class="keyword">) {<br />
&nbsp; echo </span><span class="default">$foo</span><span class="keyword">;<br />
}<br />
</span><span class="comment">// */<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="48204"></a>
 <div class="note">
  <strong class='user'>Steve</strong>
  <a href="#48204" class="date">15-Dec-2004 04:41</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Be careful when commenting out regular expressions.<br />
<br />
E.g. the following causes a parser error.<br />
<br />
I do prefer using # as regexp delimiter anyway so it won't hurt me ;-)<br />
<br />
<span class="default">&lt;?php <br />
<br />
</span><span class="comment">/*<br />
<br />
&nbsp;$f-&gt;setPattern('/^\d.*/</span><span class="keyword">);<br />
<br />
*/<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=language.basic-syntax.comments&amp;redirect=@w{MS5MCSDC}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.basic-syntax.comments&amp;redirect=@w{MS5MCSDC}">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/language.basic-syntax.comments.php">show source</a> |
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