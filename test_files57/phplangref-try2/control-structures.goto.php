<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: goto - Manual</title>
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
 <link rel="index" href="language.control-structures.php" />
 <link rel="prev" href="function.include-once.php" />
 <link rel="next" href="language.functions.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/goto" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/control-structures.goto.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/control-structures.goto.php" />
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
 <li class="header up"><a href="language.control-structures.php">Control Structures</a></li>
 <li><a href="control-structures.intro.php">Introduction</a></li>
 <li><a href="control-structures.if.php">if</a></li>
 <li><a href="control-structures.else.php">else</a></li>
 <li><a href="control-structures.elseif.php">elseif/else if</a></li>
 <li><a href="control-structures.alternative-syntax.php">Alternative syntax for control structures</a></li>
 <li><a href="control-structures.while.php">while</a></li>
 <li><a href="control-structures.do.while.php">do-while</a></li>
 <li><a href="control-structures.for.php">for</a></li>
 <li><a href="control-structures.foreach.php">foreach</a></li>
 <li><a href="control-structures.break.php">break</a></li>
 <li><a href="control-structures.continue.php">continue</a></li>
 <li><a href="control-structures.switch.php">switch</a></li>
 <li><a href="control-structures.declare.php">declare</a></li>
 <li><a href="function.return.php">return</a></li>
 <li><a href="function.require.php">require</a></li>
 <li><a href="function.include.php">include</a></li>
 <li><a href="function.require-once.php">require_<span class="w"> </span>once</a></li>
 <li><a href="function.include-once.php">include_<span class="w"> </span>once</a></li>
 <li class="active"><a href="control-structures.goto.php">goto</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="language.functions.php">Functions<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="function.include-once.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />include_once</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/control-structures.goto.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/control-structures.goto.php">Brazilian Portuguese</option>
    <option value="zh/control-structures.goto.php">Chinese (Simplified)</option>
    <option value="fr/control-structures.goto.php">French</option>
    <option value="de/control-structures.goto.php">German</option>
    <option value="ja/control-structures.goto.php">Japanese</option>
    <option value="pl/control-structures.goto.php">Polish</option>
    <option value="ro/control-structures.goto.php">Romanian</option>
    <option value="ru/control-structures.goto.php">Russian</option>
    <option value="fa/control-structures.goto.php">Persian</option>
    <option value="es/control-structures.goto.php">Spanish</option>
    <option value="tr/control-structures.goto.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="control-structures.goto" class="sect1">
 <h2 class="title"><em>goto</em></h2>
 <p class="verinfo">(PHP 5 &gt;= 5.3.0)</p>
 <p class="para">
  The <em>goto</em> operator can be used to jump to another
  section in the program.  The target point is specified by a label
  followed by a colon, and the instruction is given as
  <em>goto</em> followed by the desired target label.  This
  is not a full unrestricted <em>goto</em>.  The target
  label must be within the same file and context, meaning that you cannot jump
  out of a function or method, nor can you jump into one.  You also
  cannot jump into any sort of loop or switch structure.  You may jump
  out of these, and a common use is to use a <em>goto</em>
  in place of a multi-level <em>break</em>.
 </p>
 <p class="para">
  <div class="example" id="example-142">
   <p><strong>Example #1 <em>goto</em> example</strong></p>
   <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">goto&nbsp;</span><span style="color: #0000BB">a</span><span style="color: #007700">;<br />echo&nbsp;</span><span style="color: #DD0000">'Foo'</span><span style="color: #007700">;<br />&nbsp;<br /></span><span style="color: #0000BB">a</span><span style="color: #007700">:<br />echo&nbsp;</span><span style="color: #DD0000">'Bar'</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
   </div>

   <div class="example-contents"><p>The above example will output:</p></div>
   <div class="example-contents screen">
<div class="cdata"><pre>
Bar
</pre></div>
   </div>
  </div>
 </p>
 <p class="para">
  <div class="example" id="example-143">
   <p><strong>Example #2 <em>goto</em> loop example</strong></p>
   <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">for(</span><span style="color: #0000BB">$i</span><span style="color: #007700">=</span><span style="color: #0000BB">0</span><span style="color: #007700">,</span><span style="color: #0000BB">$j</span><span style="color: #007700">=</span><span style="color: #0000BB">50</span><span style="color: #007700">;&nbsp;</span><span style="color: #0000BB">$i</span><span style="color: #007700">&lt;</span><span style="color: #0000BB">100</span><span style="color: #007700">;&nbsp;</span><span style="color: #0000BB">$i</span><span style="color: #007700">++)&nbsp;{<br />&nbsp;&nbsp;while(</span><span style="color: #0000BB">$j</span><span style="color: #007700">--)&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;if(</span><span style="color: #0000BB">$j</span><span style="color: #007700">==</span><span style="color: #0000BB">17</span><span style="color: #007700">)&nbsp;goto&nbsp;</span><span style="color: #0000BB">end</span><span style="color: #007700">;&nbsp;<br />&nbsp;&nbsp;}&nbsp;&nbsp;<br />}<br />echo&nbsp;</span><span style="color: #DD0000">"i&nbsp;=&nbsp;</span><span style="color: #0000BB">$i</span><span style="color: #DD0000">"</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">end</span><span style="color: #007700">:<br />echo&nbsp;</span><span style="color: #DD0000">'j&nbsp;hit&nbsp;17'</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
   </div>

   <div class="example-contents"><p>The above example will output:</p></div>
   <div class="example-contents screen">
