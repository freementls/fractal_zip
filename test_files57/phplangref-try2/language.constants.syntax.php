<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Syntax - Manual</title>
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
 <link rel="index" href="language.constants.php" />
 <link rel="prev" href="language.constants.php" />
 <link rel="next" href="language.constants.predefined.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/constants.syntax" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/language.constants.syntax.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/language.constants.syntax.php" />
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
 <li class="header up"><a href="language.constants.php">Constants</a></li>
 <li class="active"><a href="language.constants.syntax.php">Syntax</a></li>
 <li><a href="language.constants.predefined.php">Magic constants</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="language.constants.predefined.php">Magic constants<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.constants.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Constants</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.constants.syntax.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/language.constants.syntax.php">Brazilian Portuguese</option>
    <option value="zh/language.constants.syntax.php">Chinese (Simplified)</option>
    <option value="fr/language.constants.syntax.php">French</option>
    <option value="de/language.constants.syntax.php">German</option>
    <option value="ja/language.constants.syntax.php">Japanese</option>
    <option value="pl/language.constants.syntax.php">Polish</option>
    <option value="ro/language.constants.syntax.php">Romanian</option>
    <option value="ru/language.constants.syntax.php">Russian</option>
    <option value="fa/language.constants.syntax.php">Persian</option>
    <option value="es/language.constants.syntax.php">Spanish</option>
    <option value="tr/language.constants.syntax.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="language.constants.syntax" class="sect1">
   <h2 class="title">Syntax</h2>
   <p class="simpara">
    You can define a constant by using the 
     <span class="function"><a href="function.define.php" class="function">define()</a></span>-function or by using the 
    <em>const</em> keyword outside a class definition as 
    of PHP 5.3.0. Once a constant is defined, it can never be 
    changed or undefined.
   </p>
   <p class="simpara">
    Only scalar data (<span class="type"><a href="language.types.boolean.php" class="type boolean">boolean</a></span>, <span class="type"><a href="language.types.integer.php" class="type integer">integer</a></span>, 
    <span class="type"><a href="language.types.float.php" class="type float">float</a></span> and <span class="type"><a href="language.types.string.php" class="type string">string</a></span>) can be contained 
    in constants. It is possible to define constants as a 
    <span class="type"><a href="language.types.resource.php" class="type resource">resource</a></span>, but it should be avoided, as it can cause 
    unexpected results.
   </p>
   <p class="simpara">
    You can get the value of a constant by simply specifying its name.
    Unlike with variables, you should <em class="emphasis">not</em> prepend
    a constant with a <em>$</em>.
    You can also use the function  <span class="function"><a href="function.constant.php" class="function">constant()</a></span> to
    read a constant&#039;s value if you wish to obtain the constant&#039;s name
    dynamically. 
    Use  <span class="function"><a href="function.get-defined-constants.php" class="function">get_defined_constants()</a></span> to get a list of 
    all defined constants.
   </p>
   <blockquote class="note"><p><strong class="note">Note</strong>: 
    <span class="simpara">
     Constants and (global) variables are in a different namespace. 
     This implies that for example <strong><code>TRUE</code></strong> and 
     <var class="varname"><var class="varname">$TRUE</var></var> are generally different.
    </span>
   </p></blockquote>
   <p class="simpara">
    If you use an undefined constant, PHP assumes that you mean
    the name of the constant itself, just as if you called it as
    a <span class="type"><a href="language.types.string.php" class="type string">string</a></span> (CONSTANT vs &quot;CONSTANT&quot;).  An error of level
    <a href="ref.errorfunc.php" class="link">E_NOTICE</a> will be issued
    when this happens.  See also the manual entry on why 
    <a href="language.types.array.php#language.types.array.foo-bar" class="link">$foo[bar]</a> is
    wrong (unless you first  <span class="function"><a href="function.define.php" class="function">define()</a></span>
    <em>bar</em> as a constant).  If you simply want to check if a
    constant is set, use the  <span class="function"><a href="function.defined.php" class="function">defined()</a></span> function.
   </p>
   <p class="para">
    These are the differences between constants and variables:
    <ul class="itemizedlist">
     <li class="listitem">
      <span class="simpara">
       Constants do not have a dollar sign (<em>$</em>)
       before them;
      </span>
     </li>
     <li class="listitem">
      <span class="simpara">
       Constants may only be defined using the
        <span class="function"><a href="function.define.php" class="function">define()</a></span> function, not by simple assignment;
      </span>
     </li>
     <li class="listitem">
      <span class="simpara">
       Constants may be defined and accessed anywhere without regard
       to variable scoping rules;
      </span>
     </li>
     <li class="listitem">
      <span class="simpara">
       Constants may not be redefined or undefined once they have been
       set; and
      </span>
     </li>
     <li class="listitem">
      <span class="simpara">
       Constants may only evaluate to scalar values.
       </span>
     </li>
    </ul>
   </p>

   <p class="para">
    <div class="example" id="example-111">
     <p><strong>Example #1 Defining Constants</strong></p>
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br />define</span><span style="color: #007700">(</span><span style="color: #DD0000">"CONSTANT"</span><span style="color: #007700">,&nbsp;</span><span style="color: #DD0000">"Hello&nbsp;world."</span><span style="color: #007700">);<br />echo&nbsp;</span><span style="color: #0000BB">CONSTANT</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;outputs&nbsp;"Hello&nbsp;world."<br /></span><span style="color: #007700">echo&nbsp;</span><span style="color: #0000BB">Constant</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;outputs&nbsp;"Constant"&nbsp;and&nbsp;issues&nbsp;a&nbsp;notice.<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

    </div>
   </p>

   <p class="para">
    <div class="example" id="example-112">
     <p><strong>Example #2 Defining Constants using the <em>const</em> keyword</strong></p>
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #FF8000">//&nbsp;Works&nbsp;as&nbsp;of&nbsp;PHP&nbsp;5.3.0<br /></span><span style="color: #007700">const&nbsp;</span><span style="color: #0000BB">CONSTANT&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'Hello&nbsp;World'</span><span style="color: #007700">;<br /><br />echo&nbsp;</span><span style="color: #0000BB">CONSTANT</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

    </div>
   </p>

   <blockquote class="note"><p><strong class="note">Note</strong>: 
    <p class="para">
     As opposed to defining constants using  <span class="function"><a href="function.define.php" class="function">define()</a></span>,
     constants defined using the <em>const</em> keyword must be
     declared at the top-level scope because they are defined at compile-time.
     This means that they cannot be declared inside functions, loops or
     <em>if</em> statements.
    </p>
   </p></blockquote>

   <p class="simpara">
    See also <a href="language.oop5.constants.php" class="link">Class Constants</a>.
   </p>
  </div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="language.constants.predefined.php">Magic constants<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.constants.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Constants</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.constants.syntax.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=language.constants.syntax&amp;redirect=http://www.php.net/manual/en/language.constants.syntax.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.constants.syntax&amp;redirect=http://www.php.net/manual/en/language.constants.syntax.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Syntax</strong>
 </div><div id="allnotes">
 <a name="107327"></a>
 <div class="note">
  <strong class='user'>0gb dot us at 0gb dot us</strong>
  <a href="#107327" class="date">31-Jan-2012 05:17</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
