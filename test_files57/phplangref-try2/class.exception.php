<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Exception - Manual</title>
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
 <link rel="index" href="reserved.exceptions.php" />
 <link rel="prev" href="reserved.exceptions.php" />
 <link rel="next" href="exception.construct.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/exception" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="alternate" href="/manual/en/feeds/class.exception.atom" type="application/atom+xml" />
 <link rel="canonical" href="http://php.net/manual/en/class.exception.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/class.exception.php" />
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
 <li class="header up"><a href="reserved.exceptions.php">Predefined Exceptions</a></li>
 <li class="active"><a href="class.exception.php">Exception</a></li>
 <li><a href="class.errorexception.php">ErrorException</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="exception.construct.php">Exception::__construct<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="reserved.exceptions.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Predefined Exceptions</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/class.exception.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/class.exception.php">Brazilian Portuguese</option>
    <option value="zh/class.exception.php">Chinese (Simplified)</option>
    <option value="fr/class.exception.php">French</option>
    <option value="de/class.exception.php">German</option>
    <option value="ja/class.exception.php">Japanese</option>
    <option value="pl/class.exception.php">Polish</option>
    <option value="ro/class.exception.php">Romanian</option>
    <option value="ru/class.exception.php">Russian</option>
    <option value="fa/class.exception.php">Persian</option>
    <option value="es/class.exception.php">Spanish</option>
    <option value="tr/class.exception.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="class.exception" class="reference">
 <h1 class="title">Exception</h1>
 
 
 <div class="partintro"><p class="verinfo">(PHP 5 &gt;= 5.1.0)</p>
 

  <div class="section" id="exception.intro">
   <h2 class="title">Introduction</h2>
   <p class="para">
    <span class="ooclass"><strong class="classname">Exception</strong></span> is the base class for
    all Exceptions.
   </p>
  </div>

 
  <div class="section" id="exception.synopsis">
   <h2 class="title">Class synopsis</h2>
 

   <div class="classsynopsis">
    <div class="ooclass"></div>
 

    <div class="classsynopsisinfo">
     <span class="ooclass">
      <strong class="classname">Exception</strong>
     </span>
     {</div>

 
    <div class="classsynopsisinfo classsynopsisinfo_comment">/* Properties */</div>
    <div class="fieldsynopsis">
     <span class="modifier">protected</span>
     <span class="type">string</span>
      <var class="varname"><a href="class.exception.php#exception.props.message">$<var class="varname">message</var></a></var>
    ;</div>

    <div class="fieldsynopsis">
     <span class="modifier">protected</span>
     <span class="type">int</span>
      <var class="varname"><a href="class.exception.php#exception.props.code">$<var class="varname">code</var></a></var>
    ;</div>

    <div class="fieldsynopsis">
     <span class="modifier">protected</span>
     <span class="type">string</span>
      <var class="varname"><a href="class.exception.php#exception.props.file">$<var class="varname">file</var></a></var>
    ;</div>

    <div class="fieldsynopsis">
     <span class="modifier">protected</span>
     <span class="type">int</span>
      <var class="varname"><a href="class.exception.php#exception.props.line">$<var class="varname">line</var></a></var>
    ;</div>


    <div class="classsynopsisinfo classsynopsisinfo_comment">/* Methods */</div>
    <div class="constructorsynopsis dc-description">
   <span class="modifier">public</span>  <span class="methodname"><a href="exception.construct.php" class="methodname">__construct</a></span>
    ([ <span class="methodparam"><span class="type">string</span> <code class="parameter">$message</code><span class="initializer"> = &quot;&quot;</span></span>
   [, <span class="methodparam"><span class="type">int</span> <code class="parameter">$code</code><span class="initializer"> = 0</span></span>
   [, <span class="methodparam"><span class="type"><a href="class.exception.php" class="type Exception">Exception</a></span> <code class="parameter">$previous</code><span class="initializer"> = <strong><code>NULL</code></strong></span></span>
  ]]] )</div>

    <div class="methodsynopsis dc-description">
   <span class="modifier">final</span> <span class="modifier">public</span> <span class="type">string</span> <span class="methodname"><a href="exception.getmessage.php" class="methodname">getMessage</a></span>
    ( <span class="methodparam">void</span>
   )</div>
<div class="methodsynopsis dc-description">
   <span class="modifier">final</span> <span class="modifier">public</span> <span class="type">Exception</span> <span class="methodname"><a href="exception.getprevious.php" class="methodname">getPrevious</a></span>
    ( <span class="methodparam">void</span>
   )</div>
<div class="methodsynopsis dc-description">
   <span class="modifier">final</span> <span class="modifier">public</span> <span class="type">mixed</span> <span class="methodname"><a href="exception.getcode.php" class="methodname">getCode</a></span>
    ( <span class="methodparam">void</span>
   )</div>
<div class="methodsynopsis dc-description">
   <span class="modifier">final</span> <span class="modifier">public</span> <span class="type">string</span> <span class="methodname"><a href="exception.getfile.php" class="methodname">getFile</a></span>
    ( <span class="methodparam">void</span>
   )</div>
<div class="methodsynopsis dc-description">
   <span class="modifier">final</span> <span class="modifier">public</span> <span class="type">int</span> <span class="methodname"><a href="exception.getline.php" class="methodname">getLine</a></span>
    ( <span class="methodparam">void</span>
   )</div>
<div class="methodsynopsis dc-description">
   <span class="modifier">final</span> <span class="modifier">public</span> <span class="type">array</span> <span class="methodname"><a href="exception.gettrace.php" class="methodname">getTrace</a></span>
    ( <span class="methodparam">void</span>
   )</div>
<div class="methodsynopsis dc-description">
   <span class="modifier">final</span> <span class="modifier">public</span> <span class="type">string</span> <span class="methodname"><a href="exception.gettraceasstring.php" class="methodname">getTraceAsString</a></span>
    ( <span class="methodparam">void</span>
   )</div>
<div class="methodsynopsis dc-description">
   <span class="modifier">public</span> <span class="type">string</span>  <span class="methodname"><a href="exception.tostring.php" class="methodname">__toString</a></span>
    ( <span class="methodparam">void</span>
   )</div>
<div class="methodsynopsis dc-description">
   <span class="modifier">final</span> <span class="modifier">private</span> <span class="type">void</span> <span class="methodname"><a href="exception.clone.php" class="methodname">__clone</a></span>
    ( <span class="methodparam">void</span>
   )</div>

   }</div>
 

 
  </div>
 

  <div class="section" id="exception.props">
   <h2 class="title">Properties</h2>
   <dl>

    <dt id="exception.props.message">
     <span class="term"><var class="varname"><var class="varname">message</var></var></span>
     <dd>

      <p class="para">The exception message</p>
     </dd>

    </dt>

    <dt id="exception.props.code">
     <span class="term"><var class="varname"><var class="varname">code</var></var></span>
     <dd>

      <p class="para">The exception code</p>
     </dd>

    </dt>

    <dt id="exception.props.file">
     <span class="term"><var class="varname"><var class="varname">file</var></var></span>
     <dd>

      <p class="para">The filename where the exception was created</p>
     </dd>

    </dt>

    <dt id="exception.props.line">
     <span class="term"><var class="varname"><var class="varname">line</var></var></span>
     <dd>

      <p class="para">The line where the exception was created</p>
     </dd>

    </dt>

   </dl>

  </div>

 
 </div>
 
 



 



 



 



 



 



 



 



 



 



 



 



 







 



 



 



 



 



 



 