<div class="cdata"><pre>
j hit 17
</pre></div>
   </div>
  </div>
 </p>
 <p class="para">
  <div class="example" id="example-144">
   <p><strong>Example #3 This will not work</strong></p>
   <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">goto&nbsp;</span><span style="color: #0000BB">loop</span><span style="color: #007700">;<br />for(</span><span style="color: #0000BB">$i</span><span style="color: #007700">=</span><span style="color: #0000BB">0</span><span style="color: #007700">,</span><span style="color: #0000BB">$j</span><span style="color: #007700">=</span><span style="color: #0000BB">50</span><span style="color: #007700">;&nbsp;</span><span style="color: #0000BB">$i</span><span style="color: #007700">&lt;</span><span style="color: #0000BB">100</span><span style="color: #007700">;&nbsp;</span><span style="color: #0000BB">$i</span><span style="color: #007700">++)&nbsp;{<br />&nbsp;&nbsp;while(</span><span style="color: #0000BB">$j</span><span style="color: #007700">--)&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">loop</span><span style="color: #007700">:<br />&nbsp;&nbsp;}<br />}<br />echo&nbsp;</span><span style="color: #DD0000">"</span><span style="color: #0000BB">$i</span><span style="color: #DD0000">&nbsp;=&nbsp;</span><span style="color: #0000BB">$i</span><span style="color: #DD0000">"</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
   </div>

   <div class="example-contents"><p>The above example will output:</p></div>
   <div class="example-contents screen">
<div class="cdata"><pre>
Fatal error: &#039;goto&#039; into loop or switch statement is disallowed in
script on line 2
</pre></div>
   </div>
  </div>
 </p>
 <blockquote class="note"><p><strong class="note">Note</strong>: 
  <p class="para">
   The <em>goto</em> operator is available as of PHP 5.3.
  </p>
 </p></blockquote>
 <p class="para">
  <div class="mediaobject">
   
   <div class="imageobject">
    <img src="images/0baa1b9fae6aec55bbb73037f3016001-xkcd-goto.png" alt="What's the worse thing that could happen if you use goto?" width="740" height="201" />
   </div>
  </div>
  Image courtesy of <a href="http://xkcd.com/292" class="link external">&raquo;&nbsp;xkcd</a>
 </p>
</div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="language.functions.php">Functions<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="function.include-once.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />include_once</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/control-structures.goto.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=control-structures.goto&amp;redirect=http://www.php.net/manual/en/control-structures.goto.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=control-structures.goto&amp;redirect=http://www.php.net/manual/en/control-structures.goto.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>goto</strong>
 </div><div id="allnotes">
 <a name="109027"></a>
 <div class="note">
  <strong class='user'>sixoclockish at gmail dot com</strong>
  <a href="#109027" class="date">14-Jun-2012 07:58</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
You are also allowed to jump backwards with a goto statement. To run a block of goto as one block is as follows:<br />
example has a prefix of iw_ to keep label groups structured and an extra underscore to do a backwards goto.<br />
<br />
Note the `iw_end_gt` to get out of the labels area<br />
<br />
<span class="default">&lt;?php<br />
&nbsp;&nbsp;&nbsp; $link </span><span class="keyword">= </span><span class="default">true</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp;&nbsp; if ( </span><span class="default">$link </span><span class="keyword">) </span><span class="default">goto iw_link_begin</span><span class="keyword">; <br />
&nbsp;&nbsp;&nbsp; if(</span><span class="default">false</span><span class="keyword">) </span><span class="default">iw__link_begin</span><span class="keyword">:<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; if ( </span><span class="default">$link </span><span class="keyword">) </span><span class="default">goto iw_link_text</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; if(</span><span class="default">false</span><span class="keyword">) </span><span class="default">iw__link_text</span><span class="keyword">:<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; if ( </span><span class="default">$link </span><span class="keyword">) </span><span class="default">goto iw_link_end</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; if(</span><span class="default">false</span><span class="keyword">) </span><span class="default">iw__link_end</span><span class="keyword">:<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; </span><span class="default">goto iw_end_gt</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; if (</span><span class="default">false</span><span class="keyword">) </span><span class="default">iw_link_begin</span><span class="keyword">:<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">'&lt;a href="#"&gt;'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">goto iw__link_begin</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; if (</span><span class="default">false</span><span class="keyword">) </span><span class="default">iw_link_text</span><span class="keyword">:<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">'Sample Text'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">goto iw__link_text</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; if (</span><span class="default">false</span><span class="keyword">) </span><span class="default">iw_link_end</span><span class="keyword">:<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">'&lt;/a&gt;'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">goto iw__link_end</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; </span><span class="default">iw_end_gt</span><span class="keyword">:<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="108217"></a>
 <div class="note">
  <strong class='user'>roman4work at gmail dot com</strong>
  <a href="#108217" class="date">09-Apr-2012 03:36</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
