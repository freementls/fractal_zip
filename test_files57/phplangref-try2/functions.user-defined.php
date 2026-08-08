<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: User-defined functions - Manual</title>
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
 <link rel="index" href="language.functions.php" />
 <link rel="prev" href="language.functions.php" />
 <link rel="next" href="functions.arguments.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/functions.user-defined" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/functions.user-defined.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/functions.user-defined.php" />
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
 <li class="header up"><a href="language.functions.php">Functions</a></li>
 <li class="active"><a href="functions.user-defined.php">User-defined functions</a></li>
 <li><a href="functions.arguments.php">Function arguments</a></li>
 <li><a href="functions.returning-values.php">Returning values</a></li>
 <li><a href="functions.variable-functions.php">Variable functions</a></li>
 <li><a href="functions.internal.php">Internal (built-in) functions</a></li>
 <li><a href="functions.anonymous.php">Anonymous functions</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="functions.arguments.php">Function arguments<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.functions.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Functions</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/functions.user-defined.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/functions.user-defined.php">Brazilian Portuguese</option>
    <option value="zh/functions.user-defined.php">Chinese (Simplified)</option>
    <option value="fr/functions.user-defined.php">French</option>
    <option value="de/functions.user-defined.php">German</option>
    <option value="ja/functions.user-defined.php">Japanese</option>
    <option value="pl/functions.user-defined.php">Polish</option>
    <option value="ro/functions.user-defined.php">Romanian</option>
    <option value="ru/functions.user-defined.php">Russian</option>
    <option value="fa/functions.user-defined.php">Persian</option>
    <option value="es/functions.user-defined.php">Spanish</option>
    <option value="tr/functions.user-defined.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="functions.user-defined" class="sect1">
   <h2 class="title">User-defined functions</h2>
 
   <p class="para">
    A function may be defined using syntax such as the following:
   </p>
   <p class="para">
    <div class="example" id="example-145">
     <p><strong>Example #1 Pseudo code to demonstrate function uses</strong></p>
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">function&nbsp;</span><span style="color: #0000BB">foo</span><span style="color: #007700">(</span><span style="color: #0000BB">$arg_1</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$arg_2</span><span style="color: #007700">,&nbsp;</span><span style="color: #FF8000">/*&nbsp;...,&nbsp;*/&nbsp;</span><span style="color: #0000BB">$arg_n</span><span style="color: #007700">)<br />{<br />&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"Example&nbsp;function.\n"</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;return&nbsp;</span><span style="color: #0000BB">$retval</span><span style="color: #007700">;<br />}<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

    </div>
   </p>
   
   <p class="simpara">
    Any valid PHP code may appear inside a function, even other
    functions and <a href="keyword.class.php" class="link">class</a>
    definitions.
   </p>
   <p class="para">
    Function names follow the same rules as other labels in PHP. A
    valid function name starts with a letter or underscore, followed
    by any number of letters, numbers, or underscores. As a regular
    expression, it would be expressed thus:
    <em>[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*</em>.
   </p>
   <div class="tip"><strong class="tip">Tip</strong><p class="simpara">See also the
<a href="userlandnaming.php" class="xref">Userland Naming Guide</a>.</p></div>
   <p class="simpara">
    Functions need not be defined before they are referenced,
    <em class="emphasis">except</em> when a function is conditionally defined as
    shown in the two examples below.
   </p>
   <p class="para">
    When a function is defined in a conditional manner such as the two
    examples shown. Its definition must be processed <em class="emphasis">prior</em>
    to being called.
   </p>
   <p class="para">
    <div class="example" id="example-146">
     <p><strong>Example #2 Conditional functions</strong></p>
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /><br />$makefoo&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">true</span><span style="color: #007700">;<br /><br /></span><span style="color: #FF8000">/*&nbsp;We&nbsp;can't&nbsp;call&nbsp;foo()&nbsp;from&nbsp;here&nbsp;<br />&nbsp;&nbsp;&nbsp;since&nbsp;it&nbsp;doesn't&nbsp;exist&nbsp;yet,<br />&nbsp;&nbsp;&nbsp;but&nbsp;we&nbsp;can&nbsp;call&nbsp;bar()&nbsp;*/<br /><br /></span><span style="color: #0000BB">bar</span><span style="color: #007700">();<br /><br />if&nbsp;(</span><span style="color: #0000BB">$makefoo</span><span style="color: #007700">)&nbsp;{<br />&nbsp;&nbsp;function&nbsp;</span><span style="color: #0000BB">foo</span><span style="color: #007700">()<br />&nbsp;&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"I&nbsp;don't&nbsp;exist&nbsp;until&nbsp;program&nbsp;execution&nbsp;reaches&nbsp;me.\n"</span><span style="color: #007700">;<br />&nbsp;&nbsp;}<br />}<br /><br /></span><span style="color: #FF8000">/*&nbsp;Now&nbsp;we&nbsp;can&nbsp;safely&nbsp;call&nbsp;foo()<br />&nbsp;&nbsp;&nbsp;since&nbsp;$makefoo&nbsp;evaluated&nbsp;to&nbsp;true&nbsp;*/<br /><br /></span><span style="color: #007700">if&nbsp;(</span><span style="color: #0000BB">$makefoo</span><span style="color: #007700">)&nbsp;</span><span style="color: #0000BB">foo</span><span style="color: #007700">();<br /><br />function&nbsp;</span><span style="color: #0000BB">bar</span><span style="color: #007700">()&nbsp;<br />{<br />&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"I&nbsp;exist&nbsp;immediately&nbsp;upon&nbsp;program&nbsp;start.\n"</span><span style="color: #007700">;<br />}<br /><br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

    </div>
   </p>
   <p class="para">
    <div class="example" id="example-147">
     <p><strong>Example #3 Functions within functions</strong></p>
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">function&nbsp;</span><span style="color: #0000BB">foo</span><span style="color: #007700">()&nbsp;<br />{<br />&nbsp;&nbsp;function&nbsp;</span><span style="color: #0000BB">bar</span><span style="color: #007700">()&nbsp;<br />&nbsp;&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"I&nbsp;don't&nbsp;exist&nbsp;until&nbsp;foo()&nbsp;is&nbsp;called.\n"</span><span style="color: #007700">;<br />&nbsp;&nbsp;}<br />}<br /><br /></span><span style="color: #FF8000">/*&nbsp;We&nbsp;can't&nbsp;call&nbsp;bar()&nbsp;yet<br />&nbsp;&nbsp;&nbsp;since&nbsp;it&nbsp;doesn't&nbsp;exist.&nbsp;*/<br /><br /></span><span style="color: #0000BB">foo</span><span style="color: #007700">();<br /><br /></span><span style="color: #FF8000">/*&nbsp;Now&nbsp;we&nbsp;can&nbsp;call&nbsp;bar(),<br />&nbsp;&nbsp;&nbsp;foo()'s&nbsp;processing&nbsp;has<br />&nbsp;&nbsp;&nbsp;made&nbsp;it&nbsp;accessible.&nbsp;*/<br /><br /></span><span style="color: #0000BB">bar</span><span style="color: #007700">();<br /><br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

    </div>
   </p>
   <p class="para">
    All functions and classes in PHP have the global scope - they can be
    called outside a function even if they were defined inside and vice versa.
   </p>
   <p class="simpara">
    PHP does not support function overloading, nor is it possible to
    undefine or redefine previously-declared functions.
   </p>
   <blockquote class="note"><p><strong class="note">Note</strong>: 
    <span class="simpara">
     Function names are case-insensitive, though it is usually good form
     to call functions as they appear in their declaration.
    </span>
   </p></blockquote>   
   <p class="simpara">
    Both <a href="functions.arguments.php#functions.variable-arg-list" class="link">variable number of
    arguments</a> and <a href="functions.arguments.php#functions.arguments.default" class="link">default
    arguments</a> are supported in functions. See also the function
    references for
     <span class="function"><a href="function.func-num-args.php" class="function">func_num_args()</a></span>,
     <span class="function"><a href="function.func-get-arg.php" class="function">func_get_arg()</a></span>, and
     <span class="function"><a href="function.func-get-args.php" class="function">func_get_args()</a></span> for more information.
   </p>
   
   <p class="para">
    It is possible to call recursive functions in PHP. However avoid recursive
    function/method calls with over 100-200 recursion levels as it can smash
    the stack and cause a termination of the current script.
    <div class="example" id="example-148">
     <p><strong>Example #4 Recursive functions</strong></p>
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">function&nbsp;</span><span style="color: #0000BB">recursion</span><span style="color: #007700">(</span><span style="color: #0000BB">$a</span><span style="color: #007700">)<br />{<br />&nbsp;&nbsp;&nbsp;&nbsp;if&nbsp;(</span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">&lt;&nbsp;</span><span style="color: #0000BB">20</span><span style="color: #007700">)&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"</span><span style="color: #0000BB">$a</span><span style="color: #DD0000">\n"</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">recursion</span><span style="color: #007700">(</span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">+&nbsp;</span><span style="color: #0000BB">1</span><span style="color: #007700">);<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

    </div>
   </p>

  </div><br /><br />
<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=functions.user-defined&amp;redirect=http://www.php.net/manual/en/functions.user-defined.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=functions.user-defined&amp;redirect=http://www.php.net/manual/en/functions.user-defined.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>User-defined functions</strong>
 </div><div id="allnotes">
 <a name="100885"></a>
 <div class="note">
  <strong class='user'>gestioni at gerry dot it</strong>
  <a href="#100885" class="date">12-Nov-2010 10:43</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
<span class="default">&lt;?php <br />
<br />
</span><span class="keyword">function </span><span class="default">A</span><span class="keyword">(){ <br />
&nbsp;&nbsp;&nbsp; echo(</span><span class="string">"Hello, I'm A\n"</span><span class="keyword">); <br />
<br />
} <br />
echo(</span><span class="string">"I just defined A\n"</span><span class="keyword">);<br />
return; <br />
<br />
function </span><span class="default">B</span><span class="keyword">(){ <br />
&nbsp;&nbsp;&nbsp; echo(</span><span class="string">"Hello, I'm B\n"</span><span class="keyword">); <br />
<br />
} <br />
echo(</span><span class="string">"I just defined B\n"</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span><br />
If you include this file, the second echo() is not excuted but B() is still defined</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=functions.user-defined&amp;redirect=http://www.php.net/manual/en/functions.user-defined.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=functions.user-defined&amp;redirect=http://www.php.net/manual/en/functions.user-defined.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/functions.user-defined.php">show source</a> |
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