<h2>Table of Contents</h2><ul class="chunklist chunklist_reference"><li><a href="exception.construct.php">Exception::__construct</a> — Construct the exception</li><li><a href="exception.getmessage.php">Exception::getMessage</a> — Gets the Exception message</li><li><a href="exception.getprevious.php">Exception::getPrevious</a> — Returns previous Exception</li><li><a href="exception.getcode.php">Exception::getCode</a> — Gets the Exception code</li><li><a href="exception.getfile.php">Exception::getFile</a> — Gets the file in which the exception occurred</li><li><a href="exception.getline.php">Exception::getLine</a> — Gets the line in which the exception occurred</li><li><a href="exception.gettrace.php">Exception::getTrace</a> — Gets the stack trace</li><li><a href="exception.gettraceasstring.php">Exception::getTraceAsString</a> — Gets the stack trace as a string</li><li><a href="exception.tostring.php">Exception::__toString</a> — String representation of the exception</li><li><a href="exception.clone.php">Exception::__clone</a> — Clone the exception</li></ul>
</div>
<br /><br />
<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=class.exception&amp;redirect=http://www.php.net/manual/en/class.exception.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=class.exception&amp;redirect=http://www.php.net/manual/en/class.exception.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Exception</strong>
 </div><div id="allnotes">
 <a name="108423"></a>
 <div class="note">
  <strong class='user'>derak dot kilgo at esc13 dot txed dot net</strong>
  <a href="#108423" class="date">25-Apr-2012 03:37</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The 'previous' parameter first appeared in php &gt; 5.3.0</span>
</code></div>
  </div>
 </div>
 <a name="84617"></a>
 <div class="note">
  <strong class='user'>taras dot dot dot di at gmail dot com</strong>
  <a href="#84617" class="date">22-Jul-2008 04:08</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Note that the default message is an empty string, and the default code is zero</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=class.exception&amp;redirect=http://www.php.net/manual/en/class.exception.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=class.exception&amp;redirect=http://www.php.net/manual/en/class.exception.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/class.exception.php">show source</a> |
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