since label executes all the time even if you don't use goto label;<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">if (</span><span class="default">false</span><span class="keyword">)<br />
&nbsp;&nbsp; </span><span class="default">goto label<br />
<br />
label </span><span class="keyword">: <br />
&nbsp;&nbsp; echo </span><span class="string">"label triggered"</span><span class="keyword">;<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
Will output: label triggered<br />
<br />
I use labels like this<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">...</span><span class="default">some code</span><span class="keyword">...<br />
<br />
if (</span><span class="default">false</span><span class="keyword">) {<br />
&nbsp;&nbsp; </span><span class="default">label1 </span><span class="keyword">: <br />
&nbsp;&nbsp; &nbsp;&nbsp; echo </span><span class="string">"label 1 triggered"</span><span class="keyword">;<br />
}<br />
<br />
if (</span><span class="default">false</span><span class="keyword">) {<br />
<br />
&nbsp;&nbsp; </span><span class="default">label2 </span><span class="keyword">: <br />
&nbsp;&nbsp; &nbsp;&nbsp; echo </span><span class="string">"label 2 triggered"</span><span class="keyword">;<br />
}<br />
<br />
if (</span><span class="default">false</span><span class="keyword">) {<br />
<br />
&nbsp;&nbsp; </span><span class="default">label3 </span><span class="keyword">: <br />
&nbsp;&nbsp; &nbsp;&nbsp; echo </span><span class="string">"label 3 triggered"</span><span class="keyword">;<br />
}<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
It will never output unless you use "goto &lt;label&gt;".</span>
</code></div>
  </div>
 </div>
 <a name="106719"></a>
 <div class="note">
  <strong class='user'>f at francislacroix dot info</strong>
  <a href="#106719" class="date">30-Nov-2011 01:11</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The goto operator CAN be evaluated with eval, provided the label is in the eval'd code:<br />
<br />
<span class="default">&lt;?php<br />
a</span><span class="keyword">: eval(</span><span class="string">"goto a;"</span><span class="keyword">); </span><span class="comment">// undefined label 'a'<br />
</span><span class="keyword">eval(</span><span class="string">"a: goto a;"</span><span class="keyword">); </span><span class="comment">// works<br />
</span><span class="default">?&gt;<br />
</span><br />
It's because PHP does not consider the eval'd code, containing the label, to be in the same "file" as the goto statement.</span>
</code></div>
  </div>
 </div>
 <a name="106323"></a>
 <div class="note">
  <strong class='user'>Ray dot Paseur at Gmail dot com</strong>
  <a href="#106323" class="date">27-Oct-2011 06:59</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
You cannot implement a Fortran-style "computed GOTO" in PHP because the label cannot be a variable. See: <a href="http://en.wikipedia.org/wiki/Considered_harmful" rel="nofollow" target="_blank">http://en.wikipedia.org/wiki/Considered_harmful</a><br />
<br />
<span class="default">&lt;?php </span><span class="comment">// RAY_goto.php<br />
</span><span class="default">error_reporting</span><span class="keyword">(</span><span class="default">E_ALL</span><span class="keyword">);<br />
<br />
</span><span class="comment">// DEMONSTRATE THAT THE GOTO LABEL IS CASE-SENSITIVE<br />
<br />
</span><span class="default">goto a</span><span class="keyword">;<br />
echo </span><span class="string">'Foo'</span><span class="keyword">;<br />
</span><span class="default">a</span><span class="keyword">: echo </span><span class="string">'Bar'</span><span class="keyword">;<br />
<br />
</span><span class="default">goto A</span><span class="keyword">;<br />
echo </span><span class="string">'Foo'</span><span class="keyword">;<br />
</span><span class="default">A</span><span class="keyword">: echo </span><span class="string">'Baz'</span><span class="keyword">;<br />
<br />
</span><span class="comment">// CAN THE GOTO LABEL BE A VARIABLE?<br />
<br />
</span><span class="default">$a </span><span class="keyword">= </span><span class="string">'abc'</span><span class="keyword">;<br />
</span><span class="default">goto $a</span><span class="keyword">; </span><span class="comment">// NOPE: PARSE ERROR<br />
</span><span class="keyword">echo </span><span class="string">'Foo'</span><span class="keyword">;<br />
</span><span class="default">abc</span><span class="keyword">: echo </span><span class="string">'Boom'</span><span class="keyword">;<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="104790"></a>
 <div class="note">
  <strong class='user'>contact at xpertmailer dot com</strong>
  <a href="#104790" class="date">07-Jul-2011 06:08</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