While most constants are only defined in one namespace, the case-insensitive true, false, and null constants are defined in ALL namespaces. So, this is not valid:<br />
<br />
<span class="default">&lt;?php namespace false</span><span class="keyword">;<br />
const </span><span class="default">ENT_QUOTES </span><span class="keyword">= </span><span class="string">'My value'</span><span class="keyword">;<br />
echo </span><span class="default">ENT_QUOTES</span><span class="keyword">;</span><span class="comment">//Outputs as expected: 'My value'<br />
<br />
</span><span class="keyword">const </span><span class="default">FALSE </span><span class="keyword">= </span><span class="string">'Odd, eh?'</span><span class="keyword">;</span><span class="comment">//FATAL ERROR! </span><span class="default">?&gt;<br />
</span><br />
Fatal error: Cannot redeclare constant 'FALSE' in /Volumes/WebServer/0gb.us/test.php on line 5</span>
</code></div>
  </div>
 </div>
 <a name="107164"></a>
 <div class="note">
  <strong class='user'>timucinbahsi at gmail dot com</strong>
  <a href="#107164" class="date">11-Jan-2012 10:23</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Constant names shouldn't include operators. Otherwise php doesn't take them as part of the constant name and tries to evaluate them:<br />
<br />
<span class="default">&lt;?php<br />
define</span><span class="keyword">(</span><span class="string">"SALARY-WORK"</span><span class="keyword">,</span><span class="default">0.02</span><span class="keyword">); </span><span class="comment">// set the proportion<br />
<br />
</span><span class="default">$salary</span><span class="keyword">=</span><span class="default">SALARY</span><span class="keyword">-</span><span class="default">WORK</span><span class="keyword">*</span><span class="default">$work</span><span class="keyword">; </span><span class="comment">// tries to subtract WORK times $work from SALARY<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="92786"></a>
 <div class="note">
  <strong class='user'>uramihsayibok, gmail, com</strong>
  <a href="#92786" class="date">08-Aug-2009 11:54</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Don't let the comparison between const (in the global context) and define() confuse you: while define() allows expressions as the value, const does not. In that sense it behaves exactly as const (in class context) does.<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="comment">// this works<br />
/**<br />
&nbsp;* Path to the root of the application<br />
&nbsp;*/<br />
</span><span class="default">define</span><span class="keyword">(</span><span class="string">"PATH_ROOT"</span><span class="keyword">, </span><span class="default">dirname</span><span class="keyword">(</span><span class="default">__FILE__</span><span class="keyword">));<br />
<br />
</span><span class="comment">// this does not<br />
/**<br />
&nbsp;* Path to configuration files<br />
&nbsp;*/<br />
</span><span class="keyword">const </span><span class="default">PATH_CONFIG </span><span class="keyword">= </span><span class="default">PATH_ROOT </span><span class="keyword">. </span><span class="string">"/config"</span><span class="keyword">;<br />
<br />
</span><span class="comment">// this does<br />
/**<br />
&nbsp;* Path to configuration files - DEPRECATED, use PATH_CONFIG<br />
&nbsp;*/<br />
</span><span class="keyword">const </span><span class="default">PATH_CONF </span><span class="keyword">= </span><span class="default">PATH_CONFIG</span><span class="keyword">;<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=language.constants.syntax&amp;redirect=http://www.php.net/manual/en/language.constants.syntax.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.constants.syntax&amp;redirect=http://www.php.net/manual/en/language.constants.syntax.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/language.constants.syntax.php">show source</a> |
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