goto operator can NOT be evaluate with eval()</span>
</code></div>
  </div>
 </div>
 <a name="100284"></a>
 <div class="note">
  <strong class='user'>tweston at coldsteelstudios dot com</strong>
  <a href="#100284" class="date">05-Oct-2010 08:12</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
In a challenge of myself for a college class I decided to use the goto to remove all while loops from my code. It was actually easy, and AS FAST as While loops.<br />
<br />
<span class="default">&lt;?PHP<br />
$start </span><span class="keyword">= </span><span class="default">microtime</span><span class="keyword">(</span><span class="default">true</span><span class="keyword">);<br />
</span><span class="default">$i </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">;<br />
</span><span class="default">StartOfLoop</span><span class="keyword">:<br />
</span><span class="default">$i</span><span class="keyword">++;<br />
if(</span><span class="default">$i </span><span class="keyword">&lt; </span><span class="default">1000000</span><span class="keyword">) </span><span class="default">goto StartOfLoop</span><span class="keyword">;<br />
<br />
echo </span><span class="default">microtime</span><span class="keyword">(</span><span class="default">true</span><span class="keyword">) - </span><span class="default">$start</span><span class="keyword">.</span><span class="default">PHP_EOL</span><span class="keyword">;<br />
<br />
</span><span class="default">$start </span><span class="keyword">= </span><span class="default">microtime</span><span class="keyword">(</span><span class="default">true</span><span class="keyword">);<br />
</span><span class="default">$i </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">;<br />
while(</span><span class="default">$i </span><span class="keyword">&lt; </span><span class="default">1000000</span><span class="keyword">){<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$i</span><span class="keyword">++;<br />
}<br />
<br />
echo </span><span class="default">microtime</span><span class="keyword">(</span><span class="default">true</span><span class="keyword">) - </span><span class="default">$start</span><span class="keyword">.</span><span class="default">PHP_EOL</span><span class="keyword">;<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="92763"></a>
 <div class="note">
  <strong class='user'>chrisstocktonaz at gmail dot com</strong>
  <a href="#92763" class="date">07-Aug-2009 03:03</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Remember if you are not a fan of wild labels hanging around you are free to use braces in this construct creating a slightly cleaner look. Labels also are always executed and do not need to be called to have their associated code block ran. A purposeless example is below.<br />
<br />
<span class="default">&lt;?php<br />
<br />
$headers </span><span class="keyword">= Array(</span><span class="string">'subject'</span><span class="keyword">, </span><span class="string">'bcc'</span><span class="keyword">, </span><span class="string">'to'</span><span class="keyword">, </span><span class="string">'cc'</span><span class="keyword">, </span><span class="string">'date'</span><span class="keyword">, </span><span class="string">'sender'</span><span class="keyword">);<br />
</span><span class="default">$position </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">;<br />
<br />
</span><span class="default">hIterator</span><span class="keyword">: {<br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$c </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="default">$headers</span><span class="keyword">[</span><span class="default">$position</span><span class="keyword">] . </span><span class="default">PHP_EOL</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">cIterator</span><span class="keyword">: {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">' ' </span><span class="keyword">. </span><span class="default">$headers</span><span class="keyword">[</span><span class="default">$position</span><span class="keyword">][</span><span class="default">$c</span><span class="keyword">] . </span><span class="default">PHP_EOL</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if(!isset(</span><span class="default">$headers</span><span class="keyword">[</span><span class="default">$position</span><span class="keyword">][++</span><span class="default">$c</span><span class="keyword">])) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">goto cIteratorExit</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">goto cIterator</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">cIteratorExit</span><span class="keyword">: {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if(isset(</span><span class="default">$headers</span><span class="keyword">[++</span><span class="default">$position</span><span class="keyword">])) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">goto hIterator</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=control-structures.goto&amp;redirect=http://www.php.net/manual/en/control-structures.goto.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=control-structures.goto&amp;redirect=http://www.php.net/manual/en/control-structures.goto.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/control-structures.goto.php">show source</a> |
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